<?php

use function Livewire\Volt\{state, mount};
use Illuminate\Support\Facades\Auth;
use App\Models\Promotion;
use App\Models\Group;
use App\Models\Product;

state([
    'promotionId' => null,
    'name' => '',
    'code' => '',
    'description' => '',
    'type' => 'code',
    'discount_type' => 'percentage',
    'discount_value' => 0,
    'max_discount_amount' => null,
    'min_order_amount' => null,
    'valid_from' => null,
    'valid_to' => null,
    'restriction_type' => 'none',
    'selected_groups' => [],
    'selected_products' => [],
    'available_groups' => [],
    'available_products' => [],
    'is_active' => true,
    'can_combine_with_others' => false,
    'always_applicable' => false,
    'priority' => 0,
    'showSuccessMessage' => false,
]);

$addGroup = function ($groupPath, $groupName) {
    if (!in_array($groupPath, $this->selected_groups)) {
        $this->selected_groups[] = $groupPath;
        $this->dispatch('groups-updated');
    }
};

$removeGroup = function ($groupPath) {
    $this->selected_groups = array_values(array_filter($this->selected_groups, fn($path) => $path !== $groupPath));
    $this->dispatch('groups-updated');
};

$addProduct = function ($productId, $productName) {
    if (!in_array($productId, $this->selected_products)) {
        $this->selected_products[] = $productId;
    }
};

$removeProduct = function ($productId) {
    $this->selected_products = array_values(array_filter($this->selected_products, fn($id) => $id != $productId));
};

$updatedRestrictionType = function () {
    // Resetuj wartości przy zmianie typu ograniczenia
    $this->selected_groups = [];
    $this->selected_products = [];
};

$flattenGroups = function ($groups) {
    $result = [];
    foreach ($groups as $groupName => $groupData) {
        if (is_array($groupData) && isset($groupData['full_path'])) {
            $fullPath = $groupData['full_path'];
            $result[] = [
                'path' => $fullPath,
                'name' => $groupData['name'] ?? $groupName,
                'full_name' => str_replace('\\', ' / ', $fullPath),
            ];
            if (isset($groupData['children']) && is_array($groupData['children']) && !empty($groupData['children'])) {
                $result = array_merge($result, $this->flattenGroups($groupData['children']));
            }
        }
    }
    return $result;
};

mount(function ($promotionId = null) {
    // Sprawdź czy użytkownik jest zalogowany
    if (!Auth::check() || (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('editor'))) {
        abort(403, 'Brak dostępu');
    }

    // Pobierz dostępne grupy (spłaszczona struktura) - z limitem aby uniknąć błędu pamięci
    $hierarchicalGroups = Group::limit(50)
        ->get()
        ->map(function ($group) {
            return [
                'path' => $group->path,
                'name' => $group->name,
                'full_name' => $group->full_name,
            ];
        })
        ->toArray();
    $this->available_groups = $hierarchicalGroups;

    // Pobierz dostępne produkty (tylko aktywne, z grupą)
    $this->available_products = Product::with('productNameFeature')
        ->orderBy('Nazwa')
        ->limit(1000)
        ->get()
        ->map(function ($product) {
            return [
                'ID' => $product->ID,
                'Nazwa' => $product->productNameFeature->Name ?? ($product->Nazwa ?? 'Produkt #' . $product->ID),
            ];
        })
        ->keyBy('ID')
        ->toArray();

    // Jeśli edycja, załaduj dane promocji
    if ($promotionId) {
        $this->promotionId = $promotionId;
        $this->loadPromotionData();
    }
});

$loadPromotionData = function () {
    // Jeśli edycja, załaduj dane promocji
    if ($this->promotionId) {
        $promotion = Promotion::with(['promotionProducts' => fn($q) => $q->limit(10), 'promotionGroups' => fn($q) => $q->limit(10)])->findOrFail($this->promotionId);
        $this->name = $promotion->name;
        $this->code = $promotion->code ?? '';
        $this->description = $promotion->description ?? '';
        $this->type = $promotion->type;
        $this->discount_type = $promotion->discount_type;
        $this->discount_value = $promotion->discount_value;
        $this->max_discount_amount = $promotion->max_discount_amount;
        $this->min_order_amount = $promotion->min_order_amount;
        $this->valid_from = $promotion->valid_from ? $promotion->valid_from->format('Y-m-d') : null;
        $this->valid_to = $promotion->valid_to ? $promotion->valid_to->format('Y-m-d') : null;

        // Ustaw typ ograniczenia na podstawie relacji
        $hasGroups = $promotion->promotionGroups->count() > 0;
        $hasProducts = $promotion->promotionProducts->count() > 0;

        if ($hasGroups) {
            $this->restriction_type = 'groups';
            $this->selected_groups = $promotion->promotionGroups->pluck('group_path')->toArray();
        } elseif ($hasProducts) {
            $this->restriction_type = 'products';
            $this->selected_products = $promotion->promotionProducts->pluck('product_id')->toArray();
        } else {
            $this->restriction_type = 'none';
            $this->selected_groups = [];
            $this->selected_products = [];
        }

        $this->is_active = $promotion->is_active;
        $this->can_combine_with_others = $promotion->can_combine_with_others;
        $this->always_applicable = $promotion->always_applicable;
        $this->priority = $promotion->priority;
    } else {
        // Resetuj formularz dla nowej promocji
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->type = 'code';
        $this->discount_type = 'percentage';
        $this->discount_value = 0;
        $this->max_discount_amount = null;
        $this->min_order_amount = null;
        $this->valid_from = null;
        $this->valid_to = null;
        $this->restriction_type = 'none';
        $this->selected_groups = [];
        $this->selected_products = [];
        $this->is_active = true;
        $this->can_combine_with_others = false;
        $this->always_applicable = false;
        $this->priority = 0;
    }
};

$save = function () {
    // Walidacja
    $this->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:code,automatic,seasonal',
        'discount_type' => 'required|in:percentage,fixed,free_delivery',
        'discount_value' => $this->discount_type !== 'free_delivery' ? 'required|numeric|min:0' : 'nullable',
    ]);

    // Zapisz promocję
    $promotion = Promotion::updateOrCreate(
        ['id' => $this->promotionId],
        [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'type' => $this->type,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value ?? 0,
            'max_discount_amount' => $this->max_discount_amount,
            'min_order_amount' => $this->min_order_amount,
            'valid_from' => $this->valid_from ? \Carbon\Carbon::parse($this->valid_from) : null,
            'valid_to' => $this->valid_to ? \Carbon\Carbon::parse($this->valid_to) : null,
            'is_active' => $this->is_active,
            'can_combine_with_others' => $this->can_combine_with_others,
            'always_applicable' => $this->always_applicable,
            'priority' => $this->priority,
        ],
    );

    // Zapisz ograniczenia
    if ($this->restriction_type === 'groups') {
        // Usuń stare grupy
        $promotion->promotionGroups()->delete();
        // Dodaj nowe grupy
        foreach ($this->selected_groups as $groupPath) {
            $promotion->promotionGroups()->create(['group_path' => $groupPath]);
        }
        // Usuń produkty (jeśli były)
        $promotion->promotionProducts()->delete();
    } elseif ($this->restriction_type === 'products') {
        // Usuń stare produkty
        $promotion->promotionProducts()->delete();
        // Dodaj nowe produkty
        foreach ($this->selected_products as $productId) {
            $promotion->promotionProducts()->create(['product_id' => $productId]);
        }
        // Usuń grupy (jeśli były)
        $promotion->promotionGroups()->delete();
    } else {
        // Brak ograniczeń - usuń wszystkie
        $promotion->promotionGroups()->delete();
        $promotion->promotionProducts()->delete();
    }

    $this->promotionId = $promotion->id;

    // Wyświetl komunikat sukcesu w modalu
    $this->showSuccessMessage = true;
};

?>

<div>
    {{-- Komunikat sukcesu --}}
    @if ($showSuccessMessage)
        <x-admin-bar.message :show-success-message="$showSuccessMessage"
            message="{{ $promotionId ? 'Promocja została zaktualizowana.' : 'Promocja została utworzona.' }}"
            :overlay="false" />
    @endif

    <div wire:ignore.self x-data @save-promotion-form.window="$wire.call('save')" class="space-y-6">
        {{-- Podstawowe informacje --}}
        <div class="border border-gray-200 rounded-lg bg-white">
            <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">
                Podstawowe informacje</flux:heading>
            <div class="p-4 pt-3 space-y-3">
                <flux:field>
                    <flux:label>Nazwa promocji</flux:label>
                    <flux:input type="text" wire:model="name" required />
                </flux:field>

                <flux:field>
                    <flux:label>Typ promocji</flux:label>
                    <flux:select wire:model.live="type">
                        <option value="code">Kod promocyjny</option>
                        <option value="automatic">Automatyczna</option>
                        <option value="seasonal">Sezonowa</option>
                    </flux:select>
                </flux:field>

                @if ($type === 'code')
                    <flux:field>
                        <flux:label>Kod promocyjny</flux:label>
                        <flux:input type="text" wire:model="code" />
                        <flux:description class="mt-1!">Kod, który użytkownik musi wprowadzić, aby skorzystać z
                            promocji</flux:description>
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Opis</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                </flux:field>
            </div>
        </div>

        {{-- Zniżka --}}
        <div class="border border-gray-200 rounded-lg bg-white">
            <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">
                Zniżka</flux:heading>
            <div class="p-4 pt-3 space-y-3">
                <flux:field>
                    <flux:label>Typ zniżki</flux:label>
                    <flux:select wire:model.live="discount_type">
                        <option value="percentage">Procentowa (%)</option>
                        <option value="fixed">Kwotowa (zł)</option>
                        <option value="free_delivery">Darmowa dostawa</option>
                    </flux:select>
                </flux:field>

                @if ($discount_type !== 'free_delivery')
                    <flux:field>
                        <flux:label>Wartość zniżki</flux:label>
                        <flux:input type="number" wire:model="discount_value" step="0.01" min="0" required />
                    </flux:field>
                @endif
            </div>
        </div>

        {{-- Daty i limity --}}
        <div class="border border-gray-200 rounded-lg bg-white">
            <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">Daty
                i limity</flux:heading>
            <div class="p-4 pt-3 space-y-3">
                <flux:field>
                    <flux:label>Okres ważności (opcjonalnie)</flux:label>
                    <div class="flex items-center gap-3">
                        <flux:input type="date" wire:model="valid_from" placeholder="Od" class="flex-1" />
                        <span class="text-gray-500">-</span>
                        <flux:input type="date" wire:model="valid_to" placeholder="Do" class="flex-1" />
                    </div>
                </flux:field>

                <flux:field>
                    <flux:label>Minimalna wartość zamówienia (opcjonalnie)</flux:label>
                    <flux:input type="number" wire:model="min_order_amount" step="0.01" min="0"
                        placeholder="0.00" />
                    <flux:description class="mt-1!">Minimalna wartość zamówienia wymagana do aktywacji promocji
                    </flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Ograniczenia</flux:label>
                    <flux:select wire:model.live="restriction_type">
                        <option value="none">Brak ograniczeń</option>
                        <option value="groups">Grupy produktów</option>
                        <option value="products">Poszczególne produkty</option>
                    </flux:select>
                </flux:field>

                @if ($restriction_type === 'groups')
                    <flux:field wire:key="groups-field-{{ md5(implode(',', $selected_groups)) }}">
                        <flux:label>Dodaj grupy</flux:label>
                        <div x-data="{
                            search: '',
                            suggestions: [],
                            showSuggestions: false,
                            selectedIndex: -1,
                            groups: @js($available_groups),
                            get selectedGroups() {
                                return $wire.get('selected_groups');
                            },
                            init() {
                                this.$watch('search', (value) => {
                                    if (value.length >= 2) {
                                        // Zawsze aktualizuj selectedGroups przed filtrowaniem
                                        this.selectedGroups = $wire.get('selected_groups');
                                        this.filterSuggestions();
                                        this.showSuggestions = true;
                                    } else {
                                        this.showSuggestions = false;
                                    }
                                });
                            },
                            filterSuggestions() {
                                const query = this.search.toLowerCase();
                                this.suggestions = this.groups
                                    .filter(g => {
                                        // Sprawdź czy nazwa zawiera zapytanie
                                        if (!g.full_name.toLowerCase().includes(query)) {
                                            return false;
                                        }

                                        // Sprawdź czy grupa nie jest już dodana
                                        if (this.selectedGroups.includes(g.path)) {
                                            return false;
                                        }

                                        // Sprawdź czy grupa nie jest podgrupą już dodanej grupy
                                        const isSubgroup = this.selectedGroups.some(selectedPath => {
                                            // Sprawdź czy ścieżka grupy zaczyna się od ścieżki dodanej grupy
                                            return g.path.startsWith(selectedPath + '\\') || g.path.startsWith(selectedPath + '/');
                                        });

                                        return !isSubgroup;
                                    })
                                    .slice(0, 10);
                            },
                            addGroup(group) {
                                $wire.call('addGroup', group.path, group.full_name);
                                this.search = '';
                                this.showSuggestions = false;
                            },
                            handleKeydown(e) {
                                if (e.key === 'ArrowDown') {
                                    e.preventDefault();
                                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                                } else if (e.key === 'ArrowUp') {
                                    e.preventDefault();
                                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                                } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                                    e.preventDefault();
                                    this.addGroup(this.suggestions[this.selectedIndex]);
                                }
                            }
                        }" class="space-y-2">
                            <div class="relative">
                                <flux:input type="text" x-model="search" @keydown="handleKeydown"
                                    @focus="showSuggestions = search.length >= 2" @click.away="showSuggestions = false"
                                    placeholder="Wpisz nazwę grupy..." />
                                <div x-show="showSuggestions && suggestions.length > 0" x-cloak
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg"
                                    style="max-height: 160px; overflow-y: auto; overflow-x: hidden;">
                                    <template x-for="(suggestion, index) in suggestions" :key="suggestion.path">
                                        <div @click="addGroup(suggestion)" @mouseenter="selectedIndex = index"
                                            :class="index === selectedIndex ? 'bg-gray-100' : ''"
                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                            <span x-text="suggestion.full_name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            @if (count($selected_groups) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($selected_groups as $groupPath)
                                        @php
                                            $group = collect($available_groups)->firstWhere('path', $groupPath);
                                            $groupName = $group['full_name'] ?? $groupPath;
                                        @endphp
                                        <div
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 rounded-full text-sm">
                                            <span>{{ $groupName }}</span>
                                            <button type="button" wire:click="removeGroup('{{ $groupPath }}')"
                                                class="ml-1 text-gray-500 hover:text-gray-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </flux:field>
                @endif

                @if ($restriction_type === 'products')
                    <flux:field wire:key="products-field">
                        <flux:label>Dodaj produkty</flux:label>
                        <div x-data="{
                            search: '',
                            suggestions: [],
                            showSuggestions: false,
                            selectedIndex: -1,
                            products: @js($available_products),
                            selectedProducts: @js($selected_products),
                            init() {
                                this.$watch('search', (value) => {
                                    if (value.length >= 2) {
                                        this.filterSuggestions();
                                        this.showSuggestions = true;
                                    } else {
                                        this.showSuggestions = false;
                                    }
                                });
                            },
                            filterSuggestions() {
                                const query = this.search.toLowerCase();
                                const queryTrimmed = this.search.trim(); // Dla ID używamy oryginalnego query (bez toLowerCase)

                                this.suggestions = this.products
                                    .filter(p => {
                                        // Sprawdź czy produkt nie jest już dodany
                                        if (this.selectedProducts.includes(p.ID)) {
                                            return false;
                                        }

                                        // Sprawdź czy nazwa zawiera zapytanie (case-insensitive)
                                        const matchesName = p.Nazwa.toLowerCase().includes(query);

                                        // Sprawdź czy ID zaczyna się od wpisanych cyfr (traktujemy jako tekst, case-sensitive)
                                        const productIdStr = p.ID.toString();
                                        const matchesId = productIdStr.startsWith(queryTrimmed);

                                        return matchesName || matchesId;
                                    })
                                    .slice(0, 10);
                            },
                            addProduct(product) {
                                @this.call('addProduct', product.ID, product.Nazwa);
                                // Zaktualizuj selectedProducts po dodaniu (użyj setTimeout, aby poczekać na aktualizację Livewire)
                                setTimeout(() => {
                                    this.selectedProducts = $wire.get('selected_products');
                                    this.search = '';
                                    this.showSuggestions = false;
                                }, 50);
                            },
                            handleKeydown(e) {
                                if (e.key === 'ArrowDown') {
                                    e.preventDefault();
                                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                                } else if (e.key === 'ArrowUp') {
                                    e.preventDefault();
                                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                                } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                                    e.preventDefault();
                                    this.addProduct(this.suggestions[this.selectedIndex]);
                                }
                            }
                        }" class="space-y-2">
                            <div class="relative">
                                <flux:input type="text" x-model="search" @keydown="handleKeydown"
                                    @focus="showSuggestions = search.length >= 2" @click.away="showSuggestions = false"
                                    placeholder="Wpisz ID lub nazwę produktu..." />
                                <div x-show="showSuggestions && suggestions.length > 0" x-cloak
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg"
                                    style="max-height: 160px; overflow-y: auto; overflow-x: hidden;">
                                    <template x-for="(suggestion, index) in suggestions" :key="suggestion.ID">
                                        <div @click="addProduct(suggestion)" @mouseenter="selectedIndex = index"
                                            :class="index === selectedIndex ? 'bg-gray-100' : ''"
                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                            <span x-text="suggestion.Nazwa"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            @if (count($selected_products) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($selected_products as $productId)
                                        @php
                                            $product = collect($available_products)->firstWhere('ID', $productId);
                                            $productName = $product['Nazwa'] ?? 'Produkt #' . $productId;
                                        @endphp
                                        <div
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 rounded-full text-sm">
                                            <span>{{ $productName }} (ID: {{ $productId }})</span>
                                            <button type="button" wire:click="removeProduct({{ $productId }})"
                                                class="ml-1 text-gray-500 hover:text-gray-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </flux:field>
                @endif
            </div>
        </div>

        {{-- Ustawienia zaawansowane --}}
        <div class="border border-gray-200 rounded-lg bg-white">
            <flux:heading size="sm" class="p-4 pb-3 font-semibold text-gray-900 border-b border-gray-200">
                Ustawienia zaawansowane</flux:heading>
            <div class="p-4 pt-3 space-y-3">
                <flux:checkbox wire:model="is_active" label="Promocja aktywna" />

                <flux:checkbox wire:model="can_combine_with_others" label="Można łączyć z innymi promocjami" />

                <flux:checkbox wire:model="always_applicable" label="Zawsze dostępna" />

                <div class="flex items-center gap-3">
                    <flux:label class="mb-0! whitespace-nowrap">Priorytet</flux:label>
                    <flux:select wire:model="priority" class="flex-1">
                        <option value="0">0 - Najniższy</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5 - Najwyższy</option>
                    </flux:select>
                </div>
                <flux:description class="mt-1!">Wyższy priorytet = pierwszeństwo przy konfliktach
                </flux:description>
            </div>
        </div>
    </div>
</div>

<?php

use function Livewire\Volt\{state, mount};
use Illuminate\Support\Facades\Auth;
use App\Models\Promotion;
use App\Models\Group;
use App\Models\Product;

state([
    'promotionId' => null,
    'editingContent' => '',
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
    'saved' => false,
    'showValidationError' => false,
    'validationErrorMessage' => '',
]);

$addGroup = function ($groupPath, $groupName) {
    if (!in_array($groupPath, $this->selected_groups)) {
        // Wyczyść produkty przy dodaniu grupy (inny typ ograniczenia)
        $this->selected_products = [];
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
        // Wyczyść grupy przy dodaniu produktu (inny typ ograniczenia)
        $this->selected_groups = [];
        $this->selected_products[] = $productId;
    }
};

$removeProduct = function ($productId) {
    $this->selected_products = array_values(array_filter($this->selected_products, fn($id) => $id != $productId));
};

$updatedRestrictionType = function () {
    // Nie czyścimy elementów przy zmianie typu - użytkownik może chcieć wrócić
    // Elementy zostaną wyczyszczone tylko przy dodaniu innego typu lub przy zapisie
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

    // Pobierz dostępne grupy z cache - użyj getAllGroupsHierarchy() (43 grupy z produktami)
    $hierarchicalGroups = Group::getAllGroupsHierarchy();
    $flatGroups = Group::flattenHierarchyForFlux($hierarchicalGroups);

    // Przekształć do formatu potrzebnego dla autocomplete
    $availableGroups = collect($flatGroups)->map(function ($group) {
        return [
            'path' => $group['full_path'],
            'name' => $group['name'],
            'full_name' => $group['full_path'], // Użyj pełnej ścieżki jako nazwy
        ];
    });

    // Debug: sprawdź liczbę grup przed i po unikalności (używamy getAllGroupsHierarchy)
    logger('Grupy (getAllGroupsHierarchy) przed unique: ' . $availableGroups->count());
    $availableGroups = $availableGroups->unique('path'); // Usuń duplikaty po ścieżce
    logger('Grupy (getAllGroupsHierarchy) po unique: ' . $availableGroups->count());

    $availableGroups = $availableGroups->values()->toArray();

    $this->available_groups = $availableGroups;

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

$setPromotionId = function ($promotionId) {
    $this->promotionId = $promotionId;
    $this->loadPromotionData();
};

$save = function () {
    // Walidacja
    $this->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|in:code,automatic,seasonal',
        'code' => $this->type === 'code' ? 'required|string|max:50' : 'nullable',
        'discount_type' => 'required|in:percentage,fixed,free_delivery',
        'discount_value' => $this->discount_type !== 'free_delivery' ? 'required|numeric|min:0' : 'nullable',
    ]);

    // Walidacja ograniczeń - sprawdź czy wybrano typ ale nie dodano elementów
    if ($this->restriction_type === 'groups' && empty($this->selected_groups)) {
        $this->showValidationError = true;
        $this->validationErrorMessage = 'Nie wybrano grup produktów';
        return;
    }

    if ($this->restriction_type === 'products' && empty($this->selected_products)) {
        $this->showValidationError = true;
        $this->validationErrorMessage = 'Nie wybrano produktów';
        return;
    }

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
    } elseif ($this->restriction_type === 'products') {
        // Usuń stare produkty
        $promotion->promotionProducts()->delete();
        // Dodaj nowe produkty
        foreach ($this->selected_products as $productId) {
            $promotion->promotionProducts()->create(['product_id' => $productId]);
        }
    }

    // Pokaż komunikat sukcesu
    $this->saved = true;
    logger('Promotion saved: ' . ($this->saved ? 'true' : 'false'));
    $this->promotionId = $promotion->id;

    // Zaktualizuj listę promocji
    $this->dispatch('refresh-promotions');
};

?>

<div>
    <x-admin-bar.edit-modal name="promotion-form-modal" title="Dodaj promocję" subtitle="Utwórz nową promocję"
        widthClass="md:max-w-[50vw]!" save-action="save" :show-success="$saved"
        success-message="{{ $promotionId ? 'Promocja została zaktualizowana.' : 'Promocja została utworzona.' }}"
        x-data="{ promotionId: null }" @edit-promotion.window="promotionId = $event.detail.id">
        <x-slot name="extraActions">
            <flux:button type="button" variant="ghost" wire:off x-data=""
                @click.stop="
                    $flux.modal('promotion-form-modal').close();
                    setTimeout(() => {
                        $flux.modal('promotions-modal').show();
                    }, 300);
                ">
                Lista promocji
            </flux:button>
        </x-slot>
        <div x-init="$watch('promotionId', (value) => {
            setTimeout(() => {
                if (value) {
                    @this.call('setPromotionId', value);
                }
            }, 100);
        });">
            <!-- Podstawowe informacje -->
            <flux:input wire:model="name" label="Nazwa promocji" placeholder="Wprowadź nazwę promocji" class="mb-4" />

            <!-- Status i ograniczenia -->
            <div class="mb-4 p-3 border border-gray-200 rounded-lg bg-gray-50">
                <flux:label class="text-sm font-medium text-gray-700 mb-2 block">Status promocji</flux:label>
                <div class="space-y-2">
                    <flux:checkbox wire:model="is_active" label="Promocja aktywna" />
                    <flux:checkbox wire:model="can_combine_with_others" label="Można łączyć z innymi promocjami" />
                </div>
            </div>

            <flux:textarea wire:model="description" label="Opis promocji" placeholder="Wprowadź opis promocji"
                rows="3" class="mb-4" />

            <!-- Typ promocji -->
            <flux:select wire:model.live="type" label="Typ promocji" class="mb-4">
                <option value="code">Kod promocyjny</option>
                <option value="automatic">Automatyczna</option>
                <option value="seasonal">Sezonowa</option>
            </flux:select>

            @if ($type === 'code')
                <!-- Kod promocyjny -->
                <div class="mb-4">
                    <flux:input wire:model="code" label="Kod promocyjny" placeholder="Wprowadź kod promocyjny" />
                    <flux:description class="mt-1!">Kod, który użytkownik musi wprowadzić, aby skorzystać z promocji
                    </flux:description>
                </div>
            @endif

            <!-- Typ i wartość zniżki -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <flux:select wire:model.live="discount_type" label="Typ zniżki">
                    <option value="percentage">Procentowa (%)</option>
                    <option value="fixed">Kwotowa (zł)</option>
                    <option value="free_delivery">Darmowa dostawa</option>
                </flux:select>

                @if ($discount_type !== 'free_delivery')
                    <flux:input type="number" wire:model="discount_value" label="Wartość zniżki" step="0.01"
                        min="0" />
                @endif
            </div>

            @if ($discount_type === 'percentage')
                <flux:input type="number" wire:model="max_discount_amount" label="Maksymalna wartość zniżki (zł)"
                    step="0.01" min="0" placeholder="Bez limitu" class="mb-4" />
            @endif

            <!-- Minimalna wartość zamówienia -->
            <div class="mb-4">
                <flux:input type="number" wire:model="min_order_amount" label="Minimalna wartość zamówienia (zł)"
                    step="0.01" min="0" placeholder="0.00" />
                <flux:description class="mt-1!">Minimalna wartość zamówienia wymagana do aktywacji promocji
                </flux:description>
            </div>

            <!-- Daty ważności -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <flux:input type="date" wire:model="valid_from" label="Data od" />
                <flux:input type="date" wire:model="valid_to" label="Data do" />
            </div>

            <!-- Ograniczenia -->
            <div class="mb-4">
                <flux:select wire:model.live="restriction_type" label="Zastosuj do">
                    <option value="none">Wszystkie produkty</option>
                    <option value="products">Wybrane produkty</option>
                    <option value="groups">Wybrane grupy produktów</option>
                </flux:select>
                <x-admin-bar.message :show="$showValidationError" :message="$validationErrorMessage" type="error" :timeout="5000"
                    :show-background="false" wire-property="showValidationError" />
            </div>

            @if ($restriction_type === 'groups')
                <!-- Wybór grup -->
                <div class="mb-4">
                    <flux:label>Wybierz grupy produktów</flux:label>
                    <div class="text-xs text-gray-500 mb-2">Dostępne grupy: {{ count($available_groups) }}</div>
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
                            console.log('Grupy dostępne:', this.groups.length);
                            console.log('Pierwsza grupa w JS:', this.groups[0]);
                            console.log('Wszystkie grupy:', this.groups.slice(0, 3));
                            this.$watch('search', (value) => {
                                console.log('Wyszukiwanie grup:', value);
                                if (value.length >= 2) {
                                    console.log('Uruchamiam filtrowanie dla:', value);
                                    this.selectedGroups = $wire.get('selected_groups');
                                    this.filterSuggestions();
                                    this.showSuggestions = true;
                                } else {
                                    console.log('Ukrywam sugestie - za mało znaków');
                                    this.showSuggestions = false;
                                }
                            });
                        },
                        filterSuggestions() {
                            const query = this.search.toLowerCase();
                            console.log('Filtruję grupy dla query:', query);
                            this.suggestions = this.groups
                                .filter((g, index) => {
                                    if (!g.full_name || !g.full_name.toLowerCase().includes(query)) {
                                        return false;
                                    }
                                    if (this.selectedGroups.includes(g.path)) {
                                        return false;
                                    }
                                    const isSubgroup = this.selectedGroups.some(selectedPath => {
                                        return g.path.startsWith(selectedPath + '\\') || g.path.startsWith(selectedPath + '/');
                                    });
                                    return !isSubgroup;
                                });
                            console.log('Znalezione sugestie grup:', this.suggestions.length);
                        },
                        addGroup(group) {
                            $wire.call('addGroup', group.path, group.full_name);
                            this.search = '';
                            this.showSuggestions = false;
                        },
                        handleKeydown(e) {
                            if (!this.showSuggestions || this.suggestions.length === 0) return;
                    
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                            } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                            } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                                e.preventDefault();
                                this.addGroup(this.suggestions[this.selectedIndex]);
                            } else if (e.key === 'Escape') {
                                this.showSuggestions = false;
                                this.selectedIndex = -1;
                            }
                        }
                    }">
                        <div class="relative">
                            <flux:input x-model="search" @keydown="handleKeydown($event)"
                                placeholder="Wpisz nazwę grupy (min. 2 znaki)" />

                            <div x-show="showSuggestions && suggestions.length > 0"
                                class="fixed z-50 bg-white border border-gray-300 rounded-lg shadow-lg max-h-96 overflow-y-auto"
                                :style="`top: ${$el.previousElementSibling.getBoundingClientRect().bottom + window.scrollY + 4}px; left: ${$el.previousElementSibling.getBoundingClientRect().left + window.scrollX}px; width: ${$el.previousElementSibling.offsetWidth}px;`">
                                <template x-for="(suggestion, index) in suggestions"
                                    :key="suggestion.path + '_' + index">
                                    <div @click="addGroup(suggestion)"
                                        :class="index === selectedIndex ? 'bg-blue-50' : 'bg-white'"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                                        <div class="font-medium" x-text="suggestion.full_name"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        @if (count($selected_groups) > 0)
                            <div class="mt-3 flex flex-wrap gap-2">
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($restriction_type === 'products')
                <!-- Wybór produktów -->
                <div class="mb-4">
                    <flux:label>Wybierz produkty</flux:label>
                    <div class="text-xs text-gray-500 mb-2">Dostępne produkty: {{ count($available_products) }}</div>
                    <div x-data="{
                        search: '',
                        suggestions: [],
                        showSuggestions: false,
                        selectedIndex: -1,
                        products: @js($available_products),
                        selectedProducts: @js($selected_products),
                        init() {
                            console.log('Produkty dostępne:', this.products.length);
                            this.$watch('search', (value) => {
                                console.log('Wyszukiwanie produktów:', value);
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
                            const queryTrimmed = this.search.trim();
                            console.log('Filtruję produkty dla query:', query, 'selectedProducts:', this.selectedProducts);
                    
                            this.suggestions = this.products
                                .filter(p => {
                                    // Sprawdź czy produkt nie jest już wybrany
                                    if (this.selectedProducts.includes(p.ID)) {
                                        return false;
                                    }
                    
                                    // Sprawdź czy ID pasuje (wyszukiwanie od lewej)
                                    const productIdStr = p.ID.toString();
                                    if (productIdStr.startsWith(queryTrimmed)) {
                                        return true;
                                    }
                    
                                    // Sprawdź czy nazwa pasuje (jeśli istnieje)
                                    if (p.Nazwa && p.Nazwa.toLowerCase().includes(query)) {
                                        return true;
                                    }
                    
                                    return false;
                                });
                            console.log('Znalezione sugestie produktów:', this.suggestions.length);
                        },
                        addProduct(product) {
                            @this.call('addProduct', product.ID, product.Nazwa);
                            setTimeout(() => {
                                this.selectedProducts = $wire.get('selected_products');
                                this.search = '';
                                this.showSuggestions = false;
                            }, 50);
                        },
                        handleKeydown(e) {
                            if (!this.showSuggestions || this.suggestions.length === 0) return;
                    
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                            } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                            } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                                e.preventDefault();
                                this.addProduct(this.suggestions[this.selectedIndex]);
                            } else if (e.key === 'Escape') {
                                this.showSuggestions = false;
                                this.selectedIndex = -1;
                            }
                        }
                    }">
                        <div class="relative">
                            <flux:input x-model="search" @keydown="handleKeydown($event)"
                                placeholder="Wpisz nazwę produktu lub ID (min. 2 znaki)" />

                            <div x-show="showSuggestions && suggestions.length > 0"
                                class="fixed z-50 bg-white border border-gray-300 rounded-lg shadow-lg max-h-96 overflow-y-auto"
                                :style="`top: ${$el.previousElementSibling.getBoundingClientRect().bottom + window.scrollY + 4}px; left: ${$el.previousElementSibling.getBoundingClientRect().left + window.scrollX}px; width: ${$el.previousElementSibling.offsetWidth}px;`">
                                <template x-for="(suggestion, index) in suggestions" :key="suggestion.ID">
                                    <div @click="addProduct(suggestion)"
                                        :class="index === selectedIndex ? 'bg-blue-50' : 'bg-white'"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                                        <div class="font-medium" x-text="suggestion.Nazwa"></div>
                                        <div class="text-sm text-gray-500">ID: <span x-text="suggestion.ID"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        @if (count($selected_products) > 0)
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($selected_products as $productId)
                                    @php
                                        $product = collect($available_products)->firstWhere('ID', $productId);
                                        $productName = $product['Nazwa'] ?? 'Produkt #' . $productId;
                                    @endphp
                                    <div
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 rounded-full text-sm">
                                        <span>{{ $productName }}</span>
                                        <button type="button" wire:click="removeProduct({{ $productId }})"
                                            class="ml-1 text-gray-500 hover:text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Priorytet -->
            <div class="mb-4">
                <flux:select wire:model="priority" label="Priorytet">
                    <option value="0">0 - Niski</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5 - Wysoki</option>
                </flux:select>
                <flux:description class="mt-1!">Wyższy priorytet = pierwszeństwo przy konfliktach</flux:description>
            </div>
        </div>
    </x-admin-bar.edit-modal>
</div>

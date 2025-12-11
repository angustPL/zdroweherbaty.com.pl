<?php

use function Livewire\Volt\{state, mount};
use Illuminate\Support\Facades\Auth;
use App\Models\Promotion;

state([
    'promotions' => [],
]);

mount(function () {
    // Sprawdź czy użytkownik jest zalogowany
    if (!Auth::check()) {
        abort(403, 'Brak dostępu');
    }

    // Pobierz promocje z bazy danych z relacjami
    $promotions = Promotion::with(['promotionProducts', 'promotionGroups'])->get();

    // Sortuj: najpierw status (aktywne i ważne), potem daty obowiązywania (valid_to - późniejsze wyżej)
    $this->promotions = $promotions
        ->sort(function ($a, $b) {
            $aIsActive = $a->is_active && $a->isValid();
            $bIsActive = $b->is_active && $b->isValid();

            // Najpierw sortuj według statusu (aktywne pierwsze)
            if ($aIsActive !== $bIsActive) {
                return $aIsActive ? -1 : 1; // Aktywne (-1) przed nieaktywnymi (1)
            }

            // Jeśli status jest taki sam, sortuj według dat obowiązywania (valid_to - późniejsze wyżej)
            $aValidTo = $a->valid_to ? $a->valid_to->timestamp : PHP_INT_MAX;
            $bValidTo = $b->valid_to ? $b->valid_to->timestamp : PHP_INT_MAX;

            return $bValidTo <=> $aValidTo; // Późniejsze daty (większy timestamp) wyżej
        })
        ->values();
});

$refreshPromotions = function () {
    // Pobierz promocje z bazy danych z relacjami
    $promotions = Promotion::with(['promotionProducts', 'promotionGroups'])->get();

    // Sortuj: najpierw status (aktywne i ważne), potem daty obowiązywania (valid_to - późniejsze wyżej)
    $this->promotions = $promotions
        ->sort(function ($a, $b) {
            $aIsActive = $a->is_active && $a->isValid();
            $bIsActive = $b->is_active && $b->isValid();

            // Najpierw sortuj według statusu (aktywne pierwsze)
            if ($aIsActive !== $bIsActive) {
                return $aIsActive ? -1 : 1; // Aktywne (-1) przed nieaktywnymi (1)
            }

            // Jeśli status jest taki sam, sortuj według dat obowiązywania (valid_to - późniejsze wyżej)
            $aValidTo = $a->valid_to ? $a->valid_to->timestamp : PHP_INT_MAX;
            $bValidTo = $b->valid_to ? $b->valid_to->timestamp : PHP_INT_MAX;

            return $bValidTo <=> $aValidTo; // Późniejsze daty (większy timestamp) wyżej
        })
        ->values();
};

?>

<div x-data @refresh-promotions.window="$wire.call('refreshPromotions')">
    @if (session('promotion_saved'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm text-green-800">{{ session('promotion_saved') }}</p>
            </div>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Lista promocji</h2>
        <flux:button variant="primary" type="button" wire:off x-data=""
            @click.stop="
                $dispatch('edit-promotion', { id: null });
                $flux.modal('promotions-modal').close();
                setTimeout(() => {
                    $flux.modal('promotion-form-modal').show();
                }, 300);
            ">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Dodaj promocję
        </flux:button>
    </div>

    @if (empty($promotions))
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Brak promocji</h3>
            <p class="mt-1 text-sm text-gray-500">Zacznij od utworzenia nowej promocji.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nazwa
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Typ
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Wartość
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data ważności
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ograniczenia
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Akcje
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($promotions as $promotion)
                        @php
                            $isInactive = !$promotion->is_active || !$promotion->isValid();
                        @endphp
                        <tr class="{{ $isInactive ? 'bg-gray-50' : '' }}">
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $isInactive ? 'text-gray-400' : 'text-gray-900' }}">
                                {{ $promotion->name }}
                                @if ($promotion->code)
                                    <span
                                        class="text-xs {{ $isInactive ? 'text-gray-300' : 'text-gray-500' }}">({{ $promotion->code }})</span>
                                @endif
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm {{ $isInactive ? 'text-gray-400' : 'text-gray-500' }}">
                                @if ($promotion->type === 'code')
                                    Kod promocyjny
                                @elseif($promotion->type === 'automatic')
                                    Automatyczna
                                @elseif($promotion->type === 'seasonal')
                                    Sezonowa
                                @else
                                    {{ $promotion->type }}
                                @endif
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm {{ $isInactive ? 'text-gray-400' : 'text-gray-500' }}">
                                @if ($promotion->discount_type === 'percentage')
                                    {{ number_format($promotion->discount_value, 2) }}%
                                @elseif($promotion->discount_type === 'fixed')
                                    {{ number_format($promotion->discount_value, 2) }} zł
                                @elseif($promotion->discount_type === 'free_delivery')
                                    Darmowa dostawa
                                @else
                                    {{ $promotion->discount_value }}
                                @endif
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm {{ $isInactive ? 'text-gray-400' : 'text-gray-500' }}">
                                @if ($promotion->valid_to)
                                    {{ $promotion->valid_to->format('Y-m-d') }}
                                @else
                                    Bez limitu
                                @endif
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm {{ $isInactive ? 'text-gray-400' : 'text-gray-500' }}">
                                @php
                                    $hasProducts = $promotion->promotionProducts->count() > 0;
                                    $hasGroups = $promotion->promotionGroups->count() > 0;
                                @endphp
                                @if ($hasProducts)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $isInactive ? 'bg-blue-50 text-blue-300' : 'bg-blue-100 text-blue-800' }}">
                                        Produkty
                                    </span>
                                @elseif($hasGroups)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $isInactive ? 'bg-blue-50 text-blue-300' : 'bg-blue-100 text-blue-800' }}">
                                        Grupy
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $isInactive ? 'bg-gray-50 text-gray-300' : 'bg-gray-100 text-gray-800' }}">
                                        Brak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" type="button" wire:off x-data=""
                                        class="{{ $isInactive ? 'opacity-60' : '' }}"
                                        @click.stop="
                                            $dispatch('edit-promotion', { id: {{ $promotion->id }} });
                                            $flux.modal('promotions-modal').close();
                                            setTimeout(() => {
                                                $flux.modal('promotion-form-modal').show();
                                            }, 300);
                                        ">
                                        Edytuj
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" type="button" wire:off
                                        class="{{ $isInactive ? 'opacity-60' : '' }}"
                                        @click.stop="if(confirm('Czy na pewno chcesz usunąć tę promocję?')) { /* TODO: Implementacja usuwania */ }">
                                        Usuń
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<?php

use function Livewire\Volt\{state, mount, layout};
use Illuminate\Support\Facades\Auth;
use App\Models\Promotion;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;

layout('layouts.app');

// SEO Meta Tags - ZABRONIONE INDEKSOWANIE
SEOTools::setTitle('Promocje - Zdrowe Herbaty BIFIX');
SEOTools::setDescription('Zarządzanie promocjami sklepu internetowego Zdrowe Herbaty BIFIX.');
SEOMeta::setRobots('noindex, nofollow');

state([
    'promotions' => [],
]);

mount(function () {
    // Sprawdź czy użytkownik jest zalogowany
        if (!Auth::check() || (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('editor'))) {
        abort(403, 'Brak dostępu');
    }

    // Pobierz promocje z bazy danych z relacjami
    $this->promotions = Promotion::with(['promotionProducts', 'promotionGroups'])
        ->orderBy('created_at', 'desc')
        ->get();
});

?>

<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Zarządzanie promocjami</h1>
            <p class="text-gray-600 mt-2">Twórz i zarządzaj promocjami w sklepie</p>
        </div>

        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Lista promocji</h2>
                <flux:button variant="primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Dodaj promocję
                </flux:button>
            </div>

            @if (empty($promotions))
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2">
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
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nazwa
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Typ
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Wartość
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data ważności
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ograniczenia
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Akcje
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($promotions as $promotion)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $promotion->name }}
                                        @if ($promotion->code)
                                            <span class="text-xs text-gray-500">({{ $promotion->code }})</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($promotion->valid_to)
                                            {{ $promotion->valid_to->format('Y-m-d H:i') }}
                                        @else
                                            Bez limitu
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @php
                                            $hasProducts = $promotion->promotionProducts->count() > 0;
                                            $hasGroups = $promotion->promotionGroups->count() > 0;
                                        @endphp
                                        @if ($hasProducts && $hasGroups)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $promotion->promotionProducts->count() }} produktów,
                                                {{ $promotion->promotionGroups->count() }} grup
                                            </span>
                                        @elseif($hasProducts)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $promotion->promotionProducts->count() }} produktów
                                            </span>
                                        @elseif($hasGroups)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ $promotion->promotionGroups->count() }} grup
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                                Wszystkie produkty
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($promotion->is_active && $promotion->isValid())
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktywna
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Nieaktywna
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="#" class="text-primary hover:text-primary-dark mr-4">Edytuj</a>
                                        <a href="#" class="text-red-600 hover:text-red-900">Usuń</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

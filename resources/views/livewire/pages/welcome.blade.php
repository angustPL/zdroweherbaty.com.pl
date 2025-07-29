<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;

layout('layouts.app');

// state(['products' => Product::all()]);

// dd(
//     Product::with(['group', 'productNameFeature', 'price'])
//         ->get()
//         ->map(function ($product) {
//             return [
//                 'ID' => $product->ID,
//                 'Nazwa' => $product->Nazwa,
//                 'Nazwa_Produktu' => $product->productNameFeature->Name ?? null,
//                 'Grupa' => $product->group->clean_name ?? null,
//                 'Opis' => $product->Opis,
//                 'MasaNettoValue' => $product->MasaNettoValue,
//                 'MasaNettoSymbol' => $product->MasaNettoSymbol,
//                 'MasaBruttoValue' => $product->MasaBruttoValue,
//                 'MasaBruttoSymbol' => $product->MasaBruttoSymbol,
//                 'SWW' => $product->SWW,
//                 'DefinicjaStawki' => $product->DefinicjaStawki,
//                 'NettoValue' => $product->price->NettoValue,
//                 'BruttoValue' => $product->price->BruttoValue,
//                 'StandardowaIloscValue' => $product->price->StandardowaIloscValue,
//                 'Jednostka' => $product->price->Jednostka,
//                 'StandardowaIloscSymbol' => $product->price->StandardowaIloscSymbol,
//             ];
//         })
//         ->toArray(),
// );

?>

<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Lista grup produktów
            </h1>
            <p class="text-lg text-gray-600">
                Wszystkie dostępne kategorie produktów
            </p>
        </div>
        {{--
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <ul class="divide-y divide-gray-200">
                @foreach ($groups as $group)
                    <li class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-lg font-medium text-gray-900">
                                {{ $group->clean_name }}
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold text-green-800 bg-green-100 rounded-full">
                                {{ $group->products_count ?? 0 }} produktów
                            </span>
                        </div>
                        <div class="mt-1 text-sm text-gray-500">
                            ID: {{ $group->ID }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div> --}}

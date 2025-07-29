<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;
use App\Models\Group;

layout('layouts.app');

state(['grupa' => '', 'products' => [], 'categoryName' => '']);

mount(function ($grupa) {
    $this->grupa = $grupa;

    // Dekodowanie URL
    $decodedGrupa = urldecode($grupa);

    // Konwersja z formatu URL na format bazy danych
    $groupPath = str_replace(config('enova.grupa_url_separator'), '\\', $decodedGrupa);

    // Dodanie prefixu i końcowego ukośnika dla formatu Enova
    $prefix = config('enova.features.product_group_prefix');
    $dbPath = $prefix . $groupPath . '\\';

    // Pobieranie produktów dla danej kategorii z nazwą i ceną
    $this->products = Product::with(['productNameFeature', 'price'])
        ->whereHas('group', function ($query) use ($dbPath) {
            $query->where('Data', $dbPath);
        })
        ->get()
        ->map(function ($product) {
            return [
                'ID' => $product->ID,
                'Nazwa' => $product->productNameFeature->Name ?? $product->Nazwa,
                'Grupa' => $product->group->clean_name ?? null,
                'Opis' => $product->Opis,
                'MasaNettoValue' => $product->MasaNettoValue,
                'MasaNettoSymbol' => $product->MasaNettoSymbol,
                'MasaBruttoValue' => $product->MasaBruttoValue,
                'MasaBruttoSymbol' => $product->MasaBruttoSymbol,
                'SWW' => $product->SWW,
                'DefinicjaStawki' => $product->DefinicjaStawki,
                'NettoValue' => $product->price->NettoValue,
                'BruttoValue' => $product->price->BruttoValue,
                'StandardowaIloscValue' => $product->price->StandardowaIloscValue,
                'Jednostka' => $product->price->Jednostka,
                'StandardowaIloscSymbol' => $product->price->StandardowaIloscSymbol,
            ];
        });

    // Pobieranie nazwy kategorii
    $category = Group::where('Data', $dbPath)->first();
    $this->categoryName = $category ? $category->clean_name : $decodedGrupa;
});

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $categoryName }}</h1>
        <p class="text-gray-600">Znaleziono {{ $products->count() }} produktów</p>
    </div>

    @if ($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ $product['Nazwa'] }}
                    </h3>
                    @if ($product['BruttoValue'])
                        <p class="text-2xl font-bold text-primary">{{ number_format($product['BruttoValue'], 2) }} zł</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Brak produktów w tej kategorii</p>
        </div>
    @endif
</div>

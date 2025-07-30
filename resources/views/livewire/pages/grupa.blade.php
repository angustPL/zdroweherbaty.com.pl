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
        <h1 class="text-3xl font-bold text-black mt-1 mb-2">{{ $categoryName }}</h1>
        <p class="text-gray-600">Znaleziono {{ $products->count() }} produktów</p>
    </div>

    @if ($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <div
                    class="relative text-center bg-white p-4 border border-gray-300 rounded-lg hover:shadow-xl hover:transition-shadow hover:duration-300 duration-300">
                    <a href="{{ route('towar', $product['ID']) }}" class="absolute top-4 left-4 right-4">
                        <h3 class="text-primary text-lg font-normal leading-tight text-gray-600 mb-2">
                            {{ $product['Nazwa'] }}
                        </h3>
                    </a>
                    <a href="{{ route('towar', $product['ID']) }}">
                        <img src="{{ Storage::disk('public')->exists('img/towary/' . $product['ID'] . '_200x120.jpg') ? Storage::disk('public')->url('img/towary/' . $product['ID'] . '_200x120.jpg') : asset('img/towary/placeholder.jpg') }}"
                            alt="{{ $product['Nazwa'] }}" class="w-auto max-h-[120px] mx-auto my-[80px]">
                    </a>
                    <div class="absolute bottom-4 left-4 right-4 text-center">
                        <p class="text-center text-2xl mb-1.5">
                            {{ number_format($product['BruttoValue'], 2, ',', '.') }} zł</p>
                        <livewire:components.add-to-cart-button :product-id="$product['ID']" :product-name="$product['Nazwa']" :price="$product['BruttoValue']"
                            :image="$product['ID'] . '_200x120.jpg'" />
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Brak produktów w tej kategorii</p>
        </div>
    @endif
</div>

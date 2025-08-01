{{-- Logika: app/Livewire/Pages/Towar.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;

layout('layouts.app');

state(['product' => null, 'productId' => null]);

mount(function ($id) {
    $this->productId = $id;
    $this->loadProduct();
});

$loadProduct = function () {
    // Załaduj produkt z bazy danych
    $product = Product::with(['productNameFeature', 'price', 'group'])->find($this->productId);

    if ($product) {
        $this->product = $product->toDisplayArray();
    } else {
        $this->product = null;
    }
};

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ $product['Nazwa'] ?? 'Produkt' }}
        </h1>
        <p class="text-gray-600">{{ $product['Grupa'] ?? 'Kategoria' }}</p>
    </div>

    @if ($product)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Zdjęcie produktu --}}
                <div class="text-center">
                    <img src="{{ Storage::disk('public')->exists('img/towary/' . $product['ID'] . '_800x600.jpg') ? Storage::disk('public')->url('img/towary/' . $product['ID'] . '_800x600.jpg') : asset('img/towary/placeholder.jpg') }}"
                        alt="{{ $product['Nazwa'] }}" class="w-full max-w-md mx-auto rounded-lg">
                </div>

                {{-- Informacje o produkcie --}}
                <div class="space-y-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary mb-4">
                            {{ number_format($product['BruttoValue'], 2, ',', '.') }} zł
                        </div>

                        <div>
                            <livewire:components.add-to-cart-button :product-id="$product['ID']" :product-name="$product['Nazwa']"
                                :price="$product['BruttoValue']" :image="$product['ID'] . '_200x120.jpg'" />
                        </div>
                    </div>

                    <div class="text-gray-600 leading-relaxed product-description">
                        {!! Str::of($product['Opis'] ?? 'Brak opisu produktu')->markdown() !!}
                    </div>
                    <style>
                        .product-description p {
                            margin-bottom: 1rem !important;
                        }
                    </style>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Produkt nie został znaleziony</p>
        </div>
    @endif
</div>

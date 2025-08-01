{{-- Logika: app/Livewire/Pages/Towar.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;
use Illuminate\Support\Str;

layout('layouts.app');

state(['product' => null, 'productId' => null]);

mount(function ($id, $name = null) {
    $this->productId = $id;

    // Załaduj produkt z bazy danych
    $product = Product::with(['productNameFeature', 'price', 'group'])
        ->where('ID', $this->productId)
        ->first();

    if ($product) {
        $this->product = $product->toDisplayArray();
    } else {
        $this->product = null;
    }

    // Sprawdź czy nazwa w URL zgadza się z nazwą w bazie
    if ($this->product) {
        $correctSlug = Str::slug($this->product['Nazwa']);

        // Jeśli nazwa jest nieprawidłowa lub brak nazwy, przekieruj na prawidłowy URL
        if ($name !== $correctSlug) {
            return redirect()->route('towar', [$id, $correctSlug]);
        }
    }
});

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ $product['Nazwa'] ?? 'Produkt' }}
        </h1>
        <p class="text-gray-600">{{ $product['Grupa'] ?? 'Kategoria' }}</p>
    </div>

    @if ($product)
        <div class="bg-white rounded-lg shadow p-6 mb-12">
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

        {{-- Podobne produkty --}}
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Podobne produkty</h2>
            <livewire:components.similar-products :product-id="$product['ID']" :product-name="$product['Nazwa']" />
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Produkt nie został znaleziony</p>
        </div>
    @endif
</div>

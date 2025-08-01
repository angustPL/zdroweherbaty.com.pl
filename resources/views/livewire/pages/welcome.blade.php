{{-- Logika: app/Livewire/Pages/Welcome.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;

layout('layouts.app');

state(['products' => []]);

mount(function () {
    // Pobieranie produktów z nazwą i ceną
    $this->products = Product::with(['productNameFeature', 'price', 'group'])
        ->get()
        ->map(function ($product) {
            return $product->toDisplayArray();
        });
});

?>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Zdrowe herbaty BIFIX
            </h1>
            <p class="text-lg text-gray-600">
                {{ $products->count() }} produktów
            </p>
        </div>

        @if ($products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    <livewire:components.product-card :product="$product" variant="default" />
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">Brak produktów do wyświetlenia</p>
            </div>
        @endif
    </div>
</div>

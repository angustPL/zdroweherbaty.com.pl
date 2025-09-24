<?php

use function Livewire\Volt\{state, mount, computed, layout};
use App\Models\Product;

layout('layouts.app');

// SEO Meta Tags - canonical URL jest automatycznie ustawiany z konfiguracji

// Open Graph - URL jest automatycznie ustawiany z konfiguracji

// Schema.org JSON-LD - wszystkie dane są automatycznie ustawiane z konfiguracji

state(['query' => '']);
state(['perPage' => 12]);
state(['currentPage' => 1]);

mount(function () {
    // GTM page type
    try {
        app('googletagmanager')->set('pageType', 'search');
    } catch (\Exception $e) {
        // Silent fail - GTM event not critical for functionality
    }
});

// Pobierz query z URL
$query = computed(function () {
    return request()->get('query', '');
});

// Wyszukiwanie produktów
$products = computed(function () {
    if (strlen($this->query) < 2) {
        return collect();
    }

    // Wyszukiwanie przez Algolia Scout
    $results = Product::search($this->query)->get();

    // Mapuj produkty przez toDisplayArray() żeby ceny były dostępne
    $results = $results->map(function ($product) {
        return $product->toDisplayArray();
    });

    return $results;
});

// Resetuj paginację przy zmianie query
$updatedQuery = function () {
    $this->currentPage = 1;
};

?>

<div class="container mx-auto px-4 py-8">
    <!-- Nagłówek -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-center text-gray-900 mb-4">
            @if ($this->query)
                Wyniki wyszukiwania dla: "{{ $this->query }}"
            @else
                Wyszukiwanie produktów
            @endif
        </h1>

        <!-- Pole wyszukiwania -->
        <div class="max-w-md mx-auto">
            <flux:input wire:model.live.debounce.500ms="query" icon="magnifying-glass" placeholder="Wyszukaj produkty..."
                clearable />
        </div>
    </div>

    <!-- Wyniki wyszukiwania -->
    @if ($this->query && strlen($this->query) >= 2)
        @if ($this->products->count() > 0)
            <div class="mb-6">
                <p class="text-gray-600">
                    Znaleziono {{ $this->products->count() }} produktów
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($this->products as $product)
                    <livewire:components.product-card :product="$product" variant="default" :wire:key="$product['ID']" />
                @endforeach
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-2xl font-medium text-gray-900 mb-4">Brak wyników</h3>
                <p class="text-gray-600 max-w-md mx-auto">
                    Nie znaleziono produktów spełniających kryteria wyszukiwania "{{ $this->query }}".
                    Spróbuj zmienić zapytanie lub sprawdź inne kategorie produktów.
                </p>
            </div>
        @endif
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-medium text-gray-900 mb-4">Wyszukaj produkty</h3>
            <p class="text-gray-600 max-w-md mx-auto">
                Wpisz co najmniej 2 znaki w polu wyszukiwania, aby rozpocząć wyszukiwanie produktów.
            </p>
        </div>
    @endif
</div>

<div>
    <!-- Pole wyszukiwania -->
    <div class="mb-6">
        <flux:input wire:model.live.debounce.300ms="query" icon="magnifying-glass" placeholder="Wyszukaj produkty"
            clearable />
    </div>

    <!-- Wyniki wyszukiwania -->
    @if ($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <livewire:components.product-card :product="$product" variant="default" :wire:key="$product['ID']" />
            @endforeach
        </div>

        <!-- Paginacja -->
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="text-xl font-medium mb-2">Brak wyników</h3>
            <p class="text-gray-500">
                @if ($query)
                    Nie znaleziono produktów spełniających kryteria wyszukiwania.
                @else
                    Brak dostępnych produktów.
                @endif
            </p>
        </div>
    @endif
</div>

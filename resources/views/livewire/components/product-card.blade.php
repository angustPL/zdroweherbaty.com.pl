{{-- Logika: app/Livewire/Components/ProductCard.php --}}
<div
    class="relative text-center bg-white p-4 border border-gray-300 rounded-lg hover:shadow-xl hover:transition-shadow hover:duration-300 duration-300">
    <a href="{{ route('towar', $product['ID']) }}" class="absolute top-4 left-4 right-4">
        <h3 class="text-primary text-lg font-normal leading-tight text-gray-600 mb-2">
            {{ $product['Nazwa'] }}
        </h3>
    </a>

    @if ($showImage)
        <a href="{{ route('towar', $product['ID']) }}">
            <img src="{{ Storage::disk('public')->exists('img/towary/' . $product['ID'] . '_200x120.jpg') ? Storage::disk('public')->url('img/towary/' . $product['ID'] . '_200x120.jpg') : asset('img/towary/placeholder.jpg') }}"
                alt="{{ $product['Nazwa'] }}" class="w-auto max-h-[120px] mx-auto my-[80px]">
        </a>
    @endif

    <div class="absolute bottom-4 left-4 right-4 text-center">
        @if ($showPrice)
            <p class="text-center text-2xl mb-1.5">
                {{ number_format($product['BruttoValue'], 2, ',', '.') }} zł
            </p>
        @endif

        @if ($showAddToCart)
            <livewire:components.add-to-cart-button :product-id="$product['ID']" :product-name="$product['Nazwa']" :price="$product['BruttoValue']"
                :image="$product['ID'] . '_200x120.jpg'" />
        @endif
    </div>
</div>

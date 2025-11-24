{{-- Logika: app/Livewire/Components/ProductCard.php --}}
<div
    class="relative text-center bg-white p-4 border border-gray-300 rounded-lg hover:shadow-xl hover:transition-shadow hover:duration-300 duration-300">
    <a href="{{ route('product', [$productId, Str::slug($productName)]) }}" class="absolute top-4 left-4 right-4"
        onclick="
            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    'event': 'select_item',
                    'ecommerce': {
                        'items': [{
                            'item_id': '{{ $productId }}',
                            'item_name': '{{ $productName }}',
                            'price': {{ $productPrice }},
                            'currency': 'PLN',
                            'item_category': '{{ $productGroup ?? '' }}',
                            'item_list_name': '{{ $productGroup ?? '' }}',
                            'item_list_id': '{{ $productGroup ?? '' }}'
                        }]
                    }
                });
            }
        ">
        <h3
            class="text-primary {{ $variant === 'compact' ? 'text-sm' : 'text-lg' }} font-normal leading-tight text-gray-600 mb-2">
            {{ $productName }}
        </h3>
    </a>

    @if ($showImage)
        <a href="{{ route('product', [$productId, Str::slug($productName)]) }}"
            onclick="
                if (typeof dataLayer !== 'undefined') {
                    dataLayer.push({
                        'event': 'select_item',
                        'ecommerce': {
                            'items': [{
                                'item_id': '{{ $productId }}',
                                'item_name': '{{ $productName }}',
                                'price': {{ $productPrice }},
                                'currency': 'PLN',
                                'item_category': '{{ $productGroup ?? '' }}',
                                'item_list_name': '{{ $productGroup ?? '' }}',
                                'item_list_id': '{{ $productGroup ?? '' }}'
                            }]
                        }
                    });
                }
            ">
            @php
                // Fallback: jeśli flaga nie jest ustawiona lub jest false, sprawdź plik bezpośrednio (dla kompatybilności wstecznej)
                // Używamy isset() aby sprawdzić czy właściwość istnieje, jeśli nie - sprawdzamy plik
                $imageExists = isset($hasImageSmall) ? $hasImageSmall : Storage::disk('public')->exists('img/towary/' . $productId . '_200x120.jpg');
            @endphp
            <img src="{{ $imageExists ? Storage::disk('public')->url('img/towary/' . $productId . '_200x120.jpg') : asset('img/towary/placeholder.jpg') }}"
                alt="{{ $productName }}"
                class="w-auto {{ $variant === 'compact' ? 'max-h-[80px]' : 'max-h-[120px] ?>' }} mx-auto my-[80px]">
        </a>
    @endif

    <div class="absolute bottom-4 left-4 right-4 text-center">
        @if ($showPrice)
            <p class="text-center {{ $variant === 'compact' ? 'text-lg' : 'text-2xl' }} mb-1.5">
                {{ Number::currency($productPrice, 'PLN', 'pl_PL') }}
            </p>
        @endif

        @if ($showAddToCart && $variant !== 'compact')
            <livewire:components.add-to-cart-button :product-id="$productId" :product-name="$productName" :price="$productPrice"
                :image="$productId . '_200x120.jpg'" :weight="$productWeight" />
        @endif
    </div>
</div>

{{-- Logika: app/Livewire/Components/ProductCard.php --}}
<div
    class="relative text-center bg-white p-4 border border-gray-300 rounded-lg hover:shadow-xl hover:transition-shadow hover:duration-300 duration-300">
    <a href="{{ route('product', [$product['ID'], Str::slug($product['Nazwa'])]) }}" class="absolute top-4 left-4 right-4"
        onclick="
            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    'event': 'select_item',
                    'ecommerce': {
                        'items': [{
                            'item_id': '{{ $product['ID'] }}',
                            'item_name': '{{ $product['Nazwa'] }}',
                            'price': {{ $product['BruttoValue'] }},
                            'currency': 'PLN',
                            'item_category': '{{ $product['Grupa'] ?? '' }}',
                            'item_list_name': '{{ $product['Grupa'] ?? '' }}',
                            'item_list_id': '{{ $product['Grupa'] ?? '' }}'
                        }]
                    }
                });
            }
        ">
        <h3
            class="text-primary {{ $variant === 'compact' ? 'text-sm' : 'text-lg' }} font-normal leading-tight text-gray-600 mb-2">
            {{ $product['Nazwa'] }}
        </h3>
    </a>

    @if ($showImage)
        <a href="{{ route('product', [$product['ID'], Str::slug($product['Nazwa'])]) }}"
            onclick="
                if (typeof dataLayer !== 'undefined') {
                    dataLayer.push({
                        'event': 'select_item',
                        'ecommerce': {
                            'items': [{
                                'item_id': '{{ $product['ID'] }}',
                                'item_name': '{{ $product['Nazwa'] }}',
                                'price': {{ $product['BruttoValue'] }},
                                'currency': 'PLN',
                                'item_category': '{{ $product['Grupa'] ?? '' }}',
                                'item_list_name': '{{ $product['Grupa'] ?? '' }}',
                                'item_list_id': '{{ $product['Grupa'] ?? '' }}'
                            }]
                        }
                    });
                }
            ">
            <img src="{{ Storage::disk('public')->exists('img/towary/' . $product['ID'] . '_200x120.jpg') ? Storage::disk('public')->url('img/towary/' . $product['ID'] . '_200x120.jpg') : asset('img/towary/placeholder.jpg') }}"
                alt="{{ $product['Nazwa'] }}"
                class="w-auto {{ $variant === 'compact' ? 'max-h-[80px]' : 'max-h-[120px] ?>' }} mx-auto my-[80px]">
        </a>
    @endif

    <div class="absolute bottom-4 left-4 right-4 text-center">
        @if ($showPrice)
            <p class="text-center {{ $variant === 'compact' ? 'text-lg' : 'text-2xl' }} mb-1.5">
                {{ Number::currency($product['BruttoValue'], 'PLN', 'pl_PL') }}
            </p>
        @endif

        @if ($showAddToCart && $variant !== 'compact')
            <livewire:components.add-to-cart-button :product-id="$product['ID']" :product-name="$product['Nazwa']" :price="$product['BruttoValue']"
                :image="$product['ID'] . '_200x120.jpg'" />
        @endif
    </div>
</div>

<div>
    @if (count($similarProducts) > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($similarProducts as $product)
                <livewire:components.product-card :product-id="$product['ID']" :product-name="$product['Nazwa']" :product-price="$product['BruttoValue']"
                    :product-group="$product['Grupa']" :product-weight="$product['MasaBruttoValue']" :has-image-small="$product['HasImageSmall'] ?? false" :wire:key="$product['ID']" />
            @endforeach
        </div>
    @endif
</div>

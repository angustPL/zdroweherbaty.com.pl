
<div
    class="relative text-center bg-white p-4 border border-gray-300 rounded-lg hover:shadow-xl hover:transition-shadow hover:duration-300 duration-300">
    <a href="<?php echo e(route('product', [$productId, Str::slug($productName)])); ?>" class="absolute top-4 left-4 right-4"
        onclick="
            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    'event': 'select_item',
                    'ecommerce': {
                        'items': [{
                            'item_id': '<?php echo e($productId); ?>',
                            'item_name': '<?php echo e($productName); ?>',
                            'price': <?php echo e($productPrice); ?>,
                            'currency': 'PLN',
                            'item_category': '<?php echo e($productGroup ?? ''); ?>',
                            'item_list_name': '<?php echo e($productGroup ?? ''); ?>',
                            'item_list_id': '<?php echo e($productGroup ?? ''); ?>'
                        }]
                    }
                });
            }
        ">
        <h3
            class="text-primary <?php echo e($variant === 'compact' ? 'text-sm' : 'text-lg'); ?> font-normal leading-tight text-gray-600 mb-2">
            <?php echo e($productName); ?>

        </h3>
    </a>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImage): ?>
        <a href="<?php echo e(route('product', [$productId, Str::slug($productName)])); ?>"
            onclick="
                if (typeof dataLayer !== 'undefined') {
                    dataLayer.push({
                        'event': 'select_item',
                        'ecommerce': {
                            'items': [{
                                'item_id': '<?php echo e($productId); ?>',
                                'item_name': '<?php echo e($productName); ?>',
                                'price': <?php echo e($productPrice); ?>,
                                'currency': 'PLN',
                                'item_category': '<?php echo e($productGroup ?? ''); ?>',
                                'item_list_name': '<?php echo e($productGroup ?? ''); ?>',
                                'item_list_id': '<?php echo e($productGroup ?? ''); ?>'
                            }]
                        }
                    });
                }
            ">
            <?php
                // Używamy hasImageSmall z cache - nie sprawdzamy pliku ponownie (to było wolne!)
                $imageExists = $hasImageSmall ?? false;
            ?>
            <img src="<?php echo e($imageExists ? asset('img/towary/' . $productId . '_200x120.jpg') : asset('img/towary/placeholder.jpg')); ?>"
                alt="<?php echo e($productName); ?>"
                class="w-auto <?php echo e($variant === 'compact' ? 'max-h-[80px]' : 'max-h-[120px] ?>'); ?> mx-auto my-[80px]">
        </a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="absolute bottom-4 left-4 right-4 text-center">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPrice): ?>
            <p class="text-center <?php echo e($variant === 'compact' ? 'text-lg' : 'text-2xl'); ?> mb-1.5">
                <?php echo e(Number::currency($productPrice, 'PLN', 'pl_PL')); ?>

            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAddToCart && $variant !== 'compact'): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.add-to-cart-button', ['productId' => $productId,'productName' => $productName,'price' => $productPrice,'image' => $productId . '_200x120.jpg','weight' => $productWeight,'groupCleanName' => $productGroup]);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1828396455-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views/livewire/components/product-card.blade.php ENDPATH**/ ?>
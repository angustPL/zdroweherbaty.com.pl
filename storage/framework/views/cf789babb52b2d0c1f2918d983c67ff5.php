<?php

use App\Models\Product;

?>




<?php $__env->startPush('header-banner'); ?>
    
    <div class="w-full h-[250px] md:h-[350px] lg:h-[450px] overflow-x-hidden"
        style="display: flex; align-items: center; justify-content: center;">
        <img src="<?php echo e(asset('img/bifix-hp-bg.jpg')); ?>" alt="BIFIX - Zdrowe herbaty"
            style="height: 100%; width: auto; min-width: 100%; object-fit: cover; object-position: center;">
    </div>
<?php $__env->stopPush(); ?>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Zdrowe herbaty BIFIX
            </h1>
            <p class="text-lg text-gray-600">
                <?php echo e(count($products)); ?> produktów
            </p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($products) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.product-card', ['productId' => $product['ID'],'productName' => $product['Nazwa'],'productPrice' => $product['BruttoValue'],'productGroup' => $product['Grupa'],'productWeight' => $product['MasaBruttoValue'],'hasImageSmall' => $product['HasImageSmall'] ?? false,'variant' => 'default']);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-722689996-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">Brak produktów do wyświetlenia</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views\livewire/pages/welcome.blade.php ENDPATH**/ ?>
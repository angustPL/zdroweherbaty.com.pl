<?php

use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;

?>

<div x-data @open-edit-modal.window="$wire.call('openEditModal')">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-black mt-1 mb-2">
            <?php echo e($groupContent && isset($groupContent->meta['h1']) && !empty($groupContent->meta['h1']) ? $groupContent->meta['h1'] : $groupName); ?>

        </h1>
        <p class="text-gray-600">Znaleziono <?php echo e(count($products)); ?> produktów</p>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($products) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('components.product-card', ['productId' => $product['ID'],'productName' => $product['Nazwa'],'productPrice' => $product['BruttoValue'],'productGroup' => $product['Grupa'],'productWeight' => $product['MasaBruttoValue'],'hasImageSmall' => $product['HasImageSmall'] ?? false,'variant' => 'default']);

$key = 'product-' . $product['ID'];

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-963628884-0', 'product-' . $product['ID']);

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
            <p class="text-gray-500 text-lg">Brak produktów w tej kategorii</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupContent && !empty($groupContent->content)): ?>
        <div class="hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8"></h2>
            <p class="text-gray-700 mb-4 mt-6"></p>
        </div>
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed mt-12">
            <?php echo $groupContent->content; ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check()): ?>
        <?php $__env->startPush('admin-bar-actions'); ?>
            <?php if (isset($component)) { $__componentOriginal1db8c57e729d67f7d4103875cf3230cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.trigger','data' => ['name' => 'edit-group-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-group-modal']); ?>
                <?php if (isset($component)) { $__componentOriginalf5109f209df079b3a83484e1e6310749 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5109f209df079b3a83484e1e6310749 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::tooltip.index','data' => ['content' => 'Edytuj treść grupy','position' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['content' => 'Edytuj treść grupy','position' => 'right']); ?>
                    <button type="button" @click="$dispatch('open-edit-modal')"
                        class="p-2 hover:bg-gray-800 transition-colors block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5109f209df079b3a83484e1e6310749)): ?>
<?php $attributes = $__attributesOriginalf5109f209df079b3a83484e1e6310749; ?>
<?php unset($__attributesOriginalf5109f209df079b3a83484e1e6310749); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5109f209df079b3a83484e1e6310749)): ?>
<?php $component = $__componentOriginalf5109f209df079b3a83484e1e6310749; ?>
<?php unset($__componentOriginalf5109f209df079b3a83484e1e6310749); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $attributes = $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__attributesOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb)): ?>
<?php $component = $__componentOriginal1db8c57e729d67f7d4103875cf3230cb; ?>
<?php unset($__componentOriginal1db8c57e729d67f7d4103875cf3230cb); ?>
<?php endif; ?>
        <?php $__env->stopPush(); ?>

        <?php if (isset($component)) { $__componentOriginal71513080e04c322fc133e6ffb8c2b9e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal71513080e04c322fc133e6ffb8c2b9e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-bar.edit-modal','data' => ['name' => 'edit-group-modal','title' => 'Edytuj treść grupy','showSuccess' => $saved,'successMessage' => 'Treść grupy została zapisana.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-bar.edit-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-group-modal','title' => 'Edytuj treść grupy','show-success' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($saved),'success-message' => 'Treść grupy została zapisana.']); ?>
            <?php if (isset($component)) { $__componentOriginal5ed719c1971b2710ce6f28e12185de56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5ed719c1971b2710ce6f28e12185de56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rich-editor','data' => ['name' => 'editingContent','value' => $editingContent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rich-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'editingContent','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editingContent)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5ed719c1971b2710ce6f28e12185de56)): ?>
<?php $attributes = $__attributesOriginal5ed719c1971b2710ce6f28e12185de56; ?>
<?php unset($__attributesOriginal5ed719c1971b2710ce6f28e12185de56); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5ed719c1971b2710ce6f28e12185de56)): ?>
<?php $component = $__componentOriginal5ed719c1971b2710ce6f28e12185de56; ?>
<?php unset($__componentOriginal5ed719c1971b2710ce6f28e12185de56); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal71513080e04c322fc133e6ffb8c2b9e3)): ?>
<?php $attributes = $__attributesOriginal71513080e04c322fc133e6ffb8c2b9e3; ?>
<?php unset($__attributesOriginal71513080e04c322fc133e6ffb8c2b9e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71513080e04c322fc133e6ffb8c2b9e3)): ?>
<?php $component = $__componentOriginal71513080e04c322fc133e6ffb8c2b9e3; ?>
<?php unset($__componentOriginal71513080e04c322fc133e6ffb8c2b9e3); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views\livewire/pages/group.blade.php ENDPATH**/ ?>
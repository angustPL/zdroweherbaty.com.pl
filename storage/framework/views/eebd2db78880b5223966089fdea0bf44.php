
<div class="relative transition-all duration-300 cart-icon">
    <a href="<?php echo e(route('cart')); ?>"
        class="flex items-center gap-2 px-3 py-2 text-primary hover:text-primary transition-colors">
        <div class="relative">
            <?php if (isset($component)) { $__componentOriginalba19ebcac9d8345c4eacd2892266930c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba19ebcac9d8345c4eacd2892266930c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::icon.shopping-basket','data' => ['class' => 'w-12 h-12 -my-1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::icon.shopping-basket'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-12 h-12 -my-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba19ebcac9d8345c4eacd2892266930c)): ?>
<?php $attributes = $__attributesOriginalba19ebcac9d8345c4eacd2892266930c; ?>
<?php unset($__attributesOriginalba19ebcac9d8345c4eacd2892266930c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba19ebcac9d8345c4eacd2892266930c)): ?>
<?php $component = $__componentOriginalba19ebcac9d8345c4eacd2892266930c; ?>
<?php unset($__componentOriginalba19ebcac9d8345c4eacd2892266930c); ?>
<?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemCount > 0): ?>
                <?php if (isset($component)) { $__componentOriginal4cc377eda9b63b796b6668ee7832d023 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cc377eda9b63b796b6668ee7832d023 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::badge.index','data' => ['size' => 'sm','variant' => 'solid','color' => 'zinc','class' => 'absolute -bottom-2 -right-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','variant' => 'solid','color' => 'zinc','class' => 'absolute -bottom-2 -right-2']); ?>
                    <?php echo e($itemCount > 99 ? '99+' : $itemCount); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $attributes = $__attributesOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__attributesOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cc377eda9b63b796b6668ee7832d023)): ?>
<?php $component = $__componentOriginal4cc377eda9b63b796b6668ee7832d023; ?>
<?php unset($__componentOriginal4cc377eda9b63b796b6668ee7832d023); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </a>
</div>
<?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views/livewire/components/cart-icon.blade.php ENDPATH**/ ?>
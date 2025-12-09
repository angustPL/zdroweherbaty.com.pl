<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="<?php echo e(route('home')); ?>" class="mx-auto mb-2" wire:navigate>
                <?php if (isset($component)) { $__componentOriginal65eaf09008bea6225251a8ee6f4bd6ab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65eaf09008bea6225251a8ee6f4bd6ab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::image.logo','data' => ['variant' => 'dark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::image.logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'dark']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65eaf09008bea6225251a8ee6f4bd6ab)): ?>
<?php $attributes = $__attributesOriginal65eaf09008bea6225251a8ee6f4bd6ab; ?>
<?php unset($__attributesOriginal65eaf09008bea6225251a8ee6f4bd6ab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65eaf09008bea6225251a8ee6f4bd6ab)): ?>
<?php $component = $__componentOriginal65eaf09008bea6225251a8ee6f4bd6ab; ?>
<?php unset($__componentOriginal65eaf09008bea6225251a8ee6f4bd6ab); ?>
<?php endif; ?>
            </a>
            <div class="flex flex-col gap-6">
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
    <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>

</body>

</html>
<?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views/components/layouts/auth/simple.blade.php ENDPATH**/ ?>
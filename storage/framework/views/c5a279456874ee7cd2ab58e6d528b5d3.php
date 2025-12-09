<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Promotion;

?>

<div x-data @refresh-promotions.window="$wire.call('refreshPromotions')">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('promotion_saved')): ?>
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm text-green-800"><?php echo e(session('promotion_saved')); ?></p>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Lista promocji</h2>
        <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['variant' => 'primary','type' => 'button','wire:off' => true,'xData' => '','@click.stop' => '
                $dispatch(\'edit-promotion\', { id: null });
                $flux.modal(\'promotions-modal\').close();
                setTimeout(() => {
                    $flux.modal(\'promotion-form-modal\').show();
                }, 300);
            ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','type' => 'button','wire:off' => true,'x-data' => '','@click.stop' => '
                $dispatch(\'edit-promotion\', { id: null });
                $flux.modal(\'promotions-modal\').close();
                setTimeout(() => {
                    $flux.modal(\'promotion-form-modal\').show();
                }, 300);
            ']); ?>
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Dodaj promocję
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($promotions)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Brak promocji</h3>
            <p class="mt-1 text-sm text-gray-500">Zacznij od utworzenia nowej promocji.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nazwa
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Typ
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Wartość
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data ważności
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ograniczenia
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Akcje
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promotion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isInactive = !$promotion->is_active || !$promotion->isValid();
                        ?>
                        <tr class="<?php echo e($isInactive ? 'bg-gray-50' : ''); ?>">
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium <?php echo e($isInactive ? 'text-gray-400' : 'text-gray-900'); ?>">
                                <?php echo e($promotion->name); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promotion->code): ?>
                                    <span
                                        class="text-xs <?php echo e($isInactive ? 'text-gray-300' : 'text-gray-500'); ?>">(<?php echo e($promotion->code); ?>)</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm <?php echo e($isInactive ? 'text-gray-400' : 'text-gray-500'); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promotion->type === 'code'): ?>
                                    Kod promocyjny
                                <?php elseif($promotion->type === 'automatic'): ?>
                                    Automatyczna
                                <?php elseif($promotion->type === 'seasonal'): ?>
                                    Sezonowa
                                <?php else: ?>
                                    <?php echo e($promotion->type); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm <?php echo e($isInactive ? 'text-gray-400' : 'text-gray-500'); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promotion->discount_type === 'percentage'): ?>
                                    <?php echo e(number_format($promotion->discount_value, 2)); ?>%
                                <?php elseif($promotion->discount_type === 'fixed'): ?>
                                    <?php echo e(number_format($promotion->discount_value, 2)); ?> zł
                                <?php elseif($promotion->discount_type === 'free_delivery'): ?>
                                    Darmowa dostawa
                                <?php else: ?>
                                    <?php echo e($promotion->discount_value); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm <?php echo e($isInactive ? 'text-gray-400' : 'text-gray-500'); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promotion->valid_to): ?>
                                    <?php echo e($promotion->valid_to->format('Y-m-d')); ?>

                                <?php else: ?>
                                    Bez limitu
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm <?php echo e($isInactive ? 'text-gray-400' : 'text-gray-500'); ?>">
                                <?php
                                    $hasProducts = $promotion->promotionProducts->count() > 0;
                                    $hasGroups = $promotion->promotionGroups->count() > 0;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasProducts): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium <?php echo e($isInactive ? 'bg-blue-50 text-blue-300' : 'bg-blue-100 text-blue-800'); ?>">
                                        Produkty
                                    </span>
                                <?php elseif($hasGroups): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium <?php echo e($isInactive ? 'bg-blue-50 text-blue-300' : 'bg-blue-100 text-blue-800'); ?>">
                                        Grupy
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium <?php echo e($isInactive ? 'bg-gray-50 text-gray-300' : 'bg-gray-100 text-gray-800'); ?>">
                                        Brak
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['size' => 'sm','type' => 'button','wire:off' => true,'xData' => '','class' => ''.e($isInactive ? 'opacity-60' : '').'','@click.stop' => '
                                            $dispatch(\'edit-promotion\', { id: '.e($promotion->id).' });
                                            $flux.modal(\'promotions-modal\').close();
                                            setTimeout(() => {
                                                $flux.modal(\'promotion-form-modal\').show();
                                            }, 300);
                                        ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','type' => 'button','wire:off' => true,'x-data' => '','class' => ''.e($isInactive ? 'opacity-60' : '').'','@click.stop' => '
                                            $dispatch(\'edit-promotion\', { id: '.e($promotion->id).' });
                                            $flux.modal(\'promotions-modal\').close();
                                            setTimeout(() => {
                                                $flux.modal(\'promotion-form-modal\').show();
                                            }, 300);
                                        ']); ?>
                                        Edytuj
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::button.index','data' => ['size' => 'sm','variant' => 'danger','type' => 'button','wire:off' => true,'class' => ''.e($isInactive ? 'opacity-60' : '').'','@click.stop' => 'if(confirm(\'Czy na pewno chcesz usunąć tę promocję?\')) { /* TODO: Implementacja usuwania */ }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','variant' => 'danger','type' => 'button','wire:off' => true,'class' => ''.e($isInactive ? 'opacity-60' : '').'','@click.stop' => 'if(confirm(\'Czy na pewno chcesz usunąć tę promocję?\')) { /* TODO: Implementacja usuwania */ }']); ?>
                                        Usuń
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $attributes = $__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__attributesOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580)): ?>
<?php $component = $__componentOriginalc04b147acd0e65cc1a77f86fb0e81580; ?>
<?php unset($__componentOriginalc04b147acd0e65cc1a77f86fb0e81580); ?>
<?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views\livewire/components/promotions-list.blade.php ENDPATH**/ ?>
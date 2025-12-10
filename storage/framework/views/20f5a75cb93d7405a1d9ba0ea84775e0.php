<?php

use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;

?>




<div x-data @open-edit-modal.window="$wire.call('openEditModal')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Regulamin</h1>
        </div>

        
        <div class="hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8"></h2>
            <p class="text-gray-700 mb-4 mt-6"></p>
        </div>

        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($termsContent): ?>
                <?php echo $termsContent; ?>

            <?php else: ?>
                <p class="text-gray-500">Treść regulaminu nie jest dostępna.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::check() && Auth::user()->hasRole('admin')): ?>
        <?php $__env->startPush('admin-bar-actions'); ?>
            <?php if (isset($component)) { $__componentOriginal1db8c57e729d67f7d4103875cf3230cb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1db8c57e729d67f7d4103875cf3230cb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.trigger','data' => ['name' => 'edit-terms-modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal.trigger'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-terms-modal']); ?>
                <?php if (isset($component)) { $__componentOriginalf5109f209df079b3a83484e1e6310749 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5109f209df079b3a83484e1e6310749 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::tooltip.index','data' => ['content' => 'Edytuj regulamin','position' => 'right']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['content' => 'Edytuj regulamin','position' => 'right']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-bar.edit-modal','data' => ['name' => 'edit-terms-modal','title' => 'Edytuj regulamin','subtitle' => 'Zaktualizuj treść regulaminu sklepu','showSuccess' => $saved,'successMessage' => 'Regulamin został zapisany.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-bar.edit-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-terms-modal','title' => 'Edytuj regulamin','subtitle' => 'Zaktualizuj treść regulaminu sklepu','show-success' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($saved),'success-message' => 'Regulamin został zapisany.']); ?>
            <?php if (isset($component)) { $__componentOriginal5ed719c1971b2710ce6f28e12185de56 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5ed719c1971b2710ce6f28e12185de56 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rich-editor','data' => ['name' => 'editingContent','value' => $editingContent,'wire:input' => '$set(\'saved\', false)','xOn:keydown' => '$wire.set(\'saved\', false)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rich-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'editingContent','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($editingContent),'wire:input' => '$set(\'saved\', false)','x-on:keydown' => '$wire.set(\'saved\', false)']); ?>
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
</div><?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views\livewire/pages/terms.blade.php ENDPATH**/ ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($groupData['children'])): ?>
        <?php
            // Sprawdź czy ta grupa powinna być rozwinięta
            $currentPath = ($parentPath ?? '') . ($parentPath ? '\\' : '') . $groupName;
            $shouldExpand = false;

            // Sprawdź route 'group'
            if (request()->routeIs('group')) {
                $currentGrupa = request()->route('group');
                if ($currentGrupa) {
                    $decodedGrupa = urldecode($currentGrupa);
                    $currentGroupPath = str_replace(config('enova.grupa_url_separator'), '\\', $decodedGrupa);

                    // Sprawdź czy aktualna ścieżka zawiera tę grupę jako rodzic
                    $shouldExpand = str_contains($currentGroupPath, $currentPath . '\\');
                }
            }
            
            // Sprawdź route 'product' - rozwiń grupę jeśli produkt należy do tej grupy lub jej podgrupy
            if (!$shouldExpand && request()->routeIs('product')) {
                $currentProductGroup = view()->shared('currentProductGroup');
                if ($currentProductGroup) {
                    // Sprawdź czy grupa produktu zawiera tę grupę jako rodzic
                    $shouldExpand = str_contains($currentProductGroup, $currentPath . '\\');
                }
            }
        ?>
        <?php if (isset($component)) { $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.group','data' => ['heading' => $groupName,'expandable' => true,'expanded' => $shouldExpand]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($groupName),'expandable' => true,'expanded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shouldExpand)]); ?>
            <?php echo $__env->make('livewire.components.sidebar-group', [
                'groups' => $groupData['children'],
                'parentPath' => ($parentPath ?? '') . ($parentPath ? '\\' : '') . $groupName,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $attributes = $__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__attributesOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4)): ?>
<?php $component = $__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4; ?>
<?php unset($__componentOriginal8b1fe5c87f0876e7c101dbc6fe82a9a4); ?>
<?php endif; ?>
    <?php else: ?>
        <?php
            // Budowanie pełnej ścieżki grupy dla linku
            $groupPath = ($parentPath ?? '') . ($parentPath ? '\\' : '') . $groupName;
            // Konwersja na format URL z użyciem spójnika z konfiguracji
            $urlPath = str_replace('\\', config('enova.grupa_url_separator'), $groupPath);
            // Kodowanie URL dla bezpieczeństwa
            $encodedPath = urlencode($urlPath);

            // Sprawdź czy to jest aktualna grupa
            $isCurrent = false;
            
            // Sprawdź route 'group'
            if (request()->routeIs('group')) {
                $currentGrupa = request()->route('group');
                if ($currentGrupa) {
                    // Porównaj po dekodowaniu dla polskich znaków
                    $decodedCurrent = urldecode($currentGrupa);
                    $isCurrent = $decodedCurrent === $urlPath;
                }
            }
            
            // Sprawdź route 'product' - porównaj grupę produktu z grupą w sidebarze
            if (!$isCurrent && request()->routeIs('product')) {
                $currentProductGroup = view()->shared('currentProductGroup');
                if ($currentProductGroup) {
                    // Porównaj clean_name produktu z grupą w sidebarze
                    // clean_name ma format "Bi fix herbatki owocowe\Herbaty specjalne"
                    // urlPath ma format "Bi fix herbatki owocowe--Herbaty specjalne" (z -- zamiast \)
                    $isCurrent = $currentProductGroup === $groupPath;
                }
            }
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($parentPath)): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($encodedPath)): ?>
                <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['href' => ''.e(route('group', ['group' => $encodedPath])).'','current' => $isCurrent,'icon' => 'x-mark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('group', ['group' => $encodedPath])).'','current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isCurrent),'icon' => 'x-mark']); ?>
                    <?php echo e($groupName); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php else: ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($encodedPath)): ?>
                <?php if (isset($component)) { $__componentOriginalda376aa217444bbd92367ba1444eb3b8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda376aa217444bbd92367ba1444eb3b8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::navlist.item','data' => ['href' => ''.e(route('group', ['group' => $encodedPath])).'','current' => $isCurrent]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::navlist.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('group', ['group' => $encodedPath])).'','current' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isCurrent)]); ?>
                    <?php echo e($groupName); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $attributes = $__attributesOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__attributesOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda376aa217444bbd92367ba1444eb3b8)): ?>
<?php $component = $__componentOriginalda376aa217444bbd92367ba1444eb3b8; ?>
<?php unset($__componentOriginalda376aa217444bbd92367ba1444eb3b8); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\resources\views/livewire/components/sidebar-group.blade.php ENDPATH**/ ?>
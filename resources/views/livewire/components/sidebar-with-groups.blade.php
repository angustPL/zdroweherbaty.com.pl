<?php

use function Livewire\Volt\{state, mount};
use App\Models\Group;

state(['productGroups' => []]);

mount(function () {
    $this->productGroups = Group::getHierarchicalStructure();
});

?>

<div>
    <flux:sidebar sticky stashable
        class="lg:hidden border-e border-zinc-200 bg-white dark:border-zinc-700 dark:bg-white z-30 w-full sidebar-wrap-text">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:navlist variant="outline">
            <flux:navlist.group heading="Menu" class="grid">
                <flux:navlist.item icon="home" :href="route('home')" :current="request()->routeIs('home')">
                    Strona główna
                </flux:navlist.item>
                <flux:navlist.item icon="truck" :href="route('delivery')" :current="request()->routeIs('delivery')">
                    Dostawa
                </flux:navlist.item>
                <flux:navlist.item icon="document-text" :href="route('terms')" :current="request()->routeIs('terms')">
                    Regulamin
                </flux:navlist.item>
                <flux:navlist.item icon="envelope" :href="route('contact')" :current="request()->routeIs('contact')">
                    Kontakt
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.group heading="Kategorie">
                @include('livewire.components.sidebar-group', [
                    'groups' => $productGroups,
                    'parentPath' => '',
                ])
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />


    </flux:sidebar>
</div>

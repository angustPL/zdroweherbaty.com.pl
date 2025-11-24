<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\Group;

state(['productGroups' => []]);

mount(function () {
    // Używa cache'owanej metody (TTL z konfiguracji, domyślnie 24h)
    $this->productGroups = Group::getHierarchicalStructure();
});

?>

<div class="bg-white sidebar-wrap-text">
    <!-- Pole wyszukiwania produktów -->
    <div class="mb-4">
        <form method="GET" action="{{ route('search') }}">
            <flux:input name="q" icon="magnifying-glass" placeholder="Wyszukaj produkty" minlength="2"
                autocomplete="off" />
        </form>
    </div>

    <!-- Lista grup produktów -->
    <flux:navlist variant="outline">
        @include('livewire.components.sidebar-group', [
            'groups' => $productGroups,
            'parentPath' => '',
        ])
    </flux:navlist>
</div>

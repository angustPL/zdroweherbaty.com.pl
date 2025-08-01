<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\Group;

state(['productGroups' => [], 'searchQuery' => '']);

mount(function () {
    $this->productGroups = Group::getHierarchicalStructure();
});

// Przekierowanie do strony wyszukiwarki po wpisaniu 2+ znaków
$searchProducts = function () {
    if (strlen($this->searchQuery) >= 2) {
        return redirect()->route('search', ['query' => $this->searchQuery]);
    }
};

?>

<div class="bg-white sidebar-wrap-text">
    <!-- Pole wyszukiwania produktów -->
    <div class="mb-4">
        <flux:input wire:model.live.debounce.500ms="searchQuery" wire:change="searchProducts" icon="magnifying-glass"
            placeholder="Wyszukaj produkty" clearable />
    </div>

    <!-- Lista grup produktów -->
    <flux:navlist variant="outline">
        @include('livewire.components.sidebar-group', [
            'groups' => $productGroups,
            'parentPath' => '',
        ])
    </flux:navlist>
</div>

<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\Group;

state(['productGroups' => [], 'searchQuery' => '']);

mount(function () {
    $this->productGroups = Group::getHierarchicalStructure();
});

$filteredGroups = computed(function () {
    if (empty($this->searchQuery)) {
        return $this->productGroups;
    }

    $query = strtolower($this->searchQuery);
    $filtered = [];

    foreach ($this->productGroups as $groupName => $groupData) {
        if (str_contains(strtolower($groupName), $query)) {
            $filtered[$groupName] = $groupData;
        } else {
            // Sprawdź dzieci
            $filteredChildren = [];
            foreach ($groupData['children'] ?? [] as $childName => $childData) {
                if (str_contains(strtolower($childName), $query)) {
                    $filteredChildren[$childName] = $childData;
                }
            }
            if (!empty($filteredChildren)) {
                $filtered[$groupName] = array_merge($groupData, ['children' => $filteredChildren]);
            }
        }
    }

    return $filtered;
});

?>

<div class="bg-white sidebar-wrap-text">
    <!-- Pole wyszukiwania -->
    <div class="mb-4">
        <flux:input wire:model.live="searchQuery" icon="magnifying-glass" placeholder="Wyszukaj produkty" clearable />
    </div>

    <!-- Lista grup produktów -->
    <flux:navlist variant="outline">
        @include('livewire.components.sidebar-group', [
            'groups' => $this->filteredGroups,
            'parentPath' => '',
        ])
    </flux:navlist>
</div>

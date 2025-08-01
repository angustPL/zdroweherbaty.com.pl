<?php

use function Livewire\Volt\{layout};

layout('layouts.app');

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Wyszukiwarka produktów
        </h1>
        <p class="text-gray-600">
            Znajdź herbaty, które najlepiej pasują do Twoich potrzeb
        </p>
    </div>

    <livewire:components.search-products />
</div>

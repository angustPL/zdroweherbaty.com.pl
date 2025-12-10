<?php

use function Livewire\Volt\{state, mount, layout};
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;

layout('layouts.app');

// ZABRONIENIE INDEKSOWANIA
SEOMeta::setRobots('noindex, nofollow');
SEOMeta::addMeta('googlebot', 'noindex, nofollow');
SEOMeta::addMeta('bingbot', 'noindex, nofollow');

SEOTools::opengraph()->addProperty('robots', 'noindex, nofollow');
SEOTools::jsonLd()->setType('WebPage');

state(['products' => [], 'groupName' => '', 'groupContent' => null, 'showEditModal' => false, 'editingContent' => '', 'saved' => false]);

// Funkcje edycji
$openEditModal = function () {
    $this->showEditModal = true;
    $this->editingContent = $this->groupContent ? $this->groupContent->content : '';
};

$saveContent = function () {
    if ($this->groupContent) {
        $this->groupContent->update(['content' => $this->editingContent]);
    } else {
        $this->groupContent = \App\Models\Content::create([
            'type' => 'product_group',
            'identifier' => $this->groupName,
            'title' => $this->groupName,
            'content' => $this->editingContent,
            'meta' => [],
            'is_active' => true,
        ]);
    }
    // Wyczyszczenie cache, żeby zmiany były widoczne od razu
    \Illuminate\Support\Facades\Cache::forget('content_product_group_' . md5($this->groupName));

    $this->groupContent = $this->groupContent->fresh();
    $this->saved = true;
};

$closeEditModal = function () {
    $this->showEditModal = false;
    $this->editingContent = $this->groupContent ? $this->groupContent->content : '';
};

mount(function ($group) {
    $this->groupName = urldecode($group);
    $groupPath = str_replace(config('enova.grupa_url_separator'), '\\', $this->groupName);
    $prefix = config('enova.features.product_group_prefix');
    $this->products = \App\Models\Product::getCachedByGroup($prefix . $groupPath . '\\');

    // Pobieranie treści grupy
    $this->groupContent = \App\Models\Content::getForProductGroup($this->groupName);
    $this->editingContent = $this->groupContent ? $this->groupContent->content : '';

    // Dynamiczne meta tagi
    if ($this->groupContent) {
        $metaTitle = isset($this->groupContent->meta['meta_title']) && $this->groupContent->meta['meta_title'] ? $this->groupContent->meta['meta_title'] : $this->groupName . ' - Zdrowe Herbaty BIFIX';

        $metaDescription = isset($this->groupContent->meta['meta_description']) && $this->groupContent->meta['meta_description'] ? $this->groupContent->meta['meta_description'] : 'Przeglądaj herbaty ' . $this->groupName . ' BIFIX. Znajdź herbaty zielone, czarne, owocowe i ziołowe dla całej rodziny.';

        SEOTools::setTitle($metaTitle);
        SEOTools::setDescription($metaDescription);
    }
});

?>

<div x-data @open-edit-modal.window="$wire.call('openEditModal')">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-black mt-1 mb-2">
            {{ $groupContent && isset($groupContent->meta['h1']) && !empty($groupContent->meta['h1']) ? $groupContent->meta['h1'] : $groupName }}
        </h1>
        <p class="text-gray-600">Znaleziono {{ count($products) }} produktów</p>
    </div>

    @if (count($products) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach ($products as $product)
                <livewire:components.product-card :product-id="$product['ID']" :product-name="$product['Nazwa']" :product-price="$product['BruttoValue']"
                    :product-group="$product['Grupa']" :product-weight="$product['MasaBruttoValue']" :has-image-small="$product['HasImageSmall'] ?? false" variant="default"
                    :wire:key="'product-' . $product['ID']" />
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Brak produktów w tej kategorii</p>
        </div>
    @endif

    @if ($groupContent && !empty($groupContent->content))
        <div class="hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8"></h2>
            <p class="text-gray-700 mb-4 mt-6"></p>
        </div>
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed mt-12">
            {!! $groupContent->content !!}
        </div>
    @endif

    @if (auth()->check())
        @push('admin-bar-actions')
            <flux:modal.trigger name="edit-group-modal">
                <flux:tooltip content="Edytuj treść grupy" position="right">
                    <button type="button" @click="$dispatch('open-edit-modal')"
                        class="p-2 hover:bg-gray-800 transition-colors block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                </flux:tooltip>
            </flux:modal.trigger>
        @endpush

        <x-admin-bar.edit-modal name="edit-group-modal" title="Edytuj treść grupy" :show-success="$saved"
            success-message="Treść grupy została zapisana.">
            <x-rich-editor name="editingContent" :value="$editingContent" />
        </x-admin-bar.edit-modal>
    @endif
</div>

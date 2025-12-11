{{-- Logika: app/Livewire/Pages/Regulamin.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;

layout('layouts.app');

// SEO Meta Tags - ZABRONIONE INDEKSOWANIE
SEOTools::setTitle('Polityka prywatności - Zdrowe Herbaty BIFIX');
SEOTools::setDescription('Polityka prywatności sklepu internetowego Zdrowe Herbaty BIFIX.');
// Canonical z konfiguracji

// ZABRONIENIE INDEKSOWANIA
SEOMeta::setRobots('noindex, nofollow');
SEOMeta::addMeta('googlebot', 'noindex, nofollow');
SEOMeta::addMeta('bingbot', 'noindex, nofollow');

// Open Graph - tylko URL
SEOTools::opengraph()->setUrl(url('/polityka-prywatnosci'));
SEOTools::opengraph()->addProperty('robots', 'noindex, nofollow');

// Schema.org
SEOTools::jsonLd()->setType('WebPage');

state(['privacyContent' => null, 'editingContent' => '', 'showEditModal' => false, 'saved' => false, 'privacyModel' => null]);

mount(function () {
    $privacy = Content::getTerms('polityka-prywatnosci');
    $this->privacyModel = $privacy;
    $this->privacyContent = $privacy ? $privacy->content : null;
    $this->editingContent = $this->privacyContent ?? '';
});

$toggleSection = function ($section) {
    if (in_array($section, $this->expandedSections)) {
        $this->expandedSections = array_diff($this->expandedSections, [$section]);
    } else {
        $this->expandedSections[] = $section;
    }
};

$openEditModal = function () {
    $this->editingContent = $this->termsContent ?? '';
    $this->showEditModal = true;
};

$saveContent = function () {
    $this->validate([
        'editingContent' => 'required|string',
    ]);

    $this->privacyModel = Content::updateOrCreate(
        [
            'type' => 'terms',
            'identifier' => 'polityka-prywatnosci',
        ],
        [
            'title' => 'Polityka prywatności',
            'content' => $this->editingContent,
            'is_active' => true,
        ],
    );

    $this->privacyContent = $this->privacyModel->content;
    $this->editingContent = $this->privacyModel->content; // Zaktualizuj również editingContent
    $this->showEditModal = false;

    $this->saved = true;
};

$closeEditModal = function () {
    $this->showEditModal = false;
    $this->editingContent = $this->privacyContent ?? '';
};

// GTM page type
try {
    app('googletagmanager')->set('pageType', 'privacy');
} catch (\Exception $e) {
    // Silent fail - GTM event not critical for functionality
}

?>

<div x-data @open-edit-modal.window="$wire.call('openEditModal')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900">Polityka prywatności</h1>

        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
            @if ($privacyContent)
                {!! $privacyContent !!}
            @else
                <p class="text-gray-500">Treść polityki prywatności nie jest dostępna.</p>
            @endif
        </div>

        {{-- Klasy używane w treści z bazy danych - nie usuwać podczas buildowania --}}
        <div class="hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8"></h2>
            <p class="text-gray-700 mb-4 mt-6"></p>
        </div>
    </div>

    @if (Auth::check())
        @push('admin-bar-actions')
            <flux:modal.trigger name="edit-privacy-modal">
                <flux:tooltip content="Edytuj politykę prywatności" position="right">
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

        <x-admin-bar.edit-modal name="edit-privacy-modal" title="Edytuj politykę prywatności"
            subtitle="Zaktualizuj treść polityki prywatności sklepu" widthClass="md:max-w-[50vw]!" :show-success="$saved"
            success-message="Polityka prywatności została zapisana.">
            <x-rich-editor name="editingContent" :value="$editingContent" wire:input="$set('saved', false)"
                x-on:keydown="$wire.set('saved', false)" />
        </x-admin-bar.edit-modal>
    @endif
</div>

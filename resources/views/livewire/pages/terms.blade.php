{{-- Logika: app/Livewire/Pages/Regulamin.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Content;
use Illuminate\Support\Facades\Auth;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;

layout('layouts.app');

// SEO Meta Tags - ZABRONIONE INDEKSOWANIE
SEOTools::setTitle('Regulamin - Zdrowe Herbaty BIFIX');
SEOTools::setDescription('Regulamin sklepu internetowego Zdrowe Herbaty BIFIX. Zasady sprzedaży, dostawy, płatności i reklamacji herbat BIFIX.');
// Canonical URL jest automatycznie ustawiany z konfiguracji

// ZABRONIENIE INDEKSOWANIA
SEOMeta::setRobots('noindex, nofollow');
SEOMeta::addMeta('googlebot', 'noindex, nofollow');
SEOMeta::addMeta('bingbot', 'noindex, nofollow');

// Open Graph - tylko URL (reszta z domyślnych)
SEOTools::opengraph()->setUrl(url('/regulamin'));

// ZABRONIENIE INDEKSOWANIA W SOCIAL MEDIA
SEOTools::opengraph()->addProperty('robots', 'noindex, nofollow');

// Schema.org JSON-LD - tylko typ (reszta z domyślnych)
SEOTools::jsonLd()->setType('WebPage');

state(['expandedSections' => [], 'termsContent' => null, 'editingContent' => '', 'showEditModal' => false, 'termsModel' => null]);

mount(function () {
    // Pobieranie treści regulaminu z bazy danych
    $terms = Content::getTerms('regulamin');
    $this->termsModel = $terms;
    $this->termsContent = $terms ? $terms->content : null;
    $this->editingContent = $this->termsContent ?? '';
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

    $this->termsModel = Content::updateOrCreate(
        [
            'type' => 'terms',
            'identifier' => 'regulamin',
        ],
        [
            'title' => 'Regulamin',
            'content' => $this->editingContent,
            'is_active' => true,
        ],
    );

    $this->termsContent = $this->termsModel->content;
    $this->editingContent = $this->termsModel->content; // Zaktualizuj również editingContent
    $this->showEditModal = false;

    session()->flash('message', 'Regulamin został zapisany.');
};

$closeEditModal = function () {
    $this->showEditModal = false;
    $this->editingContent = $this->termsContent ?? '';
};

// GTM page type
try {
    app('googletagmanager')->set('pageType', 'terms');
} catch (\Exception $e) {
    // Silent fail - GTM event not critical for functionality
}

?>

<div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Regulamin</h1>
        </div>

        {{-- Klasy używane w treści z bazy danych - nie usuwać podczas buildowania --}}
        <div class="hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8"></h2>
            <p class="text-gray-700 mb-4 mt-6"></p>
        </div>

        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
            @if ($termsContent)
                {!! $termsContent !!}
            @else
                <p class="text-gray-500">Treść regulaminu nie jest dostępna.</p>
            @endif
        </div>
    </div>

    @if (Auth::check())
        @push('admin-bar-actions')
            <flux:modal.trigger name="edit-terms-modal">
                <flux:tooltip content="Edytuj regulamin" position="right">
                    <button type="button" wire:click="openEditModal" class="p-2 hover:bg-gray-800 transition-colors block">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                </flux:tooltip>
            </flux:modal.trigger>
        @endpush

        <flux:modal name="edit-terms-modal" flyout position="left"
            class="md:w-[800px] m-0! rounded-none! h-screen! flex flex-col">
            <form class="flex flex-col h-full">
                <div class="shrink-0 p-6 border-b">
                    <flux:heading size="lg">Edytuj regulamin</flux:heading>
                    <flux:subheading>Zaktualizuj treść regulaminu sklepu</flux:subheading>
                </div>

                <div class="flex-1 p-6">
                    <x-rich-editor name="editingContent" label="Treść regulaminu" :value="$editingContent" />
                </div>

                <div
                    class="shrink-0 flex justify-end space-x-2 rtl:space-x-reverse p-6 border-t bg-white sticky bottom-0">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Anuluj</flux:button>
                    </flux:modal.close>
                    <flux:button type="button" variant="primary" wire:click="saveContent"
                        x-on:click="syncTrixContent()">
                        Zapisz</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>

@if (Auth::check())
    <script>
        function syncTrixContent() {
            const trixEditor = document.querySelector('trix-editor[input="trix-editingContent"]');
            if (trixEditor) {
                const wireId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                if (wireId) {
                    const component = Livewire.find(wireId);
                    if (component) {
                        component.set('editingContent', trixEditor.value);
                    }
                }
            }
        }
    </script>
@endif

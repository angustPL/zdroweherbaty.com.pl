{{-- Logika: app/Livewire/Pages/Regulamin.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Content;
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

state(['expandedSections' => [], 'termsContent' => null]);

mount(function () {
    // Pobieranie treści regulaminu z bazy danych
    $terms = Content::getTerms('regulamin');
    $this->termsContent = $terms ? $terms->content : null;
});

$toggleSection = function ($section) {
    if (in_array($section, $this->expandedSections)) {
        $this->expandedSections = array_diff($this->expandedSections, [$section]);
    } else {
        $this->expandedSections[] = $section;
    }
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
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Regulamin</h1>

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
</div>

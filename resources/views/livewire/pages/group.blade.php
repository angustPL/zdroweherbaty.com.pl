{{-- Logika: app/Livewire/Pages/Group.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;

layout('layouts.app');

state(['products' => [], 'groupName' => '', 'dbPath' => '']);

mount(function ($group) {
    // Włącz logowanie zapytań MySQL (tylko w trybie debug) - NAJWCZEŚNIEJ
    if (config('app.debug')) {
        \Illuminate\Support\Facades\DB::enableQueryLog();
    }

    // Dekodowanie URL
    $decodedGroup = urldecode($group);
    $this->groupName = $decodedGroup; // Statyczna nazwa z URL (fallback)

    // Konwersja z formatu URL na format bazy danych
    $groupPath = str_replace(config('enova.grupa_url_separator'), '\\', $decodedGroup);

    // Dodanie prefixu i końcowego ukośnika dla formatu Enova
    $prefix = config('enova.features.product_group_prefix');
    $this->dbPath = $prefix . $groupPath . '\\';

    // Pobieranie produktów dla danej kategorii z cache'owaniem (TTL z konfiguracji, domyślnie 24h)
    $this->products = Product::getCachedByGroup($this->dbPath);

    // GTM page type
    try {
        app('googletagmanager')->set('pageType', 'category');
    } catch (\Exception $e) {
        // Silent fail - GTM event not critical for functionality
    }
});

?>

@php
    // Pobieranie nazwy kategorii z cache'owaniem (cache'owane w Group::getCachedByPath())
    $categoryName = $groupName;
    try {
        $category = \App\Models\Group::getCachedByPath($dbPath);
        $categoryName = $category ? $category->clean_name : $groupName;
    } catch (\Exception $e) {
        // Fallback do groupName
    }

    $startTime = microtime(true);

    // Pobieranie treści SEO z bazy danych - cache'owane w Content::getForProductGroup()
    // Używamy clean_name z Group (jeśli dostępne) zamiast groupName z URL, aby uniknąć wielu zapytań
    $identifierForContent = $category ? $category->clean_name : $groupName;
    $groupContent = \App\Models\Content::getForProductGroup($identifierForContent);

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) * 1000; // w milisekundach

// Aktualizacja SEO Meta Tags - użyj danych z bazy jeśli są dostępne
if ($groupContent) {
    $metaTitle =
        isset($groupContent->meta['meta_title']) && $groupContent->meta['meta_title']
            ? $groupContent->meta['meta_title']
            : $categoryName . ' - Zdrowe Herbaty BIFIX';

    $metaDescription =
        isset($groupContent->meta['meta_description']) && $groupContent->meta['meta_description']
            ? $groupContent->meta['meta_description']
            : 'Przeglądaj herbaty ' .
                $categoryName .
                ' BIFIX. Znajdź herbaty zielone, czarne, owocowe i ziołowe dla całej rodziny.';

    \Artesaos\SEOTools\Facades\SEOTools::setTitle($metaTitle);
    \Artesaos\SEOTools\Facades\SEOTools::setDescription($metaDescription);
}

// Pobierz logi zapytań MySQL (tylko w trybie debug)
$queryLog = [];
if (config('app.debug')) {
        $queryLog = \Illuminate\Support\Facades\DB::getQueryLog();
    }
@endphp

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-black mt-1 mb-2">
            {{ $groupContent && isset($groupContent->meta['h1']) && $groupContent->meta['h1'] ? $groupContent->meta['h1'] : $categoryName }}
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

    {{-- Treść SEO z bazy danych (wyświetlana po produktach, jak w starym systemie) --}}
    @if ($groupContent && !empty($groupContent->content))
        {{-- Klasy używane w treści z bazy danych - nie usuwać podczas buildowania --}}
        <div class="hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 mt-8"></h2>
            <p class="text-gray-700 mb-4 mt-6"></p>
        </div>
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed mt-12">
            {!! $groupContent->content !!}
        </div>
    @endif

    {{-- Debug: wyświetl informacje o zapytaniach MySQL (tylko w trybie debug) --}}
    @if (config('app.debug'))
        <div class="mt-4 p-4 bg-gray-100 text-xs font-mono">
            <p><strong>🔍 MySQL Query Debug:</strong></p>
            <p>Execution time: <strong>{{ number_format($executionTime, 2) }} ms</strong></p>
            <p>Total queries: <strong>{{ count($queryLog) }}</strong></p>
            @if (count($queryLog) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer font-semibold">Zobacz zapytania ({{ count($queryLog) }})</summary>
                    <div class="mt-2 space-y-2">
                        @foreach ($queryLog as $index => $query)
                            <div class="p-2 bg-white border border-gray-300 rounded">
                                <p class="font-semibold text-red-600">Query #{{ $index + 1 }}
                                    ({{ number_format($query['time'], 2) }} ms)
                                    :</p>
                                <pre class="text-xs overflow-x-auto">{{ $query['query'] }}</pre>
                                @if (!empty($query['bindings']))
                                    <p class="text-xs text-gray-600 mt-1">Bindings:
                                        {{ json_encode($query['bindings']) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </details>
            @else
                <p class="mt-2 text-yellow-600">⚠️ Brak zapytań MySQL - wszystko z cache!</p>
            @endif
        </div>
    @endif
</div>

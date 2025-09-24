{{-- Logika: app/Livewire/Pages/Group.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;
use App\Models\Group;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\JsonLd;

layout('layouts.app');

// SEO Meta Tags - canonical URL jest automatycznie ustawiany z konfiguracji

// Open Graph - URL jest automatycznie ustawiany z konfiguracji

// Schema.org JSON-LD - ustawiamy typ przed mount()
JsonLd::setType('CollectionPage');

state(['group' => '', 'products' => [], 'categoryName' => '']);

mount(function ($group) {
    $this->group = $group;

    // Dekodowanie URL
    $decodedGroup = urldecode($group);

    // Konwersja z formatu URL na format bazy danych
    $groupPath = str_replace(config('enova.grupa_url_separator'), '\\', $decodedGroup);

    // Dodanie prefixu i końcowego ukośnika dla formatu Enova
    $prefix = config('enova.features.product_group_prefix');
    $dbPath = $prefix . $groupPath . '\\';

    // Pobieranie produktów dla danej kategorii z nazwą i ceną
    $this->products = Product::with(['productNameFeature', 'price', 'group'])
        ->whereHas('group', function ($query) use ($dbPath) {
            $query->where('Data', $dbPath);
        })
        ->get()
        ->map(function ($product) {
            return $product->toDisplayArray();
        });

    // Pobieranie nazwy kategorii
    $category = Group::where('Data', $dbPath)->first();
    $this->categoryName = $category ? $category->clean_name : $decodedGroup;

    // Aktualizacja SEO Meta Tags z nazwą kategorii
    SEOTools::setTitle($this->categoryName . ' - Zdrowe Herbaty BIFIX');
    SEOTools::setDescription('Przeglądaj herbaty ' . $this->categoryName . ' BIFIX. Znajdź herbaty zielone, czarne, owocowe i ziołowe dla całej rodziny.');

    // Aktualizacja Open Graph
    SEOTools::opengraph()->setTitle($this->categoryName . ' - Zdrowe Herbaty BIFIX');
    SEOTools::opengraph()->setDescription('Przeglądaj herbaty ' . $this->categoryName . ' BIFIX. Znajdź herbaty zielone, czarne, owocowe i ziołowe dla całej rodziny.');

    // Aktualizacja Twitter
    SEOTools::twitter()->setType('summary_large_image');
    SEOTools::twitter()->setTitle($this->categoryName . ' - Zdrowe Herbaty BIFIX');
    SEOTools::twitter()->setDescription('Przeglądaj herbaty ' . $this->categoryName . ' BIFIX. Znajdź herbaty zielone, czarne, owocowe i ziołowe dla całej rodziny.');

    // Aktualizacja Schema.org JSON-LD
    JsonLd::addValue('name', $this->categoryName . ' - Zdrowe Herbaty BIFIX')->addValue('description', 'Przeglądaj herbaty ' . $this->categoryName . ' BIFIX');

    // GTM view_item_list event
    if ($this->products->count() > 0) {
        try {
            // Set page type
            app('googletagmanager')->set('pageType', 'category');

            $items = [];
            foreach ($this->products as $product) {
                $items[] = [
                    'item_id' => $product['ID'],
                    'item_name' => $product['Nazwa'],
                    'price' => $product['BruttoValue'],
                    'currency' => 'PLN',
                    'item_category' => $this->categoryName,
                    'item_list_name' => $this->categoryName,
                    'item_list_id' => $this->group,
                ];
            }

            app('googletagmanager')->set([
                'event' => 'view_item_list',
                'ecommerce' => [
                    'items' => $items,
                    'item_list_name' => $this->categoryName,
                    'item_list_id' => $this->group,
                ],
            ]);
        } catch (\Exception $e) {
            // Silent fail - GTM event not critical for functionality
        }
    }
});

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-black mt-1 mb-2">{{ $categoryName }}</h1>
        <p class="text-gray-600">Znaleziono {{ $products->count() }} produktów</p>
    </div>

    @if ($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <livewire:components.product-card :product="$product" variant="default" />
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Brak produktów w tej kategorii</p>
        </div>
    @endif

    <!-- Schema.org JSON-LD -->
    {!! JsonLd::generate() !!}
</div>

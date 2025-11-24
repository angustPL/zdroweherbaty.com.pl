{{-- Logika: app/Livewire/Pages/Towar.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\JsonLd;

layout('layouts.app');

// SEO Meta Tags - tylko canonical (reszta z domyślnych)
app('seotools')->setCanonical(url()->current());

// Open Graph - tylko typ (reszta z domyślnych, w tym URL)
app('seotools')->opengraph()->setType('product');

// Schema.org JSON-LD - tylko typ (reszta będzie ustawiona w mount())
app('seotools.json-ld')->setType('Product');

state(['product' => null, 'productId' => null]);

mount(function ($id, $name = null) {
    $this->productId = $id;

    // Załaduj produkt z cache'owaniem (TTL z konfiguracji, domyślnie 24h)
    $productData = Product::getCachedById($this->productId);

    if ($productData) {
        $this->product = $productData;

        // Aktualizacja SEO Meta Tags z danymi produktu
        SEOTools::setTitle($this->product['Nazwa'] . ' - Zdrowe Herbaty BIFIX');
        SEOTools::setDescription('Sprawdź ' . $this->product['Nazwa'] . ' BIFIX. Cena: ' . Number::currency($this->product['BruttoValue'], 'PLN', 'pl_PL') . '. ' . Str::limit($this->product['Opis'] ?? '', 150));

        // Canonical URL - generujemy zgodnie z routingiem
        SEOTools::setCanonical(route('product', [$this->productId, Str::slug($this->product['Nazwa'])]));

        // Aktualizacja Open Graph
        SEOTools::opengraph()->setTitle($this->product['Nazwa'] . ' - Zdrowe Herbaty BIFIX');
        SEOTools::opengraph()->setDescription('Sprawdź ' . $this->product['Nazwa'] . ' BIFIX. Cena: ' . Number::currency($this->product['BruttoValue'], 'PLN', 'pl_PL') . '. ' . Str::limit($this->product['Opis'] ?? '', 150));
        SEOTools::opengraph()->setType('product');
        SEOTools::opengraph()->setSiteName('Zdrowe Herbaty BIFIX');

        // Ustawienie zdjęcia produktu jako Open Graph image
        if (!empty($this->product['ID'])) {
            $imageUrl = Storage::disk('public')->url('img/towary/' . $this->product['ID'] . '_800x600.jpg');
            SEOTools::opengraph()->addImage($imageUrl);
        }

        // Aktualizacja Twitter
        SEOTools::twitter()->setType('summary_large_image');
        SEOTools::twitter()->setTitle($this->product['Nazwa'] . ' - Zdrowe Herbaty BIFIX');
        SEOTools::twitter()->setDescription('Sprawdź ' . $this->product['Nazwa'] . ' BIFIX. Cena: ' . Number::currency($this->product['BruttoValue'] ?? 0, 'PLN', 'pl_PL'));

        // Ustawienie zdjęcia produktu jako Twitter image
        if (!empty($this->product['ID'])) {
            $imageUrl = Storage::disk('public')->url('img/towary/' . $this->product['ID'] . '_800x600.jpg');
            SEOTools::twitter()->addImage($imageUrl);
        }

        // Aktualizacja Schema.org JSON-LD
        JsonLd::setType('Product')
            ->addValue('name', $this->product['Nazwa'])
            ->addValue('description', Str::limit($this->product['Opis'] ?? '', 200))
            ->addValue('brand', 'BIFIX')
            ->addValue('category', $this->product['Grupa'] ?? 'Herbata')
            ->addValue('image', Storage::disk('public')->url('img/towary/' . $this->product['ID'] . '_800x600.jpg'))
            ->addValue('offers', [
                '@type' => 'Offer',
                'price' => $this->product['BruttoValue'],
                'priceCurrency' => 'PLN',
                'availability' => 'https://schema.org/InStock',
            ]);
    } else {
        $this->product = null;
    }

    // Sprawdź czy nazwa w URL zgadza się z nazwą w bazie
    if ($this->product) {
        $correctSlug = Str::slug($this->product['Nazwa']);

        // Jeśli nazwa jest nieprawidłowa lub brak nazwy, przekieruj na prawidłowy URL
        if ($name !== $correctSlug) {
            return redirect()->route('product', [$id, $correctSlug]);
        }
    }

    // GTM page type and view_item event
    try {
        app('googletagmanager')->set('pageType', 'product');
        app('googletagmanager')->set([
            'event' => 'view_item',
            'ecommerce' => [
                'items' => [
                    [
                        'item_id' => $this->product['ID'],
                        'item_name' => $this->product['Nazwa'],
                        'price' => $this->product['BruttoValue'],
                        'currency' => 'PLN',
                    ],
                ],
            ],
        ]);
    } catch (\Exception $e) {
        // Silent fail - GTM event not critical for functionality
    }
});

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ $product['Nazwa'] ?? 'Produkt' }}
        </h1>
        <p class="text-gray-600">{{ $product['Grupa'] ?? 'Kategoria' }}</p>
    </div>

    {{-- Debug danych produktu --}}
    {{-- @php(dd($product)) --}}

    @if ($product)
        <div class="bg-white rounded-lg shadow p-6 mb-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Zdjęcie produktu --}}
                <div class="text-center">
                    <img src="{{ Storage::disk('public')->exists('img/towary/' . $product['ID'] . '_800x600.jpg') ? Storage::disk('public')->url('img/towary/' . $product['ID'] . '_800x600.jpg') : asset('img/towary/placeholder.jpg') }}"
                        alt="{{ $product['Nazwa'] }}" class="w-full max-w-md mx-auto rounded-lg">
                </div>

                {{-- Informacje o produkcie --}}
                <div class="space-y-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary mb-4">
                            {{ Number::currency($product['BruttoValue'], 'PLN', 'pl_PL') }}
                        </div>

                        <div>
                            <livewire:components.add-to-cart-button :product-id="$product['ID']" :product-name="$product['Nazwa']"
                                :price="$product['BruttoValue']" :image="$product['ID'] . '_200x120.jpg'" />
                        </div>
                    </div>

                    <div class="text-gray-600 leading-relaxed product-description">
                        {!! Str::of($product['Opis'] ?? 'Brak opisu produktu')->markdown() !!}
                    </div>
                    <style>
                        .product-description p {
                            margin-bottom: 1rem !important;
                        }
                    </style>
                </div>
            </div>
        </div>

        {{-- Podobne produkty --}}
        @if (\App\Livewire\Components\SimilarProducts::hasSimilarProducts($product['ID'], $product['Nazwa']))
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Podobne produkty</h2>
                <livewire:components.similar-products :product-id="$product['ID']" :product-name="$product['Nazwa']" />
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Produkt nie został znaleziony</p>
        </div>
    @endif

    <!-- Schema.org JSON-LD -->
    {!! JsonLd::generate() !!}
</div>

{{-- Logika: app/Livewire/Pages/Towar.php --}}
<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
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
            $imageUrl = asset('img/towary/' . $this->product['ID'] . '_800x600.jpg');
            SEOTools::opengraph()->addImage($imageUrl);
        }

        // Aktualizacja Twitter
        SEOTools::twitter()->setType('summary_large_image');
        SEOTools::twitter()->setTitle($this->product['Nazwa'] . ' - Zdrowe Herbaty BIFIX');
        SEOTools::twitter()->setDescription('Sprawdź ' . $this->product['Nazwa'] . ' BIFIX. Cena: ' . Number::currency($this->product['BruttoValue'] ?? 0, 'PLN', 'pl_PL'));

        // Ustawienie zdjęcia produktu jako Twitter image
        if (!empty($this->product['ID'])) {
            $imageUrl = asset('img/towary/' . $this->product['ID'] . '_800x600.jpg');
            SEOTools::twitter()->addImage($imageUrl);
        }

        // Aktualizacja Schema.org JSON-LD
        JsonLd::setType('Product')
            ->addValue('name', $this->product['Nazwa'])
            ->addValue('description', Str::limit($this->product['Opis'] ?? '', 200))
            ->addValue('brand', 'BIFIX')
            ->addValue('category', $this->product['Grupa'] ?? 'Herbata')
            ->addValue('image', asset('img/towary/' . $this->product['ID'] . '_800x600.jpg'))
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
    @php
        // Przekaż grupę produktu do view share, aby sidebar mógł ją użyć do zaznaczenia
        $groupUrl = null;
        if ($product && isset($product['Grupa'])) {
            view()->share('currentProductGroup', $product['Grupa']);

            // Przygotuj URL do strony grupy
            // Grupa ma format "Bi fix herbatki owocowe\Herbaty specjalne" (clean_name)
            // Konwertuj na format URL: zamień \ na separator z konfiguracji i zakoduj
            $groupPath = $product['Grupa'];
            $urlPath = str_replace('\\', config('enova.grupa_url_separator'), $groupPath);
            $groupUrl = route('group', ['group' => urlencode($urlPath)]);
        }

    @endphp
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ $product['Nazwa'] ?? 'Produkt' }}
        </h1>
    </div>


    @if ($product)
        <div class="bg-white rounded-lg shadow p-6 mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Lewa kolumna: Zdjęcie + Opis --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Zdjęcie produktu --}}
                    <div class="text-center">
                        <img src="{{ $product['HasImageLarge'] ?? false ? asset('img/towary/' . $product['ID'] . '_800x600.jpg') : asset('img/towary/placeholder.jpg') }}"
                            alt="{{ $product['Nazwa'] }}"
                            class="w-full max-w-full max-h-[50vh] object-contain mx-auto rounded-lg">
                    </div>

                    {{-- Opis produktu --}}
                    <div class="text-gray-600 leading-relaxed product-description">
                        {!! Str::of($product['Opis'] ?? 'Brak opisu produktu')->markdown() !!}
                    </div>
                    <style>
                        .product-description p {
                            margin-bottom: 1rem !important;
                        }
                    </style>
                </div>

                {{-- Prawa kolumna: Sticky z ceną, przyciskiem i grupą --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-20">
                        {{-- Podlinkowana nazwa grupy --}}
                        @if ($groupUrl && isset($product['Grupa']))
                            <div class="mb-4 pb-4 border-b">
                                <p class="text-sm text-gray-600 mb-1">Kategoria:</p>
                                <a href="{{ $groupUrl }}"
                                    class="text-primary hover:text-primary-dark hover:underline transition-colors font-medium">
                                    {{ $product['Grupa'] }}
                                </a>
                            </div>
                        @endif

                        {{-- Cena --}}
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary mb-4">
                                {{ Number::currency($product['BruttoValue'], 'PLN', 'pl_PL') }}
                            </div>

                            {{-- Przycisk dodawania do koszyka --}}
                            <div>
                                <livewire:components.add-to-cart-button :product-id="$product['ID']" :product-name="$product['Nazwa']"
                                    :price="$product['BruttoValue']" :image="$product['ID'] . '_200x120.jpg'" :weight="$product['MasaBruttoValue'] ?? 0" :group-clean-name="$product['Grupa'] ?? null" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Podobne produkty - lazy loading --}}
        <div class="mt-12">
            <livewire:components.similar-products :product-id="$product['ID']" :product-name="$product['Nazwa']" lazy />
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Produkt nie został znaleziony</p>
        </div>
    @endif

    @if (Auth::check() && Auth::user()->hasRole('admin') && $product)
        @push('admin-bar-actions')
            <flux:modal.trigger name="confirm-clear-product-cache">
                <flux:tooltip content="Odśwież cache produktu" position="right">
                    <button type="button" class="p-2 hover:bg-gray-800 transition-colors block cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                </flux:tooltip>
            </flux:modal.trigger>
        @endpush

        <flux:modal name="confirm-clear-product-cache" focusable class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Odświeżyć cache produktu?</flux:heading>
                    <flux:subheading>Czy na pewno chcesz odświeżyć cache tego produktu? Cache zostanie usunięty i
                        odświeżony z Enova.</flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse pt-4 border-t">
                    <flux:modal.close>
                        <flux:button variant="ghost">Anuluj</flux:button>
                    </flux:modal.close>
                    <div x-data="{ loading: false }">
                        <flux:button variant="primary" x-bind:disabled="loading"
                            x-on:click.prevent="
                                loading = true;
                                const url = '/cache/clear/product/{{ $productId }}';

                                fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content')
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        window.location.reload();
                                    } else {
                                        $dispatch('close-modal', 'confirm-clear-product-cache');
                                        setTimeout(() => {
                                            $dispatch('open-modal', 'cache-refresh-error');
                                            setTimeout(() => {
                                                const errorMsg = document.getElementById('cache-error-message');
                                                if (errorMsg) {
                                                    errorMsg.textContent = data.error || data.message || 'Nie udało się odświeżyć cache. Brak połączenia z Enova.';
                                                }
                                            }, 100);
                                        }, 300);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    $dispatch('close-modal', 'confirm-clear-product-cache');
                                    setTimeout(() => {
                                        $dispatch('open-modal', 'cache-refresh-error');
                                        setTimeout(() => {
                                            const errorMsg = document.getElementById('cache-error-message');
                                            if (errorMsg) {
                                                errorMsg.textContent = 'Nie udało się odświeżyć cache. Brak połączenia z Enova.';
                                            }
                                        }, 100);
                                    }, 300);
                                })
                                .finally(() => {
                                    loading = false;
                                });
                            ">
                            <span x-show="!loading">Odśwież cache</span>
                            <span x-show="loading">Odświeżanie...</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="cache-refresh-error" focusable class="max-w-md">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Błąd odświeżania cache</flux:heading>
                    <flux:subheading>
                        <span id="cache-error-message">Nie udało się odświeżyć cache produktu.</span>
                    </flux:subheading>
                </div>

                <div class="flex justify-end space-x-2 rtl:space-x-reverse pt-4 border-t">
                    <flux:modal.close>
                        <flux:button variant="primary">Zamknij</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Schema.org JSON-LD -->
    {!! JsonLd::generate() !!}
</div>

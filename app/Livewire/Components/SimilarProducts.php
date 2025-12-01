<?php

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Component;

class SimilarProducts extends Component
{
    public $productId;
    public $productName;
    public $similarProducts = [];

    public function mount($productId, $productName)
    {
        $this->productId = $productId;
        $this->productName = $productName;
        // Ładuj podobne produkty przy pierwszym renderowaniu (komponent jest lazy)
        $this->loadSimilarProducts();
    }

    public function loadSimilarProducts()
    {
        try {
            // Wyszukaj podobne produkty używając Algolia Scout
            $similarProducts = Product::search($this->productName)
                ->take(10) // Pobierz więcej wyników
                ->get();

            // Filtruj w PHP - wyklucz aktualny produkt
            $similarProducts = $similarProducts->filter(function ($product) {
                return $product->ID != $this->productId;
            })->take(3);

            // Mapuj produkty przez toDisplayArray() żeby ceny były dostępne
            $this->similarProducts = $similarProducts->map(function ($product) {
                return $product->toDisplayArray();
            })->toArray();
        } catch (\Exception $e) {
            // Fallback: użyj cache wszystkich produktów i filtruj w PHP
            \Log::info('Algolia search failed, using cache fallback: ' . $e->getMessage());

            try {
                // Pobierz wszystkie produkty z cache
                $allProducts = Product::getCachedAll();
                
                // Filtruj w PHP - wyklucz aktualny produkt i znajdź podobne
                $similarProducts = collect($allProducts)
                    ->filter(function ($product) {
                        return $product['ID'] != $this->productId
                            && (
                                stripos($product['Nazwa'] ?? '', $this->productName) !== false
                                || stripos($product['Opis'] ?? '', $this->productName) !== false
                            );
                    })
                    ->take(3)
                    ->values()
                    ->toArray();

                $this->similarProducts = $similarProducts;
            } catch (\Exception $cacheException) {
                // Jeśli cache też nie działa, zwróć pustą tablicę
                \Log::warning('Cache fallback also failed: ' . $cacheException->getMessage());
                $this->similarProducts = [];
            }
        }
    }

    public function render()
    {
        return view('livewire.components.similar-products');
    }

    /**
     * Sprawdza czy są dostępne podobne produkty (szybka wersja bez pełnego ładowania).
     * 
     * @param int $productId ID produktu
     * @param string $productName Nazwa produktu
     * @return bool
     */
    public static function hasSimilarProducts(int $productId, string $productName): bool
    {
        // Zawsze zwracaj true - podobne produkty będą ładowane lazy
        // To pozwala uniknąć synchronicznego pobierania wszystkich produktów
        return true;
    }
}

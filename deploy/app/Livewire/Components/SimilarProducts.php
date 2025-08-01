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
            // Fallback: użyj SQL jeśli Algolia nie działa
            \Log::info('Algolia search failed, using SQL fallback: ' . $e->getMessage());

            $similarProducts = Product::query()
                ->where('ID', '!=', $this->productId)
                ->where(function ($query) {
                    $query->where('Nazwa', 'like', '%' . $this->productName . '%')
                        ->orWhere('Opis', 'like', '%' . $this->productName . '%');
                })
                ->with(['group', 'price', 'productNameFeature'])
                ->take(3)
                ->get();

            $this->similarProducts = $similarProducts->map(function ($product) {
                return $product->toDisplayArray();
            })->toArray();
        }
    }

    public function render()
    {
        return view('livewire.components.similar-products');
    }
}

<?php

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class SearchProducts extends Component
{
    use WithPagination;

    public $query = '';
    public $perPage = 12;

    protected $queryString = [
        'query' => ['except' => ''],
    ];

    public function updatedQuery()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Wyszukiwanie przez Algolia Scout
        $products = Product::search($this->query)
            ->paginate($this->perPage);

        // Mapuj produkty przez toDisplayArray() żeby ceny były dostępne
        $products->getCollection()->transform(function ($product) {
            return $product->toDisplayArray();
        });

        return view('livewire.components.search-products', [
            'products' => $products,
        ]);
    }
}

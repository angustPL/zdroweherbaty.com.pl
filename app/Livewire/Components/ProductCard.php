<?php

namespace App\Livewire\Components;

use Livewire\Component;

class ProductCard extends Component
{
    public $productId;
    public $productName;
    public $productPrice;
    public $productGroup;
    public $productWeight;
    public $variant = 'default'; // 'default', 'featured', 'compact'
    public $showAddToCart = true;
    public $showPrice = true;
    public $showImage = true;

    public function mount(
        $productId,
        $productName,
        $productPrice,
        $productGroup = null,
        $productWeight = 0,
        $variant = 'default',
        $showAddToCart = true,
        $showPrice = true,
        $showImage = true
    ) {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->productPrice = $productPrice;
        $this->productGroup = $productGroup;
        $this->productWeight = $productWeight;
        $this->variant = $variant;
        $this->showAddToCart = $showAddToCart;
        $this->showPrice = $showPrice;
        $this->showImage = $showImage;
    }

    public function render()
    {
        return view('livewire.components.product-card');
    }
}

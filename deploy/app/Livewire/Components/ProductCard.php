<?php

namespace App\Livewire\Components;

use Livewire\Component;

class ProductCard extends Component
{
    public $product;
    public $variant = 'default'; // 'default', 'featured', 'compact'
    public $showAddToCart = true;
    public $showPrice = true;
    public $showImage = true;

    public function mount(
        $product,
        $variant = 'default',
        $showAddToCart = true,
        $showPrice = true,
        $showImage = true
    ) {
        $this->product = $product;
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

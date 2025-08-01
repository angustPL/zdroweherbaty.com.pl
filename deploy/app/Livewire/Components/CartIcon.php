<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Services\CartService;

class CartIcon extends Component
{
    public $itemCount = 0;

    protected $listeners = [
        'cart-updated' => 'loadCart'
    ];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getCart();
        $this->itemCount = $cart['item_count'] ?? 0;
    }

    public function render()
    {
        return view('livewire.components.cart-icon');
    }
}

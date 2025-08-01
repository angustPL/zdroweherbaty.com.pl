<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Services\CartService;

class AddToCartButton extends Component
{
    public $productId;
    public $productName;
    public $price;
    public $image;
    public $isInCart = false;
    public $isLoading = false;

    public function mount($productId, $productName, $price, $image)
    {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->price = $price;
        $this->image = $image;

        $this->checkIfInCart();
    }

    public function checkIfInCart()
    {
        $cartService = app(CartService::class);
        $this->isInCart = $cartService->isProductInCart($this->productId);
    }

    public function addToCart()
    {
        $this->isLoading = true;

        try {
            $cartService = app(CartService::class);
            $cartService->addToCart($this->productId, $this->productName, $this->price, $this->image);

            $this->isInCart = true;

            // Emituj event do odświeżenia CartIcon
            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Produkt dodany do koszyka!',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Błąd podczas dodawania do koszyka',
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.components.add-to-cart-button');
    }
}

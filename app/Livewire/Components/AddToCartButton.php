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

            // GTM add_to_cart event
            try {
                \Log::info('Próba wysłania GTM add_to_cart event');

                GoogleTagManager::set([
                    'event' => 'add_to_cart',
                    'ecommerce' => [
                        'items' => [[
                            'item_id' => $this->productId,
                            'item_name' => $this->productName,
                            'price' => $this->price,
                            'currency' => 'PLN',
                            'quantity' => 1
                        ]]
                    ]
                ]);

                \Log::info('GTM add_to_cart event wysłany');
            } catch (\Exception $e) {
                \Log::error('GTM add_to_cart error: ' . $e->getMessage());
            }

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

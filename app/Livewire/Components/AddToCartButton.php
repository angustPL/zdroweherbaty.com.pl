<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Services\CartService;
use App\Models\Product;

class AddToCartButton extends Component
{
    public $productId;
    public $productName;
    public $price;
    public $image;
    public $weight = 0;
    public $isInCart = false;
    public $isLoading = false;

    public function mount($productId, $productName, $price, $image)
    {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->price = $price;
        $this->image = $image;

        // Pobierz wagę produktu z bazy danych
        $product = Product::find($productId);
        if ($product) {
            // Waga w kg (tak jak w bazie danych)
            $this->weight = $product->MasaBruttoValue ?? 0;
        }

        $this->checkIfInCart();
    }

    public function checkIfInCart()
    {
        $cartService = app(CartService::class);
        $this->isInCart = $cartService->isProductInCart($this->productId);
    }

    public function addToCart()
    {
        // Sprawdź czy produkt już jest w koszyku
        if ($this->isInCart) {
            return;
        }

        $this->isLoading = true;

        try {
            $cartService = app(CartService::class);
            $cartService->addToCart($this->productId, $this->productName, $this->price, $this->image, 1, $this->weight);

            $this->isInCart = true;

            // GTM add_to_cart event
            try {
                app('googletagmanager')->set([
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
            } catch (\Exception $e) {
                // Silent fail - GTM event not critical for functionality
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

    public function getSnapshotData()
    {
        return [
            'productId' => $this->productId,
            'productName' => $this->productName,
            'price' => $this->price,
            'image' => $this->image,
            'weight' => $this->weight,
            'isInCart' => $this->isInCart,
            'isLoading' => $this->isLoading,
        ];
    }

    public function render()
    {
        return view('livewire.components.add-to-cart-button');
    }
}

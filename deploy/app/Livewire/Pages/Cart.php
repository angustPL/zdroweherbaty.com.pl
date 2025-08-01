<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Services\CartService;

class Cart extends Component
{
    public $cart = [];

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
        $this->cart = $cartService->getCart();
    }

    public function updateQuantity($productId, $quantity)
    {
        try {
            // Debugowanie
            \Log::info('updateQuantity called:', ['productId' => $productId, 'quantity' => $quantity]);

            $cartService = app(CartService::class);
            $cartService->updateQuantity($productId, $quantity);

            // Emituj event do odświeżenia ikony koszyka
            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Koszyk zaktualizowany',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in updateQuantity:', ['error' => $e->getMessage()]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Błąd podczas aktualizacji koszyka',
            ]);
        }
    }

    public function removeFromCart($productId)
    {
        try {
            $cartService = app(CartService::class);
            $cartService->removeFromCart($productId);

            // Emituj event do odświeżenia ikony koszyka
            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Produkt usunięty z koszyka',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Błąd podczas usuwania z koszyka',
            ]);
        }
    }

    public function clearCart()
    {
        try {
            $cartService = app(CartService::class);
            $cartService->clearCart();

            // Emituj event do odświeżenia ikony koszyka
            $this->dispatch('cart-updated');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Koszyk został wyczyszczony',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Błąd podczas czyszczenia koszyka',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.pages.cart')->layout('layouts.app');
    }
}

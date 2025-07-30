<?php

use function Livewire\Volt\{state, mount};
use App\Services\CartService;

state([
    'productId' => 0,
    'productName' => '',
    'price' => 0,
    'image' => '',
    'isLoading' => false,
    'isInCart' => false,
]);

mount(function ($productId, $productName, $price, $image) {
    $this->productId = $productId;
    $this->productName = $productName;
    $this->price = $price;
    $this->image = $image;

    $this->checkIfInCart();
});

$checkIfInCart = function () {
    $cartService = app(CartService::class);
    $this->isInCart = $cartService->isProductInCart($this->productId);
};

$addToCart = function () {
    $this->isLoading = true;

    try {
        $cartService = app(CartService::class);
        $cartService->addToCart($this->productId, $this->productName, $this->price, $this->image);

        $this->isInCart = true;

        // Emituj globalny event
        $this->dispatch('cart-updated');

        // Debug - sprawdź czy event jest emitowany
        \Log::info('Cart updated event dispatched from add-to-cart-button');

        // Pokaż powiadomienie
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
};

?>

<div x-data="{ isHovered: false }" @mouseenter="isHovered = true" @mouseleave="isHovered = false">
    @if ($this->isInCart)
        <flux:button href="{{ route('koszyk') }}" class="flex w-full items-center justify-center">
            <span wire:loading.remove x-text="isHovered ? 'Do koszyka' : 'W koszyku'">
            </span>
            <span wire:loading>Dodawanie...</span>
            <svg x-show="isHovered" class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
        </flux:button>
    @else
        <flux:button wire:click="addToCart" class="flex w-full items-center justify-center"
            wire:loading.attr="disabled">
            <span wire:loading.remove>
                Dodaj do koszyka
            </span>
            <span wire:loading>Dodawanie...</span>
        </flux:button>
    @endif
</div>

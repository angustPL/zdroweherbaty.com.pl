<?php

use function Livewire\Volt\{state, mount};
use App\Services\CartService;

state(['itemCount' => 0]);

mount(function () {
    $this->loadCart();
});

$loadCart = function () {
    $cartService = app(CartService::class);
    $cart = $cartService->getCart();
    $this->itemCount = $cart['item_count'] ?? 0;
};

?>

<div class="relative" id="cart-icon" x-data x-on:cart-updated-js.window="$wire.loadCart()">
    <a href="{{ route('koszyk') }}"
        class="flex items-center gap-2 px-3 py-2 text-primary hover:text-primary transition-colors">
        <div class="relative">
            <svg class="w-8 h-8 -my-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            @if ($itemCount > 0)
                <flux:badge size="sm" variant="solid" color="zinc" as="button"
                    class="absolute -bottom-2 -right-2">
                    {{ $itemCount > 99 ? '99+' : $itemCount }}
                </flux:badge>
            @endif
        </div>
    </a>
</div>

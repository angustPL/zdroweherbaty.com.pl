<?php

use function Livewire\Volt\{state, mount, layout};
use App\Services\CartService;

layout('layouts.app');

state(['cart' => [], 'message' => '']);

mount(function () {
    $this->loadCart();
});

$loadCart = function () {
    $cartService = app(CartService::class);
    $this->cart = $cartService->getCart();
};

$addTestProduct = function () {
    $cartService = app(CartService::class);
    $cartService->addToCart(1, 'Testowa herbata', 25.5, 'test.jpg');
    $this->loadCart();
    $this->message = 'Produkt dodany do koszyka!';
};

$clearCart = function () {
    $cartService = app(CartService::class);
    $cartService->clearCart();
    $this->loadCart();
    $this->message = 'Koszyk wyczyszczony!';
};

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-black mb-2">Test Koszyka</h1>
        <p class="text-gray-600">Testowanie funkcjonalności koszyka</p>
    </div>

    @if ($message)
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ $message }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Test Actions -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Akcje testowe</h2>
            <div class="space-y-4">
                <flux:button variant="primary" wire:click="addTestProduct">
                    Dodaj testowy produkt
                </flux:button>

                <flux:button variant="outline" wire:click="clearCart">
                    Wyczyść koszyk
                </flux:button>
            </div>
        </div>

        <!-- Cart Status -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Stan koszyka</h2>
            <div class="space-y-2">
                <p><strong>Liczba produktów:</strong> {{ $cart['item_count'] ?? 0 }}</p>
                <p><strong>Suma:</strong> {{ number_format($cart['total'] ?? 0, 2) }} zł</p>
                <p><strong>Produkty w koszyku:</strong></p>
                <ul class="list-disc list-inside ml-4">
                    @foreach ($cart['items'] ?? [] as $item)
                        <li>{{ $item['name'] }} - {{ $item['quantity'] }}x {{ number_format($item['price'], 2) }} zł
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Raw Cart Data -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Surowe dane koszyka</h2>
        <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{{ json_encode($cart, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>

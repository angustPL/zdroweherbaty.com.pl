<?php

use function Livewire\Volt\{state, mount, layout};
use App\Services\CartService;

layout('layouts.app');

state(['cart' => []]);

mount(function () {
    $this->loadCart();
});

$loadCart = function () {
    $cartService = app(CartService::class);
    $this->cart = $cartService->getCart();
};

$updateQuantity = function ($productId, $quantity) {
    try {
        $cartService = app(CartService::class);
        $cartService->updateQuantity($productId, $quantity);

        // NIE ładujemy ponownie koszyka - dane już są w UI
        // $this->loadCart();

        // Emituj event do odświeżenia ikony koszyka
        $this->dispatch('cart-updated');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Koszyk zaktualizowany',
        ]);
    } catch (\Exception $e) {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Błąd podczas aktualizacji koszyka',
        ]);
    }
};

$removeFromCart = function ($productId) {
    try {
        $cartService = app(CartService::class);
        $cartService->removeFromCart($productId);
        $this->loadCart();

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
};

$clearCart = function () {
    try {
        $cartService = app(CartService::class);
        $cartService->clearCart();

        // Emituj event do odświeżenia ikony koszyka
        $this->dispatch('cart-updated');

        // Reset state i ponowne załadowanie
        $this->reset('cart');
        $this->loadCart();

        // JavaScript - natychmiastowe ukrycie produktów
        $this->dispatch('clear-cart-ui');

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
};

?>

<div wire:key="cart-{{ md5(json_encode($cart)) }}">
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('clear-cart-ui', () => {
                setTimeout(() => {
                    window.location.reload();
                }, 100);
            });
        });
    </script>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Koszyk</h1>
        <p class="text-gray-600">
            @if (empty($cart['items']))
                Twój koszyk jest pusty
            @else
                Masz {{ $cart['item_count'] }} produktów w koszyku
            @endif
        </p>
    </div>

    @if (empty($cart['items']))
        <div class="text-center py-12">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z">
                </path>
            </svg>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Twój koszyk jest pusty</h3>
            <p class="text-gray-500 mb-6">Dodaj produkty do koszyka, aby rozpocząć zakupy</p>
            <flux:button variant="primary" href="{{ route('home') }}">
                Przejdź do sklepu
            </flux:button>
        </div>
    @else
        <div class="space-y-6">
            {{-- Tabela produktów --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Produkt
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cena jednostkowa
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ilość
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Wartość
                            </th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">

                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($cart['items'] as $productId => $item)
                            <tr>
                                {{-- Produkt --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-16 w-16">
                                            <img src="{{ Storage::disk('public')->exists('img/towary/' . $item['image']) ? Storage::disk('public')->url('img/towary/' . $item['image']) : asset('img/towary/placeholder.jpg') }}"
                                                alt="{{ $item['name'] }}" class="h-16 w-16 object-cover rounded">
                                        </div>
                                        <div class="ml-4">
                                            <a href="{{ route('towar', $item['id']) }}"
                                                class="text-sm/4! font-medium text-gray-900 hover:text-primary">
                                                {{ $item['name'] }}
                                            </a>
                                        </div>
                                    </div>
                                </td>

                                {{-- Cena jednostkowa --}}
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    {{ number_format($item['price'], 2, ',', '.') }} zł
                                </td>

                                {{-- Ilość --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2" x-data="{
                                        debounceTimer: null,
                                        updateQuantityDebounced(productId, newQuantity) {
                                            // Natychmiastowa zmiana w UI
                                            $wire.$set('cart.items.' + productId + '.quantity', newQuantity);
                                    
                                            // Przelicz total
                                            let total = 0;
                                            Object.values($wire.cart.items).forEach(item => {
                                                total += item.price * item.quantity;
                                            });
                                            $wire.$set('cart.total', total);
                                    
                                            // Debounce zapisanie
                                            clearTimeout(this.debounceTimer);
                                            this.debounceTimer = setTimeout(() => {
                                                $wire.updateQuantity(productId, newQuantity);
                                            }, 1000);
                                        }
                                    }">
                                        <flux:button variant="outline" size="sm"
                                            @click="updateQuantityDebounced({{ $productId }}, {{ $item['quantity'] - 1 }})"
                                            class="w-8 h-8 p-0 flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 12H4">
                                                </path>
                                            </svg>
                                        </flux:button>

                                        <span
                                            class="w-8 text-center font-medium text-md">{{ $item['quantity'] }}</span>

                                        <flux:button variant="outline" size="sm"
                                            @click="updateQuantityDebounced({{ $productId }}, {{ $item['quantity'] + 1 }})"
                                            class="w-8 h-8 p-0 flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </flux:button>
                                    </div>
                                </td>

                                {{-- Wartość --}}
                                <td class="px-6 py-4 text-right text-md">
                                    {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }} zł
                                </td>

                                {{-- Akcje --}}
                                <td class="px-6 py-4 text-center">
                                    <flux:button variant="outline" size="sm"
                                        wire:click="removeFromCart({{ $productId }})"
                                        class="w-8 h-8 p-0 text-red-500 hover:text-red-700 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Podsumowanie --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-xl font-medium">Suma:</span>
                    <span class="text-3xl font-bold text-primary">
                        {{ number_format($cart['total'] ?? 0, 2, ',', '.') }} zł
                    </span>
                </div>

                <div class="flex gap-4">
                    <flux:button variant="outline" wire:click="clearCart" wire:loading.attr="disabled"
                        class="flex-1 flex items-center justify-center">
                        <span wire:loading.remove>Wyczyść koszyk</span>
                        <span wire:loading>Czyszczenie...</span>
                    </flux:button>

                    <flux:button variant="primary" class="flex-1">
                        Przejdź do zamówienia
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

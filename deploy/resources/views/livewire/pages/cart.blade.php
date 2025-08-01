{{-- Logika: app/Livewire/Pages/Cart.php --}}
<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Koszyk</h1>
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
            <h3 class="text-xl font-medium mb-2">Twój koszyk jest pusty</h3>
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
                                            <img class="h-16 w-16 rounded object-cover"
                                                src="{{ Storage::disk('public')->exists('img/towary/' . $item['image']) ? Storage::disk('public')->url('img/towary/' . $item['image']) : asset('img/towary/placeholder.jpg') }}"
                                                alt="{{ $item['name'] }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('towar', [$item['id'], Str::slug($item['name'])]) }}"
                                                    class="hover:text-primary">{{ $item['name'] }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Cena jednostkowa --}}
                                <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                    {{ number_format($item['price'], 2, ',', '.') }} zł
                                </td>

                                {{-- Ilość --}}
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2" x-data="{
                                        debounceTimer: null,
                                        updateQuantityDebounced(productId, newQuantity) {
                                            // Walidacja - ilość musi być > 0
                                            if (newQuantity <= 0) {
                                                return;
                                            }

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
                                            }, 500);
                                        }
                                    }">
                                        <flux:button variant="outline" size="sm"
                                            @click="updateQuantityDebounced({{ $productId }}, {{ $item['quantity'] - 1 }})"
                                            class="w-8 h-8 p-0 flex items-center justify-center {{ $item['quantity'] <= 1 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                                            <span class="text-lg font-bold">−</span>
                                        </flux:button>

                                        <span
                                            class="w-8 text-center font-medium text-md">{{ $item['quantity'] }}</span>

                                        <flux:button variant="outline" size="sm"
                                            @click="updateQuantityDebounced({{ $productId }}, {{ $item['quantity'] + 1 }})"
                                            class="w-8 h-8 p-0 flex items-center justify-center cursor-pointer">
                                            <span class="text-lg font-bold">+</span>
                                        </flux:button>
                                    </div>
                                </td>

                                {{-- Wartość --}}
                                <td class="px-6 py-4 text-right text-md whitespace-nowrap">
                                    {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }} zł
                                </td>

                                {{-- Akcje --}}
                                <td class="px-6 py-4 text-center">
                                    <flux:button variant="outline" size="sm"
                                        wire:click="removeFromCart({{ $productId }})" wire:loading.attr="disabled"
                                        class="w-8 h-8 p-0 text-red-500 hover:text-red-700 flex items-center justify-center cursor-pointer">
                                        <flux:icon.trash-2 variant="micro" />
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
                    <flux:button variant="outline" wire:click="clearCart"
                        class="flex-1 flex items-center justify-center">
                        Wyczyść koszyk
                    </flux:button>

                    <flux:button variant="primary" class="flex-1">
                        Przejdź do zamówienia
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

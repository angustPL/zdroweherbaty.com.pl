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
                                                <a href="{{ route('product', [$item['id'], Str::slug($item['name'])]) }}"
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

                                            // GTM update_cart event
                                            if (typeof dataLayer !== 'undefined') {
                                                dataLayer.push({
                                                    'event': 'update_cart',
                                                    'ecommerce': {
                                                        'items': [{
                                                            'item_id': '{{ $item['id'] }}',
                                                            'item_name': '{{ $item['name'] }}',
                                                            'price': {{ $item['price'] }},
                                                            'currency': 'PLN',
                                                            'quantity': newQuantity
                                                        }]
                                                    }
                                                });
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
                                        onclick="
                                            if (typeof dataLayer !== 'undefined') {
                                                dataLayer.push({
                                                    'event': 'remove_from_cart',
                                                    'ecommerce': {
                                                        'items': [{
                                                            'item_id': '{{ $item['id'] }}',
                                                            'item_name': '{{ $item['name'] }}',
                                                            'price': {{ $item['price'] }},
                                                            'currency': 'PLN',
                                                            'quantity': {{ $item['quantity'] }}
                                                        }]
                                                    }
                                                });
                                            }
                                        "
                                        class="w-8 h-8 p-0 text-red-500 hover:text-red-700 flex items-center justify-center cursor-pointer">
                                        <flux:icon.trash-2 variant="micro" />
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Komunikat o darmowej dostawie --}}
            @php
                $freeThreshold = (float) config('enova.delivery.free_delivery_threshold', 0);
                $cartTotal = $cart['total'] ?? 0;
                $hasFreeDelivery = $freeThreshold > 0 && $cartTotal >= $freeThreshold;
            @endphp
            @if ($hasFreeDelivery)
                <div class="mb-2">
                    <flux:callout variant="success" icon="check-circle">
                        <flux:callout.heading>Darmowa dostawa</flux:callout.heading>
                        <flux:callout.text>
                            Przekroczono próg {{ number_format($freeThreshold, 2, ',', '.') }} zł — koszt dostawy 0 zł
                        </flux:callout.text>
                    </flux:callout>
                </div>
            @elseif ($freeThreshold > 0)
                @php $missing = max(0, $freeThreshold - $cartTotal); @endphp
                @if ($missing > 0)
                    <div class="mb-2">
                        <flux:callout variant="secondary" icon="information-circle">
                            <flux:callout.heading>
                                Brakuje {{ number_format($missing, 2, ',', '.') }} zł do darmowej dostawy
                            </flux:callout.heading>
                        </flux:callout>
                    </div>
                @endif
            @endif

            {{-- Podsumowanie --}}
            <div class="bg-white rounded-lg shadow p-6">
                {{-- Pole na kod rabatowy --}}
                <div class="mb-4 flex justify-end">
                    <div class="flex flex-col items-end gap-2">
                        <div class="flex gap-2 items-end">
                            <div class="w-48">
                                <flux:input 
                                    wire:model="promotionCode"
                                    placeholder="Wprowadź kod rabatowy"
                                    class="w-full"
                                    wire:keydown.enter="applyPromotionCode"
                                />
                            </div>
                        @if ($appliedPromotion)
                            <flux:button 
                                variant="outline" 
                                wire:click="removePromotionCode"
                                class="whitespace-nowrap"
                            >
                                Usuń kod
                            </flux:button>
                        @else
                            <flux:button 
                                variant="primary" 
                                wire:click="applyPromotionCode"
                                wire:loading.attr="disabled"
                                class="whitespace-nowrap"
                            >
                                Zastosuj
                            </flux:button>
                        @endif
                        </div>
                        @if ($promotionError)
                            <p class="text-sm text-red-600 mt-1">{{ $promotionError }}</p>
                        @endif
                    </div>
                </div>

                {{-- Podsumowanie --}}
                <div class="space-y-2">
                    @if ($appliedPromotion && $promotionDiscount > 0)
                        {{-- Wartość produktów --}}
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Wartość produktów:</span>
                            <span>{{ number_format($cart['total'] ?? 0, 2, ',', '.') }} zł</span>
                        </div>
                        
                        {{-- Zniżka z promocji --}}
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Zniżka ({{ $appliedPromotion->code }}):</span>
                            <span>-{{ number_format($promotionDiscount, 2, ',', '.') }} zł</span>
                        </div>
                        
                        {{-- Razem --}}
                        <div class="flex justify-between font-semibold text-lg pt-2 border-t">
                            <span>Razem:</span>
                            <span>{{ number_format(max(0, ($cart['total'] ?? 0) - $promotionDiscount), 2, ',', '.') }} zł</span>
                        </div>
                    @else
                        {{-- Razem (bez zniżki) --}}
                        <div class="flex justify-between font-semibold text-lg">
                            <span>Razem:</span>
                            <span>{{ number_format($cart['total'] ?? 0, 2, ',', '.') }} zł</span>
                        </div>
                    @endif
                </div>

                @php
                    // Przygotuj dane dla GTM przed użyciem w JavaScript
                    $gtmItems = [];
                    if (!empty($cart['items'])) {
                        foreach ($cart['items'] as $productId => $item) {
                            $gtmItems[] = [
                                'item_id' => $item['id'],
                                'item_name' => $item['name'],
                                'price' => $item['price'],
                                'currency' => 'PLN',
                                'quantity' => $item['quantity']
                            ];
                        }
                    }
                @endphp

                <div class="flex gap-4 mt-6">
                    <flux:button variant="outline" wire:click="clearCart"
                        onclick="
                            if (typeof dataLayer !== 'undefined') {
                                dataLayer.push({
                                    'event': 'remove_from_cart',
                                    'ecommerce': {
                                        'items': @js($gtmItems)
                                    }
                                });
                            }
                        "
                        class="flex-1 flex items-center justify-center">
                        Wyczyść koszyk
                    </flux:button>

                    <flux:button variant="primary" href="{{ route('order.create') }}" class="flex-1">
                        Przejdź do zamówienia
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>

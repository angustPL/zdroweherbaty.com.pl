<?php

use function Livewire\Volt\{state, mount, layout};

layout('layouts.app');

state(['selectedDelivery' => 'dpd']);

?>

<div>
    <!-- Hero Section -->
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Dostawa
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Szybka i bezpieczna dostawa do Twojego domu
                </p>
            </div>
        </div>
    </section>

    <!-- Delivery Options Section -->
    <section class="bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Sposoby dostawy
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Delivery Option 1 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                    wire:click="selectedDelivery = 'dpd'"
                    :class="{ 'ring-2 ring-primary': $selectedDelivery === 'dpd' }">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Kurier DPD</h3>
                        <p class="text-gray-600 mb-4">Dostawa w 1-2 dni robocze</p>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Koszt dostawy:</span>
                                <span class="font-semibold text-primary">15,00 zł</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Czas dostawy:</span>
                                <span class="font-semibold">1-2 dni</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Option 2 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                    wire:click="selectedDelivery = 'poczta'"
                    :class="{ 'ring-2 ring-primary': $selectedDelivery === 'poczta' }">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Poczta Polska</h3>
                        <p class="text-gray-600 mb-4">Dostawa w 2-3 dni robocze</p>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Koszt dostawy:</span>
                                <span class="font-semibold text-primary">12,00 zł</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Czas dostawy:</span>
                                <span class="font-semibold">2-3 dni</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Option 3 -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 cursor-pointer hover:ring-2 hover:ring-primary transition-all"
                    wire:click="selectedDelivery = 'osobisty'"
                    :class="{ 'ring-2 ring-primary': $selectedDelivery === 'osobisty' }">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Odbiór osobisty</h3>
                        <p class="text-gray-600 mb-4">Bezpłatny odbiór w naszym sklepie</p>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Koszt dostawy:</span>
                                <span class="font-semibold text-green-600">0,00 zł</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Czas dostawy:</span>
                                <span class="font-semibold">Tego samego dnia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Delivery Info -->
            @if ($selectedDelivery)
                <div class="mt-8">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Wybrana opcja dostawy</h3>
                            <div class="space-y-4">
                                @if ($selectedDelivery === 'dpd')
                                    <p class="text-gray-600">Kurier DPD - dostawa w 1-2 dni robocze za 15,00 zł</p>
                                @elseif($selectedDelivery === 'poczta')
                                    <p class="text-gray-600">Poczta Polska - dostawa w 2-3 dni robocze za 12,00 zł</p>
                                @elseif($selectedDelivery === 'osobisty')
                                    <p class="text-gray-600">Odbiór osobisty - bezpłatnie, tego samego dnia</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Delivery Info Section -->
    <section class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Informacje o dostawie
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Obszar dostawy</h3>
                        <p class="text-gray-600">
                            Dostarczamy na terenie całej Polski. W przypadku zamówień powyżej 100 zł
                            dostawa kurierem DPD jest bezpłatna.
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Śledzenie przesyłki</h3>
                        <p class="text-gray-600">
                            Po wysłaniu zamówienia otrzymasz email z numerem śledzenia,
                            dzięki któremu będziesz mógł śledzić status swojej przesyłki.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

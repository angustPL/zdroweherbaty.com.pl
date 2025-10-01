<?php

use function Livewire\Volt\{state, mount, layout};
use App\Services\CartService;
use App\Models\Delivery;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\JsonLd;

layout('layouts.app');

// SEO Meta Tags
app('seotools')->setCanonical(url('/zamowienie'));
app('seotools')->opengraph()->setUrl(url('/zamowienie'));
app('seotools.json-ld')->setType('WebPage')->addValue('url', url('/zamowienie'))->addValue('name', 'Zamówienie - Zdrowe Herbaty BIFIX')->addValue('description', 'Złóż zamówienie w sklepu Zdrowe Herbaty BIFIX. Wypełnij dane dostawy i wybierz sposób płatności.');

// Stan komponentu
state([
    'cart' => [],
    'customerData' => [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'street' => '',
        'street_number' => '',
        'apartment' => '',
        'city' => '',
        'postal_code' => '',
        'post_office' => '',
        'country' => 'Polska',
        'invoice_required' => false,
    ],
    'invoiceData' => [
        'company_name' => '',
        'nip' => '',
        'street' => '',
        'street_number' => '',
        'apartment' => '',
        'city' => '',
        'postal_code' => '',
        'post_office' => '',
    ],
    'deliveryOptions' => [],
    'selectedDelivery' => null,
    'paymentOptions' => [],
    'selectedPayment' => null,
]);

// Reguły walidacji
$rules = [
    'customerData.first_name' => 'required|string|max:255',
    'customerData.last_name' => 'required|string|max:255',
    'customerData.email' => 'required|email|max:255',
    'customerData.phone' => 'nullable|string|max:20',
    'customerData.street' => 'required|string|max:255',
    'customerData.street_number' => 'required|string|max:10',
    'customerData.apartment' => 'nullable|string|max:10',
    'customerData.city' => 'nullable|string|max:255',
    'customerData.postal_code' => 'required|string|max:10',
    'customerData.post_office' => 'required|string|max:255',
    'invoiceData.company_name' => 'required_if:customerData.invoice_required,true|string|max:255',
    'invoiceData.nip' => 'required_if:customerData.invoice_required,true|string|max:20',
    'invoiceData.street' => 'required_if:customerData.invoice_required,true|string|max:255',
    'invoiceData.street_number' => 'required_if:customerData.invoice_required,true|string|max:10',
    'invoiceData.apartment' => 'nullable|string|max:10',
    'invoiceData.city' => 'required_if:customerData.invoice_required,true|string|max:255',
    'invoiceData.postal_code' => 'required_if:customerData.invoice_required,true|string|max:10',
    'invoiceData.post_office' => 'required_if:customerData.invoice_required,true|string|max:255',
    'selectedDelivery' => 'required|integer',
];

// Komunikaty błędów
$messages = [
    'customerData.first_name.required' => 'Imię jest wymagane.',
    'customerData.last_name.required' => 'Nazwisko jest wymagane.',
    'customerData.email.required' => 'Adres email jest wymagany.',
    'customerData.email.email' => 'Podaj prawidłowy adres email.',
    'customerData.street.required' => 'Ulica jest wymagana.',
    'customerData.street_number.required' => 'Numer domu jest wymagany.',
    'customerData.postal_code.required' => 'Kod pocztowy jest wymagany.',
    'customerData.post_office.required' => 'Poczta jest wymagana.',
    'invoiceData.company_name.required_if' => 'Nazwa firmy jest wymagana dla faktury.',
    'invoiceData.nip.required_if' => 'NIP jest wymagany dla faktury.',
    'invoiceData.street.required_if' => 'Ulica jest wymagana dla faktury.',
    'invoiceData.street_number.required_if' => 'Numer domu jest wymagany dla faktury.',
    'invoiceData.city.required_if' => 'Miejscowość jest wymagana dla faktury.',
    'invoiceData.postal_code.required_if' => 'Kod pocztowy jest wymagany dla faktury.',
    'invoiceData.post_office.required_if' => 'Poczta jest wymagana dla faktury.',
    'selectedDelivery.required' => 'Wybierz opcję dostawy.',
];

mount(function () {
    $this->loadCart();
});

// Funkcja do czyszczenia danych faktury
$clearInvoiceData = function () {
    $this->invoiceData = [
        'company_name' => '',
        'nip' => '',
        'street' => '',
        'street_number' => '',
        'apartment' => '',
        'city' => '',
        'postal_code' => '',
        'post_office' => '',
    ];
};

$loadCart = function () {
    $cartService = app(CartService::class);
    $this->cart = $cartService->getCart();

    // Jeśli koszyk jest pusty, przekieruj do koszyka
    if (empty($this->cart['items'])) {
        return redirect()->route('cart');
    }

    // Ładowanie opcji dostawy na podstawie wagi koszyka
    $this->loadDeliveryOptions();
};

$loadDeliveryOptions = function () {
    $cartWeight = $this->cart['total_weight'] ?? 0;

    // Używamy tego samego zapytania co na podstronie dostawy
    $deliveries = Delivery::join('Ceny', 'Towary.ID', '=', 'Ceny.Towar')
        ->join('Features as PaymentFeatures', function ($join) {
            $join->on('Towary.ID', '=', 'PaymentFeatures.Parent')->where('PaymentFeatures.Name', '=', config('enova.payment.feature_payment_method'));
        })
        ->join('SposobyZaplaty', 'PaymentFeatures.Data', '=', 'SposobyZaplaty.ID')
        ->where('Towary.MasaBruttoValue', '>=', $cartWeight)
        ->where('Ceny.Definicja', config('enova.prices.definition'))
        ->orderBy('MasaBruttoValue')
        ->orderBy('Ceny.BruttoValue')
        ->select(['Towary.ID', 'Towary.Nazwa', 'Towary.Opis', 'Towary.MasaBruttoValue', 'Ceny.BruttoValue', 'SposobyZaplaty.Nazwa as PaymentMethodName'])
        ->get();

    // Znajdź najniższą wagę wyższą od wagi koszyka
    $minWeight = $deliveries->min('MasaBruttoValue');

    // Filtruj tylko opcje z najniższą wagą
    $filteredDeliveries = $deliveries->where('MasaBruttoValue', $minWeight);

    $this->deliveryOptions = $filteredDeliveries
        ->map(function ($delivery) {
            // Sprawdź czy to opcja paczkomatu
            $isParcelLocker = str_contains(strtolower($delivery->Nazwa), strtolower(config('enova.delivery.parcel_locker_name', 'Paczkomaty 24/7')));

            // Darmowa dostawa po przekroczeniu progu wartości koszyka
            $cartTotal = $this->cart['total'] ?? 0;
            $freeThreshold = (float) config('enova.delivery.free_delivery_threshold', 0);
            $isFree = $cartTotal >= $freeThreshold && $freeThreshold > 0;
            $price = $isFree ? 0 : $delivery->BruttoValue;

            return [
                'id' => $delivery->ID,
                'name' => $delivery->Nazwa,
                'description' => $delivery->Opis,
                'max_weight' => $delivery->MasaBruttoValue,
                'price' => $price,
                'payment_method' => $delivery->PaymentMethodName ?? '-',
                'is_parcel_locker' => $isParcelLocker,
                'is_free' => $isFree,
            ];
        })
        ->toArray();
};

$submitOrder = function () {
    $this->validate($rules, $messages);

    // Tutaj będzie logika zapisywania zamówienia
    $this->dispatch('notify', [
        'type' => 'success',
        'message' => 'Zamówienie zostało złożone pomyślnie!',
    ]);
};

?>

<div>
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Zamówienie</h1>
        <p class="text-gray-600">
            Wypełnij dane dostawy i wybierz sposób płatności
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
            <p class="text-gray-500 mb-6">Dodaj produkty do koszyka, aby złożyć zamówienie</p>
            <a href="{{ route('home') }}"
                class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 font-medium">
                Przejdź do sklepu
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Formularz zamówienia --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Dane klienta --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Dane dostawy</h2>

                    <form wire:submit.prevent="submitOrder" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:field>
                                    <flux:label>Imię *</flux:label>
                                    <flux:input wire:model="customerData.first_name" placeholder="Wprowadź imię" />
                                    <flux:error name="customerData.first_name" />
                                </flux:field>
                            </div>
                            <div>
                                <flux:field>
                                    <flux:label>Nazwisko *</flux:label>
                                    <flux:input wire:model="customerData.last_name" placeholder="Wprowadź nazwisko" />
                                    <flux:error name="customerData.last_name" />
                                </flux:field>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:field>
                                    <flux:label>Email *</flux:label>
                                    <flux:input type="email" wire:model="customerData.email"
                                        placeholder="Wprowadź email" />
                                    <flux:error name="customerData.email" />
                                </flux:field>
                            </div>
                            <div>
                                <flux:field>
                                    <flux:label>Telefon</flux:label>
                                    <flux:input wire:model="customerData.phone" placeholder="Wprowadź numer telefonu" />
                                    <flux:error name="customerData.phone" />
                                </flux:field>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div class="md:col-span-4">
                                <flux:field>
                                    <flux:label>Ulica *</flux:label>
                                    <flux:input wire:model="customerData.street" placeholder="Wprowadź ulicę" />
                                    <flux:error name="customerData.street" />
                                </flux:field>
                            </div>
                            <div class="md:col-span-1">
                                <flux:field>
                                    <flux:label>Nr *</flux:label>
                                    <flux:input wire:model="customerData.street_number" placeholder="1" />
                                    <flux:error name="customerData.street_number" />
                                </flux:field>
                            </div>
                            <div class="md:col-span-1">
                                <flux:field>
                                    <flux:label>Lokal</flux:label>
                                    <flux:input wire:model="customerData.apartment" placeholder="1A" />
                                    <flux:error name="customerData.apartment" />
                                </flux:field>
                            </div>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Miejscowość</flux:label>
                                <flux:input wire:model="customerData.city" placeholder="Wprowadź miejscowość" />
                                <flux:error name="customerData.city" />
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <flux:field>
                                    <flux:label>Kod pocztowy *</flux:label>
                                    <flux:input wire:model="customerData.postal_code" placeholder="00-000" />
                                    <flux:error name="customerData.postal_code" />
                                </flux:field>
                            </div>
                            <div class="md:col-span-9">
                                <flux:field>
                                    <flux:label>Poczta *</flux:label>
                                    <flux:input wire:model="customerData.post_office" placeholder="Wprowadź pocztę" />
                                    <flux:error name="customerData.post_office" />
                                </flux:field>
                            </div>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Kraj</flux:label>
                                <flux:input wire:model="customerData.country" readonly class="bg-gray-50" />
                                <flux:description>Zamówienia realizowane są tylko na terenie Polski
                                </flux:description>
                            </flux:field>
                        </div>

                        {{-- Faktura VAT --}}
                        <div class="border-t pt-4" x-data x-init="$wire.$watch('customerData.invoice_required', value => { if (!value) { $wire.clearInvoiceData(); } })">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.live="customerData.invoice_required"
                                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" />
                                <label class="ml-2 block text-sm text-gray-900">
                                    Wystaw fakturę VAT
                                </label>
                            </div>

                            <div x-data="{
                                show: @entangle('customerData.invoice_required')
                            }" x-show="show"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                                x-transition:enter-end="opacity-100 max-h-screen"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 max-h-screen"
                                x-transition:leave-end="opacity-0 max-h-0 overflow-hidden" class="mt-4 space-y-4">
                                <h2 class="text-xl font-semibold mb-4">Dane do faktury</h2>
                                <div>
                                    <flux:field>
                                        <flux:label>Nazwa firmy *</flux:label>
                                        <flux:input wire:model="invoiceData.company_name"
                                            placeholder="Wprowadź nazwę firmy" />
                                        <flux:error name="invoiceData.company_name" />
                                    </flux:field>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>NIP *</flux:label>
                                        <flux:input wire:model="invoiceData.nip" placeholder="Wprowadź NIP" />
                                        <flux:error name="invoiceData.nip" />
                                    </flux:field>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                    <div class="md:col-span-4">
                                        <flux:field>
                                            <flux:label>Ulica *</flux:label>
                                            <flux:input wire:model="invoiceData.street" placeholder="Wprowadź ulicę" />
                                            <flux:error name="invoiceData.street" />
                                        </flux:field>
                                    </div>
                                    <div class="md:col-span-1">
                                        <flux:field>
                                            <flux:label>Nr *</flux:label>
                                            <flux:input wire:model="invoiceData.street_number" placeholder="1" />
                                            <flux:error name="invoiceData.street_number" />
                                        </flux:field>
                                    </div>
                                    <div class="md:col-span-1">
                                        <flux:field>
                                            <flux:label>Lokal</flux:label>
                                            <flux:input wire:model="invoiceData.apartment" placeholder="1A" />
                                            <flux:error name="invoiceData.apartment" />
                                        </flux:field>
                                    </div>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>Miejscowość *</flux:label>
                                        <flux:input wire:model="invoiceData.city" placeholder="Wprowadź miejscowość" />
                                        <flux:error name="invoiceData.city" />
                                    </flux:field>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-3">
                                        <flux:field>
                                            <flux:label>Kod pocztowy *</flux:label>
                                            <flux:input wire:model="invoiceData.postal_code" placeholder="00-000" />
                                            <flux:error name="invoiceData.postal_code" />
                                        </flux:field>
                                    </div>
                                    <div class="md:col-span-9">
                                        <flux:field>
                                            <flux:label>Poczta *</flux:label>
                                            <flux:input wire:model="invoiceData.post_office"
                                                placeholder="Wprowadź pocztę" />
                                            <flux:error name="invoiceData.post_office" />
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                {{-- Wybór dostawy --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Wybór dostawy</h2>

                    @php
                        $freeThreshold = (float) config('enova.delivery.free_delivery_threshold', 0);
                        $cartTotal = $cart['total'] ?? 0;
                        $hasFreeDelivery = $freeThreshold > 0 && $cartTotal >= $freeThreshold;
                    @endphp
                    @if ($hasFreeDelivery)
                        <div class="mb-5">
                            <flux:callout variant="success" icon="check-circle">
                                <flux:callout.heading>Darmowa dostawa</flux:callout.heading>
                                <flux:callout.text>Przekroczono próg
                                    {{ number_format($freeThreshold, 2, ',', '.') }}
                                    zł — koszt dostawy 0 zł</flux:callout.text>
                            </flux:callout>
                        </div>
                    @elseif ($freeThreshold > 0)
                        @php $missing = max(0, $freeThreshold - $cartTotal); @endphp
                        @if ($missing > 0)
                            <div class="mb-4">
                                <flux:callout variant="secondary" icon="information-circle">
                                    <flux:callout.heading>Brakuje {{ number_format($missing, 2, ',', '.') }} zł do
                                        darmowej dostawy</flux:callout.heading>
                                </flux:callout>
                            </div>
                        @endif
                    @endif

                    {{-- Informacja o wadze koszyka --}}
                    {{-- <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-blue-900">Waga produktów w koszyku</div>
                                <div class="text-blue-700">
                                    {{ number_format($cart['total_weight'] ?? 0, 3, ',', '.') }} kg
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    @if (count($deliveryOptions) > 0)
                        <div class="space-y-3">
                            @foreach ($deliveryOptions as $option)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                                    wire:click="$set('selectedDelivery', {{ $option['id'] }})">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <input type="radio" wire:model.live="selectedDelivery"
                                                value="{{ $option['id'] }}" class="mr-3">
                                            <div>
                                                <div class="font-medium">{{ $option['name'] }}</div>
                                                @if ($option['description'])
                                                    <div class="text-sm text-gray-600">{{ $option['description'] }}
                                                    </div>
                                                @endif
                                                @if ($option['is_parcel_locker'])
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Przewidywany czas dostawy: 1 dzień roboczy
                                                    </div>
                                                    @if ($selectedDelivery == $option['id'])
                                                        <div class="mt-3">
                                                            <div id="danePaczkomatu"
                                                                class="mb-2 text-sm text-gray-600"></div>
                                                            <button type="button" onclick="openEasyPackModal()"
                                                                id="parcelLockerBtn"
                                                                class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center text-sm">
                                                                <svg class="w-4 h-4 mr-2" fill="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                                </svg>
                                                                <span id="btnText">Wybierz paczkomat</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold text-lg">
                                                {{ number_format($option['price'], 2, ',', '.') }} zł
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>Brak dostępnych opcji dostawy dla wagi
                                {{ number_format($cart['total_weight'] ?? 0, 3, ',', '.') }} kg</p>
                            <p class="text-sm mt-2">Skontaktuj się z nami, aby omówić możliwości dostawy</p>
                        </div>
                    @endif
                </div>



            </div>

            {{-- Podsumowanie --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-20">
                    <h2 class="text-xl font-semibold mb-4">Podsumowanie zamówienia</h2>

                    {{-- Produkty --}}
                    <div class="space-y-3 mb-4">
                        @foreach ($cart['items'] as $productId => $item)
                            <div class="flex justify-between items-center text-sm">
                                <div class="flex-1">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    <div class="text-gray-600">Ilość: {{ $item['quantity'] }}</div>
                                </div>
                                <div class="text-right">
                                    {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }} zł
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between font-semibold text-lg">
                            <span>Razem:</span>
                            <span>{{ number_format($cart['total'] ?? 0, 2, ',', '.') }} zł</span>
                        </div>
                    </div>

                    {{-- Przycisk edycji koszyka --}}
                    <div class="mt-4 pt-4 border-t">
                        <a href="{{ route('cart') }}"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Edytuj koszyk
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Przycisk zamówienia --}}
        <div class="mt-8">
            <form wire:submit.prevent="submitOrder">
                <flux:button type="submit" variant="primary" class="w-full">
                    Złóż zamówienie
                </flux:button>
            </form>
        </div>
    @endif


    <!-- EasyPack CSS -->
    <link href="//geowidget.easypack24.net/css/easypack.css" media="screen" rel="stylesheet" type="text/css">

    <!-- EasyPack JavaScript -->
    <script src="//geowidget.easypack24.net/js/sdk-for-javascript.js"></script>

    <script>
        window.easyPackAsyncInit = function() {
            easyPack.init({
                defaultLocale: 'pl',
                mapType: 'osm',
                searchType: 'osm',
                map: {
                    googleKey: 'AIzaSyDyxnUZuehaTUYAU8FEEie7N0KGk1XMn6c'
                },
                points: {
                    types: ['parcel_locker']
                },
                map: {
                    initialTypes: ['parcel_locker']
                }
            });
        };

        // Sprawdź czy EasyPack jest załadowany po załadowaniu strony
        document.addEventListener('DOMContentLoaded', function() {
            // Opóźnij ładowanie paczkomatu, aby elementy były dostępne
            setTimeout(loadSavedParcelLocker, 500);

            // Dodatkowo: nasłuchuj zmian DOM (Livewire rerender)
            try {
                const observer = new MutationObserver((mutations) => {
                    const displayDiv = document.getElementById('danePaczkomatu');
                    const btnText = document.getElementById('btnText');
                    if (displayDiv && btnText && displayDiv.childElementCount === 0) {
                        loadSavedParcelLocker();
                    }
                });
                observer.observe(document.body, {
                    subtree: true,
                    childList: true
                });
            } catch (e) {
                // MutationObserver unavailable
            }
        });

        // Hook Livewire: po odświeżeniu komponentu spróbuj ponownie wczytać zapisany paczkomat
        document.addEventListener('livewire:navigated', () => {
            setTimeout(loadSavedParcelLocker, 200);
        });

        // Załaduj zapisany paczkomat z cookies
        function loadSavedParcelLocker() {
            const saved = getCookie('selectedParcelLocker');

            if (saved) {
                try {
                    const locker = JSON.parse(saved);
                    const displayDiv = document.getElementById('danePaczkomatu');
                    const btnText = document.getElementById('btnText');

                    if (displayDiv && locker.name) {
                        displayDiv.innerHTML = '<strong>' + locker.name + '</strong> - ' +
                            (locker.address?.line1 || '') + ', ' +
                            (locker.address?.line2 || '');

                        // Zmień tekst przycisku
                        if (btnText) {
                            btnText.textContent = 'Zmień paczkomat';
                        }
                    }
                } catch (e) {
                    // Błąd parsowania zapisanego paczkomatu
                }
            }
        }

        // Funkcja pomocnicza do odczytu cookies
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        function openEasyPackModal() {
            if (typeof easyPack === 'undefined') {
                alert('EasyPack nie jest załadowany. Spróbuj ponownie za chwilę.');
                return;
            }

            if (typeof easyPack.modalMap !== 'function') {
                alert('EasyPack.modalMap nie jest dostępne. Spróbuj ponownie za chwilę.');
                return;
            }

            easyPack.modalMap(function(point, modal) {
                modal.closeModal();
                if (point.name) {
                    // Zapisz wybór do cookies na 30 dni
                    const expiryDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
                    document.cookie =
                        `selectedParcelLocker=${JSON.stringify(point)}; expires=${expiryDate.toUTCString()}; path=/`;

                    // Wyświetl wybór
                    const displayDiv = document.getElementById('danePaczkomatu');
                    const btnText = document.getElementById('btnText');

                    if (displayDiv) {
                        displayDiv.innerHTML = '<strong>' + point.name + '</strong> - ' +
                            (point.address?.line1 || '') + ', ' +
                            (point.address?.line2 || '');
                    }

                    // Zmień tekst przycisku
                    if (btnText) {
                        btnText.textContent = 'Zmień paczkomat';
                    }
                } else {
                    alert('Brak danych paczkomatu');
                }
            }, {
                width: 500
            });
        }
    </script>
</div>

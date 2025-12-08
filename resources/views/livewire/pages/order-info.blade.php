<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Order;
use App\Models\EnovaOrder;
use App\Models\Product;
use App\Services\PayuService;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Funkcja pomocnicza do formatowania kodu pocztowego (zawsze 5 cyfr: xx-xxx)
// Jeśli kod ma mniej niż 5 cyfr, dodajemy 0 na początku
if (!function_exists('formatPostalCode')) {
    function formatPostalCode($code)
    {
        if (empty($code)) {
            return '';
        }
        // Usuń wszystkie znaki niebędące cyframi
        $code = preg_replace('/[^0-9]/', '', $code);
        $length = strlen($code);

        // Jeśli ma mniej niż 5 cyfr, dodaj 0 na początku
        if ($length < 5) {
            $code = str_pad($code, 5, '0', STR_PAD_LEFT);
        }

        // Jeśli ma więcej niż 5 cyfr, weź pierwsze 5
        if ($length > 5) {
            $code = substr($code, 0, 5);
        }

        // Sformatuj jako xx-xxx (zawsze 5 cyfr)
        return substr($code, 0, 2) . '-' . substr($code, 2, 3);
    }
}

layout('layouts.app');

// SEO Meta Tags
app('seotools')->setTitle('Szczegóły zamówienia - Zdrowe Herbaty BIFIX');
app('seotools')->setCanonical(url('/zamowienie'));
app('seotools')->opengraph()->setUrl(url('/zamowienie'));
app('seotools.json-ld')->setType('WebPage')->addValue('url', url('/zamowienie'))->addValue('name', 'Szczegóły zamówienia - Zdrowe Herbaty BIFIX')->addValue('description', 'Szczegóły zamówienia w sklepie Zdrowe Herbaty BIFIX.');

state([
    'order' => null,
    'enovaOrder' => null,
    'extOrderId' => null,
    'orderStatus' => null,
    'paymentStatus' => null, // Status płatności (osobno od statusu zamówienia)
    'orderNumber' => null,
    'orderDate' => null,
    'showPaymentSuccess' => false,
    'notFound' => false, // Flaga czy zamówienie nie zostało znalezione
    'recommendedProducts' => [], // Rekomendowane produkty do wyświetlenia gdy zamówienie nie znalezione
    'payuRedirectUri' => null, // Bezpośredni link do PayU jeśli istnieje
]);

mount(function ($ext_order_id) {
    $this->extOrderId = $ext_order_id;
    $this->showPaymentSuccess = false; // Initialize the state variable

    // Sprawdź czy mamy komunikat sukcesu z sesji (z PayU success callback)
    if (session('order_success')) {
        $this->showPaymentSuccess = true;
        // Usuń z sesji, aby nie pokazywać przy kolejnych odświeżeniach
        session()->forget('order_success');
    }

    // Najpierw sprawdź czy zamówienie jest w Enova (jak w starym systemie)
    // Obsługa timeoutów - jeśli Enova nie odpowiada, używamy lokalnej bazy
    try {
        $this->enovaOrder = EnovaOrder::byGuid($ext_order_id)->first();
    } catch (\Exception $e) {
        // Timeout lub błąd połączenia z Enova - loguj i kontynuuj z lokalną bazą
        \Log::warning('Błąd połączenia z Enova przy pobieraniu zamówienia', [
            'ext_order_id' => $ext_order_id,
            'error' => $e->getMessage(),
        ]);
        $this->enovaOrder = null;
    }

    if ($this->enovaOrder) {
        // Zamówienie jest w Enova - używamy TYLKO danych z Enova (najwyższy priorytet)
        // Status: 'Zarejestrowane w systemie' (jak w starym systemie)
        $this->orderStatus = 'Zarejestrowane w systemie';
        $this->orderNumber = $this->enovaOrder->NumerPelny;

        // Data z Enova (Data + Czas)
        if ($this->enovaOrder->Data) {
            $data = Carbon::parse($this->enovaOrder->Data);
            if ($this->enovaOrder->Czas) {
                $timeParts = explode(':', $this->enovaOrder->Czas);
                $data->setTime((int) ($timeParts[0] ?? 0), (int) ($timeParts[1] ?? 0));
            }
            $this->orderDate = $data;
        }

        // NIE pobieramy lokalnego zamówienia - Enova ma najwyższy priorytet
        $this->order = null;
    } else {
        // Zamówienie nie jest jeszcze w Enova - używamy danych z lokalnej bazy
        $this->order = Order::where('ext_order_id', $ext_order_id)->first();

        if (!$this->order) {
            // Zamówienie nie znalezione - ustawiamy flagę i zwracamy 404, ale wyświetlamy komunikat na stronie
            $this->notFound = true;
            // Ustawiamy kod odpowiedzi HTTP na 404 przez session, aby middleware mógł to odczytać
            session(['order_not_found_404' => true]);

            // Pobierz kilka losowych produktów do rekomendacji z cache
            try {
                $allProducts = Product::getCachedAll();
                $this->recommendedProducts = collect($allProducts)->shuffle()->take(6)->values()->toArray();
            } catch (\Exception $e) {
                // W przypadku błędu, zostaw puste produkty
                $this->recommendedProducts = [];
                Log::warning('Nie udało się pobrać rekomendowanych produktów: ' . $e->getMessage());
            }

            return; // Przerwij dalsze wykonywanie, jeśli zamówienie nie zostało znalezione
        }

        // Status z lokalnej bazy - używamy label() z enum dla polskich nazw
        $this->orderStatus = $this->order->status->label();
        // Status płatności - używamy label() z enum PaymentStatus
        if ($this->order->payment) {
            $this->paymentStatus = $this->order->payment->status->label();
        }
        $this->orderNumber = null; // Nie ma jeszcze numeru z Enova
        $this->orderDate = $this->order->created_at;

        // Jeśli zamówienie ma płatność PayU ze statusem "pending", sprawdź aktualny status w PayU
        if ($this->order && $this->order->payment && $this->order->payment->isPayu() && $this->order->payment->isPending()) {
            $payuOrderId = $this->order->payment->payu_order_id;
            if ($payuOrderId) {
                try {
                    $payuService = app(PayuService::class);
                    $payuData = $payuService->getOrderStatus($payuOrderId);

                    if ($payuData) {
                        // PayU może zwrócić różne struktury odpowiedzi
                        $orderData = null;
                        if (isset($payuData['orders']) && is_array($payuData['orders']) && count($payuData['orders']) > 0) {
                            $orderData = $payuData['orders'][0];
                        } elseif (isset($payuData['status'])) {
                            $orderData = $payuData;
                        }

                        if ($orderData) {
                            $status = $orderData['status'] ?? null;
                            $statusDesc = $orderData['statusDesc'] ?? null;
                            $localStatus = $payuService->mapPayuStatusToLocal($status);

                            // Jeśli zamówienie jest PENDING i ma redirectUri, zapisz go do użycia w linku
                            if ($status === 'PENDING' && isset($orderData['redirectUri'])) {
                                $this->payuRedirectUri = $orderData['redirectUri'];
                            }

                            // Zaktualizuj płatność jeśli status się zmienił
                            if ($localStatus !== $this->order->payment->status) {
                                $oldStatus = $this->order->payment->status;
                                $updateData = [
                                    'status' => $localStatus->value,
                                    'payu_data' => $payuData,
                                ];

                                if ($localStatus === \App\Enums\PaymentStatus::COMPLETED) {
                                    $updateData['paid_at'] = now();
                                    // Jeśli status zmienił się na completed, wyświetl komunikat sukcesu
                                    if ($oldStatus !== \App\Enums\PaymentStatus::COMPLETED) {
                                        $this->showPaymentSuccess = true;
                                    }
                                }

                                if ($localStatus === \App\Enums\PaymentStatus::FAILED && $statusDesc) {
                                    $updateData['failure_reason'] = $statusDesc;
                                }

                                $this->order->payment->update($updateData);

                                // Zaktualizuj status zamówienia
                                $orderStatus = $payuService->mapPaymentStatusToOrderStatus($localStatus);
                                if ($orderStatus) {
                                    $this->order->update(['status' => $orderStatus->value]);
                                }

                                // Odśwież dane zamówienia
                                $this->order->refresh();

                                // Zaktualizuj wyświetlany status - używamy label() z enum dla polskich nazw
                                $this->orderStatus = $this->order->status->label();
                                // Zaktualizuj status płatności
                                $this->order->payment->refresh();
                                $this->paymentStatus = $this->order->payment->status->label();

                                Log::info('PayU: Payment status updated from order-info page', [
                                    'payment_id' => $this->order->payment->id,
                                    'order_id' => $this->order->id,
                                    'payu_order_id' => $payuOrderId,
                                    'payu_status' => $status,
                                    'local_status' => $localStatus,
                                    'order_status' => $orderStatus,
                                    'old_status' => $oldStatus,
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Nie przerywaj wyświetlania strony jeśli sprawdzanie statusu się nie powiodło
                    Log::warning('PayU: Failed to check order status from order-info page', [
                        'order_id' => $this->order->id,
                        'payu_order_id' => $payuOrderId ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
});

?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    {{-- Komunikat sukcesu --}}
    @if ($showPaymentSuccess)
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-green-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">{{ __('Płatność zrealizowana pomyślnie!') }}
                    </h3>
                    <p class="text-green-700">
                        {{ __('Twoje zamówienie zostało opłacone i zostanie zrealizowane w najbliższym czasie.') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Komunikat błędu --}}
    @if (session('order_error') || $orderStatus === __('Płatność nieudana'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Błąd płatności</h3>
                    <p class="text-red-700">
                        {{ session('order_error') ?? 'Płatność nie została zrealizowana. Spróbuj ponownie.' }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($notFound)
        {{-- Komunikat o braku zamówienia z kodem 404 --}}
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center mb-8">
            <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
            <h2 class="text-xl font-semibold text-red-800 mb-2">Nie znaleziono zamówienia</h2>
            <p class="text-red-700 mb-4">Zamówienie o podanym numerze nie zostało znalezione w systemie.</p>
            <p class="text-sm text-red-600 mb-6">Sprawdź czy numer zamówienia został wprowadzony poprawnie.</p>

            {{-- Przycisk do strony głównej --}}
            <a href="{{ route('home') }}"
                class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                Przejdź do strony głównej
            </a>
        </div>

        {{-- Rekomendowane produkty --}}
        @if (!empty($recommendedProducts))
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Polecane produkty</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($recommendedProducts as $product)
                        <livewire:components.product-card :product-id="$product['ID']" :product-name="$product['Nazwa']" :product-price="$product['BruttoValue']"
                            :product-group="$product['Grupa']" :product-weight="$product['MasaBruttoValue']" variant="default" />
                    @endforeach
                </div>
            </div>
        @endif
    @elseif ($enovaOrder || $order)
        {{-- Tytuł strony --}}
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Szczegóły zamówienia</h1>
        </div>

        {{-- Nr zamówienia i data złożenia --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Nr zamówienia</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        @if (!empty($orderNumber))
                            {{ $orderNumber }}
                        @elseif ($order)
                            <span
                                class="text-gray-500 italic">{{ __('Numer zostanie przypisany po rejestracji w systemie') }}</span>
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Data złożenia</h3>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $orderDate ? $orderDate->format('d.m.Y H:i') : ($order ? $order->created_at->format('d.m.Y H:i') : '-') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Dane zamawiającego i faktury w jednym boksie --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="grid md:grid-cols-2 gap-6">
                {{-- Zamawiający --}}
                <div>
                    <h2 class="text-xl font-semibold mb-4 pb-3 border-b">Zamawiający</h2>
                    @if ($enovaOrder)
                        @php
                            $recipient = $enovaOrder->getRecipient();
                        @endphp
                        @if ($recipient)
                            <p class="text-gray-700">
                                <strong class="text-gray-900">{{ $recipient->Nazwa ?? '' }}</strong>
                                @if ($recipient->AdresUlica)
                                    <br>{{ $recipient->AdresUlica }} {{ $recipient->AdresNrDomu ?? '' }}
                                    @if ($recipient->AdresNrLokalu)
                                        / {{ $recipient->AdresNrLokalu }}
                                    @endif
                                @endif
                                @if ($recipient->AdresKodPocztowy && $recipient->AdresMiejscowosc)
                                    <br>{{ formatPostalCode($recipient->AdresKodPocztowy) }}
                                    {{ $recipient->AdresMiejscowosc }}
                                @endif
                                @if ($recipient->AdresPoczta)
                                    <br>{{ $recipient->AdresPoczta }}
                                @endif
                                @if ($enovaOrder->email)
                                    <br><strong>Email:</strong> {{ $enovaOrder->email }}
                                @endif
                                @if ($enovaOrder->phone)
                                    <br><strong>Telefon:</strong> {{ $enovaOrder->phone }}
                                @endif
                            </p>
                        @endif
                    @elseif ($order)
                        <p class="text-gray-700">
                            <strong class="text-gray-900">{{ $order->customer_full_name }}</strong>
                            <br>{{ $order->delivery_street }} {{ $order->delivery_street_number }}
                            @if ($order->delivery_apartment)
                                / {{ $order->delivery_apartment }}
                            @endif
                            <br>{{ formatPostalCode($order->delivery_postal_code) }} {{ $order->delivery_city }}
                            @if ($order->delivery_post_office)
                                <br>{{ $order->delivery_post_office }}
                            @endif
                            <br>{{ $order->delivery_country }}
                            <br><strong>Email:</strong> {{ $order->customer_email }}
                            @if ($order->customer_phone)
                                <br><strong>Telefon:</strong> {{ $order->customer_phone }}
                            @endif
                        </p>
                    @endif
                </div>

                {{-- Dane do faktury (opcjonalnie) --}}
                @if ($enovaOrder)
                    @php
                        $invoiceContractor = $enovaOrder->getInvoiceContractor();
                    @endphp
                    @if ($invoiceContractor)
                        <div>
                            <h2 class="text-xl font-semibold mb-4 pb-3 border-b">Dane do faktury</h2>
                            <p class="text-gray-700">
                                <strong class="text-gray-900">{{ $invoiceContractor->Nazwa ?? '' }}</strong>
                                @if ($invoiceContractor->NIP)
                                    <br><strong>NIP:</strong> {{ $invoiceContractor->NIP }}
                                @endif
                                @if ($invoiceContractor->AdresUlica)
                                    <br>{{ $invoiceContractor->AdresUlica }}
                                    {{ $invoiceContractor->AdresNrDomu ?? '' }}
                                    @if ($invoiceContractor->AdresNrLokalu)
                                        / {{ $invoiceContractor->AdresNrLokalu }}
                                    @endif
                                @endif
                                @if ($invoiceContractor->AdresKodPocztowy && $invoiceContractor->AdresMiejscowosc)
                                    <br>{{ formatPostalCode($invoiceContractor->AdresKodPocztowy) }}
                                    {{ $invoiceContractor->AdresMiejscowosc }}
                                @endif
                                @if ($invoiceContractor->AdresPoczta)
                                    <br>{{ $invoiceContractor->AdresPoczta }}
                                @endif
                            </p>
                        </div>
                    @endif
                @elseif ($order && $order->invoice_required)
                    <div>
                        <h2 class="text-xl font-semibold mb-4 pb-3 border-b">Dane do faktury</h2>
                        <p class="text-gray-700">
                            <strong class="text-gray-900">{{ $order->invoice_company_name }}</strong>
                            <br><strong>NIP:</strong> {{ $order->invoice_nip }}
                            <br>{{ $order->invoice_street }} {{ $order->invoice_street_number }}
                            @if ($order->invoice_apartment)
                                / {{ $order->invoice_apartment }}
                            @endif
                            <br>{{ formatPostalCode($order->invoice_postal_code) }} {{ $order->invoice_city }}
                            @if ($order->invoice_post_office)
                                <br>{{ $order->invoice_post_office }}
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Płatność i dostawa w jednym boksie --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="grid md:grid-cols-2 gap-6">
                {{-- Płatność --}}
                <div>
                    <h2 class="text-xl font-semibold mb-4 pb-3 border-b">Płatność</h2>
                    @if ($enovaOrder)
                        {{-- Dane z Enova - sposób płatności dostępny w systemie Enova --}}
                    @elseif ($order && $order->payment)
                        <div class="grid grid-cols-2 gap-4"
                            style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Sposób płatności') }}</h3>
                                <p class="text-gray-900">
                                    @if ($order->payment->payu_order_id)
                                        PayU
                                    @else
                                        {{ __('Gotówka przy odbiorze') }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                {{-- Status płatności wyświetlamy tylko jeśli to nie gotówka --}}
                                @if ($order->payment->payu_order_id)
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Status płatności') }}
                                    </h3>
                                    @if ($order->payment && $order->payment->isPayu() && !$order->payment->isCompleted())
                                        {{-- Jeśli płatność nie jest zrealizowana, pokaż tylko link --}}
                                        @if ($payuRedirectUri)
                                            {{-- Użyj bezpośredniego linku do PayU jeśli jest dostępny --}}
                                            <a href="{{ $payuRedirectUri }}"
                                                class="text-primary hover:text-primary/80 text-sm font-medium underline">
                                                Zapłać teraz on-line
                                            </a>
                                        @else
                                            {{-- Jeśli nie ma bezpośredniego linku, użyj route do utworzenia nowego zamówienia --}}
                                            <a href="{{ route('order.retry-payment', $order->ext_order_id) }}"
                                                class="text-primary hover:text-primary/80 text-sm font-medium underline">
                                                Zapłać teraz on-line
                                            </a>
                                        @endif
                                    @else
                                        {{-- Jeśli płatność jest zrealizowana, pokaż status --}}
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        @if ($paymentStatus === __('Opłacone')) bg-green-100 text-green-800
                                        @elseif($paymentStatus === __('Oczekuje na potwierdzenie')) bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                            {{ $paymentStatus ?? 'Oczekuje na płatność' }}
                                        </span>
                                    @endif
                                @else
                                    {{-- Dla gotówki pozostaw puste miejsce, aby zachować układ kolumn --}}
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Dostawa --}}
                <div>
                    <h2 class="text-xl font-semibold mb-4 pb-3 border-b">Dostawa</h2>
                    <div class="space-y-2">
                        @if ($enovaOrder)
                            @php
                                $deliveryPosition = null;
                                $positions = $enovaOrder->positions;
                                if ($positions) {
                                    foreach ($positions as $pos) {
                                        $productId = $pos->Towar ?? null;
                                        $product = null;
                                        $kod = '';
                                        try {
                                            $product = $productId ? Product::getCachedById($productId) : null;
                                            if ($product) {
                                                $kod = strtolower($product['Kod'] ?? '');
                                            }
                                        } catch (\Exception $e) {
                                            // Ignoruj błędy
                                            $product = null;
                                        }
                                        $isDelivery =
                                            !empty($kod) &&
                                            (str_contains($kod, 'przes') || str_contains($kod, 'dostaw'));
                                        if ($isDelivery) {
                                            $deliveryPosition = $pos;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            @if ($deliveryPosition && $product)
                                <p class="text-gray-900">
                                    <strong>{{ $product->Nazwa ?? 'Dostawa' }}</strong>
                                </p>
                            @endif
                            {{-- Wyciągnij dane paczkomatu z uwag (gdy dane z Enova) --}}
                            @php
                                $parcelLockerFromNotes = null;
                                if ($enovaOrder->notes && stripos($enovaOrder->notes, 'Paczkomat:') !== false) {
                                    // Format: "Paczkomat: name, address.line1, address.line2"
                                    preg_match('/Paczkomat:\s*(.+?)(?:\n|$)/i', $enovaOrder->notes, $matches);
                                    if (!empty($matches[1])) {
                                        $parts = array_map('trim', explode(',', $matches[1]));
                                        if (count($parts) >= 3) {
                                            $parcelLockerFromNotes = [
                                                'name' => $parts[0],
                                                'address' => [
                                                    'line1' => $parts[1],
                                                    'line2' => $parts[2],
                                                ],
                                            ];
                                        }
                                    }
                                }
                            @endphp
                            @if ($parcelLockerFromNotes)
                                <p class="text-sm text-gray-700 mt-2">
                                    <strong>{{ $parcelLockerFromNotes['name'] }}</strong>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $parcelLockerFromNotes['address']['line1'] }}
                                    @if (!empty($parcelLockerFromNotes['address']['line2']))
                                        <br>{{ $parcelLockerFromNotes['address']['line2'] }}
                                    @endif
                                </p>
                            @endif
                        @elseif ($order)
                            <p class="text-gray-900">
                                <strong>{{ $order->delivery_name }}</strong>
                            </p>
                            @if ($order->parcel_locker_data)
                                @php
                                    $locker = $order->parcel_locker_data;
                                @endphp
                                <p class="text-sm text-gray-700">
                                    <strong>{{ $locker['name'] ?? '' }}</strong>
                                </p>
                                @if (isset($locker['address']))
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $locker['address']['line1'] ?? '' }}
                                        @if (isset($locker['address']['line2']))
                                            <br>{{ $locker['address']['line2'] }}
                                        @endif
                                    </p>
                                @endif
                            @endif
                            {{-- Uwagi są dla wewnętrznej informacji - nie wyświetlamy --}}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Produkty --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold">Produkty</h2>
            </div>
            @if ($enovaOrder)
                @php
                    // Użyj relacji positions() - model EnovaOrderPosition ma już prawidłową nazwę tabeli
                    $positions = $enovaOrder->positions;
                    $subtotal = 0;
                    $deliveryPosition = null;

                    // Oblicz subtotal (bez dostawy) i znajdź pozycję dostawy
                    if ($positions) {
                        foreach ($positions as $pos) {
                            $productId = $pos->Towar ?? null;

                            // Spróbuj pobrać produkt aby sprawdzić kod
                            $product = null;
                            $kod = '';
                            if ($productId) {
                                try {
                                    $product = Product::getCachedById($productId);
                                    if ($product) {
                                        $kod = strtolower($product['Kod'] ?? '');
                                    }
                                } catch (\Exception $e) {
                                    // Ignoruj błędy
                                    $product = null;
                                }
                            }

                            $isDelivery = !empty($kod) && (str_contains($kod, 'przes') || str_contains($kod, 'dostaw'));

                            if ($isDelivery) {
                                $deliveryPosition = $pos;
                            } else {
                                // Tylko produkty (bez dostawy) liczą się do subtotal
                                $price = $pos->CenaValue ?? 0;
                                $quantity = (int) ($pos->IloscValue ?? 1); // Ilość jako liczba całkowita
                                $subtotal += $price * $quantity;
                            }
                        }
                    }
                @endphp
                @if ($positions && $positions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Produkt
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cena jednostkowa
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ilość
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Wartość
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($positions as $position)
                                    @php
                                        // Pobierz dane z pozycji
                                        $productId = $position->Towar ?? null;
                                        $price = $position->CenaValue ?? 0;
                                        $quantity = (int) ($position->IloscValue ?? 1); // Ilość jako liczba całkowita
                                        $total = $price * $quantity;

                                        // Spróbuj pobrać produkt z cache
                                        $product = null;
                                        try {
                                            $product = $productId ? Product::getCachedById($productId) : null;
                                        } catch (\Exception $e) {
                                            // Ignoruj błędy
                                            $product = null;
                                        }

                                        // Pobierz nazwę produktu
                                        $productName = 'Produkt';
                                        $productKod = '';
                                        if ($product) {
                                            // Produkt z cache jest tablicą, nazwa jest już w 'Nazwa'
                                            $productName = $product['Nazwa'] ?? 'Produkt';
                                            $productKod = strtolower($product['Kod'] ?? '');
                                        }

                                        // Pobierz obraz produktu
                                        $imagePath = null;
                                        if ($productId) {
                                            $imagePath = 'img/towary/' . $productId . '_200x120.jpg';
                                        }

                                        // Sprawdź czy to dostawa (po kodzie produktu)
                                        $isDelivery =
                                            !empty($productKod) &&
                                            (str_contains($productKod, 'przes') || str_contains($productKod, 'dostaw'));
                                    @endphp
                                    @if (!$isDelivery)
                                        <tr>
                                            {{-- Produkt --}}
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-16 w-16">
                                                        <img class="h-16 w-16 rounded object-cover"
                                                            src="{{ $imagePath && file_exists(public_path($imagePath)) ? asset($imagePath) : asset('img/towary/placeholder.jpg') }}"
                                                            alt="{{ $productName }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            @if ($product)
                                                                <a href="{{ route('product', [$product->ID, Str::slug($productName)]) }}"
                                                                    class="hover:text-primary">{{ $productName }}</a>
                                                            @else
                                                                {{ $productName }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Cena jednostkowa --}}
                                            <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                                {{ number_format($price, 2, ',', '.') }} zł
                                            </td>

                                            {{-- Ilość --}}
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <span class="font-medium text-md">{{ (int) $quantity }}</span>
                                            </td>

                                            {{-- Wartość --}}
                                            <td class="px-6 py-4 text-right text-md whitespace-nowrap font-medium">
                                                {{ number_format($total, 2, ',', '.') }} zł
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Koszt dostawy dla Enova --}}
                    @php
                        // Znajdź pozycję dostawy
                        $deliveryPosition = null;
                        if ($positions) {
                            foreach ($positions as $pos) {
                                $productId = $pos->Towar ?? null;
                                $product = null;
                                $kod = '';
                                try {
                                    $product = $productId ? Product::getCachedById($productId) : null;
                                    if ($product) {
                                        $kod = strtolower($product['Kod'] ?? '');
                                    }
                                } catch (\Exception $e) {
                                    // Ignoruj błędy
                                    $product = null;
                                }
                                $isDelivery =
                                    !empty($kod) && (str_contains($kod, 'przes') || str_contains($kod, 'dostaw'));
                                if ($isDelivery) {
                                    $deliveryPosition = $pos;
                                    break;
                                }
                            }
                        }
                        // Oblicz koszt dostawy
                        $deliveryCost = 0;
                        $deliveryPrice = 0;
                        $deliveryName = 'Dostawa';
                        if ($deliveryPosition) {
                            $deliveryPrice = $deliveryPosition->CenaValue ?? 0;
                            $deliveryCost = $deliveryPrice * (int) ($deliveryPosition->IloscValue ?? 1);
                            // Spróbuj pobrać nazwę produktu dostawy
                            if ($productId = $deliveryPosition->Towar ?? null) {
                                try {
                                    $deliveryProduct = Product::getCachedById($productId);
                                    if ($deliveryProduct) {
                                        // Nazwa produktu jest już w cache (productNameFeature)
                                        $deliveryName = $deliveryProduct['Nazwa'] ?? 'Dostawa';
                                    }
                                } catch (\Exception $e) {
                                    // Ignoruj błędy
                                    $deliveryProduct = null;
                                }
                            }
                        }
                        // Wyciągnij informacje o promocji z uwag
                        $promotionCodeFromNotes = null;
                        $promotionDiscountFromNotes = 0;
                        if ($enovaOrder->notes && stripos($enovaOrder->notes, 'Promocja:') !== false) {
                            // Format: "Promocja: CODE (nazwa) - zniżka: X,XX zł"
                            if (preg_match('/Promocja:\s*([A-Z0-9]+)/i', $enovaOrder->notes, $codeMatch)) {
                                $promotionCodeFromNotes = strtoupper(trim($codeMatch[1]));
                            }
                            // Wyciągnij kwotę zniżki
                            if (preg_match('/zniżka:\s*([0-9,\.]+)\s*zł/i', $enovaOrder->notes, $discountMatch)) {
                                $promotionDiscountFromNotes = (float) str_replace(',', '.', $discountMatch[1]);
                            }
                        }

                        // Oblicz razem (uwzględniając zniżkę z promocji jeśli jest)
                        $sumaBrutto = $enovaOrder->SumaBrutto ?? ($subtotal ?? 0);
                        $total = $sumaBrutto + $deliveryCost - $promotionDiscountFromNotes;
                        // Sprawdź czy jest dostawa (jeśli nie ma deliveryPosition, to odbiór osobisty)
                        $hasDelivery = $deliveryPosition !== null;
                    @endphp
                    {{-- Podsumowanie produktów - tylko jeśli jest dostawa (nie odbiór osobisty) --}}
                    @if ($hasDelivery)
                        <div class="p-6 border-t bg-gray-50">
                            <div class="flex justify-between text-sm text-gray-600 font-semibold">
                                <span>Wartość produktów:</span>
                                <span>{{ number_format($subtotal ?? 0, 2, ',', '.') }}
                                    zł</span>
                            </div>
                        </div>
                    @endif
                    {{-- Koszt dostawy - pokazuj jeśli jest dostawa (nawet jeśli darmowa) --}}
                    @if ($hasDelivery)
                        <div class="p-6 border-t bg-white">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Dostawa ({{ $deliveryName }}):</span>
                                <span class="font-medium">{{ number_format($deliveryCost, 2, ',', '.') }} zł</span>
                            </div>
                        </div>
                    @endif
                    {{-- Zniżka z promocji (gdy dane z Enova) --}}
                    @if ($promotionCodeFromNotes && $promotionDiscountFromNotes > 0)
                        <div class="p-6 border-t bg-white">
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Zniżka ({{ $promotionCodeFromNotes }}):</span>
                                <span
                                    class="font-medium">-{{ number_format($promotionDiscountFromNotes, 2, ',', '.') }}
                                    zł</span>
                            </div>
                        </div>
                    @endif
                    {{-- Razem --}}
                    <div class="p-6 border-t bg-gray-50">
                        <div class="flex justify-between text-lg font-semibold text-gray-900">
                            <span>Razem:</span>
                            <span>{{ number_format($total, 2, ',', '.') }} zł</span>
                        </div>
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        <p>Brak pozycji w zamówieniu.</p>
                    </div>
                @endif
            @elseif ($order && $order->items)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Produkt
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cena jednostkowa
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ilość
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Wartość
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($order->items as $productId => $item)
                                <tr>
                                    {{-- Produkt --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-16 w-16">
                                                <img class="h-16 w-16 rounded object-cover"
                                                    src="{{ file_exists(public_path('img/towary/' . ($item['image'] ?? $productId . '_200x120.jpg'))) ? asset('img/towary/' . ($item['image'] ?? $productId . '_200x120.jpg')) : asset('img/towary/placeholder.jpg') }}"
                                                    alt="{{ $item['name'] ?? 'Produkt' }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    @if (isset($item['id']))
                                                        <a href="{{ route('product', [$item['id'], Str::slug($item['name'] ?? 'produkt')]) }}"
                                                            class="hover:text-primary">{{ $item['name'] ?? 'Produkt' }}</a>
                                                    @else
                                                        {{ $item['name'] ?? 'Produkt' }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Cena jednostkowa --}}
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                        {{ number_format($item['price'] ?? 0, 2, ',', '.') }} zł
                                    </td>

                                    {{-- Ilość --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="font-medium text-md">{{ (int) ($item['quantity'] ?? 1) }}</span>
                                    </td>

                                    {{-- Wartość --}}
                                    <td class="px-6 py-4 text-right text-md whitespace-nowrap font-medium">
                                        {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2, ',', '.') }}
                                        zł
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @php
                    // Sprawdź czy jest dostawa (jeśli nie ma delivery_name, to odbiór osobisty)
                    $hasDelivery = !empty($order->delivery_name);
                @endphp
                {{-- Podsumowanie produktów - tylko jeśli jest dostawa (nie odbiór osobisty) --}}
                @if ($hasDelivery)
                    <div class="p-6 border-t bg-gray-50">
                        <div class="flex justify-between text-sm text-gray-600 font-semibold">
                            <span>Wartość produktów:</span>
                            <span>{{ number_format($order->subtotal, 2, ',', '.') }} zł</span>
                        </div>
                    </div>
                @endif
                {{-- Koszt dostawy - pokazuj jeśli jest dostawa (nawet jeśli darmowa) --}}
                @if ($hasDelivery)
                    <div class="p-6 border-t bg-white">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Dostawa ({{ $order->delivery_name }}):</span>
                            <span class="font-medium">{{ number_format($order->delivery_cost ?? 0, 2, ',', '.') }}
                                zł</span>
                        </div>
                    </div>
                @endif
                {{-- Zniżka z promocji --}}
                @if ($order->discount_amount > 0 && $order->promotion_code)
                    <div class="p-6 border-t bg-white">
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Zniżka ({{ $order->promotion_code }}):</span>
                            <span class="font-medium">-{{ number_format($order->discount_amount, 2, ',', '.') }}
                                zł</span>
                        </div>
                    </div>
                @endif
                {{-- Razem --}}
                <div class="p-6 border-t bg-gray-50">
                    <div class="flex justify-between text-lg font-semibold text-gray-900">
                        <span>Razem:</span>
                        <span>{{ number_format($order->total, 2, ',', '.') }} zł</span>
                    </div>
                </div>
            @else
                <div class="p-6 text-center text-gray-500">
                    <p>Dane produktów dostępne w systemie Enova.</p>
                </div>
            @endif

        </div>
    @endif
</div>

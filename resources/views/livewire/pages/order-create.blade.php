<?php

use function Livewire\Volt\{state, mount, layout, action};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Services\CartService;
use App\Services\PayuService;
use App\Services\EnovaXmlService;
use App\Services\PromotionService;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Content;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderCreated;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\JsonLd;

layout('layouts.app');

// SEO Meta Tags
app('seotools')->setCanonical(url('/zamawianie'));
app('seotools')->opengraph()->setUrl(url('/zamawianie'));
app('seotools.json-ld')->setType('WebPage')->addValue('url', url('/zamawianie'))->addValue('name', 'Zamawianie - Zdrowe Herbaty BIFIX')->addValue('description', 'Złóż zamówienie w sklepu Zdrowe Herbaty BIFIX. Wypełnij dane dostawy i wybierz sposób płatności.');

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
    'selectedPaymentGuid' => null,
    'selectedPayuOption' => null,
    'notes' => '',
    'parcelLockerData' => null,
    'acceptedTerms' => false,
    'termsContent' => null,
    'appliedPromotion' => null,
    'promotionDiscount' => 0,
    'currentStep' => 1, // Aktualny krok walidacji: 1=dane, 2=dostawa, 3=płatność, 4=regulamin
    'stepErrors' => [
        'step1' => [],
        'step2' => [],
        'step3' => [],
        'step4' => [],
    ],
]);

// Reguły walidacji - KROK 1: Dane zamawiającego + faktura
$rulesStep1 = [
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
    'invoiceData.city' => 'nullable|string|max:255',
    'invoiceData.postal_code' => 'required_if:customerData.invoice_required,true|string|max:10',
    'invoiceData.post_office' => 'required_if:customerData.invoice_required,true|string|max:255',
];

// Reguły walidacji - KROK 2: Wybór dostawy
$rulesStep2 = [
    'selectedDelivery' => 'required|integer',
];

// Reguły walidacji - KROK 3: Wybór płatności
$rulesStep3 = [
    'selectedPayment' => 'required|string',
];

// Reguły walidacji - KROK 4: Akceptacja regulaminu
$rulesStep4 = [
    'acceptedTerms' => 'required|accepted',
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
    'invoiceData.postal_code.required_if' => 'Kod pocztowy jest wymagany dla faktury.',
    'invoiceData.post_office.required_if' => 'Poczta jest wymagana dla faktury.',
    'selectedDelivery.required' => 'Wybierz opcję dostawy.',
    'selectedPayment.required' => 'Wybierz sposób płatności.',
    'acceptedTerms.required' => 'Musisz zaakceptować regulamin, aby złożyć zamówienie.',
    'acceptedTerms.accepted' => 'Musisz zaakceptować regulamin, aby złożyć zamówienie.',
    'parcel_locker' => 'Wybierz paczkomat dla dostawy do paczkomatu.',
];

// Funkcje walidacji dla każdego kroku
$validateStep1 = action(function () use ($rulesStep1, $messages) {
    $this->stepErrors['step1'] = [];
    try {
        $this->validate($rulesStep1, $messages);
        return true;
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->stepErrors['step1'] = $e->errors();
        $this->currentStep = 1;

        // Sprawdź czy są tylko błędy faktury - jeśli tak, przewiń do sekcji faktury
        $hasCustomerErrors = false;
        $hasInvoiceErrors = false;
        foreach ($e->errors() as $field => $messages) {
            if (str_starts_with($field, 'invoiceData.')) {
                $hasInvoiceErrors = true;
            } else {
                $hasCustomerErrors = true;
            }
        }

        // Jeśli są tylko błędy faktury, przewiń do sekcji faktury
        if ($hasInvoiceErrors && !$hasCustomerErrors) {
            $this->dispatch('scroll-to-invoice');
        } else {
            // W przeciwnym razie przewiń do góry sekcji
            $this->dispatch('scroll-to-step', step: 1);
        }

        return false;
    }
});

$validateStep2 = action(function () use ($rulesStep2, $messages) {
    $this->stepErrors['step2'] = [];
    try {
        $this->validate($rulesStep2, $messages);

        // Walidacja paczkomatu - jeśli wybrano dostawę do paczkomatu, sprawdź czy wskazano paczkomat
        // Sprawdzamy przez nazwę dostawy (jak w starym systemie - stripos na nazwie)
        if ($this->selectedDelivery) {
            $selectedDeliveryOption = collect($this->deliveryOptions)->firstWhere('id', (int) $this->selectedDelivery);

            if ($selectedDeliveryOption) {
                // Sprawdź czy nazwa dostawy zawiera frazę paczkomatu (jak w starym systemie)
                $deliveryName = strtolower($selectedDeliveryOption['name'] ?? '');
                $parcelLockerName = strtolower(config('enova.delivery.parcel_locker_name', 'Paczkomaty 24/7'));
                $isParcelLocker = str_contains($deliveryName, $parcelLockerName);

                if ($isParcelLocker) {
                    // Sprawdź czy są dane paczkomatu w hidden input (parcelLockerData)
                    $parcelLockerData = $this->parcelLockerData;

                    // Jeśli to string JSON, zdekoduj
                    if (is_string($parcelLockerData)) {
                        $parcelLockerData = json_decode($parcelLockerData, true);
                    }

                    // Sprawdź czy dane są poprawne
                    if (empty($parcelLockerData) || !is_array($parcelLockerData) || empty($parcelLockerData['name'])) {
                        $this->stepErrors['step2']['parcel_locker'] = ['Wybierz paczkomat dla dostawy do paczkomatu.'];
                        $this->currentStep = 2;
                        $this->dispatch('scroll-to-step', step: 2);
                        return false;
                    }
                }
            }
        }

        return true;
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->stepErrors['step2'] = $e->errors();
        $this->currentStep = 2;
        $this->dispatch('scroll-to-step', step: 2);
        return false;
    }
});

$validateStep3 = action(function () use ($rulesStep3, $messages) {
    $this->stepErrors['step3'] = [];
    try {
        $this->validate($rulesStep3, $messages);
        return true;
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->stepErrors['step3'] = $e->errors();
        $this->currentStep = 3;
        $this->dispatch('scroll-to-step', step: 3);
        return false;
    }
});

$validateStep4 = action(function () use ($rulesStep4, $messages) {
    $this->stepErrors['step4'] = [];
    try {
        $this->validate($rulesStep4, $messages);
        return true;
    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->stepErrors['step4'] = $e->errors();
        $this->currentStep = 4;
        $this->dispatch('scroll-to-step', step: 4);
        return false;
    }
});

mount(function () {
    $this->loadCart();

    // Pobierz regulamin z bazy danych
    $this->termsContent = Content::getTerms('regulamin');

    // Pobierz kod promocyjny z sesji i oblicz zniżkę
    $promotionCode = session('promotion_code');
    if ($promotionCode) {
        $promotionService = app(PromotionService::class);
        $promotion = $promotionService->findByCode($promotionCode);

        if ($promotion) {
            // Przygotuj dane koszyka
            $cartItems = [];
            foreach ($this->cart['items'] ?? [] as $productId => $item) {
                $cartItems[] = [
                    'id' => $item['id'] ?? $productId,
                    'group' => $item['group_clean_name'] ?? null, // clean_name grupy produktu (dla promocji)
                    'price' => $item['price'] ?? 0,
                    'quantity' => $item['quantity'] ?? 1,
                ];
            }
            $cartTotal = $this->cart['total'] ?? 0;

            // Waliduj promocję
            $validation = $promotionService->validatePromotion($promotion, $cartItems, $cartTotal);

            if ($validation['valid']) {
                $this->appliedPromotion = $promotion;
                $this->promotionDiscount = $promotionService->calculateDiscount($promotion, $cartItems, $cartTotal);
            } else {
                // Kod nieprawidłowy - usuń z sesji
                session()->forget('promotion_code');
            }
        } else {
            // Kod nie istnieje - usuń z sesji
            session()->forget('promotion_code');
        }
    }

    // Wczytaj dane klienta z cookies
    $savedCustomerData = Cookie::get('savedCustomerData');
    if ($savedCustomerData) {
        try {
            $data = json_decode($savedCustomerData, true);
            if (is_array($data) && (isset($data['first_name']) || isset($data['email']))) {
                $this->customerData = array_merge($this->customerData, $data);
            }
        } catch (\Exception $e) {
            // Ignoruj błędy parsowania
        }
    }

    // Wczytaj dane faktury z cookies
    $savedInvoiceData = Cookie::get('savedInvoiceData');
    if ($savedInvoiceData) {
        try {
            $data = json_decode($savedInvoiceData, true);
            if (is_array($data) && (isset($data['company_name']) || isset($data['nip']))) {
                $this->invoiceData = array_merge($this->invoiceData, $data);
            }
        } catch (\Exception $e) {
            // Ignoruj błędy parsowania
        }
    }
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

// Funkcja do zapisywania danych klienta w cookies
$saveCustomerData = function () {
    $data = json_encode($this->customerData);
    $this->dispatch('save-customer-data', data: $data);
};

// Funkcja do wczytywania danych klienta z cookies
$loadCustomerData = function () {
    $this->dispatch('load-customer-data');
};

// Funkcja do zapisywania danych faktury w cookies
$saveInvoiceData = function () {
    $data = json_encode($this->invoiceData);
    $this->dispatch('save-invoice-data', data: $data);
};

// Funkcja do wczytywania danych faktury z cookies
$loadInvoiceData = function () {
    $this->dispatch('load-invoice-data');
};

$loadCart = function () {
    $cartService = app(CartService::class);
    $this->cart = $cartService->getCart();

    // Nie przekierowuj do koszyka, jeśli użytkownik wraca z PayU (ma komunikat w sesji)
    // Pozwól mu zobaczyć komunikat sukcesu/błędu płatności
    if (empty($this->cart['items']) && !session('order_success') && !session('order_error') && !session('message')) {
        return redirect()->route('cart');
    }

    // Ładowanie opcji dostawy na podstawie wagi koszyka (tylko jeśli koszyk nie jest pusty)
    if (!empty($this->cart['items'])) {
        $this->loadDeliveryOptions();
    }
};

$loadDeliveryOptions = function () {
    $cartWeight = $this->cart['total_weight'] ?? 0;

    // Używamy tego samego zapytania co na podstronie dostawy
    $deliveries = Delivery::join('Ceny', 'Towary.ID', '=', 'Ceny.Towar')
        ->join('Features as PaymentFeatures', function ($join) {
            $join->on('Towary.ID', '=', 'PaymentFeatures.Parent')->where('PaymentFeatures.Name', '=', config('enova.payment.feature_payment_method'));
        })
        ->leftJoin('SposobyZaplaty', function ($join) {
            $join->on(DB::raw('LTRIM(RTRIM(PaymentFeatures.Data))'), '=', DB::raw('CAST(SposobyZaplaty.ID AS NVARCHAR(255))'));
        })
        ->where('Towary.MasaBruttoValue', '>=', $cartWeight)
        ->where('Ceny.Definicja', config('enova.prices.definition'))
        ->orderBy('MasaBruttoValue')
        ->orderBy('Ceny.BruttoValue')
        ->select(['Towary.ID', 'Towary.Nazwa', 'Towary.Opis', 'Towary.MasaBruttoValue', 'Ceny.BruttoValue', 'SposobyZaplaty.Nazwa as PaymentMethodName', DB::raw('CONVERT(VARCHAR(36), SposobyZaplaty.Guid) as PaymentMethodGuid'), 'PaymentFeatures.Data as PaymentMethodId'])
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

            // Pobierz próg bezpłatnej dostawy z promocji w bazie
            $freeDeliveryPromotion = \App\Models\Promotion::where('type', 'automatic')
                ->where('discount_type', 'free_delivery')
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('valid_from')->orWhere('valid_from', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('valid_to')->orWhere('valid_to', '>=', now());
                })
                ->first();

            $freeThreshold = $freeDeliveryPromotion && $freeDeliveryPromotion->min_order_amount ? (float) $freeDeliveryPromotion->min_order_amount : 0;
            $isFree = $cartTotal >= $freeThreshold && $freeThreshold > 0;
            $price = $isFree ? 0 : $delivery->BruttoValue;

            return [
                'id' => $delivery->ID,
                'name' => $delivery->Nazwa,
                'description' => $delivery->Opis,
                'max_weight' => $delivery->MasaBruttoValue,
                'price' => $price,
                'payment_method' => $delivery->PaymentMethodName ?? '-',
                'payment_method_id' => isset($delivery->PaymentMethodId) ? trim($delivery->PaymentMethodId) : null,
                'payment_method_guid' => $delivery->PaymentMethodGuid ?? null,
                'is_parcel_locker' => $isParcelLocker,
                'is_free' => $isFree,
            ];
        })
        ->toArray();
};

$getPayuOptions = function () {
    $payuOptions = config('enova.payment.payu.options', []);

    return [
        [
            'code' => 'payu_blik',
            'label' => 'BLIK',
            'payu_option' => $payuOptions['blik'] ?? 'blik',
            'logo' => 'https://static.payu.com/images/mobile/logos/pbl_blik.png',
        ],
        [
            'code' => 'payu_card',
            'label' => 'Płatność online kartą płatniczą',
            'payu_option' => $payuOptions['card'] ?? 'c',
            'logo' => 'https://static.payu.com/images/mobile/logos/pbl_c.png',
        ],
        [
            'code' => 'payu_google_pay',
            'label' => 'Google Pay',
            'payu_option' => $payuOptions['google_pay'] ?? 'ap',
            'logo' => 'https://static.payu.com/images/mobile/logos/pbl_ap.png',
        ],
        [
            'code' => 'payu_apple_pay',
            'label' => 'Apple Pay',
            'payu_option' => $payuOptions['apple_pay'] ?? 'jp',
            'logo' => 'https://static.payu.com/images/mobile/logos/pbl_jp.png',
        ],
        [
            'code' => 'payu_transfer',
            'label' => 'Szybki przelew',
            'payu_option' => $payuOptions['transfer'] ?? 'przelew',
            'logo' => null, // PayU nie udostępnia dedykowanego logo dla przelewu - użyj lokalnego pliku lub zostaw puste
        ],
    ];
};

$resolvePaymentDefinition = function (array $delivery) use ($getPayuOptions) {
    $guid = strtoupper((string) ($delivery['payment_method_guid'] ?? ''));
    $rawName = $delivery['payment_method'] ?? '';

    // GUID == przedpłata → opcje PayU
    $przedplataGuid = strtoupper((string) config('enova.payment.methods.przedplata'));
    if ($guid && $przedplataGuid && $guid === $przedplataGuid) {
        $payuOptions = $getPayuOptions();
        // Dodaj GUID do każdej opcji
        return array_map(function ($option) use ($guid, $rawName) {
            return array_merge($option, [
                'guid' => $guid,
                'raw_name' => $rawName,
            ]);
        }, $payuOptions);
    }

    // Wszystkie inne to gotówka przy odbiorze
    return [
        [
            'code' => 'cash',
            'label' => 'Gotówka przy odbiorze',
            'guid' => $guid ?: null,
            'raw_name' => $rawName,
        ],
    ];
};

$updatedSelectedDelivery = function ($value) use ($resolvePaymentDefinition) {
    if (empty($value)) {
        $this->paymentOptions = [];
        $this->selectedPayment = null;
        $this->selectedPaymentGuid = null;
        $this->selectedPayuOption = null;
        $this->parcelLockerData = null;

        return;
    }

    $delivery = collect($this->deliveryOptions)->firstWhere('id', (int) $value);

    if (!$delivery) {
        $this->paymentOptions = [];
        $this->selectedPayment = null;
        $this->selectedPaymentGuid = null;
        $this->selectedPayuOption = null;
        $this->parcelLockerData = null;

        return;
    }

    // Sprawdź czy to paczkomat i automatycznie załaduj dane z cookies (jak w starym systemie)
    $deliveryName = strtolower($delivery['name'] ?? '');
    $parcelLockerName = strtolower(config('enova.delivery.parcel_locker_name', 'Paczkomaty 24/7'));
    $isParcelLocker = str_contains($deliveryName, $parcelLockerName);

    if ($isParcelLocker) {
        // Pobierz dane paczkomatu z cookies (jak w starym systemie - zawsze ładujemy jeśli istnieje)
        // W starym systemie: if ($paczkomat = $_COOKIE['paczkomat']) $this->view->assign('paczkomat', json_decode($paczkomat));
        $cookieData = request()->cookie('selectedParcelLocker');
        if (!empty($cookieData)) {
            try {
                // Cookie jest zapisywane jako JSON string (JSON.stringify w JavaScript)
                $parcelLocker = json_decode($cookieData, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($parcelLocker['name'])) {
                    // Ustaw parcelLockerData jako tablicę - będzie dostępne dla walidatora i formularza (jak ukryte pole w starym systemie)
                    $this->parcelLockerData = $parcelLocker;
                } else {
                    $this->parcelLockerData = null;
                }
            } catch (\Exception $e) {
                $this->parcelLockerData = null;
            }
        } else {
            // Brak cookie - sprawdź czy może być już ustawione w parcelLockerData (z wire:model)
            // Jeśli nie, wyczyść
            if (empty($this->parcelLockerData)) {
                $this->parcelLockerData = null;
            }
        }
    } else {
        // To nie paczkomat - wyczyść dane
        $this->parcelLockerData = null;
    }

    $options = $resolvePaymentDefinition($delivery);
    $this->paymentOptions = $options;

    // Jeśli jest tylko jedna opcja płatności, zaznacz ją automatycznie
    if (count($options) === 1) {
        $firstOption = $options[0];
        $this->selectedPayment = $firstOption['code'] ?? null;
        $this->selectedPaymentGuid = $firstOption['guid'] ?? null;
        $this->selectedPayuOption = $firstOption['payu_option'] ?? null;

        // Wymuś odświeżenie widoku, aby zaznaczyć checkbox
        $this->dispatch('$refresh');
    } else {
        // Jeśli jest więcej opcji, nie zaznaczaj żadnej - użytkownik musi wybrać
        $this->selectedPayment = null;
        $this->selectedPaymentGuid = $options[0]['guid'] ?? null;
        $this->selectedPayuOption = null;
    }
};

$updatedSelectedPayment = function ($value) {
    $option = collect($this->paymentOptions)->firstWhere('code', $value);
    $this->selectedPaymentGuid = $option['guid'] ?? null;
    $this->selectedPayuOption = $option['payu_option'] ?? null;
};

// Funkcja pomocnicza do generowania GUID
$generateGuid = function () {
    mt_srand((float) microtime() * 10000);
    $charid = strtoupper(md5(uniqid(rand(), true)));
    return substr($charid, 0, 8) . '-' . substr($charid, 8, 4) . '-' . substr($charid, 12, 4) . '-' . substr($charid, 16, 4) . '-' . substr($charid, 20, 12);
};

// Funkcja pomocnicza do zapisywania zamówienia
// Przekazujemy wszystkie potrzebne dane jako parametry, ponieważ $this nie jest dostępne w closure
$saveOrder = function ($extOrderId, $paymentMethodGuid, $payuOrderId, $component) {
    $selectedDeliveryOption = collect($component->deliveryOptions)->firstWhere('id', $component->selectedDelivery);
    $deliveryPrice = $selectedDeliveryOption['price'] ?? 0;
    $subtotal = $component->cart['total'] ?? 0;
    $promotionDiscount = $component->promotionDiscount ?? 0;
    $total = max(0, $subtotal + $deliveryPrice - $promotionDiscount);

    // Przygotuj uwagi (np. informacje o paczkomacie i promocjach)
    $notes = '';
    if (!empty($component->notes)) {
        $notes = $component->notes;
    }

    // Pobierz dane paczkomatu tylko jeśli wybrano dostawę do paczkomatu (sprawdzamy nazwę dostawy jak w starym sklepie)
    $parcelLockerData = null;
    $deliveryName = $selectedDeliveryOption['name'] ?? '';
    $parcelLockerName = config('enova.delivery.parcel_locker_name', 'Paczkomaty 24/7');
    $isParcelLocker = stripos($deliveryName, $parcelLockerName) !== false;

    // Buduj uwagi w strukturze ułatwiającej późniejsze odczytanie
    $notesParts = [];

    // 1. Paczkomat (jeśli jest)
    if ($isParcelLocker && !empty($component->parcelLockerData)) {
        $parcelLockerData = is_string($component->parcelLockerData) ? json_decode($component->parcelLockerData, true) : $component->parcelLockerData;

        if (is_array($parcelLockerData) && !empty($parcelLockerData['name'])) {
            $notesParts[] = 'Paczkomat: ' . ($parcelLockerData['name'] ?? '') . ', ' . ($parcelLockerData['address']['line1'] ?? '') . ', ' . ($parcelLockerData['address']['line2'] ?? '');
        }
    }

    // 2. Promocje (jeśli są)
    if ($component->appliedPromotion) {
        $promotionInfo = 'Promocja: ' . $component->appliedPromotion->code . ' (' . $component->appliedPromotion->name . ')' . ' - zniżka: ' . number_format($promotionDiscount, 2, ',', '.') . ' zł';
        $notesParts[] = $promotionInfo;
    }

    // 3. Uwagi klienta (jeśli są)
    if (!empty($notes)) {
        $notesParts[] = $notes;
    }

    // Połącz wszystkie części z podwójnym znakiem nowej linii dla czytelności
    $notes = implode("\n\n", $notesParts);

    // Zapisz zamówienie (płatność to osobna operacja, można ją ponowić)
    $order = Order::create([
        'ext_order_id' => $extOrderId,
        'status' => OrderStatus::SUBMITTED->value, // Złożone, oczekuje na synchronizację z Enova
        'customer_first_name' => $component->customerData['first_name'],
        'customer_last_name' => $component->customerData['last_name'],
        'customer_email' => $component->customerData['email'],
        'customer_phone' => $component->customerData['phone'] ?? null,
        'delivery_street' => $component->customerData['street'],
        'delivery_street_number' => $component->customerData['street_number'],
        'delivery_apartment' => $component->customerData['apartment'] ?? null,
        'delivery_city' => $component->customerData['city'] ?? null,
        'delivery_postal_code' => $component->customerData['postal_code'],
        'delivery_post_office' => $component->customerData['post_office'],
        'delivery_country' => $component->customerData['country'] ?? 'Polska',
        'invoice_required' => $component->customerData['invoice_required'] ?? false,
        'invoice_company_name' => $component->invoiceData['company_name'] ?? null,
        'invoice_nip' => $component->invoiceData['nip'] ?? null,
        'invoice_street' => $component->invoiceData['street'] ?? null,
        'invoice_street_number' => $component->invoiceData['street_number'] ?? null,
        'invoice_apartment' => $component->invoiceData['apartment'] ?? null,
        'invoice_city' => $component->invoiceData['city'] ?? null,
        'invoice_postal_code' => $component->invoiceData['postal_code'] ?? null,
        'invoice_post_office' => $component->invoiceData['post_office'] ?? null,
        'delivery_id' => $component->selectedDelivery,
        'delivery_name' => $selectedDeliveryOption['name'] ?? '',
        'delivery_price' => $deliveryPrice,
        'items' => $component->cart['items'] ?? [],
        'subtotal' => $subtotal,
        'delivery_cost' => $deliveryPrice,
        'is_free_delivery' => $selectedDeliveryOption['is_free'] ?? false,
        'promotion_id' => $component->appliedPromotion?->id ?? null,
        'discount_amount' => $promotionDiscount,
        'promotion_code' => $component->appliedPromotion?->code ?? null,
        'total' => $total,
        'currency' => 'PLN',
        'notes' => $notes,
        'parcel_locker_data' => $parcelLockerData,
    ]);

    // Zapisz relację promocji w pivot table (jeśli jest promocja)
    if ($component->appliedPromotion) {
        $order->promotions()->attach($component->appliedPromotion->id);

        // Zwiększ licznik użycia promocji
        $component->appliedPromotion->increment('usage_count');
    }

    // Zapisz płatność (osobna operacja - można ją ponowić w przypadku niepowodzenia)
    $payment = Payment::create([
        'order_id' => $order->id,
        'payment_method' => $component->selectedPayment,
        'payment_method_guid' => $paymentMethodGuid,
        'payu_order_id' => $payuOrderId,
        'payu_option' => $component->selectedPayuOption,
        'ext_order_id' => $extOrderId,
        'status' => PaymentStatus::PENDING->value,
        'amount' => $total, // Total już uwzględnia zniżkę
        'currency' => 'PLN',
    ]);

    // Wywołaj event - XML zostanie wygenerowany przez listener
    event(new OrderCreated($order));

    return $order;
};

$submitOrder = function () use ($validateStep1, $validateStep2, $validateStep3, $validateStep4, $generateGuid, $saveOrder) {
    \Log::info('submitOrder wywołane');

    // Walidacja w 4 krokach - zatrzymujemy się przy pierwszym błędzie
    if (!$validateStep1()) {
        return; // Błąd w kroku 1 - zatrzymujemy walidację
    }

    if (!$validateStep2()) {
        return; // Błąd w kroku 2 - zatrzymujemy walidację
    }

    if (!$validateStep3()) {
        return; // Błąd w kroku 3 - zatrzymujemy walidację
    }

    if (!$validateStep4()) {
        return; // Błąd w kroku 4 - zatrzymujemy walidację
    }

    // Sprawdź czy wybrano PayU
    $isPayu = str_starts_with($this->selectedPayment, 'payu_');

    // Generuj GUID zamówienia
    $extOrderId = $generateGuid();

    // Pobierz GUID sposobu zapłaty
    $selectedPaymentOption = collect($this->paymentOptions)->firstWhere('code', $this->selectedPayment);
    $paymentMethodGuid = $selectedPaymentOption['guid'] ?? config('enova.payment.methods.gotowka');

    if ($isPayu) {
        // Płatność przez PayU
        $payuService = app(PayuService::class);

        // Przygotuj dane klienta dla PayU
        $buyer = [
            'email' => $this->customerData['email'],
            'phone' => $this->customerData['phone'] ?? '',
            'firstName' => $this->customerData['first_name'],
            'lastName' => $this->customerData['last_name'],
            'language' => 'pl',
        ];

        // Przygotuj adres dostawy
        $deliveryAddress = [
            'street' => $this->customerData['street'] . ' ' . $this->customerData['street_number'],
            'postalCode' => $this->customerData['postal_code'],
            'city' => $this->customerData['city'] ?? $this->customerData['post_office'],
            'countryCode' => 'PL',
        ];

        if (!empty($this->customerData['apartment'])) {
            $deliveryAddress['street'] .= '/' . $this->customerData['apartment'];
        }

        $buyer['delivery'] = $deliveryAddress;

        // Przygotuj produkty dla PayU
        $products = [];
        foreach ($this->cart['items'] ?? [] as $item) {
            $products[] = [
                'name' => $item['name'],
                'unitPrice' => (int) round($item['price'] * 100), // w groszach
                'quantity' => $item['quantity'],
            ];
        }

        // Dodaj koszt dostawy jako produkt
        $selectedDeliveryOption = collect($this->deliveryOptions)->firstWhere('id', $this->selectedDelivery);
        if ($selectedDeliveryOption && ($selectedDeliveryOption['price'] ?? 0) > 0) {
            $products[] = [
                'name' => 'Dostawa: ' . ($selectedDeliveryOption['name'] ?? ''),
                'unitPrice' => (int) round($selectedDeliveryOption['price'] * 100),
                'quantity' => 1,
            ];
        }

        // Oblicz całkowitą kwotę (produkty + dostawa - zniżka)
        $totalAmount = $this->cart['total'] ?? 0;
        if ($selectedDeliveryOption && ($selectedDeliveryOption['price'] ?? 0) > 0) {
            $totalAmount += $selectedDeliveryOption['price'];
        }
        $totalAmount = max(0, $totalAmount - ($this->promotionDiscount ?? 0));

        // Przygotuj dane zamówienia
        $orderData = [
            'description' => 'Zamówienie ze sklepu Zdrowe Herbaty BIFIX',
            'currency' => 'PLN',
            'total_amount' => $totalAmount,
            'ext_order_id' => $extOrderId,
            'buyer' => $buyer,
            'products' => $products,
        ];

        // Utwórz zamówienie w PayU
        \Log::info('PayU: Creating order', [
            'selected_payment' => $this->selectedPayment,
            'selected_payu_option' => $this->selectedPayuOption,
            'ext_order_id' => $extOrderId,
            'total_amount' => $totalAmount,
        ]);

        $payuOrder = $payuService->createOrder($orderData, $this->selectedPayuOption);

        \Log::info('PayU: Order creation response', [
            'payu_order' => $payuOrder,
            'has_redirect_uri' => isset($payuOrder['redirectUri']),
        ]);

        if ($payuOrder && isset($payuOrder['redirectUri'])) {
            // Zapisz zamówienie do bazy przed przekierowaniem
            $saveOrder($extOrderId, $paymentMethodGuid, $payuOrder['orderId'] ?? null, $this);

            // Wyczyść koszyk
            $cartService = app(CartService::class);
            $cartService->clearCart();

            // Przekieruj do PayU
            return redirect($payuOrder['redirectUri']);
        } else {
            // Błąd tworzenia zamówienia
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Wystąpił błąd podczas tworzenia płatności. Spróbuj ponownie.',
            ]);
            return;
        }
    } else {
        // Płatność gotówką przy odbiorze - zapisz zamówienie w bazie
        $order = $saveOrder($extOrderId, $paymentMethodGuid, null, $this);

        // Wyczyść koszyk
        $cartService = app(CartService::class);
        $cartService->clearCart();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Zamówienie zostało złożone pomyślnie! Płatność przy odbiorze.',
        ]);

        // Przekieruj na stronę zamówienia
        return redirect()->route('order.info', ['ext_order_id' => $order->ext_order_id]);
    }
};

?>

<div>
    {{-- Ogólny komunikat --}}
    @if (session('message'))
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-700">{{ session('message') }}</p>
        </div>
    @endif

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
                {{-- KROK 1: Dane klienta --}}
                <div class="bg-white rounded-lg shadow p-6" id="step-1">
                    {{-- Alert błędów dla kroku 1 - tylko błędy zamawiającego --}}
                    @php
                        // Filtruj błędy - pokazuj tylko błędy zamawiającego (nie faktury)
                        $customerErrors = [];
                        if (!empty($stepErrors['step1'])) {
                            foreach ($stepErrors['step1'] as $field => $messages) {
                                if (!str_starts_with($field, 'invoiceData.')) {
                                    $customerErrors[$field] = $messages;
                                }
                            }
                        }
                    @endphp
                    @if (!empty($customerErrors))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-red-800 mb-2">Proszę uzupełnić wszystkie wymagane
                                        pola w danych zamawiającego.</p>
                                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                        @foreach ($customerErrors as $field => $messages)
                                            @foreach ($messages as $message)
                                                <li>{{ $message }}</li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-4">
                        {{-- Dane zamawiającego --}}
                        <div class="mb-4">
                            <h2 class="text-xl font-semibold">Dane zamawiającego</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:field>
                                    <flux:label>Imię *</flux:label>
                                    <flux:input wire:model="customerData.first_name" placeholder="Wprowadź imię"
                                        x-on:change="$wire.saveCustomerData()" />
                                </flux:field>
                            </div>
                            <div>
                                <flux:field>
                                    <flux:label>Nazwisko *</flux:label>
                                    <flux:input wire:model="customerData.last_name" placeholder="Wprowadź nazwisko" />
                                </flux:field>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:field>
                                    <flux:label>Email *</flux:label>
                                    <flux:input type="email" wire:model="customerData.email"
                                        placeholder="Wprowadź email" />
                                </flux:field>
                            </div>
                            <div>
                                <flux:field>
                                    <flux:label>Telefon</flux:label>
                                    <flux:input wire:model="customerData.phone" placeholder="Wprowadź numer telefonu" />
                                </flux:field>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div class="md:col-span-4">
                                <flux:field>
                                    <flux:label>Ulica *</flux:label>
                                    <flux:input wire:model="customerData.street" placeholder="Wprowadź ulicę" />
                                </flux:field>
                            </div>
                            <div class="md:col-span-1">
                                <flux:field>
                                    <flux:label>Nr *</flux:label>
                                    <flux:input wire:model="customerData.street_number" placeholder="1" />
                                </flux:field>
                            </div>
                            <div class="md:col-span-1">
                                <flux:field>
                                    <flux:label>Lokal</flux:label>
                                    <flux:input wire:model="customerData.apartment" placeholder="1A" />
                                </flux:field>
                            </div>
                        </div>

                        <div>
                            <flux:field>
                                <flux:label>Miejscowość</flux:label>
                                <flux:input wire:model="customerData.city" placeholder="Wprowadź miejscowość" />
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <flux:field>
                                    <flux:label>Kod pocztowy *</flux:label>
                                    <flux:input wire:model="customerData.postal_code" placeholder="00-000" />
                                </flux:field>
                            </div>
                            <div class="md:col-span-9">
                                <flux:field>
                                    <flux:label>Poczta *</flux:label>
                                    <flux:input wire:model="customerData.post_office" placeholder="Wprowadź pocztę" />
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
                        <div class="border-t pt-4" id="invoice-section" x-data x-init="$wire.$watch('customerData.invoice_required', value => { if (!value) { $wire.clearInvoiceData(); } })">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="customerData.invoice_required"
                                    id="invoice_required"
                                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" />
                                <span class="ml-2 block text-sm text-gray-900">
                                    Wystaw fakturę VAT
                                </span>
                            </label>

                            <div x-data="{
                                show: @entangle('customerData.invoice_required')
                            }" x-show="show"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                                x-transition:enter-end="opacity-100 max-h-screen"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 max-h-screen"
                                x-transition:leave-end="opacity-0 max-h-0 overflow-hidden" class="mt-4 space-y-4">
                                <div class="mb-4">
                                    <h2 class="text-xl font-semibold">Dane do faktury</h2>
                                </div>

                                {{-- Alert błędów dla faktury (tylko błędy związane z fakturą) --}}
                                @php
                                    $invoiceErrors = [];
                                    if (!empty($stepErrors['step1'])) {
                                        foreach ($stepErrors['step1'] as $field => $messages) {
                                            if (str_starts_with($field, 'invoiceData.')) {
                                                $invoiceErrors[$field] = $messages;
                                            }
                                        }
                                    }
                                @endphp
                                @if (!empty($invoiceErrors))
                                    <div id="invoice-alert" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-red-800 mb-2">Proszę uzupełnić
                                                    wszystkie wymagane pola w danych do faktury.</p>
                                                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                                    @foreach ($invoiceErrors as $field => $messages)
                                                        @foreach ($messages as $message)
                                                            <li>{{ $message }}</li>
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div>
                                    <flux:field>
                                        <flux:label>Nazwa firmy *</flux:label>
                                        <flux:input wire:model="invoiceData.company_name"
                                            placeholder="Wprowadź nazwę firmy" />
                                    </flux:field>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>NIP *</flux:label>
                                        <flux:input wire:model="invoiceData.nip" placeholder="Wprowadź NIP" />
                                    </flux:field>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                    <div class="md:col-span-4">
                                        <flux:field>
                                            <flux:label>Ulica *</flux:label>
                                            <flux:input wire:model="invoiceData.street"
                                                placeholder="Wprowadź ulicę" />
                                        </flux:field>
                                    </div>
                                    <div class="md:col-span-1">
                                        <flux:field>
                                            <flux:label>Nr *</flux:label>
                                            <flux:input wire:model="invoiceData.street_number" placeholder="1" />
                                        </flux:field>
                                    </div>
                                    <div class="md:col-span-1">
                                        <flux:field>
                                            <flux:label>Lokal</flux:label>
                                            <flux:input wire:model="invoiceData.apartment" placeholder="1A" />
                                        </flux:field>
                                    </div>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>Miejscowość *</flux:label>
                                        <flux:input wire:model="invoiceData.city"
                                            placeholder="Wprowadź miejscowość" />
                                    </flux:field>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-3">
                                        <flux:field>
                                            <flux:label>Kod pocztowy *</flux:label>
                                            <flux:input wire:model="invoiceData.postal_code" placeholder="00-000" />
                                        </flux:field>
                                    </div>
                                    <div class="md:col-span-9">
                                        <flux:field>
                                            <flux:label>Poczta *</flux:label>
                                            <flux:input wire:model="invoiceData.post_office"
                                                placeholder="Wprowadź pocztę" />
                                        </flux:field>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KROK 2: Wybór dostawy --}}
                <div class="bg-white rounded-lg shadow p-6" id="step-2">
                    {{-- Alert błędów dla kroku 2 --}}
                    @if (!empty($stepErrors['step2']))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    @foreach ($stepErrors['step2'] as $field => $errors)
                                        @foreach ($errors as $error)
                                            <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    <h2 class="text-xl font-semibold mb-4">Wybór dostawy</h2>

                    @php
                        // Pobierz próg bezpłatnej dostawy z promocji w bazie
                        $freeDeliveryPromotion = \App\Models\Promotion::where('type', 'automatic')
                            ->where('discount_type', 'free_delivery')
                            ->where('is_active', true)
                            ->where(function ($query) {
                                $query->whereNull('valid_from')->orWhere('valid_from', '<=', now());
                            })
                            ->where(function ($query) {
                                $query->whereNull('valid_to')->orWhere('valid_to', '>=', now());
                            })
                            ->first();

                        $freeThreshold =
                            $freeDeliveryPromotion && $freeDeliveryPromotion->min_order_amount
                                ? (float) $freeDeliveryPromotion->min_order_amount
                                : 0;
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
                                                                class="mb-2 text-sm text-gray-600">
                                                                @if (!empty($parcelLockerData) && is_array($parcelLockerData) && !empty($parcelLockerData['name']))
                                                                    <strong>{{ $parcelLockerData['name'] }}</strong>
                                                                    @if (!empty($parcelLockerData['address']['line1']))
                                                                        - {{ $parcelLockerData['address']['line1'] }},
                                                                        {{ $parcelLockerData['address']['line2'] ?? '' }}
                                                                    @endif
                                                                @endif
                                                            </div>
                                                            <button type="button" onclick="openEasyPackModal()"
                                                                id="parcelLockerBtn"
                                                                class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center text-sm">
                                                                <svg class="w-4 h-4 mr-2" fill="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                                </svg>
                                                                <span
                                                                    id="btnText">{{ !empty($parcelLockerData) && is_array($parcelLockerData) && !empty($parcelLockerData['name']) ? 'Zmień' : 'Wybierz' }}
                                                                    paczkomat</span>
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

                {{-- KROK 3: Wybór płatności --}}
                <div class="bg-white rounded-lg shadow p-6" id="step-3">
                    {{-- Alert błędów dla kroku 3 --}}
                    @if (!empty($stepErrors['step3']))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Proszę wybrać sposób płatności.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <h2 class="text-xl font-semibold mb-4">Wybór płatności</h2>

                    @if (empty($selectedDelivery))
                        <flux:callout variant="secondary" icon="information-circle">
                            <flux:callout.heading>Najpierw wybierz sposób dostawy</flux:callout.heading>
                            <flux:callout.text>Po wskazaniu dostawy pokażemy dostępne sposoby płatności.
                            </flux:callout.text>
                        </flux:callout>
                    @elseif (count($paymentOptions) === 0)
                        <flux:callout variant="secondary" icon="exclamation-triangle">
                            <flux:callout.heading>Brak konfiguracji płatności</flux:callout.heading>
                            <flux:callout.text>Nie znaleziono powiązanej metody płatności dla tej dostawy. Skontaktuj
                                się
                                z obsługą sklepu.</flux:callout.text>
                        </flux:callout>
                    @else
                        <div class="space-y-3">
                            @foreach ($paymentOptions as $option)
                                <div class="border rounded-lg p-4 transition-colors cursor-pointer hover:bg-gray-50 {{ $selectedPayment === $option['code'] ? 'ring-1 ring-primary/40 bg-primary/5' : '' }}"
                                    wire:click="$set('selectedPayment', '{{ $option['code'] }}')"
                                    wire:key="payment-{{ $option['code'] }}">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" wire:model.live="selectedPayment"
                                                value="{{ $option['code'] }}" id="payment-{{ $option['code'] }}"
                                                class="mt-0">
                                            <div class="font-medium text-base text-gray-900">
                                                {{ $option['label'] ?? ($option['raw_name'] ?? 'Metoda płatności') }}
                                            </div>
                                        </div>
                                        @if (!empty($option['logo']))
                                            <img src="{{ $option['logo'] }}" alt="{{ $option['label'] ?? 'PayU' }}"
                                                class="h-8 w-auto object-contain">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                        </div>
                    @endif
                </div>


            </div>

            {{-- Podsumowanie --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-20">
                    <h2 class="text-lg font-semibold mb-3">W koszyku</h2>

                    {{-- Produkty --}}
                    <div class="space-y-2 mb-4">
                        @foreach ($cart['items'] as $productId => $item)
                            <div class="text-xs">
                                <div class="font-medium mb-0.5">{{ $item['name'] }}</div>
                                <div class="flex justify-between items-center text-gray-600">
                                    <span>Ilość: {{ $item['quantity'] }}</span>
                                    <span
                                        class="font-medium text-gray-900">{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}
                                        zł</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t pt-4 space-y-2">
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
                                <span>{{ number_format(max(0, ($cart['total'] ?? 0) - $promotionDiscount), 2, ',', '.') }}
                                    zł</span>
                            </div>
                        @else
                            {{-- Razem (bez zniżki) --}}
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Razem:</span>
                                <span>{{ number_format($cart['total'] ?? 0, 2, ',', '.') }} zł</span>
                            </div>
                        @endif
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

        {{-- KROK 4: Akceptacja regulaminu --}}
        <div class="bg-white rounded-lg shadow p-6 mt-6" id="step-4">
            {{-- Alert błędów dla kroku 4 --}}
            @if (!empty($stepErrors['step4']))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            @foreach ($stepErrors['step4'] as $field => $errors)
                                @foreach ($errors as $error)
                                    <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <h2 class="text-xl font-semibold mb-4">Akceptacja regulaminu</h2>

            {{-- Checkbox akceptacji regulaminu --}}
            <div class="mb-4">
                <label class="flex items-start">
                    <input type="checkbox" wire:model.live="acceptedTerms"
                        class="mt-1 mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <span class="text-sm text-gray-700">
                        Akceptuję
                        <flux:modal.trigger name="terms-modal">
                            <button type="button" class="text-primary hover:underline">
                                Regulamin
                            </button>
                        </flux:modal.trigger>
                    </span>
                </label>
            </div>
        </div>

        {{-- Przycisk zamówienia --}}
        <div class="mt-8">
            <form wire:submit.prevent="submitOrder">
                <input type="hidden" wire:model.live="parcelLockerData" id="parcelLockerDataInput">

                <flux:button type="submit" variant="primary" class="w-full">
                    Złóż zamówienie
                </flux:button>
            </form>
        </div>
    @endif

    {{-- Modal z regulaminem --}}
    <flux:modal name="terms-modal" focusable class="max-w-4xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="xl">
                    {{ $termsContent?->title ?? 'Regulamin' }}
                </flux:heading>
                <flux:subheading>
                    Regulamin sklepu internetowego Zdrowe Herbaty
                </flux:subheading>
            </div>

            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                @if ($termsContent && $termsContent->content)
                    {!! $termsContent->content !!}
                @else
                    <p class="text-gray-500">Regulamin nie jest dostępny.</p>
                @endif
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="filled">Zamknij</flux:button>
                </flux:modal.close>
            </div>
        </x-slot>
    </flux:modal>

    <!-- EasyPack CSS -->
    <link href="//geowidget.easypack24.net/css/easypack.css" media="screen" rel="stylesheet" type="text/css">

    <!-- EasyPack JavaScript -->
    <script src="//geowidget.easypack24.net/js/sdk-for-javascript.js"></script>

    <script>
        // Przewijanie do sekcji z błędami
        document.addEventListener('livewire:init', () => {
            Livewire.on('scroll-to-step', (data) => {
                // Livewire 3 może przekazać dane w różny sposób
                const step = data?.step || data?.[0]?.step || (Array.isArray(data) ? data[0] : null);
                if (!step) return;

                const element = document.getElementById(`step-${step}`);
                if (element) {
                    // Przewiń do sekcji z offsetem uwzględniającym floating header
                    // Dla kroku 1 (dane zamawiającego) przewijamy do alertu na górze
                    // Dla kroku 2 i 3 przewijamy do alertu na górze sekcji
                    const offset = 80; // Offset dla floating header (zmniejszony)
                    const elementPosition = element.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;

                    window.scrollTo({
                        top: Math.max(0,
                            offsetPosition), // Upewnij się, że nie przewijamy powyżej góry strony
                        behavior: 'smooth'
                    });

                    // Dodaj lekkie podświetlenie sekcji z błędem
                    element.classList.add('ring-2', 'ring-red-500', 'ring-opacity-50');
                    setTimeout(() => {
                        element.classList.remove('ring-2', 'ring-red-500', 'ring-opacity-50');
                    }, 2000);
                }
            });

            // Przewijanie do sekcji faktury (gdy są tylko błędy faktury)
            Livewire.on('scroll-to-invoice', () => {
                // Najpierw sprawdź czy sekcja faktury jest widoczna (checkbox zaznaczony)
                const invoiceSection = document.getElementById('invoice-section');
                if (!invoiceSection) return;

                // Sprawdź czy sekcja faktury jest widoczna (Alpine.js x-show)
                const isVisible = invoiceSection.offsetParent !== null;
                if (!isVisible) {
                    // Jeśli sekcja nie jest widoczna, zaznacz checkbox i poczekaj na animację
                    const checkbox = document.getElementById('invoice_required');
                    if (checkbox && !checkbox.checked) {
                        checkbox.click();
                        // Poczekaj na animację Alpine.js (300ms) przed przewinięciem
                        setTimeout(() => {
                            scrollToInvoiceAlert();
                        }, 350);
                    } else {
                        scrollToInvoiceAlert();
                    }
                } else {
                    scrollToInvoiceAlert();
                }

                function scrollToInvoiceAlert() {
                    const alertElement = document.getElementById('invoice-alert');
                    if (alertElement) {
                        const offset = 80; // Offset dla floating header
                        const elementPosition = alertElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - offset;

                        window.scrollTo({
                            top: Math.max(0, offsetPosition),
                            behavior: 'smooth'
                        });

                        // Dodaj lekkie podświetlenie alertu faktury
                        alertElement.classList.add('ring-2', 'ring-red-500', 'ring-opacity-50');
                        setTimeout(() => {
                            alertElement.classList.remove('ring-2', 'ring-red-500',
                                'ring-opacity-50');
                        }, 2000);
                    }
                }
            });
        });

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
                    const paczkomatNameInput = document.getElementById('paczkomat_name');
                    const parcelLockerDataInput = document.getElementById('parcelLockerDataInput');

                    if (locker.name) {
                        // Wyświetl dane
                        if (displayDiv) {
                            displayDiv.innerHTML = '<strong>' + locker.name + '</strong> - ' +
                                (locker.address?.line1 || '') + ', ' +
                                (locker.address?.line2 || '');
                        }

                        // Zmień tekst przycisku
                        if (btnText) {
                            btnText.textContent = 'Zmień paczkomat';
                        }

                        // Ustaw wartość ukrytego pola paczkomat_name (jak w starym systemie)
                        if (paczkomatNameInput) {
                            paczkomatNameInput.value = locker.name || '';
                        }

                        // Synchronizuj z Livewire przez parcelLockerDataInput
                        if (parcelLockerDataInput) {
                            parcelLockerDataInput.value = JSON.stringify(locker);
                            parcelLockerDataInput.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            parcelLockerDataInput.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }
                    }
                } catch (e) {
                    // Błąd parsowania zapisanego paczkomatu
                    console.error('Błąd parsowania danych paczkomatu:', e);
                }
            }
        }

        // Funkcja pomocnicza do odczytu cookies
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
            return null;
        }

        function setCookie(name, value, days = 365) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = `expires=${date.toUTCString()}`;
            document.cookie = `${name}=${encodeURIComponent(value)};${expires};path=/`;
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

                // Sprawdź czy point ma wymagane dane
                if (!point || !point.name) {
                    return;
                }

                // Zapisz wybór do cookies na 30 dni
                const expiryDate = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
                document.cookie =
                    `selectedParcelLocker=${JSON.stringify(point)}; expires=${expiryDate.toUTCString()}; path=/`;

                // Ustaw wartość w hidden input - Livewire automatycznie zsynchronizuje przez wire:model.live
                const input = document.getElementById('parcelLockerDataInput');
                if (input) {
                    input.value = JSON.stringify(point);
                    input.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                    input.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                // Wyświetl wybór
                const displayDiv = document.getElementById('danePaczkomatu');
                const btnText = document.getElementById('btnText');

                if (displayDiv) {
                    displayDiv.innerHTML = '<strong>' + point.name + '</strong> - ' +
                        (point.address?.line1 || '') + ', ' +
                        (point.address?.line2 || '');
                }

                if (btnText) {
                    btnText.textContent = 'Zmień paczkomat';
                }
            }, {
                width: 500
            });
        }


        // Nasłuchuj na eventy Livewire do zapisywania i wczytywania danych
        document.addEventListener('livewire:init', () => {
            // Zapisywanie danych klienta
            Livewire.on('save-customer-data', (event) => {
                if (event.data) {
                    setCookie('savedCustomerData', event.data);
                }
            });

            // Zapisywanie danych faktury
            Livewire.on('save-invoice-data', (event) => {
                if (event.data) {
                    setCookie('savedInvoiceData', event.data);
                }
            });
        });
    </script>

    {{-- Przewijanie do formularza przy błędach walidacji --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scroll-to-form', () => {
                const form = document.getElementById('order-form');
                const header = document.getElementById('header-top');

                if (form) {
                    // Pobierz pozycję formularza względem dokumentu
                    const formTop = form.getBoundingClientRect().top + window.pageYOffset;
                    // Pobierz wysokość header'a
                    const headerHeight = header ? header.offsetHeight : 0;
                    // Przewiń do samej góry formularza, uwzględniając wysokość header'a i większy margines
                    window.scrollTo({
                        top: formTop - headerHeight - 40,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</div>

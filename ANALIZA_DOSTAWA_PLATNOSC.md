# Analiza porównawcza: Dostawa i Płatność - Stary vs Nowy System

## 1. WYBÓR DOSTAWY

### STARY SYSTEM (Zend Framework 1.2)

**Logika:**

-   `dostawaAction()` - pobiera opcje dostawy na podstawie:
    -   **Wagi koszyka** (`$waga`) - suma `MasaBruttoValue * ilość` dla wszystkich produktów
    -   **Czy są towary wielkogabarytowe** (`$wielkogabarytowe`) - sprawdza `$towar['Wielkogabarytowy']` dla każdego produktu
-   Używa `$dostawa->fetchOpcje($waga, $wielkogabarytowe)` - metoda w modelu Enova, która filtruje opcje dostawy
-   **Filtrowanie przy darmowej dostawie:**
    -   Jeśli koszyk >= próg darmowej dostawy:
        -   Pokazuje **tylko** opcje z `SposobZaplatyID` == przedpłata LUB gotówka
        -   Ukrywa inne opcje (np. pobranie)
    -   Jeśli koszyk < próg:
        -   Pokazuje wszystkie opcje dostawy
-   **Paczkomat:**
    -   Sprawdza czy nazwa dostawy zawiera nazwę paczkomatu (z config)
    -   Jeśli tak i brak cookie `paczkomat` → przekierowuje do koszyka (w niektórych miejscach zakomentowane)
    -   Dane paczkomatu z cookie są przypisywane do `$dostawa['Paczkomat']`

**Kod:**

```php
$waga = 0;
$wielkogabarytowe = false;
foreach ($towary as $towar) {
    $waga += $towar['MasaBruttoValue'] * $koszyk->ilosc($towar['ID']);
    if ($towar['Wielkogabarytowy']) {
        $wielkogabarytowe = true;
    }
}
$dostawaArr = $dostawa->fetchOpcje($waga, $wielkogabarytowe);

// Filtrowanie przy darmowej dostawie
foreach ($dostawaArr as $row) {
  if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna &&
        $koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna) {
      if ((Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata == $row['SposobZaplatyID'])
          || (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->gotowka == $row['SposobZaplatyID']))
        $this->view->dostawa[$row['ID']] = $row;
  } else {
    $this->view->dostawa[$row['ID']] = $row;
  }
}
```

### NOWY SYSTEM (Laravel 12 + Livewire)

**Logika:**

-   `loadDeliveryOptions()` - pobiera opcje dostawy na podstawie:
    -   **Tylko wagi koszyka** (`$cartWeight`) - suma `weight * quantity` dla wszystkich produktów
    -   **BRAK sprawdzania wielkogabarytowych** - nie sprawdza flagi `Wielkogabarytowy`
-   Filtruje opcje: `where('Towary.MasaBruttoValue', '>=', $cartWeight)`
-   Wybiera tylko opcje z **najniższą wagą** >= waga koszyka
-   **Darmowa dostawa:**
    -   Jeśli koszyk >= próg: ustawia `price = 0` dla wszystkich opcji
    -   **NIE filtruje opcji** - pokazuje wszystkie, tylko z ceną 0
-   **Paczkomat:**
    -   Sprawdza czy nazwa dostawy zawiera nazwę paczkomatu (z config)
    -   **BRAK walidacji** - nie sprawdza czy paczkomat jest wybrany przed finalizacją
    -   Dane paczkomatu zapisuje w `parcel_locker_data` (JSON) w zamówieniu

**Kod:**

```php
$cartWeight = $this->cart['total_weight'] ?? 0;

$deliveries = Delivery::join('Ceny', 'Towary.ID', '=', 'Ceny.Towar')
    ->where('Towary.MasaBruttoValue', '>=', $cartWeight)
    ->orderBy('MasaBruttoValue')
    ->orderBy('Ceny.BruttoValue')
    ->get();

$minWeight = $deliveries->min('MasaBruttoValue');
$filteredDeliveries = $deliveries->where('MasaBruttoValue', $minWeight);

// Darmowa dostawa - tylko ustawia cenę na 0
$isFree = $cartTotal >= $freeThreshold && $freeThreshold > 0;
$price = $isFree ? 0 : $delivery->BruttoValue;
```

### RÓŻNICE I PROBLEMY

#### ❌ **PROBLEM 1: Brak obsługi wielkogabarytowych**

-   **Stary:** Sprawdza `Wielkogabarytowy` i przekazuje do `fetchOpcje()`
-   **Nowy:** Nie sprawdza wielkogabarytowych
-   **Wpływ:** Może pokazywać nieprawidłowe opcje dostawy dla dużych produktów
-   **Rekomendacja:** Dodać sprawdzanie flagi `Wielkogabarytowy` w koszyku i przekazywać do zapytania

#### ⚠️ **PROBLEM 2: Filtrowanie opcji przy darmowej dostawie**

-   **Stary:** Filtruje opcje - pokazuje tylko przedpłatę i gotówkę
-   **Nowy:** Pokazuje wszystkie opcje, tylko z ceną 0
-   **Wpływ:** Użytkownik może wybrać opcję, która nie powinna być dostępna przy darmowej dostawie (np. pobranie)
-   **Rekomendacja:** Zastosować filtrowanie jak w starym systemie - pokazywać tylko opcje z przedpłatą lub gotówką

#### ⚠️ **PROBLEM 3: Brak walidacji paczkomatu**

-   **Stary:** Przekierowuje do koszyka jeśli brak cookie (w niektórych miejscach zakomentowane)
-   **Nowy:** Brak walidacji - można złożyć zamówienie bez wyboru paczkomatu
-   **Wpływ:** Możliwe zamówienia z dostawą do paczkomatu bez danych paczkomatu
-   **Rekomendacja:** Dodać walidację przed finalizacją - jeśli wybrano paczkomat, sprawdzić czy są dane w cookie

---

## 2. WYBÓR PŁATNOŚCI

### STARY SYSTEM

**Logika:**

-   `platnoscAction()` - wyświetla opcje płatności na podstawie wybranej dostawy
-   Sprawdza `$dostawa['SposobZaplatyID']`:
    -   Jeśli == **przedpłata** → pobiera opcje PayU (`$payU->getOptions()`)
    -   W przeciwnym razie → pokazuje tylko "Gotówka"
-   W `finalizacjaAction()` - ustawia typ płatności na podstawie dostawy:
    ```php
    switch ($dostawa['SposobZaplatyID']) {
      case przedplata: $platnosc->setTyp('payu'); break;
      case pobranie: $platnosc->setTyp('pobranie'); break;
      case gotowka: $platnosc->setTyp('gotowka'); break;
    }
    ```
-   **Specjalna obsługa "przelew":**
    -   Jeśli `$platnosc->getId() <> 'przelew'` → ustawia `payMethods` z `type: 'PBL'` i `value: $platnosc->getId()`
    -   Jeśli `'przelew'` → nie ustawia `payMethods` (domyślna opcja PayU)

**Kod:**

```php
if ($dostawa['SposobZaplatyID'] == Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata) {
  $payU = new Application_Model_PayU();
  $opcje = $payU->getOptions();
} else {
  $opcje[] = [
    'value' => 'gotowka',
    'name' => 'Gotówka'
  ];
}

// W finalizacjaAction:
if ($platnosc->getTyp() == 'payu') {
  if ($platnosc->getId() <> 'przelew') {
    $order['payMethods']['payMethod']['type'] = 'PBL';
    $order['payMethods']['payMethod']['value'] = $platnosc->getId();
    $platnosc->addData($order);
  }
}
```

### NOWY SYSTEM

**Logika:**

-   `resolvePaymentDefinition()` - sprawdza `payment_method_guid` dostawy
-   Jeśli GUID == **przedpłata** → zwraca opcje PayU (blik, card, google_pay, apple_pay, transfer)
-   W przeciwnym razie → zwraca "Gotówka przy odbiorze"
-   `updatedSelectedDelivery()` - automatycznie zaznacza płatność jeśli jest tylko jedna opcja
-   `updatedSelectedPayment()` - ustawia `selectedPaymentGuid` i `selectedPayuOption`
-   W `submitOrder()` - używa `selectedPayuOption` do przekazania do PayU REST API

**Kod:**

```php
$resolvePaymentDefinition = function (array $delivery) use ($getPayuOptions) {
    $guid = strtoupper((string) ($delivery['payment_method_guid'] ?? ''));
    $przedplataGuid = strtoupper((string) config('enova.payment.methods.przedplata'));

    if ($guid && $przedplataGuid && $guid === $przedplataGuid) {
        $payuOptions = $getPayuOptions(); // zwraca opcje PayU
        return array_map(function ($option) use ($guid, $rawName) {
            return array_merge($option, [
                'guid' => $guid,
                'raw_name' => $rawName,
            ]);
        }, $payuOptions);
    }

    return [
        [
            'code' => 'cash',
            'label' => 'Gotówka przy odbiorze',
            'guid' => $guid ?: null,
        ],
    ];
};

// Automatyczne zaznaczanie
if (count($options) === 1) {
    $firstOption = $options[0];
    $this->selectedPayment = $firstOption['code'] ?? null;
    $this->selectedPaymentGuid = $firstOption['guid'] ?? null;
    $this->selectedPayuOption = $firstOption['payu_option'] ?? null;
}
```

### RÓŻNICE I PROBLEMY

#### ✅ **POPRAWNE: Automatyczne zaznaczanie płatności**

-   **Nowy:** Automatycznie zaznacza jeśli jest tylko jedna opcja
-   **Stary:** Wymaga ręcznego wyboru
-   **Wpływ:** Lepsze UX - użytkownik nie musi klikać jeśli jest tylko jedna opcja
-   **Status:** OK, nie wymaga zmiany

#### ⚠️ **PROBLEM 4: Brak obsługi "pobranie" jako osobnego typu**

-   **Stary:** Ma osobny typ `'pobranie'` dla dostaw z pobraniem
-   **Nowy:** Wszystko co nie jest przedpłatą → "Gotówka przy odbiorze"
-   **Wpływ:** Może być mylące - "Gotówka przy odbiorze" vs "Pobranie" to różne rzeczy
-   **Rekomendacja:** Sprawdzić czy w Enova są dostawy z `SposobZaplatyID == pobranie` i dodać obsługę

#### ✅ **POPRAWNE: PayU REST API**

-   **Stary:** Używa starego PayU API z `payMethods.payMethod.type: 'PBL'`
-   **Nowy:** Używa PayU REST API z `payMethods.payMethod.value`
-   **Wpływ:** Nowoczesne API, lepsza obsługa
-   **Status:** OK, nie wymaga zmiany

---

## 3. FINALIZACJA ZAMÓWIENIA

### STARY SYSTEM

**Logika:**

-   `finalizacjaAction()` - finalizuje zamówienie
-   Sprawdza paczkomat - jeśli brak cookie → przekierowuje do `/kasa`
-   Zapisuje dane paczkomatu w `$uwagi` jako tekst:
    ```php
    $uwagi .= 'Paczkomat: '
        . $paczkomat->name . ', '
        . $paczkomat->address->line1 . ', '
        . $paczkomat->address->line2
        . "\n\n";
    ```
-   Ustawia typ płatności na podstawie dostawy
-   Tworzy zamówienie w Enova (XML)
-   Jeśli PayU → tworzy zamówienie w PayU i przekierowuje

### NOWY SYSTEM

**Logika:**

-   `submitOrder()` - finalizuje zamówienie
-   **BRAK walidacji paczkomatu** przed zapisem
-   Zapisuje dane paczkomatu w `parcel_locker_data` (JSON) w zamówieniu
-   Tworzy zamówienie lokalnie w bazie
-   Jeśli PayU → tworzy zamówienie w PayU i przekierowuje
-   Event `OrderCreated` → generuje XML przez listener

### RÓŻNICE I PROBLEMY

#### ❌ **PROBLEM 5: Brak walidacji paczkomatu przed finalizacją**

-   **Stary:** Przekierowuje do `/kasa` jeśli brak cookie
-   **Nowy:** Brak walidacji
-   **Wpływ:** Możliwe zamówienia bez danych paczkomatu
-   **Rekomendacja:** Dodać walidację w `validateStep2()` - jeśli wybrano paczkomat, sprawdzić czy są dane

#### ✅ **POPRAWNE: Struktura danych paczkomatu**

-   **Stary:** Zapisuje jako tekst w `$uwagi`
-   **Nowy:** Zapisuje jako JSON w `parcel_locker_data`
-   **Wpływ:** Lepsza struktura, łatwiejsze parsowanie
-   **Status:** OK, nie wymaga zmiany

---

## 4. PODSUMOWANIE PROBLEMÓW I REKOMENDACJI

### ✅ ZREALIZOWANE

1. **✅ Walidacja paczkomatu** - ZREALIZOWANE
    - Dodano walidację w `validateStep2()` - sprawdza czy wybrana dostawa to paczkomat (przez nazwę)
    - Jeśli tak, sprawdza czy są dane w cookie `selectedParcelLocker` lub `parcelLockerData`
    - Wyświetla komunikat błędu jeśli brak danych
    - Rozpoznawanie paczkomatu: przez sprawdzenie czy nazwa dostawy zawiera frazę z config (jak w starym systemie)

### POMINIĘTE (zgodnie z wymaganiami użytkownika)

2. **⏭️ Brak obsługi wielkogabarytowych** - POMINIĘTE

    - Użytkownik: "na tą chwilę ze względu na rodzaj asortymentu, nie będziemy uwzględniać czy wielkogabarytowe"
    - Status: Nie będzie implementowane

3. **⏭️ Filtrowanie opcji przy darmowej dostawie** - POMINIĘTE
    - Użytkownik: "przy darmowej dostawie nie będziemy ograniczac form płątności, będą takie same jakie by yły przy płątnej dostawie"
    - Status: Nie będzie implementowane

### DO ROZWAŻENIA (opcjonalne)

4. **⚠️ Obsługa "pobranie" jako osobnego typu**
    - Sprawdzić czy w Enova są dostawy z `SposobZaplatyID == pobranie`
    - Jeśli tak, dodać obsługę jako osobny typ płatności (nie "Gotówka przy odbiorze")

### OPCJONALNE (można zostawić)

5. **✅ Automatyczne zaznaczanie płatności** - poprawne, lepsze UX
6. **✅ PayU REST API** - nowoczesne, poprawne
7. **✅ Struktura danych paczkomatu** - lepsza niż w starym systemie

---

## 5. PLAN DZIAŁAŃ

### ✅ Krok 1: Dodać walidację paczkomatu - ZREALIZOWANE

-   ✅ W `validateStep2()` - sprawdza czy wybrana dostawa to paczkomat (przez nazwę - jak w starym systemie)
-   ✅ Jeśli tak, sprawdza czy są dane w cookie `selectedParcelLocker` lub `parcelLockerData`
-   ✅ Jeśli brak, dodaje błąd walidacji z komunikatem "Wybierz paczkomat dla dostawy do paczkomatu."
-   ✅ Błąd wyświetlany w alertach kroku 2

### ⏭️ Krok 2: Obsługa wielkogabarytowych - POMINIĘTE

-   Użytkownik zdecydował, że nie będzie implementowane ze względu na rodzaj asortymentu

### ⏭️ Krok 3: Filtrowanie opcji przy darmowej dostawie - POMINIĘTE

-   Użytkownik zdecydował, że przy darmowej dostawie będą takie same formy płatności jak przy płatnej

### DO ROZWAŻENIA: Krok 4: Sprawdzić obsługę "pobranie"

-   Sprawdzić w Enova czy są dostawy z `SposobZaplatyID == pobranie`
-   Jeśli tak, dodać obsługę jako osobny typ płatności w `resolvePaymentDefinition()`

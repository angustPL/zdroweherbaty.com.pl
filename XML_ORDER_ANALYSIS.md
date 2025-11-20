# Analiza struktury XML zamówienia dla Enova

## Struktura główna

```xml
<?xml version="1.0" encoding="utf-8" ?>
<session xmlns="http://www.soneta.pl/schema/business" business="true">
  <Kontrahent business="false" id="id2" key="Kod=WWW"></Kontrahent>
  <DokumentHandlowy guid="GUID">
    <!-- Zawartość dokumentu -->
  </DokumentHandlowy>
</session>
```

## Elementy dokumentu

### 1. Kontrahent (stały)

-   **Element**: `<Kontrahent business="false" id="id2" key="Kod=WWW"></Kontrahent>`
-   **Wartość**: Zawsze `Kod=WWW` (kontrahent dla zamówień online)
-   **Lokalizacja**: Poza `DokumentHandlowy`, na poziomie `session`

### 2. DokumentHandlowy - Nagłówek

#### Definicja dokumentu

-   **Element**: `<Definicja where="Symbol=ZOW" />`
-   **Wartość**: `ZOW` (Zamówienie Online)
-   **Typ**: Stała wartość

#### Magazyn

-   **Element**: `<Magazyn where="Symbol=WWW" />`
-   **Wartość**: `WWW` (magazyn dla sklepu online)
-   **Typ**: Stała wartość

#### Daty i czas

-   **Element**: `<Data>YYYY-MM-DD</Data>` - data zamówienia
-   **Element**: `<Czas>HH:MM</Czas>` - czas zamówienia
-   **Element**: `<DataOperacji>YYYY-MM-DD</DataOperacji>` - data operacji (zwykle taka sama jak Data)

#### Kontrahent i Odbiorca

-   **Element**: `<Kontrahent where="Kod=WWW" />` - kontrahent (stały)
-   **Element**: `<Odbiorca where="Kod=WWW" />` - odbiorca (stały)

### 3. Platnosci (Płatności)

```xml
<Platnosci>
  <Platnosc class="Soneta.Kasa.Naleznosc,Soneta.Kasa">
    <SposobZaplaty>GUID</SposobZaplaty>
    <Kwota>159.44 PLN</Kwota>
    <TerminDni>0</TerminDni>
  </Platnosc>
</Platnosci>
```

**Pola**:

-   `SposobZaplaty` - GUID sposobu zapłaty z Enova (z `config('enova.payment.methods.*')`)
    -   PayU: `B4413968-D8EA-4810-8DFF-7735D65A92AF`
    -   Gotówka: `00000000-0003-0004-0001-000000000000`
    -   Pobranie: `B13EDB83-341B-4FC6-AAE7-20CF19837650`
    -   Przedpłata: `43931EB3-6259-497E-8545-656187F90D3C`
-   `Kwota` - kwota w formacie `"XXX.XX PLN"` (wartość numeryczna + `" PLN"`)
-   `TerminDni` - termin płatności w dniach:
    -   W starym kodzie: **zawsze `0`** (linia 400)
    -   Możliwe że Enova automatycznie ustawia termin na podstawie `SposobZaplaty`

### 4. Pozycje (Produkty)

```xml
<Pozycje>
  <Pozycja>
    <Towar where="Kod=KOD_PRODUKTU" />
    <Ilosc>1 pud</Ilosc>
    <Cena>6.99 PLN</Cena>
    <CenaPoRabacie>6.99 PLN</CenaPoRabacie>
    <Wspolczynnik>1/1</Wspolczynnik>
  </Pozycja>
</Pozycje>
```

**Pola dla każdej pozycji**:

-   `Towar where="Kod=XXX"` - kod produktu z Enova (pole `Kod` z tabeli `Towary`)
-   `Ilosc` - ilość z jednostką (np. `"1 pud"`, `"3 pud"`, `"1 szt"`)
    -   Format: `"{liczba} {jednostka}"` (spacja między liczbą a jednostką)
    -   Jednostka z `IloscSymbol` (która pochodzi z `Jednostka` lub `StandardowaIloscSymbol`)
-   `Cena` - cena jednostkowa brutto w formacie `"XXX.XX PLN"` (używamy `BruttoValue` lub `PrzecenaBruttoValue` jeśli istnieje)
-   `CenaPoRabacie` - cena po rabacie w formacie `"XXX.XX PLN"` (używamy `PrzecenaBruttoValue` jeśli istnieje, w przeciwnym razie `BruttoValue`)
-   `Wspolczynnik` - zawsze `"1/1"`

**Uwaga**: Dostawa również jest pozycją z kodem produktu dostawy (np. `"PRZES 000/003 PACZKOMAT INPOST PRZED"`)

### 5. DaneKontrahenta (Dane do faktury)

```xml
<DaneKontrahenta>
  <Nazwa>NAZWA_FIRMY</Nazwa>
  <NIP>1234567890</NIP>
  <Adres>
    <Ulica>ul. Nazwa Ulicy</Ulica>
    <NrDomu>1</NrDomu>
    <NrLokalu>2</NrLokalu>
    <Miejscowosc>Warszawa</Miejscowosc>
    <Poczta>Warszawa</Poczta>
    <KodPocztowy>00-000</KodPocztowy>
  </Adres>
</DaneKontrahenta>
```

**Pola**:

-   `Nazwa` - nazwa firmy (wymagane jeśli faktura)
-   `NIP` - numer NIP (wymagane jeśli faktura)
-   `Adres`:
    -   `Ulica` - nazwa ulicy (z prefiksem "ul. " jeśli potrzebne)
    -   `NrDomu` - numer domu
    -   `NrLokalu` - numer lokalu (opcjonalne, może być puste)
    -   `Miejscowosc` - miejscowość (opcjonalne)
    -   `Poczta` - poczta (wymagane)
    -   `KodPocztowy` - kod pocztowy **TYLKO CYFRY** (bez myślników, filtrowane przez `Zend_Filter_Digits`)

**Uwaga**: Sekcja `DaneKontrahenta` jest **ZAWSZE** dodawana - jeśli nie ma faktury, kopiujemy dane odbiorcy (linie 361-374 w starym kodzie)

### 6. DaneOdbiorcy (Dane dostawy)

```xml
<DaneOdbiorcy>
  <Nazwa>IMIĘ NAZWISKO</Nazwa>
  <NIP></NIP>
  <Adres>
    <Ulica>ul. Nazwa Ulicy</Ulica>
    <NrDomu>1</NrDomu>
    <NrLokalu></NrLokalu>
    <Telefon>123456789</Telefon>
    <Miejscowosc>Warszawa</Miejscowosc>
    <Poczta>Warszawa</Poczta>
    <KodPocztowy>00-000</KodPocztowy>
  </Adres>
</DaneOdbiorcy>
```

**Pola**:

-   `Nazwa` - imię i nazwisko (format: `"IMIĘ NAZWISKO"`)
-   `NIP` - opcjonalne, może być puste
-   `Adres`:
    -   `Ulica` - nazwa ulicy (z prefiksem "ul. " jeśli potrzebne)
    -   `NrDomu` - numer domu
    -   `NrLokalu` - numer lokalu (opcjonalne, może być puste)
    -   `Telefon` - numer telefonu (bez spacji, myślników)
    -   `Miejscowosc` - miejscowość (opcjonalne)
    -   `Poczta` - poczta (wymagane)
    -   `KodPocztowy` - kod pocztowy **TYLKO CYFRY** (bez myślników, filtrowane przez `Zend_Filter_Digits`)

### 7. Features (Dodatkowe informacje)

```xml
<features>
  <feature name="E-mail_zamowienia">email@example.com</feature>
  <feature name="Telefon_zamowienia">123456789</feature>
  <feature name="Uwagi">
    Tekst uwag (np. informacje o paczkomacie)
  </feature>
</features>
```

**Pola**:

-   `E-mail_zamowienia` - adres email klienta
-   `Telefon_zamowienia` - numer telefonu klienta
-   `Uwagi` - dodatkowe uwagi (np. informacje o paczkomacie, instrukcje dostawy)

## Mapowanie danych z zamówienia do XML

### Z bazy danych Order:

-   `ext_order_id` → `guid` w `DokumentHandlowy`
-   `customer_data` → `DaneOdbiorcy`
-   `invoice_data` → `DaneKontrahenta` (jeśli `invoice_required = true`)
-   `items` (JSON) → `Pozycje`
-   `delivery_info` → pozycja dostawy w `Pozycje` + `Uwagi` w `features`
-   `payment_method_guid` → `SposobZaplaty` w `Platnosci`
-   `total` → `Kwota` w `Platnosci`

### Z koszyka (CartService):

-   `items[productId]` → pozycje produktów
-   Każdy item potrzebuje:
    -   `id` → pobranie `Kod` z bazy Enova (`Product::find($id)->Kod`)
    -   `quantity` → `Ilosc`
    -   `price` → `Cena` i `CenaPoRabacie`
    -   Jednostka z `Product->price->StandardowaIloscSymbol`

### Z wybranej dostawy:

-   `id` → kod produktu dostawy (z tabeli `Towary`)
-   `name` → nazwa dostawy (może być w uwagach)
-   `price` → pozycja dostawy w `Pozycje`
-   Informacje o paczkomacie → `Uwagi` w `features`

## Wymagane dane do generowania XML

### Z zamówienia (Order model):

1. `ext_order_id` - GUID zamówienia
2. `customer_data` - dane klienta (JSON)
3. `invoice_data` - dane do faktury (JSON, opcjonalne)
4. `items` - pozycje zamówienia (JSON)
5. `delivery_info` - informacje o dostawie (JSON)
6. `payment_method_guid` - GUID sposobu zapłaty
7. `total` - całkowita kwota
8. `subtotal` - wartość produktów
9. `delivery_cost` - koszt dostawy
10. `created_at` - data i czas zamówienia

### Z produktów (Product model):

-   `Kod` - kod produktu w Enova (dla `Towar where="Kod=XXX"`)
-   `price->BruttoValue` - cena brutto
-   `price->StandardowaIloscSymbol` - jednostka (dla `Ilosc`)

### Z dostawy (Delivery model):

-   `Kod` - kod produktu dostawy
-   `price->BruttoValue` - cena dostawy

## Formatowanie wartości

### Kwoty

-   Format: Wartość numeryczna + `" PLN"` (bez formatowania do 2 miejsc po przecinku - PHP automatycznie formatuje float)
-   Przykład: `"159.44 PLN"`, `"0 PLN"`, `"6.99 PLN"`
-   Uwaga: W starym kodzie używano bezpośrednio wartości z bazy (`$t['BruttoValue'] . ' PLN'`)
-   Zalecenie: Użyć `number_format($value, 2, '.', '')` dla pewności

### Daty

-   Format: `"YYYY-MM-DD"` (Zend_Date::toString('YYYY-MM-dd'))
-   Przykład: `"2025-05-29"`

### Czas

-   Format: `"HH:MM"` (Zend_Date::TIME_SHORT)
-   Przykład: `"15:53"`

### Kod pocztowy

-   Format: **TYLKO CYFRY** (bez myślników!)
-   Filtrowanie: `Zend_Filter_Digits` - usuwa wszystkie znaki niebędące cyframi
-   Przykład: `"00-791"` → `"00791"`, `"05-532"` → `"05532"`
-   **WAŻNE**: W przykładzie XML widzimy `02791` i `05532` - bez myślników!
-   Implementacja: `preg_replace('/[^0-9]/', '', $kod)` lub `filter_var($kod, FILTER_SANITIZE_NUMBER_INT)`

### Ilość

-   Format: `"{liczba} {jednostka}"` (spacja między liczbą a jednostką)
-   Przykład: `"1 pud"`, `"3 pud"`, `"1 szt"`
-   Jednostka z `IloscSymbol` (która pochodzi z `Jednostka` lub `StandardowaIloscSymbol`)

## Przykładowe wartości stałe

-   `Kontrahent key`: `"Kod=WWW"`
-   `Definicja`: `"ZOW"`
-   `Magazyn`: `"WWW"`
-   `Kontrahent where`: `"Kod=WWW"`
-   `Odbiorca where`: `"Kod=WWW"`
-   `Platnosc class`: `"Soneta.Kasa.Naleznosc,Soneta.Kasa"`
-   `Wspolczynnik`: `"1/1"`

## Uwagi implementacyjne

1. **GUID zamówienia**: Używamy `ext_order_id` z zamówienia (już w formacie GUID)
2. **Kod produktu**: Musimy pobrać `Kod` z tabeli `Towary` dla każdego produktu w koszyku
3. **Jednostka**: Pobieramy z `price->StandardowaIloscSymbol` lub `Jednostka`
4. **Dostawa jako pozycja**: Dostawa również musi być dodana jako pozycja w `Pozycje` (z kodem produktu dostawy)
5. **Paczkomat**: Informacje o paczkomacie (jeśli wybrano) trafiają do `Uwagi` w `features`
6. **Termin płatności**:
    - Zawsze `0` w starym kodzie (linia 400)
    - Możliwe że Enova automatycznie ustawia termin na podstawie `SposobZaplaty`
7. **Dane kontrahenta**: ZAWSZE są dodawane, nawet jeśli nie ma faktury - wtedy kopiujemy dane odbiorcy (linie 361-374)
8. **CenaPoRabacie**: Używamy `PrzecenaBruttoValue` jeśli istnieje, w przeciwnym razie `BruttoValue`
9. **Escape'owanie XML**: W starym kodzie NIE MA escape'owania! Może być problem z `<`, `>`, `&` w nazwach produktów/adresach - **NALEŻY DODAĆ** `htmlspecialchars()` lub użyć DOMDocument
10. **Formatowanie kwot**: Wartości float są formatowane automatycznie przez PHP, ale warto użyć `number_format($value, 2, '.', '')` dla pewności
11. **Kod pocztowy**: **WAŻNE** - używać tylko cyfr (filtrować przez `preg_replace('/[^0-9]/', '', $kod)` lub podobne)
12. **Struktura generowania**: Stary kod używa tablicy stringów i `implode("\n", $str)` - można użyć DOMDocument dla bezpieczeństwa

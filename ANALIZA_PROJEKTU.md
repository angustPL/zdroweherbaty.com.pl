# 📊 Analiza Projektu - Zdrowe Herbaty Laravel 12

**Data analizy:** 2025-01-XX  
**Wersja Laravel:** 12.0  
**Status:** Kopia serwisu po resecie workspace

---

## 🎯 Przegląd Projektu

### Cel Projektu
Modernizacja sklepu internetowego z **Zend Framework 1** na **Laravel 12** z zachowaniem kompatybilności z bazą danych **Enova (MSSQL)**.

### Główne Założenia
- ✅ Modernizacja technologiczna (ZF1 → Laravel 12)
- ✅ Kompatybilność z istniejącą bazą Enova
- ✅ Brak systemu użytkowników (prosty sklep)
- ✅ Każde zamówienie niezależne (brak kont klientów)
- ✅ Dane klienta w cookies
- ✅ Brak kontroli stanów magazynowych

---

## 🏗️ Architektura Techniczna

### Stack Technologiczny

#### Backend
- **Laravel 12** - framework PHP
- **Livewire Volt** - komponenty reaktywne (inline PHP)
- **Livewire Dedicated Classes** - dedykowane klasy dla złożonych komponentów
- **MSSQL** - baza danych Enova (read-only)
- **SQLite** - lokalna baza danych (zamówienia, płatności, promocje)

#### Frontend
- **Tailwind CSS 4.0** - framework CSS
- **Alpine.js** - interaktywność JavaScript
- **Flux UI** - komponenty UI
- **Vite** - bundler assetów

#### Integracje
- **PayU REST API** - płatności online
- **Algolia Scout** - wyszukiwarka produktów
- **Meilisearch** - alternatywna wyszukiwarka
- **Artesaos SEO Tools** - SEO meta tagi i Schema.org

---

## 📁 Struktura Projektu

### Modele Enova (Read-Only)

Wszystkie modele Enova dziedziczą z `EnovaModel` i są **read-only**:

```
app/Models/
├── EnovaModel.php          # Bazowy model (read-only, failover, cache)
├── Product.php             # Produkty (Towary)
├── Group.php               # Grupy produktów (Features)
├── Feature.php             # Cechy/atrybuty produktów
├── Price.php               # Ceny produktów
├── Delivery.php            # Opcje dostawy
└── PaymentMethod.php       # Sposoby płatności
```

**Kluczowe funkcjonalności:**
- ✅ Automatyczny failover (primary → backup host)
- ✅ Cache z fallback (48h TTL, używa cache nawet jeśli wygasł gdy Enova nie działa)
- ✅ Globalne scope'y (notBlocked, hasGroup, hasProductMark)
- ✅ Metody cache'ujące: `getCachedAll()`, `getCachedById()`, `getCachedByGroup()`

### Modele Lokalne

```
app/Models/
├── Order.php               # Zamówienia
├── Payment.php             # Płatności
├── Promotion.php           # Promocje
├── PromotionGroup.php      # Promocje grupowe
├── PromotionProduct.php    # Promocje produktowe
├── OrderPromotion.php      # Promocje w zamówieniach
├── Content.php             # Treści SEO
└── User.php                # Użytkownicy (opcjonalne)
```

### Serwisy

```
app/Services/
├── CartService.php         # Zarządzanie koszykiem (cookies)
├── PayuService.php         # Integracja PayU REST API
├── EnovaXmlService.php     # Generowanie XML dla Enova
└── PromotionService.php    # Logika promocji
```

### Komponenty Livewire

#### Dedykowane Klasy (Dedicated Classes)
```
app/Livewire/
├── Components/
│   ├── CartIcon.php           # Ikona koszyka z licznikiem
│   ├── AddToCartButton.php    # Przycisk dodawania do koszyka
│   ├── ProductCard.php        # Karta produktu
│   ├── SimilarProducts.php    # Podobne produkty
│   └── SearchProducts.php     # Wyszukiwarka produktów
└── Pages/
    └── Cart.php               # Strona koszyka
```

#### Komponenty Volt (Inline PHP)
```
resources/views/livewire/
├── components/
│   └── desktop-sidebar.blade.php    # Sidebar z grupami
└── pages/
    ├── welcome.blade.php            # Strona główna
    ├── group.blade.php              # Strona grupy produktów
    ├── product.blade.php            # Strona produktu
    ├── delivery.blade.php           # Strona dostawy
    ├── contact.blade.php            # Formularz kontaktowy
    ├── order-create.blade.php       # Proces zamawiania
    └── order-info.blade.php         # Informacje o zamówieniu
```

---

## 🔧 Kluczowe Funkcjonalności

### 1. System Koszyka

**CartService** - zarządzanie koszykiem w cookies:
- ✅ Dodawanie/usuwanie produktów
- ✅ Aktualizacja ilości
- ✅ Automatyczne obliczanie totalów (cena, ilość, waga)
- ✅ TTL: 30 dni

**Komponenty:**
- `CartIcon` - ikona z licznikiem produktów
- `AddToCartButton` - dynamiczny przycisk ("W koszyku" / "Dodaj do koszyka")
- `Cart` - strona koszyka z edycją ilości

### 2. System Zamówień

**Proces zamawiania:**
1. Wybór dostawy (na podstawie wagi koszyka)
2. Wybór płatności (zależny od dostawy)
3. Dane klienta (dostawa + opcjonalna faktura)
4. Finalizacja → tworzenie zamówienia lokalnie
5. Generowanie XML dla Enova (przez Event Listener)
6. Przekierowanie do PayU (jeśli przedpłata)

**Model Order:**
- Statusy: `PENDING`, `PROCESSING`, `COMPLETED`, `CANCELLED`
- Dane klienta, dostawy, faktury
- Promocje, paczkomat (JSON)
- Relacje: `payments()`, `promotion()`, `promotions()`

### 3. Integracja PayU

**PayuService** - PayU REST API:
- ✅ OAuth token (cache 1h)
- ✅ Tworzenie zamówień
- ✅ Weryfikacja sygnatury (notify_url)
- ✅ Mapowanie statusów płatności
- ✅ Obsługa sandbox/produkcja

**Kontroler:** `PayuController`
- `/payu/notify` - webhook od PayU
- `/payu/success` - powrót po płatności
- `/order/{id}/retry-payment` - ponowienie płatności

### 4. Generowanie XML dla Enova

**EnovaXmlService** - generowanie XML zamówień:
- ✅ Struktura zgodna z Enova (session, DokumentHandlowy)
- ✅ Escape'owanie XML (htmlspecialchars)
- ✅ Formatowanie kwot, kodów pocztowych, telefonów
- ✅ Wysyłka przez FTP lub kopia do katalogu

**Struktura XML:**
```xml
<session>
  <Kontrahent key="Kod=WWW" />
  <DokumentHandlowy guid="...">
    <Definicja where="Symbol=ZOW" />
    <Platnosci>...</Platnosci>
    <Pozycje>...</Pozycje>
    <DaneKontrahenta>...</DaneKontrahenta>
    <DaneOdbiorcy>...</DaneOdbiorcy>
    <features>...</features>
  </DokumentHandlowy>
</session>
```

### 5. System Cache'owania

**Strategia cache'owania:**
1. **Najpierw cache** - sprawdza cache przed połączeniem z Enova
2. **Fallback do Enova** - jeśli brak cache, pobiera z Enova
3. **Fallback do wygasłego cache** - jeśli Enova nie działa, używa cache nawet jeśli wygasł

**Metody cache'ujące:**
- `Product::getCachedAll()` - wszystkie produkty
- `Product::getCachedById($id)` - pojedynczy produkt
- `Product::getCachedByGroup($path)` - produkty w grupie
- `Group::getHierarchicalStructure()` - hierarchia grup
- `Delivery::getCachedAll()` - opcje dostawy

**TTL:** 48 godzin (172800 sekund)

**Komenda:** `php artisan enova:generate-backup-cache`
- Generuje cache dla wszystkich danych
- Wysyła email z raportem
- Cron: codziennie o 4:00

### 6. Failover Enova

**EnovaModel::ensureWorkingConnection():**
1. Próbuje połączyć z `DB_ENOVA_HOST` (primary)
2. Jeśli primary nie działa → przełącza na `DB_ENOVA_HOST_BACKUP`
3. Cache działającego hosta (5 minut)
4. Logowanie przełączeń

**Zmienne środowiskowe:**
```env
DB_ENOVA_HOST=adres_podstawowy
DB_ENOVA_HOST_BACKUP=adres_rezerwowy
DB_ENOVA_PORT=1433
DB_ENOVA_DATABASE=nazwa_bazy
DB_ENOVA_USERNAME=uzytkownik
DB_ENOVA_PASSWORD=haslo
```

### 7. System Promocji

**Modele:**
- `Promotion` - główny model promocji
- `PromotionGroup` - promocje dla grup
- `PromotionProduct` - promocje dla produktów
- `OrderPromotion` - promocje w zamówieniach (pivot)

**PromotionService:**
- Walidacja kodów rabatowych
- Obliczanie zniżek
- Sprawdzanie limitów użycia

### 8. SEO

**Artesaos SEO Tools:**
- ✅ Meta tags (title, description, canonical)
- ✅ Open Graph (Facebook, Twitter)
- ✅ Twitter Cards
- ✅ Schema.org JSON-LD (WebSite, Product, CollectionPage, ContactPage)

**Model Content:**
- Treści SEO z bazy danych
- Cache'owanie treści
- Warianty identyfikatorów grup

### 9. Wyszukiwarka

**Algolia Scout:**
- Indeksowanie produktów
- Filtrowanie po kategoriach, cenach
- Sortowanie po nazwie, cenie
- Paginacja wyników

**Komponent:** `SearchProducts` (Livewire)

---

## ⚙️ Konfiguracja

### Pliki Konfiguracyjne

```
config/
├── enova.php              # Konfiguracja Enova (features, ceny, dostawa, zamówienia, płatności)
├── seotools.php           # Konfiguracja SEO Tools
├── scout.php              # Konfiguracja Algolia/Meilisearch
├── googletagmanager.php   # Google Tag Manager
└── ...
```

### Zmienne Środowiskowe

**Baza danych Enova:**
```env
DB_ENOVA_HOST=...
DB_ENOVA_HOST_BACKUP=...
DB_ENOVA_PORT=1433
DB_ENOVA_DATABASE=BIFIX
DB_ENOVA_USERNAME=...
DB_ENOVA_PASSWORD=...
```

**PayU:**
```env
PAYU_SANDBOX=true
PAYU_POS_ID=...
PAYU_KEY=...
PAYU_KEY2=...
PAYU_POS_AUTH_KEY=...
PAYU_CONTINUE_URL=...
PAYU_NOTIFY_URL=...
```

**Cache:**
```env
ENOVA_CACHE_TTL=86400  # 24 godziny
CACHE_DRIVER=file      # lub redis
```

---

## 📋 Routing

### Główne Trasy

```php
/                           # Strona główna
/grupa/{group}              # Strona grupy produktów
/towar/{id}/{name?}         # Strona produktu
/wyszukaj                   # Wyszukiwarka
/koszyk                     # Koszyk
/zamawianie                 # Proces zamawiania
/zamowienie/{id}            # Informacje o zamówieniu
/dostawa                    # Strona dostawy
/kontakt                    # Formularz kontaktowy
/regulamin                  # Regulamin
```

### PayU Callbacks

```php
POST /payu/notify           # Webhook od PayU
GET  /payu/success           # Powrót po płatności
GET  /zamowienie/{id}/retry-payment  # Ponowienie płatności
```

### Cache Management

```php
GET  /cache                 # Panel zarządzania cache
GET  /cache/status/{type}   # Status cache
POST /cache/clear/{type}    # Czyszczenie cache
POST /cache/clear/all       # Czyszczenie wszystkich cache
```

---

## 🗄️ Baza Danych

### Lokalna Baza (SQLite)

**Tabele:**
- `orders` - zamówienia
- `payments` - płatności
- `promotions` - promocje
- `promotion_groups` - promocje grupowe
- `promotion_products` - promocje produktowe
- `order_promotions` - promocje w zamówieniach (pivot)
- `contents` - treści SEO

### Baza Enova (MSSQL)

**Główne tabele:**
- `Towary` - produkty i opcje dostawy
- `Features` - cechy/atrybuty produktów
- `Ceny` - ceny produktów
- `SposobyZaplaty` - sposoby płatności
- `DefStawekVat` - definicje stawek VAT

---

## 🚀 Komendy Artisan

### Cache Enova

```bash
# Generuj backup cache (codziennie o 4:00 przez cron)
php artisan enova:generate-backup-cache

# Wymuś regenerację cache
php artisan enova:generate-backup-cache --force

# Sprawdź status cache
php artisan enova:generate-backup-cache --check
```

### Wyszukiwarka

```bash
# Indeksuj produkty
php artisan scout:import "App\Models\Product"

# Wyczyść indeks
php artisan scout:flush "App\Models\Product"
```

---

## ✅ Status Implementacji

### Zrealizowane ✅

- [x] Podstawowa infrastruktura Laravel 12
- [x] Modele Enova (read-only, failover, cache)
- [x] System koszyka (cookies)
- [x] Proces zamawiania
- [x] Integracja PayU REST API
- [x] Generowanie XML dla Enova
- [x] System promocji
- [x] SEO (meta tags, Schema.org)
- [x] Wyszukiwarka (Algolia)
- [x] Cache'owanie z fallback
- [x] Failover Enova (primary → backup)

### Do Rozważenia ⚠️

- [ ] Model VAT (zgodnie z TODO w README)
- [ ] Model przecen (zgodnie z TODO w README)
- [ ] Optymalizacja wydajności (zgodnie z TODO w README)
- [ ] Animacje i przejścia (zgodnie z TODO w README)
- [ ] Testy użyteczności (zgodnie z TODO w README)

---

## 🔍 Potencjalne Problemy i Zalecenia

### 1. Pliki Środowiskowe ✅

**Status:** ✅ Pliki `.env` i `.env.production` są gotowe.

**Uwaga:** Upewnić się, że `.env` nie jest commitowany do repozytorium (sprawdzić `.gitignore`).

### 2. Konfiguracja PayU

**Status:** ✅ Zaimplementowane
- Sandbox/produkcja
- OAuth token cache
- Weryfikacja sygnatury
- Obsługa różnych metod płatności

**Uwaga:** Sprawdzić czy `PAYU_SANDBOX` jest ustawione na `false` na produkcji.

### 3. Cache'owanie

**Status:** ✅ Zaimplementowane z fallback
- TTL: 48 godzin
- Automatyczny fallback do wygasłego cache
- Komenda do generowania cache

**Uwaga:** Upewnić się, że cron job jest skonfigurowany na produkcji.

### 4. Failover Enova

**Status:** ✅ Zaimplementowane
- Automatyczne przełączanie primary → backup
- Cache działającego hosta (5 minut)
- Logowanie przełączeń

**Uwaga:** Sprawdzić czy `DB_ENOVA_HOST_BACKUP` jest ustawione na produkcji.

### 5. Walidacja Paczkomatu

**Status:** ✅ Zaimplementowane (zgodnie z ANALIZA_DOSTAWA_PLATNOSC.md)
- Walidacja w `validateStep2()`
- Sprawdzanie cookie `selectedParcelLocker` lub `parcelLockerData`

### 6. Obsługa Wielkogabarytowych

**Status:** ⏭️ Pominięte (zgodnie z ANALIZA_DOSTAWA_PLATNOSC.md)
- Użytkownik zdecydował, że nie będzie implementowane

### 7. Filtrowanie Opcji przy Darmowej Dostawie

**Status:** ⏭️ Pominięte (zgodnie z ANALIZA_DOSTAWA_PLATNOSC.md)
- Użytkownik zdecydował, że przy darmowej dostawie będą takie same formy płatności

---

## 📝 Dokumentacja

### Pliki MD w Projekcie

1. **README.md** - główna dokumentacja projektu
2. **ANALIZA_DOSTAWA_PLATNOSC.md** - analiza dostawy i płatności (stary vs nowy system)
3. **CACHE_METHODS.md** - dokumentacja metod cache'ujących
4. **CHANGE_DATADIR_INSTRUCTIONS.md** - instrukcja zmiany lokalizacji datadir MariaDB
5. **CRON_SETUP.md** - konfiguracja cron job dla backup cache
6. **PAYU_SANDBOX_CONFIG.md** - konfiguracja PayU Sandbox
7. **README_ENV.md** - konfiguracja środowisk
8. **SEO_IMPLEMENTATION.md** - implementacja SEO Tools
9. **XML_ORDER_ANALYSIS.md** - analiza struktury XML zamówienia

---

## 🎯 Następne Kroki

### Priorytet 1: Weryfikacja Konfiguracji

1. ✅ Pliki `.env` i `.env.production` są gotowe
2. ⚠️ Sprawdzić czy wszystkie zmienne środowiskowe są poprawnie ustawione
3. ⚠️ Sprawdzić czy cron job jest skonfigurowany
4. ⚠️ Sprawdzić czy failover Enova działa
5. ⚠️ Sprawdzić czy PayU działa (sandbox/produkcja)

### Priorytet 2: Testy

1. ⚠️ Uruchomić testy jednostkowe
2. ⚠️ Uruchomić testy funkcjonalne
3. ⚠️ Przetestować proces zamawiania end-to-end
4. ⚠️ Przetestować integrację PayU

### Priorytet 3: Optymalizacja

1. ⚠️ Sprawdzić wydajność cache'owania
2. ⚠️ Sprawdzić wydajność zapytań do Enova
3. ⚠️ Sprawdzić Core Web Vitals

---

## 📊 Podsumowanie

### Mocne Strony ✅

- ✅ Nowoczesna architektura (Laravel 12, Livewire)
- ✅ Dobrze zorganizowany kod
- ✅ Kompleksowa dokumentacja
- ✅ System cache'owania z fallback
- ✅ Failover Enova
- ✅ Integracja PayU REST API
- ✅ SEO (meta tags, Schema.org)
- ✅ Wyszukiwarka (Algolia)

### Obszary do Usprawnienia ⚠️

- ⚠️ Niektóre funkcjonalności z TODO nie są zaimplementowane (VAT, przeceny)
- ⚠️ Wymaga weryfikacji konfiguracji na produkcji

### Ogólna Ocena

**Status:** ✅ Projekt jest w dobrym stanie technicznym

Projekt został dobrze zorganizowany, ma kompleksową dokumentację i implementuje kluczowe funkcjonalności. Wymaga weryfikacji konfiguracji i testów przed wdrożeniem na produkcję.

---

**Ostatnia aktualizacja:** 2025-01-XX  
**Wersja analizy:** 1.0.0


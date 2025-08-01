# Zdrowe Herbaty - Nowa Wersja

## Opis Projektu

Nowa wersja sklepu internetowego Zdrowe Herbaty, oparta na frameworku Laravel 12 z Livewire Volt. Projekt jest modernizacją istniejącego sklepu opartego na Zend Framework 1, z zachowaniem kompatybilności z bazą danych Enova.

## Założenia Projektu

-   Modernizacja technologiczna z ZF1 na Laravel 12 + Livewire
-   Zachowanie kompatybilności z istniejącą bazą danych Enova
-   Uproszczenie struktury kodu przy zachowaniu funkcjonalności
-   Implementacja nowoczesnych rozwiązań i praktyk programistycznych
-   Brak systemu użytkowników/kont (prosty sklep)
-   Każde zamówienie jest niezależne (brak kont klientów)
-   Dane klienta przechowywane w cookies dla ułatwienia wypełniania formularzy
-   Brak kontroli stanów magazynowych

## Technologie

-   **Laravel 12** - framework PHP
-   **Livewire Volt** - komponenty reaktywne
-   **Tailwind CSS** - framework CSS
-   **Alpine.js** - interaktywność JavaScript
-   **Flux UI** - komponenty UI
-   **MSSQL** - baza danych Enova

## Struktura Bazy Danych (Enova)

### Tabele Główne

-   `dbo.Towary` - główna tabela produktów
-   `dbo.Features` - cechy/atrybuty produktów
-   `dbo.Ceny` - ceny produktów
-   `dbo.DefStawekVat` - definicje stawek VAT
-   `dbo.PrzecenyOkres` i `dbo.PrzecenyOkresTwr` - przeceny okresowe
-   `dbo.SposobyZaplaty` - sposoby płatności

### System Kategoryzacji

Enova używa systemu atrybutów do kategoryzacji produktów:

-   Kategoryzacja jest realizowana przez atrybut `www_grupa` w tabeli `Features`
-   Hierarchia kategorii jest oddzielona separatorem "--"
-   URL-e kategorii mają przedrostek "kategoria"
-   Atrybuty produktów są przechowywane w tabeli `Features`:
    -   `Parent` - ID produktu
    -   `Name` - nazwa atrybutu
    -   `Data` - wartość atrybutu
    -   `LP` - kolejność (używane do sortowania)

### Ważne Atrybuty Produktów

-   `www_grupa` - kategoria produktu
-   `www_nazwa` - nazwa produktu dla sklepu
-   `www_sklep` - dostępność w sklepie
-   `www_na_zamowienie` - produkt na zamówienie
-   `www_hasla` - słowa kluczowe
-   `www_stan_magazynu` - stan magazynowy (opcjonalnie)
-   `Forma Płatności dla dostawy` - sposób płatności dla opcji dostawy

## TODO Lista

### Faza 1 - Podstawowa Infrastruktura ✅

-   [x] Inicjalizacja projektu Laravel
-   [x] Konfiguracja Git i repozytorium
-   [x] Konfiguracja środowiska deweloperskiego
-   [x] Konfiguracja połączenia z bazą Enova

### Faza 2 - Modele i Mapowanie Danych ✅

-   [x] Implementacja bazowego modelu EnovaModel (read-only)
-   [x] Implementacja modelu produktów (Towary)
-   [x] Implementacja modelu grup produktowych (Group)
-   [x] Implementacja modelu atrybutów (Features)
-   [x] Implementacja modelu cen (Price)
-   [x] Implementacja modelu dostawy (Delivery)
-   [x] Implementacja modelu sposobów płatności (PaymentMethod)
-   [ ] Implementacja modelu VAT
-   [ ] Implementacja modelu przecen

### Faza 3 - Podstawowe Funkcjonalności 🚧

-   [x] Implementacja listy produktów w grupach
-   [x] Implementacja hierarchicznego sidebar z grupami
-   [x] Implementacja wyszukiwania w grupach
-   [x] Implementacja automatycznego rozwijania grup
-   [x] Implementacja stron statycznych (home, dostawa, regulamin, kontakt)
-   [x] Implementacja systemu koszyka z cookies
-   [x] Implementacja komponentu "Dodaj do koszyka"
-   [x] Implementacja ikony koszyka w header
-   [x] Implementacja strony koszyka z tabelą produktów
-   [x] Implementacja strony dostawy z opcjami i sposobami płatności
-   [ ] Implementacja widoku produktu
-   [ ] Implementacja procesu zamawiania

### Faza 4 - Optymalizacja i Usprawnienia

-   [x] Implementacja cache'owania
-   [x] Optymalizacja zapytań do bazy
-   [x] Implementacja wyszukiwarki produktów z Meilisearch
-   [ ] Optymalizacja wydajności

### Faza 5 - Frontend i UX

-   [x] Implementacja responsywnego designu
-   [x] Implementacja sidebar z grupami
-   [x] Implementacja wyszukiwania w czasie rzeczywistym
-   [x] Implementacja efektów hover na kartach produktów
-   [x] Implementacja dynamicznych przycisków koszyka
-   [ ] Implementacja animacji i przejść
-   [ ] Testy użyteczności

## Wymagania Systemowe

-   PHP 8.3+
-   Composer
-   Node.js i NPM
-   Git
-   Dostęp do bazy danych Enova
-   Sterowniki MSSQL dla PHP

## Instalacja i Konfiguracja

1. Sklonuj repozytorium
2. Skopiuj `.env.example` do `.env` i skonfiguruj zmienne środowiskowe
3. Zainstaluj zależności PHP: `composer install`
4. Zainstaluj zależności Node.js: `npm install`
5. Wygeneruj klucz aplikacji: `php artisan key:generate`
6. Uruchom serwer deweloperski: `php artisan serve`

## Funkcjonalności Livewire

### Refaktoryzacja z Volt na Dedicated Classes

Projekt przeszedł refaktoryzację z komponentów Volt (inline PHP) na dedykowane klasy Livewire dla lepszej architektury i debugowania:

#### Komponenty z Dedicated Classes:

-   **CartIcon** (`app/Livewire/Components/CartIcon.php`) - ikona koszyka z licznikiem
-   **AddToCartButton** (`app/Livewire/Components/AddToCartButton.php`) - przycisk dodawania do koszyka
-   **Cart** (`app/Livewire/Pages/Cart.php`) - strona koszyka
-   **ProductCard** (`app/Livewire/Components/ProductCard.php`) - karta produktu

#### Komunikacja między komponentami:

-   Używanie `protected $listeners = ['cart-updated' => 'loadCart']`
-   Eventy `$this->dispatch('cart-updated')`
-   Automatyczne odświeżanie komponentów po zmianach w koszyku

### Komponenty Volt

#### Desktop Sidebar (`desktop-sidebar.blade.php`)

-   Hierarchiczne wyświetlanie grup produktów
-   Wyszukiwanie w czasie rzeczywistym
-   Automatyczne rozwijanie grup nadrzędnych
-   Wyróżnianie aktualnej grupy

#### Mobile Sidebar (`sidebar-with-groups.blade.php`)

-   Responsywny sidebar dla urządzeń mobilnych
-   Menu nawigacyjne
-   Grupy produktów

#### Strona Grupy (`grupa.blade.php`)

-   Wyświetlanie produktów w grupie
-   Nazwy produktów z features
-   Ceny z właściwej definicji
-   Responsywny grid layout
-   Komponenty "Dodaj do koszyka" z dynamicznym tekstem

#### System Koszyka

-   **CartService** - serwis zarządzający koszykiem w cookies
-   **Cart Icon** - ikona koszyka w header z licznikiem produktów
-   **Add to Cart Button** - dynamiczny przycisk z tekstem "W koszyku"/"Dodaj do koszyka"
-   **Cart Page** - strona koszyka z tabelą produktów i możliwością edycji ilości

### Strony Statyczne

-   **Home** (`welcome.blade.php`) - strona główna
-   **Dostawa** (`dostawa.blade.php`) - informacje o dostawie z opcjami i sposobami płatności
-   **Regulamin** (`regulamin.blade.php`) - regulamin sklepu
-   **Kontakt** (`kontakt.blade.php`) - dane kontaktowe

## Modele Enova

### Globalny warunek Blokada=0

Wszystkie modele Enova automatycznie filtrują rekordy z warunkiem `Towary.Blokada = 0`. Jest to niezbędny warunek globalny, który:

-   **Filtruje aktywne produkty** - wyklucza zablokowane/nieaktywne rekordy
-   **Zapewnia spójność danych** - tylko aktywne produkty są wyświetlane
-   **Jest automatyczny** - nie trzeba pamiętać o dodawaniu tego warunku

#### Modele z globalnym scope `notBlocked`:

-   **Product** - produkty nie zablokowane
-   **Delivery** - opcje dostawy nie zablokowane
-   **Group** - grupy produktów nie zablokowane

### Model Delivery

Model `Delivery` reprezentuje opcje dostawy w systemie Enova.

#### Główne funkcjonalności:

-   Global scope filtrujący opcje dostawy po grupie (`www_dostawasklep`)
-   Relacja do modelu Price z filtrowaniem po definicji ceny
-   Relacja do modelu PaymentMethod przez Features
-   Sortowanie po masie brutto i cenie
-   Mapowanie danych do wyświetlenia

#### Przykład użycia:

```php
// Pobranie opcji dostawy z cenami i sposobami płatności
$deliveries = Delivery::with(['price', 'paymentMethod'])
    ->orderBy('MasaBruttoValue')
    ->orderBy('price.BruttoValue')
    ->get();

// Pobranie sposobu płatności dla dostawy
$paymentMethod = $delivery->paymentMethod->Nazwa;
```

### Model PaymentMethod

Model `PaymentMethod` reprezentuje sposoby płatności w systemie Enova.

#### Główne funkcjonalności:

-   Read-only model (dane zarządzane w Enova)
-   Tabela: `SposobyZaplaty`
-   Relacja do Delivery przez Features

#### Przykład użycia:

```php
// Pobranie sposobu płatności
$paymentMethod = PaymentMethod::find($id);
$methodName = $paymentMethod->Nazwa;
```

### Integracja z Enova - Grupy Produktów

### Model Group

Model `Group` reprezentuje grupy produktów w systemie Enova. Każda grupa jest przechowywana w tabeli `Features` z odpowiednim prefiksem.

#### Główne funkcjonalności:

-   Automatyczne filtrowanie grup po prefiksie zdefiniowanym w konfiguracji (`config('enova.features.product_group_prefix')`)
-   Czyszczenie nazw grup (usuwanie prefiksu i końcowego ukośnika)
-   Relacja z produktami przez klucz obcy `Parent`
-   Hierarchiczna struktura grup

#### Przykład użycia:

```php
// Pobranie wszystkich grup
$groups = \App\Models\Group::all();

// Pobranie nazwy grupy (z automatycznym czyszczeniem)
$cleanName = $group->clean_name; // np. "Herbaty zielone"

// Pobranie hierarchicznej struktury
$hierarchicalGroups = Group::getHierarchicalStructure();
```

### Model Product

Model `Product` reprezentuje produkty w systemie Enova i zawiera rozszerzoną funkcjonalność związaną z grupami.

#### Główne funkcjonalności:

-   Automatyczne filtrowanie produktów posiadających grupę
-   Relacja do modelu Group
-   Relacja do modelu Price z filtrowaniem po definicji
-   Relacja do features (nazwa produktu)
-   Scope do wyszukiwania po nazwie grupy
-   Metoda `toDisplayArray()` do mapowania danych

#### Przykład użycia:

```php
// Pobranie wszystkich produktów z grupą
$products = \App\Models\Product::all();

// Pobranie produktów z konkretnej grupy
$productsInGroup = \App\Models\Product::whereGroupIs('Herbaty zielone')->get();

// Pobranie grupy dla produktu
$groupName = $product->group->clean_name;

// Pobranie nazwy z features
$productName = $product->productNameFeature->Name;

// Pobranie ceny
$price = $product->price->BruttoValue;

// Mapowanie do wyświetlenia
$displayData = $product->toDisplayArray();
```

### Model Price

Model `Price` reprezentuje ceny produktów w systemie Enova.

#### Główne funkcjonalności:

-   Global scope filtrujący po definicji ceny
-   Relacja do modelu Product
-   Automatyczne pobieranie właściwej definicji ceny
-   Optymalizacja - pobieranie tylko potrzebnych kolumn

## System Koszyka

### CartService

Serwis zarządzający koszykiem zakupowym z wykorzystaniem cookies.

#### Główne funkcjonalności:

-   **Dodawanie produktów** - `addToCart($productId, $name, $price, $image)`
-   **Aktualizacja ilości** - `updateQuantity($productId, $quantity)`
-   **Usuwanie produktów** - `removeFromCart($productId)`
-   **Czyszczenie koszyka** - `clearCart()`
-   **Sprawdzanie zawartości** - `isProductInCart($productId)`
-   **Automatyczne obliczanie totalów** - `updateCartTotals()`

#### Przykład użycia:

```php
$cartService = app(CartService::class);

// Dodanie produktu
$cartService->addToCart(123, 'Herbata Zielona', 25.99, '123_200x120.jpg');

// Sprawdzenie czy produkt jest w koszyku
$isInCart = $cartService->isProductInCart(123);

// Pobranie koszyka
$cart = $cartService->getCart();
```

### Komponenty Koszyka

#### Cart Icon (`app/Livewire/Components/CartIcon.php`)

-   Ikona koszyka w header z licznikiem produktów
-   Automatyczne odświeżanie po zmianach w koszyku
-   Listener na event `cart-updated`
-   Link do strony koszyka

#### Add to Cart Button (`app/Livewire/Components/AddToCartButton.php`)

-   Dynamiczny przycisk z tekstem zależnym od stanu koszyka
-   Efekt hover z ikoną dla produktów już w koszyku
-   Loading state podczas dodawania
-   Dispatch event `cart-updated` po dodaniu

#### Cart Page (`app/Livewire/Pages/Cart.php`)

-   Tabela produktów z możliwością edycji ilości
-   Debounce dla aktualizacji ilości (1 sekunda)
-   Przyciski +/- dla zmiany ilości
-   Automatyczne przeliczanie wartości
-   Komunikat o pustym koszyku z ikoną
-   Listener na event `cart-updated`

## Strona Dostawy

### Funkcjonalności:

-   **Dynamiczne opcje dostawy** - pobierane z bazy Enova
-   **Sposoby płatności** - przypisane do każdej opcji dostawy
-   **Sortowanie** - po masie brutto i cenie
-   **Konfigurowalny próg** - bezpłatna dostawa powyżej określonej kwoty
-   **Responsywna tabela** - z nowoczesnym designem

### Konfiguracja:

```php
// config/enova.php
'orders' => [
    'free_delivery_threshold' => env('ENOVA_ORDERS_FREE_DELIVERY_THRESHOLD', 80),
    'feature_payment_method' => env('ENOVA_ORDERS_FEATURE_PAYMENT_METHOD', 'Forma Płatności dla dostawy'),
],
```

### Relacje:

```
Towary.ID → Features.Parent (gdzie Name = 'Forma Płatności dla dostawy')
Features.Data → SposobyZaplaty.ID
```

## Struktura Katalogów

```
├── app/
│   ├── Models/         # Modele mapujące dane z Enova
│   │   ├── EnovaModel.php      # Bazowy model (read-only)
│   │   ├── Product.php         # Produkty
│   │   ├── Group.php           # Grupy produktów
│   │   ├── Feature.php         # Cechy/atrybuty
│   │   ├── Price.php           # Ceny
│   │   ├── Delivery.php        # Opcje dostawy
│   │   └── PaymentMethod.php   # Sposoby płatności
│   ├── Livewire/       # Komponenty Livewire
│   │   ├── Components/ # Komponenty (CartIcon, AddToCartButton)
│   │   └── Pages/      # Strony (Cart)
│   ├── Services/       # Serwisy biznesowe (CartService)
│   └── Http/
│       ├── Controllers/# Kontrolery
│       └── Requests/   # Walidacja requestów
├── config/
│   └── enova.php      # Konfiguracja Enova
├── resources/
│   └── views/
│       ├── livewire/   # Komponenty Livewire Volt
│       │   ├── components/  # Komponenty (product-card)
│       │   └── pages/       # Strony Volt (grupa, dostawa)
│       └── layouts/    # Layouty aplikacji
└── routes/
    └── web.php        # Definicje routingu
```

## Kontrybucja

1. Stwórz nową gałąź dla swojej funkcjonalności
2. Zrób commit zmian
3. Stwórz pull request

## Licencja

Prywatna - Wszelkie prawa zastrzeżone

## Konfiguracja tunelu SSH dla bazy MS SQL

Aby połączyć się z bazą MS SQL przez tunel SSH, użyj skryptu `scripts/start-tunnel.ps1`. Skrypt przyjmuje następujące parametry:

-   `$sshUser` - Twój login na serwerze SSH (domyślnie: `kaate`)
-   `$sshHost` - Adres serwera SSH (domyślnie: `kaate.pl`)
-   `$dbHost` - Adres serwera bazy danych (domyślnie: `178.183.13.109`)
-   `$dbPort` - Port bazy danych (domyślnie: `1433`)
-   `$localPort` - Lokalny port, na którym zostanie zestawiony tunel (domyślnie: `11433`)

Przykład uruchomienia:

```powershell
.\scripts\start-tunnel.ps1
```

Po uruchomieniu tunelu, w pliku `.env` ustaw:

```
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=11433
DB_DATABASE=BIFIX
DB_USERNAME=sklep
DB_PASSWORD=Enova2013,
DB_ENCRYPT=true
DB_TRUST_SERVER_CERTIFICATE=true
DB_READONLY=true
```

Następnie możesz przetestować połączenie, uruchamiając:

```sh
php artisan migrate:status
```

lub odwiedzając `/test-db` w przeglądarce.

## Konfiguracja połączenia zdalnego MSSQL z obsługą dwóch hostów (failover)

Aby umożliwić automatyczne przełączanie połączenia z bazą MSSQL na host rezerwowy w przypadku problemów z połączeniem, należy dodać do pliku `.env` następujące zmienne:

```
DB_ENOVA_HOST=adres_podstawowy
DB_ENOVA_HOST_BACKUP=adres_rezerwowy
DB_ENOVA_PORT=1433
DB_ENOVA_DATABASE=nazwa_bazy
DB_ENOVA_USERNAME=uzytkownik
DB_ENOVA_PASSWORD=haslo
```

-   `DB_ENOVA_HOST` – adres główny (np. IP lub DNS) serwera MSSQL
-   `DB_ENOVA_HOST_BACKUP` – adres rezerwowy (np. IP lub DNS) serwera MSSQL
-   Pozostałe zmienne jak w standardowej konfiguracji połączenia z bazą MSSQL

**Failover**: W kodzie aplikacji można zaimplementować logikę, która w przypadku problemów z połączeniem do hosta głównego, automatycznie przełączy połączenie na host rezerwowy.

Przykład użycia w kodzie:

```php
use Illuminate\Support\Facades\DB;

try {
    config(['database.connections.sqlsrv.host' => env('DB_ENOVA_HOST')]);
    $conn = DB::connection('sqlsrv');
    // ... zapytania ...
} catch (\Exception $e) {
    config(['database.connections.sqlsrv.host' => env('DB_ENOVA_HOST_BACKUP')]);
    $conn = DB::connection('sqlsrv');
    // ... zapytania ...
}
```

Dzięki temu aplikacja będzie mogła korzystać z rezerwowego hosta w przypadku awarii głównego połączenia.

## Wyszukiwarka produktów

### Algolia Scout

Wyszukiwarka produktów wykorzystuje **Algolia Scout** - zaawansowany system wyszukiwania w chmurze.

#### Instalacja i konfiguracja:

1. **Zainstaluj pakiety:**

```bash
composer require laravel/scout
composer require algolia/scout-extended
```

2. **Dodaj zmienne środowiskowe do `.env`:**

```
SCOUT_DRIVER=algolia
ALGOLIA_APP_ID=your_app_id
ALGOLIA_SECRET=your_admin_api_key
ALGOLIA_SEARCH=your_search_only_api_key
```

3. **Zaindeksuj produkty:**

```bash
php artisan scout:import "App\Models\Product"
```

4. **Aktualizuj indeks po zmianach:**

```bash
php artisan scout:flush "App\Models\Product"
```

#### Funkcjonalności wyszukiwarki:

-   **Wyszukiwanie w czasie rzeczywistym** - wyniki pojawiają się podczas wpisywania
-   **Filtrowanie po kategoriach** - wybierz konkretną grupę produktów
-   **Filtrowanie po cenach** - zakresy cenowe (budget, medium, premium, luxury)
-   **Sortowanie** - po nazwie lub cenie (rosnąco/malejąco)
-   **Paginacja** - wyniki podzielone na strony
-   **Responsywność** - działa na wszystkich urządzeniach

#### Komponent SearchProducts:

-   **Lokalizacja:** `app/Livewire/Components/SearchProducts.php`
-   **Widok:** `resources/views/livewire/components/search-products.blade.php`
-   **Route:** `/wyszukaj`

#### Konfiguracja Scout:

-   **Driver:** Algolia (cloud-based)
-   **Index:** `products`
-   **Searchable attributes:** name, description, group
-   **Filterable attributes:** group, price_range
-   **Sortable attributes:** price, name

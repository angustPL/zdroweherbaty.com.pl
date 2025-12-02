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

### Faza 3 - Podstawowe Funkcjonalności ✅

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
-   [x] Implementacja widoku produktu
-   [x] Implementacja procesu zamawiania
-   [x] Implementacja formularza kontaktowego z walidacją
-   [x] Implementacja systemu promocji i kodów rabatowych
-   [x] Implementacja podobnych produktów

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

## Komendy Artisan

### Generowanie Cache Enova

```bash
# Generuj backup cache (codziennie o 4:00 przez cron)
php artisan enova:generate-backup-cache

# Wymuś regenerację cache (ignoruje istniejący cache)
php artisan enova:generate-backup-cache --force

# Sprawdź status cache bez generowania
php artisan enova:generate-backup-cache --check
```

**Funkcjonalności:**

-   Generuje cache dla wszystkich produktów, grup, produktów w grupach i opcji dostawy
-   TTL: 48 godzin
-   Automatyczny fallback na cache w przypadku awarii Enova
-   Wysyłka emaila z raportem do admina po zakończeniu

## Funkcjonalności Livewire

### Refaktoryzacja z Volt na Dedicated Classes

Projekt przeszedł refaktoryzację z komponentów Volt (inline PHP) na dedykowane klasy Livewire dla lepszej architektury i debugowania:

#### Komponenty z Dedicated Classes:

-   **CartIcon** (`app/Livewire/Components/CartIcon.php`) - ikona koszyka z licznikiem
-   **AddToCartButton** (`app/Livewire/Components/AddToCartButton.php`) - przycisk dodawania do koszyka
-   **Cart** (`app/Livewire/Pages/Cart.php`) - strona koszyka z obsługą promocji
-   **ProductCard** (`app/Livewire/Components/ProductCard.php`) - karta produktu
-   **SimilarProducts** (`app/Livewire/Components/SimilarProducts.php`) - podobne produkty na stronie produktu
-   **SearchProducts** (`app/Livewire/Components/SearchProducts.php`) - wyszukiwarka produktów

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
-   SEO content z bazy danych
-   Optymalizacja wydajności (cache, lazy loading)

#### Strona Produktu (`product.blade.php`)

-   Szczegóły produktu z obrazkami
-   Komponent "Dodaj do koszyka" z wagą produktu
-   Podobne produkty (lazy loading)
-   Link do grupy produktu
-   SEO meta tags
-   GTM tracking

#### System Koszyka

-   **CartService** - serwis zarządzający koszykiem w cookies
-   **Cart Icon** - ikona koszyka w header z licznikiem produktów
-   **Add to Cart Button** - dynamiczny przycisk z tekstem "W koszyku"/"Dodaj do koszyka"
-   **Cart Page** - strona koszyka z tabelą produktów i możliwością edycji ilości

### Strony Statyczne

-   **Home** (`welcome.blade.php`) - strona główna
-   **Dostawa** (`dostawa.blade.php`) - informacje o dostawie z opcjami i sposobami płatności
-   **Regulamin** (`regulamin.blade.php`) - regulamin sklepu
-   **Kontakt** (`kontakt.blade.php`) - formularz kontaktowy z walidacją (Volt + Flux UI)

### Formularz Kontaktowy

Formularz kontaktowy został zaimplementowany z wykorzystaniem Livewire Volt i Flux UI:

-   **Walidacja:** Walidacja po stronie serwera z polskimi komunikatami błędów
-   **Komponenty:** Flux UI (`flux:input`, `flux:textarea`, `flux:button`)
-   **Email:** Automatyczne wysyłanie emaila do admina z informacjami o nadawcy
-   **Reply-To:** Ustawienie reply-to z nazwą nadawcy dla łatwej odpowiedzi
-   **Pola:** Imię i nazwisko, Email, Wiadomość

**Klasa Mail:** `App\Mail\ContactFormMail`
**Szablon emaila:** `resources/views/emails/contact-form.blade.php`

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

-   **Dodawanie produktów** - `addToCart($productId, $name, $price, $image, $quantity, $weight)`
-   **Aktualizacja ilości** - `updateQuantity($productId, $quantity)`
-   **Usuwanie produktów** - `removeFromCart($productId)`
-   **Czyszczenie koszyka** - `clearCart()`
-   **Sprawdzanie zawartości** - `isProductInCart($productId)`
-   **Automatyczne obliczanie totalów** - `updateCartTotals()` (cena, ilość, waga)
-   **Wyliczanie łącznej wagi** - automatyczne sumowanie wag wszystkich produktów

#### Przykład użycia:

```php
$cartService = app(CartService::class);

// Dodanie produktu z wagą
$cartService->addToCart(123, 'Herbata Zielona', 25.99, '123_200x120.jpg', 1, 50.0);

// Sprawdzenie czy produkt jest w koszyku
$isInCart = $cartService->isProductInCart(123);

// Pobranie koszyka (zawiera total_weight)
$cart = $cartService->getCart();
// $cart['total_weight'] - łączna waga wszystkich produktów
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

### System Wagi w Koszyku

#### Funkcjonalności wagi:

-   **Pobieranie wagi produktu** - automatyczne z bazy danych (`MasaBruttoValue`)
-   **Zapisywanie wagi w koszyku** - każdy produkt ma przypisaną wagę
-   **Wyliczanie łącznej wagi** - suma (waga × ilość) dla wszystkich produktów
-   **Aktualizacja wagi** - automatyczne przeliczanie przy zmianie ilości

#### Struktura danych koszyka:

```php
$cart = [
    'items' => [
        123 => [
            'id' => 123,
            'name' => 'Herbata Zielona',
            'price' => 25.99,
            'quantity' => 2,
            'image' => '123_200x120.jpg',
            'weight' => 50.0  // waga w gramach
        ]
    ],
    'total' => 51.98,
    'item_count' => 1,
    'total_weight' => 100.0  // łączna waga wszystkich produktów
];
```

#### Użycie w komponentach:

```php
// AddToCartButton automatycznie pobiera wagę z bazy
$product = Product::find($productId);
$this->weight = $product->MasaBruttoValue ?? 0;

// CartService wylicza łączną wagę
private function updateCartTotals(array &$cart): void
{
    $totalWeight = 0;
    foreach ($cart['items'] as $item) {
        $totalWeight += ($item['weight'] ?? 0) * $item['quantity'];
    }
    $cart['total_weight'] = $totalWeight;
}
```

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

#### Użycie w kodzie:

```php
use App\Models\Product;

// Wyszukiwanie z filtrami
$products = Product::search('herbata zielona')
    ->within('group', 'Herbaty zielone')
    ->within('price_range', 'medium')
    ->orderBy('price', 'asc')
    ->paginate(12);
```

## Cache'owanie i Optymalizacja

### Cache'owanie

Aplikacja wykorzystuje system cache'owania Laravel do optymalizacji wydajności:

#### Typy cache'owania:

-   **Cache bazy danych** - wyniki zapytań do Enova
-   **Backup cache** - cache zapasowy dla awarii serwera Enova (TTL: 48h)
-   **Cache widoków** - skompilowane szablony Blade
-   **Cache konfiguracji** - ustawienia aplikacji
-   **Cache routingu** - zdefiniowane ścieżki

#### Konfiguracja cache:

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'file'),
'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
],
```

#### Użycie w kodzie:

```php
use Illuminate\Support\Facades\Cache;

// Cache'owanie wyników zapytań
$products = Cache::remember('products_group_' . $groupId, 3600, function () use ($groupId) {
    return Product::whereGroupIs($groupId)->get();
});

// Inwalidacja cache
Cache::forget('products_group_' . $groupId);
```

#### Backup Cache dla Enova

Aplikacja automatycznie generuje cache dla danych Enova, który jest używany w przypadku awarii serwera Enova:

-   **Generowanie:** Codziennie o 4:00 rano (wymaga skonfigurowanego cron joba)
-   **TTL:** 48 godzin (bufor bezpieczeństwa na wypadek problemów z cronem)
-   **Zakres:** Wszystkie produkty, grupy, produkty w grupach, pojedyncze produkty, opcje dostawy
-   **Fallback:** Automatyczne użycie cache (nawet jeśli wygasł) gdy Enova nie odpowiada

**Dokumentacja metod cache'ujących:** Zobacz [CACHE_METHODS.md](CACHE_METHODS.md) dla pełnej listy wszystkich metod cache'ujących i ich użycia.

**Konfiguracja cron joba:**

```bash
# Dodaj do crontab (crontab -e)
# Uruchamia się codziennie o 4:00 rano
0 4 * * * cd /ścieżka/do/projektu && php artisan enova:generate-backup-cache >> /dev/null 2>&1
```

**Przykład dla `/var/www/zdroweherbaty.com.pl`:**

```bash
0 4 * * * cd /var/www/zdroweherbaty.com.pl && php artisan enova:generate-backup-cache >> /dev/null 2>&1
```

Szczegółowe instrukcje: zobacz `CRON_SETUP.md`

**Ręczne uruchomienie:**

```bash
# Generuj backup cache
php artisan enova:generate-backup-cache

# Wymuś regenerację
php artisan enova:generate-backup-cache --force

# Sprawdź status cache (bez generowania)
php artisan enova:generate-backup-cache --check
```

**Raport email po generowaniu cache:**

Po każdym uruchomieniu komendy `enova:generate-backup-cache` automatycznie wysyłany jest email z raportem do admina zawierający:

-   Status wykonania (sukces/błąd)
-   Statystyki: liczba produktów, grup, opcji dostawy
-   Czas wykonania
-   Informacje o błędach (jeśli wystąpiły)

**Klasa Mail:** `App\Mail\CacheGenerationReportMail`
**Szablon emaila:** `resources/views/emails/cache-generation-report.blade.php`
**Adres odbiorcy:** `config('enova.orders.email.address')` (domyślnie: `sklep@bifix.pl`)

### Optymalizacja Zapytań

#### Eager Loading:

```php
// Pobieranie produktów z relacjami w jednym zapytaniu
$products = Product::with(['group', 'price', 'features'])->get();
```

#### Select Only:

```php
// Pobieranie tylko potrzebnych kolumn
$products = Product::select(['ID', 'Nazwa', 'Symbol'])->get();
```

#### Query Scopes:

```php
// Globalne scope'y w modelach
class Product extends EnovaModel
{
    protected static function booted()
    {
        static::addGlobalScope('notBlocked', function ($query) {
            $query->where('Blokada', 0);
        });
    }
}
```

## Testowanie

### Struktura testów:

```
tests/
├── Feature/           # Testy funkcjonalności
│   ├── Auth/         # Testy autoryzacji
│   ├── Dashboard/    # Testy dashboardu
│   └── Settings/     # Testy ustawień
├── Unit/             # Testy jednostkowe
└── Pest.php          # Konfiguracja Pest
```

### Uruchamianie testów:

```bash
# Wszystkie testy
php artisan test

# Testy z coverage
php artisan test --coverage

# Konkretny test
php artisan test --filter=ProductTest
```

### Przykład testu:

```php
// tests/Feature/ProductTest.php
test('can display products in group', function () {
    $response = $this->get('/kategoria/herbaty-zielone');

    $response->assertStatus(200);
    $response->assertSee('Herbata Zielona');
});
```

## Deployment

### Wymagania produkcyjne:

-   **Serwer:** PHP 8.3+, MySQL/PostgreSQL, Redis (opcjonalnie)
-   **Web Server:** Nginx/Apache
-   **SSL:** Certyfikat SSL dla HTTPS
-   **Cache:** Redis lub Memcached dla lepszej wydajności

### Proces deploymentu:

1. **Przygotowanie serwera:**

    ```bash
    # Instalacja zależności systemowych
    sudo apt update
    sudo apt install php8.3-fpm nginx mysql-server redis-server
    ```

2. **Konfiguracja aplikacji:**

    ```bash
    # Sklonowanie kodu
    git clone <repository> /var/www/zdroweherbaty.com.pl
    cd /var/www/zdroweherbaty.com.pl

    # Instalacja zależności
    composer install --optimize-autoloader --no-dev
    npm install && npm run build

    # Konfiguracja środowiska
    cp .env.example .env
    php artisan key:generate
    ```

3. **Konfiguracja bazy danych:**

    ```bash
    # Migracje i seedery
    php artisan migrate --force
    php artisan db:seed --force

    # Indeksowanie wyszukiwarki
    php artisan scout:import "App\Models\Product"
    ```

4. **Optymalizacja:**

    ```bash
    # Cache'owanie
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Optymalizacja autoloadera
    composer dump-autoload --optimize
    ```

### Konfiguracja Nginx:

```nginx
server {
    listen 80;
    server_name zdroweherbaty.com.pl;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name zdroweherbaty.com.pl;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    root /var/www/zdroweherbaty.com.pl/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Monitoring i Logi

### Logi aplikacji:

-   **Laravel logs:** `storage/logs/laravel.log`
-   **Access logs:** `/var/log/nginx/access.log`
-   **Error logs:** `/var/log/nginx/error.log`

### Monitoring wydajności:

```bash
# Sprawdzenie statusu aplikacji
php artisan about

# Sprawdzenie połączenia z bazą
php artisan tinker
DB::connection()->getPdo();

# Sprawdzenie cache'owania
php artisan cache:table
php artisan cache:clear
```

### Backup bazy danych:

```bash
# Backup MSSQL (Windows)
sqlcmd -S server -U username -P password -Q "BACKUP DATABASE BIFIX TO DISK='C:\backup\bifix.bak'"

# Backup z tunelu SSH
ssh user@server "sqlcmd -S localhost -U username -P password -Q 'BACKUP DATABASE BIFIX TO DISK=\"/tmp/bifix.bak\"'"
scp user@server:/tmp/bifix.bak ./backup/
```

## Rozwiązywanie Problemów

### Typowe problemy i rozwiązania:

#### Problem z połączeniem do bazy:

```bash
# Sprawdzenie tunelu SSH
netstat -an | findstr 11433

# Test połączenia
php artisan tinker
DB::connection()->getPdo();
```

#### Problem z cache'owaniem:

```bash
# Czyszczenie cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### Problem z wyszukiwarce:

```bash
# Reset indeksu Algolia
php artisan scout:flush "App\Models\Product"
php artisan scout:import "App\Models\Product"
```

#### Problem z uprawnieniami:

```bash
# Ustawienie uprawnień na Linux
sudo chown -R www-data:www-data /var/www/zdroweherbaty.com.pl
sudo chmod -R 755 /var/www/zdroweherbaty.com.pl
sudo chmod -R 775 /var/www/zdroweherbaty.com.pl/storage
```

## Wsparcie i Kontakt

### Zespół deweloperski:

-   **Lead Developer:** [Nazwa]
-   **Backend:** [Nazwa]
-   **Frontend:** [Nazwa]
-   **DevOps:** [Nazwa]

### Kanały komunikacji:

-   **GitHub Issues:** [Link do repozytorium]
-   **Email:** [Email zespołu]
-   **Slack/Discord:** [Kanał zespołu]

### Dokumentacja dodatkowa:

-   **API Documentation:** [Link do dokumentacji API]
-   **User Manual:** [Link do instrukcji użytkownika]
-   **Developer Guide:** [Link do przewodnika dewelopera]

---

## System Promocji

Aplikacja obsługuje system promocji i kodów rabatowych:

### Funkcjonalności:

-   **Kody rabatowe** - możliwość wprowadzenia kodu promocyjnego w koszyku
-   **Promocje grupowe** - promocje przypisane do grup produktów
-   **Promocje produktowe** - promocje przypisane do konkretnych produktów
-   **Progi bezpłatnej dostawy** - automatyczne wykrywanie bezpłatnej dostawy
-   **Walidacja kodów** - sprawdzanie ważności i limitów użycia kodów

### Modele:

-   **Promotion** (`app/Models/Promotion.php`) - główny model promocji
-   **PromotionGroup** (`app/Models/PromotionGroup.php`) - promocje dla grup
-   **PromotionProduct** (`app/Models/PromotionProduct.php`) - promocje dla produktów
-   **OrderPromotion** (`app/Models/OrderPromotion.php`) - promocje zastosowane w zamówieniach

### Serwis:

-   **PromotionService** (`app/Services/PromotionService.php`) - logika biznesowa promocji

## SEO i Optymalizacja

### SEO Tools (Artesaos)

Aplikacja wykorzystuje pakiet `artesaos/seotools` do zarządzania meta tagami:

-   **Meta tags** - tytuł, opis, słowa kluczowe
-   **Open Graph** - tagi dla mediów społecznościowych
-   **Twitter Cards** - karty Twitter
-   **JSON-LD** - strukturalne dane dla wyszukiwarek

### Content Management

-   **Model Content** (`app/Models/Content.php`) - treści SEO z bazy danych
-   **Cache'owanie** - treści SEO są cache'owane dla lepszej wydajności
-   **Warianty identyfikatorów** - automatyczne wyszukiwanie treści po różnych wariantach nazw grup

## Email System

### Klasy Mail:

-   **ContactFormMail** (`app/Mail/ContactFormMail.php`) - email z formularza kontaktowego
-   **CacheGenerationReportMail** (`app/Mail/CacheGenerationReportMail.php`) - raport generowania cache
-   **OrderConfirmationMail** (`app/Mail/OrderConfirmationMail.php`) - potwierdzenie zamówienia

### Szablony Email:

-   `resources/views/emails/contact-form.blade.php` - szablon formularza kontaktowego
-   `resources/views/emails/cache-generation-report.blade.php` - szablon raportu cache
-   `resources/views/emails/order-confirmation.blade.php` - szablon potwierdzenia zamówienia

**Ostatnia aktualizacja:** 2025-12-01

**Wersja dokumentacji:** 2.0.0

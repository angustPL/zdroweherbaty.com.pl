# Zdrowe Herbaty - Nowa Wersja

## Opis Projektu

Nowa wersja sklepu internetowego Zdrowe Herbaty, oparta na frameworku Laravel 12. Projekt jest modernizacją istniejącego sklepu opartego na Zend Framework 1, z zachowaniem kompatybilności z bazą danych Enova.

## Założenia Projektu

-   Modernizacja technologiczna z ZF1 na Laravel 12
-   Zachowanie kompatybilności z istniejącą bazą danych Enova
-   Uproszczenie struktury kodu przy zachowaniu funkcjonalności
-   Implementacja nowoczesnych rozwiązań i praktyk programistycznych
-   Brak systemu użytkowników/kont (prosty sklep)
-   Każde zamówienie jest niezależne (brak kont klientów)
-   Dane klienta przechowywane w cookies dla ułatwienia wypełniania formularzy
-   Brak kontroli stanów magazynowych

## Struktura Bazy Danych (Enova)

### Tabele Główne

-   `dbo.Towary` - główna tabela produktów
-   `dbo.Features` - cechy/atrybuty produktów
-   `dbo.Ceny` - ceny produktów
-   `dbo.DefStawekVat` - definicje stawek VAT
-   `dbo.PrzecenyOkres` i `dbo.PrzecenyOkresTwr` - przeceny okresowe

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

## TODO Lista

### Faza 1 - Podstawowa Infrastruktura

-   [x] Inicjalizacja projektu Laravel
-   [x] Konfiguracja Git i repozytorium
-   [x] Konfiguracja środowiska deweloperskiego
-   [x] Konfiguracja połączenia z bazą Enova

### Faza 2 - Modele i Mapowanie Danych

-   [x] Implementacja bazowego modelu EnovaModel (read-only)
-   [x] Implementacja modelu produktów (Towary)
-   [x] Implementacja modelu grup produktowych (Group)
-   [x] Implementacja modelu atrybutów (Features)
-   [ ] Implementacja modelu cen
-   [ ] Implementacja modelu VAT
-   [ ] Implementacja modelu przecen

### Faza 3 - Podstawowe Funkcjonalności

-   [ ] Implementacja listy produktów
-   [ ] Implementacja widoku produktu
-   [ ] Implementacja kategorii
-   [ ] Implementacja koszyka
-   [ ] Implementacja procesu zamawiania

### Faza 4 - Optymalizacja i Usprawnienia

-   [ ] Implementacja cache'owania
-   [ ] Optymalizacja zapytań do bazy
-   [ ] Implementacja wyszukiwarki
-   [ ] Optymalizacja wydajności

### Faza 5 - Frontend i UX

-   [ ] Implementacja responsywnego designu
-   [ ] Optymalizacja UX
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

## Integracja z Enova - Grupy Produktów

### Model Group

Model `Group` reprezentuje grupy produktów w systemie Enova. Każda grupa jest przechowywana w tabeli `Features` z odpowiednim prefiksem.

#### Główne funkcjonalności:
- Automatyczne filtrowanie grup po prefiksie zdefiniowanym w konfiguracji (`config('enova.features.product_group_prefix')`)
- Czyszczenie nazw grup (usuwanie prefiksu i końcowego ukośnika)
- Relacja z produktami przez klucz obcy `Parent`

#### Przykład użycia:
```php
// Pobranie wszystkich grup
$groups = \App\Models\Group::all();

// Pobranie nazwy grupy (z automatycznym czyszczeniem)
$cleanName = $group->clean_name; // np. "Herbaty zielone"
```

### Model Product

Model `Product` reprezentuje produkty w systemie Enova i zawiera rozszerzoną funkcjonalność związaną z grupami.

#### Główne funkcjonalności:
- Automatyczne filtrowanie produktów posiadających grupę
- Relacja do modelu Group
- Scope do wyszukiwania po nazwie grupy

#### Przykład użycia:
```php
// Pobranie wszystkich produktów z grupą
$products = \App\Models\Product::all();

// Pobranie produktów z konkretnej grupy
$productsInGroup = \App\Models\Product::whereGroupIs('Herbaty zielone')->get();

// Pobranie grupy dla produktu
$groupName = $product->group->clean_name;
```

## Struktura Katalogów

```
├── app/
│   ├── Models/         # Modele mapujące dane z Enova
│   ├── Services/       # Serwisy biznesowe
│   └── Http/
│       ├── Controllers/# Kontrolery
│       └── Requests/   # Walidacja requestów
├── config/
│   └── enova.php      # Konfiguracja Enova
├── resources/
│   └── views/         # Widoki Blade
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

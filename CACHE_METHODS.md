# Metody Cache'ujące dla Enova

Dokumentacja wszystkich metod cache'ujących używanych w aplikacji do pobierania danych z Enova.

## Przegląd

Wszystkie metody cache'ujące dziedziczą z `EnovaModel` i używają uniwersalnej metody `getCachedWithBackup()`, która zapewnia:

-   **TTL:** 48 godzin (domyślnie)
-   **Fallback:** Automatyczne użycie cache (nawet jeśli wygasł) gdy Enova nie odpowiada
-   **Failover:** Automatyczne przełączanie między primary a backup hostem Enova

## Metody Cache'ujące

### Product (Produkty)

#### `Product::getCachedAll(?int $ttl = null): array`

-   **Opis:** Pobiera wszystkie produkty z cache'owaniem
-   **Cache Key:** `enova_products_all`
-   **Zapytanie SQL:** Wszystkie produkty z relacjami (productNameFeature, price, group, productMark)
-   **Użycie:** Strona główna, wyszukiwarka, lista wszystkich produktów
-   **Komenda:** `generateProductsCache()`

#### `Product::getCachedById(int $productId, ?int $ttl = null): ?array`

-   **Opis:** Pobiera pojedynczy produkt z cache'owaniem
-   **Cache Key:** `enova_product_{productId}`
-   **Zapytanie SQL:** Pojedynczy produkt po ID z relacjami
-   **Użycie:** Strona produktu (`/towar/{id}`)
-   **Komenda:** `generateIndividualProductsCache()` (dla wszystkich produktów)

#### `Product::getCachedByGroup(string $groupPath, ?int $ttl = null)`

-   **Opis:** Pobiera produkty w określonej grupie z cache'owaniem
-   **Cache Key:** `enova_products_group_{md5(groupPath)}`
-   **Zapytanie SQL:** Produkty filtrowane po ścieżce grupy
-   **Użycie:** Strona grupy (`/grupa/{group}`)
-   **Komenda:** `generateProductsByGroupCache()` (dla wszystkich grup)

### Group (Grupy)

#### `Group::getHierarchicalStructure(?int $ttl = null): array`

-   **Opis:** Pobiera hierarchiczną strukturę grup z produktami
-   **Cache Key:** `enova_groups_hierarchy_with_products`
-   **Zapytanie SQL:** Drzewo grup z produktami spełniającymi warunki (ma grupę, product_mark=1, nie zablokowany)
-   **Użycie:** Menu nawigacyjne, sidebar z grupami
-   **Komenda:** `generateGroupsCache()`

### Delivery (Dostawa)

#### `Delivery::getCachedAll(?int $ttl = null): array`

-   **Opis:** Pobiera wszystkie opcje dostawy z cache'owaniem
-   **Cache Key:** `enova_deliveries_all`
-   **Zapytanie SQL:** Opcje dostawy z cenami i metodami płatności
-   **Użycie:** Strona dostawy (`/dostawa`)
-   **Komenda:** `generateDeliveriesCache()`

## Komenda Generująca Cache

Komenda `php artisan enova:generate-backup-cache` automatycznie generuje cache dla wszystkich powyższych metod:

```bash
# Generowanie cache (pomija jeśli już istnieje)
php artisan enova:generate-backup-cache

# Wymuszenie regeneracji cache
php artisan enova:generate-backup-cache --force

# Sprawdzenie statusu cache
php artisan enova:generate-backup-cache --check
```

### Kolejność generowania:

1. **Wszystkie produkty** (`enova_products_all`)
2. **Hierarchia grup** (`enova_groups_hierarchy_with_products`)
3. **Produkty w grupach** (`enova_products_group_*` - dla każdej grupy)
4. **Pojedyncze produkty** (`enova_product_*` - dla każdego produktu)
5. **Opcje dostawy** (`enova_deliveries_all`)

## Nowe Produkty i Aktualizacje

### Jak działają nowe produkty?

1. **Automatyczne cache'owanie przy pierwszym dostępie:**

    - Gdy użytkownik odwiedza stronę produktu/grupy, która nie ma cache
    - Metoda cache'ująca automatycznie pobiera dane z Enova i zapisuje do cache
    - **Nie wymaga** ręcznego wywołania komendy

2. **Cron job (codziennie o 4:00):**

    - Automatycznie regeneruje wszystkie cache'y
    - Zapewnia świeże dane dla wszystkich produktów, grup i opcji dostawy
    - Nowe produkty dodane w Enova pojawią się najpóźniej po 48h (TTL cache) lub po następnym cronie

3. **Ręczna regeneracja:**
    ```bash
    php artisan enova:generate-backup-cache --force
    ```

### Kiedy nowe produkty pojawią się w cache?

-   **Natychmiast:** Jeśli użytkownik odwiedzi stronę produktu/grupy (automatyczne cache'owanie)
-   **Do 48h:** Jeśli cache już istnieje, nowe produkty pojawią się po wygaśnięciu TTL
-   **Następnego dnia:** Po uruchomieniu cron joba o 4:00

### Zalecenia:

-   **Dla nowych produktów:** Nie wymagają ręcznej akcji - pojawią się automatycznie przy pierwszym dostępie
-   **Dla zmian w istniejących produktach:** Można wyczyścić cache ręcznie lub poczekać na cron
-   **Dla masowych zmian:** Uruchomić komendę z `--force`

## Klucze Cache

Wszystkie klucze cache mają prefiks `enova_`:

-   `enova_products_all` - wszystkie produkty
-   `enova_product_{id}` - pojedynczy produkt
-   `enova_products_group_{md5(path)}` - produkty w grupie
-   `enova_groups_hierarchy_with_products` - hierarchia grup
-   `enova_deliveries_all` - opcje dostawy
-   `enova_working_host` - działający host Enova (primary/backup)

## Czyszczenie Cache

Cache można wyczyścić przez:

-   **API:** `POST /cache/clear/{type}` (patrz `CacheController`)
-   **Ręcznie:** `Cache::forget('enova_products_all')`
-   **Komenda:** `php artisan enova:generate-backup-cache --force` (regeneruje)

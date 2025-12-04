# 🚀 Instalacja na Hostingu - Krok po Kroku

**Projekt:** Zdrowe Herbaty Laravel 12  
**Data:** 2025-01-XX

---

## 📋 Wymagania Systemowe

### Minimalne wymagania:

-   **PHP:** 8.2+ (zalecane 8.3+)
-   **Composer:** 2.x
-   **Node.js:** 18.x+ (dla build assetów)
-   **NPM:** 9.x+
-   **Baza danych lokalna:** MariaDB 10.5+ lub MySQL 8.0+ (dla zamówień, płatności, promocji)
-   **Baza danych zdalna:** Enova (MSSQL) - dostęp tylko do odczytu przez cache
-   **Rozszerzenia PHP:**
    -   `pdo_sqlsrv` - sterownik MSSQL dla Enova
    -   `pdo_mysql` - lokalna baza danych (MariaDB/MySQL)
    -   `mbstring`, `xml`, `curl`, `zip`, `gd`, `fileinfo`
    -   `openssl` - dla PayU

### Wymagane dla produkcji:

-   **Redis** - cache, queue i sesje (używany przez aplikację)

---

## 🔧 Krok 1: Przygotowanie Plików

### Opcja A: Przez Git (zalecane)

```bash
# Na hostingu
cd /var/www/zdroweherbaty.com.pl  # lub inna ścieżka
git clone <repository-url> .
```

### Opcja B: Przez FTP/SFTP

1. Wgraj wszystkie pliki projektu na hosting
2. Upewnij się, że struktura katalogów jest zachowana

**Ważne katalogi do wgrania:**

-   `app/`
-   `bootstrap/`
-   `config/`
-   `database/`
-   `public/`
-   `resources/`
-   `routes/`
-   `storage/` (z uprawnieniami do zapisu)
-   `vendor/` (lub zainstaluj przez composer)
-   `composer.json`, `composer.lock`
-   `package.json`, `package-lock.json`
-   `vite.config.js`
-   `artisan`

---

## 🔧 Krok 2: Konfiguracja Środowiska

### 2.1. Skopiuj plik .env

```bash
# Na hostingu
cp .env.production .env
```

### 2.2. Edytuj plik .env

Uzupełnij wszystkie wymagane zmienne:

```env
# Aplikacja
APP_NAME="Zdrowe Herbaty"
APP_ENV=production
APP_KEY=  # Zostanie wygenerowany w następnym kroku
APP_DEBUG=false
APP_URL=https://zdroweherbaty.com.pl

# Baza danych lokalna (MariaDB/MySQL)
DB_CONNECTION=mariadb
DB_HOST=kaate.mysql.dhosting.pl
DB_PORT=3306
DB_DATABASE=opec7a_bifix
DB_USERNAME=au4goo_bifix
DB_PASSWORD=twoje_haslo

# Baza danych Enova (MSSQL)
DB_ENOVA_HOST=adres_serwera_enova
DB_ENOVA_HOST_BACKUP=adres_backup_enova  # opcjonalne
DB_ENOVA_PORT=1433
DB_ENOVA_DATABASE=BIFIX
DB_ENOVA_USERNAME=uzytkownik
DB_ENOVA_PASSWORD=haslo

# Cache (Redis)
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=/home/klient.dhosting.pl/kaate/.redis/redis.sock
REDIS_PORT=0
REDIS_PASSWORD=null
REDIS_CACHE_DB=1
REDIS_PREFIX=zdroweherbaty_database_

# Session (database)
SESSION_DRIVER=database
SESSION_CONNECTION=mariadb
SESSION_LIFETIME=120

# Queue (Redis)
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=email@example.com
MAIL_PASSWORD=haslo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=sklep@bifix.pl
MAIL_FROM_NAME="Sklep Bifix"

# PayU
PAYU_SANDBOX=false  # true dla testów, false dla produkcji
PAYU_POS_ID=twoj_pos_id
PAYU_KEY=twoj_klucz
PAYU_KEY2=twoj_drugi_klucz
PAYU_POS_AUTH_KEY=twoj_oauth_secret
PAYU_CONTINUE_URL=https://zdroweherbaty.com.pl/payu/success
PAYU_NOTIFY_URL=https://zdroweherbaty.com.pl/payu/notify

# Algolia/Meilisearch (jeśli używane)
SCOUT_DRIVER=algolia  # lub meilisearch
ALGOLIA_APP_ID=twoj_app_id
ALGOLIA_SECRET=twoj_secret
ALGOLIA_SEARCH=twoj_search_key

# Enova Orders
ENOVA_ORDERS_EMAIL_ADDRESS=sklep@bifix.pl
ENOVA_ORDERS_EMAIL_NAME="Sklep Bifix"
ENOVA_ORDERS_FTP_HOST=  # opcjonalne
ENOVA_ORDERS_FTP_USER=  # opcjonalne
ENOVA_ORDERS_FTP_PASS=  # opcjonalne

# Administrator (tworzony przez DatabaseSeeder)
ADMIN_EMAIL=admin@zdroweherbaty.com.pl
ADMIN_PASSWORD=twoje_bezpieczne_haslo
ADMIN_NAME=Administrator
```

### 2.3. Wygeneruj klucz aplikacji

```bash
php artisan key:generate
```

---

## 🔧 Krok 3: Instalacja Zależności

### 3.1. Zależności PHP (Composer)

```bash
# Na hostingu
composer install --no-dev --optimize-autoloader
```

**Uwaga o ostrzeżeniach deprecated:**
Jeśli widzisz ostrzeżenia typu `"Using ${var} in strings is deprecated"`:

-   To są tylko **warnings**, nie błędy - instalacja powinna działać
-   Zaktualizuj Composer: `composer self-update`
-   Lub zignoruj ostrzeżenia (nie blokują instalacji)

**Sprawdź czy instalacja się powiodła:**

```bash
# Sprawdź czy folder vendor istnieje
ls -la vendor/

# Sprawdź czy autoloader jest wygenerowany
ls -la vendor/autoload.php
```

**Uwaga:** Jeśli nie masz dostępu do `composer` na hostingu:

1. Zainstaluj zależności lokalnie
2. Wgraj folder `vendor/` na hosting

### 3.3. Zależności Node.js (NPM)

**Opcja A: Jeśli masz Node.js i npm na hostingu**

```bash
# Na hostingu
npm install --production
npm run build
```

**Opcja B: Jeśli NIE masz Node.js/npm na hostingu (Zalecane)**

Zbuduj assety lokalnie i wgraj na hosting:

```bash
# Na lokalnym komputerze
npm install
npm run build

# Wgraj folder public/build/ na hosting przez SFTP/FTP
```

**Instalacja Node.js na hostingu:**

Jeśli chcesz zainstalować Node.js na hostingu, zobacz szczegółowe instrukcje w pliku `INSTALACJA_NODEJS.md`.

**Szybka instalacja przez NVM (bez root):**

```bash
# Zainstaluj NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# Załaduj NVM
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Zainstaluj Node.js
nvm install --lts
nvm use --lts

# Sprawdź
node --version
npm --version
```

---

## 🔧 Krok 4: Konfiguracja Baz Danych

**Uwaga:** Projekt używa **2 baz danych**:

1. **Lokalna baza (MariaDB/MySQL)** - dla zamówień, płatności, promocji
2. **Zdalna baza Enova (MSSQL)** - tylko do odczytu, dane cache'owane lokalnie

### 4.1. Utwórz bazę danych lokalną (MariaDB/MySQL)

**Dla MariaDB:**

```bash
# Na hostingu (przez phpMyAdmin lub MySQL CLI)
# Baza danych jest już utworzona przez hosting (dhosting.pl)
# Użyj danych dostępowych z panelu hostingu
```

**Przykładowa konfiguracja (dhosting.pl):**

-   Host: `kaate.mysql.dhosting.pl`
-   Database: `opec7a_bifix`
-   Username: `au4goo_bifix`

**Uwaga:** Upewnij się, że zmienne w `.env` są poprawnie ustawione:

```env
DB_CONNECTION=mariadb
DB_HOST=kaate.mysql.dhosting.pl
DB_PORT=3306
DB_DATABASE=opec7a_bifix
DB_USERNAME=au4goo_bifix
DB_PASSWORD=twoje_haslo
```

**Uwaga o bazie Enova:**

-   Baza Enova (MSSQL) jest **zdalna** i dostępna tylko do odczytu
-   Dane z Enova są cache'owane lokalnie przez komendę `enova:generate-backup-cache`
-   Cache jest generowany codziennie o 4:00 przez cron job
-   W przypadku awarii Enova, aplikacja automatycznie używa cache (nawet jeśli wygasł)

### 4.2. Sprawdź połączenie z bazą danych

```bash
php artisan tinker
```

W tinker:

```php
DB::connection()->getPdo();
// Powinno zwrócić: PDO object
```

### 4.3. Uruchom migracje

```bash
php artisan migrate --force
```

**Sprawdź czy wszystkie migracje przeszły:**

```bash
php artisan migrate:status
```

### 4.4. Uruchom seedery

```bash
php artisan db:seed --force
```

-   `DatabaseSeeder` - tworzy użytkownika administratora z `.env`
-   `ContentSeeder` - treści SEO (regulamin, treści dla grup produktów, strona główna)
-   `PromotionSeeder` - promocje (darmowa dostawa, kody rabatowe)

**Uwaga:** `ContentSeeder` wymaga plików `.phtml` z treściami SEO. Jeśli nie masz tych plików, seeder pominie te treści (regulamin zostanie dodany zawsze).

**Uruchomienie pojedynczych seedów:**

```bash
# Tylko treści SEO
php artisan db:seed --class=ContentSeeder --force

# Tylko promocje
php artisan db:seed --class=PromotionSeeder --force
```

---

## 🔧 Krok 5: Konfiguracja Uprawnień

### 5.1. Uprawnienia do katalogów

```bash
# Na hostingu (Linux)
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Lub dla użytkownika hostingowego:**

```bash
chmod -R 775 storage bootstrap/cache
```

### 5.2. Utwórz katalogi storage

```bash
# Na hostingu
mkdir -p storage/app/enova/orders
mkdir -p storage/app/enova/orders/sent
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
chmod -R 775 storage
```

---

## 🔧 Krok 6: Konfiguracja Redis

**Uwaga:** Aplikacja używa Redis do cache, queue i sesji.

### 6.1. Sprawdź czy Redis działa

```bash
# Sprawdź czy Redis jest uruchomiony
redis-cli ping
# Powinno zwrócić: PONG
```

### 6.2. Sprawdź konfigurację Redis w `.env`

Upewnij się, że zmienne są ustawione:

```env
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=/home/klient.dhosting.pl/kaate/.redis/redis.sock
REDIS_PORT=0
REDIS_PASSWORD=null
REDIS_CACHE_DB=1
REDIS_PREFIX=zdroweherbaty_database_
```

**Uwaga:** Na hostingu dhosting.pl Redis używa socket file zamiast TCP.

### 6.3. Sprawdź czy Redis działa w Laravel

```bash
php artisan tinker
```

W tinker:

```php
Cache::put('test', 'value', 60);
Cache::get('test');
// Powinno zwrócić: "value"
```

---

## 🔧 Krok 7: Konfiguracja Bazy Enova (MSSQL)

**Uwaga:** Baza Enova jest **zdalna** i używana tylko do odczytu. Dane są cache'owane lokalnie.

### 7.1. Sprawdź połączenie z Enova

```bash
php artisan tinker
```

W tinker:

```php
DB::connection('sqlsrv')->getPdo();
// Powinno zwrócić: PDO object
```

**Jeśli połączenie nie działa:**

-   Sprawdź zmienne `DB_ENOVA_*` w `.env`
-   Sprawdź czy serwer Enova jest dostępny
-   Sprawdź czy failover działa (jeśli `DB_ENOVA_HOST_BACKUP` jest ustawione)

### 7.2. Wygeneruj cache Enova

**Ważne:** Jeśli widzisz błąd "DB_ENOVA_HOST nie jest zdefiniowany", wyczyść cache konfiguracji:

```bash
php artisan config:clear
```

Następnie wygeneruj cache:

```bash
php artisan enova:generate-backup-cache --force
```

**Sprawdź status:**

```bash
php artisan enova:generate-backup-cache --check
```

**Uwaga:**

-   Cache Enova jest generowany automatycznie codziennie o 4:00 przez cron job (patrz Krok 7).
-   Raporty z generowania cache są wysyłane na adres `ADMIN_EMAIL` z `.env`.

---

## 🔧 Krok 8: Indeksowanie Wyszukiwarki

### Jeśli używasz Algolia:

```bash
php artisan scout:import "App\Models\Product"
```

### Jeśli używasz Meilisearch:

```bash
# Upewnij się, że Meilisearch jest uruchomiony
php artisan scout:import "App\Models\Product"
```

---

## 🔧 Krok 9: Generowanie Cache Enova (Cron Job)

### 9.1. Wygeneruj backup cache (test)

```bash
php artisan enova:generate-backup-cache --force
```

**Sprawdź status:**

```bash
php artisan enova:generate-backup-cache --check
```

### 9.2. Skonfiguruj cron job (produkcja)

Dodaj do crontab (codziennie o 4:00):

```bash
crontab -e
```

Dodaj linię:

```bash
0 4 * * * cd /var/www/zdroweherbaty.com.pl && php artisan enova:generate-backup-cache >> /dev/null 2>&1
```

**Lub z logowaniem:**

```bash
0 4 * * * cd /var/www/zdroweherbaty.com.pl && php artisan enova:generate-backup-cache >> storage/logs/cron.log 2>&1
```

---

## 🔧 Krok 10: Optymalizacja Produkcyjna

### 10.1. Cache konfiguracji, routingu i widoków

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10.2. Optymalizacja autoloadera

```bash
composer dump-autoload --optimize
```

---

## 🔧 Krok 11: Konfiguracja Web Servera

### Nginx

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

    # Logi
    access_log /var/log/nginx/zdroweherbaty-access.log;
    error_log /var/log/nginx/zdroweherbaty-error.log;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Blokuj dostęp do plików ukrytych
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Blokuj dostęp do plików konfiguracyjnych
    location ~ \.(env|git|svn) {
        deny all;
    }

    # Cache statycznych plików
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache (.htaccess)

Plik `.htaccess` powinien być już w `public/.htaccess`. Jeśli nie, utwórz:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## 🔧 Krok 12: Weryfikacja Instalacji

### 12.1. Sprawdź status aplikacji

```bash
php artisan about
```

### 12.2. Sprawdź połączenie z lokalną bazą danych

```bash
php artisan tinker
```

W tinker:

```php
DB::connection()->getPdo();
// Powinno zwrócić: PDO object (lokalna baza MariaDB)
```

### 12.3. Sprawdź połączenie z Enova (MSSQL)

```bash
php artisan tinker
```

W tinker:

```php
DB::connection('sqlsrv')->getPdo();
// Powinno zwrócić: PDO object (zdalna baza Enova MSSQL)
```

### 12.4. Sprawdź Redis cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Następnie wygeneruj ponownie:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 11.5. Sprawdź logi

```bash
tail -f storage/logs/laravel.log
```

### 12.7. Automatyczna weryfikacja (opcjonalnie)

Możesz użyć skryptu weryfikacji, który automatycznie sprawdzi wszystkie elementy:

```bash
# Nadaj uprawnienia do wykonania (tylko raz)
chmod +x scripts/verify-installation.sh

# Uruchom weryfikację
./scripts/verify-installation.sh
```

**Uwaga:** Jeśli otrzymasz błąd "Permission denied", najpierw nadaj uprawnienia:

```bash
chmod +x scripts/verify-installation.sh
```

---

## ✅ Checklist Instalacji

Przed uruchomieniem testów upewnij się, że:

-   [ ] Wszystkie pliki są wgranie na hosting
-   [ ] Plik `.env` jest skonfigurowany
-   [ ] `APP_KEY` jest wygenerowany
-   [ ] Zależności PHP są zainstalowane (`composer install`)
-   [ ] Assety są zbudowane (`npm run build`)
-   [ ] Baza danych lokalna (MariaDB) jest utworzona i dostępna
-   [ ] Połączenie z bazą Enova (MSSQL) jest skonfigurowane
-   [ ] Redis jest skonfigurowany i działa
-   [ ] Session driver jest ustawiony na `database`
-   [ ] Migracje są uruchomione (`php artisan migrate`)
-   [ ] Uprawnienia do `storage/` i `bootstrap/cache/` są ustawione
-   [ ] Cache Enova jest wygenerowany
-   [ ] Cron job jest skonfigurowany
-   [ ] Web server (Nginx/Apache) jest skonfigurowany
-   [ ] SSL jest skonfigurowany (dla HTTPS)
-   [ ] Logi są dostępne i czytelne

---

## 🧪 Testy Po Instalacji

### 1. Test strony głównej

Otwórz w przeglądarce:

```
https://zdroweherbaty.com.pl
```

**Oczekiwany wynik:** Strona główna się wyświetla

### 2. Test połączenia z Enova

Otwórz w przeglądarce:

```
https://zdroweherbaty.com.pl/grupa/herbaty-zielone
```

**Oczekiwany wynik:** Produkty z grupy się wyświetlają

### 3. Test koszyka

1. Dodaj produkt do koszyka
2. Sprawdź czy koszyk działa
3. Przejdź do `/koszyk`

**Oczekiwany wynik:** Koszyk wyświetla produkty

### 4. Test procesu zamawiania

1. Dodaj produkty do koszyka
2. Przejdź do `/zamawianie`
3. Wypełnij formularz
4. Sprawdź czy zamówienie się tworzy

**Oczekiwany wynik:** Zamówienie jest tworzone w bazie

### 5. Test PayU (sandbox)

1. Utwórz zamówienie z przedpłatą
2. Sprawdź czy przekierowanie do PayU działa
3. Wykonaj testową płatność

**Oczekiwany wynik:** Płatność jest przetwarzana

---

## 🐛 Rozwiązywanie Problemów

### Problem: Błąd 500 Internal Server Error

**Rozwiązanie:**

1. Sprawdź logi: `tail -f storage/logs/laravel.log`
2. Sprawdź uprawnienia: `chmod -R 775 storage bootstrap/cache`
3. Wyczyść cache: `php artisan config:clear && php artisan cache:clear`

### Problem: Błąd połączenia z Enova

**Rozwiązanie:**

1. Sprawdź zmienne `DB_ENOVA_*` w `.env`
2. Sprawdź czy serwer Enova jest dostępny
3. Sprawdź czy failover działa: `php artisan tinker` → `DB::connection('sqlsrv')->getPdo()`

### Problem: Brak obrazów produktów

**Rozwiązanie:**

1. Sprawdź czy katalog `public/img/towary/` istnieje
2. Sprawdź uprawnienia: `chmod -R 755 public/img/`
3. Sprawdź czy obrazy są wgranione

### Problem: Cache nie działa

**Rozwiązanie:**

1. Wygeneruj cache: `php artisan enova:generate-backup-cache --force`
2. Sprawdź uprawnienia do `storage/framework/cache/`
3. Sprawdź czy `CACHE_DRIVER` jest ustawiony w `.env`

### Problem: PayU nie działa

**Rozwiązanie:**

1. Sprawdź zmienne `PAYU_*` w `.env`
2. Sprawdź czy `PAYU_SANDBOX` jest ustawione poprawnie
3. Sprawdź logi: `tail -f storage/logs/laravel.log | grep PayU`

---

## 📞 Wsparcie

Jeśli napotkasz problemy:

1. Sprawdź logi: `storage/logs/laravel.log`
2. Sprawdź dokumentację w plikach MD
3. Sprawdź konfigurację w `config/`

---

**Ostatnia aktualizacja:** 2025-01-XX  
**Wersja:** 1.0.0

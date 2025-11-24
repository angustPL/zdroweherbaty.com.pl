# Konfiguracja środowisk

## Pliki środowiskowe

-   `.env` - aktywna konfiguracja (nie commituj do repo!)
-   `.env.production` - konfiguracja produkcyjna (można commitować, ale bez haseł)
-   `.env.example` - szablon dla środowiska lokalnego

## Przełączanie środowisk

### Środowisko lokalne

```bash
# Skopiuj szablon
cp .env.example .env

# Edytuj .env i uzupełnij wartości dla lokalnego środowiska
# Następnie wygeneruj klucz aplikacji
php artisan key:generate
```

### Środowisko produkcyjne

```bash
# Skopiuj szablon produkcyjny
cp .env.production .env

# Edytuj .env i uzupełnij wrażliwe dane (hasła, klucze API)
# Następnie wygeneruj klucz aplikacji
php artisan key:generate
```

## Na produkcji

1. **Skopiuj `.env.production` do `.env`:**

    ```bash
    cp .env.production .env
    ```

2. **Uzupełnij wrażliwe dane w `.env`:**

    - Hasła do bazy danych
    - Klucze API (PayU, itp.)
    - Hasła do email (jeśli wymagane)
    - `APP_KEY` (wygeneruj: `php artisan key:generate`)

3. **Upewnij się, że `.env` nie jest w repozytorium:**
    - Sprawdź `.gitignore` - powinien zawierać `.env`

## Ważne zmienne do uzupełnienia w `.env.production`

Przed użyciem na produkcji uzupełnij:

-   `DB_HOST` - adres serwera bazy danych
-   `DB_DATABASE` - nazwa bazy danych
-   `DB_USERNAME` - użytkownik bazy danych
-   `DB_PASSWORD` - hasło do bazy danych
-   `MAIL_USERNAME` - użytkownik email (jeśli wymagane)
-   `MAIL_PASSWORD` - hasło email (jeśli wymagane)
-   `PAYU_POS_ID` - ID punktu płatności PayU
-   `PAYU_KEY` - klucz PayU
-   `PAYU_KEY2` - drugi klucz PayU
-   `PAYU_POS_AUTH_KEY` - klucz autoryzacji PayU
-   `ENOVA_ORDERS_FTP_HOST` - host FTP (jeśli używany)
-   `ENOVA_ORDERS_FTP_USER` - użytkownik FTP (jeśli używany)
-   `ENOVA_ORDERS_FTP_PASS` - hasło FTP (jeśli używany)
-   `APP_KEY` - klucz aplikacji (wygeneruj: `php artisan key:generate`)

## Bezpieczeństwo

⚠️ **NIGDY nie commituj pliku `.env` do repozytorium!**

-   `.env` zawiera wrażliwe dane (hasła, klucze API)
-   `.env.production` może zawierać przykładowe wartości, ale bez rzeczywistych haseł
-   Używaj zmiennych środowiskowych lub bezpiecznych menedżerów sekretów na produkcji

# Konfiguracja PayU Sandbox

## Wymagane kroki:

1. **Zarejestruj konto w PayU Sandbox**: https://secure.snd.payu.com/boarding/
2. **Utwórz sklep testowy** w panelu PayU Sandbox
3. **Wybierz typ punktu płatności**: REST API (Checkout)
4. **Pobierz dane konfiguracyjne** z panelu PayU:
    - ID punktu płatności (POS ID)
    - Klucz MD5 (Key)
    - Drugi klucz MD5 (Key2) - do weryfikacji sygnatury
    - OAuth Client ID (zwykle to samo co POS ID)
    - OAuth Client Secret (POS Auth Key)

## Zmienne środowiskowe do dodania w .env:

```env
# PayU Sandbox Configuration
PAYU_SANDBOX=true

# ID punktu płatności (POS ID) - znajdziesz w panelu PayU w sekcji "Punkty płatności"
PAYU_POS_ID=twoj_pos_id_z_sandbox

# Klucz MD5 (Key) - pierwszy klucz z panelu PayU
PAYU_KEY=twoj_klucz_md5

# Drugi klucz MD5 (Key2) - do weryfikacji sygnatury w notify_url
PAYU_KEY2=twoj_drugi_klucz_md5

# OAuth Client Secret (POS Auth Key) - do autoryzacji OAuth w REST API
# Zwykle znajdziesz to w panelu PayU w sekcji OAuth lub w szczegółach punktu płatności
PAYU_POS_AUTH_KEY=twoj_oauth_client_secret

# URL formularza płatności (nieużywany w REST API, ale można zostawić)
PAYU_URL=https://secure.snd.payu.com/paygw/UTF/NewPayment

# Opcje płatności PayU (domyślne wartości, zwykle nie wymagają zmiany)
PAYU_OPTION_BLIK=blik
PAYU_OPTION_CARD=c
PAYU_OPTION_GOOGLE_PAY=ap
PAYU_OPTION_APPLE_PAY=jp
PAYU_OPTION_TRANSFER=przelew

# URL logo PayU
PAYU_LOGO_URL=https://www.zdroweherbaty.com.pl/img/payu.png

# Callback URLs (dostosuj do swojej domeny)
# Dla środowiska produkcyjnego:
PAYU_CONTINUE_URL=https://beta.zdroweherbaty.com.pl/payu/success/
PAYU_NOTIFY_URL=https://beta.zdroweherbaty.com.pl/payu/notify/

# Dla środowiska deweloperskiego z fwd.host (dla domen .test):
# PAYU_CONTINUE_URL=https://fwd.host/http://zdroweherbaty.com.pl.test/payu/success
# PAYU_NOTIFY_URL=https://fwd.host/http://zdroweherbaty.com.pl.test/payu/notify
```

## Gdzie znaleźć dane w panelu PayU Sandbox:

1. Zaloguj się do panelu: https://secure.snd.payu.com/
2. Przejdź do sekcji **"Punkty płatności"** lub **"Payment Points"**
3. Wybierz swój punkt płatności
4. W sekcji **"Konfiguracja"** lub **"Configuration"** znajdziesz:
    - **POS ID** (ID punktu płatności)
    - **Klucz MD5** (Key)
    - **Drugi klucz MD5** (Key2)
5. W sekcji **"OAuth"** znajdziesz:
    - **Client ID** (zwykle to samo co POS ID)
    - **Client Secret** (POS Auth Key)

## Używanie fwd.host dla lokalnego developmentu

Jeśli pracujesz lokalnie i PayU wymaga publicznych URL dla callbacków, możesz użyć serwisu [fwd.host](https://herd.laravel.com/docs/macos/advanced-usage/social-auth) jako proxy.

`fwd.host` to serwis proxy, który przekierowuje żądania z publicznej domeny na lokalne domeny `.test`. Jest szczególnie przydatny, gdy:

-   Pracujesz na lokalnym serwerze z domeną `.test` (np. Laravel Herd)
-   PayU wymaga publicznych URL dla callbacków
-   Nie masz dostępu do tunelu (ngrok, localtunnel, etc.)

### Konfiguracja z fwd.host dla środowiska dev:

```env
# Użyj fwd.host jako proxy dla callbacków z domeny .test
PAYU_CONTINUE_URL=https://fwd.host/http://zdroweherbaty.com.pl.test/payu/success
PAYU_NOTIFY_URL=https://fwd.host/http://zdroweherbaty.com.pl.test/payu/notify
```

**Uwaga:** `fwd.host` jest przeznaczone specjalnie dla domen `.test` (Herd) i działa z nimi najlepiej. Dla środowiska deweloperskiego z domeną `zdroweherbaty.com.pl.test` to idealne rozwiązanie.

## Testowanie:

Po ustawieniu zmiennych środowiskowych:

1. Wyczyść cache konfiguracji: `php artisan config:clear`
2. Sprawdź czy serwis PayU działa (możesz przetestować przez stronę zamówienia)

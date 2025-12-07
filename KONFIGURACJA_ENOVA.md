# Konfiguracja Enova - Przewodnik

## Problem: "DB_ENOVA_HOST nie jest zdefiniowany"

### Przyczyny problemu

1. **Cache konfiguracji Laravel** - Laravel cache'uje pliki konfiguracyjne w `bootstrap/cache/config.php`

    - Jeśli zmienisz `.env`, ale nie wyczyścisz cache, stara wartość może być nadal używana
    - Rozwiązanie: `php artisan config:clear`

2. **Brak zmiennej w .env** - Zmienna `DB_ENOVA_HOST` nie jest ustawiona w pliku `.env`

    - Rozwiązanie: Dodaj `DB_ENOVA_HOST=...` do `.env`

3. **Błędna ścieżka do .env** - Aplikacja nie może znaleźć pliku `.env`
    - Rozwiązanie: Sprawdź czy `.env` istnieje w katalogu głównym projektu

### Jak zapobiec problemom

#### 1. Walidacja konfiguracji

Uruchom komendę walidacji przed ważnymi operacjami:

```bash
php artisan enova:validate-config
```

Komenda sprawdza:

-   Czy wszystkie wymagane zmienne są ustawione
-   Czy cache konfiguracji jest aktualny
-   Czy wartości w cache różnią się od `.env`

**Automatyczne naprawianie:**

```bash
php artisan enova:validate-config --fix
```

#### 2. Czyszczenie cache konfiguracji

Po każdej zmianie w `.env` wyczyść cache konfiguracji:

```bash
php artisan config:clear
```

**Lub wyczyść wszystkie cache:**

```bash
php artisan optimize:clear
```

#### 3. Sprawdzanie konfiguracji przed generowaniem cache

Przed uruchomieniem `enova:generate-backup-cache` sprawdź konfigurację:

```bash
# 1. Sprawdź konfigurację
php artisan enova:validate-config

# 2. Jeśli są problemy, napraw je
php artisan enova:validate-config --fix

# 3. Wygeneruj cache
php artisan enova:generate-backup-cache --force
```

## Wymagane zmienne środowiskowe

### Wymagane (bez nich aplikacja nie będzie działać)

```env
DB_ENOVA_HOST=adres_serwera_enova
DB_ENOVA_DATABASE=nazwa_bazy
DB_ENOVA_USERNAME=uzytkownik
DB_ENOVA_PASSWORD=haslo
```

### Opcjonalne (ale zalecane)

```env
DB_ENOVA_HOST_BACKUP=adres_backup_enova  # Failover na backup host
DB_ENOVA_PORT=1433                        # Port (domyślnie: 1433)
ADMIN_EMAIL=admin@zdroweherbaty.com.pl   # Email dla raportów
```

## Rozwiązywanie problemów

### Problem: Cache nie odbudowuje się

**Objawy:**

-   Komenda `enova:generate-backup-cache` kończy się z błędem
-   Błąd: "DB_ENOVA_HOST nie jest zdefiniowany - używaj cache"

**Rozwiązanie:**

1. Sprawdź konfigurację: `php artisan enova:validate-config`
2. Wyczyść cache konfiguracji: `php artisan config:clear`
3. Sprawdź czy `.env` zawiera `DB_ENOVA_HOST`
4. Spróbuj ponownie: `php artisan enova:generate-backup-cache --force`

### Problem: Email idzie na zły adres

**Objawy:**

-   Raporty z cache idą na `sklep@bifix.pl` zamiast na admina

**Rozwiązanie:**

1. Ustaw `ADMIN_EMAIL` w `.env`:
    ```env
    ADMIN_EMAIL=admin@zdroweherbaty.com.pl
    ```
2. Wyczyść cache konfiguracji: `php artisan config:clear`

### Problem: Stare wartości w cache konfiguracji

**Objawy:**

-   Zmieniłeś `.env`, ale aplikacja nadal używa starych wartości

**Rozwiązanie:**

```bash
php artisan config:clear
# lub
php artisan optimize:clear
```

## Checklist przed wdrożeniem na produkcję

-   [ ] Wszystkie wymagane zmienne są ustawione w `.env`
-   [ ] `php artisan enova:validate-config` nie pokazuje błędów
-   [ ] Cache konfiguracji jest wyczyszczony (`php artisan config:clear`)
-   [ ] `ADMIN_EMAIL` jest ustawiony
-   [ ] `DB_ENOVA_HOST_BACKUP` jest ustawiony (jeśli dostępny)
-   [ ] Test połączenia: `php artisan enova:generate-backup-cache --check`

## Przydatne komendy

```bash
# Walidacja konfiguracji
php artisan enova:validate-config
php artisan enova:validate-config --fix

# Czyszczenie cache
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Generowanie cache Enova
php artisan enova:generate-backup-cache
php artisan enova:generate-backup-cache --force
php artisan enova:generate-backup-cache --check

# Test połączenia
php artisan enova:test-connections
```

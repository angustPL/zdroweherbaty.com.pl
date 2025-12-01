# Konfiguracja Cron Job dla Backup Cache Enova

## Zalecane rozwiązanie: Bezpośrednie wywołanie komendy

Najprostsze i najbardziej efektywne rozwiązanie - uruchomienie komendy bezpośrednio raz dziennie.

### 1. Otwórz crontab

```bash
crontab -e
```

### 2. Dodaj następujący wpis

Dla serwera produkcyjnego (Linux):

```bash
0 4 * * * cd /ścieżka/do/projektu && php artisan enova:generate-backup-cache >> /dev/null 2>&1
```

**Przykład dla projektu w `/var/www/zdroweherbaty.com.pl`:**

```bash
0 4 * * * cd /var/www/zdroweherbaty.com.pl && php artisan enova:generate-backup-cache >> /dev/null 2>&1
```

### Wyjaśnienie formatu cron:

```
0 22 * * *
│  │  │ │ │
│  │  │ │ └─── Dzień tygodnia (0-7, gdzie 0 i 7 = niedziela)
│  │  │ └───── Miesiąc (1-12)
│  │  └─────── Dzień miesiąca (1-31)
│  └────────── Godzina (0-23) - tutaj 22:00
└───────────── Minuta (0-59) - tutaj 0 (początek godziny)
```

**Oznacza to:** Codziennie o 4:00 rano

### 3. Sprawdź czy cron działa

Możesz sprawdzić logi cron lub uruchomić ręcznie:

```bash
php artisan enova:generate-backup-cache
```

### 4. Sprawdź logi (opcjonalnie)

Jeśli chcesz logować wyniki do pliku:

```bash
0 4 * * * cd /ścieżka/do/projektu && php artisan enova:generate-backup-cache >> /ścieżka/do/projektu/storage/logs/backup-cache.log 2>&1
```

## Alternatywa: Laravel Schedule (jeśli masz więcej zadań)

Jeśli w przyszłości będziesz mieć więcej zadań do zaplanowania, możesz użyć Laravel Schedule:

### 1. Dodaj harmonogram w `bootstrap/app.php`:

```php
->withSchedule(function (Schedule $schedule) {
    $schedule->command('enova:generate-backup-cache')
        ->dailyAt('04:00')
        ->withoutOverlapping()
        ->runInBackground();
})
```

### 2. Dodaj cron job (uruchamia się co minutę):

```bash
* * * * * cd /ścieżka/do/projektu && php artisan schedule:run >> /dev/null 2>&1
```

**Uwaga:** To rozwiązanie jest mniej efektywne, ponieważ uruchamia Laravel co minutę, nawet gdy nie ma zadań do wykonania.

## Windows (Development)

Na Windows (lokalnie) możesz użyć Task Scheduler lub uruchamiać ręcznie:

```bash
php artisan enova:generate-backup-cache
```

## Sprawdzenie działania

Po skonfigurowaniu cron joba, możesz sprawdzić czy działa:

1. **Sprawdź logi Laravel:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Uruchom ręcznie (dla testów):**
   ```bash
   php artisan enova:generate-backup-cache
   ```

3. **Sprawdź cache:**
   ```bash
   php artisan tinker
   >>> Cache::has('enova_backup_products_all')
   ```


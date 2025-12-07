<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ValidateEnovaConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enova:validate-config
                            {--fix : Automatycznie wyczyść cache konfiguracji jeśli wykryto problem}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Waliduje konfigurację Enova i sprawdza czy wszystkie wymagane zmienne są ustawione';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sprawdzanie konfiguracji Enova...');
        $this->newLine();

        $errors = [];
        $warnings = [];
        $fixed = false;

        // Sprawdź wymagane zmienne środowiskowe
        $requiredVars = [
            'DB_ENOVA_HOST' => 'Adres serwera Enova (primary)',
            'DB_ENOVA_DATABASE' => 'Nazwa bazy danych Enova',
            'DB_ENOVA_USERNAME' => 'Użytkownik bazy danych Enova',
            'DB_ENOVA_PASSWORD' => 'Hasło do bazy danych Enova',
        ];

        $optionalVars = [
            'DB_ENOVA_HOST_BACKUP' => 'Adres serwera Enova (backup) - opcjonalne',
            'DB_ENOVA_PORT' => 'Port bazy danych (domyślnie: 1433)',
            'ADMIN_EMAIL' => 'Email admina dla raportów (domyślnie: admin@zdroweherbaty.com.pl)',
        ];

        // Sprawdź wymagane zmienne
        foreach ($requiredVars as $var => $description) {
            $value = env($var);
            if (empty($value)) {
                $errors[] = [
                    'variable' => $var,
                    'description' => $description,
                    'status' => 'BRAK',
                ];
            } else {
                $this->line("  ✓ {$var}: " . $this->maskSensitive($var, $value));
            }
        }

        // Sprawdź opcjonalne zmienne
        foreach ($optionalVars as $var => $description) {
            $value = env($var);
            if (empty($value)) {
                $warnings[] = [
                    'variable' => $var,
                    'description' => $description,
                ];
            } else {
                $this->line("  ✓ {$var}: " . $this->maskSensitive($var, $value));
            }
        }

        // Sprawdź cache konfiguracji
        $this->newLine();
        $this->info('Sprawdzanie cache konfiguracji...');

        $configCachePath = base_path('bootstrap/cache/config.php');
        if (file_exists($configCachePath)) {
            $this->warn('  ⚠ Cache konfiguracji istnieje (bootstrap/cache/config.php)');
            $this->line('     Jeśli zmieniłeś .env, uruchom: php artisan config:clear');

            // Sprawdź czy wartości w cache różnią się od .env
            $cachedHost = Config::get('database.connections.sqlsrv.host');
            $envHost = env('DB_ENOVA_HOST');

            if ($cachedHost !== $envHost && !empty($envHost)) {
                $warnings[] = [
                    'variable' => 'CONFIG_CACHE',
                    'description' => 'Cache konfiguracji może zawierać stare wartości',
                ];
                $this->warn("     ⚠ DB_ENOVA_HOST w cache: {$cachedHost}");
                $this->warn("     ⚠ DB_ENOVA_HOST w .env: {$envHost}");

                if ($this->option('fix')) {
                    $this->call('config:clear');
                    $this->info('     ✓ Cache konfiguracji wyczyszczony');
                    $fixed = true;
                }
            }
        } else {
            $this->line('  ✓ Brak cache konfiguracji');
        }

        // Podsumowanie
        $this->newLine();
        if (empty($errors) && empty($warnings)) {
            $this->info('✓ Konfiguracja jest poprawna!');
            return Command::SUCCESS;
        }

        if (!empty($errors)) {
            $this->error('✗ Znaleziono błędy w konfiguracji:');
            foreach ($errors as $error) {
                $this->line("  - {$error['variable']}: {$error['description']} - {$error['status']}");
            }
        }

        if (!empty($warnings)) {
            $this->warn('⚠ Ostrzeżenia:');
            foreach ($warnings as $warning) {
                $this->line("  - {$warning['variable']}: {$warning['description']}");
            }
        }

        $this->newLine();
        if (!empty($errors)) {
            $this->error('Aby naprawić błędy:');
            $this->line('  1. Sprawdź plik .env');
            $this->line('  2. Upewnij się, że wszystkie wymagane zmienne są ustawione');
            $this->line('  3. Jeśli zmieniłeś .env, uruchom: php artisan config:clear');
            return Command::FAILURE;
        }

        if ($fixed) {
            $this->info('✓ Problemy zostały naprawione automatycznie');
            return Command::SUCCESS;
        }

        $this->info('Uruchom z flagą --fix aby automatycznie wyczyścić cache konfiguracji');
        return Command::SUCCESS;
    }

    /**
     * Maskuje wrażliwe dane w wyświetlaniu
     */
    private function maskSensitive(string $var, string $value): string
    {
        if (str_contains($var, 'PASSWORD')) {
            return str_repeat('*', min(strlen($value), 10));
        }
        if (str_contains($var, 'HOST') || str_contains($var, 'EMAIL')) {
            return $value; // Host i email można pokazać
        }
        return $value;
    }
}

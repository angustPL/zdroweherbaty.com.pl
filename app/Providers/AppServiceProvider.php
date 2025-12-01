<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryException;
use App\Services\CartService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Obsługa błędów połączenia przez DB::listen - przechwytujemy wszystkie zapytania
        DB::listen(function ($query) {
            if ($query->connectionName === 'sqlsrv') {
                // Sprawdź cache i upewnij się, że używamy działającego hosta
                $this->ensureEnovaConnection();
            }
        });
    }

    /**
     * Upewnia się, że używamy działającego hosta Enova (z cache)
     */
    private function ensureEnovaConnection(): void
    {
        $connectionName = 'sqlsrv';
        $primaryHost = env('DB_ENOVA_HOST');
        $backupHost = env('DB_ENOVA_HOST_BACKUP');

        if (!$primaryHost || !$backupHost) {
            return;
        }

        $cachedHost = Cache::get('enova_working_host');
        if ($cachedHost && in_array($cachedHost, [$primaryHost, $backupHost])) {
            $currentHost = config("database.connections.{$connectionName}.host");
            if ($currentHost !== $cachedHost) {
                config(["database.connections.{$connectionName}.host" => $cachedHost]);
                DB::purge($connectionName);
            }
        }
    }

    /**
     * Obsługuje błąd połączenia - przełącza na backup host
     */
    private function handleEnovaConnectionError(QueryException $exception): void
    {
        $connectionName = 'sqlsrv';
        $primaryHost = env('DB_ENOVA_HOST');
        $backupHost = env('DB_ENOVA_HOST_BACKUP');

        if (!$primaryHost || !$backupHost) {
            return;
        }

        // Sprawdź czy to błąd połączenia
        $message = $exception->getMessage();
        $isConnectionError = str_contains($message, 'Unable to connect') ||
            str_contains($message, 'TCP Provider') ||
            str_contains($message, 'Timeout') ||
            str_contains($message, 'Server is unavailable') ||
            str_contains($message, 'does not exist');

        if (!$isConnectionError) {
            return; // To nie jest błąd połączenia
        }

        $currentHost = config("database.connections.{$connectionName}.host");

        // Jeśli już używamy backup, nie rób nic
        if ($currentHost === $backupHost) {
            return;
        }

        // Jeśli używamy primary i wystąpił błąd, przełącz na backup
        if ($currentHost === $primaryHost) {
            Log::warning('Błąd połączenia z Primary Enova host, przełączam na Backup', [
                'primary_host' => $primaryHost,
                'backup_host' => $backupHost,
                'error' => $message,
            ]);

            config(["database.connections.{$connectionName}.host" => $backupHost]);
            DB::purge($connectionName);
            Cache::put('enova_working_host', $backupHost, 300); // 5 minut
        }
    }
}

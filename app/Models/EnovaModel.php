<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * @extends \Illuminate\Database\Eloquent\Model
 */
abstract class EnovaModel extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Cache key dla działającego hosta
     */
    private const WORKING_HOST_CACHE_KEY = 'enova_working_host';
    private const WORKING_HOST_CACHE_TTL = 300; // 5 minut

    /**
     * Cache w pamięci dla działającego hosta w ramach jednego requesta
     * (optymalizacja - unikamy wielokrotnych zapytań do cache)
     */
    private static ?string $cachedWorkingHostInMemory = null;

    /**
     * Override newQuery aby obsłużyć failover przy każdym zapytaniu
     * 
     * UWAGA: Ta metoda jest wywoływana TYLKO gdy faktycznie wykonujemy zapytanie do bazy danych.
     * Jeśli używamy tylko cache (w getCachedWithBackup()), callback nie jest wykonywany,
     * więc newQuery() nie jest wywoływane, więc ensureWorkingConnection() nie jest wywoływane.
     */
    public function newQuery()
    {
        // Sprawdź i upewnij się, że używamy działającego hosta
        // To jest wywoływane TYLKO gdy faktycznie wykonujemy zapytanie do Enova
        static::ensureWorkingConnection();

        // Połączenie jest już ustawione przez właściwość $connection
        return parent::newQuery();
    }

    /**
     * Upewnia się, że używamy działającego hosta do połączenia z Enova
     *
     * Logika zgodnie z wymaganiami:
     * 1. Próbuje połączyć się z DB_ENOVA_HOST (primary)
     * 2. Jeśli nawiązanie połączenia z DB_ENOVA_HOST nie jest możliwe:
     *    - Sprawdza czy DB_ENOVA_HOST_BACKUP jest ustawione w env/config
     *    - Jeśli TAK → łączy się z hostem rezerwowym (backup)
     *    - Jeśli NIE → rzuca wyjątek
     *
     * Używa cache działającego hosta aby uniknąć wielokrotnych prób połączenia.
     */
    protected static function ensureWorkingConnection()
    {
        $connectionName = 'sqlsrv';
        $primaryHost = env('DB_ENOVA_HOST');
        $backupHost = env('DB_ENOVA_HOST_BACKUP');

        // Jeśli nie ma primary hosta, nie próbuj
        if (!$primaryHost) {
            throw new \RuntimeException('DB_ENOVA_HOST nie jest zdefiniowany - używaj cache');
        }

        // Sprawdź cache w pamięci (optymalizacja - unikamy zapytań do cache przy każdym wywołaniu)
        if (self::$cachedWorkingHostInMemory !== null) {
            $cachedHost = self::$cachedWorkingHostInMemory;
            $allowedHosts = $backupHost ? [$primaryHost, $backupHost] : [$primaryHost];
            if (in_array($cachedHost, $allowedHosts)) {
                $currentHost = config("database.connections.{$connectionName}.host");
                if ($currentHost !== $cachedHost) {
                    // Przełącz na zapisany działający host
                    config(["database.connections.{$connectionName}.host" => $cachedHost]);
                    DB::purge($connectionName);
                }
                return;
            }
        }

        // Sprawdź cache - czy mamy zapisany działający host (optymalizacja)
        $cachedHost = Cache::get(self::WORKING_HOST_CACHE_KEY);
        $allowedHosts = $backupHost ? [$primaryHost, $backupHost] : [$primaryHost];

        if ($cachedHost && in_array($cachedHost, $allowedHosts)) {
            // Zapisz w cache w pamięci dla tego requesta
            self::$cachedWorkingHostInMemory = $cachedHost;
            
            $currentHost = config("database.connections.{$connectionName}.host");
            if ($currentHost !== $cachedHost) {
                // Przełącz na zapisany działający host
                config(["database.connections.{$connectionName}.host" => $cachedHost]);
                DB::purge($connectionName);
            }
            return;
        }

        // KROK 1: Próbuj połączyć się z DB_ENOVA_HOST (primary)
        config(["database.connections.{$connectionName}.host" => $primaryHost]);
        DB::purge($connectionName);

        try {
            DB::connection($connectionName)->getPdo();
            // Primary działa - zapisz w cache i w pamięci
            Cache::put(self::WORKING_HOST_CACHE_KEY, $primaryHost, self::WORKING_HOST_CACHE_TTL);
            self::$cachedWorkingHostInMemory = $primaryHost;
            return;
        } catch (\Exception $e) {
            // KROK 2: Jeśli nawiązanie połączenia z DB_ENOVA_HOST nie jest możliwe
            // Sprawdź czy DB_ENOVA_HOST_BACKUP jest ustawione w env/config
            if ($backupHost) {
                Log::warning('Primary Enova host nie odpowiada, przełączam na backup', [
                    'primary_host' => $primaryHost,
                    'backup_host' => $backupHost,
                    'error' => $e->getMessage(),
                ]);

                // Łącz się z hostem rezerwowym (backup)
                config(["database.connections.{$connectionName}.host" => $backupHost]);
                DB::purge($connectionName);

                try {
                    DB::connection($connectionName)->getPdo();
                    // Backup działa - zapisz w cache i w pamięci
                    Cache::put(self::WORKING_HOST_CACHE_KEY, $backupHost, self::WORKING_HOST_CACHE_TTL);
                    self::$cachedWorkingHostInMemory = $backupHost;
                    Log::info('Używam backup Enova host', ['backup_host' => $backupHost]);
                    return;
                } catch (\Exception $e2) {
                    // Oba hosty nie działają - wyczyść cache i cache w pamięci
                    Cache::forget(self::WORKING_HOST_CACHE_KEY);
                    self::$cachedWorkingHostInMemory = null;
                    Log::error('Oba Enova hosty są niedostępne', [
                        'primary_host' => $primaryHost,
                        'backup_host' => $backupHost,
                        'primary_error' => $e->getMessage(),
                        'backup_error' => $e2->getMessage(),
                    ]);
                    throw $e2;
                }
            } else {
                // Nie ma backup hosta - wyczyść cache i cache w pamięci, rzuć wyjątek
                Cache::forget(self::WORKING_HOST_CACHE_KEY);
                self::$cachedWorkingHostInMemory = null;
                Log::error('Primary Enova host nie działa i nie ma backup hosta', [
                    'primary_host' => $primaryHost,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Override save method to prevent saving
     */
    public function save(array $options = [])
    {
        throw new \Exception(Lang::get('models.read_only.save'));
    }

    /**
     * Override update method to prevent updating
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception(Lang::get('models.read_only.update'));
    }

    /**
     * Override delete method to prevent deleting
     */
    public function delete()
    {
        throw new \Exception(Lang::get('models.read_only.delete'));
    }

    /**
     * Override create method to prevent creating
     */
    public static function create(array $attributes = [])
    {
        throw new \Exception(Lang::get('models.read_only.create'));
    }

    /**
     * Override forceFill method to prevent mass assignment
     */
    public function forceFill(array $attributes)
    {
        throw new \Exception(Lang::get('models.read_only.force_fill'));
    }

    /**
     * Uniwersalna metoda cache'owania z fallback do cache gdy Enova nie działa
     *
     * Logika zgodnie z wymaganiami:
     * 1. NAJPIERW: Sprawdź wyniki Enova w cache → jeśli jest, użyj (bez połączenia z bazą)
     * 2. Jeśli brak w cache → dopiero wtedy pytamy zdalnej bazy SQL (przez callback)
     *    - Próba połączenia z DB_ENOVA_HOST (primary)
     *    - Jeśli primary nie działa i DB_ENOVA_HOST_BACKUP jest ustawione → próba z backup hostem
     * 3. Jeśli połączenie z Enova nie jest możliwe (oba hosty nie działają):
     *    - Użyj cache nawet jeśli wygasł (przedłużamy TTL)
     *    - Jeśli nie ma cache w ogóle → zwróć wartość domyślną
     *
     * @param string $cacheKey Klucz cache (wyniki z Enova, TTL 48h)
     * @param callable $callback Funkcja do wykonania (pobranie danych z Enova przez SQL)
     * @param mixed $defaultValue Wartość domyślna gdy nie ma cache (np. [], null)
     * @param int|null $ttl Czas życia cache w sekundach (domyślnie 48h = 172800)
     * @param string|null $logContext Kontekst dla logów (np. 'product_id', 'group_path')
     * @return mixed
     */
    protected static function getCachedWithBackup(
        string $cacheKey,
        callable $callback,
        mixed $defaultValue = [],
        ?int $ttl = null,
        ?string $logContext = null
    ): mixed {
        // TTL domyślnie 48 godzin (172800 sekund)
        $cacheTtl = $ttl ?? (48 * 3600);

        // KROK 1: NAJPIERW sprawdź wyniki Enova w cache
        // Jeśli są w cache, używamy ich bez połączenia z bazą SQL
        // Używamy Cache::get() zamiast Cache::has() + Cache::get() aby uniknąć podwójnych zapytań
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // KROK 2: Jeśli brak w cache → dopiero wtedy pytamy zdalnej bazy SQL
        // ensureWorkingConnection() w newQuery() obsługuje failover (primary → backup jeśli zdefiniowany)
        try {
            return Cache::remember($cacheKey, $cacheTtl, $callback);
        } catch (\Exception $e) {
            // KROK 3: Jeśli połączenie z Enova nie jest możliwe (oba hosty nie działają)
            // Użyj cache nawet jeśli wygasł (przedłużamy TTL)
            $logData = ['error' => $e->getMessage()];
            if ($logContext) {
                $logData['context'] = $logContext;
            }
            Log::warning('Enova nie odpowiada, używam cache (nawet jeśli wygasł)', $logData);

            // Sprawdź czy cache istnieje (nawet jeśli wygasł, Laravel może go jeszcze mieć)
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                // Przedłuż TTL cache (zapisz ponownie)
                Cache::put($cacheKey, $cached, $cacheTtl);
                return $cached;
            }

            // Jeśli nie ma cache w ogóle, zwróć wartość domyślną
            Log::error('Brak cache', $logData);
            return $defaultValue;
        }
    }
}

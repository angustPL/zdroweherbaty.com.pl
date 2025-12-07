<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CacheController extends Controller
{
    /**
     * Wyświetla listę dostępnych typów cache do czyszczenia.
     */
    public function index()
    {
        return response()->json([
            'available_types' => [
                'groups' => 'Grupy produktów (drzewko)',
                'products_group' => 'Produkty w grupie',
                'product' => 'Pojedynczy produkt',
                'all' => 'Wszystkie cache\'y Enova',
            ],
            'usage' => [
                'check_status' => 'GET /cache/status/{type}?param={param}',
                'clear_specific' => 'POST /cache/clear/{type}',
                'clear_with_param' => 'POST /cache/clear/{type}/{param}',
                'clear_all' => 'POST /cache/clear/all',
            ],
        ]);
    }

    /**
     * Sprawdza status cache (czy istnieje i kiedy wygasa).
     *
     * @param Request $request
     * @param string $type Typ cache: 'groups', 'products_group', 'product'
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request, string $type)
    {
        try {
            $param = $request->query('param');
            $cacheKey = $this->getCacheKey($type, $param);

            if (!$cacheKey) {
                return response()->json([
                    'error' => 'Nieznany typ cache lub brak wymaganego parametru',
                    'type' => $type,
                    'param' => $param,
                ], 400);
            }

            $status = $this->getCacheStatus($cacheKey);

            return response()->json([
                'success' => true,
                'type' => $type,
                'param' => $param,
                'cache_key' => $cacheKey,
                'exists' => $status['exists'],
                'expires_at' => $status['expires_at'],
                'expires_at_formatted' => $status['expires_at_formatted'],
                'time_remaining' => $status['time_remaining'],
                'time_remaining_formatted' => $status['time_remaining_formatted'],
                'is_expired' => $status['is_expired'],
                'configured_ttl' => config('enova.cache.ttl', 86400),
                'configured_ttl_formatted' => $this->formatSeconds(config('enova.cache.ttl', 86400)),
            ]);
        } catch (\Exception $e) {
            Log::error('Cache status error', [
                'type' => $type,
                'param' => $param ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Błąd podczas sprawdzania statusu cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Czyści określony typ cache.
     *
     * @param Request $request
     * @param string $type Typ cache do wyczyszczenia: 'groups', 'products_group', 'product', 'all'
     * @param string|null $param Opcjonalny parametr (np. ID produktu lub ścieżka grupy)
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request, string $type, ?string $param = null)
    {
        try {
            switch ($type) {
                case 'groups':
                    $this->clearGroupsCache();
                    break;

                case 'products_group':
                    if (!$param) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Brak parametru: wymagana ścieżka grupy',
                        ], 400);
                    }
                    $result = $this->clearProductsGroupCache($param);
                    return response()->json($result, $result['success'] ? 200 : 503);
                    break;

                case 'product':
                    if (!$param) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Brak parametru: wymagane ID produktu',
                        ], 400);
                    }
                    $result = $this->clearProductCache((int) $param);
                    return response()->json($result, $result['success'] ? 200 : 503);
                    break;

                case 'all':
                    $this->clearAllEnovaCache();
                    break;

                default:
                    return response()->json([
                        'error' => 'Nieznany typ cache: ' . $type,
                        'available_types' => ['groups', 'products_group', 'product', 'all'],
                    ], 400);
            }

            Log::info('Cache cleared', [
                'type' => $type,
                'param' => $param,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cache typu '{$type}' został wyczyszczony" . ($param ? " (param: {$param})" : ''),
                'type' => $type,
                'param' => $param,
            ]);
        } catch (\Exception $e) {
            Log::error('Cache clear error', [
                'type' => $type,
                'param' => $param,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Błąd podczas czyszczenia cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Czyści cache grup produktów.
     */
    private function clearGroupsCache(): void
    {
        Cache::forget('enova_groups_hierarchy');
    }

    /**
     * Czyści cache produktów w określonej grupie i od razu odświeża z Enova.
     * Automatycznie czyści również cache strony głównej (HP), ponieważ produkty mogą być wyświetlane na HP.
     *
     * @param string $groupPath Ścieżka grupy
     * @return array ['success' => bool, 'message' => string, 'error' => string|null]
     */
    private function clearProductsGroupCache(string $groupPath): array
    {
        $cacheKey = 'enova_products_group_' . md5($groupPath);
        Cache::forget($cacheKey);

        // Automatycznie wyczyść cache strony głównej (HP)
        $this->clearHomePageCache();

        // Próbuj od razu odświeżyć cache z Enova
        try {
            \App\Models\Product::getCachedByGroup($groupPath);
            return [
                'success' => true,
                'message' => 'Cache grupy został odświeżony',
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Nie można odświeżyć cache grupy z Enova', [
                'group_path' => $groupPath,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Cache został usunięty, ale nie można było odświeżyć z Enova',
                'error' => 'Brak połączenia z Enova. Cache nie może być odświeżony w tym momencie.'
            ];
        }
    }

    /**
     * Czyści cache pojedynczego produktu i od razu odświeża z Enova.
     * Automatycznie czyści również cache strony głównej (HP), ponieważ produkty są wyświetlane na HP.
     *
     * @param int $productId ID produktu
     * @return array ['success' => bool, 'message' => string, 'error' => string|null]
     */
    private function clearProductCache(int $productId): array
    {
        $cacheKey = 'enova_product_' . $productId;
        Cache::forget($cacheKey);

        // Automatycznie wyczyść cache strony głównej (HP)
        $this->clearHomePageCache();

        // Próbuj od razu odświeżyć cache z Enova
        try {
            \App\Models\Product::getCachedById($productId);
            return [
                'success' => true,
                'message' => 'Cache produktu został odświeżony',
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Nie można odświeżyć cache produktu z Enova', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Cache został usunięty, ale nie można było odświeżyć z Enova',
                'error' => 'Brak połączenia z Enova. Cache nie może być odświeżony w tym momencie.'
            ];
        }
    }

    /**
     * Czyści wszystkie cache'y związane z Enova.
     */
    private function clearAllEnovaCache(): void
    {
        // Pobierz wszystkie klucze cache z prefiksem 'enova_'
        $prefix = config('cache.prefix', '') . 'enova_';

        // Dla drivera 'database' musimy ręcznie przeszukać tabelę cache
        if (config('cache.default') === 'database') {
            $this->clearEnovaCacheFromDatabase($prefix);
        } else {
            // Dla innych driverów (Redis, Memcached) możemy użyć tagów lub pattern matching
            // Na razie wyczyśćmy najważniejsze klucze ręcznie
            $this->clearGroupsCache();
            $this->clearHomePageCache();
            // Uwaga: nie możemy łatwo wyczyścić wszystkich kluczy z patternem bez iteracji
            // W produkcji warto użyć Redis z tagami lub pattern matching
        }
    }

    /**
     * Czyści cache strony głównej (HP).
     * Cache HP zawiera wszystkie produkty wyświetlane na stronie głównej.
     */
    private function clearHomePageCache(): void
    {
        Cache::forget('enova_products_all');
    }

    /**
     * Czyści cache Enova z bazy danych.
     *
     * @param string $prefix Prefiks kluczy cache
     */
    private function clearEnovaCacheFromDatabase(string $prefix): void
    {
        $connection = config('cache.stores.database.connection');
        $table = config('cache.stores.database.table', 'cache');

        DB::connection($connection)
            ->table($table)
            ->where('key', 'like', $prefix . '%')
            ->delete();
    }

    /**
     * Pobiera klucz cache dla danego typu.
     *
     * @param string $type
     * @param string|null $param
     * @return string|null
     */
    private function getCacheKey(string $type, ?string $param = null): ?string
    {
        switch ($type) {
            case 'groups':
                return $this->getFullCacheKey('enova_groups_hierarchy');

            case 'products_group':
                if (!$param) {
                    return null;
                }
                return $this->getFullCacheKey('enova_products_group_' . md5($param));

            case 'product':
                if (!$param) {
                    return null;
                }
                return $this->getFullCacheKey('enova_product_' . (int) $param);

            default:
                return null;
        }
    }

    /**
     * Pobiera pełny klucz cache z prefiksem.
     *
     * @param string $key
     * @return string
     */
    private function getFullCacheKey(string $key): string
    {
        $prefix = config('cache.prefix', '');
        return $prefix . $key;
    }

    /**
     * Sprawdza status cache (istnienie i ważność).
     *
     * @param string $cacheKey
     * @return array
     */
    private function getCacheStatus(string $cacheKey): array
    {
        $exists = Cache::has($cacheKey);

        if (!$exists) {
            return [
                'exists' => false,
                'expires_at' => null,
                'expires_at_formatted' => null,
                'time_remaining' => null,
                'time_remaining_formatted' => null,
                'is_expired' => true,
            ];
        }

        // Dla drivera database możemy sprawdzić expiration bezpośrednio
        if (config('cache.default') === 'database') {
            return $this->getCacheStatusFromDatabase($cacheKey);
        }

        // Dla innych driverów używamy Cache::get() i sprawdzamy czy istnieje
        // Nie możemy łatwo sprawdzić expiration dla Redis/Memcached bez dodatkowych operacji
        // Więc zwracamy podstawowe informacje
        return [
            'exists' => true,
            'expires_at' => null,
            'expires_at_formatted' => 'Nie można określić (driver: ' . config('cache.default') . ')',
            'time_remaining' => null,
            'time_remaining_formatted' => 'Nie można określić',
            'is_expired' => false,
        ];
    }

    /**
     * Sprawdza status cache z bazy danych.
     *
     * @param string $cacheKey
     * @return array
     */
    private function getCacheStatusFromDatabase(string $cacheKey): array
    {
        $connection = config('cache.stores.database.connection');
        $table = config('cache.stores.database.table', 'cache');

        $cacheEntry = DB::connection($connection)
            ->table($table)
            ->where('key', $cacheKey)
            ->first();

        if (!$cacheEntry) {
            return [
                'exists' => false,
                'expires_at' => null,
                'expires_at_formatted' => null,
                'time_remaining' => null,
                'time_remaining_formatted' => null,
                'is_expired' => true,
            ];
        }

        $expiration = $cacheEntry->expiration;
        $now = time();
        $isExpired = $expiration < $now;
        $timeRemaining = $isExpired ? 0 : ($expiration - $now);

        return [
            'exists' => true,
            'expires_at' => $expiration,
            'expires_at_formatted' => date('Y-m-d H:i:s', $expiration),
            'time_remaining' => $timeRemaining,
            'time_remaining_formatted' => $this->formatSeconds($timeRemaining),
            'is_expired' => $isExpired,
        ];
    }

    /**
     * Formatuje sekundy na czytelny format.
     *
     * @param int $seconds
     * @return string
     */
    private function formatSeconds(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'Wygasł';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'godzina' : ($hours < 5 ? 'godziny' : 'godzin'));
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . ($minutes === 1 ? 'minuta' : ($minutes < 5 ? 'minuty' : 'minut'));
        }
        if ($secs > 0 && $hours === 0) {
            $parts[] = $secs . ' ' . ($secs === 1 ? 'sekunda' : ($secs < 5 ? 'sekundy' : 'sekund'));
        }

        return implode(' ', $parts) ?: '0 sekund';
    }
}

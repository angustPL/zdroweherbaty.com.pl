<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Group;
use App\Mail\CacheGenerationReportMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateEnovaBackupCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enova:generate-backup-cache
                            {--force : Wymuś regenerację cache nawet jeśli już istnieje}
                            {--check : Sprawdź status backup cache bez generowania}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generuje cache backup dla danych Enova (produkty, grupy) na wypadek awarii serwera';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Jeśli używamy opcji --check, tylko sprawdź status
        if ($this->option('check')) {
            return $this->checkCacheStatus();
        }

        $this->info('Rozpoczynam generowanie cache dla Enova (TTL: 48h)...');
        $force = $this->option('force');

        $startTime = microtime(true);
        $stats = [
            'products' => 0,
            'groups' => 0,
            'products_by_group' => 0,
            'individual_products' => 0,
            'deliveries' => 0,
        ];

        try {
            // 1. Cache wszystkich produktów + w jednym przelocie: cache pojedynczych produktów, grup i produktów w grupach
            $this->info('Generowanie cache produktów i powiązanych danych...');
            $productsResult = $this->generateProductsAndRelatedCache($force);
            $stats['products'] = $productsResult['products'];
            $stats['individual_products'] = $productsResult['individual_products'];
            $stats['products_by_group'] = $productsResult['products_by_group'];
            $stats['groups'] = $productsResult['groups'];

            // 2. Cache hierarchii grup (jeśli nie został wygenerowany w kroku 1)
            if ($stats['groups'] == 0) {
                $this->info('Generowanie cache hierarchii grup...');
                $stats['groups'] = $this->generateGroupsCache($force);
            }

            // 3. Cache produktów dla wszystkich grup z hierarchii (uzupełnienie cache z kroku 1)
            $this->info('Generowanie cache produktów dla wszystkich grup z hierarchii...');
            $additionalGroupsCache = $this->generateProductsByGroupCache($force);
            if ($additionalGroupsCache > 0) {
                $stats['products_by_group'] += $additionalGroupsCache;
            }

            // 4. Cache opcji dostawy
            $this->info('Generowanie cache opcji dostawy...');
            $stats['deliveries'] = $this->generateDeliveriesCache($force);

            $duration = round(microtime(true) - $startTime, 2);

            // Podsumowanie
            $this->newLine();
            $this->info('=== Podsumowanie ===');
            $this->line("✓ Produkty: {$stats['products']}");
            $this->line("✓ Pojedyncze produkty: {$stats['individual_products']}");
            $this->line("✓ Produkty w grupach: {$stats['products_by_group']} grup");
            $this->line("✓ Grupy: " . ($stats['groups'] > 0 ? $stats['groups'] : 'Tak (z hierarchii)'));
            $this->line("✓ Opcje dostawy: {$stats['deliveries']}");
            $this->line("⏱ Czas wykonania: {$duration}s");
            $this->newLine();
            $this->info('✓ Cache został wygenerowany pomyślnie!');

            Log::info('Enova cache został wygenerowany pomyślnie', $stats);

            // Wyślij email do admina z raportem
            $this->sendReportEmail($stats, $duration, true);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $this->error('Błąd podczas generowania cache: ' . $e->getMessage());
            Log::error('Błąd podczas generowania Enova cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Wyślij email do admina z informacją o błędzie
            $this->sendReportEmail($stats ?? [], $duration, false, $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Sprawdza status cache
     */
    private function checkCacheStatus(): int
    {
        $this->info('Sprawdzanie statusu cache...');
        $this->newLine();

        $cacheKeys = [
            'enova_products_all' => 'Wszystkie produkty',
            'enova_groups_hierarchy_with_products' => 'Hierarchia grup',
            'enova_deliveries_all' => 'Opcje dostawy',
        ];

        $allExists = true;
        foreach ($cacheKeys as $key => $name) {
            $exists = Cache::has($key);
            $status = $exists ? '✓' : '✗';
            $color = $exists ? 'info' : 'error';

            $this->$color("{$status} {$name}: " . ($exists ? 'ISTNIEJE' : 'BRAK'));

            if ($exists) {
                $data = Cache::get($key);
                if (is_array($data)) {
                    $count = count($data);
                    $this->line("  └─ Liczba elementów: {$count}");
                }
            }

            if (!$exists) {
                $allExists = false;
            }
        }

        // Sprawdź cache produktów w grupach
        $this->newLine();
        $this->info('Sprawdzanie cache produktów w grupach...');
        try {
            $groups = Group::getHierarchicalStructure();
            $groupsWithCache = 0;
            $totalGroups = 0;

            $checkGroup = function ($groupHierarchy, $prefix = '') use (&$checkGroup, &$groupsWithCache, &$totalGroups) {
                foreach ($groupHierarchy as $groupName => $groupData) {
                    $fullPath = $prefix ? $prefix . '\\' . $groupName : $groupName;
                    $dbPath = config('enova.features.product_group_prefix') . $fullPath . '\\';
                    $cacheKey = 'enova_products_group_' . md5($dbPath);
                    
                    $totalGroups++;
                    if (Cache::has($cacheKey)) {
                        $groupsWithCache++;
                    }

                    // Przetwórz dzieci
                    if (!empty($groupData['children'])) {
                        $checkGroup($groupData['children'], $fullPath);
                    }
                }
            };

            $checkGroup($groups);
            
            $status = $groupsWithCache > 0 ? '✓' : '✗';
            $color = $groupsWithCache > 0 ? 'info' : 'error';
            $this->$color("{$status} Produkty w grupach: {$groupsWithCache}/{$totalGroups} grup ma cache");
        } catch (\Exception $e) {
            $this->warn("  ⚠ Nie udało się sprawdzić cache produktów w grupach: " . $e->getMessage());
        }

        $this->newLine();
        if ($allExists) {
            $this->info('✓ Cache jest kompletny');
        } else {
            $this->warn('⚠ Cache jest niekompletny - uruchom: php artisan enova:generate-backup-cache');
        }

        return $allExists ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Generuje cache wszystkich produktów oraz w jednym przelocie:
     * - cache pojedynczych produktów
     * - cache grup
     * - cache produktów w grupach
     *
     * @return array Statystyki: ['products' => int, 'individual_products' => int, 'products_by_group' => int, 'groups' => int]
     */
    private function generateProductsAndRelatedCache(bool $force): array
    {
        $cacheKey = 'enova_products_all';
        $cacheTtl = 48 * 3600; // 48 godzin w sekundach
        $stats = [
            'products' => 0,
            'individual_products' => 0,
            'products_by_group' => 0,
            'groups' => 0,
        ];

        if (!$force && Cache::has($cacheKey)) {
            $products = Cache::get($cacheKey);
            $count = is_array($products) ? count($products) : 0;
            $this->line("  Cache produktów już istnieje ({$count} produktów), pomijam...");
            $stats['products'] = $count;
            return $stats;
        }

        // Jeśli force=true, wyczyść cache żeby wymusić pobranie z Enova
        if ($force) {
            Cache::forget($cacheKey);
        }

        try {
            // Pobierz wszystkie produkty jako modele (z relacjami) - bezpośrednio z bazy, nie z cache
            $products = Product::with(['productNameFeature', 'price', 'group', 'productMark'])->get();
            $count = $products->count();

            // Konwertuj do tablicy i zapisz cache wszystkich produktów
            $productsArray = $products->map(function ($product) {
                return $product->toDisplayArray();
            })->toArray();
            Cache::put($cacheKey, $productsArray, $cacheTtl);
            $stats['products'] = $count;
            $this->line("  ✓ Zcache'owano {$count} produktów");

            // W jednym foreach: generuj cache dla pojedynczych produktów, grup i produktów w grupach
            $productsByGroup = []; // Grupowanie produktów po grupach
            $groupsCache = []; // Cache dla grup

            foreach ($products as $product) {
                $productId = $product->ID;
                $productArray = $product->toDisplayArray();

                // 1. Cache pojedynczego produktu
                $productCacheKey = 'enova_product_' . $productId;
                if ($force) {
                    Cache::forget($productCacheKey);
                }
                if ($force || !Cache::has($productCacheKey)) {
                    Cache::put($productCacheKey, $productArray, $cacheTtl);
                    $stats['individual_products']++;
                }

                // 2. Grupowanie produktów po grupach
                if ($product->group) {
                    $groupPath = $product->group->Data;
                    if (!isset($productsByGroup[$groupPath])) {
                        $productsByGroup[$groupPath] = [];
                    }
                    $productsByGroup[$groupPath][] = $productArray;

                    // 3. Cache dla grupy (tylko raz dla każdej grupy)
                    $groupCacheKey = 'enova_group_' . md5($groupPath);
                    if (!isset($groupsCache[$groupPath])) {
                        if ($force) {
                            Cache::forget($groupCacheKey);
                        }
                        if ($force || !Cache::has($groupCacheKey)) {
                            // Zapisz grupę jako tablicę atrybutów (model może nie być serializowalny)
                            $groupData = [
                                'ID' => $product->group->Parent ?? null,
                                'Data' => $product->group->Data ?? null,
                                'Name' => $product->group->Name ?? null,
                                'clean_name' => $product->group->clean_name ?? null,
                            ];
                            Cache::put($groupCacheKey, $groupData, $cacheTtl);
                            $stats['groups']++;
                        }
                        $groupsCache[$groupPath] = true;
                    }
                }
            }

            // 4. Cache produktów w grupach
            foreach ($productsByGroup as $groupPath => $groupProducts) {
                $groupCacheKey = 'enova_products_group_' . md5($groupPath);
                if ($force) {
                    Cache::forget($groupCacheKey);
                }
                if ($force || !Cache::has($groupCacheKey)) {
                    Cache::put($groupCacheKey, $groupProducts, $cacheTtl);
                    $stats['products_by_group']++;
                }
            }

            $this->line("  ✓ Zcache'owano {$stats['individual_products']} pojedynczych produktów");
            $this->line("  ✓ Zcache'owano {$stats['products_by_group']} grup z produktami");
            $this->line("  ✓ Zcache'owano {$stats['groups']} grup");

            return $stats;
        } catch (\Exception $e) {
            $this->warn("  ⚠ Nie udało się wygenerować cache produktów: " . $e->getMessage());
            // Jeśli nie udało się z Enova, sprawdź czy istnieje cache (nawet jeśli wygasł)
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                $count = is_array($cached) ? count($cached) : 0;
                // Przedłuż TTL cache
                Cache::put($cacheKey, $cached, $cacheTtl);
                $this->line("  ✓ Użyto istniejącego cache ({$count} produktów)");
                $stats['products'] = $count;
            }
            return $stats;
        }
    }

    /**
     * Generuje cache hierarchii grup
     *
     * @return int 1 jeśli sukces, 0 jeśli błąd
     */
    private function generateGroupsCache(bool $force): int
    {
        $cacheKey = 'enova_groups_hierarchy_with_products';
        $cacheTtl = 48 * 3600; // 48 godzin w sekundach

        if (!$force && Cache::has($cacheKey)) {
            $this->line('  Cache grup już istnieje, pomijam...');
            return 1;
        }

        // Jeśli force=true, wyczyść cache żeby wymusić pobranie z Enova
        if ($force) {
            Cache::forget($cacheKey);
        }

        try {
            // getHierarchicalStructure() automatycznie zapisze do cache po pobraniu z Enova
            $groups = Group::getHierarchicalStructure();
            $this->line("  ✓ Zcache'owano hierarchię grup");
            return 1;
        } catch (\Exception $e) {
            $this->warn("  ⚠ Nie udało się wygenerować cache grup: " . $e->getMessage());
            // Jeśli nie udało się z Enova, sprawdź czy istnieje cache (nawet jeśli wygasł)
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                // Przedłuż TTL cache
                Cache::put($cacheKey, $cached, $cacheTtl);
                $this->line("  ✓ Użyto istniejącego cache");
                return 1;
            }
            return 0;
        }
    }

    /**
     * Generuje cache produktów dla każdej grupy
     *
     * @return int Liczba zcache'owanych grup
     */
    private function generateProductsByGroupCache(bool $force): int
    {
        $cacheTtl = 48 * 3600; // 48 godzin w sekundach
        $processed = 0;
        $errors = 0;

        try {
            // Pobierz wszystkie grupy
            $groups = Group::getHierarchicalStructure();

            // Funkcja rekurencyjna do przetworzenia wszystkich grup
            $processGroup = function ($groupHierarchy, $prefix = '') use (&$processGroup, &$processed, &$errors, $cacheTtl, $force) {
                foreach ($groupHierarchy as $groupName => $groupData) {
                    $fullPath = $prefix ? $prefix . '\\' . $groupName : $groupName;
                    $dbPath = config('enova.features.product_group_prefix') . $fullPath . '\\';

                    $cacheKey = 'enova_products_group_' . md5($dbPath);

                    if (!$force && Cache::has($cacheKey)) {
                        continue;
                    }

                    // Jeśli force=true, wyczyść cache żeby wymusić pobranie z Enova
                    if ($force) {
                        Cache::forget($cacheKey);
                    }

                    try {
                        // getCachedByGroup() automatycznie zapisze do cache po pobraniu z Enova
                        $products = Product::getCachedByGroup($dbPath);
                        $processed++;
                    } catch (\Exception $e) {
                        $errors++;
                        // Spróbuj użyć istniejącego cache (nawet jeśli wygasł)
                        $cached = Cache::get($cacheKey);
                        if ($cached !== null) {
                            // Przedłuż TTL cache
                            Cache::put($cacheKey, $cached, $cacheTtl);
                            $processed++;
                        }
                    }

                    // Przetwórz dzieci
                    if (!empty($groupData['children'])) {
                        $processGroup($groupData['children'], $fullPath);
                    }
                }
            };

            $processGroup($groups);

            $this->line("  ✓ Zcache'owano produkty dla {$processed} grup");
            if ($errors > 0) {
                $this->warn("  ⚠ {$errors} grup nie udało się zcache'ować");
            }
            return $processed;
        } catch (\Exception $e) {
            $this->warn("  ⚠ Nie udało się pobrać listy grup: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generuje cache pojedynczych produktów (tylko aktywne)
     *
     * @return int Liczba zcache'owanych produktów
     */
    private function generateIndividualProductsCache(bool $force): int
    {
        $cacheTtl = 48 * 3600; // 48 godzin w sekundach
        $processed = 0;
        $errors = 0;

        try {
            // Pobierz wszystkie produkty
            $products = Cache::get('enova_products_all') ?? [];

            if (empty($products)) {
                $this->warn('  ⚠ Brak produktów do zcache\'owania');
                return 0;
            }

            foreach ($products as $product) {
                $productId = $product['ID'] ?? null;
                if (!$productId) {
                    continue;
                }

                $cacheKey = 'enova_product_' . $productId;

                if (!$force && Cache::has($cacheKey)) {
                    continue;
                }

                // Jeśli force=true, wyczyść cache żeby wymusić pobranie z Enova
                if ($force) {
                    Cache::forget($cacheKey);
                }

                try {
                    // getCachedById() automatycznie zapisze do cache po pobraniu z Enova
                    $productData = Product::getCachedById($productId);
                    if ($productData) {
                        $processed++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    // Spróbuj użyć istniejącego cache (nawet jeśli wygasł)
                    $cached = Cache::get($cacheKey);
                    if ($cached !== null) {
                        // Przedłuż TTL cache
                        Cache::put($cacheKey, $cached, $cacheTtl);
                        $processed++;
                    }
                }
            }

            $this->line("  ✓ Zcache'owano {$processed} pojedynczych produktów");
            if ($errors > 0) {
                $this->warn("  ⚠ {$errors} produktów nie udało się zcache'ować");
            }
            return $processed;
        } catch (\Exception $e) {
            $this->warn("  ⚠ Nie udało się wygenerować cache pojedynczych produktów: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generuje cache opcji dostawy
     *
     * @return int Liczba zcache'owanych opcji dostawy
     */
    private function generateDeliveriesCache(bool $force): int
    {
        $cacheKey = 'enova_deliveries_all';
        $cacheTtl = 48 * 3600; // 48 godzin w sekundach

        if (!$force && Cache::has($cacheKey)) {
            $deliveries = Cache::get($cacheKey);
            $count = is_array($deliveries) ? count($deliveries) : 0;
            $this->line("  Cache opcji dostawy już istnieje ({$count} opcji), pomijam...");
            return $count;
        }

        // Jeśli force=true, wyczyść cache żeby wymusić pobranie z Enova
        if ($force) {
            Cache::forget($cacheKey);
        }

        try {
            // getCachedAll() automatycznie zapisze do cache po pobraniu z Enova
            $deliveries = \App\Models\Delivery::getCachedAll();
            $count = count($deliveries);
            $this->line("  ✓ Zcache'owano {$count} opcji dostawy");
            return $count;
        } catch (\Exception $e) {
            $this->warn("  ⚠ Nie udało się wygenerować cache opcji dostawy: " . $e->getMessage());
            // Jeśli nie udało się z Enova, sprawdź czy istnieje cache (nawet jeśli wygasł)
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                $count = is_array($cached) ? count($cached) : 0;
                // Przedłuż TTL cache
                Cache::put($cacheKey, $cached, $cacheTtl);
                $this->line("  ✓ Użyto istniejącego cache ({$count} opcji)");
                return $count;
            }
            return 0;
        }
    }

    /**
     * Wysyła email z raportem do admina
     */
    private function sendReportEmail(array $stats, float $duration, bool $success, ?string $errorMessage = null): void
    {
        try {
            $adminEmail = config('enova.orders.email.address', 'sklep@bifix.pl');
            
            if (empty($adminEmail)) {
                $this->warn('  ⚠ Brak skonfigurowanego adresu email admina - pomijam wysyłkę raportu');
                return;
            }

            Mail::to($adminEmail)->send(
                new CacheGenerationReportMail($stats, $duration, $success, $errorMessage)
            );

            $this->line("  ✓ Raport wysłany na adres: {$adminEmail}");
            Log::info('Raport generowania cache wysłany do admina', [
                'email' => $adminEmail,
                'success' => $success,
            ]);
        } catch (\Exception $e) {
            $this->warn("  ⚠ Nie udało się wysłać raportu email: " . $e->getMessage());
            Log::error('Błąd wysyłki raportu generowania cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

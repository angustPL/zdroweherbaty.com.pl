<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExportEnovaCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enova:export-cache
                            {--file= : Nazwa pliku eksportu (domyślnie: cache_export.json)}
                            {--path= : Ścieżka do pliku eksportu (domyślnie: storage/app)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eksportuje cache Enova do pliku JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileName = $this->option('file') ?? 'cache_export.json';
        $exportPath = $this->option('path') ?? storage_path('app');
        $filePath = rtrim($exportPath, '/') . '/' . $fileName;

        $this->info('Rozpoczynam eksport cache Enova...');
        $this->newLine();

        $exportedData = [];
        $stats = [
            'keys_found' => 0,
            'keys_exported' => 0,
            'keys_skipped' => 0,
        ];

        // Lista głównych kluczy cache
        $mainKeys = [
            'enova_products_all',
            'enova_groups_hierarchy_with_products',
            'enova_deliveries_all',
        ];

        // Eksportuj główne klucze
        foreach ($mainKeys as $key) {
            $value = Cache::get($key);
            if ($value !== null) {
                $exportedData[$key] = [
                    'value' => $value,
                    'ttl' => 48 * 3600, // 48 godzin
                ];
                $stats['keys_exported']++;
                $this->line("  ✓ Eksportowano: {$key}");
            } else {
                $stats['keys_skipped']++;
                $this->line("  ✗ Brak: {$key}");
            }
            $stats['keys_found']++;
        }

        // Eksportuj pojedyncze produkty i grupy (jeśli dostępne przez Redis)
        if (config('cache.default') === 'redis') {
            $this->info('Eksportowanie pojedynczych produktów i grup...');
            $productKeys = $this->getCacheKeysByPattern('enova_product_*');
            $groupKeys = $this->getCacheKeysByPattern('enova_products_group_*');
            
            $allKeys = array_merge($productKeys, $groupKeys);
            
            foreach ($allKeys as $key) {
                $value = Cache::get($key);
                if ($value !== null) {
                    $exportedData[$key] = [
                        'value' => $value,
                        'ttl' => 48 * 3600,
                    ];
                    $stats['keys_exported']++;
                }
            }
            
            $this->line("  ✓ Eksportowano " . count($allKeys) . " dodatkowych kluczy");
        } else {
            $this->warn("  ⚠ Eksportowanie pojedynczych produktów/grup wymaga Redis");
            $this->warn("  ⚠ Eksportowane są tylko główne klucze cache");
        }

        // Zapisz do pliku
        $jsonData = json_encode($exportedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $jsonData);

        $fileSize = round(filesize($filePath) / 1024 / 1024, 2);

        $this->newLine();
        $this->info('=== Podsumowanie eksportu ===');
        $this->line("✓ Klucze znalezione: {$stats['keys_found']}");
        $this->line("✓ Klucze wyeksportowane: {$stats['keys_exported']}");
        $this->line("✗ Klucze pominięte: {$stats['keys_skipped']}");
        $this->line("📁 Plik: {$filePath}");
        $this->line("📦 Rozmiar: {$fileSize} MB");
        $this->newLine();
        $this->info('✓ Eksport zakończony pomyślnie!');

        return Command::SUCCESS;
    }

    /**
     * Pobiera klucze cache pasujące do wzorca.
     */
    private function getCacheKeysByPattern(string $pattern): array
    {
        $keys = [];
        $prefix = config('cache.prefix', '');

        // Dla Redis - użyj bezpośredniego połączenia Redis
        if (config('cache.default') === 'redis') {
            try {
                $redisConfig = config('database.redis.cache');
                $redis = new \Redis();
                
                // Połącz się z Redis (socket lub TCP)
                $host = $redisConfig['host'] ?? '127.0.0.1';
                $port = $redisConfig['port'] ?? 6379;
                
                if (strpos($host, '.sock') !== false || $port == 0) {
                    // Socket connection
                    $redis->connect($host);
                } else {
                    // TCP connection
                    $redis->connect($host, $port);
                }
                
                if (isset($redisConfig['password']) && $redisConfig['password']) {
                    $redis->auth($redisConfig['password']);
                }
                
                if (isset($redisConfig['database'])) {
                    $redis->select($redisConfig['database']);
                }
                
                $patternWithPrefix = $prefix . str_replace('*', '*', $pattern);
                
                // Użyj keys (prostsze, ale mniej wydajne dla dużych baz)
                $allKeys = $redis->keys($patternWithPrefix);
                
                foreach ($allKeys as $key) {
                    // Usuń prefix z klucza
                    $cleanKey = str_replace($prefix, '', $key);
                    $keys[] = $cleanKey;
                }
                
                $redis->close();
            } catch (\Exception $e) {
                $this->warn("  ⚠ Nie można przeskanować Redis: " . $e->getMessage());
            }
        } else {
            // Dla innych driverów - nie można przeskanować
            $this->warn("  ⚠ Skanowanie kluczy nie jest dostępne dla drivera: " . config('cache.default'));
        }

        return array_unique($keys);
    }
}

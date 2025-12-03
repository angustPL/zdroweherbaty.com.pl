<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ImportEnovaCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enova:import-cache
                            {file : Ścieżka do pliku JSON z cache}
                            {--force : Nadpisz istniejący cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importuje cache Enova z pliku JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $force = $this->option('force');

        // Sprawdź czy plik istnieje
        if (!file_exists($filePath)) {
            $this->error("✗ Plik nie istnieje: {$filePath}");
            return Command::FAILURE;
        }

        $this->info('Rozpoczynam import cache Enova...');
        $this->newLine();

        // Wczytaj dane z pliku
        $jsonData = file_get_contents($filePath);
        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('✗ Błąd parsowania JSON: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        $stats = [
            'total' => count($data),
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $this->info("Znaleziono {$stats['total']} kluczy do zaimportowania...");
        $this->newLine();

        // Importuj każdy klucz
        foreach ($data as $key => $item) {
            try {
                // Sprawdź czy klucz już istnieje (jeśli nie --force)
                if (!$force && Cache::has($key)) {
                    $stats['skipped']++;
                    $this->line("  ⊙ Pominięto (już istnieje): {$key}");
                    continue;
                }

                $value = $item['value'] ?? $item; // Obsługa starego formatu (bez 'value')
                $ttl = $item['ttl'] ?? (48 * 3600); // Domyślnie 48h

                Cache::put($key, $value, $ttl);
                $stats['imported']++;
                $this->line("  ✓ Zaimportowano: {$key}");
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("  ✗ Błąd importu {$key}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('=== Podsumowanie importu ===');
        $this->line("✓ Zaimportowano: {$stats['imported']}");
        $this->line("⊙ Pominięto: {$stats['skipped']}");
        if ($stats['errors'] > 0) {
            $this->line("✗ Błędy: {$stats['errors']}");
        }
        $this->newLine();
        $this->info('✓ Import zakończony pomyślnie!');

        return Command::SUCCESS;
    }
}

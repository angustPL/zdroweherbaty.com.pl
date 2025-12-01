<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class TestEnovaConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enova:test-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testuje połączenia z oboma hostami Enova (primary i backup)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $primaryHost = env('DB_ENOVA_HOST');
        $backupHost = env('DB_ENOVA_HOST_BACKUP');
        $connectionName = 'sqlsrv';

        $this->info('Testowanie połączeń z bazą Enova...');
        $this->newLine();

        if (!$primaryHost || !$backupHost) {
            $this->error('Brak konfiguracji hostów w .env');
            $this->line('Wymagane zmienne:');
            $this->line('  - DB_ENOVA_HOST');
            $this->line('  - DB_ENOVA_HOST_BACKUP');
            return Command::FAILURE;
        }

        // Test Primary Host
        $this->info("Testowanie Primary Host: {$primaryHost}");
        $primaryResult = $this->testConnection($connectionName, $primaryHost, 'Primary');
        $this->newLine();

        // Test Backup Host
        $this->info("Testowanie Backup Host: {$backupHost}");
        $backupResult = $this->testConnection($connectionName, $backupHost, 'Backup');
        $this->newLine();

        // Podsumowanie
        $this->info('=== Podsumowanie ===');
        if ($primaryResult['success']) {
            $this->info("✓ Primary Host ({$primaryHost}): DZIAŁA - {$primaryResult['time']}ms");
        } else {
            $this->error("✗ Primary Host ({$primaryHost}): BŁĄD - {$primaryResult['error']}");
        }

        if ($backupResult['success']) {
            $this->info("✓ Backup Host ({$backupHost}): DZIAŁA - {$backupResult['time']}ms");
        } else {
            $this->error("✗ Backup Host ({$backupHost}): BŁĄD - {$backupResult['error']}");
        }

        if (!$primaryResult['success'] && !$backupResult['success']) {
            $this->newLine();
            $this->error('UWAGA: Oba hosty są niedostępne!');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Testuje połączenie z hostem
     */
    private function testConnection(string $connectionName, string $host, string $label): array
    {
        $startTime = microtime(true);
        
        try {
            // Ustaw host i krótszy timeout dla testu
            Config::set("database.connections.{$connectionName}.host", $host);
            Config::set("database.connections.{$connectionName}.options", [
                \PDO::ATTR_TIMEOUT => 5, // 5 sekund timeout dla testu
            ]);
            
            // Wyczyść cache połączenia
            DB::purge($connectionName);
            
            // Test połączenia z timeoutem
            $pdo = DB::connection($connectionName)->getPdo();
            
            // Test prostego zapytania
            $result = DB::connection($connectionName)->select('SELECT 1 as test');
            
            $endTime = microtime(true);
            $time = round(($endTime - $startTime) * 1000, 2);
            
            $this->line("  ✓ Połączenie udane");
            $this->line("  ✓ Zapytanie testowe wykonane pomyślnie");
            $this->line("  ⏱ Czas odpowiedzi: {$time}ms");
            
            return [
                'success' => true,
                'time' => $time,
                'error' => null,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $time = round(($endTime - $startTime) * 1000, 2);
            
            $errorMessage = $e->getMessage();
            $this->line("  ✗ Błąd: {$errorMessage}");
            $this->line("  ⏱ Czas do błędu: {$time}ms");
            
            return [
                'success' => false,
                'time' => $time,
                'error' => $errorMessage,
            ];
        }
    }
}

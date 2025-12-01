<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Obsługa błędów połączenia Enova - automatyczne przełączanie na backup
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($e->getConnectionName() === 'sqlsrv') {
                $connectionName = 'sqlsrv';
                $primaryHost = env('DB_ENOVA_HOST');
                $backupHost = env('DB_ENOVA_HOST_BACKUP');

                if ($primaryHost && $backupHost) {
                    $message = $e->getMessage();
                    $isConnectionError = str_contains($message, 'Unable to connect') ||
                        str_contains($message, 'TCP Provider') ||
                        str_contains($message, 'Timeout') ||
                        str_contains($message, 'Server is unavailable') ||
                        str_contains($message, 'does not exist');

                    if ($isConnectionError) {
                        $currentHost = config("database.connections.{$connectionName}.host");

                        // Jeśli używamy primary i wystąpił błąd, przełącz na backup
                        if ($currentHost === $primaryHost) {
                            \Illuminate\Support\Facades\Log::warning('Błąd połączenia z Primary Enova host, przełączam na Backup', [
                                'primary_host' => $primaryHost,
                                'backup_host' => $backupHost,
                                'error' => $message,
                            ]);

                            config(["database.connections.{$connectionName}.host" => $backupHost]);
                            \Illuminate\Support\Facades\DB::purge($connectionName);
                            \Illuminate\Support\Facades\Cache::put('enova_working_host', $backupHost, 300);

                            // Zwróć błąd, ale host jest już przełączony na backup dla kolejnych zapytań
                        }
                    }
                }
            }
        });
    })->create();

<?php

// Skrypt do pakowania plików do wdrożenia
$sourceDir = __DIR__;
$targetDir = __DIR__ . '/deploy';

// Katalogi do skopiowania
$dirs = [
    'app',
    'bootstrap',
    'config',
    'database',
    'lang',
    'public',
    'resources',
    'routes',
    'storage',
    'vendor',
];

// Pliki do skopiowania
$files = [
    '.env.example',
    'artisan',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'vite.config.js',
    'phpunit.xml',
];

// Katalogi do wykluczenia
$excludeDirs = [
    'node_modules',
    '.git',
    '.fleet',
    '.idea',
    '.nova',
    '.zed',
    'tests',
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/framework/views',
];

echo "Pakowanie plików do wdrożenia...\n";

// Utwórz katalog docelowy
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Kopiuj katalogi
foreach ($dirs as $dir) {
    if (is_dir($sourceDir . '/' . $dir)) {
        echo "Kopiuję katalog: $dir\n";
        copyDir($sourceDir . '/' . $dir, $targetDir . '/' . $dir, $excludeDirs);
    }
}

// Kopiuj pliki
foreach ($files as $file) {
    if (file_exists($sourceDir . '/' . $file)) {
        echo "Kopiuję plik: $file\n";
        copy($sourceDir . '/' . $file, $targetDir . '/' . $file);
    }
}

echo "\nPakowanie zakończone! Pliki w katalogu: $targetDir\n";
echo "Następne kroki:\n";
echo "1. Wyślij pliki z katalogu 'deploy' na hosting\n";
echo "2. Na hostingu: composer install --no-dev --optimize-autoloader\n";
echo "3. Na hostingu: npm install --production && npm run build\n";
echo "4. Skonfiguruj .env na hostingu\n";

function copyDir($src, $dst, $excludeDirs)
{
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }

    while (($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;

            // Sprawdź czy katalog jest wykluczony
            $excluded = false;
            foreach ($excludeDirs as $excludeDir) {
                if (strpos($srcPath, $excludeDir) !== false) {
                    $excluded = true;
                    break;
                }
            }

            if (!$excluded) {
                if (is_dir($srcPath)) {
                    copyDir($srcPath, $dstPath, $excludeDirs);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
    }
    closedir($dir);
}

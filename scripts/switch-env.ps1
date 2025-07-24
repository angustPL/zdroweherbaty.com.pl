# Skrypt do przełączania między środowiskami w Laravel

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("local", "production")]
    [string]$Environment
)

# Sprawdź, czy istnieje plik .env.example
$envExamplePath = Join-Path $PSScriptRoot "..\.env.example"
$envPath = Join-Path $PSScriptRoot "..\.env"

if (-not (Test-Path $envExamplePath)) {
    Write-Error "Nie znaleziono pliku .env.example"
    exit 1
}

# Skopiuj .env.example do .env jeśli nie istnieje
if (-not (Test-Path $envPath)) {
    Copy-Item $envExamplePath $envPath
}

# Wczytaj zawartość pliku .env
$envContent = Get-Content $envPath -Raw

# Zdekomentuj wszystkie linie z ustawieniami
$envContent = $envContent -replace '([A-Z_]+=.*)(?=\n|$)', '# $1'

# Dla środowiska lokalnego
if ($Environment -eq "local") {
    $envContent = $envContent -replace '# DB_CONNECTION=sqlsrv', 'DB_CONNECTION=sqlsrv' -replace '# DB_HOST=LAPTOP\SQLEXPRESS', 'DB_HOST=LAPTOP\SQLEXPRESS' -replace '# DB_PORT=1433', 'DB_PORT=1433' -replace '# DB_DATABASE=Bifix', 'DB_DATABASE=Bifix' -replace '# DB_USERNAME=admin', 'DB_USERNAME=admin' -replace '# DB_PASSWORD=123ptaszeK', 'DB_PASSWORD=123ptaszeK' -replace '# DB_ENCRYPT=false', 'DB_ENCRYPT=false' -replace '# DB_TRUST_SERVER_CERTIFICATE=true', 'DB_TRUST_SERVER_CERTIFICATE=true' -replace '# DB_READONLY=false', 'DB_READONLY=false' -replace '# APP_ENV=local', 'APP_ENV=local' -replace '# APP_DEBUG=true', 'APP_DEBUG=true' -replace '# APP_URL=http://localhost', 'APP_URL=http://localhost' -replace '# CACHE_DRIVER=array', 'CACHE_DRIVER=array' -replace '# SESSION_DRIVER=array', 'SESSION_DRIVER=array' -replace '# QUEUE_CONNECTION=sync', 'QUEUE_CONNECTION=sync'
}

# Dla środowiska produkcyjnego
if ($Environment -eq "production") {
    $envContent = $envContent -replace '# DB_CONNECTION=sqlsrv', 'DB_CONNECTION=sqlsrv' -replace '# DB_HOST=adres_zdalny', 'DB_HOST=adres_zdalny' -replace '# DB_PORT=1433', 'DB_PORT=1433' -replace '# DB_DATABASE=nazwa_bazy', 'DB_DATABASE=nazwa_bazy' -replace '# DB_USERNAME=remote_user', 'DB_USERNAME=remote_user' -replace '# DB_PASSWORD=remote_password', 'DB_PASSWORD=remote_password' -replace '# DB_ENCRYPT=true', 'DB_ENCRYPT=true' -replace '# DB_TRUST_SERVER_CERTIFICATE=true', 'DB_TRUST_SERVER_CERTIFICATE=true' -replace '# DB_READONLY=true', 'DB_READONLY=true' -replace '# APP_ENV=production', 'APP_ENV=production' -replace '# APP_DEBUG=false', 'APP_DEBUG=false' -replace '# APP_URL=https://zdroweherbaty.com.pl', 'APP_URL=https://zdroweherbaty.com.pl' -replace '# MAIL_MAILER=smtp', 'MAIL_MAILER=smtp' -replace '# MAIL_SCHEME=tls', 'MAIL_SCHEME=tls' -replace '# MAIL_HOST=smtp.gmail.com', 'MAIL_HOST=smtp.gmail.com' -replace '# MAIL_PORT=587', 'MAIL_PORT=587'
}

# Zapisz zmiany
$envContent | Set-Content $envPath -Encoding UTF8

Write-Host "Przełączono na środowisko: $Environment" -ForegroundColor Green
Write-Host "Plik .env został zaktualizowany" -ForegroundColor Green
Write-Host "Należy uruchomić ponownie serwer Laravel" -ForegroundColor Yellow

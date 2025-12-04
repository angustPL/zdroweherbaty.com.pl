# Instrukcja zmiany lokalizacji datadir MariaDB

## Aktualna lokalizacja
```
C:/www/zdroweherbaty.com.pl-laravel/database/mariadb
```

## Proponowana nowa lokalizacja
```
C:/Users/jacek/AppData/Local/MariaDB/data
```

## Kroki do wykonania

### 1. Zatrzymaj MariaDB
- Otwórz DBngin
- Zatrzymaj serwer MariaDB

### 2. Utwórz nowy katalog
```powershell
New-Item -ItemType Directory -Path "C:\Users\jacek\AppData\Local\MariaDB\data" -Force
```

### 3. Przenieś pliki bazy danych
```powershell
# Skopiuj wszystkie pliki i katalogi
Copy-Item -Path "C:\www\zdroweherbaty.com.pl-laravel\database\mariadb\*" -Destination "C:\Users\jacek\AppData\Local\MariaDB\data\" -Recurse -Force

# Po weryfikacji, usuń stare pliki (UWAGA: tylko jeśli wszystko działa!)
# Remove-Item -Path "C:\www\zdroweherbaty.com.pl-laravel\database\mariadb\*" -Recurse -Force
```

### 4. Zmień konfigurację w pliku my.ini

Plik: `C:\www\zdroweherbaty.com.pl-laravel\database\mariadb\my.ini`

Zmień:
- Linia 4: `datadir="C:/www/zdroweherbaty.com.pl-laravel/database/mariadb"` 
  → `datadir="C:/Users/jacek/AppData/Local/MariaDB/data"`
- Linia 6: `log-error="C:/www/zdroweherbaty.com.pl-laravel/database/mariadb/mariadbd.local.err"`
  → `log-error="C:/Users/jacek/AppData/Local/MariaDB/data/mariadbd.local.err"`
- Linia 7: `pid-file="C:/www/zdroweherbaty.com.pl-laravel/database/mariadb/mariadb.pid"`
  → `pid-file="C:/Users/jacek/AppData/Local/MariaDB/data/mariadb.pid"`
- Linia 10: `datadir=C:/www/zdroweherbaty.com.pl-laravel/database/mariadb`
  → `datadir=C:/Users/jacek/AppData/Local/MariaDB/data`

### 5. Zmień konfigurację w pliku my.cnf

Plik: `C:\www\zdroweherbaty.com.pl-laravel\database\mariadb\my.cnf`

Zmień:
- Linia 4: `datadir="C:/www/zdroweherbaty.com.pl-laravel/database/mariadb"`
  → `datadir="C:/Users/jacek/AppData/Local/MariaDB/data"`
- Linia 6: `log-error="C:/www/zdroweherbaty.com.pl-laravel/database/mariadb/mariadbd.local.err"`
  → `log-error="C:/Users/jacek/AppData/Local/MariaDB/data/mariadbd.local.err"`
- Linia 7: `pid-file="C:/www/zdroweherbaty.com.pl-laravel/database/mariadb/mariadb.pid"`
  → `pid-file="C:/Users/jacek/AppData/Local/MariaDB/data/mariadb.pid"`

### 6. Uruchom ponownie MariaDB
- Otwórz DBngin
- Uruchom serwer MariaDB

### 7. Weryfikacja
```powershell
cd C:\www\zdroweherbaty.com.pl
php artisan tinker --execute="echo DB::select('SHOW VARIABLES LIKE \"datadir\"')[0]->Value;"
```

Powinno pokazać: `C:\Users\jacek\AppData\Local\MariaDB\data`

## Alternatywna lokalizacja (jeśli wolisz)
```
C:\ProgramData\MariaDB\data
```
(wymaga uprawnień administratora)


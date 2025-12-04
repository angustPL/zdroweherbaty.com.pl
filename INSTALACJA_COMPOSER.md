# 📦 Instalacja Composer na Serwerze

**Projekt:** Zdrowe Herbaty Laravel 12  
**Data:** 2025-01-XX

---

## 📋 Wymagania

-   PHP 8.2+ (zalecane 8.3+)
-   Dostęp SSH do serwera
-   Uprawnienia do zapisu w katalogu domowym lub `/usr/local/bin`

---

## 🔧 Metoda 1: Instalacja Globalna (Zalecana)

### Krok 1: Pobierz instalator Composer

```bash
# Połącz się z serwerem przez SSH
ssh uzytkownik@serwer.pl

# Pobierz instalator
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
```

### Krok 2: Zweryfikuj instalator (opcjonalnie, ale zalecane)

```bash
# Pobierz hash weryfikacyjny
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cc84e6d') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
```

**Uwaga:** Hash może się zmienić - sprawdź aktualny na https://getcomposer.org/download/

### Krok 3: Uruchom instalator

```bash
php composer-setup.php
```

### Krok 4: Przenieś Composer do katalogu globalnego

```bash
# Przenieś do /usr/local/bin (wymaga sudo)
sudo mv composer.phar /usr/local/bin/composer

# Lub do katalogu domowego (bez sudo)
mkdir -p ~/bin
mv composer.phar ~/bin/composer
chmod +x ~/bin/composer
```

### Krok 5: Dodaj do PATH (jeśli używasz ~/bin)

```bash
# Dodaj do ~/.bashrc lub ~/.bash_profile
echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
```

### Krok 6: Usuń instalator

```bash
php -r "unlink('composer-setup.php');"
```

### Krok 7: Sprawdź instalację

```bash
composer --version
# Powinno wyświetlić: Composer version X.X.X
```

---

## 🔧 Metoda 2: Instalacja Lokalna (Bez Uprawnień Root)

Jeśli nie masz uprawnień sudo, możesz zainstalować Composer lokalnie:

### Krok 1: Pobierz i zainstaluj

```bash
# Przejdź do katalogu domowego
cd ~

# Pobierz instalator
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Uruchom instalator
php composer-setup.php

# Usuń instalator
php -r "unlink('composer-setup.php');"
```

### Krok 2: Utwórz alias (opcjonalnie)

```bash
# Dodaj alias do ~/.bashrc
echo 'alias composer="php ~/composer.phar"' >> ~/.bashrc
source ~/.bashrc
```

### Krok 3: Sprawdź instalację

```bash
php ~/composer.phar --version
# Lub jeśli masz alias:
composer --version
```

---

## 🔧 Metoda 3: Instalacja przez cURL (Jedna Komenda)

```bash
# Pobierz i zainstaluj w jednej komendzie
curl -sS https://getcomposer.org/installer | php

# Przenieś do katalogu globalnego (wymaga sudo)
sudo mv composer.phar /usr/local/bin/composer

# Lub do katalogu domowego
mkdir -p ~/bin
mv composer.phar ~/bin/composer
chmod +x ~/bin/composer
```

---

## 🔧 Metoda 4: Instalacja na Hostingu dhosting.pl

Na hostingu dhosting.pl Composer może być już zainstalowany. Sprawdź:

```bash
# Sprawdź czy Composer jest dostępny
which composer
composer --version
```

Jeśli nie jest zainstalowany, użyj **Metody 2** (instalacja lokalna w katalogu domowym).

---

## ✅ Weryfikacja Instalacji

### Sprawdź wersję

```bash
composer --version
```

### Sprawdź konfigurację

```bash
composer config --list
```

### Sprawdź lokalizację

```bash
which composer
```

---

## 🔧 Aktualizacja Composer

### Aktualizacja do najnowszej wersji

```bash
composer self-update
```

### Aktualizacja do konkretnej wersji

```bash
composer self-update 2.7.0
```

### Sprawdź aktualną wersję

```bash
composer --version
```

---

## ⚠️ Ostrzeżenia Deprecated (PHP 8.2+)

### Problem: "Using ${var} in strings is deprecated"

**Wyjaśnienie:**

-   To są tylko **ostrzeżenia (warnings)**, nie błędy
-   Instalacja Composer powinna działać pomimo tych ostrzeżeń
-   Problem wynika z przestarzałej składni `${var}` w starszych wersjach Composer

**Rozwiązanie 1: Zaktualizuj Composer (zalecane)**

```bash
# Zaktualizuj Composer do najnowszej wersji
composer self-update

# Sprawdź wersję
composer --version
```

**Rozwiązanie 2: Wycisz ostrzeżenia (tymczasowe)**

```bash
# Wyłącz wyświetlanie deprecated warnings
php -d error_reporting=E_ALL & ~E_DEPRECATED ~E_STRICT composer install --no-dev --optimize-autoloader

# Lub użyj zmiennej środowiskowej
export PHP_INI_SCAN_DIR=""
php -d error_reporting=E_ALL & ~E_DEPRECATED composer install --no-dev --optimize-autoloader
```

**Rozwiązanie 3: Zignoruj ostrzeżenia (jeśli instalacja działa)**

Ostrzeżenia nie blokują instalacji. Możesz je bezpiecznie zignorować, jeśli:

-   Instalacja się kończy sukcesem
-   Zależności są zainstalowane poprawnie
-   Aplikacja działa

**Sprawdź czy instalacja się powiodła:**

```bash
# Sprawdź czy folder vendor istnieje
ls -la vendor/

# Sprawdź czy autoloader jest wygenerowany
ls -la vendor/autoload.php

# Sprawdź czy zależności są zainstalowane
composer show
```

---

## 🐛 Rozwiązywanie Problemów

### Problem: "composer: command not found"

**Rozwiązanie:**

1. Sprawdź czy Composer jest zainstalowany:

    ```bash
    ls -la ~/composer.phar
    # Lub
    ls -la /usr/local/bin/composer
    ```

2. Sprawdź PATH:

    ```bash
    echo $PATH
    ```

3. Dodaj do PATH (jeśli używasz ~/bin):

    ```bash
    echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
    source ~/.bashrc
    ```

4. Użyj pełnej ścieżki:
    ```bash
    php ~/composer.phar --version
    ```

### Problem: "Permission denied"

**Rozwiązanie:**

1. Nadaj uprawnienia do wykonania:

    ```bash
    chmod +x composer.phar
    # Lub
    chmod +x ~/bin/composer
    ```

2. Sprawdź uprawnienia:
    ```bash
    ls -la composer.phar
    ```

### Problem: "PHP version too old"

**Rozwiązanie:**

1. Sprawdź wersję PHP:

    ```bash
    php -v
    ```

2. Użyj konkretnej wersji PHP (jeśli masz kilka):
    ```bash
    /usr/bin/php8.3 composer.phar --version
    ```

### Problem: "SSL certificate problem"

**Rozwiązanie:**

1. Wyłącz weryfikację SSL (tylko dla instalacji):

    ```bash
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" --no-check-certificate
    ```

2. Lub użyj http zamiast https (niezalecane):
    ```bash
    php -r "copy('http://getcomposer.org/installer', 'composer-setup.php');"
    ```

---

## 📝 Użycie Composer w Projekcie

Po zainstalowaniu Composer, możesz użyć go w projekcie:

```bash
# Przejdź do katalogu projektu
cd /var/www/zdroweherbaty.com.pl

# Zainstaluj zależności (produkcja)
composer install --no-dev --optimize-autoloader

# Zainstaluj zależności (development)
composer install

# Aktualizuj zależności
composer update

# Sprawdź zależności
composer show

# Wyczyść cache Composer
composer clear-cache
```

---

## 🔒 Bezpieczeństwo

### Weryfikacja instalatora

Zawsze weryfikuj hash instalatora przed uruchomieniem:

```bash
# Pobierz aktualny hash z https://getcomposer.org/download/
php -r "if (hash_file('sha384', 'composer-setup.php') === 'AKTUALNY_HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
```

### Uprawnienia

Upewnij się, że tylko Ty masz uprawnienia do pliku Composer:

```bash
chmod 755 composer.phar
# Lub
chmod 755 ~/bin/composer
```

---

## 📚 Dokumentacja

-   **Oficjalna dokumentacja:** https://getcomposer.org/doc/
-   **Instalacja:** https://getcomposer.org/download/
-   **FAQ:** https://getcomposer.org/doc/faqs/

---

**Ostatnia aktualizacja:** 2025-01-XX  
**Wersja:** 1.0.0

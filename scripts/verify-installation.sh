#!/bin/bash

# Skrypt weryfikacji instalacji na hostingu
# Użycie: ./scripts/verify-installation.sh

echo "🔍 Weryfikacja instalacji Zdrowe Herbaty Laravel 12"
echo "=================================================="
echo ""

# Kolory
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Liczniki
PASSED=0
FAILED=0

# Funkcja sprawdzająca
check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $1${NC}"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}❌ $1${NC}"
        ((FAILED++))
        return 1
    fi
}

# 1. Sprawdź PHP
echo "1. Sprawdzanie PHP..."
php -v > /dev/null 2>&1
check "PHP jest zainstalowany"

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "   Wersja PHP: $PHP_VERSION"

# 2. Sprawdź Composer
echo ""
echo "2. Sprawdzanie Composer..."
composer --version > /dev/null 2>&1
check "Composer jest zainstalowany"

# 3. Sprawdź Node.js
echo ""
echo "3. Sprawdzanie Node.js..."
node --version > /dev/null 2>&1
check "Node.js jest zainstalowany"

# 4. Sprawdź plik .env
echo ""
echo "4. Sprawdzanie pliku .env..."
if [ -f .env ]; then
    check "Plik .env istnieje"

    # Sprawdź APP_KEY
    if grep -q "APP_KEY=base64:" .env; then
        check "APP_KEY jest ustawiony"
    else
        echo -e "${RED}❌ APP_KEY nie jest ustawiony${NC}"
        echo -e "${YELLOW}   Uruchom: php artisan key:generate${NC}"
        ((FAILED++))
    fi
else
    echo -e "${RED}❌ Plik .env nie istnieje${NC}"
    echo -e "${YELLOW}   Utwórz: cp .env.production .env${NC}"
    ((FAILED++))
fi

# 5. Sprawdź vendor
echo ""
echo "5. Sprawdzanie zależności PHP..."
if [ -d vendor ]; then
    check "Katalog vendor istnieje"
else
    echo -e "${RED}❌ Katalog vendor nie istnieje${NC}"
    echo -e "${YELLOW}   Uruchom: composer install --no-dev --optimize-autoloader${NC}"
    ((FAILED++))
fi

# 6. Sprawdź build assets
echo ""
echo "6. Sprawdzanie assetów..."
if [ -d public/build ]; then
    check "Katalog public/build istnieje"
else
    echo -e "${YELLOW}⚠️  Katalog public/build nie istnieje${NC}"
    echo -e "${YELLOW}   Uruchom: npm install && npm run build${NC}"
fi

# 7. Sprawdź połączenie z bazą danych
echo ""
echo "7. Sprawdzanie połączenia z bazą danych..."
php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    check "Połączenie z bazą danych działa"
else
    echo -e "${RED}❌ Nie można połączyć się z bazą danych${NC}"
    echo -e "${YELLOW}   Sprawdź zmienne DB_* w .env${NC}"
    ((FAILED++))
fi

# 8. Sprawdź migracje
echo ""
echo "8. Sprawdzanie migracji..."
php artisan migrate:status > /dev/null 2>&1
if [ $? -eq 0 ]; then
    check "Migracje są dostępne"

    # Sprawdź czy wszystkie migracje są uruchomione
    PENDING=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || echo "0")
    if [ "$PENDING" -eq 0 ]; then
        check "Wszystkie migracje są uruchomione"
    else
        echo -e "${YELLOW}⚠️  Istnieją nieuruchomione migracje: $PENDING${NC}"
        echo -e "${YELLOW}   Uruchom: php artisan migrate --force${NC}"
    fi
else
    echo -e "${RED}❌ Nie można sprawdzić statusu migracji${NC}"
    ((FAILED++))
fi

# 9. Sprawdź uprawnienia storage
echo ""
echo "9. Sprawdzanie uprawnień storage..."
if [ -d storage ]; then
    check "Katalog storage istnieje"

    if [ -w storage ]; then
        check "Katalog storage ma uprawnienia do zapisu"
    else
        echo -e "${RED}❌ Katalog storage nie ma uprawnień do zapisu${NC}"
        echo -e "${YELLOW}   Uruchom: chmod -R 775 storage${NC}"
        ((FAILED++))
    fi
else
    echo -e "${RED}❌ Katalog storage nie istnieje${NC}"
    ((FAILED++))
fi

# 10. Sprawdź uprawnienia bootstrap/cache
echo ""
echo "10. Sprawdzanie uprawnień bootstrap/cache..."
if [ -d bootstrap/cache ]; then
    check "Katalog bootstrap/cache istnieje"

    if [ -w bootstrap/cache ]; then
        check "Katalog bootstrap/cache ma uprawnienia do zapisu"
    else
        echo -e "${RED}❌ Katalog bootstrap/cache nie ma uprawnień do zapisu${NC}"
        echo -e "${YELLOW}   Uruchom: chmod -R 775 bootstrap/cache${NC}"
        ((FAILED++))
    fi
else
    echo -e "${RED}❌ Katalog bootstrap/cache nie istnieje${NC}"
    ((FAILED++))
fi

# 11. Sprawdź cache Enova
echo ""
echo "11. Sprawdzanie cache Enova..."
php artisan enova:generate-backup-cache --check > /dev/null 2>&1
if [ $? -eq 0 ]; then
    check "Cache Enova jest dostępny"
else
    echo -e "${YELLOW}⚠️  Cache Enova nie jest wygenerowany${NC}"
    echo -e "${YELLOW}   Uruchom: php artisan enova:generate-backup-cache --force${NC}"
fi

# 12. Sprawdź połączenie z Enova
echo ""
echo "12. Sprawdzanie połączenia z Enova..."
php artisan tinker --execute="DB::connection('sqlsrv')->getPdo();" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    check "Połączenie z Enova działa"
else
    echo -e "${YELLOW}⚠️  Nie można połączyć się z Enova${NC}"
    echo -e "${YELLOW}   Sprawdź zmienne DB_ENOVA_* w .env${NC}"
fi

# 13. Sprawdź cache Laravel
echo ""
echo "13. Sprawdzanie cache Laravel..."
php artisan config:cache > /dev/null 2>&1
if [ $? -eq 0 ]; then
    check "Cache konfiguracji można wygenerować"
else
    echo -e "${YELLOW}⚠️  Nie można wygenerować cache konfiguracji${NC}"
fi

# Podsumowanie
echo ""
echo "=================================================="
echo "📊 Podsumowanie:"
echo -e "${GREEN}✅ Przeszło: $PASSED${NC}"
echo -e "${RED}❌ Nie przeszło: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}🎉 Wszystkie testy przeszły! Instalacja wygląda na poprawną.${NC}"
    exit 0
else
    echo -e "${YELLOW}⚠️  Niektóre testy nie przeszły. Sprawdź powyższe komunikaty.${NC}"
    exit 1
fi


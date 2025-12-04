# 📦 Instalacja Node.js i NPM na Serwerze

**Projekt:** Zdrowe Herbaty Laravel 12  
**Data:** 2025-01-XX

---

## 📋 Wymagania

-   Dostęp SSH do serwera
-   Uprawnienia do instalacji w katalogu domowym (bez root)

---

## 🔧 Metoda 1: Instalacja przez NVM (Node Version Manager) - Zalecana

NVM pozwala na instalację Node.js bez uprawnień root.

### Krok 1: Zainstaluj NVM

```bash
# Połącz się z serwerem przez SSH
ssh uzytkownik@serwer.pl

# Pobierz i zainstaluj NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# Lub przez wget
wget -qO- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
```

### Krok 2: Załaduj NVM

```bash
# Załaduj NVM do bieżącej sesji
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"

# Lub dodaj do ~/.bashrc (dla stałego działania)
echo 'export NVM_DIR="$HOME/.nvm"' >> ~/.bashrc
echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> ~/.bashrc
echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> ~/.bashrc
source ~/.bashrc
```

### Krok 3: Zainstaluj Node.js

```bash
# Zainstaluj najnowszą wersję LTS
nvm install --lts

# Lub konkretną wersję
nvm install 20.10.0

# Użyj zainstalowanej wersji
nvm use --lts

# Ustaw jako domyślną
nvm alias default node
```

### Krok 4: Sprawdź instalację

```bash
node --version
npm --version
```

---

## 🔧 Metoda 2: Instalacja Binarna (Bez NVM)

### Krok 1: Pobierz Node.js

```bash
# Przejdź do katalogu domowego
cd ~

# Pobierz Node.js (sprawdź najnowszą wersję na nodejs.org)
wget https://nodejs.org/dist/v20.10.0/node-v20.10.0-linux-x64.tar.xz

# Rozpakuj
tar -xf node-v20.10.0-linux-x64.tar.xz

# Przenieś do katalogu bin
mkdir -p ~/bin
mv node-v20.10.0-linux-x64 ~/bin/nodejs
```

### Krok 2: Dodaj do PATH

```bash
# Dodaj do ~/.bashrc
echo 'export PATH="$HOME/bin/nodejs/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
```

### Krok 3: Sprawdź instalację

```bash
node --version
npm --version
```

---

## 🔧 Metoda 3: Instalacja przez Pakiet Menedżera (Wymaga Root)

Jeśli masz uprawnienia root lub sudo:

### Dla Ubuntu/Debian:

```bash
# Zainstaluj Node.js z repozytorium NodeSource
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### Dla CentOS/RHEL:

```bash
# Zainstaluj Node.js z repozytorium NodeSource
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo yum install -y nodejs
```

---

## 🔧 Metoda 4: Alternatywa - Budowanie Lokalnie (Zalecane dla Hostingu)

Jeśli nie możesz zainstalować Node.js na serwerze, zbuduj assety lokalnie i wgraj na hosting.

### Krok 1: Zbuduj assety lokalnie

```bash
# Na lokalnym komputerze (Windows/Mac/Linux)
cd C:\www\zdroweherbaty.com.pl-laravel

# Zainstaluj zależności
npm install

# Zbuduj assety produkcyjne
npm run build
```

### Krok 2: Wgraj folder `public/build/` na hosting

```bash
# Przez SFTP/FTP wgraj:
public/build/
```

**Struktura do wgrania:**

```
public/
└── build/
    ├── assets/
    │   ├── app-xxxxx.js
    │   ├── app-xxxxx.css
    │   └── ...
    └── manifest.json
```

### Krok 3: Sprawdź na hostingu

```bash
# Na hostingu sprawdź czy pliki są wgranione
ls -la public/build/
ls -la public/build/assets/
```

---

## ✅ Weryfikacja Instalacji

### Sprawdź wersję Node.js

```bash
node --version
# Powinno wyświetlić: v20.x.x lub podobne
```

### Sprawdź wersję NPM

```bash
npm --version
# Powinno wyświetlić: 10.x.x lub podobne
```

### Sprawdź lokalizację

```bash
which node
which npm
```

---

## 🔧 Użycie w Projekcie

Po zainstalowaniu Node.js i npm:

```bash
# Przejdź do katalogu projektu
cd /var/www/zdroweherbaty.com.pl

# Zainstaluj zależności (produkcja)
npm install --production

# Zbuduj assety
npm run build

# Lub dla developmentu
npm install
npm run dev
```

---

## 🔧 Aktualizacja Node.js i NPM

### Przez NVM:

```bash
# Zainstaluj najnowszą wersję LTS
nvm install --lts

# Użyj nowej wersji
nvm use --lts

# Ustaw jako domyślną
nvm alias default node
```

### Przez NPM:

```bash
# Aktualizuj npm do najnowszej wersji
npm install -g npm@latest
```

---

## 🐛 Rozwiązywanie Problemów

### Problem: "command not found: node"

**Rozwiązanie:**

1. Sprawdź czy Node.js jest zainstalowany:

    ```bash
    ls -la ~/bin/nodejs/bin/node
    # Lub
    ls -la ~/.nvm/versions/node/
    ```

2. Sprawdź PATH:

    ```bash
    echo $PATH
    ```

3. Dodaj do PATH:

    ```bash
    echo 'export PATH="$HOME/bin/nodejs/bin:$PATH"' >> ~/.bashrc
    source ~/.bashrc
    ```

4. Dla NVM:
    ```bash
    source ~/.nvm/nvm.sh
    nvm use --lts
    ```

### Problem: "Permission denied"

**Rozwiązanie:**

1. Nadaj uprawnienia:

    ```bash
    chmod +x ~/bin/nodejs/bin/node
    chmod +x ~/bin/nodejs/bin/npm
    ```

2. Sprawdź uprawnienia:
    ```bash
    ls -la ~/bin/nodejs/bin/
    ```

### Problem: "npm install" nie działa

**Rozwiązanie:**

1. Sprawdź czy npm jest zainstalowany:

    ```bash
    npm --version
    ```

2. Wyczyść cache npm:

    ```bash
    npm cache clean --force
    ```

3. Sprawdź uprawnienia do katalogu projektu:
    ```bash
    ls -la package.json
    chmod 644 package.json
    ```

### Problem: Brak miejsca na dysku

**Rozwiązanie:**

1. Sprawdź miejsce na dysku:

    ```bash
    df -h
    ```

2. Wyczyść cache npm:

    ```bash
    npm cache clean --force
    ```

3. Usuń node_modules i zainstaluj ponownie:
    ```bash
    rm -rf node_modules
    npm install --production
    ```

---

## 📝 Alternatywa: Budowanie Lokalnie

Jeśli instalacja Node.js na serwerze jest problematyczna, **zalecane jest budowanie lokalnie**:

### Zalety budowania lokalnie:

-   ✅ Nie wymaga Node.js na serwerze
-   ✅ Szybsze (lokalny komputer jest zwykle szybszy)
-   ✅ Łatwiejsze debugowanie
-   ✅ Mniej problemów z uprawnieniami

### Proces:

1. **Lokalnie:**

    ```bash
    npm install
    npm run build
    ```

2. **Wgraj na hosting:**

    - Folder `public/build/` przez SFTP/FTP

3. **Sprawdź na hostingu:**
    ```bash
    ls -la public/build/assets/
    ```

---

## 📚 Dokumentacja

-   **Node.js:** https://nodejs.org/
-   **NPM:** https://www.npmjs.com/
-   **NVM:** https://github.com/nvm-sh/nvm
-   **Vite (bundler):** https://vitejs.dev/

---

**Ostatnia aktualizacja:** 2025-01-XX  
**Wersja:** 1.0.0

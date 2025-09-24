# 🚀 **Implementacja SEO Tools - Artesaos SEO Tools**

## 📋 **Przegląd**

Zaimplementowano kompleksowe SEO dla strony **Zdrowe Herbaty BIFIX** używając pakietu **Artesaos SEO Tools**. System obejmuje:

-   ✅ **Meta Tags** (title, description, keywords, canonical)
-   ✅ **Open Graph** (Facebook, Twitter, LinkedIn)
-   ✅ **Twitter Cards** (summary_large_image)
-   ✅ **Schema.org JSON-LD** (strukturalne dane)

## 🛠️ **Zainstalowane komponenty**

### **1. Pakiet SEO Tools**

```bash
composer require artesaos/seotools
```

### **2. Plik konfiguracyjny**

-   `config/seotools.php` - główna konfiguracja SEO

### **3. Zaimplementowane strony**

-   🏠 **Strona główna** (`/`) - `WebSite` + `SearchAction`
-   🔍 **Wyszukiwarka** (`/wyszukaj`) - `WebSite` + `SearchAction`
-   📂 **Kategorie** (`/grupa/{nazwa}`) - `CollectionPage`
-   🛍️ **Produkty** (`/towar/{id}/{nazwa}`) - `Product` + `Offer`
-   🛒 **Koszyk** (`/koszyk`) - `WebPage`
-   🚚 **Dostawa** (`/dostawa`) - `WebPage`
-   📞 **Kontakt** (`/kontakt`) - `ContactPage` + `Organization`
-   📜 **Regulamin** (`/regulamin`) - `WebPage` (NOINDEX, NOFOLLOW) - **Zabronione indeksowanie**

## 🎯 **Funkcjonalności SEO**

### **Meta Tags**

```php
SEOTools::setTitle('Tytuł strony - Zdrowe Herbaty BIFIX');
SEOTools::setDescription('Opis strony z kluczowymi słowami');
SEOTools::setCanonical(url()->current()); // Aktualny URL strony
```

### **Open Graph**

```php
SEOTools::opengraph()->setTitle('Tytuł OG');
SEOTools::opengraph()->setDescription('Opis OG');
SEOTools::opengraph()->setUrl(url()->current());
SEOTools::opengraph()->setType('website');
SEOTools::opengraph()->setSiteName('Zdrowe Herbaty BIFIX');
```

### **Twitter Cards**

```php
SEOTools::twitter()->setCard('summary_large_image');
SEOTools::twitter()->setTitle('Tytuł Twitter');
SEOTools::twitter()->setDescription('Opis Twitter');
```

### **Schema.org JSON-LD**

```php
JsonLd::setType('Product')
    ->addValue('name', 'Nazwa produktu')
    ->addValue('description', 'Opis produktu')
    ->addValue('brand', 'BIFIX')
    ->addValue('offers', [
        '@type' => 'Offer',
        'price' => 29.99,
        'priceCurrency' => 'PLN'
    ]);
```

### **Canonical URL**

```php
// Canonical URL powinien wskazywać na aktualną stronę
SEOTools::setCanonical(url()->current());

// NIE ustawiaj canonical na stronę główną dla podstron
// To może powodować problemy z SEO i duplikacją treści
```

### **Zabronienie indeksowania (NOINDEX, NOFOLLOW)**

```php
// Dla stron, które nie powinny być indeksowane (np. regulamin)
SEOTools::addMeta('robots', 'noindex, nofollow');
SEOTools::addMeta('googlebot', 'noindex, nofollow');
SEOTools::addMeta('bingbot', 'noindex, nofollow');

// Open Graph
SEOTools::opengraph()->addProperty('robots', 'noindex, nofollow');
```

## 🔧 **Implementacja z fasadami**

### **Fasady Artesaos SEO Tools**

Zgodnie z [dokumentacją artesaos/seotools](https://github.com/artesaos/seotools), używamy fasad zamiast `app()` helper:

```php
use Artesaos\SEOTools\Facades\SEOTools;      // Główna fasad
use Artesaos\SEOTools\Facades\SEOMeta;       // Meta tags
use Artesaos\SEOTools\Facades\OpenGraph;     // Open Graph
use Artesaos\SEOTools\Facades\TwitterCard;   // Twitter Cards
use Artesaos\SEOTools\Facades\JsonLd;        // Schema.org JSON-LD
use Artesaos\SEOTools\Facades\JsonLdMulti;   // Wiele Schema.org
```

**Przed (z `app()` helper):**

```php
app('seotools')->setTitle('Tytuł');
app('seotools.json-ld')->setType('WebPage');
```

**Po (z fasadami):**

```php
SEOTools::setTitle('Tytuł');
JsonLd::setType('WebPage');
```

## 🔧 **Implementacja w layoucie**

### **Architektura SEO**

**Główny layout** (`resources/views/layouts/app.blade.php`) zawiera:

-   **Globalne SEO Tools** - generuje meta tagi i JSON-LD z poszczególnych stron

**Konfiguracja** (`config/seotools.php`) zawiera:

-   **Globalne dane organizacji** - automatycznie dodawane do każdej strony

**Poszczególne strony** (Volt/Livewire) zawierają:

-   **Meta tagi** - title, description, Open Graph, Twitter
-   **Stronowo-specyficzne Schema.org** - bez duplikacji danych organizacji

### **Główny layout** (`resources/views/layouts/app.blade.php`)

```blade
<!-- SEO Meta Tags -->
{!! app('seotools')->generate() !!}
```

**Schema.org JSON-LD jest generowany na każdej stronie indywidualnie!**

### **Konfiguracja SEO** (`config/seotools.php`)

```php
'meta' => [
    'defaults' => [
        'title' => 'Zdrowe Herbaty BIFIX - Herbaty dla całej rodziny',
        'description' => 'Odkryj świat zdrowych herbat BIFIX...',
        'canonical' => 'current', // Automatycznie app('url')->current()
        'logo' => 'https://www.zdroweherbaty.com.pl/img/bifix-logo.png',
    ],
],

'opengraph' => [
    'defaults' => [
        'title' => 'Zdrowe Herbaty BIFIX',
        'description' => 'Odkryj świat zdrowych herbat BIFIX...',
        'type' => 'website',
        'url' => null, // Automatycznie app('url')->current()
        'site_name' => 'Zdrowe Herbaty BIFIX',
        'images' => ['https://www.zdroweherbaty.com.pl/img/bifix-logo.png'],
    ],
],

'json-ld' => [
    'defaults' => [
        'type' => 'WebSite', // Domyślny typ
        'title' => 'Zdrowe Herbaty BIFIX',
        'description' => 'Odkryj świat zdrowych herbat BIFIX...',
        'url' => 'current', // Automatycznie app('url')->current()
        'images' => ['https://www.zdroweherbaty.com.pl/img/bifix-logo.png'],
        'publisher' => [/* Dane organizacji */],
        'potentialAction' => [/* SearchAction - globalne */],
    ],
],
```

### **Strony Volt - Uproszczone**

```php
<?php
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\JsonLd;

// SEO Meta Tags - wszystko jest automatyczne z konfiguracji!

// Open Graph - wszystko jest automatyczne z konfiguracji!

// Schema.org - tylko typ (reszta z domyślnych)
JsonLd::setType('WebPage');

// Generowanie Schema.org na końcu strony
JsonLd::generate();
?>
```

**Wszystkie dane z konfiguracji są automatycznie używane!**

### **Nowa architektura Schema.org:**

-   🏢 **Layout**: generuje wszystkie Schema.org z konfiguracji (w tym Organization)
-   📄 **Strony**: tylko nadpisują typ i dodają specyficzne dane
-   ✅ **Brak duplikacji**: dane organizacji są w konfiguracji, nie na stronach

### **Co jest automatycznie ustawiane:**

-   ✅ **Meta Tags**: `title`, `description`, `keywords`, `logo`, `canonical`
-   ✅ **Open Graph**: `title`, `description`, `type`, `site_name`, `images`, `url`
-   ✅ **Twitter Cards**: `type`
-   ✅ **Schema.org**: `type`, `url`, `name`, `description`, `images`, `publisher` (Organization), `potentialAction` (SearchAction)

### **Co trzeba ustawić na każdej stronie:**

-   🔧 **Import fasad**: `use Artesaos\SEOTools\Facades\SEOTools; use Artesaos\SEOTools\Facades\JsonLd;`
-   🔧 **Schema.org typ**: `JsonLd::setType('WebPage')`
-   🔧 **Generowanie**: `{!! JsonLd::generate() !!}` na końcu strony
-   🔧 **Specyficzne dane**: tylko to co różni się od domyślnych

### **Co zostało usunięte (teraz automatyczne):**

-   ❌ **Canonical URL**: `app('seotools')->setCanonical(url()->current())`
-   ❌ **Open Graph URL**: `app('seotools')->opengraph()->setUrl(url()->current())`
-   ❌ **Schema.org URL**: `app('seotools.json-ld')->addValue('url', url()->current())`
-   ❌ **Schema.org podstawowe dane**: `name`, `description`, `potentialAction` (z konfiguracji)
-   ❌ **Ręczne dodawanie danych**: `addValue()` nie jest już potrzebne na większości stron

### **Jak to działa:**

1. **Layout** generuje `{!! app('seotools.json-ld')->generate() !!}`
2. **Konfiguracja** zawiera domyślny typ `WebSite`, URL `'current'` i dane organizacji w `publisher`
3. **Strony** nadpisują `type` przez `setType()` i dodają tylko specyficzne dane
4. **Rezultat**: jeden Schema.org z nadpisanym typem + automatycznym URL + danymi organizacji + specyficznymi danymi strony

**✅ `setType()` bezpiecznie nadpisuje domyślny typ z konfiguracji!**

### **Automatyczne URL-e:**

-   **JsonLd**: `'url' => 'current'` → automatycznie `app('url')->current()`
-   **OpenGraph**: `'url' => null` → automatycznie `app('url')->current()`
-   **Canonical**: `'canonical' => 'current'` → automatycznie `app('url')->current()`
-   **Strony**: nie muszą ręcznie ustawiać żadnych URL-i - są automatyczne!

### **Jak działa nadpisywanie:**

-   **Konfiguracja**: `'type' => 'WebSite'` (domyślny)
-   **Strona główna**: `setType('WebSite')` → pozostaje `WebSite`
-   **Strona produktu**: `setType('Product')` → nadpisuje na `Product`
-   **Strona grupy**: `setType('CollectionPage')` → nadpisuje na `CollectionPage`
-   **Dane organizacji**: zawsze zachowane z konfiguracji

### **Strony Livewire (dedykowane klasy)**

```php
<?php
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\JsonLd;

public function mount()
{
    // SEO Meta Tags
    SEOTools::setTitle('Tytuł strony');
    SEOTools::setDescription('Opis strony');

    // Schema.org
    JsonLd::setType('WebPage');
}
?>
```

## 📊 **Struktura Schema.org**

### **WebSite** (Strona główna, Wyszukiwarka)

```json
{
    "@type": "WebSite",
    "name": "Zdrowe Herbaty BIFIX",
    "description": "Herbaty dla całej rodziny",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "https://www.zdroweherbaty.com.pl/towary/szukaj?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
```

### **Product** (Strony produktów)

```json
{
    "@type": "Product",
    "name": "Nazwa herbaty",
    "description": "Opis produktu",
    "brand": "BIFIX",
    "category": "Kategoria",
    "offers": {
        "@type": "Offer",
        "price": 29.99,
        "priceCurrency": "PLN",
        "availability": "https://schema.org/InStock"
    }
}
```

### **CollectionPage** (Kategorie produktów)

```json
{
    "@type": "CollectionPage",
    "name": "Nazwa kategorii - Zdrowe Herbaty BIFIX",
    "description": "Przeglądaj herbaty z kategorii"
}
```

### **ContactPage** (Strona kontaktowa)

```json
{
    "@type": "ContactPage",
    "name": "Kontakt - Zdrowe Herbaty BIFIX",
    "description": "Skontaktuj się z nami w sprawie herbat BIFIX"
}
```

_Uwaga: Dane organizacji są dostępne globalnie na wszystkich stronach dzięki Schema.org w głównym layoucie._

### **Global Organization** (Wszystkie strony)

```json
{
    "@context": "http://schema.org",
    "@type": "Organization",
    "url": "https://www.zdroweherbaty.com.pl",
    "name": "BiFIX Wojciech Piasecki Sp.j.",
    "logo": "https://www.zdroweherbaty.com.pl/img/bifix-logo.png",
    "contactPoint": [
        {
            "@type": "ContactPoint",
            "telephone": "+48426144058",
            "contactType": "customer service"
        }
    ],
    "address": {
        "addressCountry": "Poland",
        "postalCode": "95-080",
        "addressRegion": "Górki Małe",
        "addressLocality": "ul. Dworska 33"
    }
}
```

## 🎨 **Dostosowanie**

### **1. Zmiana tytułów i opisów**

Edytuj odpowiednie pliki w `resources/views/livewire/pages/` lub klasy Livewire.

### **2. Dodanie nowych stron**

Skopiuj wzorzec z istniejących stron i dostosuj:

-   Meta tags
-   Open Graph
-   Twitter Cards
-   Schema.org JSON-LD

### **3. Konfiguracja globalna**

Edytuj `config/seotools.php` dla domyślnych wartości.

## 🚀 **Korzyści SEO**

-   ✅ **Lepsze pozycjonowanie** - kompletne meta tagi
-   ✅ **Social Media** - atrakcyjne podglądy na Facebook/Twitter
-   ✅ **Strukturalne dane** - rich snippets w Google
-   ✅ **UX** - lepsze opisy w wynikach wyszukiwania
-   ✅ **Mobile** - zoptymalizowane dla urządzeń mobilnych
-   ✅ **Kontrola indeksowania** - możliwość zabronienia indeksowania wybranych stron
-   ✅ **Efektywność** - brak duplikacji danych Schema.org
-   ✅ **Centralizacja** - dane organizacji w jednym miejscu
-   ✅ **Domyślna logika** - wykorzystuje standardowe mechanizmy pakietu
-   ✅ **Łatwość utrzymania** - zmiana danych w jednym pliku konfiguracyjnym
-   ✅ **Logo organizacji** - automatycznie dodawane do Open Graph i Schema.org
-   ✅ **Dynamiczne URL-e** - canonical i Open Graph URL ustawiane na aktualną stronę
-   ✅ **Poprawne SEO** - canonical URL wskazuje na właściwą stronę
-   ✅ **Centralizacja danych** - wspólne wartości w jednym miejscu
-   ✅ **Łatwość utrzymania** - zmiana marki w jednym pliku
-   ✅ **Automatyczne domyślne** - nie trzeba powtarzać wspólnych wartości
-   ✅ **Minimalny kod** - tylko specyficzne dla strony dane

## 🔍 **Testowanie**

### **1. Sprawdź meta tagi**

```bash
curl -s https://twoja-domena.pl | grep -i "meta\|title"
```

### **2. Sprawdź Schema.org**

-   Użyj [Google Rich Results Test](https://search.google.com/test/rich-results)
-   Sprawdź [Schema.org Validator](https://validator.schema.org/)

### **3. Sprawdź Open Graph**

-   [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
-   [Twitter Card Validator](https://cards-dev.twitter.com/validator)

## 📚 **Dokumentacja**

-   **Artesaos SEO Tools**: https://github.com/artesaos/seotools
-   **Schema.org**: https://schema.org/
-   **Open Graph**: https://ogp.me/
-   **Twitter Cards**: https://developer.twitter.com/en/docs/twitter-for-websites/cards

## 🎯 **Następne kroki**

1. **Dodaj obrazy Open Graph** dla lepszych social media
2. **Zaimplementuj Breadcrumbs** Schema.org
3. **Dodaj LocalBusiness** Schema.org dla kontaktów
4. **Zoptymalizuj dla Core Web Vitals**
5. **Dodaj sitemap.xml** z priorytetami SEO

---

**Implementacja zakończona pomyślnie! 🎉**

Wszystkie strony mają teraz kompletne SEO z meta tagami, Open Graph, Twitter Cards i Schema.org JSON-LD.

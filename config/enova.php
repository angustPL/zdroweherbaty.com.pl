<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nazwy Cech (Atrybutów) Enova
    |--------------------------------------------------------------------------
    |
    | Ten plik służy do przechowywania nazw cech (atrybutów) używanych
    | w bazie danych Enova. Pozwala to na łatwą konfigurację bez
    | potrzeby "hardkodowania" tych wartości w kodzie aplikacji.
    |
    */

    'features' => [
        // Nazwa cechy (atrybutu) przechowującej informację o grupie/kategorii produktu.
        'product_mark' => env('ENOVA_FEATURE_PRODUCT_MARK', 'www_sklep'),
        'product_group' => env('ENOVA_FEATURE_PRODUCT_GROUP', 'www_grupa'),
        'product_group_prefix' => env('ENOVA_FEATURE_PRODUCT_GROUP_PREFIX', '\\kategoria\\'),
        'product_name' => env('ENOVA_FEATURE_PRODUCT_NAME', 'www_nazwa'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfiguracja URL Grup
    |--------------------------------------------------------------------------
    |
    | Ustawienia związane z generowaniem URL dla grup produktów.
    |
    */

    'grupa_url_separator' => env('ENOVA_GRUPA_URL_SEPARATOR', '--'),

    'prices' => [
        'definition' => env('ENOVA_PRICE_DEFINITION', 3),
    ],
];

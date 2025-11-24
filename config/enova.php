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

    'delivery' => [
        'group_name' => env('ENOVA_DELIVERY_GROUP', 'www_dostawasklep'),
        'free_delivery_threshold' => env('ENOVA_DELIVERY_FREE_THRESHOLD', 80),
        'parcel_locker_name' => env('ENOVA_DELIVERY_PARCEL_LOCKER_NAME', 'Paczkomaty 24/7'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfiguracja Cache
    |--------------------------------------------------------------------------
    |
    | Ustawienia cache'owania danych z Enova.
    | TTL (Time To Live) w sekundach - domyślnie 24 godziny (86400 sekund).
    |
    */

    'cache' => [
        'ttl' => env('ENOVA_CACHE_TTL', 86400), // 24 godziny w sekundach
    ],

    'orders' => [
        'contractor' => env('ENOVA_ORDERS_CONTRACTOR', 'WWW'),
        'symbol' => env('ENOVA_ORDERS_SYMBOL', 'ZOW'),
        'warehouse' => env('ENOVA_ORDERS_WAREHOUSE', 'WWW'),
        'xml_directory' => env('ENOVA_ORDERS_XML_DIRECTORY', storage_path('app/enova/orders')),
        'xml_destination' => env('ENOVA_ORDERS_XML_DESTINATION', storage_path('app/enova/orders/sent')),
        'email' => [
            'address' => env('ENOVA_ORDERS_EMAIL_ADDRESS', 'sklep@bifix.pl'),
            'name' => env('ENOVA_ORDERS_EMAIL_NAME', 'Sklep Bifix'),
        ],
        'ftp' => [
            'host' => env('ENOVA_ORDERS_FTP_HOST', ''),
            'user' => env('ENOVA_ORDERS_FTP_USER', ''),
            'pass' => env('ENOVA_ORDERS_FTP_PASS', ''),
            'path' => env('ENOVA_ORDERS_FTP_PATH', '/'),
            'passive' => env('ENOVA_ORDERS_FTP_PASSIVE', true),
        ],
    ],

    'payment' => [
        'feature_payment_method' => env('ENOVA_PAYMENT_FEATURE_PAYMENT_METHOD', 'Forma Płatności dla dostawy'),
        'methods' => [
            'domyslna' => env('ENOVA_PAYMENT_DOMYSLNA_GUID', '00000000-0003-0004-0002-000000000000'),
            'gotowka' => env('ENOVA_PAYMENT_GOTOWKA_GUID', '00000000-0003-0004-0001-000000000000'),
            'przelew' => env('ENOVA_PAYMENT_PRZELEW_GUID', '00000000-0003-0004-0002-000000000000'),
            'przedplata' => env('ENOVA_PAYMENT_PRZEDPLATA_GUID', '43931EB3-6259-497E-8545-656187F90D3C'),
            'pobranie' => env('ENOVA_PAYMENT_POBRANIE_GUID', 'B13EDB83-341B-4FC6-AAE7-20CF19837650'),
            'payu' => env('ENOVA_PAYMENT_PAYU_GUID', 'B4413968-D8EA-4810-8DFF-7735D65A92AF'),
        ],
        'payu' => [
            'pos_id' => env('PAYU_POS_ID', '1718488'),
            'key' => env('PAYU_KEY', '7a966f844920c62468edf6047fe124cd'),
            'key2' => env('PAYU_KEY2', '420b4d6bd5bd46df20b051e01912f96d'),
            'pos_auth_key' => env('PAYU_POS_AUTH_KEY', 'DkM5pH2'),
            'url' => env('PAYU_URL', 'https://secure.payu.com/paygw/UTF/NewPayment'),
            'options' => [
                'blik' => env('PAYU_OPTION_BLIK', 'blik'),
                'card' => env('PAYU_OPTION_CARD', 'c'),
                'google_pay' => env('PAYU_OPTION_GOOGLE_PAY', 'ap'),
                'apple_pay' => env('PAYU_OPTION_APPLE_PAY', 'jp'),
                'transfer' => env('PAYU_OPTION_TRANSFER', 'p'), // 'p' dla przelewu bankowego w PayU REST API
            ],
            'logo_url' => env('PAYU_LOGO_URL', 'https://www.zdroweherbaty.com.pl/img/payu.png'),
            // Używamy env z fallbackiem do route() - route() automatycznie używa poprawnego URL
            // W .env można ustawić PAYU_CONTINUE_URL i PAYU_NOTIFY_URL dla pełnej kontroli
            'continue_url' => env('PAYU_CONTINUE_URL'),
            'notify_url' => env('PAYU_NOTIFY_URL'),
        ],
    ],
];

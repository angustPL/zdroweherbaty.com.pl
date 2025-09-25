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

    'payment' => [
        'feature_payment_method' => env('ENOVA_PAYMENT_FEATURE_PAYMENT_METHOD', 'Forma Płatności dla dostawy'),
        'methods' => [
            'domyslna' => env('ENOVA_PAYMENT_DOMYSLNA_GUID', '00000000-0003-0004-0002-000000000000'),
            'gotowka' => env('ENOVA_PAYMENT_GOTOWKA_GUID', '00000000-0003-0004-0001-000000000000'),
            'przelew' => env('ENOVA_PAYMENT_PRZELEW_GUID', '00000000-0003-0004-0002-000000000000'),
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
                'transfer' => env('PAYU_OPTION_TRANSFER', 'przelew'),
            ],
            'logo_url' => env('PAYU_LOGO_URL', 'https://www.zdroweherbaty.com.pl/img/payu.png'),
            'continue_url' => env('PAYU_CONTINUE_URL', 'https://www.zdroweherbaty.com.pl/payu/success/'),
            'notify_url' => env('PAYU_NOTIFY_URL', 'https://www.zdroweherbaty.com.pl/payu/notify/'),
        ],
    ],
];

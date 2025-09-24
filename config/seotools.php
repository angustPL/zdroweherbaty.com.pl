<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO Tools Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Artesaos SEO Tools package.
    | You can customize the default values for meta tags, Open Graph, and Twitter.
    |
    */

    'meta' => [
        'defaults' => [
            'title' => 'Zdrowe Herbaty BIFIX - Herbaty dla całej rodziny',
            'description' => 'Odkryj świat zdrowych herbat BIFIX. Szeroki wybór herbat zielonych, czarnych, owocowych i ziołowych dla całej rodziny.',
            'separator' => ' - ',
            'keywords' => ['herbata', 'zdrowe', 'bifix', 'herbaty zielone', 'herbaty czarne', 'herbaty owocowe', 'herbaty ziołowe'],
            'canonical' => 'current', // Automatycznie ustawia aktualny URL
            'logo' => 'https://www.zdroweherbaty.com.pl/img/bifix-logo.png',
        ],
        'webmaster_tags' => [
            'google' => null,
            'bing' => null,
            'alexa' => null,
            'pinterest' => null,
            'yandex' => null,
        ],
    ],

    'opengraph' => [
        'defaults' => [
            'title' => 'Zdrowe Herbaty BIFIX',
            'description' => 'Odkryj świat zdrowych herbat BIFIX. Szeroki wybór herbat zielonych, czarnych, owocowych i ziołowych dla całej rodziny.',
            'type' => 'website',
            'url' => null, // Automatycznie ustawia aktualny URL
            'site_name' => 'Zdrowe Herbaty BIFIX',
            'images' => [
                'https://www.zdroweherbaty.com.pl/img/bifix-logo.png'
            ],
        ],
    ],

    'twitter' => [
        'defaults' => [
            'type' => 'summary_large_image',
            'site' => null,
            'creator' => null,
        ],
    ],

    'json-ld' => [
        'defaults' => [
            'title' => 'Zdrowe Herbaty BIFIX',
            'description' => 'Odkryj świat zdrowych herbat BIFIX. Szeroki wybór herbat zielonych, czarnych, owocowych i ziołowych dla całej rodziny.',
            'type' => null, // Brak domyślnego typu - strony muszą ustawić swój
            'url' => 'current', // Automatycznie ustawia aktualny URL
            'images' => [
                'https://www.zdroweherbaty.com.pl/img/bifix-logo.png'
            ],
            // Globalne dane organizacji dostępne na wszystkich stronach
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'BiFIX Wojciech Piasecki Sp.j.',
                'url' => 'https://www.zdroweherbaty.com.pl',
                'logo' => 'https://www.zdroweherbaty.com.pl/img/bifix-logo.png',
                'contactPoint' => [
                    [
                        '@type' => 'ContactPoint',
                        'telephone' => '+48426144058',
                        'contactType' => 'customer service'
                    ]
                ],
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'Poland',
                    'postalCode' => '95-080',
                    'addressRegion' => 'Górki Małe',
                    'addressLocality' => 'ul. Dworska 33'
                ]
            ],
            // Globalne akcje dostępne na wszystkich stronach
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => 'https://www.zdroweherbaty.com.pl/towary/szukaj?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ],
    ],

    // Wspólne wartości używane na wszystkich stronach
    'common' => [
        'brand_name' => 'BIFIX',
        'site_name' => 'Zdrowe Herbaty BIFIX',
        'base_url' => 'https://www.zdroweherbaty.com.pl',
        'logo_url' => 'https://www.zdroweherbaty.com.pl/img/bifix-logo.png',
        'default_description' => 'Odkryj świat zdrowych herbat BIFIX. Szeroki wybór herbat zielonych, czarnych, owocowych i ziołowych dla całej rodziny.',
        'twitter_type' => 'summary_large_image',
        'opengraph_type' => 'website',
    ],
];

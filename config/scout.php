<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | You can change the default driver by setting the "driver" option.
    |
    */

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may set a prefix that will be applied to all search index
    | names used by Laravel Scout. This prefix may be useful if you
    | are using the same search index for several applications.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    |
    | This option allows you to control the maximum number of records that may
    | be synchronized for indexing operations using the "flush" command.
    |
    */

    'batch_size' => env('SCOUT_BATCH_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => env('SCOUT_CHUNK_SEARCHABLE', 500),
        'unsearchable' => env('SCOUT_CHUNK_UNSEARCHABLE', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records when
    | a force delete is performed via the "flush" command.
    |
    | Available Options: "trashed", "withTrashed", "withoutTrashed"
    |
    */

    'soft_delete' => env('SCOUT_SOFT_DELETE', false),

    /*
    |--------------------------------------------------------------------------
    | Identify
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether the Scout should "identify" the
    | user using the application's authentication system when indexing records.
    | This can be useful for "user-aware" search indexing.
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application id and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
        'search_key' => env('ALGOLIA_SEARCH', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scout Extended Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure Scout Extended specific settings.
    |
    */

    'extended' => [
        'driver' => env('SCOUT_EXTENDED_DRIVER', 'algolia'),
        'queue' => env('SCOUT_EXTENDED_QUEUE', false),
        'chunk' => [
            'searchable' => env('SCOUT_EXTENDED_CHUNK_SEARCHABLE', 500),
            'unsearchable' => env('SCOUT_EXTENDED_CHUNK_UNSEARCHABLE', 500),
        ],
    ],

];

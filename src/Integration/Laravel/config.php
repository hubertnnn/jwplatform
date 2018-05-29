<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JwPlatform credentials api keys
    |--------------------------------------------------------------------------
    |
    | Private key and site id are required by all api calls.
    | User id is required only if you are planning to create videos
    |
    */

    'credentials' => [
        'apiKey' => env('JWPLATFORM_API_KEY'),
        'secret' => env('JWPLATFORM_API_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | JwPlatform fallback template
    |--------------------------------------------------------------------------
    |
    | This template will be used on older browsers that dont support HLS
    |
    */
    'fallbackTemplate' => env('JWPLATFORM_FALLBACK_TEMPLATE'),
];

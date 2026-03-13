<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The booking website and any external integrations call /api/public/* and
    | /api/booking/* from a different origin. Those prefixes are wide-open by
    | design (with their own token protection for write endpoints).
    |
    | For the web/admin panel we restrict to same-origin only.
    |
    | Set CORS_ALLOWED_ORIGINS in .env to a comma-separated list of domains:
    |   CORS_ALLOWED_ORIGINS=https://yourbookingsite.com,https://www.yourbookingsite.com
    | Leave blank or set to * to allow all origins (fine for public read endpoints).
    |
    */

    'paths' => [
        'api/public/*',
        'api/booking/*',
        'api/login',
    ],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    // Parse from env: comma-separated list, or * for all
    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', '*'))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'Authorization',
        'X-Booking-Token',
        'X-Requested-With',
        'X-CSRF-TOKEN',
    ],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];

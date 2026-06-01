<?php

return [

    /*
  |--------------------------------------------------------------------------
  | Cross-Origin Resource Sharing (CORS) Configuration
  |--------------------------------------------------------------------------
  |
  | Here you may configure CORS settings for your application. As a multi-tenant
  | SaaS, REDIL Cloud needs to allow requests from tenant subdomains.
  |
  | Note: Update the allowed_origins_patterns when migrating servers.
  |
  */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        env('APP_URL', 'https://redil.ubicalo.com'),
    ],

    'allowed_origins_patterns' => [
        '/^https?:\/\/([a-zA-Z0-9\-]+)\.ubicalo\.com$/',
    ],

    'allowed_headers' => ['X-CSRF-TOKEN', 'Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

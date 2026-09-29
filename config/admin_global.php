<?php

return [
    'registration_domain' => env('CENTRAL_DOMAIN', 'redil.cloud'),
    'central_url' => env('REDIL_CENTRAL_URL', env('APP_URL', 'https://redil.cloud')),
    'reserved_subdomains' => ['www', 'admin', 'api', 'mail', 'smtp', 'support', 'soporte', 'status', 'staging', 'test', 'dev', 'app'],
    'invitation_days' => 7,
    'access_hours' => 48,
    'mfa_minutes' => 10,
    'session_hours' => 8,
];

<?php

use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Google Calendar Config (Dynamic)
|--------------------------------------------------------------------------
|
| This configuration file automatically generates the necessary JSON files
| (credentials and token) from your .env variables if they don't exist.
| This allows you to manage your secrets in .env while satisfying the
| spatie/laravel-google-calendar package requirements.
|
*/

// Helper to ensure directory exists
$ensureDir = function($path) {
    $dir = dirname($path);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
};

// 1. Handle Credentials File
$credentialsPath = storage_path('app/google-calendar/oauth-credentials.json');
// Only create if missing and we have the client ID in env
if (!file_exists($credentialsPath) && env('GOOGLE_CLIENT_ID')) {
    $ensureDir($credentialsPath);
    $credentials = [
        'web' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'project_id' => env('GOOGLE_PROJECT_ID', 'consejerias-redil'),
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uris' => [env('GOOGLE_REDIRECT_URI', 'https://developers.google.com/oauthplayground')],
        ]
    ];
    file_put_contents($credentialsPath, json_encode($credentials, JSON_PRETTY_PRINT));
}

// 2. Handle Token File
$tokenPath = storage_path('app/google-calendar/oauth-token.json');
// Only create if missing and we have the refresh token in env
if (!file_exists($tokenPath) && env('GOOGLE_REFRESH_TOKEN')) {
    $ensureDir($tokenPath);
    // Minimal token structure with refresh token to allow auto-refresh
    $token = [
        'access_token' => '', // Will be refreshed automatically
        'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),
        'scope' => 'https://www.googleapis.com/auth/calendar',
        'token_type' => 'Bearer',
        'created' => time() - 7200, // Hace 2 horas
        'expires_in' => 3599,
    ];
    file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));
}

return [

    'default_auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'oauth'),

    'auth_profiles' => [

        /*
         * Authenticate using a service account.
         */
        'service_account' => [
            /*
             * Path to the json file containing the credentials.
             */
            'credentials_json' => storage_path('app/google-calendar/service-account-credentials.json'),
        ],

        /*
         * Authenticate with actual google user account.
         */
        'oauth' => [
            /*
             * Path to the json file containing the oauth2 credentials.
             */
            'credentials_json' => $credentialsPath,

            /*
             * Path to the json file containing the oauth2 token.
             */
            'token_json' => $tokenPath,
        ],
    ],

    /*
     *  The id of the Google Calendar that will be used by default.
     */
    'calendar_id' => env('GOOGLE_CALENDAR_ID'),

     /*
     *  The email address of the user account to impersonate.
     */
    'user_to_impersonate' => env('GOOGLE_CALENDAR_IMPERSONATE'),
];

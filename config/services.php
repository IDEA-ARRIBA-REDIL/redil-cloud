<?php

return [

    /*
  |--------------------------------------------------------------------------
  | Third Party Services
  |--------------------------------------------------------------------------
  |
  | This file is for storing the credentials for third party services such
  | as Mailgun, Postmark, AWS and more. This file provides the de facto
  | location for this type of information, allowing packages to have
  | a conventional file to locate the various service credentials.
  |
  */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // config/services.php
    'zonapagos' => [
        'api_url' => env('ZONAPAGOS_API_URL'),
        'id_comercio' => env('ZONAPAGOS_ID_COMERCIO'),
        'usuario' => env('ZONAPAGOS_USUARIO'),
        'clave' => env('ZONAPAGOS_CLAVE'),
        'codigo_servicio' => env('ZONAPAGOS_CODIGO_SERVICIO'),
        'minutos_abandono_pasarela' => (int) env('ZONAPAGOS_MINUTOS_ABANDONO_PASARELA', 45),
        'minutos_abandono_borrador' => (int) env('ZONAPAGOS_MINUTOS_ABANDONO_BORRADOR', 30),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'landing' => [
        'whatsapp_number' => env('LANDING_WHATSAPP_NUMBER', '573000000000'),
        'contact_email' => env('LANDING_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'contacto@redilcloud.com')),
        'whatsapp_default_message' => env('LANDING_WHATSAPP_MESSAGE', 'Hola, quiero agendar una videollamada para conocer todas las ventajas de REDIL Cloud para mi iglesia.'),
    ],

];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS gateway
    |--------------------------------------------------------------------------
    |
    | Keep all environment reads in configuration files. This allows the
    | production deployment to use `php artisan config:cache` safely.
    |
    */
    'driver' => env('SMS_GATEWAY', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Development OTP preview
    |--------------------------------------------------------------------------
    |
    | This is deliberately disabled by default. It must only be enabled for a
    | non-production development environment using the log gateway, allowing
    | testers to see the current OTP on the verification screen without a real
    | SMS provider.
    |
    */
    'development_preview' => env('OTP_DEVELOPMENT_PREVIEW', false),

    'timeout' => (int) env('SMS_TIMEOUT_SECONDS', 10),

    'msegat' => [
        'username' => env('MSEGAT_USERNAME'),
        'password' => env('MSEGAT_PASSWORD'),
        'sender' => env('MSEGAT_SENDER', 'RamoStore'),
    ],

    'vonage' => [
        'key' => env('VONAGE_KEY'),
        'secret' => env('VONAGE_SECRET'),
        'from' => env('VONAGE_FROM', 'RamoStore'),
    ],
];

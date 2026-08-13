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

<?php

use App\Support\Telescope\ParameterizedQueryWatcher;
use Laravel\Telescope\Watchers\RequestWatcher;

return [
    'enabled' => env('TELESCOPE_ENABLED', false),
    'storage' => [
        'database' => [
            'connection' => 'telescope',
            'chunk' => 1000,
        ],
    ],
    'middleware' => ['web', 'admin.auth'],
    'ignore_paths' => ['_debugbar*'],
    'watchers' => [
        RequestWatcher::class => ['enabled' => true, 'size_limit' => 0],
        ParameterizedQueryWatcher::class => ['enabled' => true, 'slow' => 100],
    ],
];

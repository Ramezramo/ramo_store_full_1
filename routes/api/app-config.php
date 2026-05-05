<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppConfigController;

/*
|-----------------------------------------------------------------------
| App Config API
| Flutter app: GET /api/app-config?lang=en
|              GET /api/app-config/{key}?lang=en
|              GET /api/app-config/groups
|-----------------------------------------------------------------------
*/

Route::prefix('app-config')->group(function () {
    Route::get('/',        [AppConfigController::class, 'index']);
    Route::get('/groups',  [AppConfigController::class, 'groups']);
    Route::get('/{key}',   [AppConfigController::class, 'show']);
});

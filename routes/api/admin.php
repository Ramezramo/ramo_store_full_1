<?php

use App\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin.auth.api', \App\Http\Middleware\InvalidateStorefrontData::class])
    ->post('ramo/config-storing', [ConfigController::class, 'uploadConfig']);
<?php

use App\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->post('ramo/config-storing', [ConfigController::class, 'uploadConfig']);
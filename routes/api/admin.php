<?php

use App\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Route;

// Warning: This should be protected by proper admin middleware!
Route::post('ramo/config-storing', [ConfigController::class, 'uploadConfig']);
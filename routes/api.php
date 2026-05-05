<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => response()->json(['message' => 'API v3 - Running'], 200));

// Load modular route files
require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/user.php';
require __DIR__ . '/api/vendor.php';
require __DIR__ . '/api/coupons.php';
require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/cart.php';
require __DIR__ . '/api/app-config.php';




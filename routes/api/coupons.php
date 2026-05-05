<?php

use App\Http\Controllers\CouponController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'vendor.auth'])->prefix('ramo/coupons')->group(function () {
    Route::get('show', [CouponController::class, 'index']);
    Route::post('store', [CouponController::class, 'store'])->middleware('throttle:3,1');
    Route::get('get/{id}', [CouponController::class, 'show']);
    Route::put('update/{id}', [CouponController::class, 'update'])->middleware('throttle:3,1');
    Route::delete('remove/{id}', [CouponController::class, 'destroy'])->middleware('throttle:3,1');

    // Publicly usable by authenticated users (optional: move to user.php if needed)
    Route::post('/validate', [CouponController::class, 'validateCoupon']);
    Route::post('/apply', [CouponController::class, 'applyCoupon']);
});
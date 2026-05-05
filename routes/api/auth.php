<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ShopRegistrationController;
use Illuminate\Support\Facades\Route;

// Customer Authentication
Route::prefix('user')->group(function () {
    Route::post('send-otp-number', [AuthController::class, 'sendOtp'])->middleware('throttle:6,1');
    Route::post('register-with-phone', [AuthController::class, 'registerWithPhone'])->middleware('throttle:3,1');

    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:3,1');// ✅️
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:3,1');// ✅️
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');// ✅️
    Route::get('reset-password-page', [AuthController::class, 'showResetPasswordFormHTML'])
        ->name('password.reset.form');// ✅️
    Route::post('reset-password', [AuthController::class, 'recievingNewPassMod'])->middleware('throttle:3,1')->name('password.reset.getter');// ✅️
    Route::post('generateTokenTesting', [AuthController::class, 'generateTokenTesting'])->middleware('throttle:3,1');// ✅️
});

// Vendor (Shop) Registration & Login
Route::prefix('vendor')->group(function () {
    Route::post('register', [ShopRegistrationController::class, 'registerShopAndVendor'])->middleware('throttle:6,10');// ✅️
    Route::post('login', [ShopRegistrationController::class, 'login'])->middleware('throttle:10,10')->name('vendor.login');// ✅️
});
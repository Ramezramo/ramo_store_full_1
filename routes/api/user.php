<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\SippingController;
use App\Http\Controllers\UserNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('user')->group(function () {

    // Auth & Profile
    Route::get('me', [AuthController::class, 'me']);// ✅️
    Route::get('logout', [AuthController::class, 'logout']);// ✅️
    Route::get('refresh', [AuthController::class, 'refresh']);// ✅️
    Route::delete('delete-account', [AuthController::class, 'deleteAccount']);// ✅️

    // Shipping & Payment
    Route::get('shipping-methods', [SippingController::class, 'shippingMethods']);// ✅️
    Route::get('payment-methods', [SippingController::class, 'paymentMethods']);// ✅️

    // Orders
    Route::post('create-order', [OrdersController::class, 'createOrder'])->middleware('throttle:3,1');// ✅️
    Route::get('get-all-user-orders', [OrdersController::class, 'getAllUserOrders']);// ✅️

    // Notes
    Route::post('create-user-note', [UserNoteController::class, 'store']);// ✅️
    Route::get('get-order-notes', [UserNoteController::class, 'getAll']);// ✅️
});
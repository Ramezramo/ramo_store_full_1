<?php

use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\WishlistApiController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // ── Cart ──
    Route::prefix('cart')->group(function () {
        Route::get('/',        [CartApiController::class, 'index']);   // GET  /api/cart
        Route::post('/add',    [CartApiController::class, 'add'])->middleware('throttle:20,1');     // POST /api/cart/add
        Route::put('/{id}',    [CartApiController::class, 'update']);  // PUT  /api/cart/{id}
        Route::delete('/{id}', [CartApiController::class, 'remove']);  // DELETE /api/cart/{id}
        Route::delete('/',     [CartApiController::class, 'clear']);   // DELETE /api/cart
    });

    // ── Wishlist ──
    Route::prefix('wishlist')->group(function () {
        Route::get('/',         [WishlistApiController::class, 'index']);   // GET  /api/wishlist
        Route::post('/toggle',  [WishlistApiController::class, 'toggle']); // POST /api/wishlist/toggle
    });

    // ── Reviews (write) ──
    Route::post('reviews', [ReviewController::class, 'store']); // POST /api/reviews
});

// ── Reviews (read — public) ──
Route::get('reviews/{product_id}', [ReviewController::class, 'index']); // GET /api/reviews/{id}

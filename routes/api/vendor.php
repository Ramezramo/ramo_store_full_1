<?php

use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopRegistrationController;
use App\Http\Controllers\SimulationController1;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'vendor.auth'])->group(function () {

    // Products CRUD
    Route::prefix('vendor')->group(function () {
        // Vendor Profile
        Route::get('get-profile', [ShopRegistrationController::class, 'getVendor']); // ✅️
        Route::post('update-profile', [ShopRegistrationController::class, 'updateShop']); // ✅️

        // Update Order State (Vendor)
        Route::post('update-order-state', [OrdersController::class, 'updateOrderState'])
            ->middleware('throttle:3,1');
        Route::post('add-product', [ProductController::class, 'addNewProduct']); // ✅️
        Route::get('products', [ProductController::class, 'getproductsvendor']); // ✅️
        Route::get('view-product/{id}', [ProductController::class, 'viewOneProduct']); // ✅️
        Route::post('product-update/{id}', [ProductController::class, 'updateProduct']); // ✅️
        Route::post('product-images-update/{id}', [ProductController::class, 'updateProductImages']); // ✅️
        Route::post('product-image-oldimg-link-update/{id}', [ProductController::class, 'updateProductImageByOldLink']); // ✅️
        Route::delete('product-delete/{id}', [ProductController::class, 'deleteProduct']); // ✅️
        Route::post('product-variation-upd/{id}', [ProductController::class, 'updateVariation']); // ✅️
        // Route::post('product-images-colors-up/{id}', [ProductController::class, 'updateProductColorImages']);

        Route::get('popular-two-product', [ProductController::class, 'getPopularTwoProducts']); // ✅️
        // Route::get('get-product-review', [ProductController::class, 'productWiseReview']);
    });

    // Vendor Dashboard & Stats
    Route::prefix('vendor')->group(function () {// ✅️
        Route::get('refunds', [SimulationController1::class, 'refundSimulation']);
        Route::get('simulate-seller-info', [SimulationController1::class, 'sellerInfo']);
        Route::get('orders-seller-info', [SimulationController1::class, 'sellerOrdersInfo']);
        Route::get('order-statistics', [SimulationController1::class, 'orderStatistics']);
        Route::get('stock-out-list', [SimulationController1::class, 'stockoutlist']);
        Route::get('top-selling-product', [SimulationController1::class, 'topsellingproduct']);
        Route::get('all-category-cost', [SimulationController1::class, 'allCategoryCost']);
        Route::get('get-earning-statitics', [SimulationController1::class, 'getEarningStatitics']);
        Route::get('seller/notification', [SimulationController1::class, 'sellerNotification']);
        Route::get('top-delivery-man', [SimulationController1::class, 'topDeliveryMan']);
        Route::get('get-shipping-method', [SimulationController1::class, 'shippingMethod']);
        Route::put('language-change', [SimulationController1::class, 'languageChange']);
    })->middleware('throttle:10,1');
});

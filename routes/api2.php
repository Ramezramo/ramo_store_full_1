<?php

use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\CouponController;
// use App\Http\Controllers\Api\CouponController;
// use App\Http\Controllers\Api\CouponsController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CouponController;
// use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopRegistrationController;
use App\Http\Controllers\SimulationController1;
use App\Http\Controllers\SippingController;
use App\Http\Controllers\UserNoteController;
// use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return response()->json("you are good man v2", 200);
// });

// // User Authentication and Management
// Route::post('/user/register', [AuthController::class, 'register'])->middleware(['throttle:3,1']);// ✅️ ✔️
// Route::post('/user/login', [AuthController::class, 'login'])->middleware(['throttle:3,1']);// ✅️ ✔️
// Route::Get('/user/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');// ✅️ ✔️
// Route::Get('/user/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');// ✅️ ✔️
// Route::Get('/user/mee', [AuthController::class, 'me'])->middleware('auth:sanctum');// ✅️ ✔️
// Route::post('/user/forgot-password', [AuthController::class, 'forgotPassword'])->middleware(['throttle:3,1']);// ✅️ ✔️
// // Route::get('/user/reset-password-page', [AuthController::class, 'showResetPasswordFormHTML'])->name('password.reset.form');//✔️
// // Route::post('/user/reset-password', [AuthController::class, 'recievingNewPassMod'])->middleware(['throttle:3,1'])->name('password.reset.getter');// ✅️ ✔️
// Route::post('/user/generateTokenTesting', [AuthController::class, 'generateTokenTesting'])->middleware(['throttle:3,1']);// ✅️ ✔️


// Route::delete('/user/delete-account', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');// ✅️ ✔️



// Route::Get('/user/shipping-methods', [SippingController::class, 'shippingMethods'])->middleware('auth:sanctum');// ✅️ ✔️
// Route::Get('/user/payment-methods', [SippingController::class, 'paymentMethods'])->middleware('auth:sanctum');// ✅️ ✔️

// Route::post('/user/create-order', [OrdersController::class, 'storeOrder'])->middleware(['auth:sanctum','throttle:3,1']);// ✅️ ✔️
// Route::post('/vendor/update-order-state', [OrdersController::class, 'updateOrderState'])->middleware(['auth:sanctum', 'vendor.auth','throttle:3,1']);// ✅️✔️
// Route::prefix('/admin/coupons')->group(function () {
//     Route::get('/show', [CouponController::class, 'index'])->middleware('auth:sanctum'); // ✅️       // GET /api/coupons✔️
//     Route::post('/store', [CouponController::class, 'store'])->middleware(['auth:sanctum', 'vendor.auth','throttle:3,1']);  // ✅️  ✔️       // POST /api/coupons
//     Route::get('/get/{id}', [CouponController::class, 'show'])->middleware('auth:sanctum');    // ✅️    // GET /api/coupons/{id}
//     Route::put('/update/{id}', [CouponController::class, 'update'])->middleware(['auth:sanctum', 'vendor.auth','throttle:3,1']);  // ✅️ ✔️   // PUT /api/coupons/{id}
//     Route::delete('/remove/{id}', [CouponController::class, 'destroy'])->middleware(['auth:sanctum', 'vendor.auth','throttle:3,1']); // ✅️ ✔️// DELETE /api/coupons/{id}
    
//     // Special endpoints
//     Route::post('/ad/validate', [CouponController::class, 'validateCoupon'])->middleware('auth:sanctum'); // ✅️ ✔️// POST /api/coupons/validate
//     Route::post('/ad/apply', [CouponController::class, 'applyCoupon'])->middleware('auth:sanctum'); // ✅️  ✔️     // POST /api/coupons/apply
// });

// Route::Get('/user/get-all-user-orders', [OrdersController::class, 'getAllUserOrders'])->middleware('auth:sanctum');// ✅️✔️
// Route::post('/user/create-user-note', [UserNoteController::class, 'store'])->middleware('auth:sanctum');// ✅️✔️
// Route::Get('/user/get-order-notes', [UserNoteController::class, 'getAll'])->middleware('auth:sanctum');// ✅️✔️

// Route::post('/shop-a-nedor-registration', [ShopRegistrationController::class, 'registerShopAndVendor'])->middleware(['throttle:6,10']);// ✅️✔️
// Route::post('/shop-login', [ShopRegistrationController::class, 'login'])->middleware(['throttle:10,10'])->name('vendor.login');// ✅️✔️
// // Route::get('/get-shop', [ShopRegistrationController::class, 'getShopDetails'])->middleware('auth:sanctum');// ✅️✔️
// Route::get('/get-vendor', [ShopRegistrationController::class, 'getVendor'])->middleware('auth:sanctum');// ✅️✔️

// Route::post('/update-vendor', [ShopRegistrationController::class, 'updateShop'])->middleware('auth:sanctum');// ✅️✔️
// // Route::post('/update-seller', [ShopRegistrationController::class, 'updateSeller'])->middleware('auth:sanctum');



// Route::post('/vendor/add-product', [ProductController::class, 'addNewProduct'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);// ✅️✔️
// Route::get('/vendor/products', [ProductController::class, 'getproductsvendor'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);// ✅️✔️
// Route::get('/vendor/view-product/{id}', [ProductController::class, 'viewOneProduct'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);// ✅️✔️
// // Route::get('/product-images/{id}', [ProductController::class, 'imagesData'])->middleware('auth:sanctum');
// // Route::post('/update-product', [ProductController::class, 'updateProduct']);
// Route::post('/vendor/product-update/{id}', [ProductController::class, 'updateProduct'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);// ✅️✔️
// Route::post('/vendor/product-images-update/{id}', [ProductController::class, 'updateProductImages'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);//✔️
// Route::post('/vendor/product-image-oldimg-link-update/{id}', [ProductController::class, 'updateProductImageByOldLink'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);//✔️
// Route::delete('/vendor/product-delete/{id}', [ProductController::class, 'deleteProduct'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);//✅️✔️
// Route::post('/vendor/product-variation-upd/{id}', [ProductController::class, 'updateVariation'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);//✅️✔️
// Route::post('/vendor/product-images-colors-up/{id}', [ProductController::class, 'updateProductColorImages'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);//✅️✔️

// Route::get('/vendor/refunds', [SimulationController1::class, 'refundSimulation'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/simulate-seller-info', [SimulationController1::class, 'sellerInfo'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/orders-seller-info', [SimulationController1::class, 'sellerOrdersInfo'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);

// Route::get('/vendor/order-statistics', [SimulationController1::class, 'orderStatistics'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/most-popular-product', [ProductController::class, 'getPopularProducts'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/get-product-review', [ProductController::class, 'productWiseReview'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);

// Route::get('/vendor/stock-out-list', [SimulationController1::class, 'stockoutlist'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/top-selling-product', [SimulationController1::class, 'topsellingproduct'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/all-category-cost', [SimulationController1::class, 'allCategoryCost'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/get-earning-statitics', [SimulationController1::class, 'getEarningStatitics'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);

// // /api/v3/seller/notification
// Route::get('/vendor/seller/notification', [SimulationController1::class, 'sellerNotification'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);

// // /api/v3/seller/top-delivery-man
// Route::get('/vendor/top-delivery-man', [SimulationController1::class, 'topDeliveryMan'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::put('/vendor/language-change', [SimulationController1::class, 'languageChange'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);
// Route::get('/vendor/get-shipping-method', [SimulationController1::class, 'shippingMethod'])->middleware(['auth:sanctum', 'vendor.auth','throttle:10,1']);

// // configs management 
// Route::post('/admin/config-storing', [ConfigController::class, 'uploadConfig']);
// /api/v3/seller/shipping/all-category-cost
// Route::get('/seller/shipping/all-category-cost', [SimulationController1::class, 'allCategoryCost']);
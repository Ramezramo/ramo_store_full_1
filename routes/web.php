<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\Web\PolicyPageController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\WishlistController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Admin\ConfigAdminController;
use App\Http\Controllers\Admin\AdminTimelineController;
use App\Http\Controllers\Admin\AuthSettingsController;
use App\Http\Controllers\Admin\ShippingSettingsController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Web\VendorOrderController;
use App\Http\Controllers\Web\OrderMessageController;
use App\Http\Controllers\Web\RefundRequestController;
use App\Http\Controllers\Web\VendorRefundController;
use App\Http\Controllers\Web\OtpAuthController;
use App\Http\Controllers\Web\GoogleAuthController;
use App\Http\Controllers\Web\PaymentReceiptController;
use App\Http\Controllers\Web\PaymentReviewController;
use App\Http\Controllers\Admin\PaymentMethodsController;
use App\Http\Controllers\Admin\ImageGalleryController;

Route::get('/health', HealthController::class)->name('health');
Route::get('/', [WebController::class, 'home'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/privacy', [PolicyPageController::class, 'show'])->defaults('page', 'privacy')->name('policy.privacy');
Route::get('/terms', [PolicyPageController::class, 'show'])->defaults('page', 'terms')->name('policy.terms');
Route::get('/shipping-policy', [PolicyPageController::class, 'show'])->defaults('page', 'shipping-policy')->name('policy.shipping');
Route::get('/returns-policy', [PolicyPageController::class, 'show'])->defaults('page', 'returns-policy')->name('policy.returns');
Route::get('/contact', [PolicyPageController::class, 'show'])->defaults('page', 'contact')->name('policy.contact');
Route::get('/payment-info', [PolicyPageController::class, 'show'])->defaults('page', 'payment-info')->name('policy.payment');
Route::get('/language/{lang}', [WebController::class, 'setLocale'])->where('lang', '[A-Za-z-]+')->name('language.switch');
Route::get('/shop', [WebController::class, 'shop'])->name('shop');
Route::get('/offers', [WebController::class, 'offers'])->name('offers');
Route::get('/product/{id}', [WebController::class, 'product'])->name('product');
Route::get('/vendor/{id}', [WebController::class, 'vendor'])->name('vendor.store');

Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->middleware('throttle:cart-mutation')->name('cart.add');
Route::post('/cart/add-multiple', [CartController::class, 'addMultiple'])->middleware('throttle:cart-mutation')->name('cart.add-multiple');
Route::post('/cart/update/{rowId}', [CartController::class, 'update'])->middleware('throttle:cart-mutation')->name('cart.update');
Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove'])->middleware('throttle:cart-mutation')->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->middleware('throttle:cart-mutation')->name('cart.clear');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->middleware('throttle:coupon-check')->name('cart.coupon');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->middleware('throttle:coupon-check')->name('cart.coupon.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/place', [CheckoutController::class, 'place'])->middleware('throttle:checkout-place')->name('checkout.place');
Route::get('/order-success/{id}', [CheckoutController::class, 'success'])->name('order.success');

Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->middleware(['throttle:login-web', 'throttle:login-account']);
Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthWebController::class, 'register'])->middleware('throttle:referral-register');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
Route::post('/auth/send-otp', [OtpAuthController::class, 'sendOtp'])
    ->middleware(['throttle:otp-send-ip', 'throttle:otp-send-phone'])
    ->name('auth.send-otp');
Route::post('/auth/verify-otp', [OtpAuthController::class, 'verifyOtp'])
    ->middleware(['throttle:otp-verify-ip', 'throttle:otp-verify-phone'])
    ->name('auth.verify-otp');
Route::get('/auth/otp-verify', fn() => view('web.auth.otp-verify'))->name('auth.otp-verify');
Route::get('/auth/complete-profile', [OtpAuthController::class, 'showCompleteProfile'])->name('auth.complete-profile');
Route::post('/auth/complete-profile', [OtpAuthController::class, 'completeProfile'])->middleware('throttle:referral-register')->name('auth.complete-profile.post');
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

use App\Http\Controllers\Web\PasswordResetController;
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:password-forgot')->name('password.forgot.send');
Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:password-reset')->name('password.reset');

use App\Http\Controllers\Web\GuestOrderController;
Route::get('/my-order', [GuestOrderController::class, 'index'])->name('guest.order');
Route::post('/my-order', [GuestOrderController::class, 'lookup'])->middleware('throttle:order-lookup')->name('guest.order.lookup');
Route::post('/my-order/{id}/payment-receipt', [PaymentReceiptController::class, 'uploadForGuest'])
    ->middleware('throttle:order-lookup')
    ->name('guest.order.payment-receipt');

use App\Http\Controllers\Web\EmailVerificationController;
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('email.verify.notice');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('email.verify.resend');
Route::get('/email/verify/confirm', [EmailVerificationController::class, 'verify'])->name('email.verify');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::get('/wishlist/state', [WishlistController::class, 'state'])->name('wishlist.state');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::delete('/wishlist/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');

Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('/', [AccountController::class, 'hub'])->name('account.hub');
    Route::get('/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::get('/referral', [AccountController::class, 'referral'])->name('account.referral');
    Route::post('/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/orders/{id}', [AccountController::class, 'orderDetail'])->name('account.order');
    Route::post('/orders/{id}/messages', [OrderMessageController::class, 'store'])->name('account.order.messages.store');
    Route::post('/orders/{id}/payment-receipt', [PaymentReceiptController::class, 'uploadForAccount'])->name('account.order.payment-receipt');
    Route::get('/reviews', [AccountController::class, 'reviews'])->name('account.reviews');
    Route::get('/refunds', [RefundRequestController::class, 'index'])->name('account.refunds');
    Route::get('/refunds/create', [RefundRequestController::class, 'create'])->name('account.refunds.create');
    Route::post('/refunds', [RefundRequestController::class, 'store'])->name('account.refunds.store');
    Route::get('/refunds/{id}', [RefundRequestController::class, 'show'])->name('account.refunds.show');
    Route::patch('/refunds/{id}/cancel', [RefundRequestController::class, 'cancel'])->name('account.refunds.cancel');
});

Route::middleware('auth')->post('/reviews', [ReviewController::class, 'webStore'])->name('review.store');
Route::middleware('auth')->delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('review.destroy');
Route::post('/reviews/{id}/helpful', [ReviewController::class, 'helpful'])->name('review.helpful');
Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:search')->name('search');

use App\Http\Controllers\Web\OrderTrackingController;
Route::get('/track', [OrderTrackingController::class, 'index'])->name('order.track');
Route::post('/track', [OrderTrackingController::class, 'track'])->middleware('throttle:order-lookup')->name('order.track.submit');

use App\Http\Controllers\Web\VendorWebController;
use App\Http\Controllers\Web\VendorProductController;
use App\Http\Controllers\Web\CategoryBrandRequestController;
Route::get('/become-a-vendor', [VendorWebController::class, 'showRegister'])->name('vendor.register');
Route::post('/become-a-vendor', [VendorWebController::class, 'register'])->name('vendor.register.submit');
Route::get('/vendor-login', [VendorWebController::class, 'showLogin'])->name('vendor.login');
Route::post('/vendor-login', [VendorWebController::class, 'login'])->name('vendor.login.submit');
Route::post('/vendor-logout', [VendorWebController::class, 'logout'])->name('vendor.logout');
Route::middleware('vendor.web.auth')->group(function () {
    Route::get('/seller/dashboard', [VendorWebController::class, 'dashboard'])->name('vendor.dashboard');
    Route::get('/seller/store-profile', [VendorWebController::class, 'showStoreProfile'])->name('vendor.store.profile');
    Route::post('/seller/store-profile', [VendorWebController::class, 'updateStoreProfile'])->name('vendor.store.profile.update');
    Route::get('/seller/orders', [VendorOrderController::class, 'index'])->name('vendor.orders');
    Route::get('/seller/orders/{id}', [VendorOrderController::class, 'show'])->name('vendor.orders.show');
    Route::post('/seller/orders/{id}/status', [VendorOrderController::class, 'updateStatus'])->name('vendor.orders.status');
    Route::post('/seller/orders/{id}/reply', [VendorOrderController::class, 'reply'])->name('vendor.orders.reply');
    Route::post('/seller/orders/{id}/payment-review', [PaymentReviewController::class, 'reviewAsVendor'])->name('vendor.orders.payment-review');
    Route::get('/seller/refunds', [VendorRefundController::class, 'index'])->name('vendor.refunds');
    Route::get('/seller/refunds/{id}', [VendorRefundController::class, 'show'])->name('vendor.refunds.show');
    Route::get('/seller/products', [VendorProductController::class, 'index'])->name('vendor.products');
    Route::get('/seller/products/create', [VendorProductController::class, 'create'])->name('vendor.products.create');
    Route::get('/seller/products/search-json', [VendorProductController::class, 'searchJson'])->name('vendor.products.search');
    Route::post('/seller/products/bulk-price', [VendorProductController::class, 'bulkPrice'])->name('vendor.products.bulk-price');
    Route::post('/seller/products', [VendorProductController::class, 'store'])->name('vendor.products.store');
    Route::get('/seller/products/{id}', [VendorProductController::class, 'show'])->name('vendor.products.show');
    Route::get('/seller/products/{id}/edit', [VendorProductController::class, 'edit'])->name('vendor.products.edit');
    Route::post('/seller/products/{id}', [VendorProductController::class, 'update'])->name('vendor.products.update');
    Route::post('/seller/products/{id}/section', [VendorProductController::class, 'updateSection'])->name('vendor.products.update-section');
    Route::delete('/seller/products/{id}', [VendorProductController::class, 'destroy'])->name('vendor.products.destroy');
    Route::get('/seller/requests', [CategoryBrandRequestController::class, 'index'])->name('vendor.requests');
    Route::get('/seller/requests/create', [CategoryBrandRequestController::class, 'create'])->name('vendor.requests.create');
    Route::post('/seller/requests', [CategoryBrandRequestController::class, 'store'])->name('vendor.requests.store');
});

Route::get('/admin/login', [AuthWebController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthWebController::class, 'adminLogin'])->name('admin.login.post');
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCategoryBrandController;
use App\Http\Controllers\Admin\ReferralAdminController;
Route::prefix('admin')->middleware(['auth', 'admin.auth', \App\Http\Middleware\RefreshAdminSidebarCounts::class])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('admin.users');
    Route::patch('/users/{id}/block', [AdminDashboardController::class, 'blockUser'])->name('admin.users.block');
    Route::patch('/users/{id}/unblock', [AdminDashboardController::class, 'unblockUser'])->name('admin.users.unblock');
    Route::patch('/users/{id}/role', [AdminDashboardController::class, 'updateUserRole'])->name('admin.users.role');
    Route::delete('/users/{id}', [AdminDashboardController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/orders', [AdminDashboardController::class, 'orders'])->name('admin.orders');
    Route::get('/orders/{id}', [AdminDashboardController::class, 'orderDetail'])->name('admin.orders.detail');
    Route::patch('/orders/{id}/status', [AdminDashboardController::class, 'updateOrderStatus'])->name('admin.orders.status');
     Route::patch('/orders/{id}/force-override', [AdminDashboardController::class, 'forceOverrideOrderStatus'])->name('admin.orders.force-override');
     Route::delete('/orders/{id}/force-override', [AdminDashboardController::class, 'clearForceOverride'])->name('admin.orders.force-override.clear');
     Route::patch('/orders/{orderId}/sub-orders/{subOrderId}/status', [AdminDashboardController::class, 'updateSubOrderStatus'])->name('admin.orders.sub-orders.status');
    Route::get('/vendors', [AdminDashboardController::class, 'vendors'])->name('admin.vendors');
    Route::get('/vendors/{id}', [AdminDashboardController::class, 'vendorShow'])->name('admin.vendors.show');
    Route::patch('/vendors/{id}/approve', [AdminDashboardController::class, 'approveVendor'])->name('admin.vendors.approve');
    Route::patch('/vendors/{id}/block', [AdminDashboardController::class, 'blockVendor'])->name('admin.vendors.block');
    Route::patch('/vendors/{id}/reject', [AdminDashboardController::class, 'rejectVendor'])->name('admin.vendors.reject');
    Route::delete('/vendors/{id}', [AdminDashboardController::class, 'deleteVendor'])->name('admin.vendors.delete');
    Route::get('/products', [AdminDashboardController::class, 'products'])->name('admin.products');
    Route::post('/products/bulk', [AdminDashboardController::class, 'bulkProducts'])->name('admin.products.bulk');
    Route::get('/products/{id}', [AdminDashboardController::class, 'showProduct'])->name('admin.products.show');
    Route::post('/products/{id}/section', [AdminDashboardController::class, 'adminUpdateProductSection'])->name('admin.products.section');
    Route::patch('/products/{id}/approve', [AdminDashboardController::class, 'approveProduct'])->name('admin.products.approve');
    Route::patch('/products/{id}/reject', [AdminDashboardController::class, 'rejectProduct'])->name('admin.products.reject');
    Route::patch('/products/{id}/toggle', [AdminDashboardController::class, 'toggleProductStatus'])->name('admin.products.toggle');
    Route::delete('/products/{id}', [AdminDashboardController::class, 'deleteProduct'])->name('admin.products.delete');
    Route::get('/devices', [AdminDashboardController::class, 'devices'])->name('admin.devices');
    Route::patch('/devices/{id}/block', [AdminDashboardController::class, 'blockDevice'])->name('admin.devices.block');
    Route::patch('/devices/{id}/unblock', [AdminDashboardController::class, 'unblockDevice'])->name('admin.devices.unblock');
    Route::delete('/devices/{id}', [AdminDashboardController::class, 'deleteDevice'])->name('admin.devices.delete');
    Route::post('/devices/block-by-id', [AdminDashboardController::class, 'blockDeviceByDeviceId'])->name('admin.devices.block-by-id');
    Route::get('/timeline', [AdminTimelineController::class, 'index'])->name('admin.timeline');
    Route::get('/image-gallery', [ImageGalleryController::class, 'index'])->name('admin.image-gallery');
    Route::post('/image-gallery', [ImageGalleryController::class, 'store'])->name('admin.image-gallery.store');
    Route::delete('/image-gallery/{image}', [ImageGalleryController::class, 'destroy'])->name('admin.image-gallery.destroy');
    Route::get('/live-preview', [AdminTimelineController::class, 'livePreview'])->name('admin.live.preview');
    Route::post('/timeline/save', [AdminTimelineController::class, 'save'])->name('admin.timeline.save');
    Route::get('/products/search', [AdminTimelineController::class, 'searchProducts'])->name('admin.products.search');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('admin.analytics');
    Route::get('/coupons', [AdminDashboardController::class, 'coupons'])->name('admin.coupons');
    Route::post('/coupons', [AdminDashboardController::class, 'createCoupon'])->name('admin.coupons.create');
    Route::patch('/coupons/{id}', [AdminDashboardController::class, 'updateCoupon'])->name('admin.coupons.update');
    Route::patch('/coupons/{id}/toggle', [AdminDashboardController::class, 'toggleCoupon'])->name('admin.coupons.toggle');
    Route::delete('/coupons/{id}', [AdminDashboardController::class, 'deleteCoupon'])->name('admin.coupons.delete');
    Route::get('/refunds', [AdminDashboardController::class, 'refunds'])->name('admin.refunds');
    Route::get('/refunds/{id}', [AdminDashboardController::class, 'showRefund'])->name('admin.refunds.show');
    Route::patch('/refunds/{id}', [AdminDashboardController::class, 'updateRefund'])->name('admin.refunds.update');
    Route::get('/reviews', [AdminDashboardController::class, 'reviews'])->name('admin.reviews');
    Route::patch('/reviews/{id}/toggle', [AdminDashboardController::class, 'toggleReview'])->name('admin.reviews.toggle');
    Route::delete('/reviews/{id}', [AdminDashboardController::class, 'deleteReview'])->name('admin.reviews.delete');
    Route::get('/configs', [ConfigAdminController::class, 'index'])->name('admin.configs');
    Route::put('/configs/{id}', [ConfigAdminController::class, 'update'])->name('admin.configs.update');
    Route::post('/configs', [ConfigAdminController::class, 'create'])->name('admin.configs.create');
    Route::post('/configs/shop-mobile-layout', [ConfigAdminController::class, 'updateShopMobileLayout'])->name('admin.configs.shop-mobile-layout');
    Route::delete('/configs/{id}', [ConfigAdminController::class, 'destroy'])->name('admin.configs.destroy');
    Route::get('/auth-settings', [AuthSettingsController::class, 'index'])->name('admin.auth-settings');
    Route::put('/auth-settings', [AuthSettingsController::class, 'update'])->name('admin.auth-settings.update');
    Route::get('/shipping-settings', [ShippingSettingsController::class, 'index'])->name('admin.shipping-settings');
    Route::put('/shipping-settings', [ShippingSettingsController::class, 'update'])->name('admin.shipping-settings.update');
    Route::get('/payment-methods', [PaymentMethodsController::class, 'index'])->name('admin.payment-methods');
    Route::put('/payment-methods', [PaymentMethodsController::class, 'update'])->name('admin.payment-methods.update');
    Route::get('/referral', [ReferralAdminController::class, 'index'])->name('admin.referral.index');
    Route::put('/referral/settings', [ReferralAdminController::class, 'updateSettings'])->name('admin.referral.settings.update');
    Route::post('/referral', [ReferralAdminController::class, 'store'])->name('admin.referral.store');
    Route::patch('/referral/{referral}', [ReferralAdminController::class, 'update'])->name('admin.referral.update');
    Route::delete('/referral/{referral}', [ReferralAdminController::class, 'destroy'])->name('admin.referral.destroy');
    Route::patch('/referral/commissions/{commission}/approve', [ReferralAdminController::class, 'approveCommission'])->name('admin.referral.commissions.approve');
    Route::patch('/referral/commissions/{commission}/reject', [ReferralAdminController::class, 'rejectCommission'])->name('admin.referral.commissions.reject');
    Route::patch('/referral/commissions/{commission}', [ReferralAdminController::class, 'updateCommission'])->name('admin.referral.commissions.update');
    Route::post('/orders/{id}/payment-review', [PaymentReviewController::class, 'reviewAsAdmin'])->name('admin.orders.payment-review');
    Route::get('/category-brand-requests', [AdminCategoryBrandController::class, 'index'])->name('admin.cbr');
    Route::patch('/category-brand-requests/{id}/approve', [AdminCategoryBrandController::class, 'approve'])->name('admin.cbr.approve');
    Route::patch('/category-brand-requests/{id}/reject', [AdminCategoryBrandController::class, 'reject'])->name('admin.cbr.reject');
    // Category CRUD
    Route::post('/categories', [AdminCategoryBrandController::class, 'storeCategory'])->name('admin.categories.store');
    Route::patch('/categories/{id}', [AdminCategoryBrandController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminCategoryBrandController::class, 'destroyCategory'])->name('admin.categories.destroy');
    // Brand CRUD
    Route::post('/brands', [AdminCategoryBrandController::class, 'storeBrand'])->name('admin.brands.store');
    Route::patch('/brands/{id}', [AdminCategoryBrandController::class, 'updateBrand'])->name('admin.brands.update');
    Route::delete('/brands/{id}', [AdminCategoryBrandController::class, 'destroyBrand'])->name('admin.brands.destroy');
});
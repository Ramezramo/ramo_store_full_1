<?php

namespace App\Providers;

use App\Http\Controllers\CouponController;
use App\Http\Controllers\ProductController;
use App\Http\View\Composers\AdminSidebarComposer;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CouponController::class, function ($app) {
            return new CouponController;
        });
        $this->app->singleton(ProductController::class, function ($app) {
            return new ProductController;
        });
    //    $this->app->singleton(ImageManager::class, function () {
    //         return new ImageManager(new Driver());
    //     });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Record counts shown as badges in the admin sidebar.
        View::composer('admin.layout', AdminSidebarComposer::class);

        RateLimiter::for('otp-send-ip', fn (Request $request) => Limit::perMinute(5)
            ->by('otp.send.ip.' . $request->ip()));

        RateLimiter::for('otp-send-phone', fn (Request $request) => Limit::perHour(6)
            ->by('otp.send.phone.' . self::otpPhoneKey($request)));

        RateLimiter::for('otp-verify-ip', fn (Request $request) => Limit::perMinute(10)
            ->by('otp.verify.ip.' . $request->ip()));

        RateLimiter::for('otp-verify-phone', fn (Request $request) => Limit::perMinute(10)
            ->by('otp.verify.phone.' . self::otpPhoneKey($request)));

        RateLimiter::for('login-web', fn (Request $request) => Limit::perMinute(5)
            ->by('login.web.' . $request->ip() . '.' . hash('sha256', strtolower((string) $request->input('email', '')))));

        RateLimiter::for('referral-register', fn (Request $request) => Limit::perHour(5)
            ->by('referral.register.' . $request->ip() . '.' . hash('sha256', strtolower((string) $request->cookie('ref_code', $request->query('ref', ''))))));

        RateLimiter::for('cart-mutation', fn (Request $request) => Limit::perMinute(40)
            ->by('cart.mutation.' . self::customerOrIpKey($request)));

        RateLimiter::for('coupon-check', fn (Request $request) => Limit::perMinute(10)
            ->by('coupon.check.' . self::customerOrIpKey($request)));

        RateLimiter::for('checkout-place', fn (Request $request) => Limit::perMinute(6)
            ->by('checkout.place.' . self::customerOrIpKey($request)));

        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(90)
            ->by('search.' . $request->ip()));

        RateLimiter::for('order-lookup', fn (Request $request) => Limit::perMinute(6)
            ->by('order.lookup.' . $request->ip()));

    //    if (!class_exists('Image')) {
    //         class_alias(\Intervention\Image\Facades\Image::class, 'Image');
    //     }
    }

    private static function customerOrIpKey(Request $request): string
    {
        return $request->user()
            ? 'user.' . $request->user()->getAuthIdentifier()
            : 'ip.' . $request->ip();
    }

    private static function otpPhoneKey(Request $request): string
    {
        $phone = preg_replace('/\s+/', '', (string) $request->input('phone', '')) ?? '';

        if (str_starts_with($phone, '0')) {
            $phone = '+2' . $phone;
        }

        if ($phone !== '' && !str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return hash('sha256', $phone);
    }
}

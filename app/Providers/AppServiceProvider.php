<?php

namespace App\Providers;

use App\Http\Controllers\CouponController;
use App\Http\Controllers\ProductController;
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
    //    if (!class_exists('Image')) {
    //         class_alias(\Intervention\Image\Facades\Image::class, 'Image');
    //     }
    }
}

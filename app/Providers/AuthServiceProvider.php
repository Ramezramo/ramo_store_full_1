<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\RefundRequest;
use App\Models\SubOrder;
use App\Policies\CartItemPolicy;
use App\Policies\CouponPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductReviewPolicy;
use App\Policies\RefundRequestPolicy;
use App\Policies\SubOrderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CartItem::class => CartItemPolicy::class,
        Coupon::class => CouponPolicy::class,
        Order::class => OrderPolicy::class,
        ProductReview::class => ProductReviewPolicy::class,
        RefundRequest::class => RefundRequestPolicy::class,
        SubOrder::class => SubOrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}

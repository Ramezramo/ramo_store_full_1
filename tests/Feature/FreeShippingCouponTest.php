<?php

namespace Tests\Feature;

use App\Helpers\ShippingConfig;
use App\Models\User;
use Database\Seeders\FreeShippingCouponsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FreeShippingCouponTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_saved_coupon_is_refreshed_from_database(): void
    {
        $suffix = strtoupper(substr(sha1(uniqid('', true)), 0, 10));
        $code = 'REFRESH'.$suffix;
        $couponId = DB::table('coupons')->insertGetId([
            'code' => $code,
            'amount' => 25,
            'discount_type' => 'percent',
            'status' => 'publish',
            'usage_count' => 0,
            'usage_limit_per_user' => null,
            'minimum_amount' => 0,
            'maximum_amount' => 0,
            'date_created' => now(),
            'date_created_gmt' => now(),
            'date_modified' => now(),
            'date_modified_gmt' => now(),
            'free_shipping' => true,
            'description' => 'Refreshed coupon test',
            'meta_data' => '[]',
        ]);

        try {
            $this->withSession([
                'ramo_coupon' => [
                    'code' => strtolower($code),
                    'amount' => 0,
                    'discount_type' => 'fixed_cart',
                    'free_shipping' => false,
                ],
            ]);

            $controller = app(\App\Http\Controllers\Web\CartController::class);
            $method = new \ReflectionMethod($controller, 'appliedCoupon');
            $method->setAccessible(true);
            $refreshed = $method->invoke($controller);

            $this->assertSame($code, $refreshed['code']);
            $this->assertSame(25.0, (float) $refreshed['amount']);
            $this->assertSame('percent', $refreshed['discount_type']);
            $this->assertTrue($refreshed['free_shipping']);
            $this->assertSame(25.0, (float) session('ramo_coupon.amount'));
        } finally {
            DB::table('coupons')->where('id', $couponId)->delete();
        }
    }

    public function test_free_shipping_seeder_is_idempotent_and_offers_page_shows_expiry(): void
    {
        $codes = ['AC46264BD', 'BK13H16D'];
        DB::table('coupons')->whereIn('code', $codes)->delete();

        try {
            $this->seed(FreeShippingCouponsSeeder::class);
            $this->seed(FreeShippingCouponsSeeder::class);

            $this->assertSame(1, DB::table('coupons')->where('code', 'AC46264BD')->count());
            $this->assertSame(1, DB::table('coupons')->where('code', 'BK13H16D')->count());
            $this->assertDatabaseHas('coupons', [
                'code' => 'AC46264BD',
                'free_shipping' => true,
                'minimum_amount' => 700,
                'date_expires' => '2026-08-31 23:59:59',
            ]);

            $response = $this->withSession(['locale' => 'ar'])->get(route('offers'));
            $response->assertOk()
                ->assertSee('توصيل مجاني')
                ->assertSee('700 EGP')
                ->assertSee('31/08/2026')
                ->assertSee('AC46264BD');
        } finally {
            DB::table('coupons')->whereIn('code', $codes)->delete();
        }
    }

    public function test_free_shipping_coupon_removes_shipping_from_checkout_and_order(): void
    {
        $originalShippingConfig = ShippingConfig::get();
        $user = null;
        $productId = null;
        $variationId = null;
        $couponId = null;
        $orderId = null;
        $idempotencyKey = (string) Str::uuid();
        $suffix = uniqid('free-shipping-', true);
        $couponCode = 'FREE'.strtoupper(substr(str_replace('.', '', $suffix), -8));

        try {
            ShippingConfig::save([
                'standard_shipping_fee' => 100,
                'free_shipping_enabled' => false,
                'cod_fee' => 40,
            ]);

            $user = User::create([
                'name' => 'Free Shipping Tester',
                'email' => 'free-shipping-'.str_replace('.', '-', $suffix).'@ramostore.local',
                'password' => 'temporary-test-password',
                'phone' => '01000000005',
            ]);
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'Free shipping test product '.$suffix,
                'slug' => 'free-shipping-'.str_replace('.', '-', $suffix),
                'search_text' => 'Free shipping test product '.$suffix,
                'status' => 'publish',
                'acceptance_status' => 'approved',
                'purchasable' => true,
                'manage_stock' => true,
                'minimum_order_qty' => 1,
                'max_orders_per_person' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $variationId = DB::table('product_variations')->insertGetId([
                'product_id' => $productId,
                'main_variation' => true,
                'status' => 'publish',
                'stock_status' => 'instock',
                'attributes' => '{}',
                'price' => 100,
                'regular_price' => 100,
                'sale_price' => null,
                'stock_quantity' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('cart_items')->insert([
                'user_id' => $user->id,
                'product_id' => $productId,
                'variation_id' => $variationId,
                'qty' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $couponId = DB::table('coupons')->insertGetId([
                'code' => $couponCode,
                'vendor_id' => null,
                'amount' => 0,
                'discount_type' => 'fixed_cart',
                'status' => 'publish',
                'usage_count' => 0,
                'usage_limit' => null,
                'usage_limit_per_user' => null,
                'limit_usage_to_x_items' => null,
                'minimum_amount' => 100,
                'maximum_amount' => 0,
                'date_expires' => now()->addDay(),
                'date_created' => $now,
                'date_created_gmt' => $now,
                'date_modified' => $now,
                'date_modified_gmt' => $now,
                'individual_use' => false,
                'free_shipping' => true,
                'exclude_sale_items' => false,
                'product_ids' => '[]',
                'excluded_product_ids' => '[]',
                'product_categories' => '[]',
                'excluded_product_categories' => '[]',
                'email_restrictions' => '[]',
                'description' => 'Test free shipping coupon',
                'meta_data' => '[]',
            ]);

            $applyResponse = $this->actingAs($user)->postJson(route('cart.coupon'), [
                'code' => $couponCode,
            ]);
            $applyResponse->assertOk()->assertJson(['success' => true, 'reload' => true]);
            $this->assertTrue((bool) session('ramo_coupon.free_shipping'));

            $cartPage = $this->actingAs($user)->withSession(['locale' => 'ar'])->get(route('cart'));
            $cartPage->assertOk()
                ->assertSee('توصيل مجاني')
                ->assertDontSee('−0.00 EGP');

            $checkoutPage = $this->actingAs($user)->get(route('checkout'));
            $checkoutPage->assertOk();
            $this->assertSame(0.0, (float) $checkoutPage->viewData('shippingFee'));

            $response = $this->actingAs($user)->post(route('checkout.place'), [
                'first_name' => 'Free',
                'last_name' => 'Shipping',
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => 'Free Shipping Test Street',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'address_note' => '',
                'payment_method' => 'cod',
                'notes' => '',
                'idempotency_key' => $idempotencyKey,
            ]);

            $response->assertRedirect();
            $orderId = (int) DB::table('idempotency_keys')->where('key', $idempotencyKey)->value('order_id');
            $this->assertGreaterThan(0, $orderId);
            $order = DB::table('orders')->where('id', $orderId)->first();
            $this->assertSame('0.00', number_format((float) $order->shipping_total, 2, '.', ''));
            $this->assertSame('100.00', number_format((float) $order->final_total, 2, '.', ''));
            $this->assertSame([], json_decode($order->fee_lines ?? '[]', true) ?: []);
        } finally {
            DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
            if ($orderId) DB::table('orders')->where('id', $orderId)->delete();
            DB::table('idempotency_keys')->where('key', $idempotencyKey)->delete();
            if ($couponId) DB::table('coupons')->where('id', $couponId)->delete();
            if ($user) DB::table('cart_items')->where('user_id', $user->id)->delete();
            if ($variationId) DB::table('product_variations')->where('id', $variationId)->delete();
            if ($productId) DB::table('products_data')->where('id', $productId)->delete();
            if ($user) $user->delete();
            ShippingConfig::save($originalShippingConfig);
        }
    }
}

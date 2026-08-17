<?php

namespace Tests\Feature;

use App\Helpers\PaymentConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CouponOrderUsageTest extends TestCase
{
    public function test_completed_checkout_persists_coupon_and_records_usage(): void
    {
        $user = null;
        $productId = null;
        $variationId = null;
        $couponId = null;
        $orderId = null;
        $idempotencyKey = (string) Str::uuid();
        $suffix = uniqid('coupon-order-usage-', true);

        try {
            $user = User::create([
                'name' => 'Coupon Usage Test',
                'email' => 'coupon-order-usage-' . $suffix . '@ramostore.local',
                'password' => 'temporary-test-password',
                'role' => json_encode(['customer']),
            ]);

            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'Coupon usage product ' . $suffix,
                'slug' => 'coupon-usage-' . str_replace('.', '-', $suffix),
                'search_text' => 'Coupon usage product ' . $suffix,
                'sku' => 'COUPON-' . substr(sha1($suffix), 0, 10),
                'images' => json_encode(['thumbnail' => 'https://cdn.example.test/products/coupon-usage.jpg']),
                'status' => 'publish',
                'acceptance_status' => 'approved',
                'minimum_order_qty' => 1,
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
                'sale_price' => 100,
                'stock_quantity' => 3,
                'images' => '[]',
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
                'code' => 'TEST' . strtoupper(substr(sha1($suffix), 0, 8)),
                'amount' => 20,
                'status' => 'publish',
                'discount_type' => 'percent',
                'usage_count' => 0,
                'usage_limit_per_user' => 2,
                'minimum_amount' => 0,
                'maximum_amount' => 0,
                'date_created' => $now,
                'date_created_gmt' => $now,
                'date_modified' => $now,
                'date_modified_gmt' => $now,
                'meta_data' => '[]',
            ]);
            $couponCode = (string) DB::table('coupons')->where('id', $couponId)->value('code');

            $paymentMethod = array_key_first(PaymentConfig::checkoutMethods());
            $this->assertNotNull($paymentMethod, 'A checkout payment method must be enabled for this test.');

            $csrfToken = 'coupon-order-usage-csrf';
            $payload = [
                '_token' => $csrfToken,
                'idempotency_key' => $idempotencyKey,
                'first_name' => 'Coupon',
                'last_name' => 'Tester',
                'email' => $user->email,
                'phone' => '01000000002',
                'address' => '2 Coupon Street',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'payment_method' => $paymentMethod,
                'save_address' => '0',
            ];

            $response = $this->withSession([
                '_token' => $csrfToken,
                'ramo_coupon' => [
                    'code' => $couponCode,
                    'discount_type' => 'percent',
                    'amount' => 20,
                    'description' => 'Test coupon',
                ],
            ])->actingAs($user)->post(route('checkout.place'), $payload);

            $orderId = (int) DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->value('order_id');

            $this->assertGreaterThan(0, $orderId);
            $response->assertRedirect(route('order.success', $orderId));
            $this->assertDatabaseHas('orders', [
                'id' => $orderId,
                'coupon_code' => $couponCode,
                'discount_total' => 20,
                'coupon_applied' => 1,
            ]);
            $this->assertSame(1, (int) DB::table('coupons')->where('id', $couponId)->value('usage_count'));
            $this->assertDatabaseHas('coupon_user_limits', [
                'coupon_id' => $couponId,
                'user_id' => $user->id,
                'use_count' => 1,
            ]);
        } finally {
            if ($orderId) {
                DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
                DB::table('orders')->where('id', $orderId)->delete();
            }
            DB::table('idempotency_keys')->where('key', $idempotencyKey)->delete();
            if ($couponId) {
                DB::table('coupon_user_limits')->where('coupon_id', $couponId)->delete();
                DB::table('coupons')->where('id', $couponId)->delete();
            }
            if ($user) {
                DB::table('cart_items')->where('user_id', $user->id)->delete();
            }
            if ($variationId) {
                DB::table('product_variations')->where('id', $variationId)->delete();
            }
            if ($productId) {
                DB::table('products_data')->where('id', $productId)->delete();
            }
            if ($user) {
                $user->delete();
            }
        }
    }

    public function test_per_user_limit_uses_canonical_table_when_individual_use_is_disabled(): void
    {
        $user = null;
        $couponId = null;
        $suffix = uniqid('coupon-per-user-limit-', true);

        try {
            $user = User::create([
                'name' => 'Coupon Per User Limit Test',
                'email' => 'coupon-per-user-limit-' . $suffix . '@ramostore.local',
                'password' => 'temporary-test-password',
                'role' => json_encode(['customer']),
            ]);

            $now = now();
            $couponId = DB::table('coupons')->insertGetId([
                'code' => 'LIMIT' . strtoupper(substr(sha1($suffix), 0, 8)),
                'amount' => 10,
                'status' => 'publish',
                'discount_type' => 'percent',
                'usage_count' => 0,
                'usage_limit_per_user' => 1,
                'individual_use' => false,
                'minimum_amount' => 0,
                'maximum_amount' => 0,
                'date_created' => $now,
                'date_created_gmt' => $now,
                'date_modified' => $now,
                'date_modified_gmt' => $now,
                'meta_data' => '[]',
            ]);

            $coupon = \App\Models\Coupon::findOrFail($couponId);
            $controller = app(\App\Http\Controllers\CouponController::class);
            $increment = new \ReflectionMethod($controller, 'incrementCouponUsage');
            $increment->setAccessible(true);

            $this->assertTrue($increment->invoke($controller, $coupon, $user->id));
            $this->assertFalse($increment->invoke($controller, $coupon, $user->id));
            $this->assertSame(1, (int) DB::table('coupons')->where('id', $couponId)->value('usage_count'));
            $this->assertDatabaseHas('coupon_user_limits', [
                'coupon_id' => $couponId,
                'user_id' => $user->id,
                'use_count' => 1,
            ]);
            $this->assertSame('[]', (string) DB::table('coupons')->where('id', $couponId)->value('used_by'));
        } finally {
            if ($couponId) {
                DB::table('coupon_user_limits')->where('coupon_id', $couponId)->delete();
                DB::table('coupons')->where('id', $couponId)->delete();
            }
            $user?->delete();
        }
    }
}

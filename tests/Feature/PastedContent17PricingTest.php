<?php

namespace Tests\Feature;

use App\Helpers\ShippingConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PastedContent17PricingTest extends TestCase
{
    public function test_coupon_preview_ignores_client_total_and_does_not_consume_usage(): void
    {
        $user = null;
        $productId = null;
        $variationId = null;
        $couponId = null;
        $suffix = uniqid('p17-preview-', true);

        try {
            $user = User::create([
                'name' => 'Pasted Content 17 Preview',
                'email' => 'p17-preview-'.$suffix.'@ramostore.local',
                'password' => 'temporary-test-password',
            ]);
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'P17 preview product '.$suffix,
                'slug' => 'p17-preview-'.str_replace('.', '-', $suffix),
                'search_text' => 'P17 preview product '.$suffix,
                'sku' => 'P17-PREVIEW-'.substr(sha1($suffix), 0, 10),
                'discount_percentage' => 20,
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
                'stock_quantity' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('cart_items')->insert([
                'user_id' => $user->id,
                'product_id' => $productId,
                'variation_id' => $variationId,
                'qty' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $couponId = DB::table('coupons')->insertGetId([
                'code' => 'P17PREVIEW'.strtoupper(substr(sha1($suffix), 0, 8)),
                'amount' => 10,
                'status' => 'publish',
                'discount_type' => 'percent',
                'usage_count' => 0,
                'usage_limit_per_user' => 1,
                'minimum_amount' => 0,
                'maximum_amount' => 0,
                'date_created' => $now,
                'date_created_gmt' => $now,
                'date_modified' => $now,
                'date_modified_gmt' => $now,
                'used_by' => '[]',
                'meta_data' => '[]',
            ]);
            $code = (string) DB::table('coupons')->where('id', $couponId)->value('code');

            $response = $this->actingAs($user, 'sanctum')
                ->postJson('/api/ramo/coupons/apply', [
                    'code' => $code,
                    'cart_total' => 999999,
                ]);
            $response->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.data.discount_amount', 16)
                ->assertJsonPath('data.data.new_total', 144);

            $this->assertSame(0, (int) DB::table('coupons')->where('id', $couponId)->value('usage_count'));
            $this->assertDatabaseMissing('coupon_user_limits', [
                'coupon_id' => $couponId,
                'user_id' => $user->id,
            ]);
        } finally {
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

    public function test_api_order_uses_db_price_and_server_shipping_not_client_totals(): void
    {
        $user = null;
        $productId = null;
        $variationId = null;
        $couponId = null;
        $orderId = null;
        $idempotencyKey = (string) Str::uuid();
        $suffix = uniqid('p17-order-', true);

        try {
            $user = User::create([
                'name' => 'Pasted Content 17 Order',
                'email' => 'p17-order-'.$suffix.'@ramostore.local',
                'password' => 'temporary-test-password',
            ]);
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'P17 order product '.$suffix,
                'slug' => 'p17-order-'.str_replace('.', '-', $suffix),
                'search_text' => 'P17 order product '.$suffix,
                'sku' => 'P17-ORDER-'.substr(sha1($suffix), 0, 10),
                'discount_percentage' => 25,
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
                'stock_quantity' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('cart_items')->insert([
                'user_id' => $user->id,
                'product_id' => $productId,
                'variation_id' => $variationId,
                'qty' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $couponId = DB::table('coupons')->insertGetId([
                'code' => 'P17ORDER'.strtoupper(substr(sha1($suffix), 0, 8)),
                'amount' => 10,
                'status' => 'publish',
                'discount_type' => 'percent',
                'usage_count' => 0,
                'usage_limit_per_user' => 1,
                'minimum_amount' => 0,
                'maximum_amount' => 0,
                'date_created' => $now,
                'date_created_gmt' => $now,
                'date_modified' => $now,
                'date_modified_gmt' => $now,
                'used_by' => '[]',
                'meta_data' => '[]',
            ]);
            $code = (string) DB::table('coupons')->where('id', $couponId)->value('code');
            $paymentMethod = 'cod';
            $payload = [
                'idempotency_key' => $idempotencyKey,
                'coupon' => $code,
                'currency' => 'EGP',
                'payment_method' => $paymentMethod,
                'payment_method_title' => 'Cash on Delivery',
                'billing' => [
                    'first_name' => 'P17',
                    'last_name' => 'Tester',
                    'email' => $user->email,
                    'address_1' => 'P17 Test Street',
                    'city' => 'Cairo',
                    'state' => 'Cairo',
                    'country' => 'EG',
                    'phone' => '01000000002',
                ],
                'shipping' => [
                    'first_name' => 'P17',
                    'last_name' => 'Tester',
                    'email' => $user->email,
                    'address_1' => 'P17 Test Street',
                    'city' => 'Cairo',
                    'state' => 'Cairo',
                    'country' => 'EG',
                    'phone' => '01000000002',
                ],
                'shipping_lines' => [[
                    'method_id' => 'flat_rate:2',
                    'method_title' => 'Attacker supplied shipping',
                    'total' => 999999,
                ]],
                'line_items' => [[
                    'product_id' => 999999,
                    'variation_id' => 999999,
                    'quantity' => 999,
                    'price' => 0.01,
                ]],
            ];

            $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/create-order', $payload);
            $response->assertOk()->assertJsonPath('success', true);

            $orderId = (int) DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->value('order_id');
            $this->assertGreaterThan(0, $orderId);

            $order = DB::table('orders')->where('id', $orderId)->first();
            $shippingFee = ShippingConfig::feeForSubtotal(135.0);
            $this->assertSame('150.00', number_format((float) $order->original_total, 2, '.', ''));
            $this->assertSame('15.00', number_format((float) $order->discount_total, 2, '.', ''));
            $this->assertSame(number_format($shippingFee, 2, '.', ''), number_format((float) $order->shipping_total, 2, '.', ''));
            $this->assertSame(number_format(135 + $shippingFee, 2, '.', ''), number_format((float) $order->final_total, 2, '.', ''));

            $lineItems = json_decode($order->line_items, true);
            $this->assertCount(1, $lineItems);
            $this->assertSame((int) $productId, (int) $lineItems[0]['product_id']);
            $this->assertSame('75.00', $lineItems[0]['price']['final']);
            $this->assertSame('150.00', $lineItems[0]['price']['subtotal']);
            $this->assertNotSame('999999.00', number_format((float) $order->shipping_total, 2, '.', ''));
            $this->assertSame(1, (int) DB::table('coupons')->where('id', $couponId)->value('usage_count'));
        } finally {
            DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
            if ($orderId) {
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
}

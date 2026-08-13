<?php

namespace Tests\Feature;

use App\Helpers\PaymentConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutIdempotencyTest extends TestCase
{
    public function test_repeating_a_checkout_token_reuses_the_first_order_without_second_stock_decrement(): void
    {
        $user = null;
        $productId = null;
        $variationId = null;
        $orderId = null;
        $idempotencyKey = (string) Str::uuid();
        $suffix = uniqid('checkout-idempotency-', true);

        try {
            $user = User::create([
                'name' => 'Checkout Idempotency Test',
                'email' => 'checkout-idempotency-' . $suffix . '@ramostore.local',
                'password' => 'temporary-test-password',
                'role' => json_encode(['customer']),
            ]);

            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'Checkout idempotency product ' . $suffix,
                'slug' => 'checkout-idempotency-' . str_replace('.', '-', $suffix),
                'search_text' => 'Checkout idempotency product ' . $suffix,
                'sku' => 'CHECKOUT-' . substr(sha1($suffix), 0, 10),
                'images' => json_encode(['thumbnail' => 'https://cdn.example.test/products/checkout-idempotency.jpg']),
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

            $paymentMethod = array_key_first(PaymentConfig::checkoutMethods());
            $this->assertNotNull($paymentMethod, 'A checkout payment method must be enabled for this test.');

            $csrfToken = 'checkout-idempotency-csrf';
            $payload = [
                '_token' => $csrfToken,
                'idempotency_key' => $idempotencyKey,
                'first_name' => 'Checkout',
                'last_name' => 'Tester',
                'email' => $user->email,
                'phone' => '01000000001',
                'address' => '1 Test Street',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'payment_method' => $paymentMethod,
                'save_address' => '0',
            ];

            $firstResponse = $this->withSession(['_token' => $csrfToken])
                ->actingAs($user)
                ->post(route('checkout.place'), $payload);

            $orderId = (int) DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->value('order_id');

            $this->assertGreaterThan(0, $orderId);
            $firstResponse->assertRedirect(route('order.success', $orderId));
            $this->assertSame(2, (int) DB::table('product_variations')->where('id', $variationId)->value('stock_quantity'));

            $secondResponse = $this->withSession(['_token' => $csrfToken])
                ->actingAs($user)
                ->post(route('checkout.place'), $payload);

            $secondResponse->assertRedirect(route('order.success', $orderId));
            $this->assertSame(1, DB::table('orders')->where('customer_id', $user->id)->where('id', $orderId)->count());
            $this->assertSame(2, (int) DB::table('product_variations')->where('id', $variationId)->value('stock_quantity'));
        } finally {
            if ($orderId) {
                DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
                DB::table('orders')->where('id', $orderId)->delete();
            }
            DB::table('idempotency_keys')->where('key', $idempotencyKey)->delete();
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

<?php

namespace Tests\Feature;

use App\Helpers\PaymentConfig;
use App\Helpers\ShippingConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashOnDeliveryFeeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_cod_has_no_extra_fee_in_web_order_total(): void
    {
        $originalConfig = ShippingConfig::get();
        $user = null;
        $productId = null;
        $variationId = null;
        $orderId = null;
        $idempotencyKey = (string) Str::uuid();
        $suffix = uniqid('cod-fee-', true);

        try {
            $user = User::create([
                'name' => 'COD Fee Tester',
                'email' => 'cod-fee-'.$suffix.'@ramostore.local',
                'password' => 'temporary-test-password',
                'phone' => '01000000003',
            ]);
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'COD fee test product '.$suffix,
                'slug' => 'cod-fee-'.str_replace('.', '-', $suffix),
                'search_text' => 'COD fee test product '.$suffix,
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

            $checkoutPage = $this->actingAs($user)->get(route('checkout'));
            $checkoutPage->assertOk()
                ->assertDontSee('id="cod-fee-summary-row"', false)
                ->assertDontSee('Cash on Delivery fee', false);

            $response = $this->actingAs($user)->post(route('checkout.place'), [
                'first_name' => 'COD',
                'last_name' => 'Tester',
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => 'COD Test Street',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'address_note' => '',
                'payment_method' => 'cod',
                'notes' => '',
                'idempotency_key' => $idempotencyKey,
            ]);

            $response->assertRedirect();
            $orderId = (int) DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->value('order_id');
            $this->assertGreaterThan(0, $orderId);

            $order = DB::table('orders')->where('id', $orderId)->first();
            $expectedTotal = 100 + ShippingConfig::feeForSubtotal(100.0);
            $this->assertSame(number_format($expectedTotal, 2, '.', ''), number_format((float) $order->final_total, 2, '.', ''));
            $this->assertSame([], json_decode($order->fee_lines ?? '[]', true) ?: []);
        } finally {
            DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
            if ($orderId) {
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
            ShippingConfig::save($originalConfig);
        }
    }

    public function test_non_cod_web_order_does_not_receive_cod_fee(): void
    {
        $originalConfig = ShippingConfig::get();
        $originalPaymentConfig = PaymentConfig::get();
        $user = null;
        $productId = null;
        $variationId = null;
        $orderId = null;
        $idempotencyKey = (string) Str::uuid();
        $suffix = uniqid('non-cod-fee-', true);

        try {
            PaymentConfig::save(['credit_card_enabled' => true]);
            $user = User::create([
                'name' => 'Non COD Fee Tester',
                'email' => 'non-cod-fee-'.$suffix.'@ramostore.local',
                'password' => 'temporary-test-password',
                'phone' => '01000000004',
            ]);
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'Non COD fee test product '.$suffix,
                'slug' => 'non-cod-fee-'.str_replace('.', '-', $suffix),
                'search_text' => 'Non COD fee test product '.$suffix,
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

            $response = $this->actingAs($user)->post(route('checkout.place'), [
                'first_name' => 'Non COD',
                'last_name' => 'Tester',
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => 'Non COD Test Street',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'address_note' => '',
                'payment_method' => 'credit_card',
                'notes' => '',
                'idempotency_key' => $idempotencyKey,
            ]);

            $response->assertRedirect();
            $orderId = (int) DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->value('order_id');
            $this->assertGreaterThan(0, $orderId);

            $order = DB::table('orders')->where('id', $orderId)->first();
            $expectedTotal = 100 + ShippingConfig::feeForSubtotal(100.0);
            $this->assertSame(number_format($expectedTotal, 2, '.', ''), number_format((float) $order->final_total, 2, '.', ''));
            $this->assertSame([], json_decode($order->fee_lines ?? '[]', true) ?: []);
        } finally {
            DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
            if ($orderId) {
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
            ShippingConfig::save($originalConfig);
            PaymentConfig::save($originalPaymentConfig);
        }
    }
}

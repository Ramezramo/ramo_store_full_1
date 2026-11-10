<?php

namespace Tests\Feature;

use App\Helpers\PaymentConfig;
use App\Models\User;
use App\Services\OrderPaymentExpiryService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutIdempotencyTest extends TestCase
{
    public function test_repeating_a_checkout_token_reuses_the_first_order_without_second_stock_decrement(): void
    {
        $this->placeAndReplayCheckout();
    }

    public function test_pay_later_checkout_expires_once_and_restores_its_reserved_stock(): void
    {
        $this->placeAndReplayCheckout(payLater: true);
    }

    private function placeAndReplayCheckout(bool $payLater = false): void
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

            $methods = PaymentConfig::checkoutMethods();
            $paymentMethod = array_key_first($payLater ? array_diff_key($methods, ['cod' => true]) : $methods);
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
            if ($payLater) {
                $payload['payment_timing'] = 'later';
            }

            $firstResponse = $this->withSession(['_token' => $csrfToken])
                ->actingAs($user)
                ->post(route('checkout.place'), $payload);

            $orderId = (int) DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->value('order_id');

            $this->assertGreaterThan(0, $orderId);
            $confirmationUrl = route('order.success', $payLater ? ['id' => $orderId, 'pay' => 'later'] : $orderId);
            $firstResponse->assertRedirect($confirmationUrl);
            $this->assertSame(2, (int) DB::table('product_variations')->where('id', $variationId)->value('stock_quantity'));

            $secondResponse = $this->withSession(['_token' => $csrfToken])
                ->actingAs($user)
                ->post(route('checkout.place'), $payload);

            $secondResponse->assertRedirect($confirmationUrl);
            $this->assertSame(1, DB::table('orders')->where('customer_id', $user->id)->where('id', $orderId)->count());
            $this->assertSame(2, (int) DB::table('product_variations')->where('id', $variationId)->value('stock_quantity'));

            if ($payLater) {
                $order = DB::table('orders')->find($orderId);
                $deadline = Carbon::parse($order->payment_due_at);
                $this->assertSame('pending_payment', $order->payment_status);
                $this->assertTrue($deadline->equalTo(Carbon::parse($order->date_created)->addHours(24)));
                $this->get($confirmationUrl)->assertOk()->assertSee('Complete payment')->assertDontSee('Upload receipt');
                $this->get(route('order.success', $orderId))->assertOk()->assertSee('Upload receipt');
                $this->get(route('account.order', $orderId))->assertOk()->assertSee('Cairo time')->assertSee('Upload receipt');

                $expire = fn () => DB::transaction(fn () => app(OrderPaymentExpiryService::class)
                    ->expireLockedOrder(DB::table('orders')->lockForUpdate()->find($orderId)));

                $this->travelTo($deadline->copy()->subSecond());
                $this->assertFalse($expire());
                $this->travelTo($deadline);
                DB::table('orders')->where('id', $orderId)->update(['payment_status' => 'pending_verification']);
                $this->assertFalse($expire());
                DB::table('orders')->where('id', $orderId)->update(['payment_status' => 'rejected']);
                $this->assertTrue($expire());
                $this->assertFalse($expire());
                $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => 'cancelled', 'payment_status' => 'expired']);
                $this->assertDatabaseHas('order_sub_orders', ['parent_order_id' => $orderId, 'vendor_status' => 'cancelled']);
                $this->assertDatabaseHas('product_variations', ['id' => $variationId, 'stock_quantity' => 3]);
            }
        } finally {
            $this->travelBack();
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

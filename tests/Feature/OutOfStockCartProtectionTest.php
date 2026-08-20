<?php

namespace Tests\Feature;

use App\Helpers\PaymentConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OutOfStockCartProtectionTest extends TestCase
{
    public function test_cart_marks_out_of_stock_items_and_blocks_checkout_links(): void
    {
        $productId = null;
        $variationId = null;
        $rowId = 'out-of-stock-cart-' . uniqid();
        $suffix = uniqid('out-of-stock-ui-', true);

        try {
            [$productId, $variationId] = $this->createOutOfStockProduct($suffix);
            $sessionCart = [
                $rowId => [
                    'rowId'        => $rowId,
                    'product_id'   => $productId,
                    'variation_id' => $variationId,
                    'name'         => 'Unavailable test product',
                    'price'        => 125,
                    'qty'          => 1,
                    'image'        => null,
                    'stock'        => 1,
                    'attrs'        => [],
                ],
            ];

            $response = $this->withSession([
                'locale'    => 'ar',
                'ramo_cart' => $sessionCart,
            ])->get(route('cart'));

            $response->assertOk()
                ->assertSee('المنتج غير متوفر')
                ->assertSee('data-out-of-stock="1"', false)
                ->assertSee('data-cart-checkout-warning', false)
                ->assertSee('is-blocked', false)
                ->assertSee('disabled', false);

            $checkoutResponse = $this->withSession([
                'locale'    => 'ar',
                'ramo_cart' => $sessionCart,
            ])->get(route('checkout'));
            $checkoutResponse->assertRedirect(route('cart'));

        } finally {
            $this->app['session']->forget('ramo_cart');
            $this->deleteProductFixtures($productId, $variationId);
        }
    }

    public function test_checkout_place_rejects_out_of_stock_cart_without_creating_an_order(): void
    {
        $user = null;
        $productId = null;
        $variationId = null;
        $idempotencyKey = (string) Str::uuid();
        $suffix = uniqid('out-of-stock-checkout-', true);

        try {
            $user = User::create([
                'name'     => 'Out of Stock Checkout Test',
                'email'    => 'out-of-stock-' . $suffix . '@ramostore.local',
                'password' => 'temporary-test-password',
                'role'     => json_encode(['customer']),
            ]);

            [$productId, $variationId] = $this->createOutOfStockProduct($suffix);
            $now = now();
            DB::table('cart_items')->insert([
                'user_id'     => $user->id,
                'product_id'  => $productId,
                'variation_id'=> $variationId,
                'qty'         => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $paymentMethod = array_key_first(PaymentConfig::checkoutMethods());
            $this->assertNotNull($paymentMethod, 'A checkout payment method must be enabled for this test.');

            $csrfToken = 'out-of-stock-checkout-csrf-' . $suffix;
            $response = $this->withSession(['locale' => 'en', '_token' => $csrfToken])
                ->actingAs($user)
                ->post(route('checkout.place'), [
                    '_token'         => $csrfToken,
                    'idempotency_key' => $idempotencyKey,
                    'first_name'      => 'Out',
                    'last_name'       => 'OfStock',
                    'email'           => $user->email,
                    'phone'           => '01000000009',
                    'address'         => '9 Test Street',
                    'city'            => 'Cairo',
                    'state'           => 'Cairo',
                    'payment_method'  => $paymentMethod,
                    'notes'           => '',
                ]);

            $response->assertRedirect(route('cart'));
            $this->assertNotNull(session('error'));
            $this->assertSame(0, DB::table('orders')->where('customer_id', $user->id)->count());
            $this->assertSame(0, (int) DB::table('product_variations')->where('id', $variationId)->value('stock_quantity'));
            $this->assertDatabaseHas('cart_items', [
                'user_id'     => $user->id,
                'product_id'  => $productId,
                'variation_id'=> $variationId,
                'qty'         => 1,
            ]);
        } finally {
            if ($user) {
                DB::table('order_sub_orders')->where('customer_id', $user->id)->delete();
                DB::table('orders')->where('customer_id', $user->id)->delete();
                DB::table('cart_items')->where('user_id', $user->id)->delete();
                $user->delete();
            }
            DB::table('idempotency_keys')->where('key', $idempotencyKey)->delete();
            $this->deleteProductFixtures($productId, $variationId);
        }
    }

    /** @return array{0:int,1:int} */
    private function createOutOfStockProduct(string $suffix): array
    {
        $now = now();
        $productId = DB::table('products_data')->insertGetId([
            'name'               => 'Out of stock product ' . $suffix,
            'slug'               => 'out-of-stock-' . str_replace('.', '-', $suffix),
            'search_text'        => 'Out of stock product ' . $suffix,
            'sku'                => 'OOS-' . substr(sha1($suffix), 0, 10),
            'images'             => json_encode([]),
            'status'             => 'publish',
            'acceptance_status'  => 'approved',
            'minimum_order_qty'  => 1,
            'max_orders_per_person' => 0,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id'    => $productId,
            'main_variation' => true,
            'status'        => 'publish',
            'stock_status'  => 'outofstock',
            'attributes'    => '{}',
            'price'         => 125,
            'regular_price' => 125,
            'sale_price'   => 125,
            'stock_quantity'=> 0,
            'images'        => '[]',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        return [$productId, $variationId];
    }

    private function deleteProductFixtures(?int $productId, ?int $variationId): void
    {
        if ($variationId) {
            DB::table('product_variations')->where('id', $variationId)->delete();
        }
        if ($productId) {
            DB::table('products_data')->where('id', $productId)->delete();
        }
    }
}

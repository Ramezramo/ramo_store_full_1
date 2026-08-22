<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartReAddAvailabilityTest extends TestCase
{
    public function test_guest_can_add_more_of_a_variation_when_stock_remains(): void
    {
        [$productId, $variationId] = $this->createFixture(2, 0, 'cart-readd-available');

        try {
            $session = ['locale' => 'en', '_token' => 'cart-readd-available-token'];
            $payload = [
                '_token' => 'cart-readd-available-token',
                'product_id' => $productId,
                'variation_id' => $variationId,
                'qty' => 1,
            ];

            $first = $this->withSession($session)->postJson(route('cart.add'), $payload);
            $first->assertOk()->assertJsonPath('success', true);
            $this->assertSame(1, $first->json('items.0.qty'));

            $second = $this->withSession($session)->postJson(route('cart.add'), $payload);
            $second->assertOk()->assertJsonPath('success', true);
            $this->assertSame(2, $second->json('items.0.qty'));
            $this->assertSame(2, session('ramo_cart.' . md5($productId . '_' . $variationId) . '.qty'));
        } finally {
            $this->app['session']->forget('ramo_cart');
            $this->deleteFixture($productId, $variationId);
        }
    }

    public function test_readding_when_cart_already_uses_all_stock_returns_a_specific_message(): void
    {
        [$productId, $variationId] = $this->createFixture(1, 0, 'cart-readd-exhausted');

        try {
            $session = ['locale' => 'en', '_token' => 'cart-readd-exhausted-token'];
            $payload = [
                '_token' => 'cart-readd-exhausted-token',
                'product_id' => $productId,
                'variation_id' => $variationId,
                'qty' => 1,
            ];

            $first = $this->withSession($session)->postJson(route('cart.add'), $payload);
            $first->assertOk()->assertJsonPath('success', true);

            $second = $this->withSession($session)->postJson(route('cart.add'), $payload);
            $second->assertStatus(422)
                ->assertJsonPath('success', false)
                ->assertJsonPath('message', 'You already have 1 unit(s) of "Cart readd exhausted" in your cart, and that quantity counts against the available stock. Only 0 more unit(s) can be added (maximum 1 per order).');

            $this->assertSame(1, session('ramo_cart.' . md5($productId . '_' . $variationId) . '.qty'));
        } finally {
            $this->app['session']->forget('ramo_cart');
            $this->deleteFixture($productId, $variationId);
        }
    }

    /** @return array{0:int,1:int} */
    private function createFixture(int $stock, int $maximum, string $label): array
    {
        $suffix = uniqid($label . '-', true);
        $now = now();
        $name = ucfirst(str_replace('-', ' ', $label));
        $productId = DB::table('products_data')->insertGetId([
            'name' => $name,
            'slug' => $label . '-' . str_replace('.', '-', $suffix),
            'search_text' => $name,
            'sku' => strtoupper(substr(sha1($suffix), 0, 10)),
            'images' => '[]',
            'status' => 'publish',
            'acceptance_status' => 'approved',
            'minimum_order_qty' => 1,
            'max_orders_per_person' => $maximum,
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
            'stock_quantity' => $stock,
            'images' => '[]',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$productId, $variationId];
    }

    private function deleteFixture(int $productId, int $variationId): void
    {
        DB::table('product_variations')->where('id', $variationId)->delete();
        DB::table('products_data')->where('id', $productId)->delete();
    }
}

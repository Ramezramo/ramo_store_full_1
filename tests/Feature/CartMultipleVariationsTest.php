<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartMultipleVariationsTest extends TestCase
{
    public function test_guest_can_add_multiple_color_variations_with_independent_quantities(): void
    {
        $productId = null;
        $variationIds = [];
        $suffix = uniqid('cart-multiple-', true);

        try {
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'Multi color cart product ' . $suffix,
                'slug' => 'multi-color-' . str_replace('.', '-', $suffix),
                'search_text' => 'Multi color cart product ' . $suffix,
                'sku' => 'MULTI-' . substr(sha1($suffix), 0, 10),
                'images' => json_encode(['thumbnail' => 'https://cdn.example.test/products/multi-color.jpg']),
                'status' => 'publish',
                'acceptance_status' => 'approved',
                'minimum_order_qty' => 1,
                'max_orders_per_person' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ([['Red' => 'red'], ['Blue' => 'blue']] as $attributes) {
                $variationIds[] = DB::table('product_variations')->insertGetId([
                    'product_id' => $productId,
                    'main_variation' => count($variationIds) === 0,
                    'status' => 'publish',
                    'stock_status' => 'instock',
                    'attributes' => json_encode($attributes),
                    'price' => 100,
                    'regular_price' => 100,
                    'sale_price' => 100,
                    'stock_quantity' => 8,
                    'images' => '[]',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $csrfToken = 'cart-multiple-csrf-' . $suffix;
            $response = $this->withSession(['locale' => 'en', '_token' => $csrfToken])->postJson(route('cart.add-multiple'), [
                '_token' => $csrfToken,
                'product_id' => $productId,
                'items' => [
                    ['variation_id' => $variationIds[0], 'qty' => 2],
                    ['variation_id' => $variationIds[1], 'qty' => 3],
                ],
            ]);

            $response->assertOk()->assertJsonPath('success', true);
            $this->assertSame(2, $response->json('count'));
            $this->assertCount(2, $response->json('items'));
            $this->assertSame(
                [2, 3],
                collect($response->json('items'))->sortBy('variation_id')->pluck('qty')->values()->all()
            );
            $this->assertSame(
                collect($variationIds)->sort()->values()->all(),
                collect($response->json('items'))->pluck('variation_id')->sort()->values()->all()
            );
        } finally {
            $this->app['session']->forget('ramo_cart');
            if ($variationIds) {
                DB::table('product_variations')->whereIn('id', $variationIds)->delete();
            }
            if ($productId) {
                DB::table('products_data')->where('id', $productId)->delete();
            }
        }
    }

    public function test_bulk_add_rejects_variations_belonging_to_another_product(): void
    {
        $productIds = [];
        $variationIds = [];
        $suffix = uniqid('cart-multiple-isolation-', true);

        try {
            $now = now();
            foreach (['owner', 'other'] as $label) {
                $productId = DB::table('products_data')->insertGetId([
                    'name' => "Bulk isolation {$label} {$suffix}",
                    'slug' => "bulk-isolation-{$label}-" . str_replace('.', '-', $suffix),
                    'search_text' => "Bulk isolation {$label} {$suffix}",
                    'sku' => strtoupper($label) . '-' . substr(sha1($suffix . $label), 0, 8),
                    'images' => '[]',
                    'status' => 'publish',
                    'acceptance_status' => 'approved',
                    'minimum_order_qty' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $productIds[$label] = $productId;
                $variationIds[$label] = DB::table('product_variations')->insertGetId([
                    'product_id' => $productId,
                    'main_variation' => true,
                    'status' => 'publish',
                    'stock_status' => 'instock',
                    'attributes' => json_encode(['Color' => ucfirst($label)]),
                    'price' => 50,
                    'regular_price' => 50,
                    'sale_price' => 50,
                    'stock_quantity' => 5,
                    'images' => '[]',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $csrfToken = 'cart-multiple-isolation-csrf-' . $suffix;
            $response = $this->withSession(['locale' => 'en', '_token' => $csrfToken])->postJson(route('cart.add-multiple'), [
                '_token' => $csrfToken,
                'product_id' => $productIds['owner'],
                'items' => [
                    ['variation_id' => $variationIds['owner'], 'qty' => 1],
                    ['variation_id' => $variationIds['other'], 'qty' => 1],
                ],
            ]);

            $response->assertOk()->assertJsonPath('success', true);
            $this->assertCount(1, $response->json('items'));
            $this->assertSame($variationIds['owner'], $response->json('items.0.variation_id'));
            $this->assertCount(1, $response->json('failed_items'));
            $this->assertSame($variationIds['other'], $response->json('failed_items.0.variation_id'));
        } finally {
            $this->app['session']->forget('ramo_cart');
            if ($variationIds) {
                DB::table('product_variations')->whereIn('id', array_values($variationIds))->delete();
            }
            if ($productIds) {
                DB::table('products_data')->whereIn('id', array_values($productIds))->delete();
            }
        }
    }
}

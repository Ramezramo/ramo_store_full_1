<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductPriceDisplayTest extends TestCase
{
    public function test_discounted_product_with_one_effective_variation_price_does_not_show_range_explanation(): void
    {
        $suffix = uniqid('product-price-display-', true);
        $productId = null;
        $variationIds = [];

        try {
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'Uniform price product ' . $suffix,
                'slug' => 'uniform-price-' . str_replace('.', '-', $suffix),
                'search_text' => 'Uniform price product ' . $suffix,
                'sku' => 'UNIFORM-' . substr(sha1($suffix), 0, 10),
                'images' => '[]',
                'status' => 'publish',
                'acceptance_status' => 'approved',
                'discount_percentage' => 20,
                'stock_quantity' => 4,
                'minimum_order_qty' => 1,
                'max_orders_per_person' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (['Red', 'Blue'] as $index => $color) {
                $variationIds[] = DB::table('product_variations')->insertGetId([
                    'product_id' => $productId,
                    'main_variation' => $index === 0,
                    'status' => 'publish',
                    'stock_status' => 'instock',
                    'attributes' => json_encode(['Color' => $color]),
                    'price' => 500,
                    'regular_price' => 500,
                    'sale_price' => 500,
                    'stock_quantity' => 4,
                    'images' => '[]',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $response = $this->withSession(['locale' => 'ar'])->get(route('product', $productId));

            $response->assertOk()
                ->assertSee('400.00 EGP')
                ->assertSee('500.00 EGP')
                ->assertSee('خصم 20%')
                ->assertDontSee('ليه فيه سعرين؟');
        } finally {
            if ($variationIds) {
                DB::table('product_variations')->whereIn('id', $variationIds)->delete();
            }
            if ($productId) {
                DB::table('products_data')->where('id', $productId)->delete();
            }
        }
    }
}

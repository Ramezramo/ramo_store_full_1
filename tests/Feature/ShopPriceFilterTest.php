<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShopPriceFilterTest extends TestCase
{
    public function test_shop_ajax_price_range_filters_by_the_minimum_variation_price(): void
    {
        $suffix = substr(sha1(uniqid('shop-price-', true)), 0, 12);
        $categoryId = null;
        $productIds = [];

        try {
            $categoryId = DB::table('categories2')->insertGetId([
                'name' => 'Shop price test ' . $suffix,
                'slug' => 'shop-price-test-' . $suffix,
            ]);

            $belowName = 'Below shop price ' . $suffix;
            $insideName = 'Inside shop price ' . $suffix;
            $aboveName = 'Above shop price ' . $suffix;

            $productIds[] = $this->createProduct($belowName, $categoryId, [50, 80]);
            $productIds[] = $this->createProduct($insideName, $categoryId, [120, 175]);
            $productIds[] = $this->createProduct($aboveName, $categoryId, [260, 300]);

            $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->get(route('shop', [
                    'category' => $categoryId,
                    'min_price' => '100',
                    'max_price' => '200',
                ]));

            $response->assertOk()->assertJsonStructure(['html', 'hasMore', 'nextPage', 'total']);
            $html = (string) $response->json('html');

            $this->assertSame(1, $response->json('total'));
            $this->assertStringContainsString($insideName, $html);
            $this->assertStringNotContainsString($belowName, $html);
            $this->assertStringNotContainsString($aboveName, $html);
        } finally {
            foreach ($productIds as $productId) {
                DB::table('product_variations')->where('product_id', $productId)->delete();
                DB::table('product_category')->where('product_id', $productId)->delete();
                DB::table('products_data')->where('id', $productId)->delete();
            }

            if ($categoryId) {
                DB::table('categories2')->where('id', $categoryId)->delete();
            }
        }
    }

    public function test_shop_ignores_malformed_price_parameters(): void
    {
        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('shop', [
                'min_price' => ['0 OR 1=1'],
                'max_price' => 'not-a-price',
            ]));

        $response->assertOk()->assertJsonStructure(['html', 'total']);
    }

    /** @param list<int|float> $prices */
    private function createProduct(string $name, int $categoryId, array $prices): int
    {
        $now = now();
        $productId = DB::table('products_data')->insertGetId([
            'name' => $name,
            'slug' => 'shop-price-' . strtolower(str_replace(' ', '-', $name)),
            'search_text' => $name,
            'sku' => 'SHOP-PRICE-' . substr(sha1($name), 0, 10),
            'images' => json_encode(['thumbnail' => 'https://cdn.example.test/products/shop-price-test.jpg']),
            'status' => 'publish',
            'acceptance_status' => 'approved',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('product_category')->insert([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        foreach ($prices as $index => $price) {
            DB::table('product_variations')->insert([
                'product_id' => $productId,
                'main_variation' => $index === 0,
                'attributes' => '{}',
                'price' => $price,
                'regular_price' => $price,
                'sale_price' => $price,
                'stock_quantity' => 3,
                'images' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $productId;
    }
}

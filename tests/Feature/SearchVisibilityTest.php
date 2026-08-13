<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchVisibilityTest extends TestCase
{
    public function test_search_only_returns_published_and_approved_sellable_products(): void
    {
        $suffix = uniqid('search-visibility-', true);
        $categoryId = null;
        $productIds = [];

        try {
            $categoryId = DB::table('categories2')->insertGetId([
                'name' => 'Search visibility ' . $suffix,
                'slug' => 'search-visibility-' . str_replace('.', '-', $suffix),
            ]);

            $visibleName = 'Visible search product ' . $suffix;
            $draftName = 'Draft search product ' . $suffix;
            $rejectedName = 'Rejected search product ' . $suffix;

            $productIds[] = $this->createProduct($visibleName, 'publish', 'approved', $categoryId);
            $productIds[] = $this->createProduct($draftName, 'draft', 'approved', $categoryId);
            $productIds[] = $this->createProduct($rejectedName, 'publish', 'rejected', $categoryId);

            $response = $this->get(route('search', ['q' => $suffix]));

            $response->assertOk();
            $response->assertSee($visibleName);
            $response->assertDontSee($draftName);
            $response->assertDontSee($rejectedName);
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

    public function test_search_excludes_products_that_only_mention_the_query_in_description(): void
    {
        $suffix = substr(sha1(uniqid('search-relevance-', true)), 0, 12);
        $needle = 'denim-token-' . $suffix;
        $categoryId = null;
        $productIds = [];

        try {
            $categoryId = DB::table('categories2')->insertGetId([
                'name' => 'Search relevance ' . $suffix,
                'slug' => 'search-relevance-' . $suffix,
            ]);

            $matchingName = 'Tailored ' . $needle . ' Trousers';
            $incidentalName = 'Classic Sneakers ' . $suffix;
            $productIds[] = $this->createProduct($matchingName, 'publish', 'approved', $categoryId);
            $incidentalProductId = $this->createProduct($incidentalName, 'publish', 'approved', $categoryId);
            $productIds[] = $incidentalProductId;

            DB::table('products_data')->where('id', $incidentalProductId)->update([
                'description' => 'Pairs nicely with ' . $needle . '.',
                'search_text' => 'Classic sneakers. Pairs nicely with ' . $needle . '.',
            ]);

            $response = $this->get(route('search', ['q' => $needle]));

            $response->assertOk();
            $response->assertSee($matchingName);
            $response->assertDontSee($incidentalName);
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

    public function test_search_excludes_products_that_only_mention_the_query_in_a_translated_description(): void
    {
        $suffix = substr(sha1(uniqid('translated-search-relevance-', true)), 0, 12);
        $needle = 'جينز-' . $suffix;
        $categoryId = null;
        $productIds = [];

        try {
            $categoryId = DB::table('categories2')->insertGetId([
                'name' => 'Translated search relevance ' . $suffix,
                'slug' => 'translated-search-relevance-' . $suffix,
            ]);

            $matchingEnglishName = 'Tailored trousers ' . $suffix;
            $matchingArabicName = 'بنطلون ' . $needle;
            $incidentalEnglishName = 'Classic sneakers ' . $suffix;
            $incidentalArabicName = 'سنيكرز كلاسيكي ' . $suffix;

            $matchingProductId = $this->createProduct($matchingEnglishName, 'publish', 'approved', $categoryId);
            $incidentalProductId = $this->createProduct($incidentalEnglishName, 'publish', 'approved', $categoryId);
            $productIds = [$matchingProductId, $incidentalProductId];

            DB::table('products_data')->where('id', $matchingProductId)->update([
                'translations' => json_encode([[
                    'locale' => 'ar',
                    'name' => $matchingArabicName,
                    'description' => 'وصف بنطلون مناسب للاستخدام اليومي.',
                ]], JSON_UNESCAPED_UNICODE),
            ]);
            DB::table('products_data')->where('id', $incidentalProductId)->update([
                'translations' => json_encode([[
                    'locale' => 'ar',
                    'name' => $incidentalArabicName,
                    'description' => 'سنيكرز مريح يليق مع ' . $needle . '.',
                ]], JSON_UNESCAPED_UNICODE),
            ]);

            $response = $this->withSession(['locale' => 'ar'])->get(route('search', ['q' => $needle]));

            $response->assertOk();
            $response->assertSee($matchingArabicName);
            $response->assertDontSee($incidentalArabicName);
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

    private function createProduct(string $name, string $status, string $acceptanceStatus, int $categoryId): int
    {
        $now = now();
        $slugSuffix = str_replace([' ', '.'], ['-', '-'], strtolower($name));
        $productId = DB::table('products_data')->insertGetId([
            'name' => $name,
            'slug' => $slugSuffix,
            'search_text' => $name,
            'sku' => 'SEARCH-' . substr(sha1($name), 0, 10),
            'images' => json_encode(['thumbnail' => 'https://cdn.example.test/products/search-test.jpg']),
            'status' => $status,
            'acceptance_status' => $acceptanceStatus,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('product_category')->insert([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        DB::table('product_variations')->insert([
            'product_id' => $productId,
            'main_variation' => true,
            'attributes' => '{}',
            'price' => 100,
            'regular_price' => 100,
            'sale_price' => 100,
            'stock_quantity' => 3,
            'images' => '[]',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $productId;
    }
}

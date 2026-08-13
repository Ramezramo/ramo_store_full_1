<?php

namespace Tests\Feature;

use App\Services\Catalog\ProductPublicationValidator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductPublicationValidatorTest extends TestCase
{
    public function test_variation_priced_product_with_required_catalog_data_is_publishable(): void
    {
        [$productId, $categoryId] = $this->createProductFixture();

        try {
            $failures = app(ProductPublicationValidator::class)->failuresFor($productId);

            $this->assertSame([], $failures);
        } finally {
            $this->deleteFixture($productId, $categoryId);
        }
    }

    public function test_missing_usable_media_blocks_publication(): void
    {
        [$productId, $categoryId] = $this->createProductFixture([
            'images' => json_encode(['thumbnail' => 'products/missing-publication-test.jpg']),
        ]);

        try {
            $failures = app(ProductPublicationValidator::class)->failuresFor($productId);

            $this->assertContains('A usable product image is required.', $failures);
        } finally {
            $this->deleteFixture($productId, $categoryId);
        }
    }

    /** @return array{0:int,1:int} */
    private function createProductFixture(array $overrides = []): array
    {
        $suffix = uniqid('publication-', true);
        $now = now();
        $categoryId = DB::table('categories2')->insertGetId([
            'name' => 'Publication test ' . $suffix,
            'slug' => 'publication-test-' . str_replace('.', '-', $suffix),
        ]);

        $productId = DB::table('products_data')->insertGetId(array_merge([
            'name' => 'Publication test product ' . $suffix,
            'slug' => 'publication-test-product-' . str_replace('.', '-', $suffix),
            'sku' => 'PUB-' . str_replace(['-', '.'], '', $suffix),
            'images' => json_encode(['thumbnail' => 'https://cdn.example.test/products/publication-test.jpg']),
            'status' => 'draft',
            'acceptance_status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ], $overrides));

        DB::table('product_category')->insert([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        DB::table('product_variations')->insert([
            'product_id' => $productId,
            'main_variation' => true,
            'attributes' => '{}',
            'price' => 125,
            'regular_price' => 125,
            'sale_price' => 125,
            'stock_quantity' => 4,
            'images' => '[]',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$productId, $categoryId];
    }

    private function deleteFixture(int $productId, int $categoryId): void
    {
        DB::table('product_variations')->where('product_id', $productId)->delete();
        DB::table('product_category')->where('product_id', $productId)->delete();
        DB::table('products_data')->where('id', $productId)->delete();
        DB::table('categories2')->where('id', $categoryId)->delete();
    }
}

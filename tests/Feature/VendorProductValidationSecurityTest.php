<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\VendorProductController;
use App\Models\VendorUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VendorProductValidationSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private VendorUser $seller;
    private int $productId;
    private int $variationId;

    protected function setUp(): void
    {
        parent::setUp();
        if (! str_contains(DB::connection()->getDatabaseName(), '_test')) {
            $this->markTestSkipped('Use a dedicated PostgreSQL test database.');
        }
        Storage::fake('public');
        $this->seller = VendorUser::forceCreate([
            'first_name' => 'Security', 'last_name' => 'Fixture', 'email' => uniqid('audit-').'@example.test',
            'phone' => '', 'password' => 'temporary-test-password', 'status' => 'approved',
            'shop_name' => 'Audit Store', 'shop_address' => 'Fixture address', 'auth_token' => '',
            'holder_name' => '', 'bank_name' => '', 'branch' => '',
        ]);
        $this->actingAs($this->seller, 'vendor_web')->withSession(['locale' => 'en', 'locale_source' => 'manual']);
        $this->productId = DB::table('products_data')->insertGetId([
            'name' => 'Audit Product', 'slug' => uniqid('audit-'), 'sku' => 'AUDIT',
            'vendor_id' => $this->seller->id, 'status' => 'publish', 'acceptance_status' => 'approved',
            'purchasable' => true, 'images' => '{}',
        ]);
        $this->variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $this->productId, 'main_variation' => true, 'status' => 'publish',
            'stock_status' => 'instock', 'regular_price' => 100, 'price' => 100, 'sale_price' => 100,
            'stock_quantity' => 5, 'attributes' => '{}', 'images' => '[]',
        ]);
    }

    private function payload(array $changes = []): array
    {
        return array_replace([
            'name' => 'Audit Product', 'status' => 'publish', 'regular_price' => 100, 'stock_quantity' => 5,
        ], $changes);
    }

    public static function invalidFields(): iterable
    {
        yield 'negative price' => [['regular_price' => -25], 'regular_price'];
        yield 'price overflow' => [['regular_price' => '1e100'], 'regular_price'];
        yield 'stock overflow' => [['stock_quantity' => 2147483648], 'stock_quantity'];
        yield 'unknown brand' => [['brand_id' => 2147483647], 'brand_id'];
        yield 'unknown category' => [['categories' => [2147483647]], 'categories.0'];
        yield 'invalid availability' => [['variation_status' => 'unrecognized'], 'variation_status'];
        yield 'invalid stock state' => [['stock_status' => 'unrecognized'], 'stock_status'];
        yield 'empty sanitized name' => [['name' => '<b></b>'], 'name'];
        yield 'oversized name' => [['name' => str_repeat('a', 256)], 'name'];
        yield 'nested description' => [['description' => ['nested']], 'description'];
        yield 'contradictory quantities' => [['minimum_order_qty' => 5, 'max_orders_per_person' => 2], 'max_orders_per_person'];
        yield 'script in locale' => [['translations' => [['locale' => "ar'+(window.__audit_xss=1)+'", 'name' => 'Example']]], 'translations.0.locale'];
        yield 'invalid nested size' => [['has_variations' => true, 'colors' => [
            ['type' => 'color', 'color_code' => '#000000', 'sizes' => [['nested']], 'price_map' => ['M' => 100]],
        ]], 'colors.0.sizes.0'];
    }

    #[DataProvider('invalidFields')]
    public function test_full_create_and_update_reject_invalid_input_before_writing(array $changes, string $field): void
    {
        $count = DB::table('products_data')->count();
        $this->postJson(route('vendor.products.store'), $this->payload($changes))
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->postJson(route('vendor.products.update', $this->productId), $this->payload($changes))
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertSame($count, DB::table('products_data')->count());
        $this->assertSame('Audit Product', DB::table('products_data')->where('id', $this->productId)->value('name'));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public static function invalidSections(): iterable
    {
        yield 'inline price' => ['variations', ['has_variations' => false, 'regular_price' => -25, 'stock_quantity' => 5], 'regular_price'];
        yield 'inline name' => ['basic', ['name' => '<b></b>', 'status' => 'publish'], 'name'];
        yield 'inline tags' => ['tags', ['tags_input' => str_repeat('a', 501)], 'tags_input'];
        yield 'inline translations' => ['translations', ['translations' => [['locale' => 'unknown', 'name' => 'Example']]], 'translations.0.locale'];
        yield 'inline category' => ['categories', ['categories' => [2147483647]], 'categories.0'];
        yield 'unknown section' => ['nonexistent-section', [], '_section'];
    }

    #[DataProvider('invalidSections')]
    public function test_inline_edit_uses_the_same_validation(string $section, array $fields, string $error): void
    {
        $this->postJson(route('vendor.products.update-section', $this->productId), ['_section' => $section] + $fields)
            ->assertUnprocessable()->assertJsonValidationErrors($error);
        $this->assertSame(100.0, (float) DB::table('product_variations')->where('id', $this->variationId)->value('price'));
    }

    public function test_foreign_coupon_cannot_be_modified_and_owned_coupon_can_be_attached(): void
    {
        $foreignId = DB::table('coupons')->insertGetId([
            'code' => uniqid('OTHER-'), 'amount' => 25, 'vendor_id' => $this->seller->id + 99999, 'product_ids' => '[]',
        ]);
        $ownId = DB::table('coupons')->insertGetId([
            'code' => uniqid('OWN-'), 'amount' => 25, 'vendor_id' => $this->seller->id, 'product_ids' => '[]',
        ]);
        $url = route('vendor.products.update-section', $this->productId);
        $this->postJson($url, ['_section' => 'coupon', 'coupon_id' => $foreignId])->assertUnprocessable();
        $this->assertSame('[]', DB::table('coupons')->where('id', $foreignId)->value('product_ids'));
        $this->postJson($url, ['_section' => 'coupon', 'coupon_id' => $ownId])->assertRedirect();
        $this->assertSame([$this->productId], json_decode(DB::table('coupons')->where('id', $ownId)->value('product_ids'), true));
        $this->postJson($url, ['_section' => 'coupon', 'coupon_id' => 0])->assertRedirect();
        $this->assertSame([], json_decode(DB::table('coupons')->where('id', $ownId)->value('product_ids'), true));
    }

    public function test_normal_update_preserves_variation_id_and_cart_reference(): void
    {
        $customer = \App\Models\User::factory()->create();
        $cartId = DB::table('cart_items')->insertGetId([
            'user_id' => $customer->id, 'product_id' => $this->productId, 'variation_id' => $this->variationId,
            'qty' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->postJson(route('vendor.products.update', $this->productId), $this->payload(['regular_price' => 125]))
            ->assertRedirect();
        $variation = DB::table('product_variations')->where('product_id', $this->productId)->sole();
        $this->assertSame($this->variationId, (int) $variation->id);
        $this->assertSame(125.0, (float) $variation->price);
        $this->assertSame($this->variationId, (int) DB::table('cart_items')->where('id', $cartId)->value('variation_id'));
    }

    public function test_other_sellers_unpublished_product_is_not_available_as_related_product(): void
    {
        $foreign = DB::table('products_data')->insertGetId([
            'name' => 'Private prototype audit', 'slug' => uniqid('private-'), 'vendor_id' => $this->seller->id + 99999,
            'status' => 'draft', 'acceptance_status' => 'pending', 'images' => '{}',
        ]);
        $this->postJson(route('vendor.products.update-section', $this->productId),
            ['_section' => 'related', 'related_ids' => (string) $foreign])->assertUnprocessable();
        DB::table('products_data')->where('id', $this->productId)->update(['related_ids' => json_encode([$foreign])]);
        $this->get(route('vendor.products.edit', $this->productId))->assertOk()->assertDontSee('Private prototype audit');
    }

    public function test_legacy_translation_locale_cannot_execute_as_an_inline_handler(): void
    {
        DB::table('products_data')->where('id', $this->productId)->update([
            'translations' => json_encode([['locale' => "ar'+(window.__audit_xss=1)+'", 'name' => 'Example']]),
        ]);
        $response = $this->get(route('vendor.products.edit', $this->productId))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);
        foreach ($xpath->query('//*[@onclick]') as $node) {
            $this->assertStringNotContainsString('__audit_xss', $node->getAttribute('onclick'));
        }
    }

    public function test_failed_write_rolls_back_product_and_removes_new_upload(): void
    {
        $failWrite = true;
        DB::listen(function ($query) use (&$failWrite) {
            if ($failWrite && str_starts_with($query->sql, 'update "products_data"')) {
                $failWrite = false;
                throw new \RuntimeException('Simulated persistence failure.');
            }
        });
        $this->postJson(route('vendor.products.update', $this->productId), $this->payload([
            'name' => 'Changed before failure', 'thumbnail' => UploadedFile::fake()->image('cover.jpg'),
        ]))->assertStatus(500);
        $this->assertSame('Audit Product', DB::table('products_data')->where('id', $this->productId)->value('name'));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_shared_thumbnail_is_kept_when_detached_from_one_product(): void
    {
        $path = 'products/thumbnails/audit-shared.jpg';
        Storage::disk('public')->put($path, 'fixture');
        $images = json_encode(['thumbnail' => $path]);
        DB::table('products_data')->where('id', $this->productId)->update(['images' => $images]);
        DB::table('products_data')->insert([
            'name' => 'Other product', 'slug' => uniqid('other-'), 'vendor_id' => $this->seller->id + 99999, 'images' => $images,
        ]);
        $this->postJson(route('vendor.products.update-section', $this->productId),
            ['_section' => 'images', 'delete_thumbnail' => true])->assertRedirect();
        $cleanup = new \ReflectionMethod(VendorProductController::class, 'deleteUnusedUpload');
        $cleanup->invoke(app(VendorProductController::class), $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_unreferenced_upload_can_be_deleted(): void
    {
        $path = 'products/thumbnails/audit-unused.jpg';
        Storage::disk('public')->put($path, 'fixture');
        $cleanup = new \ReflectionMethod(VendorProductController::class, 'deleteUnusedUpload');
        $cleanup->invoke(app(VendorProductController::class), $path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_corrupted_image_is_rejected_without_raw_file_fallback(): void
    {
        $file = UploadedFile::fake()->createWithContent('invalid.png', 'invalid image bytes');
        $save = new \ReflectionMethod(VendorProductController::class, 'storeImage');
        try {
            $save->invoke(app(VendorProductController::class), $file, 'products/thumbnails', 800, 800);
            $this->fail('Image decoding failure should reject the upload.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('images', $exception->errors());
        }
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_image_dimensions_are_checked_before_decoding(): void
    {
        $this->postJson(route('vendor.products.store'), $this->payload([
            'thumbnail' => UploadedFile::fake()->image('wide.png', 8001, 1),
        ]))->assertUnprocessable()->assertJsonValidationErrors('thumbnail');
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_null_optional_settings_use_valid_defaults(): void
    {
        $this->postJson(route('vendor.products.update', $this->productId), $this->payload([
            'unit_amount' => null, 'minimum_order_qty' => null, 'stock_status' => null, 'variation_status' => null,
        ]))->assertRedirect();
        $product = DB::table('products_data')->where('id', $this->productId)->first();
        $this->assertSame(1, (int) $product->minimum_order_qty);
        $this->assertSame(['piece' => 1], json_decode($product->unit, true));
        $this->assertSame('publish', DB::table('product_variations')->where('id', $this->variationId)->value('status'));
    }

    public function test_bulk_price_overflow_rejects_and_rolls_back_the_whole_update(): void
    {
        DB::table('product_variations')->where('id', $this->variationId)->update([
            'regular_price' => 90000000, 'sale_price' => 90000000, 'price' => 90000000,
        ]);
        $this->postJson(route('vendor.products.bulk-price'), [
            'product_ids' => [$this->productId], 'action' => 'increase_price', 'value' => 100,
        ])->assertUnprocessable()->assertJsonValidationErrors('value');
        $this->assertSame(90000000.0, (float) DB::table('product_variations')->where('id', $this->variationId)->value('price'));
    }

    public function test_storage_failure_rejects_save_without_storing_a_broken_image_path(): void
    {
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('put')->once()->andReturn(false);
        $disk->shouldReceive('delete')->once()->andReturn(true);
        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $this->postJson(route('vendor.products.update', $this->productId), $this->payload([
            'name' => 'Changed', 'thumbnail' => UploadedFile::fake()->image('cover.jpg'),
        ]))->assertUnprocessable()->assertJsonValidationErrors('images');
        $this->assertSame('Audit Product', DB::table('products_data')->where('id', $this->productId)->value('name'));
        $this->assertSame('{}', DB::table('products_data')->where('id', $this->productId)->value('images'));
    }

    public function test_seller_cannot_mutate_another_sellers_product_or_variation(): void
    {
        $foreign = DB::table('products_data')->insertGetId([
            'name' => 'Other product', 'slug' => uniqid('foreign-'), 'vendor_id' => $this->seller->id + 99999, 'images' => '{}',
        ]);
        $foreignVariation = DB::table('product_variations')->insertGetId([
            'product_id' => $foreign, 'attributes' => '{}', 'images' => '[]',
            'main_variation' => true, 'regular_price' => 100, 'price' => 100,
        ]);
        $this->postJson(route('vendor.products.update', $foreign), $this->payload())->assertNotFound();
        $this->postJson(route('vendor.products.update-section', $foreign), [
            '_section' => 'basic', 'name' => 'Changed', 'status' => 'publish',
        ])->assertNotFound();
        $this->deleteJson(route('vendor.products.destroy', $foreign))->assertNotFound();
        $this->postJson(route('vendor.products.update-section', $this->productId), [
            '_section' => 'var_images', 'variation_id' => $foreignVariation,
            'new_image_urls' => 'https://images.example.test/photo.jpg',
        ])->assertUnprocessable()->assertJsonValidationErrors('variation_id');
        $this->assertSame('[]', DB::table('product_variations')->where('id', $foreignVariation)->value('images'));
    }

    public function test_inline_search_fields_update_the_indexed_document(): void
    {
        $this->postJson(route('vendor.products.update-section', $this->productId),
            ['_section' => 'tags', 'tags_input' => 'new keyword'])->assertRedirect();
        $this->assertStringContainsString('new keyword', DB::table('products_data')->where('id', $this->productId)->value('search_text'));
    }
}

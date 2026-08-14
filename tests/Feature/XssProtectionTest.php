<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VendorUser;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class XssProtectionTest extends TestCase
{
    public function test_vendor_product_submission_strips_markup_before_storage_and_storefront_rendering(): void
    {
        $vendor = $this->createVendor();
        $productId = null;
        $payload = '<script>alert(1)</script>';
        $sizePayload = '<script>x</script>';
        $csrfToken = 'xss-vendor-product-csrf';

        try {
            $response = $this->withSession(['_token' => $csrfToken])
                ->actingAs($vendor, 'vendor_web')
                ->post(route('vendor.products.store'), [
                '_token' => $csrfToken,
                'name' => 'Safe product ' . $payload,
                'status' => 'publish',
                'short_description' => 'Short ' . $payload,
                'description' => 'Description ' . $payload,
                'sku' => 'SKU-' . $payload,
                'has_variations' => true,
                'colors' => [[
                    'name' => 'Blue ' . $payload,
                    'sizes' => ['M ' . $sizePayload],
                    'stock' => ['M ' . $sizePayload => 2],
                    'price_map' => ['M ' . $sizePayload => 100],
                    'sale_price_map' => [],
                ]],
                'translations' => [[
                    'locale' => 'ar',
                    'name' => 'اسم ' . $payload,
                    'description' => 'وصف ' . $payload,
                ]],
                'tags_input' => 'denim,' . $payload,
                'prod_attributes' => [[
                    'name' => 'Material ' . $payload,
                    'values' => 'Cotton,' . $payload,
                ]],
            ]);

            $response->assertRedirect(route('vendor.products'));
            $productId = (int) DB::table('products_data')->where('vendor_id', $vendor->id)->max('id');
            $product = DB::table('products_data')->find($productId);
            $variation = DB::table('product_variations')->where('product_id', $productId)->first();

            $this->assertNotNull($product);
            $this->assertNotNull($variation);
            $this->assertStringNotContainsString('<script', $product->name);
            $this->assertStringNotContainsString('<script', $product->description);
            $this->assertStringNotContainsString('<script', $product->short_description);
            $this->assertStringNotContainsString('<script', $product->sku);
            $this->assertStringNotContainsString('<script', $product->translations);
            $this->assertStringNotContainsString('<script', $product->attributes);
            $this->assertStringNotContainsString('<script', $product->tags);
            $this->assertStringNotContainsString('<script', $variation->attributes);

            DB::table('products_data')->where('id', $productId)->update(['acceptance_status' => 'approved']);
            $storefront = $this->get(route('product', $productId));
            $storefront->assertOk();
            $storefront->assertDontSee($payload, false);
        } finally {
            if ($productId) {
                DB::table('product_variations')->where('product_id', $productId)->delete();
                DB::table('product_category')->where('product_id', $productId)->delete();
                DB::table('products_data')->where('id', $productId)->delete();
            }
            $vendor->delete();
        }
    }

    public function test_admin_product_editor_hex_encodes_legacy_color_data_and_escapes_dynamic_input(): void
    {
        $admin = new User([
            'name' => 'XSS Regression Admin',
            'email' => 'xss-admin-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
        ]);
        $admin->role = json_encode(['admin']);
        $admin->save();

        $productId = null;
        $legacyPayload = '\"><img src=x onerror=alert(1)>';

        try {
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'Legacy XSS test product',
                'slug' => 'legacy-xss-test-product-' . uniqid(),
                'sku' => 'XSS-LEGACY',
                'images' => '{}',
                'status' => 'publish',
                'acceptance_status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('product_variations')->insert([
                'product_id' => $productId,
                'main_variation' => true,
                'attributes' => json_encode(['Color' => $legacyPayload, 'Size' => 'M']),
                'price' => 100,
                'regular_price' => 100,
                'sale_price' => 100,
                'stock_quantity' => 1,
                'images' => '[]',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $response = $this->actingAs($admin)->get(route('admin.products.show', $productId));

            $response->assertOk();
            $response->assertSee('ADMIN_EDIT_COLOR_ROWS', false);
            $response->assertSee('adminEscHtml', false);
            $response->assertDontSee('<img src=x onerror=alert(1)>', false);
            $response->assertSee('\\u003Cimg', false);
        } finally {
            if ($productId) {
                DB::table('product_variations')->where('product_id', $productId)->delete();
                DB::table('product_category')->where('product_id', $productId)->delete();
                DB::table('products_data')->where('id', $productId)->delete();
            }
            $admin->delete();
        }
    }

    private function createVendor(): VendorUser
    {
        $vendor = new VendorUser;
        $vendor->forceFill([
            'first_name' => 'XSS',
            'last_name' => 'Vendor',
            'phone' => '01000000000',
            'email' => 'xss-vendor-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
            'shop_name' => 'XSS Test Shop',
            'shop_address' => 'Test address',
            'status' => 'approved',
            'auth_token' => 'xss-test-token-' . uniqid(),
            'holder_name' => 'Test Holder',
            'bank_name' => 'Test Bank',
            'branch' => 'Test Branch',
        ]);
        $vendor->save();

        return $vendor;
    }
}

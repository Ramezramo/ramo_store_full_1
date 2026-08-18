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
                    'type' => 'color',
                    'name' => 'Blue ' . $payload,
                    'color_code' => '#0000FF',
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

    public function test_review_api_and_web_submission_strip_markup_before_storage(): void
    {
        $user = new User;
        $user->forceFill([
            'name' => 'XSS Review Tester',
            'email' => 'xss-review-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
            'role' => 'normal_user',
        ]);
        $user->save();

        $productIds = [];
        try {
            $apiProductId = $this->createReviewProduct();
            $productIds[] = $apiProductId;
            $apiResponse = $this->actingAs($user, 'sanctum')->postJson('/api/reviews', [
                'product_id' => $apiProductId,
                'rating' => 5,
                'title' => '<script>alert(1)</script>API title',
                'body' => 'API <b>review</b> <img src=x onerror=alert(2)> body',
            ]);

            $apiResponse->assertCreated()->assertJsonPath('success', true);
            $apiReview = DB::table('product_reviews')->where('user_id', $user->id)->where('product_id', $apiProductId)->first();
            $this->assertSame('alert(1)API title', $apiReview->title);
            $this->assertSame('API review  body', $apiReview->body);
            $this->assertStringNotContainsString('<', $apiReview->title . $apiReview->body);

            $webProductId = $this->createReviewProduct();
            $productIds[] = $webProductId;
            $csrfToken = 'xss-review-web-csrf';
            $webResponse = $this->withSession(['_token' => $csrfToken])
                ->actingAs($user)
                ->post(route('review.store'), [
                    '_token' => $csrfToken,
                    'product_id' => $webProductId,
                    'rating' => 4,
                    'title' => 'Web <i>title</i>',
                    'body' => 'Web <script>alert(3)</script> body',
                ]);

            $webResponse->assertRedirect(route('product', $webProductId));
            $webReview = DB::table('product_reviews')->where('user_id', $user->id)->where('product_id', $webProductId)->first();
            $this->assertSame('Web title', $webReview->title);
            $this->assertSame('Web alert(3) body', $webReview->body);
            $this->assertStringNotContainsString('<', $webReview->title . $webReview->body);
        } finally {
            DB::table('product_reviews')->where('user_id', $user->id)->delete();
            foreach ($productIds as $productId) {
                DB::table('products_data')->where('id', $productId)->delete();
            }
            $user->delete();
        }
    }

    public function test_admin_free_text_paths_normalize_markup_before_persistence(): void
    {
        $categoryBrand = file_get_contents(app_path('Http/Controllers/Admin/AdminCategoryBrandController.php'));
        $dashboard = file_get_contents(app_path('Http/Controllers/Admin/AdminDashboardController.php'));

        $this->assertStringContainsString('$this->sanitizeAdminText($request->input(\'admin_note\', \'\'))', $categoryBrand);
        $this->assertStringContainsString("'admin_note' => \$this->sanitizeAdminText(\$request->input('admin_note', ''))", $categoryBrand);
        $this->assertStringContainsString("\$reason = \$this->sanitizeProductText(\$data['reason'] ?? '')", $dashboard);
        $this->assertStringContainsString("'general_order_status_override_reason' => \$reason", $dashboard);
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
            $response->assertSee('admin-edit-color-rows-data', false);
            $response->assertSee('JSON.parse(document.getElementById(\'admin-edit-color-rows-data\').textContent)', false);
            $response->assertSee('adminEscHtml', false);
            $response->assertDontSee('<img src=x onerror=alert(1)>', false);
            $response->assertDontSee('</script><script>alert(1)</script>', false);
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

    public function test_search_product_card_escapes_legacy_name_before_safe_highlighting(): void
    {
        $categoryId = null;
        $productId = null;
        $legacyName = 'Legacy <img src=x onerror=alert(1)> product';

        try {
            $categoryId = DB::table('categories2')->insertGetId([
                'name' => 'XSS search test ' . uniqid(),
                'slug' => 'xss-search-test-' . uniqid(),
            ]);
            $now = now();
            $productId = DB::table('products_data')->insertGetId([
                'name' => $legacyName,
                'slug' => 'legacy-xss-search-' . uniqid(),
                'search_text' => $legacyName,
                'sku' => 'XSS-SEARCH-' . uniqid(),
                'images' => json_encode(['thumbnail' => 'https://cdn.example.test/products/xss-search.jpg']),
                'status' => 'publish',
                'acceptance_status' => 'approved',
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
                'stock_quantity' => 1,
                'images' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $response = $this->get(route('search', ['q' => 'alert(1)']));

            $response->assertOk();
            $response->assertSee('&lt;img src=x onerror=', false);
            $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        } finally {
            if ($productId) {
                DB::table('product_variations')->where('product_id', $productId)->delete();
                DB::table('product_category')->where('product_id', $productId)->delete();
                DB::table('products_data')->where('id', $productId)->delete();
            }
            if ($categoryId) {
                DB::table('categories2')->where('id', $categoryId)->delete();
            }
        }
    }

    public function test_related_product_chip_uses_text_content_and_event_listener(): void
    {
        $vendorCreate = file_get_contents(resource_path('views/web/vendor/products/create.blade.php'));

        $this->assertStringContainsString('label.textContent = displayName;', $vendorCreate);
        $this->assertStringContainsString('removeButton.addEventListener(\'click\', () => removeRelated(id));', $vendorCreate);
        $this->assertStringNotContainsString('tag.innerHTML = `${name.length > 35', $vendorCreate);
        $this->assertStringNotContainsString('onclick="removeRelated(${id})"', $vendorCreate);
    }

    public function test_authenticated_size_editors_do_not_build_tags_from_raw_inner_html(): void
    {
        $vendorShow = file_get_contents(resource_path('views/web/vendor/products/show.blade.php'));
        $adminShow = file_get_contents(resource_path('views/admin/products/show.blade.php'));

        $this->assertStringContainsString('document.createTextNode(size)', $vendorShow);
        $this->assertStringContainsString('document.createTextNode(size)', $adminShow);
        $this->assertStringNotContainsString('tag.innerHTML  = `${size}', $vendorShow);
        $this->assertStringNotContainsString('tag.innerHTML = `${size}', $adminShow);
        $this->assertStringNotContainsString('onclick="removeSizeShow', $vendorShow);
        $this->assertStringNotContainsString('onclick="adminRemoveSize', $adminShow);
    }

    public function test_policy_page_escapes_configured_plain_text_without_raw_html_echo(): void
    {
        $html = view('web.policy-page', [
            'page' => ['title' => 'Policy', 'summary' => 'Summary'],
            'pageKey' => 'privacy',
            'isAr' => false,
            'isPolicyDraft' => false,
            'copy' => "Safe line\n<script>alert(1)</script>",
        ])->render();

        $this->assertStringContainsString('Safe line', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('white-space:pre-line', $html);
    }

    public function test_json_payloads_use_safe_blade_json_directive(): void
    {
        $adminShow = file_get_contents(resource_path('views/admin/products/show.blade.php'));
        $vendorCreate = file_get_contents(resource_path('views/web/vendor/products/create.blade.php'));
        $vendorShow = file_get_contents(resource_path('views/web/vendor/products/show.blade.php'));

        foreach ([$adminShow, $vendorCreate, $vendorShow] as $view) {
            $this->assertStringContainsString('@json(', $view);
            $this->assertStringNotContainsString('json_encode(', $view);
        }
    }

    public function test_dynamic_html_audit_escapes_or_avoids_user_controlled_values(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $home = file_get_contents(resource_path('views/web/home.blade.php'));
        $livePreview = file_get_contents(resource_path('views/admin/live_preview.blade.php'));
        $timeline = file_get_contents(resource_path('views/admin/timeline.blade.php'));

        $this->assertStringContainsString("nameEl.textContent = String(item.name ?? '');", $layout);
        $this->assertStringNotContainsString('<div class="atc-item-name">${item.name || \'\'}</div>', $layout);
        $this->assertStringContainsString("name.textContent = String(p.name ?? '');", $home);
        $this->assertStringNotContainsString("'+p.name+'</div>", $home);
        $this->assertStringContainsString('value="${escAttr(item.label||\'\')}"', $livePreview);
        $this->assertStringContainsString('value="${escAttr(item.label||\'\')}"', $timeline);
        $this->assertStringContainsString('${escHtml(c.name)}</option>', $livePreview);
        $this->assertStringContainsString('${escHtml(c.name)}</option>', $timeline);
    }

    private function createReviewProduct(): int
    {
        $now = now();

        return (int) DB::table('products_data')->insertGetId([
            'name' => 'Review XSS fixture',
            'slug' => 'review-xss-fixture-' . uniqid(),
            'search_text' => 'review xss fixture',
            'sku' => 'REVIEW-XSS-' . uniqid(),
            'images' => '{}',
            'status' => 'publish',
            'acceptance_status' => 'approved',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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

<?php

namespace Tests\Feature;

use App\Helpers\PaymentConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

// Security regressions: fixtures, orders and inventory changes are rolled back.
class CartSecurityRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        while (DB::transactionLevel() > 0) DB::rollBack();
        parent::tearDown();
    }

    private function customer(): User
    {
        return User::factory()->create(['name' => 'Temporary security audit']);
    }

    private function product(array $changes = [], float $price = 1000): array
    {
        $suffix = (string) Str::uuid();
        $product = DB::table('products_data')->insertGetId(array_merge([
            'name' => 'Audit fixture '.$suffix, 'slug' => 'audit-'.$suffix,
            'search_text' => 'Audit fixture', 'sku' => 'AUDIT-'.$suffix,
            'images' => '{}', 'status' => 'publish', 'acceptance_status' => 'approved',
            'minimum_order_qty' => 1, 'max_orders_per_person' => 0,
            'stock_quantity' => 3, 'purchasable' => true, 'manage_stock' => true,
            'created_at' => now(), 'updated_at' => now(),
        ], $changes));
        $variation = DB::table('product_variations')->insertGetId([
            'product_id' => $product, 'main_variation' => true,
            'status' => 'publish', 'stock_status' => 'instock', 'attributes' => '{}',
            'price' => $price, 'regular_price' => $price, 'sale_price' => $price,
            'stock_quantity' => 3, 'images' => '[]', 'created_at' => now(), 'updated_at' => now(),
        ]);
        return [$product, $variation];
    }

    private function restrictedCoupon(int $included, int $excluded): string
    {
        $code = 'AUDIT'.strtoupper(Str::random(12));
        DB::table('coupons')->insert([
            'code' => $code, 'amount' => 50, 'status' => 'publish', 'discount_type' => 'percent',
            'usage_count' => 0, 'minimum_amount' => 0, 'maximum_amount' => 0,
            'product_ids' => json_encode([$included]), 'excluded_product_ids' => json_encode([$excluded]),
            'email_restrictions' => json_encode(['intended-recipient@example.test']),
            'exclude_sale_items' => true, 'limit_usage_to_x_items' => 1,
            'date_created' => now(), 'date_created_gmt' => now(),
            'date_modified' => now(), 'date_modified_gmt' => now(), 'meta_data' => '[]',
        ]);
        return $code;
    }

    private function checkout(User $user, array $extra = []): int
    {
        $token = 'temporary-audit-csrf';
        $this->actingAs($user, 'web')->withSession(['_token' => $token])
            ->post('/checkout/place', array_merge([
                '_token' => $token, 'idempotency_key' => (string) Str::uuid(),
                'first_name' => 'Audit', 'last_name' => 'Fixture', 'email' => $user->email,
                'phone' => '01000000000', 'address' => 'Temporary test address',
                'city' => 'Cairo', 'state' => 'Cairo', 'save_address' => '0',
                'payment_method' => array_key_first(PaymentConfig::checkoutMethods()),
            ], $extra))->assertRedirect();
        return (int) DB::table('orders')->where('customer_id', $user->id)->orderByDesc('id')->value('id');
    }

    public function test_default_and_explicit_main_option_share_stock_and_one_cart_row(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product();
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product, 'qty' => 2])->assertCreated();
        $this->postJson('/api/cart/add', ['product_id' => $product, 'variation_id' => $variation, 'qty' => 2])->assertUnprocessable();
        $this->postJson('/api/cart/add', ['product_id' => $product, 'variation_id' => $variation, 'qty' => 1])->assertCreated();
        $this->assertSame(1, DB::table('cart_items')->where('user_id', $user->id)->count());
        $this->assertGreaterThan(0, $this->checkout($user));
        $this->assertSame(0, (int) DB::table('product_variations')->where('id', $variation)->value('stock_quantity'));
    }

    public function test_legacy_duplicate_cart_rows_cannot_oversell_at_checkout(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product();
        foreach ([null, $variation, $variation] as $id) {
            DB::table('cart_items')->insert(['user_id' => $user->id, 'product_id' => $product, 'variation_id' => $id, 'qty' => 2, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->assertSame(0, $this->checkout($user));
        $this->assertSame(3, (int) DB::table('product_variations')->where('id', $variation)->value('stock_quantity'));
    }

    public function test_unapproved_unpublished_and_nonpurchasable_products_are_blocked(): void
    {
        $user = $this->customer();
        foreach ([['acceptance_status' => 'pending'], ['status' => 'draft'], ['purchasable' => false]] as $changes) {
            [$product, $variation] = $this->product($changes);
            $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product, 'variation_id' => $variation])->assertUnprocessable();
            $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product, 'variation_id' => $variation])->assertUnprocessable();
            DB::table('cart_items')->insert(['user_id' => $user->id, 'product_id' => $product, 'variation_id' => $variation, 'qty' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $this->assertSame(0, $this->checkout($user));
            DB::table('cart_items')->where('user_id', $user->id)->delete();
        }
    }

    public function test_api_rejects_foreign_variations_and_does_not_display_their_price(): void
    {
        $user = $this->customer();
        [$expensive] = $this->product();
        [, $cheapVariation] = $this->product([], 50);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $expensive, 'variation_id' => $cheapVariation])->assertUnprocessable();
        $row = DB::table('cart_items')->insertGetId(['user_id' => $user->id, 'product_id' => $expensive, 'variation_id' => $cheapVariation, 'qty' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->getJson('/api/cart')->assertOk()->assertJsonPath('data.items.0.price', 0);
        $this->putJson('/api/cart/'.$row, ['qty' => 2])->assertUnprocessable();
        $this->assertSame(0, $this->checkout($user));
    }

    public function test_restricted_coupon_cannot_be_applied_or_injected_into_checkout(): void
    {
        $user = $this->customer();
        [$excluded, $variation] = $this->product(['discount_percentage' => 10]);
        [$included] = $this->product();
        $code = $this->restrictedCoupon($included, $excluded);
        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $excluded, 'variation_id' => $variation])->assertOk();
        $this->postJson('/cart/coupon', ['code' => $code])->assertUnprocessable();
        $this->withSession(['ramo_coupon' => ['code' => $code]]);
        $this->assertSame(0, $this->checkout($user, ['email' => 'intended-recipient@example.test']));
        $this->assertSame(3, (int) DB::table('product_variations')->where('id', $variation)->value('stock_quantity'));
    }

    public function test_coupon_preview_whitelists_public_fields_for_an_eligible_customer(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product();
        $code = $this->restrictedCoupon($product, 0);
        DB::table('coupons')->where('code', $code)->update(['email_restrictions' => json_encode([$user->email]), 'meta_data' => json_encode(['private' => 'secret'])]);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product, 'variation_id' => $variation])->assertCreated();
        $response = $this->postJson('/api/ramo/coupons/validate', ['code' => $code, 'cart_total' => 999999]);
        $response->assertOk()->assertJsonPath('data.discount_amount', 500);
        $this->assertArrayNotHasKey('email_restrictions', $response->json('data.coupon'));
        $this->assertArrayNotHasKey('meta_data', $response->json('data.coupon'));
        $this->assertArrayNotHasKey('excluded_product_ids', $response->json('data.coupon'));
    }

    public function test_api_quantity_updates_obey_live_stock_and_sold_individually(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product(['sold_individually' => true]);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product, 'qty' => 2])->assertUnprocessable();
        $response = $this->postJson('/api/cart/add', ['product_id' => $product])->assertCreated();
        $id = $response->json('item_id');
        $this->putJson('/api/cart/'.$id, ['qty' => 2])->assertUnprocessable();
        DB::table('products_data')->where('id', $product)->update(['sold_individually' => false]);
        $this->putJson('/api/cart/'.$id, ['qty' => 4])->assertUnprocessable();
        $this->putJson('/api/cart/'.$id, ['qty' => 3])->assertOk();
    }

    public function test_coupon_discounts_only_eligible_units_consistently_through_checkout(): void
    {
        $user = $this->customer();
        [$included, $variation] = $this->product();
        [$excluded, $other] = $this->product();
        $code = $this->restrictedCoupon($included, $excluded);
        DB::table('coupons')->where('code', $code)->update(['email_restrictions' => '[]']);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $included, 'qty' => 2])->assertCreated();
        $this->postJson('/api/cart/add', ['product_id' => $excluded])->assertCreated();
        $this->postJson('/api/ramo/coupons/validate', ['code' => $code])->assertOk()->assertJsonPath('data.discount_amount', 500);
        $this->actingAs($user, 'web')->postJson('/cart/coupon', ['code' => $code])->assertOk();
        $this->get('/cart')->assertOk()->assertViewHas('discount', 500.0);
        $this->get('/checkout')->assertOk()->assertViewHas('discount', 500.0);
        $order = $this->checkout($user);
        $this->assertGreaterThan(0, $order);
        $this->assertSame(500.0, (float) DB::table('orders')->where('id', $order)->value('discount_total'));
    }

    public function test_sale_items_are_excluded_and_fixed_product_discount_is_per_eligible_unit(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product(['discount_percentage' => 10]);
        $code = $this->restrictedCoupon($product, 0);
        DB::table('coupons')->where('code', $code)->update(['email_restrictions' => '[]']);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product, 'qty' => 3])->assertCreated();
        $this->postJson('/api/ramo/coupons/validate', ['code' => $code])->assertUnprocessable();
        DB::table('coupons')->where('code', $code)->update(['exclude_sale_items' => false, 'discount_type' => 'fixed_product', 'amount' => 100, 'limit_usage_to_x_items' => 2]);
        $this->postJson('/api/ramo/coupons/validate', ['code' => $code])->assertOk()->assertJsonPath('data.discount_amount', 200);
    }

    public function test_updated_coupon_restrictions_are_checked_again_when_placing_order(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product();
        $code = $this->restrictedCoupon($product, 0);
        DB::table('coupons')->where('code', $code)->update(['email_restrictions' => '[]']);
        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product])->assertOk();
        $this->postJson('/cart/coupon', ['code' => $code])->assertOk();
        DB::table('coupons')->where('code', $code)->update(['excluded_product_ids' => json_encode([$product])]);
        $this->assertSame(0, $this->checkout($user));
        $this->assertSame(0, (int) DB::table('coupons')->where('code', $code)->value('usage_count'));
    }

    public function test_request_price_and_total_fields_cannot_change_the_checkout_price(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product();
        $this->actingAs($user)->withSession(['_token' => 'audit'])->postJson('/cart/add', [
            '_token' => 'audit', 'product_id' => $product, 'variation_id' => $variation, 'qty' => 1,
            'price' => 0.01, 'discount' => 1000,
        ])->assertOk()->assertJsonPath('items.0.price', 1000);
        $order = $this->checkout($user, ['total' => 0.01, 'subtotal' => 0.01, 'discount_total' => 9999]);
        $record = DB::table('orders')->find($order);
        $this->assertGreaterThanOrEqual(1000, (float) $record->final_total);
        $this->assertSame(0.0, (float) $record->discount_total);
        $this->assertSame(1000, json_decode($record->line_items, true)[0]['price']);
    }
    public function test_coupon_category_inclusion_and_exclusion_use_server_memberships(): void
    {
        $user = $this->customer();
        [$product] = $this->product();
        $category = DB::table('categories2')->insertGetId(['name' => 'Security fixture category']);
        $code = $this->restrictedCoupon($product, 0);
        DB::table('coupons')->where('code', $code)->update([
            'email_restrictions' => '[]', 'product_ids' => '[]', 'product_categories' => json_encode([$category]),
        ]);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product])->assertCreated();
        $this->postJson('/api/ramo/coupons/validate', ['code' => $code])->assertUnprocessable();
        DB::table('product_category')->insert(['product_id' => $product, 'category_id' => $category]);
        $this->postJson('/api/ramo/coupons/validate', ['code' => $code])->assertOk()->assertJsonPath('data.discount_amount', 500);
        DB::table('coupons')->where('code', $code)->update(['excluded_product_categories' => json_encode([$category])]);
        $this->postJson('/api/ramo/coupons/validate', ['code' => $code])->assertUnprocessable();
    }

    public function test_vendor_tokens_cannot_access_a_customers_cart_with_the_same_numeric_id(): void
    {
        $user = $this->customer();
        $vendor = new \App\Models\VendorUser;
        $vendor->forceFill([
            'id' => $user->id, 'first_name' => 'Fixture', 'last_name' => 'Vendor', 'phone' => '01000000000',
            'email' => 'vendor-'.Str::uuid().'@example.test', 'password' => 'temporary-test-password',
            'shop_name' => 'Security test store', 'shop_address' => 'Test', 'status' => 'approved',
            'auth_token' => 'fixture', 'holder_name' => 'Fixture', 'bank_name' => 'Test', 'branch' => 'Test',
        ])->save();
        [$product, $variation] = $this->product(['vendor_id' => $vendor->id]);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product])->assertCreated();
        $this->actingAs($vendor, 'sanctum')->getJson('/api/cart')->assertForbidden();
        $this->deleteJson('/api/cart')->assertForbidden();
        $this->postJson('/api/cart/add', ['product_id' => $product])->assertForbidden();
        $this->postJson('/api/ramo/coupons/validate', ['code' => 'x'])->assertForbidden();
        $this->postJson('/api/user/create-order', [])->assertForbidden();
        $this->assertSame(1, DB::table('cart_items')->where('user_id', $user->id)->count());
        DB::table('vendor_users')->where('id', $vendor->id)->update(['status' => 'pending']);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product])->assertUnprocessable();
        $this->assertSame(0, $this->checkout($user));
    }

    public function test_cart_lock_is_held_before_the_handler_reads_or_writes(): void
    {
        if (DB::getDriverName() !== 'pgsql') $this->markTestSkipped('PostgreSQL row-lock regression.');
        // A committed row is needed so a second connection can contend for the same lock.
        $user = User::query()->orderBy('id')->firstOrFail();
        $request = \Illuminate\Http\Request::create('/cart/add', 'POST');
        $request->setUserResolver(fn () => $user);
        config(['database.connections.cart_lock_probe' => config('database.connections.'.config('database.default'))]);
        $probe = DB::connection('cart_lock_probe');
        try {
            $response = app(\App\Http\Middleware\SerializeCart::class)->handle($request, function () use ($probe, $user) {
                $probe->statement("SET lock_timeout = '100ms'");
                try {
                    $probe->table('users')->where('id', $user->id)->lockForUpdate()->first();
                    $this->fail('The handler ran without holding the shared customer lock.');
                } catch (\Illuminate\Database\QueryException $e) {
                    $this->assertSame('55P03', $e->errorInfo[0]);
                }
                return response('serialized');
            });
            $this->assertSame('serialized', $response->getContent());
            $this->assertSame(30, app('router')->getRoutes()->getByName('cart.add')->locksFor());
            $this->assertSame(30, app('router')->getRoutes()->getByName('checkout.place')->locksFor());
        } finally {
            DB::purge('cart_lock_probe');
        }
    }

    private function apiOrderPayload(User $user, string $coupon): array
    {
        $address = ['first_name' => 'Test', 'last_name' => 'Fixture', 'email' => $user->email,
            'address_1' => 'Test address', 'city' => 'Cairo', 'state' => 'Cairo', 'country' => 'EG', 'phone' => '01000000000'];
        return ['idempotency_key' => (string) Str::uuid(), 'currency' => 'EGP', 'coupon' => $coupon,
            'payment_method' => 'cod', 'payment_method_title' => 'Cash on Delivery',
            'billing' => $address, 'shipping' => $address,
            'shipping_lines' => [['method_id' => 'standard_shipping', 'method_title' => 'Standard shipping', 'total' => 0]]];
    }

    public function test_api_order_uses_same_eligible_units_and_all_available_stock_can_be_purchased(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product(['manage_stock' => false]);
        $code = $this->restrictedCoupon($product, 0);
        DB::table('coupons')->where('code', $code)->update(['email_restrictions' => '[]']);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product, 'qty' => 3])->assertCreated();
        $this->postJson('/api/user/create-order', $this->apiOrderPayload($user, $code))->assertOk();
        $order = DB::table('orders')->where('customer_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertSame(500.0, (float) $order->discount_total);
        $this->assertSame(0, (int) DB::table('product_variations')->where('id', $variation)->value('stock_quantity'));
    }

    public function test_api_order_rejects_ineligible_coupon_and_rolls_back_stock(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product();
        [$included] = $this->product();
        $code = $this->restrictedCoupon($included, $product);
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product])->assertCreated();
        $payload = $this->apiOrderPayload($user, $code);
        $payload['billing']['email'] = 'intended-recipient@example.test';
        $this->postJson('/api/user/create-order', $payload)->assertUnprocessable();
        $this->assertSame(0, DB::table('orders')->where('customer_id', $user->id)->count());
        $this->assertSame(3, (int) DB::table('product_variations')->where('id', $variation)->value('stock_quantity'));
        $this->assertSame(0, (int) DB::table('coupons')->where('code', $code)->value('usage_count'));
    }

    public function test_guest_cart_merge_uses_live_stock_and_the_same_canonical_option(): void
    {
        $user = $this->customer();
        [$product, $variation] = $this->product();
        $this->actingAs($user, 'sanctum')->postJson('/api/cart/add', ['product_id' => $product, 'qty' => 2])->assertCreated();
        $this->actingAs($user, 'web')->withSession(['ramo_cart' => [
            ['product_id' => $product, 'variation_id' => null, 'qty' => 2, 'stock' => 9999],
        ]])->get('/cart')->assertOk();
        $rows = DB::table('cart_items')->where('user_id', $user->id)->get();
        $this->assertCount(1, $rows);
        $this->assertSame($variation, (int) $rows->first()->variation_id);
        $this->assertSame(3, (int) $rows->first()->qty);
        $this->assertNull(session('ramo_cart'));
    }

}

<?php

namespace Tests\Feature;

use App\Helpers\AuthConfig;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorUser;
use App\Services\OrderPaymentExpiryService;
use App\Services\VendorShipmentService;
use App\Support\ProductAttributes;
use App\Support\StorefrontDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\UsesIsolatedSecurityDatabase;
use Tests\TestCase;

class CustomerCheckoutRegressionTest extends TestCase
{
    use UsesIsolatedSecurityDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_27_000001_preserve_order_subtotal_cents.php'))->up();
        (require database_path('migrations/2026_05_03_011947_create_order_messages_table.php'))->up();
        (require database_path('migrations/2026_05_03_012001_add_sub_order_id_to_order_messages.php'))->up();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function cartFixture(): array
    {
        $user = User::factory()->create();
        $product = DB::table('products_data')->insertGetId([
            'name' => 'Checkout regression product', 'slug' => 'checkout-'.Str::uuid(),
            'search_text' => 'Checkout regression product', 'sku' => 'QA-'.Str::random(10),
            'images' => '{}', 'status' => 'publish', 'acceptance_status' => 'approved',
            'minimum_order_qty' => 1, 'max_orders_per_person' => 3, 'purchasable' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $variation = DB::table('product_variations')->insertGetId([
            'product_id' => $product, 'main_variation' => true, 'status' => 'publish', 'stock_status' => 'instock',
            'attributes' => json_encode(['Color' => 'Blue', 'Size' => 'Large', '_color_code' => '#999999']),
            'price' => 1496.93, 'regular_price' => 1496.93, 'sale_price' => 1496.93,
            'stock_quantity' => 3, 'images' => '[]', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($user)->postJson('/cart/add', ['product_id' => $product, 'variation_id' => $variation, 'qty' => 1])
            ->assertOk()->assertJsonMissingPath('items.0.attrs._color_code')->assertJsonPath('items.0.attrs.Color', 'Blue');
        return [$user, $product, $variation, md5($product.'_'.$variation)];
    }

    private function checkout(User $user): Order
    {
        $token = 'checkout-regression-token';
        $this->actingAs($user)->withSession(['_token' => $token])->post('/checkout/place', [
            '_token' => $token, 'idempotency_key' => (string) Str::uuid(),
            'first_name' => 'QA', 'last_name' => 'Tester', 'email' => $user->email,
            'phone' => '01000009027', 'address' => 'Test only', 'city' => 'Cairo', 'state' => 'Cairo',
            'payment_method' => 'cod', 'save_address' => '0',
        ])->assertRedirect();
        return Order::where('customer_id', $user->id)->sole();
    }

    public function test_cod_is_unpaid_preserves_cents_and_keeps_internal_attributes_out_of_snapshots(): void
    {
        [$user, , $variation] = $this->cartFixture();
        $order = $this->checkout($user);
        $this->assertSame('pending_collection', $order->payment_status);
        $this->assertFalse($order->set_paid);
        $this->assertTrue($order->needs_payment);
        $this->assertNull($order->date_paid_gmt);
        $this->assertNull($order->date_paid);
        $this->assertNull($order->payment_due_at);
        $this->assertEquals(1496.93, $order->original_total);
        $this->assertArrayNotHasKey('_color_code', $order->line_items[0]['attributes']);
        $this->assertSame('Blue', $order->line_items[0]['attributes']['Color']);
        $this->assertSame(2, (int) DB::table('product_variations')->where('id', $variation)->value('stock_quantity'));
        $this->assertSame(0, DB::table('cart_items')->where('user_id', $user->id)->count());
        $this->assertFalse(app(OrderPaymentExpiryService::class)->expireLockedOrder($order));
        $this->get(route('order.success', $order->id))->assertOk()->assertSee('Pending Collection')->assertSee('Cairo time')->assertDontSee('_color_code');
        $this->get(route('account.order', $order->id))->assertOk()->assertSee('awaiting collection')->assertDontSee('_color_code');
    }

    public function test_quantity_coupon_and_removal_return_updated_cart_fragments_without_redirects(): void
    {
        [$user, , , $row] = $this->cartFixture();
        $this->postJson('/cart/update/'.$row, ['qty' => 2])->assertOk()->assertJsonPath('cart_subtotal', '2,993.86')
            ->assertJsonPath('count', 1)->assertJsonStructure(['html']);
        $this->postJson('/cart/update/'.$row, ['qty' => 4])->assertUnprocessable();
        $this->assertSame(2, (int) DB::table('cart_items')->where('user_id', $user->id)->value('qty'));
        $this->postJson('/cart/coupon', ['code' => 'NO-SUCH-QA-COUPON'])->assertNotFound()->assertJsonPath('success', false);
        $code = 'QA'.Str::upper(Str::random(10));
        DB::table('coupons')->insert([
            'code' => $code, 'amount' => 10, 'discount_type' => 'percent', 'status' => 'publish',
            'usage_count' => 0, 'date_created' => now(), 'date_modified' => now(),
            'date_created_gmt' => now(), 'date_modified_gmt' => now(), 'meta_data' => '[]',
        ]);
        $couponResponse = $this->postJson('/cart/coupon', ['code' => $code])->assertOk()->assertJsonStructure(['html']);
        $this->assertStringContainsString($code, $couponResponse->json('html'));
        $this->assertStringContainsString('2,694.47', $couponResponse->json('html'));
        $this->deleteJson('/cart/coupon')->assertOk()->assertJsonStructure(['html']);
        $this->deleteJson('/cart/remove/'.$row)->assertOk()->assertJsonPath('count', 0);
        $this->assertSame(0, DB::table('cart_items')->where('user_id', $user->id)->count());
    }

    public function test_cart_mutation_returns_live_prices_in_both_totals_and_markup(): void
    {
        [, , $variation, $row] = $this->cartFixture();
        DB::table('product_variations')->where('id', $variation)->update(['price' => 1200.25, 'regular_price' => 1200.25]);
        $response = $this->postJson('/cart/update/'.$row, ['qty' => 2])->assertOk()
            ->assertJsonPath('cart_subtotal', '2,400.50')->assertJsonPath('cart_total', '2,400.50');
        $this->assertStringContainsString('2,400.50 EGP', $response->json('html'));
    }

    public function test_legacy_order_attributes_are_filtered_on_receipt_and_order_details(): void
    {
        [$user] = $this->cartFixture();
        $order = $this->checkout($user);
        $items = $order->line_items;
        $items[0]['attributes']['_color_code'] = '#999999';
        DB::table('orders')->where('id', $order->id)->update(['line_items' => json_encode($items)]);
        DB::table('order_sub_orders')->where('parent_order_id', $order->id)->update(['line_items' => json_encode($items)]);
        $this->get(route('order.success', $order->id))->assertOk()->assertDontSee('_color_code')->assertSee('Blue');
        $this->get(route('account.order', $order->id))->assertOk()->assertDontSee('_color_code')->assertSee('Blue');
    }

    public function test_otp_registration_page_explains_new_account_flow_in_both_languages(): void
    {
        AuthConfig::save(['phone_otp_login' => true]);
        $this->get('/register')->assertRedirect(route('login'));
        $this->get('/login')->assertOk()->assertSee('Sign in or create an account')->assertSee('After verifying the code')->assertDontSee('Don\'t have an account?', false);
        $this->withSession(['locale' => 'ar'])->get('/login')->assertOk()->assertSee('سجّل دخول أو اعمل حساب')->assertSee('هتكمّل بيانات حسابك');
    }

    public function test_cod_can_ship_without_payment_but_requires_admin_collection_to_be_paid(): void
    {
        [$user, $product] = $this->cartFixture();
        $vendor = new VendorUser;
        $vendor->forceFill(['first_name' => 'QA', 'last_name' => 'Vendor', 'phone' => '+201099999999',
            'email' => 'vendor@example.test', 'password' => 'testing-only', 'status' => 'approved',
            'shop_name' => 'QA Shop', 'shop_address' => 'Test', 'auth_token' => 'fixture',
            'holder_name' => 'Test', 'bank_name' => 'Test', 'branch' => 'Test'])->save();
        DB::table('products_data')->where('id', $product)->update(['vendor_id' => $vendor->id]);
        $order = $this->checkout($user);
        $shipment = (int) DB::table('order_sub_orders')->where('parent_order_id', $order->id)->value('id');
        $service = app(VendorShipmentService::class);
        $service->update($vendor, $shipment, 'shipped');
        $this->assertSame('shipped', $order->fresh()->status);
        $service->update($vendor, $shipment, 'delivered');
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertFalse($order->fresh()->set_paid);
        $this->actingAs($user)->post(route('admin.orders.payment-collected', $order->id), ['payment_received' => 1, 'reference' => 'QA'])
            ->assertForbidden();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => '["admin"]'])->save();
        $this->actingAs($admin)->post(route('admin.orders.payment-collected', $order->id), ['payment_received' => 1, 'reference' => 'QA COD collection'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue($order->fresh()->set_paid);
        $this->assertSame('confirmed', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->date_paid_gmt);
    }

    public function test_cairo_date_presentation_handles_midnight_and_preserves_utc_storage(): void
    {
        $this->assertSame('Nov 28, 2026', StorefrontDate::format('2026-11-27 23:30:00', false));
        $this->assertStringContainsString('1:30 AM Cairo time', StorefrontDate::format('2026-11-27 23:30:00'));
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame(['Color' => 'Blue'], ProductAttributes::visible(['Color' => 'Blue', '_color_code' => '#fff', 'nested' => []]));
    }
}

<?php

namespace Tests\Feature;

use App\Http\Controllers\CouponController;
use App\Models\User;
use Database\Seeders\GameChangerCouponSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GameChangerCouponTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_game_changer_coupon_is_seeded_and_listed_in_offers(): void
    {
        DB::table('coupons')->where('code', 'GAME_CHANGER')->delete();

        try {
            $this->seed(GameChangerCouponSeeder::class);
            $this->seed(GameChangerCouponSeeder::class);

            $this->assertDatabaseHas('coupons', [
                'code' => 'GAME_CHANGER',
                'amount' => 20,
                'discount_type' => 'percent',
                'minimum_amount' => 500,
                'maximum_amount' => 2000,
                'free_shipping' => false,
                'status' => 'publish',
            ]);
            $this->assertSame(1, DB::table('coupons')->where('code', 'GAME_CHANGER')->count());

            $response = $this->withSession(['locale' => 'ar'])->get(route('offers'));
            $response->assertOk()
                ->assertSee('GAME_CHANGER')
                ->assertSee('خصم 20%')
                ->assertSee('500 EGP')
                ->assertSee('2,000 EGP');
        } finally {
            DB::table('coupons')->where('code', 'GAME_CHANGER')->delete();
        }
    }

    public function test_game_changer_applies_twenty_percent_only_inside_amount_range(): void
    {
        $this->seed(GameChangerCouponSeeder::class);
        $controller = app(CouponController::class);

        try {
            $valid = $controller->applyCouponLocally('GAME_CHANGER', 1000, null, false);
            $this->assertTrue($valid['success']);
            $this->assertSame(200.0, (float) $valid['data']['discount_amount']);
            $this->assertSame(800.0, (float) $valid['data']['new_total']);

            $this->assertFalse($controller->applyCouponLocally('GAME_CHANGER', 499.99, null, false)['success']);
            $this->assertFalse($controller->applyCouponLocally('GAME_CHANGER', 2000.01, null, false)['success']);
            $this->assertTrue($controller->applyCouponLocally('GAME_CHANGER', 500, null, false)['success']);
            $this->assertTrue($controller->applyCouponLocally('GAME_CHANGER', 2000, null, false)['success']);
        } finally {
            DB::table('coupons')->where('code', 'GAME_CHANGER')->delete();
        }
    }

    public function test_checkout_redirects_to_cart_when_saved_coupon_exceeds_maximum(): void
    {
        $user = null;
        $productId = null;
        $variationId = null;
        $now = now();
        $suffix = uniqid('game-changer-checkout-', true);

        try {
            $this->seed(GameChangerCouponSeeder::class);
            $user = User::create([
                'name' => 'GAME_CHANGER checkout tester',
                'email' => 'game-changer-checkout-'.$suffix.'@ramostore.local',
                'password' => 'temporary-test-password',
            ]);
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'GAME_CHANGER checkout product '.$suffix,
                'slug' => 'game-changer-checkout-'.str_replace('.', '-', $suffix),
                'search_text' => 'GAME_CHANGER checkout product '.$suffix,
                'status' => 'publish',
                'acceptance_status' => 'approved',
                'purchasable' => true,
                'manage_stock' => true,
                'minimum_order_qty' => 1,
                'max_orders_per_person' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $variationId = DB::table('product_variations')->insertGetId([
                'product_id' => $productId,
                'main_variation' => true,
                'status' => 'publish',
                'stock_status' => 'instock',
                'attributes' => '{}',
                'price' => 1500,
                'regular_price' => 1500,
                'sale_price' => null,
                'stock_quantity' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('cart_items')->insert([
                'user_id' => $user->id,
                'product_id' => $productId,
                'variation_id' => $variationId,
                'qty' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $response = $this->actingAs($user)->withSession([
                'locale' => 'ar',
                'ramo_coupon' => [
                    'code' => 'GAME_CHANGER',
                    'amount' => 20,
                    'discount_type' => 'percent',
                    'free_shipping' => false,
                ],
            ])->get(route('checkout'));

            $response->assertRedirect(route('cart'))
                ->assertSessionHas('error', 'الكوبون ده مش متاح لقيمة السلة الحالية لأنها عدّت الحد الأقصى. عدّل الكمية أو شيل الكوبون عشان تكمل.');
            $this->assertNull(session('ramo_coupon'));
        } finally {
            DB::table('coupons')->where('code', 'GAME_CHANGER')->delete();
            if ($user) DB::table('cart_items')->where('user_id', $user->id)->delete();
            if ($variationId) DB::table('product_variations')->where('id', $variationId)->delete();
            if ($productId) DB::table('products_data')->where('id', $productId)->delete();
            if ($user) $user->delete();
        }
    }

    public function test_cart_update_invalidates_coupon_when_subtotal_exceeds_maximum(): void
    {
        $productId = null;
        $variationId = null;
        $now = now();
        $suffix = uniqid('game-changer-limit-', true);
        $rowId = null;

        try {
            $this->seed(GameChangerCouponSeeder::class);
            $productId = DB::table('products_data')->insertGetId([
                'name' => 'GAME_CHANGER limit product '.$suffix,
                'slug' => 'game-changer-limit-'.str_replace('.', '-', $suffix),
                'search_text' => 'GAME_CHANGER limit product '.$suffix,
                'status' => 'publish',
                'acceptance_status' => 'approved',
                'purchasable' => true,
                'manage_stock' => true,
                'minimum_order_qty' => 1,
                'max_orders_per_person' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $variationId = DB::table('product_variations')->insertGetId([
                'product_id' => $productId,
                'main_variation' => true,
                'status' => 'publish',
                'stock_status' => 'instock',
                'attributes' => '{}',
                'price' => 1500,
                'regular_price' => 1500,
                'sale_price' => null,
                'stock_quantity' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $rowId = md5($productId.'_'.$variationId);

            $response = $this->withSession([
                'locale' => 'ar',
                'ramo_cart' => [
                    $rowId => [
                        'rowId' => $rowId,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'name' => 'GAME_CHANGER limit product',
                        'price' => 1500,
                        'qty' => 1,
                        'stock' => 5,
                        'attrs' => [],
                    ],
                ],
                'ramo_coupon' => [
                    'code' => 'GAME_CHANGER',
                    'amount' => 20,
                    'discount_type' => 'percent',
                    'free_shipping' => false,
                ],
            ])->postJson(route('cart.update', $rowId), ['qty' => 2]);

            $response->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('coupon_invalid', true)
                ->assertJsonPath('coupon_message', 'الكوبون ده متاح لحد قيمة سلة 2000.00 جنيه بس. عدّل الكمية أو شيل الكوبون عشان تكمل.');
            $this->assertNull(session('ramo_coupon'));
        } finally {
            DB::table('coupons')->where('code', 'GAME_CHANGER')->delete();
            if ($variationId) DB::table('product_variations')->where('id', $variationId)->delete();
            if ($productId) DB::table('products_data')->where('id', $productId)->delete();
        }
    }
}

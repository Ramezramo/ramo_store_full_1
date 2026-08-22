<?php

namespace Tests\Feature;

use App\Http\Controllers\CouponController;
use Database\Seeders\GameChangerCouponSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GameChangerCouponTest extends TestCase
{
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
}

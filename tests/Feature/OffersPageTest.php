<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OffersPageTest extends TestCase
{
    public function test_offers_page_lists_active_database_coupons_only(): void
    {
        $suffix = strtoupper(substr(str_replace('.', '', uniqid('offers-', true)), -8));
        $activeCode = 'ACTIVE'.$suffix;
        $expiredCode = 'EXPIRED'.$suffix;
        $usedUpCode = 'USEDUP'.$suffix;
        $now = now();

        DB::table('coupons')->insert([
            [
                'code' => $activeCode,
                'amount' => 20,
                'status' => 'publish',
                'discount_type' => 'percent',
                'date_expires' => $now->copy()->addDays(5),
                'usage_count' => 0,
                'usage_limit' => 5,
                'minimum_amount' => 700,
                'free_shipping' => false,
                'description' => 'Active offer for testing',
            ],
            [
                'code' => $expiredCode,
                'amount' => 50,
                'status' => 'publish',
                'discount_type' => 'fixed_cart',
                'date_expires' => $now->copy()->subDay(),
                'usage_count' => 0,
                'usage_limit' => null,
                'minimum_amount' => 0,
                'free_shipping' => false,
                'description' => 'Expired offer for testing',
            ],
            [
                'code' => $usedUpCode,
                'amount' => 10,
                'status' => 'publish',
                'discount_type' => 'percent',
                'date_expires' => null,
                'usage_count' => 1,
                'usage_limit' => 1,
                'minimum_amount' => 0,
                'free_shipping' => true,
                'description' => 'Fully used offer for testing',
            ],
        ]);

        try {
            $response = $this->withSession(['locale' => 'en'])->get(route('offers'));

            $response->assertOk()
                ->assertSee($activeCode)
                ->assertSee('Active offer for testing')
                ->assertSee('700')
                ->assertDontSee($expiredCode)
                ->assertDontSee($usedUpCode);
        } finally {
            DB::table('coupons')->whereIn('code', [$activeCode, $expiredCode, $usedUpCode])->delete();
        }
    }

    public function test_offers_page_has_copy_code_interaction_and_storefront_navigation_link(): void
    {
        $template = file_get_contents(resource_path('views/web/offers.blade.php'));
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertIsString($template);
        $this->assertIsString($layout);
        $this->assertStringContainsString('function copyOfferCode(button)', $template);
        $this->assertStringContainsString('data-code="{{ strtoupper($coupon->code) }}"', $template);
        $this->assertStringContainsString("route('offers')", $layout);
        $this->assertStringContainsString('$headerOffersLabel', $layout);
        $this->assertStringContainsString('nav-offers-icon', $layout);
        $this->assertStringContainsString('aria-label="{{ $headerOffersLabel }}"', $layout);
        $this->assertStringContainsString('{{-- Wishlist --}}', $layout);
    }
}

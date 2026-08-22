<?php

namespace Tests\Feature;

use App\Helpers\ShippingConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShippingSettingsCodFeeTest extends TestCase
{
    public function test_shipping_settings_no_longer_exposes_or_persists_cod_fee(): void
    {
        $originalConfig = ShippingConfig::get();
        $admin = null;
        $suffix = uniqid('shipping-cod-admin-', true);

        try {
            $admin = User::create([
                'name' => 'Shipping Settings Admin',
                'email' => 'shipping-cod-admin-'.$suffix.'@ramostore.local',
                'password' => 'temporary-test-password',
            ]);
            DB::table('users')->where('id', $admin->id)->update([
                'role' => json_encode(['admin']),
            ]);
            $admin->refresh();

            $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                ->actingAs($admin)
                ->putJson(route('admin.shipping-settings.update'), [
                    'free_shipping_enabled' => true,
                    'free_shipping_threshold' => 1000,
                    'standard_shipping_fee' => 100,
                    'cod_fee' => 55,
                ]);

            $response->assertOk()->assertJson(['success' => true]);
            $this->assertArrayNotHasKey('cod_fee', ShippingConfig::get());

            $page = $this->actingAs($admin)->get(route('admin.shipping-settings'));
            $page->assertOk()
                ->assertDontSee('name="cod_fee"', false)
                ->assertDontSee('Cash on Delivery Fee', false);
        } finally {
            if ($admin) {
                $admin->delete();
            }
            ShippingConfig::save($originalConfig);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Helpers\ShippingConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShippingSettingsCodFeeTest extends TestCase
{
    public function test_admin_can_change_cod_fee_from_shipping_settings(): void
    {
        $originalConfig = ShippingConfig::get();
        $admin = null;
        $suffix = uniqid('shipping-cod-admin-', true);

        try {
            $admin = User::create([
                'name' => 'Shipping COD Admin',
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
            $this->assertSame(55.0, ShippingConfig::codFee());

            $page = $this->actingAs($admin)->get(route('admin.shipping-settings'));
            $page->assertOk()
                ->assertSee('name="cod_fee"', false)
                ->assertSee('value="55"', false);
        } finally {
            if ($admin) {
                $admin->delete();
            }
            ShippingConfig::save($originalConfig);
        }
    }
}

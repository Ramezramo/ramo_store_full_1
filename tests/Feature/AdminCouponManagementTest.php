<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCouponManagementTest extends TestCase
{
    public function test_admin_can_create_and_update_full_coupon_configuration(): void
    {
        $admin = null;
        $couponId = null;
        $suffix = strtoupper(substr(str_replace('.', '', uniqid('admin-coupon-', true)), -8));
        $createCode = 'FULL'.$suffix;
        $updateCode = 'EDIT'.$suffix;

        try {
            $admin = User::create([
                'name' => 'Coupon Admin Tester',
                'email' => 'coupon-admin-'.$suffix.'@ramostore.local',
                'password' => 'temporary-test-password',
            ]);
            DB::table('users')->where('id', $admin->id)->update([
                'role' => json_encode(['admin']),
            ]);
            $admin->refresh();

            $createResponse = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                ->actingAs($admin)
                ->post(route('admin.coupons.create'), [
                    'code' => $createCode,
                    'amount' => 20,
                    'discount_type' => 'percent',
                    'usage_limit' => 100,
                    'usage_limit_per_user' => 2,
                    'limit_usage_to_x_items' => 3,
                    'minimum_amount' => 300,
                    'maximum_amount' => 150,
                    'date_expires' => now()->addDays(10)->format('Y-m-d'),
                    'free_shipping' => '1',
                    'exclude_sale_items' => '1',
                    'individual_use' => '1',
                    'product_ids' => '1, 2',
                    'excluded_product_ids' => '9',
                    'product_categories' => 'shirts, summer',
                    'excluded_product_categories' => 'clearance',
                    'description' => 'Full customer-facing coupon details',
                ]);

            $createResponse->assertRedirect();
            $couponId = (int) DB::table('coupons')->where('code', $createCode)->value('id');
            $this->assertGreaterThan(0, $couponId);

            $this->assertDatabaseHas('coupons', [
                'id' => $couponId,
                'free_shipping' => true,
                'usage_limit_per_user' => 2,
                'minimum_amount' => 300,
                'description' => 'Full customer-facing coupon details',
            ]);

            $updateResponse = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                ->actingAs($admin)
                ->patch(route('admin.coupons.update', $couponId), [
                    'code' => $updateCode,
                    'amount' => 35,
                    'discount_type' => 'fixed_cart',
                    'usage_limit' => 25,
                    'usage_limit_per_user' => 1,
                    'limit_usage_to_x_items' => 1,
                    'minimum_amount' => 500,
                    'maximum_amount' => 200,
                    'date_expires' => now()->addDays(20)->format('Y-m-d'),
                    'free_shipping' => '1',
                    'description' => 'Updated offer details',
                    'product_ids' => '4, 5',
                    'excluded_product_ids' => '',
                    'product_categories' => 'new-arrivals',
                    'excluded_product_categories' => '',
                ]);

            $updateResponse->assertRedirect();
            $this->assertDatabaseHas('coupons', [
                'id' => $couponId,
                'code' => $updateCode,
                'amount' => 35,
                'discount_type' => 'fixed_cart',
                'usage_limit_per_user' => 1,
                'minimum_amount' => 500,
                'description' => 'Updated offer details',
            ]);

            $page = $this->actingAs($admin)->get(route('admin.coupons'));
            $page->assertOk()
                ->assertSee('Edit full coupon settings')
                ->assertSee('name="usage_limit_per_user"', false)
                ->assertSee('name="free_shipping"', false)
                ->assertSee($updateCode);
        } finally {
            if ($couponId) {
                DB::table('coupons')->where('id', $couponId)->delete();
            }
            if ($admin) {
                $admin->delete();
            }
        }
    }
}

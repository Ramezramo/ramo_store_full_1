<?php

namespace Tests\Feature;

use App\Helpers\AuthConfig;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\VendorApplication;
use App\Models\VendorUser;
use Illuminate\Support\Facades\Auth;
use Tests\Support\UsesIsolatedSecurityDatabase;
use Tests\TestCase;

class SellerDashboardSessionTest extends TestCase
{
    use UsesIsolatedSecurityDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_10_000002_create_vendor_applications.php'))->up();
    }

    public function test_a_saved_dashboard_link_uses_the_storefront_sign_in(): void
    {
        $dashboard = route('vendor.dashboard');

        $this->get($dashboard)->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', $dashboard);

        $this->get(route('login'))->assertOk()
            ->assertSee('Sign in with your Ramo Store account to return to the page you opened.')
            ->assertSee('Use a separate seller account');

        $this->get(route('vendor.login'))->assertOk()
            ->assertSee('Continue with your Ramo Store account');
    }

    public function test_phone_sign_in_returns_a_linked_seller_to_the_saved_page(): void
    {
        AuthConfig::save(['phone_otp_login' => true]);
        $customer = $this->customer();
        $seller = $this->seller();
        VendorApplication::forceCreate([
            'user_id' => $customer->id, 'vendor_id' => $seller->id, 'status' => 'approved',
        ]);
        $savedPage = route('vendor.store.profile', ['tab' => 'photos']);

        $this->get($savedPage)->assertRedirect(route('login'));
        OtpVerification::create([
            'phone' => $customer->phone, 'otp_code' => '123456', 'expires_at' => now()->addMinutes(5),
        ]);
        $this->postJson(route('auth.verify-otp'), ['phone' => $customer->phone, 'otp' => '123456'])
            ->assertOk()->assertJsonPath('redirect', $savedPage);
        $this->get($savedPage)->assertOk()->assertSee($seller->shop_name);
    }

    public function test_a_stale_seller_session_keeps_the_saved_page_and_supports_separate_seller_sign_in(): void
    {
        config(['app.debug' => false]);
        $seller = $this->seller();
        $savedPage = route('vendor.store.profile');

        $this->withSession([Auth::guard('vendor_web')->getName() => $seller->id])
            ->get($savedPage)->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', $savedPage);
        $this->assertGuest('vendor_web');

        $this->get(route('vendor.login'))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->post(route('vendor.login.submit'), ['email' => $seller->email, 'password' => 'seller-test-password'])
            ->assertRedirect($savedPage);
        $this->get($savedPage)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_a_customer_without_a_linked_seller_cannot_open_a_saved_dashboard(): void
    {
        $this->actingAs($this->customer())->get(route('vendor.dashboard'))
            ->assertRedirect(route('vendor.register'));
    }

    private function customer(): User
    {
        return User::create([
            'name' => 'Linked seller', 'email' => 'customer@example.test',
            'phone' => '+201000000001', 'password' => 'customer-test-password', 'shipping' => '[]',
        ]);
    }

    private function seller(): VendorUser
    {
        return VendorUser::forceCreate([
            'first_name' => 'Test', 'last_name' => 'Seller', 'email' => 'seller@example.test',
            'phone' => '01000000002', 'password' => 'seller-test-password',
            'shop_name' => 'Seller test shop', 'shop_address' => 'Test address', 'status' => 'approved',
            'auth_token' => '0', 'holder_name' => '', 'bank_name' => 'not set', 'branch' => 'not set',
        ]);
    }
}

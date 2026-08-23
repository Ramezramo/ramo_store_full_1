<?php

namespace Tests\Feature;

use App\Jobs\ClawBackReferralCommission;
use App\Jobs\ProcessReferralCommission;
use App\Helpers\AuthConfig;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\User;
use App\Services\ReferralSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReferralProgramTest extends TestCase
{
    private array $userIds = [];
    private array $orderIds = [];
    private ?object $originalSettings = null;
    private ?object $originalAuthSettings = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalSettings = DB::table('app_configs')->where('config_key', 'referral_settings')->first();
        $this->originalAuthSettings = DB::table('app_configs')->where('config_key', 'auth_settings')->first();
    }

    protected function tearDown(): void
    {
        if ($this->orderIds) {
            DB::table('referral_commissions')->whereIn('order_id', $this->orderIds)->delete();
            DB::table('orders')->whereIn('id', $this->orderIds)->delete();
        }
        if ($this->userIds) {
            DB::table('referrals')->whereIn('referred_id', $this->userIds)->orWhereIn('referrer_id', $this->userIds)->delete();
            DB::table('users')->whereIn('id', $this->userIds)->delete();
        }

        if ($this->originalSettings) {
            DB::table('app_configs')->where('config_key', 'referral_settings')->update([
                'value' => $this->originalSettings->value,
                'updated_at' => $this->originalSettings->updated_at,
            ]);
        } else {
            DB::table('app_configs')->where('config_key', 'referral_settings')->delete();
        }
        Cache::forget('referral_settings');
        if ($this->originalAuthSettings) {
            DB::table('app_configs')->where('config_key', 'auth_settings')->update([
                'value' => $this->originalAuthSettings->value,
                'updated_at' => $this->originalAuthSettings->updated_at,
            ]);
        } else {
            DB::table('app_configs')->where('config_key', 'auth_settings')->delete();
        }
        Cache::forget('auth_config');
        parent::tearDown();
    }

    public function test_first_touch_referral_is_captured_and_self_referral_is_rejected(): void
    {
        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test', ['phone' => '01000000111']);
        $this->setReferralRequest($referrer->referral_code, '198.51.100.30');

        $referred = $this->makeUser('referred-'.uniqid().'@example.test', ['phone' => '01000000222']);
        $this->assertSame($referrer->id, (int) $referred->fresh()->referred_by);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => 'pending',
        ]);

        $this->setReferralRequest($referrer->referral_code, '198.51.100.31');
        $selfReferral = $this->makeUser('self-'.uniqid().'@example.test', ['phone' => '01000000111']);
        $this->assertNull($selfReferral->fresh()->referred_by);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $selfReferral->id,
            'status' => 'rejected',
        ]);

        $this->setReferralRequest('INVALID', '203.0.113.60');
        $ipReferrer = $this->makeUser('ip-referrer-'.uniqid().'@example.test', ['phone' => '01000000333']);
        $this->setReferralRequest($ipReferrer->referral_code, '203.0.113.60');
        $ipReferred = $this->makeUser('ip-referred-'.uniqid().'@example.test', ['phone' => '01000000444']);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $ipReferrer->id,
            'referred_id' => $ipReferred->id,
            'status' => 'pending',
            'rejection_reason' => 'registration_ip_matches_referrer',
        ]);
    }

    public function test_web_capture_uses_first_touch_and_ignores_later_referral_code(): void
    {
        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test');
        $other = $this->makeUser('other-'.uniqid().'@example.test');

        $this->get('/?ref='.$referrer->referral_code)->assertCookie('ref_code', $referrer->referral_code);
        $this->withCookie('ref_code', $referrer->referral_code)
            ->get('/?ref='.$other->referral_code)
            ->assertCookie('ref_code', $referrer->referral_code);
    }

    public function test_api_style_query_referral_is_attributed_without_accepting_body_fields(): void
    {
        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test');
        app()->instance('request', Request::create(
            '/?ref='.urlencode($referrer->referral_code),
            'POST',
            [],
            [],
            [],
            ['REMOTE_ADDR' => '198.51.100.40']
        ));

        $referred = $this->makeUser('api-referred-'.uniqid().'@example.test');
        $this->assertSame($referrer->id, (int) $referred->fresh()->referred_by);
    }

    public function test_commission_is_created_only_for_first_completed_qualifying_order(): void
    {
        $settings = app(ReferralSettingsService::class);
        $settings->save([
            'referral_enabled' => true,
            'referral_min_order_amount' => 700,
            'referral_commission_type' => 'percentage',
            'referral_commission_value' => 10,
        ]);

        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test');
        $referred = $this->makeUser('referred-'.uniqid().'@example.test');
        Referral::create(['referrer_id' => $referrer->id, 'referred_id' => $referred->id, 'status' => 'pending']);

        $firstOrder = $this->makeOrder($referred->id, 1000, 'completed');
        (new ProcessReferralCommission($firstOrder->id))->handle($settings);

        $this->assertDatabaseHas('referral_commissions', [
            'order_id' => $firstOrder->id,
            'amount' => '100.00',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('referrals', ['referred_id' => $referred->id, 'status' => 'qualified', 'qualifying_order_id' => $firstOrder->id]);

        $secondOrder = $this->makeOrder($referred->id, 1500, 'completed');
        (new ProcessReferralCommission($secondOrder->id))->handle($settings);
        $this->assertSame(1, ReferralCommission::where('referral_id', Referral::where('referred_id', $referred->id)->value('id'))->count());
    }

    public function test_first_completed_order_below_threshold_cannot_qualify_a_later_order(): void
    {
        $settings = app(ReferralSettingsService::class);
        $settings->save([
            'referral_enabled' => true,
            'referral_min_order_amount' => 700,
            'referral_commission_type' => 'flat',
            'referral_commission_value' => 50,
        ]);

        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test');
        $referred = $this->makeUser('referred-'.uniqid().'@example.test');
        $referral = Referral::create(['referrer_id' => $referrer->id, 'referred_id' => $referred->id, 'status' => 'pending']);

        $firstOrder = $this->makeOrder($referred->id, 699, 'completed');
        $firstOrder->original_total = 2000;
        $firstOrder->save();
        (new ProcessReferralCommission($firstOrder->id))->handle($settings);

        $secondOrder = $this->makeOrder($referred->id, 800, 'completed');
        (new ProcessReferralCommission($secondOrder->id))->handle($settings);

        $this->assertDatabaseMissing('referral_commissions', ['referral_id' => $referral->id]);
        $this->assertDatabaseHas('referrals', ['id' => $referral->id, 'status' => 'pending']);
    }

    public function test_flat_commission_and_order_refund_clawback_are_safe_and_audited(): void
    {
        $settings = app(ReferralSettingsService::class);
        $settings->save([
            'referral_enabled' => true,
            'referral_min_order_amount' => 700,
            'referral_commission_type' => 'flat',
            'referral_commission_value' => 50,
        ]);

        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test');
        $referred = $this->makeUser('referred-'.uniqid().'@example.test');
        Referral::create(['referrer_id' => $referrer->id, 'referred_id' => $referred->id, 'status' => 'pending']);
        $order = $this->makeOrder($referred->id, 700, 'completed');

        (new ProcessReferralCommission($order->id))->handle($settings);
        $commission = ReferralCommission::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('50.00', (string) $commission->amount);
        $this->assertStringContainsString('referral_commission_created', json_encode($order->fresh()->timeline));

        $order->status = 'refunded';
        $order->save();
        (new ClawBackReferralCommission($order->id, 'customer_refund'))->handle();

        $this->assertDatabaseHas('referral_commissions', [
            'id' => $commission->id,
            'status' => 'clawed_back',
            'clawback_reason' => 'customer_refund',
        ]);
        $this->assertStringContainsString('referral_commission_clawed_back', json_encode($order->fresh()->timeline));
    }

    public function test_program_stays_disabled_by_default_and_settings_support_both_commission_types(): void
    {
        $settings = app(ReferralSettingsService::class);
        $settings->save([
            'referral_enabled' => false,
            'referral_min_order_amount' => 700,
            'referral_commission_type' => 'percentage',
            'referral_commission_value' => 5,
        ]);
        $this->assertFalse($settings->isEnabled());
        $this->assertSame(50.0, $settings->calculateCommission(1000));

        $settings->save(['referral_commission_type' => 'flat', 'referral_commission_value' => 75]);
        $this->assertSame(75.0, $settings->calculateCommission(1000));
    }

    public function test_admin_referral_page_is_protected_and_user_referral_page_is_account_only(): void
    {
        $this->get('/admin/referral')->assertRedirect();
        $this->get('/account/referral')->assertRedirect();

        $admin = $this->makeUser('admin-'.uniqid().'@example.test');
        $admin->role = json_encode(['admin']);
        $admin->save();
        $this->actingAs($admin)->get('/admin/referral')->assertOk()->assertSee('Referral Program');

        $customer = $this->makeUser('customer-'.uniqid().'@example.test');
        $this->actingAs($customer)->get('/account/referral')->assertOk()->assertSee($customer->referral_code);
    }

    public function test_admin_settings_are_manual_and_manual_self_referral_is_rejected(): void
    {
        $admin = $this->makeUser('admin-'.uniqid().'@example.test');
        $admin->role = json_encode(['admin']);
        $admin->save();
        $customer = $this->makeUser('customer-'.uniqid().'@example.test');

        $this->actingAs($customer)->get('/admin/referral')->assertForbidden();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs($admin)->put('/admin/referral/settings', [
            'referral_enabled' => '1',
            'referral_min_order_amount' => '700',
            'referral_commission_type' => 'flat',
            'referral_commission_value' => '40',
        ])->assertRedirect();
        $this->assertTrue(app(ReferralSettingsService::class)->isEnabled());

        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test', ['phone' => '01000000999']);
        $referred = $this->makeUser('referred-'.uniqid().'@example.test', ['phone' => '01000000999']);
        $this->actingAs($admin)->post('/admin/referral', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ])->assertRedirect();

        $rejected = Referral::where('referrer_id', $referrer->id)
            ->where('referred_id', $referred->id)
            ->firstOrFail();
        $this->assertSame('rejected', $rejected->status);
        $this->assertStringContainsString('phone_matches_referrer', (string) $rejected->rejection_reason);
    }

    public function test_referral_register_uses_enabled_otp_entrypoint_and_localizes_status(): void
    {
        AuthConfig::save([
            'phone_otp_login' => true,
            'email_login' => false,
            'google_login' => false,
        ]);

        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test');
        $this->get('/register?ref='.$referrer->referral_code)
            ->assertRedirect(route('login', ['ref' => $referrer->referral_code]));
        app()->instance('request', Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '198.51.100.99']));

        $referred = $this->makeUser('referred-'.uniqid().'@example.test');
        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'status' => 'rejected',
            'rejection_reason' => 'registration_ip_matches_referrer',
        ]);

        $this->actingAs($referrer)
            ->withSession(['locale' => 'ar'])
            ->get('/account/referral')
            ->assertOk()
            ->assertSee('غير مؤهلة')
            ->assertSee('700.00')
            ->assertSee('5.00%')
            ->assertSee('إزاي تاخد عمولتك؟')
            ->assertSee('استلام العمولة')
            ->assertDontSee('الأدمن')
            ->assertDontSee('>rejected<');
    }

    public function test_earnings_card_only_shows_approved_or_paid_commissions(): void
    {
        $referrer = $this->makeUser('referrer-'.uniqid().'@example.test');
        $approvedCustomer = $this->makeUser('approved-'.uniqid().'@example.test');
        $approvedReferral = Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $approvedCustomer->id,
            'status' => 'qualified',
        ]);
        $approvedOrder = $this->makeOrder($approvedCustomer->id, 1000, 'completed');
        ReferralCommission::create([
            'referral_id' => $approvedReferral->id,
            'order_id' => $approvedOrder->id,
            'amount' => 125,
            'status' => 'approved',
        ]);

        $pendingCustomer = $this->makeUser('pending-'.uniqid().'@example.test');
        $pendingReferral = Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $pendingCustomer->id,
            'status' => 'qualified',
        ]);
        $pendingOrder = $this->makeOrder($pendingCustomer->id, 1000, 'completed');
        ReferralCommission::create([
            'referral_id' => $pendingReferral->id,
            'order_id' => $pendingOrder->id,
            'amount' => 80,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($referrer)
            ->withSession(['locale' => 'ar'])
            ->get('/account/referral')
            ->assertOk()
            ->assertSee('عمولتك')
            ->getContent();

        preg_match('/referral-earnings-card.*?<\\/div>\\s*<div class="referral-stats/s', $response, $matches);
        $earningsCard = $matches[0] ?? '';
        $this->assertStringContainsString('125.00', $earningsCard);
        $this->assertStringNotContainsString('80.00', $earningsCard);
    }

    public function test_referral_fields_are_not_mass_assignable(): void
    {
        $user = new User;
        $user->fill([
            'name' => 'Safe user',
            'email' => 'safe-'.uniqid().'@example.test',
            'referral_code' => 'ATTACKER',
            'referred_by' => 999,
            'referral_lock_ip' => '203.0.113.1',
        ]);

        $this->assertSame('Safe user', $user->name);
        $this->assertNull($user->getAttribute('referral_code'));
        $this->assertNull($user->getAttribute('referred_by'));
        $this->assertNull($user->getAttribute('referral_lock_ip'));
    }

    private function makeUser(string $email, array $attributes = []): User
    {
        $user = new User(array_merge([
            'name' => 'Referral Test User',
            'first_name' => 'Referral',
            'last_name' => 'Tester',
            'email' => $email,
            'phone' => '010'.random_int(10000000, 99999999),
            'password' => 'password',
            'shipping' => json_encode([]),
            'registration_method' => 'email_password',
        ], $attributes));
        $user->role = 'normal_user';
        $user->capabilities = json_encode(['customer' => true]);
        $user->save();
        $this->userIds[] = (int) $user->id;
        return $user->fresh();
    }

    private function makeOrder(int $customerId, float $total, string $status): Order
    {
        $order = new Order;
        $order->customer_id = $customerId;
        $order->status = $status;
        $order->final_total = $total;
        $order->timeline = [];
        $order->save();
        $this->orderIds[] = (int) $order->id;
        return $order->fresh();
    }

    private function setReferralRequest(string $code, string $ip): void
    {
        app()->instance('request', Request::create(
            '/',
            'GET',
            [],
            ['ref_code' => $code],
            [],
            ['REMOTE_ADDR' => $ip]
        ));
    }
}

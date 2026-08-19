<?php

namespace Tests\Feature;

use App\Jobs\SendOtpSms;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OtpSmsDispatchTest extends TestCase
{
    private ?object $originalAuthSettings = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAuthSettings = DB::table('app_configs')
            ->where('config_key', 'auth_settings')
            ->first();

        DB::table('app_configs')->updateOrInsert(
            ['config_key' => 'auth_settings'],
            [
                'config_group' => 'auth',
                'lang' => null,
                'value' => json_encode([
                    'phone_otp_login' => true,
                    'otp_length' => 6,
                    'otp_expiry_minutes' => 5,
                    'max_resends_per_hour' => 3,
                    'resend_cooldown_seconds' => 60,
                ]),
                'label' => 'Automated OTP test settings',
                'description' => 'Temporary test configuration',
                'is_public' => false,
                'sort_order' => 0,
                'updated_at' => now(),
            ]
        );

        Cache::forget('auth_config');
    }

    protected function tearDown(): void
    {
        OtpVerification::query()
            ->whereIn('phone', ['+201000000001', '+201000000002', '+201000000003', '+201000000004'])
            ->delete();

        if ($this->originalAuthSettings) {
            DB::table('app_configs')
                ->where('config_key', 'auth_settings')
                ->update((array) $this->originalAuthSettings);
        } else {
            DB::table('app_configs')
                ->where('config_key', 'auth_settings')
                ->delete();
        }

        Cache::forget('auth_config');

        parent::tearDown();
    }

    public function test_log_driver_exposes_otp_only_when_development_preview_is_explicitly_enabled(): void
    {
        config([
            'app.debug' => false,
            'sms.driver' => 'log',
            'sms.development_preview' => true,
        ]);

        $csrfToken = 'otp-log-driver-test';
        $response = $this->withSession(['_token' => $csrfToken])
            ->postJson(route('auth.send-otp'), [
                '_token' => $csrfToken,
                'phone' => '01000000001',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('dev_note', 'Development OTP preview — not sent via real SMS.');
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $response->json('dev_otp'));
    }

    public function test_log_driver_hides_otp_when_development_preview_is_disabled(): void
    {
        config([
            'sms.driver' => 'log',
            'sms.development_preview' => false,
        ]);

        $csrfToken = 'otp-preview-disabled-test';
        $response = $this->withSession(['_token' => $csrfToken])
            ->postJson(route('auth.send-otp'), [
                '_token' => $csrfToken,
                'phone' => '01000000003',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissing(['dev_otp', 'dev_note']);
    }

    public function test_checkout_otp_request_preserves_checkout_as_the_intended_destination(): void
    {
        config([
            'sms.driver' => 'log',
            'sms.development_preview' => false,
        ]);

        $csrfToken = 'otp-checkout-context-test';
        $response = $this->withSession(['_token' => $csrfToken])
            ->postJson(route('auth.send-otp'), [
                '_token' => $csrfToken,
                'phone' => '01000000004',
                'context' => 'checkout',
            ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame(route('checkout'), session('url.intended'));
    }

    public function test_login_checkout_flag_preserves_checkout_as_the_intended_destination(): void
    {
        $this->get(route('login', ['checkout' => 1]))->assertOk();
        $this->assertSame(route('checkout'), session('url.intended'));
    }

    public function test_real_sms_provider_delivery_is_queued(): void
    {
        Queue::fake();
        config([
            'sms.driver' => 'msegat',
            'sms.development_preview' => true,
            'sms.msegat.username' => 'test-user',
            'sms.msegat.password' => 'test-key',
        ]);

        $csrfToken = 'otp-queue-driver-test';
        $response = $this->withSession(['_token' => $csrfToken])
            ->postJson(route('auth.send-otp'), [
                '_token' => $csrfToken,
                'phone' => '01000000002',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissing(['dev_otp']);

        Queue::assertPushed(SendOtpSms::class, static fn (SendOtpSms $job): bool => $job->otpVerificationId > 0);
    }
}

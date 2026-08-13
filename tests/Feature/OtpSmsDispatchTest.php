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
            ->whereIn('phone', ['+201000000001', '+201000000002'])
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

    public function test_log_driver_keeps_the_visible_development_otp_response(): void
    {
        config([
            'app.debug' => true,
            'sms.driver' => 'log',
        ]);

        $csrfToken = 'otp-log-driver-test';
        $response = $this->withSession(['_token' => $csrfToken])
            ->postJson(route('auth.send-otp'), [
                '_token' => $csrfToken,
                'phone' => '01000000001',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('dev_note', 'SMS_GATEWAY=log — OTP shown here for development only.');
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $response->json('dev_otp'));
    }

    public function test_real_sms_provider_delivery_is_queued(): void
    {
        Queue::fake();
        config([
            'sms.driver' => 'msegat',
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

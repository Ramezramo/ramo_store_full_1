<?php

namespace Tests\Feature;

use App\Helpers\AuthConfig;
use App\Models\User;
use App\Services\ReferralFraudChecker;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\Support\UsesIsolatedSecurityDatabase;
use Tests\TestCase;

class ReferralIdentitySecurityTest extends TestCase
{
    use UsesIsolatedSecurityDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_replace([
            'name' => 'Security fixture', 'email' => Str::uuid().'@example.test',
            'phone' => '+2010'.random_int(10000000, 99999999),
            'password' => 'Original-password!123', 'shipping' => '{}',
        ], $attributes));
    }

    private function registration(array $attributes = []): array
    {
        return array_replace([
            'first_name' => 'Test', 'last_name' => 'Fixture', 'name' => 'Test Fixture',
            'email' => Str::uuid().'@example.test', 'phone' => '01001234567',
            'password' => 'New-test-password!123', 'password_confirmation' => 'New-test-password!123',
        ], $attributes);
    }

    public function test_legacy_phone_routes_never_disclose_codes_or_mint_account_tokens(): void
    {
        AuthConfig::save(['phone_otp_login' => false]);
        $victim = $this->user(['phone' => '+201001234567']);
        $victim->forceFill(['role' => 'admin'])->save();
        Cache::put('otp_'.$victim->phone, '123456', 300);
        foreach (['/api/user/send-otp-number', '/api/user/register-with-phone'] as $path) {
            $this->postJson($path, ['phone' => $victim->phone, 'otp' => '123456'])
                ->assertStatus(410)->assertJsonPath('code', 'legacy_phone_auth_retired')
                ->assertJsonMissingPath('data.otp')->assertJsonMissingPath('data.cookie');
        }
        $this->assertSame(0, $victim->tokens()->count());
        $this->assertSame('123456', Cache::get('otp_'.$victim->phone));
    }

    public function test_self_referral_rejects_national_international_and_explicit_same_account(): void
    {
        $referrer = $this->user(['phone' => '+201001234567']);
        $checker = app(ReferralFraudChecker::class);
        foreach (['01001234567', '+201001234567', '00201001234567', '201001234567'] as $phone) {
            $other = new User(['email' => 'other@example.test', 'phone' => $phone]);
            $this->assertTrue($checker->evaluate($referrer, $other, '203.0.113.9')['hard_reject']);
        }
        $sameId = new User(['email' => 'different@example.test', 'phone' => '+201111234567']);
        $sameId->setAttribute('id', $referrer->id);
        $this->assertContains('account_matches_referrer', $checker->evaluate($referrer, $sameId, '203.0.113.9')['reasons']);
        $this->assertTrue($checker->evaluate($referrer, $sameId, '203.0.113.9')['hard_reject']);
    }

    public function test_api_registration_stores_a_unique_canonical_phone(): void
    {
        $this->postJson('/api/user/register', $this->registration())->assertCreated();
        $this->assertDatabaseHas('users', ['phone' => '+201001234567', 'is_phone_verified' => false]);
        $this->postJson('/api/user/register', $this->registration(['phone' => '00201001234567']))->assertStatus(400);
        $this->assertSame(1, User::count());
    }

    public function test_disabled_email_method_cannot_be_used_for_direct_signup_or_login(): void
    {
        $user = $this->user();
        AuthConfig::save(['email_login' => false, 'phone_otp_login' => true]);
        $this->postJson('/api/user/register', $this->registration())->assertForbidden();
        $this->postJson('/api/user/login', ['email' => $user->email, 'password' => 'Original-password!123'])->assertForbidden();
        $this->post('/register', $this->registration())->assertSessionHasErrors('email');
        $this->assertSame(1, User::count());
    }

    public function test_changing_referral_values_does_not_reset_the_ip_registration_allowance(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/register?ref=CODE'.$i, [])->assertUnprocessable();
        }
        $this->postJson('/register?ref=NEWCODE', [])->assertStatus(429);
        $this->withUnencryptedCookie('ref_code', ' CODE0 ')
            ->postJson('/api/user/register', [])->assertStatus(429);
    }

    public function test_real_email_proof_persists_once_without_signing_a_different_browser_in(): void
    {
        $user = $this->user();
        $token = Str::random(64);
        DB::table('email_verification_tokens')->insert([
            'email' => $user->email, 'token' => Hash::make($token), 'created_at' => now(),
        ]);
        $url = '/email/verify/confirm?'.http_build_query(['email' => $user->email, 'token' => $token]);
        $this->get($url)->assertRedirect(route('login'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertGuest();
        $this->assertDatabaseMissing('email_verification_tokens', ['email' => $user->email]);
        $this->get($url)->assertSessionHasErrors('email');
    }

    public function test_expired_email_proof_cannot_verify_an_account(): void
    {
        $user = $this->user();
        $token = Str::random(64);
        DB::table('email_verification_tokens')->insert([
            'email' => $user->email, 'token' => Hash::make($token), 'created_at' => now()->subMinutes(61),
        ]);
        $this->get('/email/verify/confirm?'.http_build_query(['email' => $user->email, 'token' => $token]))
            ->assertSessionHasErrors('email');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_mail_delivery_failure_never_returns_an_email_verification_token(): void
    {
        $user = $this->user();
        config(['mail.default' => 'smtp', 'app.debug' => true]);
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Fixture mail failure'));
        $this->actingAs($user)->post('/email/resend')->assertSessionHas('resend_error')
            ->assertSessionMissing('dev_url')->assertSessionMissing('resent');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_mail_delivery_failure_never_returns_a_password_reset_token(): void
    {
        $user = $this->user();
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'mailpit', 'app.debug' => true]);
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Fixture mail failure'));
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status')
            ->assertSessionMissing('dev_reset_url');
    }

    public function test_web_password_recovery_revokes_prior_tokens_and_consumes_the_proof(): void
    {
        $user = $this->user();
        $user->createToken('auth_token');
        $user->createToken('another_device');
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email, 'token' => Hash::make($token), 'created_at' => now(),
        ]);
        $payload = ['email' => $user->email, 'token' => $token,
            'password' => 'Recovered-password!123', 'password_confirmation' => 'Recovered-password!123'];
        $this->post('/reset-password', $payload)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('Recovered-password!123', $user->fresh()->password));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame(0, $user->tokens()->count());
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
    }

    public function test_expired_password_recovery_cannot_change_credentials_or_revoke_tokens(): void
    {
        $user = $this->user();
        $user->createToken('auth_token');
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email, 'token' => Hash::make($token), 'created_at' => now()->subMinutes(61),
        ]);
        $this->post('/reset-password', ['email' => $user->email, 'token' => $token,
            'password' => 'Recovered-password!123', 'password_confirmation' => 'Recovered-password!123'])
            ->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('Original-password!123', $user->fresh()->password));
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_routed_api_password_recovery_also_revokes_preexisting_tokens(): void
    {
        $user = $this->user();
        $user->createToken('auth_token');
        $token = Password::broker()->createToken($user);
        $this->postJson('/api/user/reset-password', ['email' => $user->email, 'token' => $token,
            'password' => 'Recovered-password!123', 'password_confirmation' => 'Recovered-password!123'])->assertOk();
        $this->assertSame(0, $user->tokens()->count());
        $this->assertTrue(Hash::check('Recovered-password!123', $user->fresh()->password));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_editing_verified_contacts_clears_the_old_verification_flags(): void
    {
        $user = $this->user(['phone' => '+201001234567']);
        $user->forceFill(['email_verified_at' => now(), 'is_phone_verified' => true])->save();
        $this->actingAs($user)->post('/account/profile', [
            'first_name' => 'Test', 'last_name' => 'Fixture', 'email' => 'replacement@example.test', 'phone' => '01111234567',
        ])->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertFalse((bool) $user->fresh()->is_phone_verified);
    }

    public function test_production_cannot_return_development_otp_even_if_preview_setting_is_enabled(): void
    {
        AuthConfig::save(['phone_otp_login' => true]);
        config(['sms.driver' => 'log', 'sms.development_preview' => true]);
        $this->app->instance('env', 'production');
        $this->mock(SmsGateway::class, function ($mock) {
            $mock->shouldReceive('send')->once()->andReturn(true);
        });
        $this->postJson('/auth/send-otp', ['phone' => '01001234567'])->assertOk()
            ->assertJsonMissingPath('dev_otp')->assertJsonMissingPath('dev_note');
    }
    private function otp(string $phone = '+201001234567', array $attributes = []): \App\Models\OtpVerification
    {
        AuthConfig::save(['phone_otp_login' => true, 'auto_register_otp' => true,
            'require_name_on_register' => true, 'max_otp_attempts' => 3, 'otp_expiry_minutes' => 5]);
        return \App\Models\OtpVerification::create(array_replace([
            'phone' => $phone, 'otp_code' => '123456', 'expires_at' => now()->addMinutes(5),
            'attempts' => 0, 'verified' => false, 'resend_count' => 0,
        ], $attributes));
    }

    public function test_disabling_phone_login_prevents_an_issued_code_from_authenticating(): void
    {
        $user = $this->user(['phone' => '+201001234567']);
        $otp = $this->otp($user->phone);
        AuthConfig::save(['phone_otp_login' => false]);
        $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123456'])->assertForbidden();
        $this->assertFalse($otp->fresh()->verified);
        $this->assertFalse((bool) $user->fresh()->is_phone_verified);
        $this->assertGuest();
    }

    public function test_wrong_otp_attempts_cannot_exceed_the_challenge_budget(): void
    {
        $user = $this->user(['phone' => '+201001234567']);
        $otp = $this->otp($user->phone);
        foreach ([2, 1, 0] as $remaining) {
            $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '000000'])
                ->assertUnprocessable()->assertJsonPath('remaining', $remaining);
        }
        $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123456'])->assertStatus(429);
        $this->assertSame(3, (int) $otp->fresh()->attempts);
        $this->assertFalse($otp->fresh()->verified);
        $this->assertGuest();
    }

    public function test_a_successful_phone_challenge_is_single_use(): void
    {
        $user = $this->user(['phone' => '+201001234567']);
        $otp = $this->otp($user->phone);
        $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123456'])
            ->assertOk()->assertJsonPath('new_user', false);
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($otp->fresh()->verified);
        $this->assertTrue((bool) $user->fresh()->is_phone_verified);
        \Illuminate\Support\Facades\Auth::logout();
        $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123456'])->assertNotFound();
        $this->assertGuest();
    }

    public function test_a_stale_otp_snapshot_cannot_bypass_the_live_attempt_limit(): void
    {
        $user = $this->user(['phone' => '+201001234567']);
        $otp = $this->otp($user->phone);
        $interleaved = false;
        \App\Models\OtpVerification::retrieved(function ($snapshot) use ($otp, &$interleaved) {
            if ($interleaved || (int) $snapshot->id !== (int) $otp->id) return;
            $interleaved = true;
            DB::table('otp_verifications')->where('id', $otp->id)->update(['attempts' => 3]);
        });
        try {
            $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123456'])->assertStatus(429);
            $this->assertTrue($interleaved);
            $this->assertFalse($otp->fresh()->verified);
            $this->assertGuest();
        } finally {
            \App\Models\OtpVerification::flushEventListeners();
        }
    }

    public function test_expired_or_blocked_account_phone_challenges_cannot_sign_in(): void
    {
        $user = $this->user(['phone' => '+201001234567']);
        $expired = $this->otp($user->phone, ['expires_at' => now()->subSecond()]);
        $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123456'])->assertStatus(410);
        $this->assertDatabaseMissing('otp_verifications', ['id' => $expired->id]);
        $user->forceFill(['is_blocked' => true])->save();
        $this->otp($user->phone);
        $this->postJson('/auth/verify-otp', ['phone' => $user->phone, 'otp' => '123456'])->assertForbidden();
        $this->assertFalse((bool) $user->fresh()->is_phone_verified);
        $this->assertGuest();
    }

    public function test_pending_profile_proof_expires_and_honors_feature_disable(): void
    {
        $this->otp();
        $this->postJson('/auth/verify-otp', ['phone' => '01001234567', 'otp' => '123456'])
            ->assertOk()->assertJsonPath('new_user', true);
        $token = session('otp_temp_token');
        $this->assertGreaterThan(now()->timestamp, session('otp_temp_expires_at'));
        AuthConfig::save(['phone_otp_login' => false]);
        $this->post('/auth/complete-profile', ['temp_token' => $token, 'name' => 'Test Fixture'])
            ->assertRedirect(route('login'))->assertSessionHasErrors('phone')->assertSessionMissing('otp_temp_token');
        $this->assertSame(0, User::count());

        AuthConfig::save(['phone_otp_login' => true]);
        $this->withSession(['otp_temp_token' => 'expired-fixture', 'otp_temp_phone' => '+201001234567',
            'otp_temp_expires_at' => now()->subSecond()->timestamp])
            ->post('/auth/complete-profile', ['temp_token' => 'expired-fixture', 'name' => 'Test Fixture'])
            ->assertRedirect(route('login'))->assertSessionHasErrors('phone');
        $this->assertSame(0, User::count());
    }

    public function test_a_valid_pending_phone_profile_still_completes_normally(): void
    {
        $this->otp();
        $this->postJson('/auth/verify-otp', ['phone' => '01001234567', 'otp' => '123456'])->assertOk();
        $token = session('otp_temp_token');
        $this->post('/auth/complete-profile', ['temp_token' => $token, 'name' => 'Test Fixture',
            'email' => 'otp-owner@example.test'])->assertRedirect(route('home'))->assertSessionHasNoErrors();
        $user = User::sole();
        $this->assertTrue((bool) $user->is_phone_verified);
        $this->assertSame('+201001234567', $user->phone);
        $this->assertAuthenticatedAs($user);
        $this->assertNull(session('otp_temp_token'));
    }

    public function test_api_recovery_rejects_expired_replayed_and_nonstring_proofs(): void
    {
        $user = $this->user();
        $user->createToken('auth_token');
        $token = Password::broker()->createToken($user);
        DB::table('password_reset_tokens')->where('email', $user->email)->update(['created_at' => now()->subMinutes(61)]);
        $payload = ['email' => $user->email, 'token' => $token,
            'password' => 'Recovered-password!123', 'password_confirmation' => 'Recovered-password!123'];
        $this->postJson('/api/user/reset-password', $payload)->assertUnprocessable();
        $this->assertSame(1, $user->tokens()->count());
        $this->postJson('/api/user/reset-password', array_replace($payload, ['token' => ['bad']]))->assertStatus(400);

        $token = Password::broker()->createToken($user);
        $payload['token'] = $token;
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->postJson('/api/user/reset-password', $payload)->assertOk();
        $this->postJson('/api/user/reset-password', $payload)->assertUnprocessable();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_blocked_accounts_cannot_use_normal_email_login_either(): void
    {
        $user = $this->user();
        $user->forceFill(['is_blocked' => true])->save();
        $payload = ['email' => $user->email, 'password' => 'Original-password!123'];
        $this->postJson('/api/user/login', $payload)->assertForbidden();
        $this->post('/login', $payload)->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(0, $user->tokens()->count());
    }

}

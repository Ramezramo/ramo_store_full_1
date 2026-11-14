<?php

namespace Tests\Feature;

use App\Helpers\AuthConfig;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Real routes and models, isolated SQLite memory database, fake provider only. */
class GoogleAuthSecurityTest extends TestCase
{
    private const STATE = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
    private int $flowCount = 0;

    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app['config']->set([
            'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array', 'session.driver' => 'array', 'queue.default' => 'sync',
            'logging.default' => 'stderr', 'app.force_https' => false,
            'view.compiled' => sys_get_temp_dir().'/ramo-google-security-tests-'.getmypid(),
        ]);
        if (! is_dir($app['config']->get('view.compiled'))) {
            mkdir($app['config']->get('view.compiled'), 0777, true);
        }
        DB::purge('sqlite');
        if (DB::getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('Google security tests must never use the application database.');
        }
        foreach ([
            '2024_01_01_000001_create_ramo_store_schema.php',
            '2026_05_04_000001_add_auth_fields_and_otp_verifications.php',
            '2026_08_23_000001_create_referral_program_tables.php',
            '2026_11_13_000003_unique_external_provider_identity.php',
        ] as $migration) {
            (require base_path('database/migrations/'.$migration))->up();
        }
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        AuthConfig::save(['google_login' => true, 'auto_register_google' => true]);
        config(['services.google.client_id' => 'isolated-client', 'services.google.client_secret' => 'isolated-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback']);
        $this->withSession(['locale' => 'en', 'locale_source' => 'manual']);
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_replace([
            'name' => 'Google security fixture', 'email' => Str::uuid().'@example.test',
            'phone' => '', 'password' => 'Fixture-password-only!123', 'shipping' => '{}',
        ], $attributes));
    }

    private function provider(array $profile = []): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'fake-provider-token']),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response(array_replace([
                'sub' => 'stable-google-identity', 'email' => 'google-owner@example.test',
                'email_verified' => true, 'name' => 'Google Owner', 'picture' => 'https://example.test/avatar.png',
            ], $profile)),
        ]);
    }

    private function performGoogleCallback(array $profile = [])
    {
        $this->provider($profile);
        $state = ++$this->flowCount === 1 ? self::STATE : hash('sha256', self::STATE.$this->flowCount);
        return $this->withSession(['google_oauth_state' => $state, 'google_oauth_issued_at' => now()->timestamp])
            ->get('/auth/google/callback?'.http_build_query(['state' => $state, 'code' => 'fake-authorization-code']));
    }

    public function test_redirect_initiates_a_fresh_unpredictable_expiring_state(): void
    {
        $response = $this->get('/auth/google')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('https://accounts.google.com/o/oauth2/v2/auth', strtok($response->headers->get('Location'), '?'));
        $this->assertSame(64, strlen($query['state']));
        $this->assertSame($query['state'], session('google_oauth_state'));
        $this->assertSame(now()->timestamp, session('google_oauth_issued_at'));
        $first = $query['state'];
        $this->get('/auth/google')->assertRedirect();
        $this->assertNotSame($first, session('google_oauth_state'));
        Http::assertNothingSent();
    }

    public function test_missing_empty_mismatched_malformed_or_expired_state_never_reaches_provider(): void
    {
        foreach ([
            [[], []],
            [['state' => ''], ['google_oauth_state' => '', 'google_oauth_issued_at' => now()->timestamp]],
            [[], ['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => now()->timestamp]],
            [['state' => ['nested']], ['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => now()->timestamp]],
            [['state' => str_repeat('x', 64)], ['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => now()->timestamp]],
            [['state' => self::STATE], ['google_oauth_state' => self::STATE]],
            [['state' => self::STATE], ['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => now()->timestamp - 601]],
            [['state' => self::STATE], ['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => now()->timestamp + 60]],
            [['state' => self::STATE], ['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => 'invalid']],
        ] as [$query, $session]) {
            session()->forget(['google_oauth_state', 'google_oauth_issued_at']);
            $this->withSession($session)->get('/auth/google/callback?'.http_build_query($query + ['code' => 'fake-code']))
                ->assertRedirect(route('login'))->assertSessionHasErrors(['email'])
                ->assertSessionMissing('google_oauth_state')->assertSessionMissing('google_oauth_issued_at');
            $this->assertGuest();
            Http::assertNothingSent();
        }
    }

    public function test_provider_denial_consumes_state_and_cannot_be_replayed(): void
    {
        $this->withSession(['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => now()->timestamp])
            ->get('/auth/google/callback?'.http_build_query(['state' => self::STATE, 'error' => 'access_denied']))
            ->assertRedirect(route('login'))->assertSessionHasErrors(['email'])->assertSessionMissing('google_oauth_state');
        $this->get('/auth/google/callback?'.http_build_query(['state' => self::STATE, 'code' => 'fake-code']))
            ->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        Http::assertNothingSent();
    }

    public function test_successful_google_login_is_single_use_and_new_address_is_verified(): void
    {
        $this->performGoogleCallback()->assertRedirect(route('home'))->assertSessionMissing('google_oauth_state')
            ->assertSessionMissing('google_oauth_issued_at');
        $user = User::where('email', 'google-owner@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('google', $user->registration_method);
        $this->assertSame('stable-google-identity', $user->provider_id);
        $this->assertSame(['customer'], json_decode($user->role, true));
        $this->assertNotEmpty($user->password);
        $this->assertFalse(Hash::needsRehash($user->password));
        Http::assertSentCount(2);
        Auth::logout();
        $this->get('/auth/google/callback?'.http_build_query(['state' => self::STATE, 'code' => 'fake-code']))
            ->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        $this->assertGuest();
        Http::assertSentCount(2);
    }

    public function test_same_email_local_account_is_not_silently_linked_or_authenticated(): void
    {
        $referrer = $this->user();
        $planted = $this->user(['email' => 'google-owner@example.test']);
        $planted->forceFill(['referred_by' => $referrer->id])->save();
        Referral::create(['referrer_id' => $referrer->id, 'referred_id' => $planted->id, 'status' => 'pending']);
        $token = $planted->createToken('preexisting-local-token')->plainTextToken;
        $password = $planted->password;
        $this->performGoogleCallback()->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        $this->assertGuest();
        $planted->refresh();
        $this->assertNull($planted->provider);
        $this->assertNull($planted->provider_id);
        $this->assertNull($planted->email_verified_at);
        $this->assertSame($password, $planted->password);
        $this->assertSame($referrer->id, $planted->referred_by);
        $this->assertNotNull(\Laravel\Sanctum\PersonalAccessToken::findToken($token));
        $this->assertStringContainsString('Forgot password', session('errors')->first('email'));
        $this->assertSame(2, User::count());
    }

    public function test_used_state_is_rejected_even_if_a_parallel_request_loaded_the_old_session(): void
    {
        $this->performGoogleCallback()->assertRedirect(route('home'));
        Http::assertSentCount(2);
        Auth::logout();
        $this->withSession(['google_oauth_state' => self::STATE, 'google_oauth_issued_at' => now()->timestamp])
            ->get('/auth/google/callback?'.http_build_query(['state' => self::STATE, 'code' => 'a-second-code']))
            ->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        $this->assertGuest();
        Http::assertSentCount(2);
    }

    public function test_verified_local_account_still_requires_existing_account_ownership_to_link(): void
    {
        $local = $this->user(['email' => 'GOOGLE-OWNER@example.test']);
        $local->forceFill(['email_verified_at' => now()])->save();
        $this->performGoogleCallback()->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        $this->assertGuest();
        $this->assertNull($local->fresh()->provider_id);
        $this->assertSame(1, User::count());
    }

    public function test_existing_google_identity_keeps_login_when_automatic_registration_is_disabled(): void
    {
        $user = $this->user(['email' => 'google-owner@example.test', 'provider' => 'google',
            'provider_id' => 'stable-google-identity', 'registration_method' => 'google']);
        $password = $user->password;
        AuthConfig::save(['auto_register_google' => false]);
        $this->performGoogleCallback()->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame($password, $user->fresh()->password);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_provider_subject_is_required_and_never_matches_a_null_identity(): void
    {
        $user = $this->user(['provider' => 'google', 'provider_id' => null]);
        foreach ([null, '', ['unexpected'], "unsafe\nidentity", ' padded ', str_repeat('a', 256)] as $subject) {
            $this->performGoogleCallback(['sub' => $subject])->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
            $this->assertGuest();
            $this->assertNull($user->fresh()->provider_id);
            $this->assertSame(1, User::count());
        }
    }

    public function test_unverified_or_malformed_provider_email_never_creates_an_account(): void
    {
        foreach ([['email_verified' => false], ['email_verified' => 'false'], ['email' => null], ['email' => ['invalid']], ['email' => 'not-an-email']] as $profile) {
            $this->performGoogleCallback($profile)->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
            $this->assertGuest();
            $this->assertSame(0, User::count());
        }
    }

    public function test_provider_identity_cannot_overwrite_another_accounts_email_or_subject(): void
    {
        $google = $this->user(['provider' => 'google', 'provider_id' => 'stable-google-identity', 'registration_method' => 'google']);
        $other = $this->user(['email' => 'google-owner@example.test']);
        $this->performGoogleCallback()->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        $this->assertGuest();
        $this->assertNull($other->fresh()->provider_id);
        $this->assertNotSame($other->email, $google->fresh()->email);
    }

    public function test_blocked_google_accounts_and_disabled_auto_registration_cannot_sign_in(): void
    {
        AuthConfig::save(['auto_register_google' => false]);
        $this->performGoogleCallback()->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        $this->assertSame(0, User::count());
        $user = $this->user(['provider' => 'google', 'provider_id' => 'stable-google-identity']);
        $user->forceFill(['is_blocked' => true])->save();
        $this->performGoogleCallback(['email' => $user->email])->assertRedirect(route('login'))->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_token_failure_malformed_json_and_connection_errors_fail_closed(): void
    {
        foreach ([
            Http::response(['error' => 'invalid_grant'], 400),
            Http::response('not json', 200),
            Http::response(['access_token' => ['invalid']], 200),
            Http::failedConnection(),
        ] as $response) {
            Http::swap(new \Illuminate\Http\Client\Factory);
            Http::preventStrayRequests();
            Http::fake(['https://oauth2.googleapis.com/token' => $response]);
            $state = Str::random(64);
            $this->withSession(['google_oauth_state' => $state, 'google_oauth_issued_at' => now()->timestamp])
                ->get('/auth/google/callback?'.http_build_query(['state' => $state, 'code' => 'fake-code']))
                ->assertRedirect(route('login'))->assertSessionHasErrors(['email'])->assertSessionMissing('google_oauth_state');
            $this->assertGuest();
            $this->assertSame(0, User::count());
        }
    }

    public function test_provider_identity_uniqueness_is_enforced_by_database(): void
    {
        $this->user(['provider' => 'google', 'provider_id' => 'stable-google-identity']);
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $this->user(['provider' => 'google', 'provider_id' => 'stable-google-identity']);
    }

    public function test_provider_migration_refuses_ambiguous_existing_identities_without_merging(): void
    {
        $migration = require base_path('database/migrations/2026_11_13_000003_unique_external_provider_identity.php');
        $migration->down();
        $this->user(['provider' => 'google', 'provider_id' => 'ambiguous-identity']);
        $this->user(['provider' => 'google', 'provider_id' => 'ambiguous-identity']);
        try {
            $migration->up();
            $this->fail('Ambiguous provider identities must be reviewed before adding the constraint.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('No accounts have been merged or deleted', $e->getMessage());
        }
        $this->assertSame(2, User::count());
    }
}

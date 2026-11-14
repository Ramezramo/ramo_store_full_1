<?php

namespace Tests\Feature;

use App\Http\Middleware\BindSessionCredentials;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Support\UsesIsolatedSecurityDatabase;
use Tests\TestCase;

class SessionCredentialSecurityTest extends TestCase
{
    use UsesIsolatedSecurityDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware(['web', 'auth'])->get('/__security/private', fn () => response()->json(['id' => auth()->id()]));
    }

    private function customer(): User
    {
        return User::create(['name' => 'Session fixture', 'email' => Str::uuid().'@example.test',
            'password' => 'test-password-123', 'phone' => '', 'shipping' => '[]']);
    }

    private function login(User $user): void
    {
        $this->postJson('/login', ['email' => $user->email, 'password' => 'test-password-123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertIsString(session(BindSessionCredentials::sessionKey('web')));
    }

    public function test_a_real_password_login_installs_and_keeps_its_session_binding(): void
    {
        $user = $this->customer(); $this->login($user);
        $this->getJson('/__security/private')->assertOk()->assertJsonPath('id', $user->id);
    }

    public function test_an_old_browser_session_is_rejected_after_credential_recovery(): void
    {
        $user = $this->customer(); $this->login($user);
        User::whereKey($user->id)->update(['password' => Hash::make('recovered-password-456')]);
        $this->getJson('/__security/private')->assertUnauthorized();
        $this->assertGuest();
        $this->postJson('/login', ['email' => $user->email, 'password' => 'recovered-password-456'])->assertRedirect();
        $this->getJson('/__security/private')->assertOk();
    }

    public function test_a_legacy_session_without_credential_binding_must_authenticate_again(): void
    {
        $user = $this->customer();
        $this->withSession([Auth::guard('web')->getName() => $user->id]);
        $this->getJson('/__security/private')->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_a_copied_binding_from_another_account_cannot_be_used(): void
    {
        $user = $this->customer(); $this->login($user);
        $other = $this->customer();
        $this->withSession([Auth::guard('web')->getName() => $other->id]);
        $this->getJson('/__security/private')->assertUnauthorized();
    }

    public function test_a_blocked_account_cannot_keep_using_a_previously_valid_session(): void
    {
        $user = $this->customer(); $this->login($user);
        User::whereKey($user->id)->update(['is_blocked' => true]);
        $this->getJson('/__security/private')->assertUnauthorized();
    }
}

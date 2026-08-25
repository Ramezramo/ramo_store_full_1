<?php

namespace Tests\Feature;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationFlowFixesTest extends TestCase
{
    private array $userIds = [];
    private array $otpPhones = [];

    protected function tearDown(): void
    {
        if ($this->otpPhones) {
            OtpVerification::query()->whereIn('phone', $this->otpPhones)->delete();
        }
        if ($this->userIds) {
            DB::table('users')->whereIn('id', $this->userIds)->delete();
        }

        parent::tearDown();
    }

    public function test_authenticated_user_cannot_start_or_verify_an_otp_session(): void
    {
        $user = $this->makeUser('otp-guard-'.uniqid().'@example.test', '+201000000101');

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($user)
            ->postJson(route('auth.send-otp'), ['phone' => '01000000101'])
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are already signed in.');

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($user)
            ->postJson(route('auth.verify-otp'), ['phone' => '01000000101', 'otp' => '123456'])
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are already signed in.');
    }

    public function test_otp_endpoint_rejects_non_egyptian_mobile_input_after_normalization(): void
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->postJson(route('auth.send-otp'), ['phone' => '9876543210']);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Please enter a valid phone number.');
    }

    public function test_email_registration_rejects_a_phone_that_collides_after_normalization(): void
    {
        $this->makeUser('existing-phone-'.uniqid().'@example.test', '+201000000102');

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/register', [
                'first_name' => 'New',
                'last_name' => 'Customer',
                'email' => 'new-phone-'.uniqid().'@example.test',
                'phone' => '01000000102',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors(['phone']);
    }

    public function test_phone_otp_account_gets_clear_email_password_guidance(): void
    {
        $user = $this->makeUser('otp-account-'.uniqid().'@example.test', '+201000000103', [
            'registration_method' => 'phone_otp',
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->withSession(['locale' => 'en'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors(['email' => 'This account uses phone sign-in. Use the phone OTP option, or set a password from your account profile first.']);
    }

    public function test_phone_profile_update_rejects_another_users_phone(): void
    {
        $owner = $this->makeUser('profile-owner-'.uniqid().'@example.test', '+201000000104');
        $other = $this->makeUser('profile-other-'.uniqid().'@example.test', '+201000000105');

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->actingAs($owner)
            ->post(route('account.profile.update'), [
                'first_name' => 'Owner',
                'last_name' => 'Updated',
                'email' => $owner->email,
                'phone' => '01000000105',
            ])
            ->assertSessionHasErrors(['phone']);

        $this->assertSame('+201000000104', $owner->fresh()->phone);
        $this->assertSame('+201000000105', $other->fresh()->phone);
    }

    private function makeUser(string $email, string $phone, array $attributes = []): User
    {
        $user = new User(array_merge([
            'name' => 'Registration Test User',
            'first_name' => 'Registration',
            'last_name' => 'Tester',
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make('secret123'),
            'shipping' => json_encode([]),
            'registration_method' => 'email_password',
        ], $attributes));
        $user->role = 'normal_user';
        $user->capabilities = json_encode(['customer' => true]);
        $user->save();
        $this->userIds[] = (int) $user->id;

        return $user->fresh();
    }
}

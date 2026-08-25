<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginFlowFixesTest extends TestCase
{
    public function test_login_and_password_routes_use_the_dedicated_rate_limiters(): void
    {
        $request = Request::create('/login', 'POST', [
            'email' => 'target-'.uniqid().'@example.test',
        ], server: ['REMOTE_ADDR' => '198.51.100.50']);

        $expectedMaxAttempts = [
            'login-account' => 10,
            'password-forgot' => 5,
            'password-reset' => 10,
        ];

        foreach ($expectedMaxAttempts as $name => $maxAttempts) {
            $limiter = RateLimiter::limiter($name);
            $this->assertNotNull($limiter, "The {$name} limiter must be registered.");
            $this->assertSame($maxAttempts, $limiter($request)->maxAttempts);
        }

        $this->assertStringContainsString('login.account.', $this->limiterKey('login-account', $request));
        $this->assertStringNotContainsString('198.51.100.50', $this->limiterKey('login-account', $request));
        $this->assertStringContainsString('password.forgot.198.51.100.50.', $this->limiterKey('password-forgot', $request));
        $this->assertStringStartsWith('password.reset.198.51.100.50', $this->limiterKey('password-reset', $request));

        $loginRoute = Route::getRoutes()->match(Request::create('/login', 'POST'));
        $forgotRoute = Route::getRoutes()->match(Request::create('/forgot-password', 'POST'));
        $resetRoute = Route::getRoutes()->match(Request::create('/reset-password', 'POST'));

        $this->assertContains('throttle:login-web', $loginRoute->middleware());
        $this->assertContains('throttle:login-account', $loginRoute->middleware());
        $this->assertContains('throttle:password-forgot', $forgotRoute->middleware());
        $this->assertContains('throttle:password-reset', $resetRoute->middleware());
    }

    public function test_failed_login_does_not_flash_the_plaintext_password(): void
    {
        $email = 'missing-'.uniqid().'@example.test';

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'email' => $email,
                'password' => 'do-not-store-this',
            ])
            ->assertSessionHasErrors(['email'])
            ->assertSessionHas('_old_input.email', $email);

        $this->assertArrayNotHasKey('password', session('_old_input', []));
    }

    public function test_google_callback_checks_email_verification_before_account_lookup(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Web/GoogleAuthController.php'));
        $this->assertIsString($source);

        $verificationCheck = "filter_var(\$profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)";
        $this->assertStringContainsString($verificationCheck, $source);
        $this->assertLessThan(
            strpos($source, "User::where('provider', 'google')"),
            strpos($source, $verificationCheck)
        );
    }

    private function limiterKey(string $name, Request $request): string
    {
        $limit = RateLimiter::limiter($name)($request);
        return (string) $limit->key;
    }
}

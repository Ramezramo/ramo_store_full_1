<?php

namespace Tests\Feature;

use App\Http\Middleware\EnforceHttps;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;

class TransportSecurityPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        app('url')->forceScheme(null);

        parent::tearDown();
    }

    public function test_opt_in_https_policy_forces_absolute_storefront_urls_to_https(): void
    {
        config([
            'app.force_https' => true,
            'app.url' => 'http://store.example.test',
        ]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', route('home'));
        $this->assertStringStartsWith('https://', route('sitemap'));
        $this->assertStringEndsWith('/sitemap.xml', route('sitemap'));
    }

    public function test_enabled_https_policy_permanently_redirects_plain_http_requests(): void
    {
        config(['app.force_https' => true]);

        $request = Request::create('http://store.example.test/health', 'GET');
        $response = app(EnforceHttps::class)->handle($request, fn () => response('ok'));

        $this->assertSame(308, $response->getStatusCode());
        $this->assertStringStartsWith('https://', (string) $response->headers->get('Location'));
        $this->assertStringEndsWith('/health', (string) $response->headers->get('Location'));
    }

    public function test_auth_state_cookie_is_secure_when_secure_session_policy_is_enabled(): void
    {
        config(['session.secure' => true]);

        $response = $this->get('/');
        $cookie = collect($response->headers->getCookies())
            ->first(fn ($item) => $item->getName() === 'ramo_auth_flag');

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isSecure());
        $this->assertFalse($cookie->isHttpOnly());
        $this->assertSame('lax', strtolower((string) $cookie->getSameSite()));
    }
}

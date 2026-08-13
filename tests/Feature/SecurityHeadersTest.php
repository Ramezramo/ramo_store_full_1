<?php

namespace Tests\Feature;

use Illuminate\Http\Middleware\TrustProxies;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.force_https' => false,
            'trustedproxy.proxies' => null,
        ]);
        TrustProxies::flushState();
    }

    protected function tearDown(): void
    {
        TrustProxies::flushState();

        parent::tearDown();
    }
    public function test_storefront_responses_include_baseline_security_headers(): void
    {
        $response = $this->get('http://localhost/');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=()')
            ->assertHeader('Content-Security-Policy-Report-Only', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; upgrade-insecure-requests")
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_only_over_https(): void
    {
        $response = $this->get('https://localhost/');

        $response->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_untrusted_forwarded_https_header_does_not_enable_secure_response_behavior(): void
    {
        $response = $this->withServerVariables([
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '203.0.113.200',
        ])->get('http://localhost/');

        $response->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }
}

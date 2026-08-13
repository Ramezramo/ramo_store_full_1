<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_storefront_responses_include_baseline_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=()')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_only_over_https(): void
    {
        $response = $this->get('https://localhost/');

        $response->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class PreventPageCachingTest extends TestCase
{
    public function test_catalog_home_uses_a_private_five_minute_cache_in_debug_mode(): void
    {
        config(['app.debug' => true]);

        $response = $this->get('/')->assertOk();

        $this->assertStringContainsString('max-age=300', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('Cookie', (string) $response->headers->get('Vary'));
    }

    public function test_development_no_cache_policy_is_not_applied_when_debug_is_disabled(): void
    {
        config(['app.debug' => false]);

        $response = $this->get('/')->assertOk();

        $this->assertStringNotContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control')
        );
    }
}

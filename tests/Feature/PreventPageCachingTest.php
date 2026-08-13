<?php

namespace Tests\Feature;

use Tests\TestCase;

class PreventPageCachingTest extends TestCase
{
    public function test_html_is_not_cached_while_debug_is_enabled(): void
    {
        config(['app.debug' => true]);

        $response = $this->get('/')->assertOk();

        $this->assertStringContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control')
        );
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

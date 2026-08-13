<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class StorefrontCacheHeadersTest extends TestCase
{
    public function test_anonymous_public_catalog_page_has_short_shared_cache_headers_in_production_mode(): void
    {
        Config::set('app.debug', false);

        $response = $this->get(route('shop'))
            ->assertOk()
            ->assertHeader('Vary', 'Cookie, Accept-Language');

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=60', $cacheControl);
        $this->assertStringContainsString('s-maxage=300', $cacheControl);
        $this->assertStringContainsString('stale-while-revalidate=60', $cacheControl);
    }

    public function test_personalized_cart_page_is_never_stored_in_production_mode(): void
    {
        Config::set('app.debug', false);

        $this->get(route('cart'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_debug_mode_keeps_visual_qa_responses_non_cacheable(): void
    {
        Config::set('app.debug', true);

        $response = $this->get(route('shop'))
            ->assertOk();

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
    }
}

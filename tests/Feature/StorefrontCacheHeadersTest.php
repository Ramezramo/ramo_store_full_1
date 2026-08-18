<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class StorefrontCacheHeadersTest extends TestCase
{
    public function test_shop_page_is_never_stored_in_production_mode(): void
    {
        Config::set('app.debug', false);

        $this->get(route('shop'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');
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
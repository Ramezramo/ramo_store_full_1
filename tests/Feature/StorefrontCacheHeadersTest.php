<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class StorefrontCacheHeadersTest extends TestCase
{
    public function test_shop_page_is_private_and_cached_for_five_minutes(): void
    {
        Config::set('app.debug', false);

        $response = $this->get(route('shop'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, must-revalidate, private');
        $this->assertContains('Cookie', $response->baseResponse->getVary());
        $this->assertContains('Accept-Language', $response->baseResponse->getVary());
    }

    public function test_personalized_cart_page_is_never_stored_in_production_mode(): void
    {
        Config::set('app.debug', false);

        $this->get(route('cart'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_filtered_shop_responses_are_not_cacheable_in_debug_mode(): void
    {
        Config::set('app.debug', true);

        $response = $this->get(route('shop', ['search' => 'test']))
            ->assertOk();

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
    }
}

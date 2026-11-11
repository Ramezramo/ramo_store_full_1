<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogKeepAliveTest extends TestCase
{
    public function test_home_and_shop_render_independent_catalog_views(): void
    {
        foreach (['/' => '/', '/shop' => '/shop'] as $url => $path) {
            $this->get($url)->assertOk()
                ->assertSee('data-catalog-view="'.$path.'"', false)
                ->assertSee('data-catalog-active="1"', false)
                ->assertSee('catalog-navigation.js')
                ->assertSee('id="storefront-page-scripts"', false);
        }
    }

    public function test_other_storefront_pages_can_reach_the_retained_catalog_and_sensitive_flows_stay_native(): void
    {
        $this->get('/cart')->assertOk()->assertSee('data-catalog-view="/cart"', false)->assertSee('catalog-navigation.js');
        $this->get('/login')->assertOk()->assertDontSee('data-catalog-view=', false)->assertDontSee('catalog-navigation.js');
        $this->get('/shop?search=sample')->assertOk()->assertDontSee('data-catalog-view=', false);
    }

    public function test_authenticated_account_destinations_announce_when_their_markup_is_ready_and_remain_private(): void
    {
        DB::beginTransaction();
        try {
            $user = User::factory()->create();
            foreach (['/account', '/account/profile', '/account/notifications'] as $url) {
                $response = $this->actingAs($user)->get($url)->assertOk()
                    ->assertSee('id="mob-nav"', false)
                    ->assertSee("window.parent.postMessage({ type: 'storefront:ready' }", false);
                $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            }
        } finally {
            DB::rollBack();
        }
    }
}

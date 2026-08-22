<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileShopFiltersTest extends TestCase
{
    public function test_arabic_shop_keeps_the_complete_filter_drawer_available_on_mobile(): void
    {
        $response = $this->withSession(['locale' => 'ar'])
            ->get(route('shop'));

        $response->assertOk()
            ->assertSee('class="page shop-page"', false)
            ->assertSee('id="shop-sidebar"', false)
            ->assertSee('id="widget-categories"', false)
            ->assertSee('id="widget-brands"', false)
            ->assertSee('id="widget-sort"', false)
            ->assertSee('.shop-page .sidebar.mobile-open', false)
            ->assertSee('max-height: calc(100dvh - 178px)', false)
            ->assertSee('overflow-y: auto', false)
            ->assertSee('ترتيب حسب', false);
    }
}

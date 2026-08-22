<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileShopFiltersTest extends TestCase
{
    public function test_arabic_shop_exposes_an_accessible_mobile_filter_alert_with_price_controls(): void
    {
        $response = $this->withSession(['locale' => 'ar'])
            ->get(route('shop'));

        $response->assertOk()
            ->assertSee('class="page shop-page"', false)
            ->assertSee('id="shop-filter-alert"', false)
            ->assertSee('aria-hidden="false"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('id="shop-filter-title"', false)
            ->assertSee('id="shop-filter-close"', false)
            ->assertSee('id="shop-sidebar"', false)
            ->assertSee('id="widget-categories"', false)
            ->assertSee('id="widget-brands"', false)
            ->assertSee('id="widget-price"', false)
            ->assertSee('id="widget-sort"', false)
            ->assertSee('id="shop-price-form"', false)
            ->assertSee('name="min_price"', false)
            ->assertSee('name="max_price"', false)
            ->assertSee('class="shop-filter-panel-actions"', false)
            ->assertSee('class="shop-filter-apply"', false)
            ->assertSee('class="shop-filter-clear"', false)
            ->assertSee('.shop-page .shop-filter-alert.open', false)
            ->assertSee('z-index: 10050', false)
            ->assertSee('bottom: calc(var(--mobile-nav-height', false)
            ->assertSee('max-height: calc(100dvh - var(--mobile-nav-height', false)
            ->assertSee('overflow-y: auto', false)
            ->assertSee('body.shop-filter-open', false)
            ->assertSee('ترتيب حسب', false)
            ->assertSee('نطاق السعر', false);
    }
}

<?php

namespace Tests\Unit;

use App\Services\TimelineLayoutService;
use PHPUnit\Framework\TestCase;

class TimelineLayoutServiceTest extends TestCase
{
    public function test_all_products_is_visible_unique_and_always_last(): void
    {
        $sections = TimelineLayoutService::ensureAllProductsLast([
            ['layout' => 'bannerImage'],
            ['layout' => 'allProducts', 'hidden' => true, 'productsPerPage' => 60],
            ['layout' => 'divider'],
            ['layout' => 'allProducts', 'headerText' => 'Duplicate'],
        ], 'ar');

        self::assertSame(['bannerImage', 'divider', 'allProducts'], array_column($sections, 'layout'));
        self::assertSame('منتجات قد تعجبك', $sections[2]['headerText']);
        self::assertSame(48, $sections[2]['productsPerPage']);
        self::assertArrayNotHasKey('hidden', $sections[2]);
    }

    public function test_existing_custom_title_and_page_size_are_preserved(): void
    {
        $sections = TimelineLayoutService::ensureAllProductsLast([
            ['layout' => 'allProducts', 'headerText' => 'Shop Everything', 'productsPerPage' => 24],
            ['layout' => 'newsletter'],
        ], 'en');

        self::assertSame('Shop Everything', $sections[1]['headerText']);
        self::assertSame(24, $sections[1]['productsPerPage']);
    }

    public function test_default_title_is_localized_for_both_storefront_languages(): void
    {
        $english = TimelineLayoutService::ensureAllProductsLast([
            ['layout' => 'allProducts', 'headerText' => 'All Products'],
        ], 'en');
        $arabic = TimelineLayoutService::ensureAllProductsLast([
            ['layout' => 'allProducts', 'headerText' => 'Products you may like'],
        ], 'ar');

        self::assertSame('Products you may like', $english[0]['headerText']);
        self::assertSame('منتجات قد تعجبك', $arabic[0]['headerText']);
    }

    public function test_products_per_page_defaults_to_ten_and_clamps_lower_values(): void
    {
        $default = TimelineLayoutService::ensureAllProductsLast([
            ['layout' => 'bannerImage'],
        ], 'en');
        $belowMinimum = TimelineLayoutService::ensureAllProductsLast([
            ['layout' => 'allProducts', 'productsPerPage' => 4],
        ], 'en');

        self::assertSame(10, $default[1]['productsPerPage']);
        self::assertSame(5, $belowMinimum[0]['productsPerPage']);
    }
}

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
        self::assertSame('كل المنتجات', $sections[2]['headerText']);
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
}

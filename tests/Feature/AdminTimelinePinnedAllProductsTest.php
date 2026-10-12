<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTimelinePinnedAllProductsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_saving_timeline_keeps_exactly_one_visible_all_products_widget_at_the_end(): void
    {
        $this->withoutMiddleware()
            ->postJson(route('admin.timeline.save'), [
                'lang' => 'zz',
                'payload' => json_encode([
                    ['layout' => 'allProducts', 'hidden' => true, 'productsPerPage' => 24],
                    ['layout' => 'bannerImage'],
                    ['layout' => 'allProducts', 'productsPerPage' => 48],
                    ['layout' => 'divider'],
                ], JSON_THROW_ON_ERROR),
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $value = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->where('lang', 'zz')
            ->value('value');
        $sections = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['bannerImage', 'divider', 'allProducts'], array_column($sections, 'layout'));
        self::assertSame(24, $sections[2]['productsPerPage']);
        self::assertArrayNotHasKey('hidden', $sections[2]);
    }
}

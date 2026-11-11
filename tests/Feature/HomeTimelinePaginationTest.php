<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeTimelinePaginationTest extends TestCase
{
    public function test_home_loads_only_one_batch_and_preview_keeps_all_sections(): void
    {
        DB::beginTransaction();
        try {
            $sections = [
                ['layout' => 'announcement', 'message' => 'Announcement test'],
                ['layout' => 'promoBlock', 'headline' => 'First batch widget'],
                ['layout' => 'divider'],
                ['layout' => 'spacer', 'height' => 5],
                ['layout' => 'statsBar', 'items' => [['key' => 'orders', 'label' => 'Batch orders']]],
                ['layout' => 'promoBlock', 'headline' => 'Hidden test widget', 'hidden' => true],
                ['layout' => 'allProducts', 'headerText' => 'Final products test'],
            ];
            DB::table('app_configs')->where('config_key', 'horizon_layout')->where('lang', 'en')
                ->update(['value' => json_encode($sections)]);
            DB::enableQueryLog();
            DB::flushQueryLog();
            $this->withSession(['locale' => 'en'])->get(route('home'))->assertOk()
                ->assertSee('Announcement test')->assertSee('First batch widget')
                ->assertDontSee('Batch orders')->assertDontSee('Final products test')
                ->assertSee('timeline-loader');
            $queries = array_column(DB::getQueryLog(), 'query');
            $this->assertFalse(collect($queries)->contains(fn ($sql) => str_contains(strtolower($sql), 'count(*)') && str_contains(strtolower($sql), '"orders"')));
            DB::disableQueryLog();

            $this->getJson(route('home.sections', ['page' => 2]))->assertOk()
                ->assertJsonPath('hasMore', false)->assertJsonPath('nextPage', null)
                ->assertJsonFragment(['hasMore' => false])
                ->assertSee('Batch orders')->assertSee('Final products test')->assertDontSee('Hidden test widget');
            $this->get(route('home', ['timeline_page' => 2]))->assertOk()->assertSee('Final products test');
            $this->get(route('home', ['tl_preview' => 1]))->assertOk()->assertSee('First batch widget')->assertSee('Final products test');
            $this->getJson(route('home.sections', ['page' => 0]))->assertUnprocessable();
        } finally {
            DB::disableQueryLog();
            DB::rollBack();
        }
    }
}

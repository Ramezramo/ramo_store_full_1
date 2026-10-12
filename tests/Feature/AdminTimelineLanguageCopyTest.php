<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTimelineLanguageCopyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_timeline_editor_lists_other_languages_that_have_saved_layouts(): void
    {
        DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->whereIn('lang', ['ar', 'de', 'en'])
            ->delete();

        foreach ([
            'ar' => [['layout' => 'arabic-section']],
            'de' => [['layout' => 'german-section']],
            'en' => [['layout' => 'english-section']],
        ] as $language => $sections) {
            DB::table('app_configs')->insert([
                'config_key' => 'horizon_layout',
                'config_group' => 'layout',
                'lang' => $language,
                'value' => json_encode($sections, JSON_THROW_ON_ERROR),
                'is_public' => true,
                'sort_order' => 0,
                'updated_at' => now(),
            ]);
        }

        $this->withoutMiddleware()
            ->get(route('admin.timeline', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('id="copySourceLang"', false)
            ->assertSee('English (EN)')
            ->assertSee('German (DE)')
            ->assertSee('Copy into AR')
            ->assertSee('english-section')
            ->assertSee('german-section')
            ->assertDontSee('<option value="ar">', false);
    }
}

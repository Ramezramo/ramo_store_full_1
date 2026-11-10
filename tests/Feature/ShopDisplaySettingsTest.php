<?php

namespace Tests\Feature;

use App\Helpers\ShopDisplayConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShopDisplaySettingsTest extends TestCase
{
    public function test_admin_settings_control_the_shop_category_row_and_filters(): void
    {
        DB::beginTransaction();

        try {
            DB::table('app_configs')->where('config_key', ShopDisplayConfig::KEY)->whereNull('lang')->delete();
            $admin = User::create([
                'name' => 'Shop Settings Admin',
                'email' => 'shop-settings-'.uniqid().'@ramostore.local',
                'password' => 'temporary-test-password',
            ]);
            DB::table('users')->where('id', $admin->id)->update(['role' => json_encode(['admin'])]);
            $admin->refresh();

            $categoryId = DB::table('categories2')->insertGetId([
                'name' => 'Selected shop settings category',
                'slug' => 'shop-settings-'.uniqid(),
                'parent' => 0,
            ]);
            $settings = [
                'category_layout' => 'wrap',
                'category_limit' => 3,
                'category_ids' => [$categoryId],
                'filters_enabled' => '1',
                'filter_price' => '0',
                'filter_categories' => '1',
                'filter_brands' => '0',
                'filter_sort' => '1',
            ];

            $this->actingAs($admin)->post(route('admin.configs.shop-display'), $settings)
                ->assertRedirect(route('admin.configs', ['group' => 'layout']));
            $this->get(route('admin.configs'))->assertOk()
                ->assertSee('class="sidebar-logo"', false)
                ->assertSee('class="nav-item active"', false)
                ->assertSee('App Configuration')
                ->assertSee('Configuration groups')
                ->assertSee('Layouts')
                ->assertSee('Languages')
                ->assertSee('Theme')
                ->assertSee('API Preview')
                ->assertSee('Shop Categories &amp; Filters', false)
                ->assertSee('Selected shop settings category');
            $this->get(route('admin.configs', ['group' => 'layout']))->assertOk()
                ->assertSee('id="config-list"', false)
                ->assertSee('Layouts</a>', false);
            $response = $this->get(route('shop'))->assertOk()
                ->assertSee('shop-category-chips--wrap', false)
                ->assertSee('data-filter-price="0"', false)
                ->assertSee('data-filter-brands="0"', false)
                ->assertSee('id="shop-filter-btn"', false);
            $html = $response->getContent();
            preg_match('/<nav class="shop-category-chips.*?<\/nav>/s', $html, $row);
            $this->assertNotEmpty($row);
            $this->assertStringContainsString('Selected shop settings category', $row[0]);
            $this->assertSame(2, substr_count($row[0], '<a class="shop-category-chip'));

            $settings['category_layout'] = 'hidden';
            $settings['filters_enabled'] = '0';
            $this->post(route('admin.configs.shop-display'), $settings)->assertRedirect();
            $this->get(route('shop'))->assertOk()
                ->assertDontSee('<nav class="shop-category-chips', false)
                ->assertDontSee('id="shop-filter-btn"', false)
                ->assertDontSee('id="shop-filter-alert"', false);
        } finally {
            DB::rollBack();
        }
    }
}

<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ShopDisplayConfig
{
    public const KEY = 'shop_display_settings';

    public const DEFAULTS = [
        'category_layout' => 'scroll',
        'category_limit' => 5,
        'category_ids' => [],
        'filters_enabled' => true,
        'filter_price' => true,
        'filter_categories' => true,
        'filter_brands' => true,
        'filter_sort' => true,
    ];

    public static function get(): array
    {
        $value = DB::table('app_configs')->where('config_key', self::KEY)->whereNull('lang')->value('value');
        $saved = is_string($value) ? json_decode($value, true) : null;
        $settings = array_replace(self::DEFAULTS, is_array($saved) ? array_intersect_key($saved, self::DEFAULTS) : []);
        $settings['category_layout'] = in_array($settings['category_layout'], ['scroll', 'wrap', 'hidden'], true)
            ? $settings['category_layout'] : self::DEFAULTS['category_layout'];
        $settings['category_limit'] = max(1, min(30, (int) $settings['category_limit']));
        $settings['category_ids'] = is_array($settings['category_ids'])
            ? array_values(array_unique(array_filter(array_map('intval', $settings['category_ids']), fn ($id) => $id > 0)))
            : [];
        foreach (['filters_enabled', 'filter_price', 'filter_categories', 'filter_brands', 'filter_sort'] as $key) {
            $settings[$key] = filter_var($settings[$key], FILTER_VALIDATE_BOOLEAN);
        }

        return $settings;
    }
}

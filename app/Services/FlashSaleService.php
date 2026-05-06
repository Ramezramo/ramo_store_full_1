<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FlashSaleService
{
    private static array $cache = [];

    public static function getActive(string $lang = 'en'): ?object
    {
        if (array_key_exists($lang, self::$cache)) {
            return self::$cache[$lang];
        }

        $row = DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->where('lang', $lang)
            ->first();

        if (!$row) {
            $row = DB::table('app_configs')
                ->where('config_key', 'horizon_layout')
                ->first();
        }

        $sections = $row ? (json_decode($row->value, true) ?? []) : [];
        $nowMs    = (int) (microtime(true) * 1000);

        foreach ($sections as $sec) {
            if (($sec['layout'] ?? '') !== 'flash') continue;
            if ($sec['hidden'] ?? false) continue;

            $endTime = (int) ($sec['endTime'] ?? 0);
            if ($endTime > 0 && $endTime <= $nowMs) continue;

            self::$cache[$lang] = (object) [
                'discount'         => (float) ($sec['discount'] ?? 20),
                'applyTo'          => $sec['applyTo'] ?? 'all',
                'targetCategories' => array_map('intval', (array) ($sec['targetCategories'] ?? [])),
                'targetProductIds' => array_map('intval', (array) ($sec['targetProductIds'] ?? [])),
                'endTime'          => $endTime,
            ];
            return self::$cache[$lang];
        }

        self::$cache[$lang] = null;
        return null;
    }
}

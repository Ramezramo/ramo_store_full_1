<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class TimelineLayoutService
{
    public const ALL_PRODUCTS_LAYOUT = 'allProducts';

    private const CACHE_KEY = 'homepage:timeline-layouts';

    public static function sections(string $lang): array
    {
        $layouts = Cache::remember(self::CACHE_KEY, now()->addMinutes(12), fn () => DB::table('app_configs')
            ->where('config_key', 'horizon_layout')
            ->orderBy('id')
            ->get(['lang', 'value']));

        $row = $layouts->firstWhere('lang', '===', $lang) ?? $layouts->first();
        $sections = $row ? json_decode($row->value, true) : [];

        return is_array($sections) ? $sections : [];
    }

    public static function forgetCachedLayouts(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function ensureAllProductsLast(array $sections, string $lang = 'en'): array
    {
        $pinnedSection = null;
        $normalizedSections = [];

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            if (($section['layout'] ?? null) === self::ALL_PRODUCTS_LAYOUT) {
                $pinnedSection ??= $section;
                continue;
            }

            $normalizedSections[] = $section;
        }

        $pinnedSection ??= [];
        $pinnedSection['layout'] = self::ALL_PRODUCTS_LAYOUT;

        $storedTitle = $pinnedSection['headerText'] ?? '';
        $storedTitle = is_scalar($storedTitle) ? trim((string) $storedTitle) : '';
        $isArabic = strtolower($lang) === 'ar';
        $isDefaultTitle = $storedTitle === ''
            || in_array($storedTitle, ['كل المنتجات', 'منتجات قد تعجبك'], true)
            || in_array(strtolower($storedTitle), ['all products', 'products you may like'], true);

        if ($isDefaultTitle) {
            $pinnedSection['headerText'] = $isArabic ? 'منتجات قد تعجبك' : 'Products you may like';
        }

        $perPage = $pinnedSection['productsPerPage'] ?? 10;
        $perPage = is_numeric($perPage) ? (int) $perPage : 10;
        $pinnedSection['productsPerPage'] = max(5, min(48, $perPage));

        unset($pinnedSection['hidden']);

        $normalizedSections[] = $pinnedSection;

        return $normalizedSections;
    }
}

<?php

namespace App\Services;

final class TimelineLayoutService
{
    public const ALL_PRODUCTS_LAYOUT = 'allProducts';

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

        $perPage = $pinnedSection['productsPerPage'] ?? 5;
        $perPage = is_numeric($perPage) ? (int) $perPage : 5;
        $pinnedSection['productsPerPage'] = max(5, min(48, $perPage));

        unset($pinnedSection['hidden']);

        $normalizedSections[] = $pinnedSection;

        return $normalizedSections;
    }
}

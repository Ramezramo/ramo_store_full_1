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

        if ($storedTitle === '' || ($isArabic && strcasecmp($storedTitle, 'All Products') === 0)) {
            $pinnedSection['headerText'] = $isArabic ? 'كل المنتجات' : 'All Products';
        }

        $perPage = $pinnedSection['productsPerPage'] ?? 16;
        $perPage = is_numeric($perPage) ? (int) $perPage : 16;
        $pinnedSection['productsPerPage'] = max(4, min(48, $perPage));

        unset($pinnedSection['hidden']);

        $normalizedSections[] = $pinnedSection;

        return $normalizedSections;
    }
}

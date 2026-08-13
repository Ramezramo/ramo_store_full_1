<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Customer-facing labels that are stored as legacy English taxonomy or variation
 * values. This intentionally changes presentation only: it does not mutate
 * merchant-entered catalog data, product identity, customer account names, or
 * seller/administrator interfaces.
 */
final class StorefrontLabels
{
    /** @var array<string, string> */
    private const ARABIC_CATEGORIES = [
        'uncategorized' => 'غير مصنف',
        'clothing' => 'ملابس',
        'bags' => 'شنط',
        'bag' => 'شنطة',
        'men' => 'رجالي',
        'jeans man' => 'جينز رجالي',
        'shirts' => 'قمصان',
        't-shirts' => 'تي شيرتات',
        'blazers-ramo' => 'بليزرات',
        'blazers' => 'بليزرات',
        'dresses' => 'فساتين',
        'jackets' => 'جاكيتات',
        'jeans' => 'جينز',
        'shoes' => 'أحذية',
        'women' => 'حريمي',
        'mobile-phones' => 'هواتف',
        'mobile phones' => 'هواتف',
        'phones' => 'هواتف',
    ];

    /** @var array<string, string> */
    private const ARABIC_COLORS = [
        'black' => 'أسود',
        'tan' => 'تان',
        'brown' => 'بني',
        'beige' => 'بيج',
        'navy' => 'كحلي',
        'khaki' => 'كاكي',
        'cream' => 'كريمي',
        'white' => 'أبيض',
        'blue' => 'أزرق',
        'red' => 'أحمر',
        'green' => 'أخضر',
        'grey' => 'رمادي',
        'gray' => 'رمادي',
    ];

    public static function category(?string $label, bool $arabic): string
    {
        return static::translate($label, $arabic, self::ARABIC_CATEGORIES);
    }

    public static function color(?string $label, bool $arabic): string
    {
        return static::translate($label, $arabic, self::ARABIC_COLORS);
    }

    /** @param array<string, string> $translations */
    private static function translate(?string $label, bool $arabic, array $translations): string
    {
        $label = trim((string) $label);
        if (!$arabic || $label === '') {
            return $label;
        }

        return $translations[mb_strtolower($label)] ?? $label;
    }
}

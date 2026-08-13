<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaxConfig
{
    private const CACHE_KEY = 'tax_config';

    private static array $defaults = [
        'enabled' => false,
        'rate_percent' => 0.0,
        'apply_to_shipping' => false,
    ];

    public static function get(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $row = DB::table('app_configs')
                ->where('config_key', 'tax_settings')
                ->first();

            $stored = $row?->value ? (json_decode($row->value, true) ?? []) : [];

            return array_merge(self::$defaults, is_array($stored) ? $stored : []);
        });
    }

    public static function enabled(): bool
    {
        return filter_var(self::get()['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public static function ratePercent(): float
    {
        return max(0.0, min(100.0, (float) (self::get()['rate_percent'] ?? 0)));
    }

    public static function cartTax(float $taxableAmount): float
    {
        if (! self::enabled()) {
            return 0.0;
        }

        return round(max(0.0, $taxableAmount) * self::ratePercent() / 100, 2);
    }

    public static function shippingTax(float $shippingAmount): float
    {
        if (! self::enabled() || ! filter_var(self::get()['apply_to_shipping'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return 0.0;
        }

        return round(max(0.0, $shippingAmount) * self::ratePercent() / 100, 2);
    }

    /**
     * Persist only an explicit, administrator-approved tax setting. The initial
     * configuration is disabled to avoid charging an unverified tax rate.
     */
    public static function save(array $data): void
    {
        $value = array_merge(self::get(), [
            'enabled' => filter_var($data['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'rate_percent' => max(0.0, min(100.0, (float) ($data['rate_percent'] ?? 0))),
            'apply_to_shipping' => filter_var($data['apply_to_shipping'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);

        DB::table('app_configs')->updateOrInsert(
            ['config_key' => 'tax_settings'],
            [
                'config_group' => 'commerce',
                'lang' => null,
                'value' => json_encode($value),
                'label' => 'Tax Settings',
                'description' => 'Administrator-approved tax configuration',
                'is_public' => false,
                'sort_order' => 0,
                'updated_at' => now(),
            ]
        );

        Cache::forget(self::CACHE_KEY);
    }
}

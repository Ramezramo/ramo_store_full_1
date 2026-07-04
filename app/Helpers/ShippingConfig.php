<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ShippingConfig
{
    private static array $defaults = [
        'free_shipping_enabled'   => true,
        'free_shipping_threshold' => 1000,
        'standard_shipping_fee'   => 0,
    ];

    public static function get(): array
    {
        return Cache::remember('shipping_config', 300, function () {
            $row = DB::table('app_configs')
                ->where('config_key', 'shipping_settings')
                ->first();

            if ($row && $row->value) {
                $stored = json_decode($row->value, true) ?? [];
                return array_merge(self::$defaults, $stored);
            }
            return self::$defaults;
        });
    }

    public static function val(string $key, mixed $fallback = null): mixed
    {
        $cfg = self::get();
        return $cfg[$key] ?? $fallback ?? self::$defaults[$key] ?? null;
    }

    public static function save(array $data): void
    {
        $current = self::get();
        $merged  = array_merge($current, $data);

        $exists = DB::table('app_configs')->where('config_key', 'shipping_settings')->exists();

        if ($exists) {
            DB::table('app_configs')
                ->where('config_key', 'shipping_settings')
                ->update(['value' => json_encode($merged), 'updated_at' => now()]);
        } else {
            DB::table('app_configs')->insert([
                'config_key'   => 'shipping_settings',
                'config_group' => 'shipping',
                'lang'         => null,
                'value'        => json_encode($merged),
                'label'        => 'Shipping Settings',
                'description'  => 'Free shipping threshold and standard shipping fee',
                'is_public'    => false,
                'sort_order'   => 0,
                'updated_at'   => now(),
            ]);
        }

        Cache::forget('shipping_config');
    }

    public static function defaults(): array
    {
        return self::$defaults;
    }

    /**
     * Compute the shipping fee for a given subtotal based on the current config.
     */
    public static function feeForSubtotal(float $subtotal): float
    {
        $enabled   = (bool) self::val('free_shipping_enabled', true);
        $threshold = (float) self::val('free_shipping_threshold', 1000);
        $fee       = (float) self::val('standard_shipping_fee', 0);

        if ($enabled && $subtotal >= $threshold) {
            return 0.0;
        }

        return $fee;
    }
}

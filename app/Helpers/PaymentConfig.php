<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentConfig
{
    private static array $defaults = [
        'wallet_enabled' => false,
        'wallet_number' => '',
        'instapay_enabled' => false,
        'instapay_number' => '',
        'instapay_link' => '',
    ];

    public static function get(): array
    {
        return Cache::remember('manual_payment_config', 300, function () {
            $row = DB::table('app_configs')->where('config_key', 'manual_payment_methods')->first();
            $stored = $row && $row->value ? (json_decode($row->value, true) ?: []) : [];

            return array_merge(self::$defaults, $stored);
        });
    }

    public static function save(array $data): void
    {
        $merged = array_merge(self::get(), $data);
        $payload = [
            'value' => json_encode($merged),
            'updated_at' => now(),
        ];

        if (DB::table('app_configs')->where('config_key', 'manual_payment_methods')->exists()) {
            DB::table('app_configs')
                ->where('config_key', 'manual_payment_methods')
                ->update($payload);
        } else {
            DB::table('app_configs')->insert(array_merge($payload, [
                'config_key' => 'manual_payment_methods',
                'config_group' => 'payment',
                'lang' => null,
                'label' => 'Manual Payment Methods',
                'description' => 'Wallet and InstaPay transfer instructions for website orders',
                'is_public' => false,
                'sort_order' => 0,
            ]));
        }

        Cache::forget('manual_payment_config');
    }

    public static function enabledMethods(): array
    {
        $config = self::get();
        $methods = [];

        if ($config['wallet_enabled'] && trim($config['wallet_number']) !== '') {
            $methods['manual_wallet'] = self::detailsFor('manual_wallet', $config);
        }

        if ($config['instapay_enabled'] && (trim($config['instapay_number']) !== '' || trim($config['instapay_link']) !== '')) {
            $methods['manual_instapay'] = self::detailsFor('manual_instapay', $config);
        }

        return $methods;
    }

    /**
     * Return the current destination for an order that already selected a
     * manual method. This intentionally does not require the method to still
     * be enabled, so disabling a method does not strand existing orders.
     */
    public static function detailsFor(string $method, ?array $config = null): ?array
    {
        $config ??= self::get();

        return match ($method) {
            'manual_wallet' => trim($config['wallet_number']) !== ''
                ? [
                    'title' => 'Pay by Wallet',
                    'description' => 'Transfer from any Egyptian mobile wallet',
                    'destination' => $config['wallet_number'],
                ]
                : null,
            'manual_instapay' => (trim($config['instapay_number']) !== '' || trim($config['instapay_link']) !== '')
                ? [
                    'title' => 'Pay by InstaPay',
                    'description' => 'Transfer using InstaPay',
                    'destination' => $config['instapay_number'] ?: $config['instapay_link'],
                    'link' => $config['instapay_link'],
                ]
                : null,
            default => null,
        };
    }

    public static function isManualMethod(?string $method): bool
    {
        return in_array($method, ['manual_wallet', 'manual_instapay'], true);
    }
}
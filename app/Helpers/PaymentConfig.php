<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentConfig
{
    private static array $defaults = [
        'cod_enabled' => true,
        'cod_data' => 'Pay when your order arrives',
        'vodafone_cash_enabled' => true,
        'vodafone_cash_data' => 'Send to 01xxxxxxxxx',
        'bank_transfer_enabled' => true,
        'bank_transfer_data' => 'Transfer to our bank account',
        'fawry_enabled' => true,
        'fawry_data' => 'Pay at any Fawry outlet',
        'credit_card_enabled' => true,
        'credit_card_data' => 'Visa / Mastercard',
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
     * Return all payment methods enabled for the web checkout, in the
     * configured customer-facing order. Manual methods remain first.
     */
    public static function checkoutMethods(): array
    {
        $config = self::get();
        $methods = [];

        if ($config['wallet_enabled'] && trim((string) $config['wallet_number']) !== '') {
            $details = self::detailsFor('manual_wallet', $config);
            $methods['manual_wallet'] = [
                'icon' => '📱',
                'title' => 'Pay by Wallet',
                'description' => 'Transfer from any Egyptian mobile wallet',
                'data' => $details['destination'] ?? '',
                'data_label' => 'Transfer to',
                'link' => null,
            ];
        }

        if ($config['instapay_enabled'] && (trim((string) $config['instapay_number']) !== '' || trim((string) $config['instapay_link']) !== '')) {
            $details = self::detailsFor('manual_instapay', $config);
            $methods['manual_instapay'] = [
                'icon' => '⚡',
                'title' => 'Pay by InstaPay',
                'description' => 'Transfer using InstaPay',
                'data' => $details['destination'] ?? '',
                'data_label' => 'Transfer to',
                'link' => $details['link'] ?? null,
            ];
        }

        $standard = [
            'cod' => [
                'icon' => '💵',
                'title' => 'Cash on Delivery',
                'description' => 'Pay when your order arrives',
                'data_label' => 'Details',
                'config_enabled' => 'cod_enabled',
                'config_data' => 'cod_data',
            ],
            'vodafone_cash' => [
                'icon' => '📱',
                'title' => 'Vodafone Cash',
                'description' => 'Send money from your Vodafone wallet',
                'data_label' => 'Transfer to',
                'config_enabled' => 'vodafone_cash_enabled',
                'config_data' => 'vodafone_cash_data',
            ],
            'bank_transfer' => [
                'icon' => '🏦',
                'title' => 'Bank Transfer',
                'description' => 'Transfer to our bank account',
                'data_label' => 'Bank details',
                'config_enabled' => 'bank_transfer_enabled',
                'config_data' => 'bank_transfer_data',
            ],
            'fawry' => [
                'icon' => '🏪',
                'title' => 'Fawry',
                'description' => 'Pay at any Fawry outlet',
                'data_label' => 'Details',
                'config_enabled' => 'fawry_enabled',
                'config_data' => 'fawry_data',
            ],
            'credit_card' => [
                'icon' => '💳',
                'title' => 'Credit Card',
                'description' => 'Visa / Mastercard',
                'data_label' => 'Details',
                'config_enabled' => 'credit_card_enabled',
                'config_data' => 'credit_card_data',
            ],
        ];

        foreach ($standard as $key => $method) {
            if (!$config[$method['config_enabled']]) {
                continue;
            }

            $methods[$key] = [
                'icon' => $method['icon'],
                'title' => $method['title'],
                'description' => $method['description'],
                'data' => trim((string) $config[$method['config_data']]),
                'data_label' => $method['data_label'],
                'link' => null,
            ];
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
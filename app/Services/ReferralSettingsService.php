<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReferralSettingsService
{
    private const CONFIG_KEY = 'referral_settings';
    private const CACHE_KEY = 'referral_settings';

    private static array $defaults = [
        'referral_enabled' => false,
        'referral_min_order_amount' => 700.00,
        'referral_commission_type' => 'percentage',
        'referral_commission_value' => 5.00,
        'referral_commission_scope' => 'first_order',
    ];

    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            $row = DB::table('app_configs')
                ->where('config_key', self::CONFIG_KEY)
                ->first();

            if (! $row || ! $row->value) {
                return self::$defaults;
            }

            $stored = json_decode((string) $row->value, true);
            if (! is_array($stored)) {
                return self::$defaults;
            }

            return $this->normalize(array_merge(self::$defaults, $stored));
        });
    }

    public function isEnabled(): bool
    {
        return (bool) $this->get()['referral_enabled'];
    }

    public function minOrderAmount(): float
    {
        return (float) $this->get()['referral_min_order_amount'];
    }

    public function commissionType(): string
    {
        return (string) $this->get()['referral_commission_type'];
    }

    public function commissionValue(): float
    {
        return (float) $this->get()['referral_commission_value'];
    }

    public function commissionScope(): string
    {
        return (string) $this->get()['referral_commission_scope'];
    }

    public function isAllOrders(): bool
    {
        return $this->commissionScope() === 'all_orders';
    }

    public function calculateCommission(float $finalTotal): float
    {
        $value = $this->commissionValue();
        $amount = $this->commissionType() === 'flat'
            ? $value
            : ($finalTotal * $value / 100);

        return round(max(0, $amount), 2);
    }

    public function save(array $data): array
    {
        $settings = $this->normalize(array_merge($this->get(), $data));
        $payload = [
            'value' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'config_group' => 'referral',
            'label' => 'Referral Program Settings',
            'description' => 'Referral attribution and commission settings',
            'is_public' => false,
            'updated_at' => now(),
        ];

        $exists = DB::table('app_configs')->where('config_key', self::CONFIG_KEY)->exists();
        if ($exists) {
            DB::table('app_configs')->where('config_key', self::CONFIG_KEY)->update($payload);
        } else {
            DB::table('app_configs')->insert(array_merge($payload, [
                'config_key' => self::CONFIG_KEY,
                'lang' => null,
                'sort_order' => 0,
            ]));
        }

        Cache::forget(self::CACHE_KEY);
        return $settings;
    }

    public function defaults(): array
    {
        return self::$defaults;
    }

    private function normalize(array $settings): array
    {
        $type = ($settings['referral_commission_type'] ?? 'percentage') === 'flat'
            ? 'flat'
            : 'percentage';
        $scope = ($settings['referral_commission_scope'] ?? 'first_order') === 'all_orders'
            ? 'all_orders'
            : 'first_order';

        return [
            'referral_enabled' => filter_var($settings['referral_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'referral_min_order_amount' => round(max(0, (float) ($settings['referral_min_order_amount'] ?? 700)), 2),
            'referral_commission_type' => $type,
            'referral_commission_value' => round(max(0, (float) ($settings['referral_commission_value'] ?? 0)), 2),
            'referral_commission_scope' => $scope,
        ];
    }
}

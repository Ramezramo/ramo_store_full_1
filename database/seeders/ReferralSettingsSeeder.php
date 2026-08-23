<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferralSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('app_configs')->where('config_key', 'referral_settings')->exists()) {
            return;
        }

        DB::table('app_configs')->insert([
            'config_key' => 'referral_settings',
            'config_group' => 'referral',
            'lang' => null,
            'value' => json_encode([
                'referral_enabled' => false,
                'referral_min_order_amount' => 700.00,
                'referral_commission_type' => 'percentage',
                'referral_commission_value' => 5.00,
            ], JSON_UNESCAPED_UNICODE),
            'label' => 'Referral Program Settings',
            'description' => 'Referral attribution and commission settings',
            'is_public' => false,
            'sort_order' => 0,
            'updated_at' => now(),
        ]);
    }
}

<?php

namespace Database\Seeders;

// use App\Models\Coupon;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run()
    {
        $sampleData = [
            [
                'code' => '10d',
                'amount' => 10.00,
                'status' => 'publish',
                'discount_type' => 'fixed_cart',
                'date_created' => Carbon::parse('2018-03-28 15:36:17'),
                'date_created_gmt' => Carbon::parse('2018-03-28 08:36:17'),
                'date_modified' => Carbon::parse('2021-01-22 17:32:57'),
                'date_modified_gmt' => Carbon::parse('2021-01-22 10:32:57'),
                'usage_count' => 10,
                'individual_use' => false,
                'usage_limit' => 1,
                'usage_limit_per_user' => 1,
                'free_shipping' => true,
                'minimum_amount' => 0.00,
                'maximum_amount' => 0.00,
                'used_by' => ['2950', '5653', '5653', '5653', '5653', '5384', '5384', '5384', '5384', '5384'],
                'meta_data' => [
                    ['id' => 103376, 'key' => '_vc_post_settings', 'value' => ['vc_grid_id' => []]],
                    ['id' => 103397, 'key' => 'slide_template', 'value' => 'default'],
                    ['id' => 270550, 'key' => '_pwb_coupon_restriction', 'value' => null]
                ]
            ]
        ];

        foreach ($sampleData as $data) {
            Coupon::create($data);
        }
    }
}
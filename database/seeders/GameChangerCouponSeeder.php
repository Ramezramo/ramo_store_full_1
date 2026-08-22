<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameChangerCouponSeeder extends Seeder
{
    public function run(): void
    {
        $code = 'GAME_CHANGER';

        if (DB::table('coupons')->whereRaw('LOWER(code) = ?', [strtolower($code)])->exists()) {
            return;
        }

        $now = now();

        DB::table('coupons')->insert([
            'code'                        => $code,
            'vendor_id'                   => null,
            'amount'                      => 20,
            'discount_type'               => 'percent',
            'status'                      => 'publish',
            'usage_count'                 => 0,
            'usage_limit'                 => null,
            'usage_limit_per_user'        => null,
            'limit_usage_to_x_items'      => null,
            'minimum_amount'              => 500,
            'maximum_amount'              => 2000,
            'date_expires'                => null,
            'date_created'                => $now,
            'date_created_gmt'            => $now,
            'date_modified'               => $now,
            'date_modified_gmt'           => $now,
            'individual_use'              => false,
            'free_shipping'               => false,
            'exclude_sale_items'          => false,
            'product_ids'                 => '[]',
            'excluded_product_ids'        => '[]',
            'product_categories'          => '[]',
            'excluded_product_categories' => '[]',
            'email_restrictions'          => '[]',
            'description'                 => 'خصم 20% على الطلبات من 500 إلى 2000 جنيه.',
            'meta_data'                   => '[]',
        ]);
    }
}

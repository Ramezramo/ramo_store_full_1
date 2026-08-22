<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FreeShippingCouponsSeeder extends Seeder
{
    public function run(): void
    {
        $expiresAt = '2026-08-31 23:59:59';
        $now = now();

        foreach ([
            ['code' => 'AC46264BD', 'minimum_amount' => 700],
            ['code' => 'BK13H16D', 'minimum_amount' => 650],
        ] as $offer) {
            $exists = DB::table('coupons')->whereRaw('LOWER(code) = ?', [strtolower($offer['code'])])->exists();
            if ($exists) {
                continue;
            }

            DB::table('coupons')->insert([
                'code'                        => $offer['code'],
                'vendor_id'                   => null,
                'amount'                      => 0,
                'discount_type'               => 'fixed_cart',
                'status'                      => 'publish',
                'usage_count'                 => 0,
                'usage_limit'                 => null,
                'usage_limit_per_user'        => null,
                'limit_usage_to_x_items'      => null,
                'minimum_amount'              => $offer['minimum_amount'],
                'maximum_amount'              => 0,
                'date_expires'                => $expiresAt,
                'date_created'                => $now,
                'date_created_gmt'            => $now,
                'date_modified'               => $now,
                'date_modified_gmt'           => $now,
                'individual_use'              => false,
                'free_shipping'               => true,
                'exclude_sale_items'          => false,
                'product_ids'                 => '[]',
                'excluded_product_ids'        => '[]',
                'product_categories'          => '[]',
                'excluded_product_categories' => '[]',
                'email_restrictions'          => '[]',
                'description'                 => 'توصيل مجاني على الطلب عند الوصول للحد الأدنى.',
                'meta_data'                   => '[]',
            ]);
        }
    }
}

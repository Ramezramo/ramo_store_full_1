<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RamoStoreSeeder::class);
        $this->call(FreeShippingCouponsSeeder::class);
        $this->call(GameChangerCouponSeeder::class);
        $this->call(ReferralSettingsSeeder::class);
    }
}

<?php

namespace Tests\Feature;

use App\Helpers\TaxConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaxConfigTest extends TestCase
{
    public function test_tax_is_disabled_by_default_and_only_applies_after_an_explicit_configuration(): void
    {
        $previous = DB::table('app_configs')->where('config_key', 'tax_settings')->first();

        try {
            DB::table('app_configs')->where('config_key', 'tax_settings')->delete();
            Cache::forget('tax_config');

            $this->assertSame(0.0, TaxConfig::cartTax(100));
            $this->assertSame(0.0, TaxConfig::shippingTax(25));

            TaxConfig::save([
                'enabled' => true,
                'rate_percent' => 14,
                'apply_to_shipping' => true,
            ]);

            $this->assertSame(14.0, TaxConfig::cartTax(100));
            $this->assertSame(3.5, TaxConfig::shippingTax(25));

            TaxConfig::save([
                'enabled' => true,
                'rate_percent' => 250,
                'apply_to_shipping' => false,
            ]);

            $this->assertSame(100.0, TaxConfig::cartTax(100));
            $this->assertSame(0.0, TaxConfig::shippingTax(25));
        } finally {
            DB::table('app_configs')->where('config_key', 'tax_settings')->delete();
            if ($previous) {
                DB::table('app_configs')->insert((array) $previous);
            }
            Cache::forget('tax_config');
        }
    }
}

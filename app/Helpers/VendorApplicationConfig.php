<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class VendorApplicationConfig
{
    public static function requireIdentity(): bool
    {
        $value = DB::table('app_configs')->where('config_key', 'vendor_application_settings')->whereNull('lang')->value('value');
        $settings = json_decode((string) $value, true);

        return is_array($settings) && array_key_exists('require_identity', $settings)
            ? (bool) $settings['require_identity'] : true;
    }
}

<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AuthConfig
{
    private static array $defaults = [
        'email_login'              => true,
        'google_login'             => false,
        'phone_otp_login'          => false,
        'guest_checkout'           => false,
        'auto_register_google'     => true,
        'auto_register_otp'        => true,
        'require_name_on_register' => true,
        'require_email_on_register'=> false,
        'otp_length'               => 6,
        'otp_expiry_minutes'       => 5,
        'max_otp_attempts'         => 3,
        'resend_cooldown_seconds'  => 60,
        'max_resends_per_hour'     => 3,
        'max_login_attempts'       => 5,
        'lockout_duration_minutes' => 15,
        'session_expiry_hours'     => 24,
    ];

    public static function get(): array
    {
        return Cache::remember('auth_config', 300, function () {
            $row = DB::table('app_configs')
                ->where('config_key', 'auth_settings')
                ->first();

            if ($row && $row->value) {
                $stored = json_decode($row->value, true) ?? [];
                return array_merge(self::$defaults, $stored);
            }
            return self::$defaults;
        });
    }

    public static function val(string $key, mixed $fallback = null): mixed
    {
        $cfg = self::get();
        return $cfg[$key] ?? $fallback ?? self::$defaults[$key] ?? null;
    }

    public static function save(array $data): void
    {
        $current = self::get();
        $merged  = array_merge($current, $data);

        $exists = DB::table('app_configs')->where('config_key', 'auth_settings')->exists();

        if ($exists) {
            DB::table('app_configs')
                ->where('config_key', 'auth_settings')
                ->update(['value' => json_encode($merged), 'updated_at' => now()]);
        } else {
            DB::table('app_configs')->insert([
                'config_key'   => 'auth_settings',
                'config_group' => 'auth',
                'lang'         => null,
                'value'        => json_encode($merged),
                'label'        => 'Auth Settings',
                'description'  => 'Login methods and security configuration',
                'is_public'    => false,
                'sort_order'   => 0,
                'updated_at'   => now(),
            ]);
        }

        Cache::forget('auth_config');
    }

    public static function defaults(): array
    {
        return self::$defaults;
    }
}

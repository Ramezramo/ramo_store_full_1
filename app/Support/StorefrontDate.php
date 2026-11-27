<?php

namespace App\Support;

use Carbon\Carbon;

class StorefrontDate
{
    /** Keep storage in UTC; only localize presentation for the Egyptian storefront. */
    public static function format(mixed $value, bool $withTime = true): string
    {
        if (empty($value)) return '—';
        $arabic = session('locale', 'en') === 'ar';
        $date = Carbon::parse($value, 'UTC')->setTimezone('Africa/Cairo')->locale($arabic ? 'ar' : 'en');
        return $date->translatedFormat($withTime
            ? ($arabic ? 'j F Y، g:i A' : 'M d, Y · g:i A')
            : ($arabic ? 'j F Y' : 'M d, Y'))
            . ($withTime ? ($arabic ? ' بتوقيت القاهرة' : ' Cairo time') : '');
    }
}

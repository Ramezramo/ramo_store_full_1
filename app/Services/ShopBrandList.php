<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ShopBrandList
{
    private const CACHE_KEY = 'shop:brands';

    public static function get(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(12), fn () => DB::table('brands')
            ->orderBy('name')
            ->get());
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

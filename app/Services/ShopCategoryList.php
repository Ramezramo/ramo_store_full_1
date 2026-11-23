<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ShopCategoryList
{
    private const CACHE_KEY = 'shop:categories';

    public static function get(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(12), fn () => DB::table('categories2')
            ->orderBy('menu_order')
            ->orderBy('name')
            ->get());
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

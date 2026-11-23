<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class StorefrontDataCache
{
    public static function rememberTimeline(string $lang, int $page, ?object $flashSale, Closure $load): array
    {
        return self::remember('timeline', $lang, $page, 1, $flashSale, $load);
    }

    public static function rememberProduct(string $lang, string $id, ?object $flashSale, Closure $load): array
    {
        return self::remember('product', $lang, $id, 12, $flashSale, $load);
    }

    private static function remember(string $scope, string $lang, int|string $id, int $minutes, ?object $flashSale, Closure $load): array
    {
        if (DB::transactionLevel() > 0) {
            return $load();
        }

        $revision = Cache::get("storefront:revision:{$scope}", 'initial');
        $key = "storefront:{$scope}:".hash('sha256', serialize([$revision, url('/'), $lang, $id]));
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $expires = now()->addMinutes($minutes);
        if (($flashSale->endTime ?? 0) > 0) {
            $expires = $expires->min(Carbon::createFromTimestampMs($flashSale->endTime));
        }

        $couponExpiry = DB::table('coupons')
            ->where('status', 'publish')
            ->where('date_expires', '>', now())
            ->min('date_expires');
        if ($couponExpiry !== null) {
            $expires = $expires->min(Carbon::parse($couponExpiry));
        }

        $data = $load();
        if ($expires->isFuture()) {
            Cache::put($key, $data, $expires);
        }

        return $data;
    }

    public static function invalidate(): void
    {
        self::rotateRevision('timeline');
        self::invalidateProducts();
    }

    public static function invalidateProducts(): void
    {
        self::rotateRevision('product');
    }

    private static function rotateRevision(string $scope): void
    {
        // Concurrent readers can only populate the revision they started with.
        Cache::put("storefront:revision:{$scope}", (string) Str::uuid(), now()->addMinutes(30));
    }
}

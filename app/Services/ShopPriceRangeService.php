<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ShopPriceRangeService
{
    /** Rebuild the cached bounds used by the storefront price slider. */
    public function refreshGlobal(): void
    {
        $productPrices = DB::table('product_variations as pv')
            ->join('products_data as p', 'p.id', '=', 'pv.product_id')
            ->where('p.status', 'publish')
            ->where('p.acceptance_status', 'approved')
            ->where('pv.regular_price', '>', 0)
            ->select('p.id as product_id')
            ->selectRaw('MIN(CAST(pv.price AS DECIMAL(12, 2))) as product_price')
            ->groupBy('p.id');

        // The storefront price filter is applied to each product's lowest
        // variation price, so cached bounds use the same per-product value.
        $range = DB::query()
            ->fromSub(clone $productPrices, 'priced_products')
            ->selectRaw('MIN(product_price) as min_price, MAX(product_price) as max_price')
            ->first();

        $min = max(0, (float) ($range->min_price ?? 0));
        $max = max($min, (float) ($range->max_price ?? $min));

        $categoryRanges = DB::table('product_category as pc')
            ->joinSub(clone $productPrices, 'priced_products', function ($join): void {
                $join->on('priced_products.product_id', '=', 'pc.product_id');
            })
            ->select('pc.category_id')
            ->selectRaw('MIN(priced_products.product_price) as min_price, MAX(priced_products.product_price) as max_price')
            ->groupBy('pc.category_id')
            ->get();

        DB::transaction(function () use ($min, $max, $categoryRanges): void {
            DB::table('storefront_price_ranges')->updateOrInsert(
                ['scope' => 'global'],
                ['min_price' => $min, 'max_price' => $max, 'updated_at' => now()]
            );

            DB::table('storefront_price_ranges')->where('scope', 'like', 'category:%')->delete();
            $now = now();
            $rows = $categoryRanges->map(fn ($range) => [
                'scope' => 'category:'.(int) $range->category_id,
                'min_price' => max(0, (float) $range->min_price),
                'max_price' => max((float) $range->min_price, (float) $range->max_price),
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('storefront_price_ranges')->insert($chunk);
            }
        });
    }
}

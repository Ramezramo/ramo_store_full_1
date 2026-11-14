<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Authoritative purchase rules shared by website, API and checkout. */
class CartRules
{
    public static function purchasable(?object $product): bool
    {
        if (! $product || $product->status !== 'publish' || $product->acceptance_status !== 'approved'
            || ! (bool) ($product->purchasable ?? false)) {
            return false;
        }

        return empty($product->vendor_id) || DB::table('vendor_users')
            ->where('id', $product->vendor_id)->where('status', 'approved')->exists();
    }

    public static function variation(int $productId, ?int $variationId): ?object
    {
        return DB::table('product_variations')->where('product_id', $productId)
            ->when($variationId, fn ($q) => $q->where('id', $variationId), fn ($q) => $q->where('main_variation', true))
            ->orderBy('id')->first();
    }

    public static function quantityError(?object $product, ?object $variation, int $quantity): ?string
    {
        if (! self::purchasable($product) || ! $variation || (int) $variation->product_id !== (int) $product->id
            || $variation->status !== 'publish' || $variation->stock_status !== 'instock') {
            return 'This product variation is no longer available.';
        }
        $minimum = max(1, (int) ($product->minimum_order_qty ?? 1));
        $maximum = max(0, (int) $variation->stock_quantity);
        if ((int) ($product->max_orders_per_person ?? 0) > 0) {
            $maximum = min($maximum, (int) $product->max_orders_per_person);
        }
        if ($product->sold_individually ?? false) $maximum = min($maximum, 1);

        return $quantity < $minimum || $quantity > $maximum
            ? "Quantity must be between {$minimum} and {$maximum} for this option." : null;
    }

    /** Resolve legacy default rows and combine duplicates before validating stock. */
    public static function canonicalize(array $cart): array
    {
        $products = collect($cart)->pluck('product_id')->unique()->all();
        $variations = DB::table('product_variations')->whereIn('product_id', $products)->orderBy('id')->get()->keyBy('id');
        $result = [];
        foreach ($cart as $key => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $variation = ! empty($item['variation_id']) ? $variations->get((int) $item['variation_id'])
                : $variations->first(fn ($v) => (int) $v->product_id === $productId && $v->main_variation);
            // Preserve invalid rows so checkout rejects them rather than silently substituting an option.
            if (! $variation || (int) $variation->product_id !== $productId) {
                $rowId = md5($productId.'_'.(int) ($item['variation_id'] ?? 0));
                $item['rowId'] = $rowId;
                $item['qty'] = (int) ($item['qty'] ?? 0) + (int) ($result[$rowId]['qty'] ?? 0);
                $result[$rowId] = $item;
                continue;
            }
            $rowId = md5($productId.'_'.$variation->id);
            $item['variation_id'] = (int) $variation->id;
            $item['rowId'] = $rowId;
            $item['qty'] = (int) ($item['qty'] ?? 0) + (int) ($result[$rowId]['qty'] ?? 0);
            $result[$rowId] = $item;
        }
        return $result;
    }
}

<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponEligibility
{
    private static function ids($values): array
    {
        return array_values(array_filter(array_map(static fn ($v) => (int) (is_array($v) ? ($v['id'] ?? 0) : $v), (array) $values)));
    }

    /** No client prices, category IDs or billing email are trusted here. */
    public static function discount(Coupon $coupon, float $subtotal, ?int $userId, ?array $cart): float
    {
        $fail = static fn () => throw ValidationException::withMessages(['coupon' => 'This coupon is not valid for your account or cart.']);
        $emails = array_map(static fn ($v) => strtolower(trim((string) $v)), $coupon->email_restrictions ?? []);
        if ($emails) {
            $email = $userId ? DB::table('users')->where('id', $userId)->value('email') : null;
            if (! $email || ! in_array(strtolower(trim($email)), $emails, true)) $fail();
        }
        // Vendor-specific codes are not supported by the shared storefront checkout.
        if ($coupon->vendor_id) $fail();
        $included = self::ids($coupon->product_ids);
        $excluded = self::ids($coupon->excluded_product_ids);
        $categories = self::ids($coupon->product_categories);
        $excludedCategories = self::ids($coupon->excluded_product_categories);
        $limit = max(0, (int) $coupon->limit_usage_to_x_items);
        $amount = max(0, (float) $coupon->amount);
        $percent = in_array($coupon->discount_type, ['percent', 'percentage'], true);
        if (! in_array($coupon->discount_type, ['percent', 'percentage', 'fixed_cart', 'fixed', 'fixed_product'], true)
            || ($percent && $amount > 100)) $fail();

        if ($cart === null) {
            // Legacy subtotal-only callers can use unrestricted coupons only.
            if ($included || $excluded || $categories || $excludedCategories || $coupon->exclude_sale_items || $limit || $coupon->discount_type === 'fixed_product') $fail();
            return round(min($subtotal, $percent ? $subtotal * $amount / 100 : $amount), 2);
        }

        $cart = CartRules::canonicalize($cart);
        $productIds = array_column($cart, 'product_id');
        $products = DB::table('products_data')->whereIn('id', $productIds)->get()->keyBy('id');
        $variations = DB::table('product_variations')->whereIn('product_id', $productIds)->get()->keyBy('id');
        $memberships = DB::table('product_category')->whereIn('product_id', $productIds)->get()->groupBy('product_id');
        $eligible = 0.0;
        $discount = 0.0;
        $remaining = $limit ?: PHP_INT_MAX;
        foreach ($cart as $item) {
            $id = (int) $item['product_id'];
            $product = $products->get($id);
            $variation = $variations->get((int) ($item['variation_id'] ?? 0));
            if (! CartRules::purchasable($product) || ! $variation || (int) $variation->product_id !== $id
                || $variation->status !== 'publish' || $variation->stock_status !== 'instock' || (int) $item['qty'] < 1) $fail();
            $productCategories = $memberships->get($id, collect())->pluck('category_id')->map(fn ($id) => (int) $id)->all();
            $price = PricingService::effectiveVariationPrice($variation, $product);
            $onSale = $price < (float) ($variation->regular_price ?? $price);
            if (($included && ! in_array($id, $included, true)) || in_array($id, $excluded, true)
                || ($categories && ! array_intersect($categories, $productCategories))
                || array_intersect($excludedCategories, $productCategories) || ($coupon->exclude_sale_items && $onSale)) continue;
            $qty = min($remaining, (int) $item['qty']);
            $remaining -= $qty;
            $eligible += $price * $qty;
            if ($coupon->discount_type === 'fixed_product') $discount += min($price, $amount) * $qty;
        }
        if ($eligible <= 0) $fail();
        if ($percent) $discount = $eligible * $amount / 100;
        elseif ($coupon->discount_type !== 'fixed_product') $discount = min($eligible, $amount);
        return round(max(0, min($subtotal, $eligible, $discount)), 2);
    }
}

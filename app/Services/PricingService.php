<?php

namespace App\Services;

class PricingService
{
    /**
     * Resolve the customer-facing price for a variation from authoritative DB rows.
     *
     * This deliberately mirrors the storefront pricing rule: use the variation's
     * live price, then apply the product-level percentage discount only when the
     * live price is not already below the regular price.
     */
    public static function effectiveVariationPrice(object $variation, object $product): float
    {
        $regularPrice = (float) ($variation->regular_price ?? 0);
        $livePrice = (float) ($variation->price ?? $regularPrice);
        $discountPercentage = max(0, min(100, (float) ($product->discount_percentage ?? 0)));

        if ($discountPercentage > 0 && $regularPrice > 0 && $livePrice >= $regularPrice) {
            $livePrice = round($regularPrice * (1 - $discountPercentage / 100), 2);
        }

        return round(max(0, $livePrice), 2);
    }
}

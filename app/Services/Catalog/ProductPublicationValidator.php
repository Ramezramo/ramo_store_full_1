<?php

namespace App\Services\Catalog;

use App\Constants\AppConstants;
use Illuminate\Support\Facades\DB;

class ProductPublicationValidator
{
    /**
     * Return business-safe reasons a product must not be published.
     *
     * Price validation intentionally uses the variation table. In this store,
     * the public price is derived from product_variations rather than the
     * legacy products_data.price column.
     *
     * @return array<int, string>
     */
    public function failuresFor(int $productId): array
    {
        $product = DB::table('products_data')->where('id', $productId)->first();

        if (! $product) {
            return ['The product no longer exists.'];
        }

        $failures = [];

        if (trim((string) $product->name) === '') {
            $failures[] = 'Product name is required.';
        }

        if (trim((string) $product->sku) === '') {
            $failures[] = 'SKU is required.';
        }

        if (! DB::table('product_category')->where('product_id', $productId)->exists()) {
            $failures[] = 'At least one category is required.';
        }

        if (! AppConstants::productThumbnailUrl($product->images)) {
            $failures[] = 'A usable product image is required.';
        }

        $hasSellablePrice = DB::table('product_variations')
            ->where('product_id', $productId)
            ->where('regular_price', '>', 0)
            ->exists();

        if (! $hasSellablePrice) {
            $failures[] = 'At least one product variation needs a price greater than zero.';
        }

        return $failures;
    }

    public function isPublishable(int $productId): bool
    {
        return $this->failuresFor($productId) === [];
    }
}

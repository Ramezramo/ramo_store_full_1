<?php

namespace App\Http\Traits;

use App\Constants\AppConstants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait CartTrait
{
    protected function getCart(): array
    {
        if (Auth::check()) {
            return $this->loadCartFromDb();
        }
        return session('ramo_cart', []);
    }

    protected function saveCart(array $cart): void
    {
        if (Auth::check()) {
            $this->syncCartToDb($cart);
        } else {
            session(['ramo_cart' => $cart]);
        }
    }

    protected function loadCartFromDb(): array
    {
        $userId = Auth::id();
        $items  = DB::table('cart_items')->where('user_id', $userId)->get();

        if ($items->isEmpty()) return [];

        $productIds   = $items->pluck('product_id')->unique()->toArray();
        $variationIds = $items->pluck('variation_id')->filter()->unique()->toArray();

        $products   = DB::table('products_data')->whereIn('id', $productIds)->get()->keyBy('id');
        $variations = DB::table('product_variations')->whereIn('id', $variationIds)->get()->keyBy('id');

        $cart = [];
        foreach ($items as $item) {
            $product = $products[$item->product_id] ?? null;
            if (!$product) continue;

            $variation = $item->variation_id ? ($variations[$item->variation_id] ?? null) : null;
            if (!$variation) {
                $variation = DB::table('product_variations')
                    ->where('product_id', $item->product_id)
                    ->where('main_variation', true)
                    ->first();
            }

            $regularPrice = (float) ($variation->regular_price ?? 0);
            $price        = (float) ($variation->price ?? $regularPrice);
            $discPct      = (float) ($product->discount_percentage ?? 0);
            if ($discPct > 0 && $regularPrice > 0 && $price >= $regularPrice) {
                $price = round($regularPrice * (1 - $discPct / 100), 2);
            }

            $attrs = [];
            if ($item->variation_id && $variation) {
                $vAttrs = $variation->attributes ?? null;
                if ($vAttrs) {
                    $attrs = json_decode($vAttrs, true)
                          ?? json_decode(stripslashes($vAttrs), true)
                          ?? [];
                }
            }

            $rowId = md5($item->product_id . '_' . ($item->variation_id ?? '0'));
            $cart[$rowId] = [
                'rowId'        => $rowId,
                'product_id'   => (int) $item->product_id,
                'variation_id' => $item->variation_id ? (int) $item->variation_id : null,
                'name'         => $product->name,
                'price'        => $price,
                'qty'          => (int) $item->qty,
                'image'        => AppConstants::productThumbnailUrl($product->images),
                'stock'        => (int) ($product->stock_quantity ?? 999),
                'attrs'        => $attrs,
            ];
        }

        return $cart;
    }

    protected function syncCartToDb(array $cart): void
    {
        $userId = Auth::id();
        DB::table('cart_items')->where('user_id', $userId)->delete();
        foreach ($cart as $item) {
            DB::table('cart_items')->insert([
                'user_id'      => $userId,
                'product_id'   => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'qty'          => $item['qty'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}

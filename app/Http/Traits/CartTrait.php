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
            // Some authentication flows (OTP, Google OAuth, and email
            // verification) log the user in without going through the
            // password-login controller. Merge the guest cart here as a
            // safety net so the cart cannot be lost during that handoff.
            $guestCart = session('ramo_cart', []);
            if (!empty($guestCart)) {
                $this->mergeGuestCartToDb(Auth::id(), $guestCart);
                session()->forget('ramo_cart');
            }

            return $this->loadCartFromDb();
        }
        return \App\Services\CartRules::canonicalize(session('ramo_cart', []));
    }

    protected function cartVariationQuantities(array $cart, int $productId): array
    {
        $quantities = [];
        foreach ($cart as $item) {
            $variationId = (int) ($item['variation_id'] ?? 0);
            if ((int) ($item['product_id'] ?? 0) === $productId && $variationId > 0) {
                $quantities[$variationId] = ($quantities[$variationId] ?? 0) + (int) ($item['qty'] ?? 0);
            }
        }

        return $quantities;
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
            if (!$item->variation_id) {
                $variation = DB::table('product_variations')
                    ->where('product_id', $item->product_id)
                    ->where('main_variation', true)
                    ->first();
            }

            $bound = $variation && (int) $variation->product_id === (int) $product->id;
            $price = $bound ? \App\Services\PricingService::effectiveVariationPrice($variation, $product) : 0;

            $attrs = [];
            if ($bound) {
                $vAttrs = $variation->attributes ?? null;
                if ($vAttrs) {
                    $attrs = json_decode($vAttrs, true)
                          ?? json_decode(stripslashes($vAttrs), true)
                          ?? [];
                }
            }

            $rowId = md5($item->product_id . '_' . ($item->variation_id ?? '0'));
            $cart[] = [
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

        return \App\Services\CartRules::canonicalize($cart);
    }

    protected function syncCartToDb(array $cart): void
    {
        DB::transaction(function () use ($cart) {
            $userId = Auth::id();
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            DB::table('cart_items')->where('user_id', $userId)->delete();
            foreach (\App\Services\CartRules::canonicalize($cart) as $item) {
                DB::table('cart_items')->insert([
                    'user_id' => $userId, 'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null, 'qty' => $item['qty'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    protected function mergeGuestCartToDb(int $userId, array $guestCart): void
    {
        if (empty($guestCart)) return;
        DB::transaction(function () use ($userId, $guestCart) {
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            foreach (\App\Services\CartRules::canonicalize($guestCart) as $item) {
                $product = DB::table('products_data')->where('id', $item['product_id'])->first();
                $variation = \App\Services\CartRules::variation((int) $item['product_id'], $item['variation_id'] ?? null);
                if (! $product || ! $variation) continue;
                $existing = DB::table('cart_items')->where('user_id', $userId)->where('product_id', $product->id)
                    ->where(function ($q) use ($variation) {
                        $q->where('variation_id', $variation->id);
                        if ($variation->main_variation) $q->orWhereNull('variation_id');
                    })->orderBy('id')->get();
                $maximum = (int) $variation->stock_quantity;
                if ((int) ($product->max_orders_per_person ?? 0) > 0) $maximum = min($maximum, (int) $product->max_orders_per_person);
                if ($product->sold_individually ?? false) $maximum = min($maximum, 1);
                $qty = min($maximum, (int) $existing->sum('qty') + (int) $item['qty']);
                if (\App\Services\CartRules::quantityError($product, $variation, $qty)) continue;
                $values = ['variation_id' => $variation->id, 'qty' => $qty, 'updated_at' => now()];
                if ($existing->isNotEmpty()) {
                    DB::table('cart_items')->where('user_id', $userId)->whereIn('id', $existing->pluck('id')->slice(1))->delete();
                    DB::table('cart_items')->where('user_id', $userId)->where('id', $existing->first()->id)->update($values);
                } else {
                    DB::table('cart_items')->insert($values + ['user_id' => $userId, 'product_id' => $product->id, 'created_at' => now()]);
                }
            }
        });
    }

    protected function mergeGuestWishlistToDb(int $userId, array $guestWishlist): void
    {
        $requestedIds = collect($guestWishlist)
            ->map(fn ($productId) => (int) $productId)
            ->filter(fn (int $productId) => $productId > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($requestedIds)) return;

        // Session data is client-controlled. Only products that still exist in
        // the catalog may be copied into an authenticated user's wishlist.
        $validProductIds = DB::table('products_data')
            ->whereIn('id', $requestedIds)
            ->pluck('id')
            ->map(fn ($productId) => (int) $productId)
            ->all();

        foreach ($validProductIds as $productId) {
            $exists = DB::table('wishlists')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();

            if (!$exists) {
                DB::table('wishlists')->insert([
                    'user_id'    => $userId,
                    'product_id' => $productId,
                    'created_at' => now(),
                ]);
            }
        }
    }

    protected function mergeGuestSessionOnLogin(int $userId): void
    {
        $this->mergeGuestCartToDb($userId, session('ramo_cart', []));
        $this->mergeGuestWishlistToDb($userId, session('ramo_wishlist', []));
        session()->forget(['ramo_cart', 'ramo_wishlist', 'ramo_coupon']);
    }
}

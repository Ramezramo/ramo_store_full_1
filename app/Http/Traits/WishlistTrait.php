<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait WishlistTrait
{
    protected function getWishlistIds(): array
    {
        if (Auth::check()) {
            // Authentication paths that do not explicitly perform the login
            // merge still arrive here before rendering the wishlist. Persist
            // the guest items first so the database and the visible count
            // cannot diverge after login.
            $guestWishlist = session('ramo_wishlist', []);
            if (is_array($guestWishlist) && !empty($guestWishlist)) {
                $this->mergeGuestWishlistToDb(Auth::id(), $guestWishlist);
                session()->forget('ramo_wishlist');
            }

            return DB::table('wishlists')
                ->where('user_id', Auth::id())
                ->pluck('product_id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }
        return session('ramo_wishlist', []);
    }

    protected function mergeGuestWishlistToDb(int $userId, array $guestWishlist): void
    {
        $productIds = collect($guestWishlist)
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return;
        }

        $validProductIds = DB::table('products_data')
            ->whereIn('id', $productIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        foreach ($validProductIds as $productId) {
            $exists = DB::table('wishlists')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();

            if (!$exists) {
                DB::table('wishlists')->insert([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'created_at' => now(),
                ]);
            }
        }
    }

    protected function saveWishlistIds(array $ids): void
    {
        if (Auth::check()) {
            $userId = Auth::id();
            DB::table('wishlists')->where('user_id', $userId)->delete();
            foreach ($ids as $pid) {
                DB::table('wishlists')->insert([
                    'user_id'    => $userId,
                    'product_id' => $pid,
                    'created_at' => now(),
                ]);
            }
        } else {
            session(['ramo_wishlist' => $ids]);
        }
    }
}

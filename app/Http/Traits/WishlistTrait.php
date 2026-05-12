<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait WishlistTrait
{
    protected function getWishlistIds(): array
    {
        if (Auth::check()) {
            return DB::table('wishlists')
                ->where('user_id', Auth::id())
                ->pluck('product_id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }
        return session('ramo_wishlist', []);
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

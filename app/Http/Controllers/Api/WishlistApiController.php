<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistApiController extends Controller
{
    public function index()
    {
        $items = DB::table('wishlists as w')
            ->join('products_data as p', 'p.id', '=', 'w.product_id')
            ->where('w.user_id', Auth::id())
            ->select('w.id', 'w.product_id', 'w.created_at',
                'p.name', 'p.images',
                DB::raw('(SELECT MIN(price) FROM product_variations WHERE product_id = p.id) as price'))
            ->orderBy('w.id', 'desc')
            ->get()
            ->map(function ($i) {
                $i->image = \App\Constants\AppConstants::productThumbnailUrl($i->images);
                unset($i->images);
                return $i;
            });

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function toggle(Request $r)
    {
        $r->validate(['product_id' => 'required|integer|exists:products_data,id']);
        $userId    = Auth::id();
        $productId = $r->product_id;

        $existing = DB::table('wishlists')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            DB::table('wishlists')->where('id', $existing->id)->delete();
            $action = 'removed';
        } else {
            DB::table('wishlists')->insert(['user_id' => $userId, 'product_id' => $productId, 'created_at' => now()]);
            $action = 'added';
        }

        $count = DB::table('wishlists')->where('user_id', $userId)->count();
        return response()->json(['success' => true, 'action' => $action, 'wishlist_count' => $count]);
    }
}

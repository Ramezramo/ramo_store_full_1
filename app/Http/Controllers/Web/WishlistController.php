<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Traits\WishlistTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    use WishlistTrait;

    public function index()
    {
        $ids      = $this->getWishlistIds();
        $products = collect();

        if (!empty($ids)) {
            $products = DB::table('products_data as p')
                ->select('p.id', 'p.name', 'p.images',
                    DB::raw('MIN(pv.price) as price'),
                    DB::raw('MIN(pv.sale_price) as sale_price'))
                ->join('product_variations as pv', 'pv.product_id', '=', 'p.id')
                ->whereIn('p.id', $ids)
                ->groupBy('p.id', 'p.name', 'p.images')
                ->get()
                ->map(function ($p) {
                    $p->thumbnail_url = \App\Constants\AppConstants::productThumbnailUrl($p->images);
                    $p->price      = (float)($p->price ?? 0);
                    $p->sale_price = (float)($p->sale_price ?? 0);
                    $p->on_sale    = $p->sale_price > 0 && $p->sale_price < $p->price;
                    return $p;
                });
        }

        return view('web.wishlist', compact('products'));
    }

    public function toggle(Request $r)
    {
        $r->validate(['product_id' => 'required|integer|exists:products_data,id']);
        $productId = (int) $r->product_id;
        $ids       = $this->getWishlistIds();

        if (in_array($productId, $ids)) {
            $ids = array_values(array_diff($ids, [$productId]));
            $this->saveWishlistIds($ids);
            return response()->json(['success' => true, 'action' => 'removed', 'count' => count($ids)]);
        } else {
            $ids[] = $productId;
            $this->saveWishlistIds($ids);
            return response()->json(['success' => true, 'action' => 'added', 'count' => count($ids)]);
        }
    }

    public function remove($productId)
    {
        $ids = $this->getWishlistIds();
        $ids = array_values(array_diff($ids, [(int)$productId]));
        $this->saveWishlistIds($ids);
        return redirect()->route('wishlist')->with('success', 'Removed from wishlist.');
    }

}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartApiController extends Controller
{
    public function index()
    {
        $items = DB::table('cart_items as c')
            ->join('products_data as p', 'p.id', '=', 'c.product_id')
            ->leftJoin('product_variations as pv', 'pv.id', '=', 'c.variation_id')
            ->where('c.user_id', Auth::id())
            ->select(
                'c.id', 'c.product_id', 'c.variation_id', 'c.qty',
                'p.name', 'p.images',
                DB::raw('COALESCE(pv.price, (SELECT MIN(price) FROM product_variations WHERE product_id = c.product_id)) as price'),
                'pv.attributes'
            )
            ->get()
            ->map(fn($i) => $this->formatItem($i));

        $subtotal = $items->sum(fn($i) => $i['price'] * $i['qty']);
        return response()->json(['success' => true, 'data' => ['items' => $items, 'subtotal' => $subtotal, 'count' => $items->count()]]);
    }

    public function add(Request $r)
    {
        $r->validate([
            'product_id'   => 'required|integer|exists:products_data,id',
            'variation_id' => 'nullable|integer|exists:product_variations,id',
            'qty'          => 'nullable|integer|min:1|max:999',
        ]);

        $userId      = Auth::id();
        $productId   = $r->product_id;
        $variationId = $r->variation_id;
        $qty         = max(1, (int) $r->input('qty', 1));

        $existing = DB::table('cart_items')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_id', $variationId)
            ->first();

        // Cap: total units of this product across all its rows must not exceed 100
        $totalQty = DB::table('cart_items')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->sum('qty');

        if ($totalQty + $qty > 100) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot add more than 100 units of the same product to your cart.',
            ], 422);
        }

        // Cap: no more than 50 distinct items (rows) in the cart
        $cartCount = DB::table('cart_items')->where('user_id', $userId)->count();
        if ($cartCount >= 50) {
            return response()->json([
                'success' => false,
                'message' => 'Cart limit reached. Maximum 50 different items allowed.',
            ], 422);
        }

        if ($existing) {
            DB::table('cart_items')->where('id', $existing->id)->update([
                'qty'        => $existing->qty + $qty,
                'updated_at' => now(),
            ]);
            $itemId = $existing->id;
        } else {
            $itemId = DB::table('cart_items')->insertGetId([
                'user_id'      => $userId,
                'product_id'   => $productId,
                'variation_id' => $variationId,
                'qty'          => $qty,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $count = DB::table('cart_items')->where('user_id', $userId)->count();
        return response()->json(['success' => true, 'message' => 'Item added to cart', 'cart_count' => $count, 'item_id' => $itemId], 201);
    }

    public function update(Request $r, $id)
    {
        $r->validate(['qty' => 'required|integer|min:1|max:999']);

        $userId = Auth::id();
        $item   = DB::table('cart_items')->where('id', $id)->where('user_id', $userId)->first();
        if (! $item) return response()->json(['success' => false, 'message' => 'Item not found'], 404);

        // Sum qty of every OTHER row for this product (the current row is being replaced, not added to)
        $otherQty = DB::table('cart_items')
            ->where('user_id', $userId)
            ->where('product_id', $item->product_id)
            ->where('id', '!=', $id)
            ->sum('qty');

        if ($otherQty + $r->qty > 100) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot have more than 100 units of the same product in your cart.',
            ], 422);
        }

        DB::table('cart_items')->where('id', $id)->update(['qty' => $r->qty, 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Cart updated']);
    }

    public function remove($id)
    {
        $deleted = DB::table('cart_items')->where('id', $id)->where('user_id', Auth::id())->delete();
        if (! $deleted) return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        return response()->json(['success' => true, 'message' => 'Item removed']);
    }

    public function clear()
    {
        DB::table('cart_items')->where('user_id', Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'Cart cleared']);
    }

    private function formatItem($item): array
    {
        $imgs = json_decode($item->images ?? '{}', true);
        $attrs = json_decode($item->attributes ?? '{}', true);
        return [
            'id'           => $item->id,
            'product_id'   => $item->product_id,
            'variation_id' => $item->variation_id,
            'name'         => $item->name,
            'price'        => (float)$item->price,
            'qty'          => $item->qty,
            'subtotal'     => round((float)$item->price * $item->qty, 2),
            'image'        => \App\Constants\AppConstants::productThumbnailUrl($imgs),
            'attributes'   => $attrs,
        ];
    }
}

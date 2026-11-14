<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Services\CartRules;
use App\Services\PricingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartApiController extends Controller
{
    public function index()
    {
        $items = DB::table('cart_items as c')
            ->join('products_data as p', 'p.id', '=', 'c.product_id')
            ->leftJoin('product_variations as pv', function ($join) {
                $join->on('pv.id', '=', 'c.variation_id')->on('pv.product_id', '=', 'c.product_id');
            })
            ->where('c.user_id', Auth::id())
            ->select(
                'c.id', 'c.product_id', 'c.variation_id', 'c.qty',
                'p.name', 'p.images', 'p.discount_percentage', 'pv.regular_price',
                DB::raw('COALESCE(pv.price, (SELECT price FROM product_variations WHERE product_id = c.product_id AND main_variation = true AND c.variation_id IS NULL ORDER BY id LIMIT 1), 0) as price'),
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

        $userId = Auth::id();
        $product = DB::table('products_data')->where('id', $r->product_id)->first();
        $variation = CartRules::variation((int) $r->product_id, $r->filled('variation_id') ? (int) $r->variation_id : null);
        if (! $variation) return response()->json(['success' => false, 'message' => 'Invalid product variation.'], 422);
        $rows = DB::table('cart_items')->where('user_id', $userId)->where('product_id', $product->id)
            ->where(function ($q) use ($variation) {
                $q->where('variation_id', $variation->id);
                if ($variation->main_variation) $q->orWhereNull('variation_id');
            })->orderBy('id')->get();
        $qty = (int) $r->input('qty', 1) + (int) $rows->sum('qty');
        if ($error = CartRules::quantityError($product, $variation, $qty)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }
        if ($rows->isEmpty() && DB::table('cart_items')->where('user_id', $userId)->count() >= 50) {
            return response()->json(['success' => false, 'message' => 'Maximum 50 different cart items allowed.'], 422);
        }
        $values = ['variation_id' => $variation->id, 'qty' => $qty, 'updated_at' => now()];
        if ($rows->isNotEmpty()) {
            $itemId = $rows->first()->id;
            DB::table('cart_items')->where('user_id', $userId)->whereIn('id', $rows->pluck('id')->slice(1))->delete();
            DB::table('cart_items')->where('user_id', $userId)->where('id', $itemId)->update($values);
        } else {
            $itemId = DB::table('cart_items')->insertGetId($values + [
                'user_id' => $userId, 'product_id' => $product->id, 'created_at' => now(),
            ]);
        }

        $count = DB::table('cart_items')->where('user_id', $userId)->count();
        return response()->json(['success' => true, 'message' => 'Item added to cart', 'cart_count' => $count, 'item_id' => $itemId], 201);
    }

    public function update(Request $r, $id)
    {
        $r->validate(['qty' => 'required|integer|min:1|max:999']);

        $userId = Auth::id();
        $item   = CartItem::find($id);
        if (! $item) return response()->json(['success' => false, 'message' => 'Item not found'], 404);

        try {
            $this->authorize('manage', $item);
        } catch (AuthorizationException) {
            // Keep the existing response contract and do not reveal ownership.
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $product = DB::table('products_data')->where('id', $item->product_id)->first();
        $variation = CartRules::variation((int) $item->product_id, $item->variation_id ? (int) $item->variation_id : null);
        $duplicates = DB::table('cart_items')->where('user_id', $userId)->where('product_id', $item->product_id)
            ->where('id', '!=', $id)->where(function ($q) use ($variation) {
                $q->where('variation_id', $variation->id ?? 0);
                if ($variation?->main_variation) $q->orWhereNull('variation_id');
            })->sum('qty');
        if ($error = CartRules::quantityError($product, $variation, (int) $r->qty + (int) $duplicates)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        DB::table('cart_items')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update(['qty' => $r->qty, 'variation_id' => $variation->id, 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Cart updated']);
    }

    public function remove($id)
    {
        $item = CartItem::find($id);
        if (! $item) return response()->json(['success' => false, 'message' => 'Item not found'], 404);

        try {
            $this->authorize('manage', $item);
        } catch (AuthorizationException) {
            // Keep the existing response contract and do not reveal ownership.
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $deleted = DB::table('cart_items')
            ->where('id', $item->id)
            ->where('user_id', Auth::id())
            ->delete();
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
        $item->price = PricingService::effectiveVariationPrice($item, $item);
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

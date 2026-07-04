<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\ShippingConfig;
use App\Http\Traits\CartTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    use CartTrait;

    public function index()
    {
        $cart = $this->getCart();

        if (!empty($cart)) {
            $cart    = $this->refreshCartPricing($cart);
            $changed = false;
            foreach ($cart as $rowId => $item) {
                $changed = $changed || ($item['_priceChanged'] ?? false);
                unset($cart[$rowId]['_priceChanged']);
            }
            if ($changed) {
                $this->saveCart($cart);
            }
        }

        $coupon   = session('ramo_coupon');
        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $discount = 0;

        if ($coupon) {
            if ($coupon['discount_type'] === 'percent') {
                $discount = $subtotal * ($coupon['amount'] / 100);
            } else {
                $discount = min((float) $coupon['amount'], $subtotal);
            }
        }

        $shippingFee = ShippingConfig::feeForSubtotal(max(0, $subtotal - $discount));
        $total       = max(0, $subtotal - $discount) + $shippingFee;
        return view('web.cart', compact('cart', 'subtotal', 'discount', 'total', 'coupon', 'shippingFee'));
    }

    private function refreshCartPricing(array $cart): array
    {
        $productIds   = array_unique(array_column($cart, 'product_id'));
        $variationIds = array_filter(array_unique(array_column($cart, 'variation_id')));

        $products = DB::table('products_data')
            ->whereIn('id', $productIds)
            ->get(['id', 'sku', 'discount_percentage'])
            ->keyBy('id');

        $variations = DB::table('product_variations')
            ->whereIn('id', $variationIds)
            ->get(['id', 'regular_price', 'price'])
            ->keyBy('id');

        $mainVariations = DB::table('product_variations')
            ->whereIn('product_id', $productIds)
            ->where('main_variation', true)
            ->get(['id', 'product_id', 'regular_price', 'price'])
            ->keyBy('product_id');

        foreach ($cart as $rowId => &$item) {
            $product = $products[$item['product_id']] ?? null;
            if (!$product) {
                // Product no longer exists — drop it from the cart entirely.
                unset($cart[$rowId]);
                continue;
            }

            $discPct = (float) ($product->discount_percentage ?? 0);
            $varId   = $item['variation_id'] ?? null;
            $varRow  = $varId ? ($variations[$varId] ?? null) : ($mainVariations[$item['product_id']] ?? null);

            $reg = $varRow ? (float) $varRow->regular_price : 0.0;
            $eff = $reg > 0 ? $reg : (float) ($varRow->price ?? 0);
            if ($discPct > 0 && $reg > 0) {
                $eff = round($reg * (1 - $discPct / 100), 2);
            }

            $item['_priceChanged'] = false;
            if (abs(($item['price'] ?? -1) - $eff) > 0.001) {
                $item['price']         = $eff;
                $item['_priceChanged'] = true;
            }
            $item['regular_price'] = $reg > $eff ? $reg : null;
            $item['sku']           = $product->sku ?? null;
        }
        unset($item);

        return $cart;
    }

    public function add(Request $r)
    {
        $r->validate([
            'product_id'   => 'required|integer|exists:products_data,id',
            'variation_id' => 'nullable|integer|exists:product_variations,id',
            'qty'          => 'nullable|integer|min:1|max:999',
        ]);

        $productId   = $r->product_id;
        $variationId = $r->variation_id;
        $qty         = max(1, (int) $r->input('qty', 1));

        $product = DB::table('products_data')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $variation = $variationId
            ? DB::table('product_variations')->where('id', $variationId)->first(['regular_price', 'sale_price', 'price'])
            : DB::table('product_variations')->where('product_id', $productId)->where('main_variation', true)->first(['regular_price', 'sale_price', 'price']);

        $regularPrice = (float) ($variation->regular_price ?? 0);
        $price        = (float) ($variation->price ?? $regularPrice);
        $discPct      = (float) ($product->discount_percentage ?? 0);
        if ($discPct > 0 && $regularPrice > 0 && $price >= $regularPrice) {
            $price = round($regularPrice * (1 - $discPct / 100), 2);
        }

        $imageUrl = \App\Constants\AppConstants::productThumbnailUrl($product->images);

        $attrs = [];
        if ($variationId) {
            $vAttrs = DB::table('product_variations')->where('id', $variationId)->value('attributes');
            if ($vAttrs) {
                $attrs = json_decode($vAttrs, true)
                      ?? json_decode(stripslashes($vAttrs), true)
                      ?? [];
            }
        }

        $rowId = md5($productId . '_' . ($variationId ?? '0'));
        $cart  = $this->getCart();

        if (isset($cart[$rowId])) {
            $cart[$rowId]['qty'] = min($cart[$rowId]['qty'] + $qty, (int) ($product->stock_quantity ?? 999));
            $cart[$rowId]['regular_price'] = $regularPrice > $price ? $regularPrice : null;
        } else {
            $cart[$rowId] = [
                'rowId'         => $rowId,
                'product_id'    => (int) $productId,
                'variation_id'  => $variationId ? (int) $variationId : null,
                'name'          => $product->name,
                'sku'           => $product->sku ?? null,
                'price'         => $price,
                'regular_price' => $regularPrice > $price ? $regularPrice : null,
                'qty'           => $qty,
                'image'         => $imageUrl,
                'stock'         => (int) ($product->stock_quantity ?? 999),
                'attrs'         => $attrs,
            ];
        }

        $this->saveCart($cart);

        return response()->json([
            'success'    => true,
            'message'    => 'Added to cart!',
            'count'      => count($cart),
            'cart_total' => collect($cart)->sum(fn($i) => $i['price'] * $i['qty']),
            'items'      => array_values($cart),
            'row_id'     => $rowId,
        ]);
    }

    private function calcTotals(float $subtotal): array
    {
        $coupon   = session('ramo_coupon');
        $discount = 0;
        if ($coupon) {
            $discount = $coupon['discount_type'] === 'percent'
                ? $subtotal * ($coupon['amount'] / 100)
                : min((float) $coupon['amount'], $subtotal);
        }
        $afterDiscount = max(0, $subtotal - $discount);
        $shippingFee   = ShippingConfig::feeForSubtotal($afterDiscount);

        return [
            'subtotal'    => $subtotal,
            'discount'    => $discount,
            'shippingFee' => $shippingFee,
            'total'       => $afterDiscount + $shippingFee,
        ];
    }

    public function update(Request $r, $rowId)
    {
        $r->validate(['qty' => 'required|integer|min:1|max:999']);
        $cart = $this->getCart();

        if (isset($cart[$rowId])) {
            $cart[$rowId]['qty'] = min((int) $r->qty, $cart[$rowId]['stock']);
            $this->saveCart($cart);
        }

        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $totals   = $this->calcTotals($subtotal);
        $item     = $cart[$rowId] ?? null;

        $hasOldPrice = $item && !empty($item['regular_price']) && $item['regular_price'] > $item['price'];

        return response()->json([
            'success'           => true,
            'item_subtotal'     => $item ? number_format($item['price'] * $item['qty'], 2) : '0.00',
            'item_subtotal_old' => $hasOldPrice ? number_format($item['regular_price'] * $item['qty'], 2) : null,
            'cart_subtotal'     => number_format($totals['subtotal'], 2),
            'shipping_fee'      => $totals['shippingFee'] > 0 ? number_format($totals['shippingFee'], 2) : null,
            'cart_total'        => number_format($totals['total'], 2),
            'count'             => count($cart),
        ]);
    }

    public function remove($rowId)
    {
        $cart = $this->getCart();
        unset($cart[$rowId]);
        $this->saveCart($cart);

        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $totals   = $this->calcTotals($subtotal);

        return response()->json([
            'success'       => true,
            'count'         => count($cart),
            'cart_subtotal' => number_format($totals['subtotal'], 2),
            'shipping_fee'  => $totals['shippingFee'] > 0 ? number_format($totals['shippingFee'], 2) : null,
            'cart_total'    => number_format($totals['total'], 2),
        ]);
    }

    public function clear()
    {
        $this->saveCart([]);
        session()->forget('ramo_coupon');
        return redirect()->route('cart')->with('success', 'Cart cleared.');
    }

    public function applyCoupon(Request $r)
    {
        $r->validate(['code' => 'required|string|max:50']);
        $code   = strtoupper(trim($r->code));
        $coupon = DB::table('coupons')->where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
        }

        $now = now();
        if ($coupon->date_expires && $now->isAfter($coupon->date_expires)) {
            return response()->json(['success' => false, 'message' => 'This coupon has expired.']);
        }

        if (!empty($coupon->vendor_id)) {
            return response()->json(['success' => false, 'message' => 'This is a vendor-specific promo code.']);
        }

        session(['ramo_coupon' => [
            'code'          => $coupon->code,
            'discount_type' => $coupon->discount_type ?? 'percent',
            'amount'        => $coupon->amount ?? 0,
            'description'   => $coupon->description ?? '',
        ]]);

        return response()->json(['success' => true, 'message' => 'Coupon applied!', 'reload' => true]);
    }

    public function removeCoupon()
    {
        session()->forget('ramo_coupon');
        return redirect()->route('cart');
    }

    public function count()
    {
        return response()->json(['count' => count($this->getCart())]);
    }
}

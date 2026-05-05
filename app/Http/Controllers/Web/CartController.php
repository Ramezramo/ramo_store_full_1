<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    private function getCart(): array
    {
        return session('ramo_cart', []);
    }

    private function saveCart(array $cart): void
    {
        session(['ramo_cart' => $cart]);
    }

    public function index()
    {
        $cart = $this->getCart();

        // ── Refresh cart prices using current discount_percentage ──────────
        // Corrects stale entries where price was stored before a discount was applied.
        if (!empty($cart)) {
            $productIds   = array_unique(array_column($cart, 'product_id'));
            $variationIds = array_filter(array_unique(array_column($cart, 'variation_id')));

            $products = DB::table('products_data')
                ->whereIn('id', $productIds)
                ->get(['id', 'discount_percentage'])
                ->keyBy('id');

            $variations = DB::table('product_variations')
                ->whereIn('id', $variationIds)
                ->get(['id', 'regular_price', 'price'])
                ->keyBy('id');

            $changed = false;
            foreach ($cart as $rowId => &$item) {
                $discPct  = (float) ($products[$item['product_id']]->discount_percentage ?? 0);
                $varId    = $item['variation_id'] ?? null;
                $varRow   = $varId ? ($variations[$varId] ?? null) : null;

                if ($varRow) {
                    $reg = (float) $varRow->regular_price;
                    $eff = $reg > 0 ? $reg : (float) $varRow->price;
                    if ($discPct > 0 && $reg > 0) {
                        $eff = round($reg * (1 - $discPct / 100), 2);
                    }
                    if (abs($item['price'] - $eff) > 0.001) {
                        $item['price'] = $eff;
                        $changed = true;
                    }
                }
            }
            unset($item);

            if ($changed) {
                $this->saveCart($cart);
            }
        }
        // ──────────────────────────────────────────────────────────────────

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

        $total = max(0, $subtotal - $discount);
        return view('web.cart', compact('cart', 'subtotal', 'discount', 'total', 'coupon'));
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
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Fetch variation with all price fields so we can apply discount fallback
        $variation = $variationId
            ? DB::table('product_variations')->where('id', $variationId)->first(['regular_price', 'sale_price', 'price'])
            : DB::table('product_variations')->where('product_id', $productId)->where('main_variation', true)->first(['regular_price', 'sale_price', 'price']);

        $regularPrice = (float) ($variation->regular_price ?? 0);
        $price        = (float) ($variation->price ?? $regularPrice);

        // Apply product-level discount_percentage fallback — same logic as parseProduct().
        // When variation price column == regular_price (discount not yet written to variations),
        // compute the effective price from discount_percentage on products_data.
        $discPct = (float) ($product->discount_percentage ?? 0);
        if ($discPct > 0 && $regularPrice > 0 && $price >= $regularPrice) {
            $price = round($regularPrice * (1 - $discPct / 100), 2);
        }

        $imageUrl  = \App\Constants\AppConstants::productThumbnailUrl($product->images);

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
            $cart[$rowId]['qty'] = min($cart[$rowId]['qty'] + $qty, (int)($product->stock_quantity ?? 999));
        } else {
            $cart[$rowId] = [
                'rowId'        => $rowId,
                'product_id'   => $productId,
                'variation_id' => $variationId,
                'name'         => $product->name,
                'price'        => $price,
                'qty'          => $qty,
                'image'        => $imageUrl,
                'stock'        => (int)($product->stock_quantity ?? 999),
                'attrs'        => $attrs,
            ];
        }

        $this->saveCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Added to cart!',
            'count'   => count($cart),
            'cart_total' => collect($cart)->sum(fn($i) => $i['price'] * $i['qty']),
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
        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => max(0, $subtotal - $discount),
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

        return response()->json([
            'success'      => true,
            'item_subtotal'=> $item ? number_format($item['price'] * $item['qty'], 2) : '0.00',
            'cart_subtotal'=> number_format($totals['subtotal'], 2),
            'cart_total'   => number_format($totals['total'], 2),
            'count'        => count($cart),
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
            'success'      => true,
            'count'        => count($cart),
            'cart_subtotal'=> number_format($totals['subtotal'], 2),
            'cart_total'   => number_format($totals['total'], 2),
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

        if (! $coupon) {
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

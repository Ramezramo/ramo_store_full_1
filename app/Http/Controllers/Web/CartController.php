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

    /**
     * Return the seller-configured quantity range constrained by live variation stock.
     * A zero maximum means "unlimited" apart from stock availability.
     */
    private function quantityBounds(object $product, int $stock): array
    {
        $minimum = max(1, (int) ($product->minimum_order_qty ?? 1));
        $maximum = $stock;
        $configuredMaximum = (int) ($product->max_orders_per_person ?? 0);

        if ($configuredMaximum > 0) {
            $maximum = min($maximum, $configuredMaximum);
        }
        if ((bool) ($product->sold_individually ?? false)) {
            $maximum = min($maximum, 1);
        }

        return [$minimum, $maximum];
    }

    private function localizedCartProductName(object $product): string
    {
        $fallback = (string) ($product->name ?? '');
        if (session('locale', 'en') !== 'ar' || empty($product->translations)) {
            return $fallback;
        }

        $translations = is_string($product->translations)
            ? json_decode($product->translations, true)
            : $product->translations;
        foreach (is_array($translations) ? $translations : [] as $translation) {
            if (is_array($translation) && ($translation['locale'] ?? '') === 'ar') {
                return trim((string) ($translation['name'] ?? '')) ?: $fallback;
            }
        }

        return $fallback;
    }

    private function quantityError(string $productName, int $quantity, int $minimum, int $maximum): ?string
    {
        if ($quantity < $minimum) {
            return "Minimum order quantity for \"{$productName}\" is {$minimum}.";
        }
        if ($maximum < $minimum) {
            return "\"{$productName}\" does not have enough stock to meet its minimum order quantity of {$minimum}.";
        }
        if ($quantity > $maximum) {
            return "You can order up to {$maximum} unit(s) of \"{$productName}\" per order.";
        }

        return null;
    }

    public function index()
    {
        $cart = $this->getCart();

        if (!empty($cart)) {
            $cartBeforeRefresh = $cart;
            $cart = $this->refreshCartPricing($cart);
            $quantityMessages = [];
            foreach ($cart as $rowId => $item) {
                if (!empty($item['_quantityMessage'])) {
                    $quantityMessages[] = $item['_quantityMessage'];
                }
                unset($cart[$rowId]['_priceChanged'], $cart[$rowId]['_quantityMessage']);
            }
            if ($cart !== $cartBeforeRefresh) {
                $this->saveCart($cart);
            }
            if (!empty($quantityMessages)) {
                session()->flash('error', implode(' ', $quantityMessages));
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

        $afterDiscount = max(0, $subtotal - $discount);
        $shippingFee = ShippingConfig::feeForSubtotal($afterDiscount);
        $freeShippingEnabled = (bool) ShippingConfig::val('free_shipping_enabled', true);
        $freeShippingThreshold = (float) ShippingConfig::val('free_shipping_threshold', 1000);
        $total       = $afterDiscount + $shippingFee;
        return view('web.cart', compact(
            'cart', 'subtotal', 'discount', 'total', 'coupon', 'shippingFee',
            'freeShippingEnabled', 'freeShippingThreshold'
        ));
    }

    private function refreshCartPricing(array $cart): array
    {
        $productIds   = array_unique(array_column($cart, 'product_id'));
        $variationIds = array_filter(array_unique(array_column($cart, 'variation_id')));

        $products = DB::table('products_data')
            ->whereIn('id', $productIds)
            ->get(['id', 'name', 'translations', 'sku', 'discount_percentage', 'minimum_order_qty', 'max_orders_per_person', 'sold_individually'])
            ->keyBy('id');

        $variations = DB::table('product_variations')
            ->whereIn('id', $variationIds)
            ->get(['id', 'product_id', 'regular_price', 'price', 'stock_quantity', 'stock_status', 'status'])
            ->keyBy('id');

        $mainVariations = DB::table('product_variations')
            ->whereIn('product_id', $productIds)
            ->where('main_variation', true)
            ->get(['id', 'product_id', 'regular_price', 'price', 'stock_quantity', 'stock_status', 'status'])
            ->keyBy('product_id');

        foreach ($cart as $rowId => &$item) {
            $product = $products[$item['product_id']] ?? null;
            if (!$product) {
                // Product no longer exists — drop it from the cart entirely.
                unset($cart[$rowId]);
                continue;
            }

            $item['display_name'] = $this->localizedCartProductName($product);

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

            $liveStock = max(0, (int) ($varRow->stock_quantity ?? 0));
            [$minimumQuantity, $maximumQuantity] = $this->quantityBounds($product, $liveStock);
            $item['stock'] = $liveStock;
            $item['minimum_qty'] = $minimumQuantity;
            $item['maximum_qty'] = $maximumQuantity;

            $currentQuantity = (int) ($item['qty'] ?? 0);
            if ($maximumQuantity < $minimumQuantity) {
                unset($cart[$rowId]);
                session()->flash('error', "\"{$product->name}\" was removed because it no longer has enough stock to meet its minimum order quantity.");
                continue;
            }
            if ($currentQuantity > $maximumQuantity) {
                $item['qty'] = $maximumQuantity;
                $item['_quantityMessage'] = "Quantity for \"{$product->name}\" was reduced from {$currentQuantity} to {$maximumQuantity} to match the current per-order limit and stock.";
            }
            if ($currentQuantity < $minimumQuantity) {
                $item['qty'] = $minimumQuantity;
                $item['_quantityMessage'] = "Quantity for \"{$product->name}\" was adjusted from {$currentQuantity} to the seller minimum of {$minimumQuantity}.";
            }
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
            ? DB::table('product_variations')
                ->where('id', $variationId)
                ->where('product_id', $productId)
                ->first(['id', 'attributes', 'regular_price', 'sale_price', 'price', 'stock_quantity', 'stock_status', 'status'])
            : DB::table('product_variations')
                ->where('product_id', $productId)
                ->where('main_variation', true)
                ->first(['id', 'attributes', 'regular_price', 'sale_price', 'price', 'stock_quantity', 'stock_status', 'status']);

        if (! $variation || (($variation->status ?? 'publish') !== 'publish') || (($variation->stock_status ?? 'instock') !== 'instock')) {
            return response()->json(['success' => false, 'message' => 'The selected product variation is unavailable.'], 422);
        }

        $stock = (int) ($variation->stock_quantity ?? 0);
        if ($stock < 1) {
            return response()->json(['success' => false, 'message' => 'The selected product variation is out of stock.'], 422);
        }

        $regularPrice = (float) ($variation->regular_price ?? 0);
        $price        = (float) ($variation->price ?? $regularPrice);
        $discPct      = (float) ($product->discount_percentage ?? 0);
        if ($discPct > 0 && $regularPrice > 0 && $price >= $regularPrice) {
            $price = round($regularPrice * (1 - $discPct / 100), 2);
        }

        $imageUrl = \App\Constants\AppConstants::productThumbnailUrl($product->images);
        $attrs = json_decode($variation->attributes ?? '[]', true)
            ?? json_decode(stripslashes($variation->attributes ?? '[]'), true)
            ?? [];

        $resolvedVariationId = (int) $variation->id;
        $rowId = md5($productId . '_' . $resolvedVariationId);
        $cart  = $this->getCart();
        $newQuantity = (int) ($cart[$rowId]['qty'] ?? 0) + $qty;
        [$minimumQuantity, $maximumQuantity] = $this->quantityBounds($product, $stock);

        if ($error = $this->quantityError($product->name, $newQuantity, $minimumQuantity, $maximumQuantity)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        if (isset($cart[$rowId])) {
            $cart[$rowId]['qty'] = $newQuantity;
            $cart[$rowId]['regular_price'] = $regularPrice > $price ? $regularPrice : null;
            $cart[$rowId]['stock'] = $stock;
        } else {
            $cart[$rowId] = [
                'rowId'         => $rowId,
                'product_id'    => (int) $productId,
                'variation_id'  => $resolvedVariationId,
                'name'          => $product->name,
                'sku'           => $product->sku ?? null,
                'price'         => $price,
                'regular_price' => $regularPrice > $price ? $regularPrice : null,
                'qty'           => $newQuantity,
                'image'         => $imageUrl,
                'stock'         => $stock,
                'attrs'         => is_array($attrs) ? $attrs : [],
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

        $enabled = (bool) ShippingConfig::val('free_shipping_enabled', true);
        $threshold = (float) ShippingConfig::val('free_shipping_threshold', 1000);
        $progress = $enabled && $threshold > 0
            ? min(100, ($afterDiscount / $threshold) * 100)
            : 100;

        return [
            'subtotal'              => $subtotal,
            'discount'              => $discount,
            'shippingFee'           => $shippingFee,
            'total'                 => $afterDiscount + $shippingFee,
            'freeShippingEnabled'   => $enabled,
            'freeShippingThreshold' => $threshold,
            'freeShippingRemaining' => max(0, $threshold - $afterDiscount),
            'freeShippingProgress'  => $progress,
        ];
    }

    public function update(Request $r, $rowId)
    {
        $r->validate(['qty' => 'required|integer|min:1|max:999']);
        $cart = $this->getCart();

        if (! isset($cart[$rowId])) {
            return response()->json(['success' => false, 'message' => 'Cart item not found.'], 404);
        }

        $item = $cart[$rowId];
        $product = DB::table('products_data')->where('id', $item['product_id'])->first();
        $variation = DB::table('product_variations')
            ->where('id', $item['variation_id'])
            ->where('product_id', $item['product_id'])
            ->first(['id', 'stock_quantity', 'stock_status', 'status']);

        if (! $product || ! $variation || (($variation->status ?? 'publish') !== 'publish') || (($variation->stock_status ?? 'instock') !== 'instock')) {
            return response()->json(['success' => false, 'message' => 'This product variation is no longer available.'], 422);
        }

        $stock = (int) ($variation->stock_quantity ?? 0);
        [$minimumQuantity, $maximumQuantity] = $this->quantityBounds($product, $stock);
        $requestedQuantity = (int) $r->qty;

        if ($error = $this->quantityError($product->name, $requestedQuantity, $minimumQuantity, $maximumQuantity)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        $cart[$rowId]['qty'] = $requestedQuantity;
        $cart[$rowId]['stock'] = $stock;
        $this->saveCart($cart);

        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $totals   = $this->calcTotals($subtotal);
        $item     = $cart[$rowId];
        $hasOldPrice = !empty($item['regular_price']) && $item['regular_price'] > $item['price'];

        return response()->json([
            'success'           => true,
            'item_subtotal'     => number_format($item['price'] * $item['qty'], 2),
            'item_subtotal_old' => $hasOldPrice ? number_format($item['regular_price'] * $item['qty'], 2) : null,
            'cart_subtotal'     => number_format($totals['subtotal'], 2),
            'shipping_fee'      => $totals['shippingFee'] > 0 ? number_format($totals['shippingFee'], 2) : null,
            'cart_total'             => number_format($totals['total'], 2),
            'cart_discount'           => number_format($totals['discount'], 2),
            'free_shipping_enabled'  => $totals['freeShippingEnabled'],
            'free_shipping_threshold' => number_format($totals['freeShippingThreshold'], 2),
            'free_shipping_remaining' => number_format($totals['freeShippingRemaining'], 2),
            'free_shipping_progress'  => $totals['freeShippingProgress'],
            'count'                  => count($cart),
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
            'shipping_fee'           => $totals['shippingFee'] > 0 ? number_format($totals['shippingFee'], 2) : null,
            'cart_total'             => number_format($totals['total'], 2),
            'cart_discount'         => number_format($totals['discount'], 2),
            'free_shipping_enabled' => $totals['freeShippingEnabled'],
            'free_shipping_threshold' => number_format($totals['freeShippingThreshold'], 2),
            'free_shipping_remaining' => number_format($totals['freeShippingRemaining'], 2),
            'free_shipping_progress' => $totals['freeShippingProgress'],
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

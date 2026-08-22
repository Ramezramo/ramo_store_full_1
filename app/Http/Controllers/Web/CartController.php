<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\AuthConfig;
use App\Helpers\ShippingConfig;
use App\Http\Controllers\CouponController;
use App\Http\Traits\CartTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    use CartTrait;

    private bool $couponWasInvalidated = false;
    private string $couponInvalidationMessage = '';
    private string $couponInvalidationCode = '';

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

    private function localized(string $english, string $arabic): string
    {
        return session('locale', 'en') === 'ar' ? $arabic : $english;
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

    private function quantityError(string $productName, int $quantity, int $minimum, int $maximum, int $existingQuantity = 0): ?string
    {
        if ($quantity < $minimum) {
            return $this->localized("Minimum order quantity for \"{$productName}\" is {$minimum}.", "أقل كمية للطلب من \"{$productName}\" هي {$minimum}.");
        }
        if ($maximum < $minimum) {
            return $this->localized("\"{$productName}\" does not have enough stock to meet its minimum order quantity of {$minimum}.", "\"{$productName}\" مفيش منه مخزون كفاية للحد الأدنى اللي هو {$minimum}.");
        }
        if ($quantity > $maximum) {
            $existingQuantity = max(0, $existingQuantity);
            if ($existingQuantity > 0) {
                $additionalAvailable = max(0, $maximum - $existingQuantity);
                return $this->localized(
                    "You already have {$existingQuantity} unit(s) of \"{$productName}\" in your cart, and that quantity counts against the available stock. Only {$additionalAvailable} more unit(s) can be added (maximum {$maximum} per order).",
                    "إنت ضايف {$existingQuantity} قطعة من \"{$productName}\" في السلة، والكمية دي محسوبة من المتاح. المتبقي للإضافة {$additionalAvailable} قطعة بس (الحد الأقصى {$maximum} في الطلب الواحد)."
                );
            }

            return $this->localized("You can order up to {$maximum} unit(s) of \"{$productName}\" per order.", "تقدر تطلب لحد {$maximum} قطعة من \"{$productName}\" في الطلب الواحد.");
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

        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $coupon   = $this->appliedCoupon($subtotal);
        $discount = 0;

        if ($coupon) {
            if ($coupon['discount_type'] === 'percent') {
                $discount = $subtotal * ($coupon['amount'] / 100);
            } else {
                $discount = min((float) $coupon['amount'], $subtotal);
            }
        }

        $afterDiscount = max(0, $subtotal - $discount);
        $freeShipping = is_array($coupon) && ! empty($coupon['free_shipping']);
        $shippingFee = $freeShipping ? 0.0 : ShippingConfig::feeForSubtotal($afterDiscount);
        $freeShippingEnabled = (bool) ShippingConfig::val('free_shipping_enabled', true);
        $freeShippingThreshold = (float) ShippingConfig::val('free_shipping_threshold', 1000);
        $hasPreviousOrders = empty($cart) && Auth::check()
            ? DB::table('orders')->where('customer_id', Auth::id())->exists()
            : false;
        $total       = $afterDiscount + $shippingFee;
        $authConfig  = AuthConfig::get();
        return view('web.cart', compact(
            'cart', 'subtotal', 'discount', 'total', 'coupon', 'shippingFee',
            'freeShippingEnabled', 'freeShippingThreshold', 'hasPreviousOrders', 'authConfig'
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
            $item['is_unavailable'] = ! $varRow
                || $liveStock < 1
                || (($varRow->status ?? 'publish') !== 'publish')
                || (($varRow->stock_status ?? 'instock') !== 'instock');

            $currentQuantity = (int) ($item['qty'] ?? 0);
            if ($item['is_unavailable'] || $maximumQuantity < $minimumQuantity) {
                // Keep the row visible so the customer can understand why checkout
                // is blocked and remove the stale item manually.
                $item['is_unavailable'] = true;
                continue;
            }
            if ($currentQuantity > $maximumQuantity) {
                $item['qty'] = $maximumQuantity;
                $item['_quantityMessage'] = $this->localized("Quantity for \"{$product->name}\" was reduced from {$currentQuantity} to {$maximumQuantity} to match the current per-order limit and stock.", "كمية \"{$this->localizedCartProductName($product)}\" اتخفضت من {$currentQuantity} لـ {$maximumQuantity} حسب الحد المسموح والمخزون الحالي.");
            }
            if ($currentQuantity < $minimumQuantity) {
                $item['qty'] = $minimumQuantity;
                $item['_quantityMessage'] = $this->localized("Quantity for \"{$product->name}\" was adjusted from {$currentQuantity} to the seller minimum of {$minimumQuantity}.", "كمية \"{$this->localizedCartProductName($product)}\" اتعدلت من {$currentQuantity} للحد الأدنى عند البائع: {$minimumQuantity}.");
            }
        }
        unset($item);

        return $cart;
    }

    private function findVariation(int $productId, ?int $variationId): ?object
    {
        $query = DB::table('product_variations')->where('product_id', $productId);
        if ($variationId !== null) {
            $query->where('id', $variationId);
        } else {
            $query->where('main_variation', true);
        }

        return $query->first([
            'id', 'attributes', 'regular_price', 'sale_price', 'price',
            'stock_quantity', 'stock_status', 'status',
        ]);
    }

    /**
     * Add one variation to an in-memory cart and return a structured result.
     * The caller decides whether and when to persist the cart.
     */
    private function addVariationToCart(array &$cart, object $product, object $variation, int $qty): array
    {
        if (($variation->status ?? 'publish') !== 'publish' || ($variation->stock_status ?? 'instock') !== 'instock') {
            return ['success' => false, 'message' => $this->localized('The selected product variation is unavailable.', 'الاختيار اللي اخترته مش متاح.')];
        }

        $stock = (int) ($variation->stock_quantity ?? 0);
        if ($stock < 1) {
            return ['success' => false, 'message' => $this->localized('The selected product variation is out of stock.', 'الاختيار اللي اخترته خلص من المخزون.')];
        }

        $regularPrice = (float) ($variation->regular_price ?? 0);
        $price = (float) ($variation->price ?? $regularPrice);
        $discPct = (float) ($product->discount_percentage ?? 0);
        if ($discPct > 0 && $regularPrice > 0 && $price >= $regularPrice) {
            $price = round($regularPrice * (1 - $discPct / 100), 2);
        }

        $resolvedVariationId = (int) $variation->id;
        $rowId = md5($product->id . '_' . $resolvedVariationId);
        $existingQuantity = (int) ($cart[$rowId]['qty'] ?? 0);
        $newQuantity = $existingQuantity + $qty;
        [$minimumQuantity, $maximumQuantity] = $this->quantityBounds($product, $stock);

        if ($error = $this->quantityError((string) $product->name, $newQuantity, $minimumQuantity, $maximumQuantity, $existingQuantity)) {
            return ['success' => false, 'message' => $error, 'row_id' => $rowId];
        }

        $attrs = json_decode($variation->attributes ?? '[]', true)
            ?? json_decode(stripslashes($variation->attributes ?? '[]'), true)
            ?? [];
        $imageUrl = \App\Constants\AppConstants::productThumbnailUrl($product->images);

        if (isset($cart[$rowId])) {
            $cart[$rowId]['qty'] = $newQuantity;
            $cart[$rowId]['price'] = $price;
            $cart[$rowId]['regular_price'] = $regularPrice > $price ? $regularPrice : null;
            $cart[$rowId]['stock'] = $stock;
        } else {
            $cart[$rowId] = [
                'rowId' => $rowId,
                'product_id' => (int) $product->id,
                'variation_id' => $resolvedVariationId,
                'name' => $this->localizedCartProductName($product),
                'sku' => $product->sku ?? null,
                'price' => $price,
                'regular_price' => $regularPrice > $price ? $regularPrice : null,
                'qty' => $newQuantity,
                'image' => $imageUrl,
                'stock' => $stock,
                'attrs' => is_array($attrs) ? $attrs : [],
            ];
        }

        return ['success' => true, 'row_id' => $rowId, 'item' => $cart[$rowId]];
    }

    private function cartAddResponse(array $cart, array $extra = []): array
    {
        return array_merge([
            'success' => true,
            'message' => $this->localized('Added to cart!', 'اتضاف للسلة!'),
            'count' => count($cart),
            'cart_total' => collect($cart)->sum(fn($i) => $i['price'] * $i['qty']),
            'items' => array_values($cart),
        ], $extra);
    }

    public function add(Request $r)
    {
        $r->validate([
            'product_id' => 'required|integer|exists:products_data,id',
            'variation_id' => 'nullable|integer|exists:product_variations,id',
            'qty' => 'nullable|integer|min:1|max:999',
        ]);

        $productId = (int) $r->input('product_id');
        $product = DB::table('products_data')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => $this->localized('Product not found', 'مش لاقيين المنتج ده.')], 404);
        }

        $variation = $this->findVariation($productId, $r->filled('variation_id') ? (int) $r->input('variation_id') : null);
        if (!$variation) {
            return response()->json(['success' => false, 'message' => $this->localized('The selected product variation is unavailable.', 'الاختيار اللي اخترته مش متاح.')], 422);
        }

        $cart = $this->getCart();
        $result = $this->addVariationToCart($cart, $product, $variation, max(1, (int) $r->input('qty', 1)));
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        $this->saveCart($cart);
        return response()->json($this->cartAddResponse($cart, ['row_id' => $result['row_id']]));
    }

    public function addMultiple(Request $r)
    {
        $r->validate([
            'product_id' => 'required|integer|exists:products_data,id',
            'items' => 'required|array|min:1|max:20',
            'items.*.variation_id' => 'required|integer|exists:product_variations,id',
            'items.*.qty' => 'required|integer|min:1|max:999',
        ]);

        $productId = (int) $r->input('product_id');
        $product = DB::table('products_data')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => $this->localized('Product not found', 'مش لاقيين المنتج ده.')], 404);
        }

        $cart = $this->getCart();
        $failed = [];
        $addedRowIds = [];

        foreach ($r->input('items', []) as $item) {
            $variationId = (int) $item['variation_id'];
            $variation = $this->findVariation($productId, $variationId);
            $result = $variation
                ? $this->addVariationToCart($cart, $product, $variation, (int) $item['qty'])
                : ['success' => false, 'message' => $this->localized('The selected product variation is unavailable.', 'الاختيار اللي اخترته مش متاح.')];

            if ($result['success']) {
                $addedRowIds[] = $result['row_id'];
            } else {
                $failed[] = [
                    'variation_id' => $variationId,
                    'qty' => (int) $item['qty'],
                    'attributes' => $variation ? (json_decode($variation->attributes ?? '[]', true) ?: []) : [],
                    'message' => $result['message'],
                ];
            }
        }

        if ($addedRowIds) {
            $this->saveCart($cart);
        }

        $response = $this->cartAddResponse($cart, [
            'failed_items' => $failed,
            'row_ids' => $addedRowIds,
        ]);
        if (!$addedRowIds) {
            $response['success'] = false;
            $response['message'] = $this->localized('No selected variations could be added.', 'مقدرناش نضيف أي اختيار من اللي حددتهم.');
            return response()->json($response, 422);
        }

        if ($failed) {
            $response['message'] = $this->localized('Some selected variations could not be added.', 'في اختيارات من اللي حددتهم ما اتضافتش.');
        }

        return response()->json($response);
    }

    private function appliedCoupon(?float $subtotal = null): ?array
    {
        $coupon = session('ramo_coupon');
        if (!is_array($coupon) || empty($coupon['code'])) {
            return null;
        }

        $record = DB::table('coupons')
            ->whereRaw('LOWER(code) = ?', [strtolower(trim((string) $coupon['code']))])
            ->first(['code', 'discount_type', 'amount', 'free_shipping', 'description', 'status', 'date_expires', 'minimum_amount', 'maximum_amount']);

        $invalidMessage = null;
        if (! $record || ($record->status ?? 'publish') !== 'publish') {
            $invalidMessage = $this->localized('This coupon is no longer available.', 'الكوبون ده مبقاش متاح.');
        } elseif ($record->date_expires && now()->isAfter($record->date_expires)) {
            $invalidMessage = $this->localized('This coupon has expired.', 'الكوبون ده انتهت صلاحيته.');
        } elseif ($subtotal !== null && $subtotal < (float) ($record->minimum_amount ?? 0)) {
            $invalidMessage = $this->localized(
                'This coupon requires a higher cart subtotal.',
                'الكوبون ده محتاج قيمة سلة أعلى من '.$record->minimum_amount.' جنيه.'
            );
        } elseif ($subtotal !== null && (float) ($record->maximum_amount ?? 0) > 0 && $subtotal > (float) $record->maximum_amount) {
            $invalidMessage = $this->localized(
                'This coupon cannot be used above its maximum cart subtotal.',
                'الكوبون ده متاح لحد قيمة سلة '.$record->maximum_amount.' جنيه بس. عدّل الكمية أو شيل الكوبون عشان تكمل.'
            );
        }

        if ($invalidMessage !== null) {
            $this->couponWasInvalidated = true;
            $this->couponInvalidationMessage = $invalidMessage;
            $this->couponInvalidationCode = strtoupper(trim((string) ($coupon['code'] ?? '')));
            session()->forget('ramo_coupon');
            session()->flash('coupon_error', $invalidMessage);
            session()->flash('coupon_error_code', $this->couponInvalidationCode);
            return null;
        }

        $coupon['code'] = $record->code;
        $coupon['discount_type'] = $record->discount_type ?? 'percent';
        $coupon['amount'] = $record->amount ?? 0;
        $coupon['free_shipping'] = (bool) ($record->free_shipping ?? false);
        $coupon['description'] = $record->description ?? ($coupon['description'] ?? '');
        session(['ramo_coupon' => $coupon]);

        return $coupon;
    }

    private function calcTotals(float $subtotal): array
    {
        $coupon   = $this->appliedCoupon($subtotal);
        $discount = 0;
        if ($coupon) {
            $discount = $coupon['discount_type'] === 'percent'
                ? $subtotal * ($coupon['amount'] / 100)
                : min((float) $coupon['amount'], $subtotal);
        }
        $afterDiscount = max(0, $subtotal - $discount);
        $freeShipping = is_array($coupon) && ! empty($coupon['free_shipping']);
        $shippingFee   = $freeShipping ? 0.0 : ShippingConfig::feeForSubtotal($afterDiscount);

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
            'freeShippingCoupon'    => $freeShipping,
            'couponInvalid'         => $this->couponWasInvalidated,
            'couponMessage'         => $this->couponInvalidationMessage,
            'couponErrorCode'       => $this->couponInvalidationCode,
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
            return response()->json(['success' => false, 'message' => $this->localized('Cart item not found.', 'مش لاقيين المنتج ده في السلة.')], 404);
        }

        $item = $cart[$rowId];
        $product = DB::table('products_data')->where('id', $item['product_id'])->first();
        $variation = DB::table('product_variations')
            ->where('id', $item['variation_id'])
            ->where('product_id', $item['product_id'])
            ->first(['id', 'stock_quantity', 'stock_status', 'status']);

        if (! $product || ! $variation || (($variation->status ?? 'publish') !== 'publish') || (($variation->stock_status ?? 'instock') !== 'instock')) {
            return response()->json(['success' => false, 'message' => $this->localized('This product variation is no longer available.', 'الاختيار ده مبقاش متاح.')], 422);
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

        $response = [
            'success'           => true,
            'item_subtotal'     => number_format($item['price'] * $item['qty'], 2),
            'item_subtotal_old' => $hasOldPrice ? number_format($item['regular_price'] * $item['qty'], 2) : null,
            'cart_subtotal'     => number_format($totals['subtotal'], 2),
            'shipping_fee'      => $totals['shippingFee'] > 0 ? number_format($totals['shippingFee'], 2) : null,
            'cart_total'             => number_format($totals['total'], 2),
            'cart_discount'           => number_format($totals['discount'], 2),
            'coupon_free_shipping'    => $totals['freeShippingCoupon'],
            'coupon_invalid'          => $totals['couponInvalid'],
            'coupon_message'          => $totals['couponMessage'],
            'free_shipping_enabled'  => $totals['freeShippingEnabled'],
            'free_shipping_threshold' => number_format($totals['freeShippingThreshold'], 2),
            'free_shipping_remaining' => number_format($totals['freeShippingRemaining'], 2),
            'free_shipping_progress'  => $totals['freeShippingProgress'],
            'count'                  => count($cart),
        ];

        if (! $r->expectsJson()) {
            if ($totals['couponInvalid']) {
                return redirect()->route('cart')->with([
                    'coupon_error' => $totals['couponMessage'],
                    'coupon_error_code' => $totals['couponErrorCode'],
                ]);
            }
            return redirect()->route('cart')->with('success', $this->localized('Quantity updated.', 'الكمية اتحدّثت.'));
        }

        return response()->json($response);
    }

    public function remove(Request $r, $rowId)
    {
        $cart = $this->getCart();
        unset($cart[$rowId]);
        $this->saveCart($cart);

        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $totals   = $this->calcTotals($subtotal);

        $response = [
            'success'       => true,
            'count'         => count($cart),
            'cart_subtotal' => number_format($totals['subtotal'], 2),
            'shipping_fee'           => $totals['shippingFee'] > 0 ? number_format($totals['shippingFee'], 2) : null,
            'cart_total'             => number_format($totals['total'], 2),
            'cart_discount'         => number_format($totals['discount'], 2),
            'coupon_free_shipping'  => $totals['freeShippingCoupon'],
            'coupon_invalid'        => $totals['couponInvalid'],
            'coupon_message'        => $totals['couponMessage'],
            'free_shipping_enabled' => $totals['freeShippingEnabled'],
            'free_shipping_threshold' => number_format($totals['freeShippingThreshold'], 2),
            'free_shipping_remaining' => number_format($totals['freeShippingRemaining'], 2),
            'free_shipping_progress' => $totals['freeShippingProgress'],
        ];

        if (! $r->expectsJson()) {
            if ($totals['couponInvalid']) {
                return redirect()->route('cart')->with([
                    'coupon_error' => $totals['couponMessage'],
                    'coupon_error_code' => $totals['couponErrorCode'],
                ]);
            }
            return redirect()->route('cart')->with('success', $this->localized('Item removed.', 'المنتج اتشال من السلة.'));
        }

        return response()->json($response);
    }

    public function clear()
    {
        $this->saveCart([]);
        session()->forget('ramo_coupon');
        return redirect()->route('cart')->with('success', $this->localized('Cart cleared.', 'السلة اتفضّت.'));
    }

    public function applyCoupon(Request $r)
    {
        $r->validate(['code' => 'required|string|max:50']);
        $code = strtoupper(trim($r->code));
        $cart = $this->getCart();
        $subtotal = round(collect($cart)->sum(fn ($item) => (float) ($item['price'] ?? 0) * (int) ($item['qty'] ?? 0)), 2);

        if ($subtotal <= 0) {
            $message = $this->localized('Your cart is empty.', 'السلة فاضية.');
            if (! $r->expectsJson()) {
                return redirect()->route('cart')->with('coupon_error', $message);
            }
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $validation = app(CouponController::class)->validateCouponRules(
            $code,
            $subtotal,
            Auth::id()
        );

        if (! $validation['valid']) {
            $message = $this->localized($validation['message'], 'الكوبون ده مش متاح للسلة دي.');
            if (! $r->expectsJson()) {
                return redirect()->route('cart')->with([
                    'coupon_error' => $message,
                    'coupon_error_code' => $code,
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $validation['code'] ?: 422);
        }

        $coupon = DB::table('coupons')->where('code', $code)->first();
        if (! $coupon || ! empty($coupon->vendor_id)) {
            $message = $this->localized('This is a vendor-specific promo code.', 'كود الخصم ده خاص بمتجر معين.');
            if (! $r->expectsJson()) {
                return redirect()->route('cart')->with([
                    'coupon_error' => $message,
                    'coupon_error_code' => $code,
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        session(['ramo_coupon' => [
            'code'          => $coupon->code,
            'discount_type' => $coupon->discount_type ?? 'percent',
            'amount'        => $coupon->amount ?? 0,
            'free_shipping' => (bool) ($coupon->free_shipping ?? false),
            'description'   => $coupon->description ?? '',
        ]]);

        if (! $r->expectsJson()) {
            return redirect()->route('cart')->with('success', $this->localized('Coupon applied!', 'كود الخصم اتطبق!'));
        }

        return response()->json(['success' => true, 'message' => $this->localized('Coupon applied!', 'كود الخصم اتطبق!'), 'reload' => true]);
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

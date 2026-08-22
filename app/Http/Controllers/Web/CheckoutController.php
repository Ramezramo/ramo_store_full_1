<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AuthConfig;
use App\Helpers\PaymentConfig;
use App\Helpers\ShippingConfig;
use App\Helpers\TaxConfig;
use App\Http\Controllers\Controller;
use App\Http\Traits\CartTrait;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    use CartTrait;

    private function requiresLogin(): bool
    {
        return ! Auth::check() && ! AuthConfig::val('guest_checkout', false);
    }

    private function localized(string $english, string $arabic): string
    {
        return session('locale', 'en') === 'ar' ? $arabic : $english;
    }

    private function localizedProductName(object $product): string
    {
        $fallback = (string) ($product->name ?? '');
        if (session('locale', 'en') !== 'ar' || empty($product->translations)) {
            return $fallback;
        }
        $translations = is_string($product->translations)
            ? (json_decode($product->translations, true) ?? [])
            : (array) $product->translations;
        $arabic = collect($translations)->first(fn ($translation) => ($translation['locale'] ?? null) === 'ar');
        return trim((string) ($arabic['name'] ?? '')) ?: $fallback;
    }

    private function couponValidationError(string $message): string
    {
        if (str_contains(strtolower($message), 'cannot exceed')) {
            return $this->localized(
                'This coupon cannot be used above the current cart subtotal. Adjust the quantity or remove it to continue.',
                'الكوبون ده مش متاح لقيمة السلة الحالية لأنها عدّت الحد الأقصى. عدّل الكمية أو شيل الكوبون عشان تكمل.'
            );
        }

        if (str_contains(strtolower($message), 'at least')) {
            return $this->localized(
                'This coupon requires a higher cart subtotal. Adjust the quantity or remove it to continue.',
                'الكوبون ده محتاج قيمة سلة أعلى. زوّد الكمية أو شيل الكوبون عشان تكمل.'
            );
        }

        return $this->localized(
            'The applied coupon is no longer valid for this cart. Remove it to continue.',
            'الكوبون المطبّق مبقاش صالح للسلة دي. شيله عشان تكمل.'
        );
    }

    private function cartQuantityIssue(array $cart): ?string
    {
        $productIds = collect($cart)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $products = DB::table('products_data')->whereIn('id', $productIds)->get()->keyBy('id');
        $variations = DB::table('product_variations')->whereIn('product_id', $productIds)->get()->keyBy('id');

        foreach ($cart as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $product = $products->get($productId);
            $quantity = (int) ($item['qty'] ?? 0);
            $variation = !empty($item['variation_id'])
                ? $variations->get((int) $item['variation_id'])
                : $variations->first(fn ($v) => (int) $v->product_id === $productId && (bool) $v->main_variation);

            if (! $product || ! $variation || (int) $variation->product_id !== $productId) {
                return $this->localized('One or more items in your cart are no longer available. Please review your cart.', 'منتج أو أكتر في السلة مبقاش متاح. راجع السلة.');
            }
            if (($product->status ?? 'publish') !== 'publish' || ($variation->status ?? 'publish') !== 'publish' || ($variation->stock_status ?? 'instock') !== 'instock' || (int) ($variation->stock_quantity ?? 0) < 1) {
                return $this->localized('One or more items in your cart are out of stock. Remove them before continuing.', 'فيه منتج أو أكتر في السلة مش متوفر. شيله قبل ما تكمل.');
            }

            $minimumQuantity = max(1, (int) ($product->minimum_order_qty ?? 1));
            $maximumQuantity = max(0, (int) ($variation->stock_quantity ?? 0));
            $configuredMaximum = (int) ($product->max_orders_per_person ?? 0);
            if ($configuredMaximum > 0) {
                $maximumQuantity = min($maximumQuantity, $configuredMaximum);
            }
            if ((bool) ($product->sold_individually ?? false)) {
                $maximumQuantity = min($maximumQuantity, 1);
            }

            if ($maximumQuantity < $minimumQuantity) {
                return $this->localized("\"{$product->name}\" no longer has enough stock to meet its minimum order quantity. Please review your cart.", "\"{$this->localizedProductName($product)}\" مخزونه مش مكفي للحد الأدنى للطلب. راجع السلة.");
            }
            if ($quantity < $minimumQuantity || $quantity > $maximumQuantity) {
                return $this->localized("The quantity for \"{$product->name}\" must be between {$minimumQuantity} and {$maximumQuantity}. Please review your cart.", "كمية \"{$this->localizedProductName($product)}\" لازم تكون من {$minimumQuantity} لحد {$maximumQuantity}. راجع السلة.");
            }
        }

        return null;
    }

    public function index()
    {
        $cart = $this->getCart();
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', $this->localized('Your cart is empty.', 'السلة فاضية.'));
        }

        if ($quantityIssue = $this->cartQuantityIssue($cart)) {
            return redirect()->route('cart')->with('error', $quantityIssue);
        }

        if (session('locale', 'en') === 'ar') {
            $productIds = collect($cart)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
            $products = DB::table('products_data')->whereIn('id', $productIds)->get()->keyBy('id');
            $cart = array_map(function (array $item) use ($products) {
                if ($product = $products->get((int) ($item['product_id'] ?? 0))) {
                    $item['name'] = $this->localizedProductName($product);
                }
                return $item;
            }, $cart);
        }

        if ($this->requiresLogin()) {
            session(['url.intended' => route('checkout')]);
            return redirect()->route('login');
        }

        $authConfig = AuthConfig::get();
        if (!Auth::check()) {
            // Guest checkout remains available, but any optional sign-in started here
            // should return the customer to this checkout after authentication.
            session(['url.intended' => route('checkout')]);
        }
        $coupon   = session('ramo_coupon');
        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);

        if (is_array($coupon) && ! empty($coupon['code'])) {
            $couponValidation = app(\App\Http\Controllers\CouponController::class)->validateCouponRules(
                (string) $coupon['code'],
                (float) $subtotal,
                Auth::id()
            );

            if (! $couponValidation['valid']) {
                session()->forget('ramo_coupon');
                return redirect()->route('cart')->with('error', $this->couponValidationError((string) ($couponValidation['message'] ?? '')));
            }

            $couponRecord = $couponValidation['data']['coupon'];
            $coupon = [
                'code' => $couponRecord->code,
                'discount_type' => $couponRecord->discount_type ?? 'percent',
                'amount' => $couponRecord->amount ?? 0,
                'free_shipping' => (bool) ($couponRecord->free_shipping ?? false),
                'description' => $couponRecord->description ?? '',
            ];
            session(['ramo_coupon' => $coupon]);
        }

        $discount = $this->calcDiscount($subtotal, $coupon);
        $afterDiscount = max(0, $subtotal - $discount);
        $freeShipping = is_array($coupon) && ! empty($coupon['free_shipping']);
        $shippingFee = $freeShipping ? 0.0 : ShippingConfig::feeForSubtotal($afterDiscount);
        $cartTax = TaxConfig::cartTax($afterDiscount);
        $shippingTax = TaxConfig::shippingTax($shippingFee);
        $totalTax = round($cartTax + $shippingTax, 2);
        $baseTotal = round($afterDiscount + $shippingFee + $totalTax, 2);
        $paymentMethods = PaymentConfig::checkoutMethods();
        $selectedPaymentMethod = old('payment_method', array_key_first($paymentMethods) ?: 'cod');
        // A free-shipping coupon waives delivery-related charges, including COD.
        $codFeeAmount = $freeShipping ? 0.0 : PaymentConfig::codFee();
        $codFee = $selectedPaymentMethod === 'cod' ? $codFeeAmount : 0.0;
        $total = round($baseTotal + $codFee, 2);

        $user = Auth::user();
        $checkoutIdempotencyKey = session('checkout_idempotency_key');
        if (! is_string($checkoutIdempotencyKey) || ! Str::isUuid($checkoutIdempotencyKey)) {
            $checkoutIdempotencyKey = (string) Str::uuid();
            session(['checkout_idempotency_key' => $checkoutIdempotencyKey]);
        }

        $savedAddress = [];
        if ($user && ! empty($user->shipping)) {
            $savedAddress = json_decode($user->shipping, true)
                ?? json_decode(stripslashes($user->shipping), true)
                ?? [];
            $savedAddress = is_array($savedAddress) ? $savedAddress : [];
        }

        return view('web.checkout', compact(
            'cart', 'subtotal', 'discount', 'shippingFee', 'cartTax', 'shippingTax', 'totalTax', 'baseTotal', 'codFeeAmount', 'codFee', 'total', 'coupon',
            'user', 'savedAddress', 'authConfig', 'paymentMethods', 'selectedPaymentMethod', 'checkoutIdempotencyKey'
        ));
    }

    public function place(Request $r)
    {
        $idempotencyKey = (string) $r->input('idempotency_key');
        // Guest checkout is normally disabled, but use a stable non-null scope if it is enabled.
        $idempotencyUserId = (int) (Auth::id() ?? 0);

        // Check completed requests before inspecting the cart: a successful first
        // request clears the cart, while a duplicate must still reach its receipt.
        if (Str::isUuid($idempotencyKey)) {
            $completedOrderId = DB::table('idempotency_keys')
                ->where('key', $idempotencyKey)
                ->where('user_id', $idempotencyUserId)
                ->value('order_id');

            if ($completedOrderId) {
                session()->forget('checkout_idempotency_key');
                return redirect()->route('order.success', $completedOrderId);
            }
        }

        $cart = $this->getCart();
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', $this->localized('Your cart is empty.', 'السلة فاضية.'));
        }

        if ($this->requiresLogin()) {
            session(['url.intended' => route('checkout')]);
            return redirect()->route('login');
        }

        $r->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'state'          => 'required|string|max:100',
            'address_note'   => 'nullable|string|max:500',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'save_address'   => 'nullable|boolean',
            'payment_method' => 'required|string',
            'notes'           => 'nullable|string|max:500',
            'idempotency_key' => 'required|uuid',
        ]);

        // Only accept methods that are currently displayed and enabled by admin.
        $paymentMethods = PaymentConfig::checkoutMethods();
        if (! array_key_exists($r->payment_method, $paymentMethods)) {
            return back()->withInput()->withErrors([
                'payment_method' => $this->localized('That payment method is not currently available.', 'طريقة الدفع دي مش متاحة دلوقتي.'),
            ]);
        }

        $paymentTitle = $paymentMethods[$r->payment_method]['title'];
        $isManualPayment = PaymentConfig::isManualMethod($r->payment_method);
        $codFeeAmount = PaymentConfig::codFee();
        session(['checkout_save_address' => $r->boolean('save_address')]);

        $billing = [
            'first_name' => $r->first_name,
            'last_name'  => $r->last_name,
            'email'      => $r->email,
            'phone'      => $r->phone,
            'address_1'  => $r->address,
            'address_2'  => $r->address_note,
            'city'       => $r->city,
            'state'      => $r->state,
            'country'    => 'EG',
            'latitude'   => $r->latitude,
            'longitude'  => $r->longitude,
        ];

        $coupon = session('ramo_coupon');
        $customerId = Auth::id();

        try {
            $checkoutResult = DB::transaction(function () use ($cart, $r, $billing, $paymentTitle, $isManualPayment, $idempotencyKey, $idempotencyUserId, $coupon, $customerId) {
                // Claim the key inside the same transaction as stock changes and order creation.
                // A double-click or retry can therefore never create a second order.
                $claimed = DB::table('idempotency_keys')->insertOrIgnore([
                    'key' => $idempotencyKey,
                    'user_id' => $idempotencyUserId,
                    'order_id' => null,
                    'created_at' => now(),
                ]);

                if (! $claimed) {
                    $existingKey = DB::table('idempotency_keys')
                        ->where('key', $idempotencyKey)
                        ->where('user_id', $idempotencyUserId)
                        ->lockForUpdate()
                        ->first();

                    if ($existingKey?->order_id) {
                        return ['order_id' => (int) $existingKey->order_id, 'replayed' => true];
                    }

                    throw new \RuntimeException($this->localized(
                        'Your order is already being processed. Please wait a moment before trying again.',
                        'طلبك بيتعالج دلوقتي. استنى لحظة قبل ما تحاول تاني.'
                    ));
                }

                $cartProductIds = collect($cart)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->values()->all();

                $dbProducts = DB::table('products_data')
                    ->whereIn('id', $cartProductIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Lock all variations for the cart's products before validating stock.
                // This serializes competing checkouts for the same variation.
                $dbVariations = DB::table('product_variations')
                    ->whereIn('product_id', $cartProductIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $verifiedCart = [];
                foreach ($cart as $rowId => $item) {
                    $productId = (int) ($item['product_id'] ?? 0);
                    $quantity = (int) ($item['qty'] ?? 0);
                    $product = $dbProducts->get($productId);

                    if (! $product || (($product->status ?? 'publish') !== 'publish')) {
                        throw new \RuntimeException($this->localized("Product \"" . ($item['name'] ?? $productId) . "\" is no longer available.", "المنتج \"" . ($item['name'] ?? $productId) . "\" مبقاش متاح."));
                    }
                    if ($quantity < 1) {
                        throw new \RuntimeException($this->localized("The quantity for \"{$product->name}\" is invalid.", "كمية \"{$this->localizedProductName($product)}\" مش صحيحة."));
                    }

                    $variation = ! empty($item['variation_id'])
                        ? $dbVariations->get((int) $item['variation_id'])
                        : $dbVariations->first(fn ($v) => (int) $v->product_id === $productId && (bool) $v->main_variation);

                    // A variation must belong to the product in the cart. This prevents
                    // a crafted request from pricing one product with another product's variation.
                    if (! $variation || (int) $variation->product_id !== $productId) {
                        throw new \RuntimeException($this->localized("The selected variation for \"{$product->name}\" is invalid.", "الاختيار اللي اخترته لمنتج \"{$this->localizedProductName($product)}\" مش صحيح."));
                    }
                    if (($variation->status ?? 'publish') !== 'publish' || ($variation->stock_status ?? 'instock') !== 'instock') {
                        throw new \RuntimeException($this->localized("The selected variation of \"{$product->name}\" is unavailable.", "الاختيار اللي اخترته لمنتج \"{$this->localizedProductName($product)}\" مش متاح."));
                    }
                    $stockQuantity = (int) ($variation->stock_quantity ?? 0);
                    $minimumQuantity = max(1, (int) ($product->minimum_order_qty ?? 1));
                    $maximumQuantity = $stockQuantity;
                    $configuredMaximum = (int) ($product->max_orders_per_person ?? 0);
                    if ($configuredMaximum > 0) {
                        $maximumQuantity = min($maximumQuantity, $configuredMaximum);
                    }
                    if ((bool) ($product->sold_individually ?? false)) {
                        $maximumQuantity = min($maximumQuantity, 1);
                    }
                    if ($maximumQuantity < $minimumQuantity) {
                        throw new \RuntimeException($this->localized("\"{$product->name}\" does not have enough stock to meet its minimum order quantity of {$minimumQuantity}.", "\"{$this->localizedProductName($product)}\" مخزونه مش مكفي للحد الأدنى اللي هو {$minimumQuantity}."));
                    }
                    if ($quantity < $minimumQuantity) {
                        throw new \RuntimeException($this->localized("Minimum order quantity for \"{$product->name}\" is {$minimumQuantity}.", "أقل كمية للطلب من \"{$this->localizedProductName($product)}\" هي {$minimumQuantity}."));
                    }
                    if ($quantity > $maximumQuantity) {
                        throw new \RuntimeException($this->localized("You can only order up to {$maximumQuantity} unit(s) of \"{$product->name}\" per order.", "تقدر تطلب لحد {$maximumQuantity} قطعة من \"{$this->localizedProductName($product)}\" في الطلب الواحد."));
                    }

                    $livePrice = PricingService::effectiveVariationPrice($variation, $product);

                    $variationAttributes = json_decode($variation->attributes ?? '[]', true)
                        ?? json_decode(stripslashes($variation->attributes ?? '[]'), true)
                        ?? [];

                    $verifiedCart[$rowId] = [
                        'product_id'   => $productId,
                        'variation_id' => (int) $variation->id,
                        'name'         => $this->localizedProductName($product),
                        'sku'          => $product->sku ?? null,
                        'price'        => $livePrice,
                        'qty'          => $quantity,
                        'attrs'        => is_array($variationAttributes) ? $variationAttributes : [],
                    ];
                }

                $couponRecord = null;
                $appliedCoupon = null;
                if (is_array($coupon) && ! empty($coupon['code'])) {
                    $couponRecord = DB::table('coupons')
                        ->where('code', strtoupper(trim((string) $coupon['code'])))
                        ->lockForUpdate()
                        ->first();

                    if (! $couponRecord || ($couponRecord->status ?? 'publish') !== 'publish') {
                        throw new \RuntimeException($this->localized('The coupon is no longer available.', 'الكوبون ده مبقاش متاح.'));
                    }
                    if ($couponRecord->date_expires && now()->isAfter($couponRecord->date_expires)) {
                        throw new \RuntimeException($this->localized('This coupon has expired.', 'الكوبون ده انتهت صلاحيته.'));
                    }
                    if ((int) ($couponRecord->usage_limit ?? 0) > 0 && (int) ($couponRecord->usage_count ?? 0) >= (int) $couponRecord->usage_limit) {
                        throw new \RuntimeException($this->localized('This coupon has reached its usage limit.', 'الكوبون ده وصل للحد الأقصى للاستخدام.'));
                    }
                    if ($customerId && (int) ($couponRecord->usage_limit_per_user ?? 0) > 0) {
                        $userUses = (int) (DB::table('coupon_user_limits')
                            ->where('coupon_id', $couponRecord->id)
                            ->where('user_id', $customerId)
                            ->lockForUpdate()
                            ->value('use_count') ?? 0);
                        if ($userUses >= (int) $couponRecord->usage_limit_per_user) {
                            throw new \RuntimeException($this->localized('You have reached this coupon\'s usage limit.', 'إنت وصلت لحد استخدام الكوبون ده.'));
                        }
                    }

                    $appliedCoupon = [
                        'code' => $couponRecord->code,
                        'discount_type' => $couponRecord->discount_type ?? 'percent',
                        'amount' => (float) ($couponRecord->amount ?? 0),
                        'free_shipping' => (bool) ($couponRecord->free_shipping ?? false),
                    ];
                }

                $subtotal = collect($verifiedCart)->sum(fn ($item) => $item['price'] * $item['qty']);
                if ($couponRecord && (float) ($couponRecord->minimum_amount ?? 0) > 0 && $subtotal < (float) $couponRecord->minimum_amount) {
                    throw new \RuntimeException($this->localized('This coupon requires a higher order subtotal.', 'الكوبون ده محتاج قيمة طلب أعلى.'));
                }
                if ($couponRecord && (float) ($couponRecord->maximum_amount ?? 0) > 0 && $subtotal > (float) $couponRecord->maximum_amount) {
                    throw new \RuntimeException($this->localized('This coupon is not available for this order subtotal.', 'الكوبون ده مش متاح لقيمة الطلب دي.'));
                }
                $discount = $this->calcDiscount($subtotal, $appliedCoupon);
                $afterDiscount = max(0, $subtotal - $discount);
                $shippingFee = (bool) ($couponRecord->free_shipping ?? false)
                    ? 0.0
                    : ShippingConfig::feeForSubtotal($afterDiscount);
                $cartTax = TaxConfig::cartTax($afterDiscount);
                $shippingTax = TaxConfig::shippingTax($shippingFee);
                $totalTax = round($cartTax + $shippingTax, 2);
                $codFee = $r->payment_method === 'cod' && ! (bool) ($couponRecord?->free_shipping ?? false)
                    ? PaymentConfig::codFee()
                    : 0.0;
                $total = round($afterDiscount + $shippingFee + $totalTax + $codFee, 2);

                // Stock is decremented only after each locked variation has passed validation.
                foreach ($verifiedCart as $item) {
                    DB::table('product_variations')
                        ->where('id', $item['variation_id'])
                        ->decrement('stock_quantity', $item['qty']);
                }

                $lineItems = collect($verifiedCart)->map(fn ($item) => [
                    'product_id'   => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'name'         => $item['name'],
                    'sku'          => $item['sku'],
                    'quantity'     => $item['qty'],
                    'price'        => $item['price'],
                    'subtotal'     => round($item['price'] * $item['qty'], 2),
                    'attributes'   => $item['attrs'],
                ])->values()->all();

                $now = now();
                $nowText = $now->toDateTimeString();
                $orderId = DB::table('orders')->insertGetId([
                    'customer_id'          => Auth::id(),
                    'status'               => 'pending',
                    'payment_status'       => $isManualPayment ? 'pending_verification' : 'confirmed',
                    'currency'             => 'EGP',
                    'currency_symbol'      => 'ج.م',
                    'payment_method'       => $r->payment_method,
                    'payment_method_title' => $paymentTitle,
                    'billing'              => json_encode($billing),
                    'shipping'             => json_encode($billing),
                    'line_items'           => json_encode($lineItems),
                    'original_total'       => (int) round($subtotal),
                    'final_total'          => $total,
                    'discount_total'       => $discount,
                    'coupon_code'          => $appliedCoupon['code'] ?? null,
                    'coupon_applied'       => $appliedCoupon ? 1 : 0,
                    'fee_lines'            => json_encode($codFee > 0 ? [[
                        'code' => 'cod_fee',
                        'name' => 'Cash on Delivery fee',
                        'total' => number_format($codFee, 2, '.', ''),
                    ]] : []),
                    'customer_note'        => $r->notes ?? '',
                    'order_key'            => 'wc_' . Str::random(20),
                    'date_created'         => $now,
                    'date_modified'        => $now,
                    'date_created_gmt'     => $nowText,
                    'date_modified_gmt'    => $nowText,
                    'date_paid_gmt'        => '',
                    'date_completed_gmt'   => '',
                    'created_at'           => $now,
                    'updated_at'           => $now,
                    'payment_url'          => '',
                    'is_editable'          => true,
                    'needs_payment'        => $isManualPayment || $r->payment_method !== 'cod',
                    'needs_processing'     => true,
                    'set_paid'             => ! $isManualPayment && $r->payment_method === 'cod',
                    'number'               => 0,
                    'timeline'             => '[]',
                    'created_via'          => 'website',
                    'customer_ip_address'  => $r->ip(),
                    'customer_user_agent'  => $r->userAgent(),
                    'cart_hash'            => md5(json_encode($lineItems)),
                    'parent_id'            => 0,
                    'shipping_total'       => $shippingFee,
                    'shipping_tax'         => $shippingTax,
                    'cart_tax'             => $cartTax,
                    'total_tax'            => $totalTax,
                ]);

                if ($couponRecord) {
                    $this->recordCouponUsage($couponRecord, $customerId);
                }

                DB::table('orders')->where('id', $orderId)->update(['number' => $orderId]);

                $vendorMap = DB::table('products_data')
                    ->whereIn('id', collect($verifiedCart)->pluck('product_id')->unique()->all())
                    ->pluck('vendor_id', 'id')
                    ->toArray();

                $vendorGroups = [];
                foreach ($verifiedCart as $item) {
                    $vendorGroups[$vendorMap[$item['product_id']] ?? 'none'][] = $item;
                }

                foreach ($vendorGroups as $vendorId => $items) {
                    $subLineItems = [];
                    $subSubtotal = 0;
                    foreach ($items as $item) {
                        $itemSubtotal = round($item['price'] * $item['qty'], 2);
                        $subSubtotal += $itemSubtotal;
                        $subLineItems[] = [
                            'product_id'   => $item['product_id'],
                            'variation_id' => $item['variation_id'],
                            'name'         => $item['name'],
                            'sku'          => $item['sku'],
                            'quantity'     => $item['qty'],
                            'price'        => $item['price'],
                            'subtotal'     => $itemSubtotal,
                            'attributes'   => $item['attrs'],
                        ];
                    }

                    $subDiscount = $subtotal > 0 ? round(($subSubtotal / $subtotal) * $discount, 2) : 0;
                    DB::table('order_sub_orders')->insert([
                        'parent_order_id' => $orderId,
                        'vendor_id'       => $vendorId === 'none' ? null : (int) $vendorId,
                        'customer_id'     => Auth::id(),
                        'status'          => 'pending',
                        'vendor_status'   => 'pending',
                        'line_items'      => json_encode($subLineItems),
                        'subtotal'        => $subSubtotal,
                        'discount_total'  => $subDiscount,
                        'total'           => max(0, $subSubtotal - $subDiscount),
                        'tracking_number' => null,
                        'tracking_carrier'=> null,
                        'timeline'        => '[]',
                        'notes'           => null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]);
                }

                DB::table('idempotency_keys')
                    ->where('key', $idempotencyKey)
                    ->where('user_id', $idempotencyUserId)
                    ->update(['order_id' => $orderId]);

                return ['order_id' => $orderId, 'replayed' => false];
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart')->with('error', $e->getMessage());
        }

        if ($checkoutResult['replayed']) {
            session()->forget('checkout_idempotency_key');
            return redirect()->route('order.success', $checkoutResult['order_id']);
        }

        $orderId = $checkoutResult['order_id'];

        if ($user = Auth::user()) {
            $shipping = [
                'first_name'   => $r->first_name,
                'last_name'    => $r->last_name,
                'address'      => $r->address,
                'address_note' => $r->address_note,
                'city'         => $r->city,
                'state'        => $r->state,
                'email'        => $r->email,
                'phone'        => $r->phone,
                'latitude'     => $r->latitude,
                'longitude'    => $r->longitude,
            ];
            $userUpdates = [
                'first_name' => $r->first_name,
                'last_name'  => $r->last_name,
                'phone'      => $r->phone,
            ];
            if ($r->boolean('save_address') && Schema::hasColumn('users', 'shipping')) {
                $userUpdates['shipping'] = json_encode($shipping);
            }
            if ($r->boolean('save_address')) {
                foreach (['address', 'city', 'state', 'address_note', 'latitude', 'longitude'] as $field) {
                    if (Schema::hasColumn('users', $field)) {
                        $userUpdates[$field] = $r->input($field);
                    }
                }
            }
            if ($user instanceof User) {
                $user->fill($userUpdates)->save();
            }
        }

        $this->saveCart([]);
        session()->forget(['ramo_coupon', 'checkout_idempotency_key']);

        return redirect()->route('order.success', $orderId);
    }

    public function success($orderId)
    {
        // Checkout requires an account. Scope the receipt by that account so an
        // incrementing order ID cannot disclose another customer's address or items.
        if (! Auth::check()) {
            abort(404);
        }

        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('customer_id', Auth::id())
            ->first();
        if (! $order) {
            abort(404);
        }
        $manualPaymentMethods = PaymentConfig::enabledMethods();
        $lineItems = json_decode($order->line_items ?? '[]', true) ?: [];

        $subOrders = DB::table('order_sub_orders as s')
            ->where('s.parent_order_id', $orderId)
            ->leftJoin('vendor_users as v', 'v.id', '=', 's.vendor_id')
            ->select(['s.*', 'v.shop_name as vendor_shop_name'])
            ->orderBy('s.id')
            ->get()
            ->map(function ($sub) {
                $sub->items = json_decode($sub->line_items ?? '[]', true) ?: [];
                return $sub;
            });

        $productIds = collect($lineItems)->pluck('product_id')
            ->merge($subOrders->flatMap(fn ($sub) => collect($sub->items)->pluck('product_id')))
            ->filter()
            ->unique()
            ->values();

        $thumbnails = $productIds->isEmpty()
            ? collect()
            : DB::table('products_data')
                ->whereIn('id', $productIds)
                ->pluck('images', 'id')
                ->map(fn ($images) => \App\Constants\AppConstants::productThumbnailUrl($images));

        $attachImages = function (array $items) use ($thumbnails) {
            return array_map(function ($item) use ($thumbnails) {
                $item['image'] = $thumbnails[$item['product_id'] ?? null] ?? null;
                return $item;
            }, $items);
        };

        $lineItems = $attachImages($lineItems);
        $subOrders = $subOrders->map(function ($sub) use ($attachImages) {
            $sub->items = $attachImages($sub->items);
            return $sub;
        });

        $paymentReceiptCount = DB::table('payment_receipts')->where('order_id', $order->id)->count();

        return view('web.order-success', compact('order', 'lineItems', 'subOrders', 'manualPaymentMethods', 'paymentReceiptCount'));
    }

    private function recordCouponUsage(object $coupon, ?int $userId): void
    {
        DB::table('coupons')
            ->where('id', $coupon->id)
            ->increment('usage_count', 1, ['date_modified' => now()]);

        if (! $userId || (int) ($coupon->usage_limit_per_user ?? 0) <= 0) {
            return;
        }

        $usageRow = DB::table('coupon_user_limits')
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        if ($usageRow) {
            DB::table('coupon_user_limits')
                ->where('id', $usageRow->id)
                ->update([
                    'use_count' => ((int) $usageRow->use_count) + 1,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('coupon_user_limits')->insert([
                'coupon_id' => $coupon->id,
                'user_id' => $userId,
                'use_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function calcDiscount(float $subtotal, ?array $coupon): float
    {
        if (! $coupon) {
            return 0;
        }
        if ($coupon['discount_type'] === 'percent') {
            return round($subtotal * ($coupon['amount'] / 100), 2);
        }
        return min((float) $coupon['amount'], $subtotal);
    }
}

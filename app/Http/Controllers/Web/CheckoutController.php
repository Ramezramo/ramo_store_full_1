<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\AuthConfig;
use App\Http\Traits\CartTrait;
use App\Models\User;
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
        return !Auth::check() && !AuthConfig::val('guest_checkout', false);
    }

    public function index()
    {
        $cart = $this->getCart();
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        if ($this->requiresLogin()) {
            session(['url.intended' => route('checkout')]);
            return redirect()->route('login');
        }

        $authConfig = AuthConfig::get();
        $coupon     = session('ramo_coupon');
        $subtotal   = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $discount   = $this->calcDiscount($subtotal, $coupon);
        $total      = max(0, $subtotal - $discount);

        $user = Auth::user();
        return view('web.checkout', compact('cart', 'subtotal', 'discount', 'total', 'coupon', 'user', 'authConfig'));
    }

    public function place(Request $r)
    {
        $cart = $this->getCart();
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        if ($this->requiresLogin()) {
            session(['url.intended' => route('checkout')]);
            return redirect()->route('login');
        }

        $r->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'state'          => 'required|string|max:100',
            'address_note'   => 'nullable|string|max:500',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'save_address'   => 'nullable|boolean',
            'payment_method' => 'required|in:cod,bank_transfer,vodafone_cash,fawry,wallet,credit_card',
            'notes'          => 'nullable|string|max:500',
        ]);

        session(['checkout_save_address' => $r->boolean('save_address')]);

        $paymentTitles = [
            'cod'            => 'Cash on Delivery',
            'bank_transfer'  => 'Bank Transfer',
            'vodafone_cash'  => 'Vodafone Cash',
            'fawry'          => 'Fawry',
            'wallet'         => 'Wallet',
            'credit_card'    => 'Credit Card',
        ];

        // ── RE-VERIFY PRICES FROM DATABASE (never trust cart-stored prices) ──────
        $cartProductIds   = collect($cart)->pluck('product_id')->unique()->values()->all();
        $cartVariationIds = collect($cart)->pluck('variation_id')->filter()->unique()->values()->all();

        $dbProducts   = DB::table('products_data')
            ->whereIn('id', $cartProductIds)
            ->get()->keyBy('id');

        $dbVariations = DB::table('product_variations')
            ->whereIn('product_id', $cartProductIds)
            ->when(!empty($cartVariationIds), fn($q) => $q->orWhereIn('id', $cartVariationIds))
            ->get()->keyBy('id');

        $verifiedCart = [];
        foreach ($cart as $rowId => $item) {
            $product = $dbProducts->get($item['product_id']);
            if (!$product) {
                return redirect()->route('cart')
                    ->with('error', "Product \"{$item['name']}\" is no longer available.");
            }

            $variation = $item['variation_id']
                ? $dbVariations->get($item['variation_id'])
                : $dbVariations->first(fn($v) => $v->product_id == $item['product_id'] && $v->main_variation);

            if (!$variation) {
                return redirect()->route('cart')
                    ->with('error', "A variation for \"{$item['name']}\" could not be found.");
            }

            $regularPrice = (float) ($variation->regular_price ?? 0);
            $livePrice    = (float) ($variation->price ?? $regularPrice);
            $discPct      = (float) ($product->discount_percentage ?? 0);
            if ($discPct > 0 && $regularPrice > 0 && $livePrice >= $regularPrice) {
                $livePrice = round($regularPrice * (1 - $discPct / 100), 2);
            }

            $verifiedCart[$rowId] = array_merge($item, ['price' => $livePrice]);
        }
        $cart = $verifiedCart;
        // ─────────────────────────────────────────────────────────────────────────

        $coupon   = session('ramo_coupon');
        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['qty']);
        $discount = $this->calcDiscount($subtotal, $coupon);
        $total    = max(0, $subtotal - $discount);

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

        if ($user = Auth::user()) {
            $shipping = [
                'first_name' => $r->first_name,
                'last_name'  => $r->last_name,
                'address'    => $r->address,
                'address_note' => $r->address_note,
                'city'       => $r->city,
                'state'      => $r->state,
                'email'      => $r->email,
                'phone'      => $r->phone,
                'latitude'   => $r->latitude,
                'longitude'  => $r->longitude,
            ];
            $userUpdates = [
                'first_name' => $r->first_name,
                'last_name'  => $r->last_name,
                'phone'      => $r->phone,
            ];
            if (Schema::hasColumn('users', 'shipping')) {
                $userUpdates['shipping'] = json_encode($shipping);
            }
            foreach (['address', 'city', 'state', 'address_note', 'latitude', 'longitude'] as $field) {
                if (Schema::hasColumn('users', $field)) {
                    $userUpdates[$field] = $r->input($field);
                }
            }
            if ($user instanceof User) {
                $user->fill($userUpdates)->save();
            }
        }

        $lineItems = [];
        foreach ($cart as $item) {
            $lineItems[] = [
                'product_id'   => $item['product_id'],
                'variation_id' => $item['variation_id'],
                'name'         => $item['name'],
                'sku'          => $item['sku'] ?? null,
                'quantity'     => $item['qty'],
                'price'        => $item['price'],
                'subtotal'     => round($item['price'] * $item['qty'], 2),
                'attributes'   => $item['attrs'] ?? [],
            ];
        }

        $now     = now();
        $nowText = $now->toDateTimeString();

        $orderId = DB::table('orders')->insertGetId([
            'customer_id'          => Auth::id(),
            'status'               => 'pending',
            'currency'             => 'EGP',
            'currency_symbol'      => 'ج.م',
            'payment_method'       => $r->payment_method,
            'payment_method_title' => $paymentTitles[$r->payment_method],
            'billing'              => json_encode($billing),
            'shipping'             => json_encode(array_merge($billing, [
                'latitude' => $r->latitude,
                'longitude' => $r->longitude,
            ])),
            'line_items'           => json_encode($lineItems),
            'original_total'       => (int) round($subtotal),
            'final_total'          => $total,
            'discount_total'       => $discount,
            'coupon_code'          => $coupon['code'] ?? null,
            'coupon_applied'       => $coupon ? 1 : 0,
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
            'needs_payment'        => $r->payment_method !== 'cod',
            'needs_processing'     => true,
            'set_paid'             => false,
            'number'               => 0,
            'timeline'             => '[]',
            'created_via'          => 'website',
            'customer_ip_address'  => $r->ip(),
            'customer_user_agent'  => $r->userAgent(),
            'cart_hash'            => md5(json_encode($lineItems)),
            'parent_id'            => 0,
            'shipping_total'       => 0,
            'shipping_tax'         => 0,
            'cart_tax'             => 0,
            'total_tax'            => 0,
        ]);

        DB::table('orders')->where('id', $orderId)->update(['number' => $orderId]);

        // ── SPLIT INTO VENDOR SUB-ORDERS ───────────────────────────────────────
        $productIds = collect($cart)->pluck('product_id')->unique()->values()->all();
        $vendorMap  = DB::table('products_data')
            ->whereIn('id', $productIds)
            ->pluck('vendor_id', 'id')
            ->toArray();

        // Group cart items by vendor
        $vendorGroups = [];
        foreach ($cart as $item) {
            $vendorId = $vendorMap[$item['product_id']] ?? null;
            $vendorGroups[$vendorId ?? 'none'][] = $item;
        }

        foreach ($vendorGroups as $vendorId => $items) {
            $subLineItems  = [];
            $subSubtotal   = 0;

            foreach ($items as $item) {
                $itemSubtotal   = round($item['price'] * $item['qty'], 2);
                $subSubtotal   += $itemSubtotal;
                $subLineItems[] = [
                    'product_id'   => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'name'         => $item['name'],
                    'sku'          => $item['sku'] ?? null,
                    'quantity'     => $item['qty'],
                    'price'        => $item['price'],
                    'subtotal'     => $itemSubtotal,
                    'attributes'   => $item['attrs'] ?? [],
                ];
            }

            // Proportional discount
            $subDiscount = ($subtotal > 0)
                ? round(($subSubtotal / $subtotal) * $discount, 2)
                : 0;
            $subTotal = max(0, $subSubtotal - $subDiscount);

            DB::table('order_sub_orders')->insert([
                'parent_order_id' => $orderId,
                'vendor_id'       => ($vendorId === 'none') ? null : (int) $vendorId,
                'customer_id'     => Auth::id(),
                'status'          => 'pending',
                'line_items'      => json_encode($subLineItems),
                'subtotal'        => $subSubtotal,
                'discount_total'  => $subDiscount,
                'total'           => $subTotal,
                'tracking_number' => null,
                'tracking_carrier'=> null,
                'timeline'        => '[]',
                'notes'           => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
        // ───────────────────────────────────────────────────────────────────────

        session()->forget(['ramo_cart', 'ramo_coupon']);

        return redirect()->route('order.success', $orderId);
    }

    public function success($orderId)
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (! $order) abort(404);

        $lineItems = json_decode($order->line_items ?? '[]', true);

        // Load sub-orders for vendor grouping display
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

        return view('web.order-success', compact('order', 'lineItems', 'subOrders'));
    }

    private function calcDiscount(float $subtotal, ?array $coupon): float
    {
        if (! $coupon) return 0;
        if ($coupon['discount_type'] === 'percent') {
            return round($subtotal * ($coupon['amount'] / 100), 2);
        }
        return min((float) $coupon['amount'], $subtotal);
    }
}

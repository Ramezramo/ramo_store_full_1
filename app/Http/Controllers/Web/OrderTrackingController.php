<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderTrackingController extends Controller
{
    public function index()
    {
        return view('web.order-tracking', ['order' => null, 'error' => null]);
    }

    public function track(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|min:1',
            'phone'    => 'required|string|min:6|max:30',
        ]);

        $orderId    = (int) $request->input('order_id');
        $phoneInput = preg_replace('/\s+/', '', $request->input('phone'));

        $order = DB::table('orders')->where('id', $orderId)->first();

        if (!$order) {
            return view('web.order-tracking', [
                'order' => null,
                'error' => 'Order #' . $orderId . ' was not found. Please check your order number.',
            ]);
        }

        // Decode billing JSON
        $billing = [];
        if ($order->billing) {
            $billing = json_decode($order->billing, true)
                ?? json_decode(stripslashes($order->billing), true)
                ?? [];
        }

        // Compare phone — strip spaces/dashes from both
        $storedPhone = preg_replace('/[\s\-]/', '', $billing['phone'] ?? '');
        $match       = $storedPhone && (
            $storedPhone === $phoneInput
            || $storedPhone === ltrim($phoneInput, '0+')
            || ltrim($storedPhone, '0+') === ltrim($phoneInput, '0+')
        );

        if (!$match) {
            return view('web.order-tracking', [
                'order' => null,
                'error' => 'The phone number does not match our records for order #' . $orderId . '. Please try again.',
            ]);
        }

        // Decode line items
        $lineItems = [];
        if ($order->line_items) {
            $decoded = json_decode($order->line_items, true)
                ?? json_decode(stripslashes($order->line_items), true)
                ?? [];
            $lineItems = is_array($decoded) ? $decoded : [];
        }

        // Enrich line items with product thumbnails
        $productIds = array_column($lineItems, 'product_id');
        $products   = $productIds
            ? DB::table('products_data')->whereIn('id', $productIds)->get()->keyBy('id')
            : collect();

        foreach ($lineItems as &$item) {
            $prod = $products->get($item['product_id'] ?? null);
            if ($prod && $prod->images) {
                $item['thumbnail'] = \App\Constants\AppConstants::productThumbnailUrl($prod->images);
            } else {
                $item['thumbnail'] = null;
            }
        }
        unset($item);

        // Each vendor shipment has its own status. Keep this separate from
        // the general order status shown in the header and progress tracker.
        $subOrders = DB::table('order_sub_orders as s')
            ->where('s.parent_order_id', $orderId)
            ->leftJoin('vendor_users as v', 'v.id', '=', 's.vendor_id')
            ->select(['s.*', 'v.shop_name as vendor_shop_name'])
            ->orderBy('s.id')
            ->get()
            ->map(function ($sub) use ($products) {
                $sub->items = json_decode($sub->line_items ?? '[]', true);
                $sub->items = is_array($sub->items) ? $sub->items : [];

                foreach ($sub->items as &$item) {
                    $product = $products->get($item['product_id'] ?? null);
                    $item['thumbnail'] = ($product && $product->images)
                        ? \App\Constants\AppConstants::productThumbnailUrl($product->images)
                        : null;
                }
                unset($item);

                return $sub;
            });

        // Shipping address
        $shipping = [];
        if ($order->shipping) {
            $shipping = json_decode($order->shipping, true)
                ?? json_decode(stripslashes($order->shipping), true)
                ?? [];
        }
        if (empty(array_filter($shipping))) {
            $shipping = $billing;
        }

        return view('web.order-tracking', [
            'order'     => $order,
            'billing'   => $billing,
            'shipping'  => $shipping,
            'lineItems' => $lineItems,
            'subOrders' => $subOrders,
            'error'     => null,
        ]);
    }

    private static array $statusMap = [
        'pending'    => ['label' => 'Pending Payment',  'color' => '#f59e0b', 'bg' => '#fffbeb', 'icon' => '⏳'],
        'processing' => ['label' => 'Processing',       'color' => '#3b82f6', 'bg' => '#eff6ff', 'icon' => '🔄'],
        'on-hold'    => ['label' => 'On Hold',           'color' => '#f97316', 'bg' => '#fff7ed', 'icon' => '⏸'],
        'completed'  => ['label' => 'Delivered',         'color' => '#22c55e', 'bg' => '#f0fdf4', 'icon' => '✅'],
        'delivered'  => ['label' => 'Delivered',         'color' => '#22c55e', 'bg' => '#f0fdf4', 'icon' => '✅'],
        'cancelled'  => ['label' => 'Cancelled',         'color' => '#ef4444', 'bg' => '#fef2f2', 'icon' => '❌'],
        'refunded'   => ['label' => 'Refunded',          'color' => '#8b5cf6', 'bg' => '#f5f3ff', 'icon' => '↩️'],
        'failed'     => ['label' => 'Payment Failed',    'color' => '#ef4444', 'bg' => '#fef2f2', 'icon' => '✗'],
        'shipped'    => ['label' => 'Shipped',           'color' => '#06b6d4', 'bg' => '#ecfeff', 'icon' => '🚚'],
    ];

    public static function statusInfo(string $status): array
    {
        return self::$statusMap[strtolower($status)]
            ?? ['label' => ucfirst($status), 'color' => '#6b7280', 'bg' => '#f9fafb', 'icon' => '📦'];
    }
}

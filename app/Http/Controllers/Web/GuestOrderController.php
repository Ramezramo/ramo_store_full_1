<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestOrderController extends Controller
{
    public function index()
    {
        return view('web.guest-order-lookup', ['order' => null, 'error' => null]);
    }

    public function lookup(Request $request)
    {
        $isAr = $request->session()->get('locale') === 'ar';

        $request->validate([
            'order_id' => 'required|integer|min:1',
            'email'    => 'required|email|max:255',
        ], $isAr ? [
            'order_id.required' => 'اكتب رقم الطلب.',
            'order_id.integer'  => 'رقم الطلب لازم يكون أرقام بس.',
            'order_id.min'      => 'رقم الطلب مش صحيح.',
            'email.required'    => 'اكتب الإيميل.',
            'email.email'       => 'اكتب إيميل صحيح.',
            'email.max'         => 'الإيميل طويل أوي.',
        ] : []);

        $orderId    = (int) $request->input('order_id');
        $emailInput = strtolower(trim($request->input('email')));

        $order = DB::table('orders')->where('id', $orderId)->first();

        if (!$order) {
            return back()->withInput()->with(
                'error', $isAr
                    ? 'مش لاقيين طلب رقم ' . $orderId . '، تأكد من رقم الطلب.'
                    : 'Order #' . $orderId . ' was not found. Please check your order number.'
            );
        }

        $billing = [];
        if ($order->billing) {
            $billing = json_decode($order->billing, true)
                ?? json_decode(stripslashes($order->billing), true)
                ?? [];
        }

        $storedEmail = strtolower(trim($billing['email'] ?? ''));

        if (!$storedEmail || $storedEmail !== $emailInput) {
            return back()->withInput()->with(
                'error', $isAr
                    ? 'الإيميل مش مطابق لطلب رقم ' . $orderId . '، حاول تاني.'
                    : 'The email address does not match our records for order #' . $orderId . '. Please try again.'
            );
        }

        $lineItems = [];
        if ($order->line_items) {
            $decoded   = json_decode($order->line_items, true)
                ?? json_decode(stripslashes($order->line_items), true)
                ?? [];
            $lineItems = is_array($decoded) ? $decoded : [];
        }

        $productIds = array_column($lineItems, 'product_id');
        $products   = $productIds
            ? DB::table('products_data')->whereIn('id', $productIds)->get()->keyBy('id')
            : collect();

        foreach ($lineItems as &$item) {
            $prod = $products->get($item['product_id'] ?? null);
            if ($isAr && $prod && !empty($prod->translations)) {
                $translations = json_decode($prod->translations, true) ?: [];
                foreach ((array) $translations as $translation) {
                    if (($translation['locale'] ?? null) === 'ar' && !empty($translation['name'])) {
                        $item['name'] = $translation['name'];
                        break;
                    }
                }
            }
            if ($prod && $prod->images) {
                $item['thumbnail'] = \App\Constants\AppConstants::productThumbnailUrl($prod->images);
            } else {
                $item['thumbnail'] = null;
            }
        }
        unset($item);

        $shipping = [];
        if ($order->shipping) {
            $shipping = json_decode($order->shipping, true)
                ?? json_decode(stripslashes($order->shipping), true)
                ?? [];
        }
        if (empty(array_filter($shipping))) {
            $shipping = $billing;
        }

        return view('web.guest-order-lookup', [
            'order'     => $order,
            'billing'   => $billing,
            'shipping'  => $shipping,
            'lineItems' => $lineItems,
            'error'     => null,
        ]);
    }
}

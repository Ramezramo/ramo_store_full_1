<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function localized(string $english, string $arabic): string
    {
        return session('locale', 'en') === 'ar' ? $arabic : $english;
    }

    public function store(Request $request, int $orderId)
    {
        $request->validate([
            'message'      => 'required|string|max:2000',
            'sub_order_id' => 'nullable|integer',
        ]);

        $order = DB::table('orders')
            ->where('id', $orderId)
            ->where('customer_id', Auth::id())
            ->first();

        if (! $order) abort(404);

        $subOrderId = $request->input('sub_order_id');
        $vendorId   = null;

        // Derive vendorId from sub-order if provided
        if ($subOrderId) {
            $subOrder = DB::table('order_sub_orders')
                ->where('id', $subOrderId)
                ->where('parent_order_id', $orderId)
                ->first();
            if ($subOrder) {
                $vendorId = $subOrder->vendor_id;
            }
        }

        // Fallback: derive from first product in the order
        if (! $vendorId) {
            $items = json_decode($order->line_items ?? '[]', true) ?: [];
            $firstProductId = $items[0]['product_id'] ?? null;
            if ($firstProductId) {
                $vendorId = DB::table('products_data')->where('id', $firstProductId)->value('vendor_id');
            }
        }

        DB::table('order_messages')->insert([
            'order_id'           => $orderId,
            'sub_order_id'       => $subOrderId ?: null,
            'customer_id'        => Auth::id(),
            'vendor_id'          => $vendorId,
            'sender_type'        => 'customer',
            'message'            => $request->message,
            'is_vendor_response' => false,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return back()->with('success', $this->localized('Message sent to the vendor.', 'رسالتك اتبعتت للبائع.'));
    }
}

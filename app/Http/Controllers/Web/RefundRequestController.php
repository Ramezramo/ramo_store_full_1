<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $refunds = DB::table('refund_requests as r')
            ->where('r.customer_id', Auth::id())
            ->leftJoin('orders as o', 'o.id', '=', 'r.order_id')
            ->orderByDesc('r.id')
            ->paginate(10, ['r.*', 'o.final_total as order_total']);

        return view('web.account.refunds', compact('refunds'));
    }

    public function create(Request $request)
    {
        $orderId = $request->input('order_id');
        $order   = null;

        if ($orderId) {
            $order = DB::table('orders')
                ->where('id', $orderId)
                ->where('customer_id', Auth::id())
                ->first();
        }

        $orders = DB::table('orders')
            ->where('customer_id', Auth::id())
            ->whereIn('status', ['completed', 'delivered', 'shipped', 'processing'])
            ->orderByDesc('id')
            ->get(['id', 'date_created', 'final_total', 'status']);

        $existingIds = DB::table('refund_requests')
            ->where('customer_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('order_id')
            ->toArray();

        return view('web.account.refund-create', compact('order', 'orders', 'existingIds', 'orderId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id'    => 'required|integer',
            'type'        => 'required|in:refund,return',
            'reason'      => 'required|in:damaged,wrong_item,changed_mind,not_as_described,other',
            'description' => 'nullable|string|max:1000',
        ]);

        $order = DB::table('orders')
            ->where('id', $request->order_id)
            ->where('customer_id', Auth::id())
            ->first();

        if (! $order) {
            return back()->with('error', 'Order not found.')->withInput();
        }

        $already = DB::table('refund_requests')
            ->where('order_id', $request->order_id)
            ->where('customer_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($already) {
            return back()->with('error', 'You already have an open request for this order.')->withInput();
        }

        $vendorId = null;
        if ($order->vendor_id ?? null) {
            $vendorId = $order->vendor_id;
        } else {
            $firstItem = DB::table('order_items')->where('order_id', $order->id)->first();
            if ($firstItem) {
                $vendorId = DB::table('products_data')->where('id', $firstItem->product_id)->value('vendor_id');
            }
        }

        DB::table('refund_requests')->insert([
            'order_id'    => $request->order_id,
            'customer_id' => Auth::id(),
            'vendor_id'   => $vendorId,
            'type'        => $request->type,
            'reason'      => $request->reason,
            'description' => $request->description,
            'status'      => 'pending',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('account.refunds')->with('success', 'Your request has been submitted. We will review it shortly.');
    }

    public function show(int $id)
    {
        $refund = DB::table('refund_requests as r')
            ->where('r.id', $id)
            ->where('r.customer_id', Auth::id())
            ->leftJoin('orders as o', 'o.id', '=', 'r.order_id')
            ->first(['r.*', 'o.final_total as order_total', 'o.status as order_status', 'o.date_created as order_date']);

        if (! $refund) abort(404);

        return view('web.account.refund-show', compact('refund'));
    }

    public function cancel(int $id)
    {
        $refund = DB::table('refund_requests')
            ->where('id', $id)
            ->where('customer_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (! $refund) {
            return back()->with('error', 'Request cannot be cancelled.');
        }

        DB::table('refund_requests')->where('id', $id)->update([
            'status'     => 'cancelled',
            'updated_at' => now(),
        ]);

        return redirect()->route('account.refunds')->with('success', 'Request cancelled.');
    }
}

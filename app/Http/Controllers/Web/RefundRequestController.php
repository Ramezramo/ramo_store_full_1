<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function localized(string $english, string $arabic): string
    {
        return session('locale') === 'ar' ? $arabic : $english;
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

        return DB::transaction(function () use ($request) {
            $order = DB::table('orders')->where('id', $request->order_id)->where('customer_id', Auth::id())
                ->lockForUpdate()->first();
            if (! $order) {
                return back()->with('error', $this->localized('Order not found.', 'مش لاقيين الطلب ده.'))->withInput();
            }
            if (! in_array($order->status, ['completed', 'delivered', 'shipped', 'processing', 'partially_delivered'], true)) {
                return back()->with('error', $this->localized('This order cannot receive a new refund request.', 'الطلب ده مش متاح لطلب استرجاع جديد.'));
            }
            $already = DB::table('refund_requests')->where('order_id', $order->id)
                ->whereIn('status', ['pending', 'approved', 'completed'])->exists();
            if ($already) {
                return back()->with('error', $this->localized('You already have a refund request for this order.', 'عندك طلب استرجاع بالفعل للطلب ده.'))->withInput();
            }
            $vendors = DB::table('order_sub_orders')->where('parent_order_id', $order->id)->pluck('vendor_id')->filter()->unique();
            DB::table('refund_requests')->insert([
                'order_id' => $order->id, 'customer_id' => Auth::id(),
                'vendor_id' => $vendors->count() === 1 ? $vendors->first() : null,
                'type' => $request->type, 'reason' => $request->reason, 'description' => $request->description,
                'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
            return redirect()->route('account.refunds')->with('success', $this->localized('Your request has been submitted. We will review it shortly.', 'طلبك اتبعت وهنراجعه قريب.'));
        });
    }
    public function show(int $id)
    {
        $refund = RefundRequest::findOrFail($id);

        try {
            $this->authorize('view', $refund);
        } catch (AuthorizationException) {
            // Do not disclose whether a refund ID belongs to another customer.
            abort(404);
        }

        // Load presentation-only order fields only after ownership is authorized.
        $details = DB::table('refund_requests as r')
            ->where('r.id', $refund->id)
            ->leftJoin('orders as o', 'o.id', '=', 'r.order_id')
            ->first(['r.*', 'o.final_total as order_total', 'o.status as order_status', 'o.date_created as order_date']);

        $refund = $details ?: $refund;

        return view('web.account.refund-show', compact('refund'));
    }

    public function cancel(int $id)
    {
        $refund = RefundRequest::findOrFail($id);

        try {
            $this->authorize('view', $refund);
        } catch (AuthorizationException) {
            // Do not disclose whether a refund ID belongs to another customer.
            abort(404);
        }

        // Preserve the existing user-facing response for non-pending requests.
        if ($refund->status !== 'pending') {
            return back()->with('error', $this->localized('Request cannot be cancelled.', 'مش ممكن تلغي الطلب ده دلوقتي.'));
        }

        $this->authorize('cancel', $refund);

        DB::table('refund_requests')
            ->where('id', $refund->id)
            ->where('customer_id', Auth::id())
            ->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);

        return redirect()->route('account.refunds')->with('success', $this->localized('Request cancelled.', 'طلب الاسترجاع اتلغى.'));
    }
}

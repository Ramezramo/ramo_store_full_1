<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SubOrder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Services\OrderStatusService;

class VendorOrderController extends Controller
{
    protected string $guard = 'vendor_web';

    private function vendor()
    {
        return Auth::guard($this->guard)->user();
    }

    // ─────────────────────────────────────────────────────────────────────
    // ORDER LIST — powered by order_sub_orders
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $vendor = $this->vendor();

        $statusFilter = $request->input('status', '');
        $search       = $request->input('search', '');

        $query = DB::table('order_sub_orders as s')
            ->where('s.vendor_id', $vendor->id)
            ->join('orders as o', 'o.id', '=', 's.parent_order_id')
            ->select(['s.*', 'o.billing as o_billing', 'o.payment_method_title as o_payment_title'])
            ->orderByDesc('s.id');

        if ($statusFilter) {
            $query->where('s.status', $statusFilter);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(s.id AS TEXT) LIKE ?', ['%'.$search.'%'])
                  ->orWhereRaw('CAST(s.parent_order_id AS TEXT) LIKE ?', ['%'.$search.'%'])
                  ->orWhereRaw("LOWER(o.billing::text) LIKE ?", ['%'.strtolower($search).'%']);
            });
        }

        $paginator = $query->paginate(15)->withQueryString();

        $orders = $paginator->through(function ($sub) {
            $sub->vendor_items  = json_decode($sub->line_items ?? '[]', true) ?: [];
            $sub->vendor_total  = $sub->total;
            $sub->billing_data  = json_decode($sub->o_billing ?? '{}', true) ?: [];
            $sub->item_count    = array_sum(array_column($sub->vendor_items, 'quantity'));
            return $sub;
        });

        // Stats
        $base = DB::table('order_sub_orders')->where('vendor_id', $vendor->id);
        $stats = [
            'total'      => (clone $base)->count(),
            'pending'    => (clone $base)->where('status', 'pending')->count(),
            'processing' => (clone $base)->where('status', 'processing')->count(),
            'shipped'    => (clone $base)->where('status', 'shipped')->count(),
            'delivered'  => (clone $base)->whereIn('status', ['delivered', 'completed'])->count(),
        ];

        return view('web.vendor.orders.index', compact('orders', 'stats', 'statusFilter', 'search', 'paginator'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // ORDER DETAIL — show one sub-order
    // ─────────────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $vendor = $this->vendor();

        $subOrderResource = SubOrder::find($id);
        if (! $subOrderResource) abort(404);

        try {
            Gate::forUser($vendor)->authorize('view', $subOrderResource);
        } catch (AuthorizationException) {
            abort(404);
        }

        $subOrder = DB::table('order_sub_orders as s')
            ->where('s.id', $id)
            ->join('orders as o', 'o.id', '=', 's.parent_order_id')
            ->select([
                's.*',
                'o.billing as o_billing',
                'o.shipping as o_shipping',
                'o.payment_method_title as o_payment_title',
                'o.payment_method as o_payment_method',
                'o.payment_status as o_payment_status',
                'o.payment_receipt_path as o_payment_receipt_path',
                'o.payment_receipt_name as o_payment_receipt_name',
                'o.payment_rejection_reason as o_payment_rejection_reason',
                'o.created_at as o_created_at',
                'o.customer_note as o_customer_note',
                'o.final_total as o_final_total',
                'o.discount_total as o_discount_total',
                'o.coupon_code as o_coupon_code',
            ])
            ->first();

        if (! $subOrder) abort(404);

        $vendorItems = json_decode($subOrder->line_items ?? '[]', true) ?: [];
        $billing     = json_decode($subOrder->o_billing ?? '{}', true) ?: [];
        $shipping    = json_decode($subOrder->o_shipping ?? '{}', true) ?: [];
        $timeline    = json_decode($subOrder->timeline ?? '[]', true) ?: [];

        // Enrich items with thumbnails
        foreach ($vendorItems as &$item) {
            $product = DB::table('products_data')->where('id', $item['product_id'])->first(['name','images','slug']);
            if ($product) {
                $item['thumbnail'] = \App\Constants\AppConstants::productThumbnailUrl($product->images);
                $item['slug']      = $product->slug;
            }
        }
        unset($item);

        $vendorTotal = $subOrder->total;

        // Messages for this sub-order / vendor pair
        $messages = DB::table('order_messages as m')
            ->leftJoin('vendor_users as v', 'v.id', '=', 'm.vendor_id')
            ->where('m.order_id', $subOrder->parent_order_id)
            ->where(function ($q) use ($vendor, $id) {
                $q->where('m.vendor_id', $vendor->id)
                  ->orWhereNull('m.vendor_id');
            })
            ->where(function ($q) use ($id) {
                $q->where('m.sub_order_id', $id)
                  ->orWhereNull('m.sub_order_id');
            })
            ->orderBy('m.id')
            ->get(['m.*', 'v.shop_name as vendor_shop_name']);
        $paymentReceipts = PaymentReceiptController::history((int) $subOrder->parent_order_id);

        // Use $order as a compat wrapper for the view
        $order = (object) [
            'id'                  => $subOrder->parent_order_id,
            'sub_order_id'        => $subOrder->id,
            'status'              => $subOrder->status,
            'created_at'          => $subOrder->o_created_at,
            'payment_method_title'=> $subOrder->o_payment_title,
            'payment_method'      => $subOrder->o_payment_method,
            'payment_status'      => $subOrder->o_payment_status,
            'payment_receipt_path'=> $subOrder->o_payment_receipt_path,
            'payment_receipt_name'=> $subOrder->o_payment_receipt_name,
            'payment_rejection_reason' => $subOrder->o_payment_rejection_reason,
            'customer_note'       => $subOrder->o_customer_note,
            'final_total'         => $subOrder->o_final_total,
            'discount_total'      => $subOrder->o_discount_total,
            'coupon_code'         => $subOrder->o_coupon_code,
        ];

        return view('web.vendor.orders.show', compact(
            'order', 'subOrder', 'vendorItems', 'vendorTotal', 'billing', 'shipping', 'timeline', 'messages', 'paymentReceipts'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE STATUS — on sub-order
    // ─────────────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, int $id)
    {
        $vendor = $this->vendor();

        $subOrderResource = SubOrder::find($id);
        if (! $subOrderResource) abort(404);

        try {
            Gate::forUser($vendor)->authorize('update', $subOrderResource);
        } catch (AuthorizationException) {
            abort(404);
        }

        $subOrder = DB::table('order_sub_orders')->where('id', $id)->first();
        if (! $subOrder) abort(404);

        // Keep seller shipment states aligned with the admin order controls.
        $allowed   = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
        $newStatus = $request->input('status');
        $newStatus = $newStatus === 'completed' ? 'delivered' : $newStatus;
        if (! in_array($newStatus, $allowed)) {
            return back()->with('error', 'Invalid status.');
        }

        $now      = now();
        $timeline = json_decode($subOrder->timeline ?? '[]', true) ?: [];
        $timeline[] = [
            'status' => $newStatus,
            'note'   => $request->input('note', ''),
            'by'     => 'vendor:'.$vendor->id,
            'at'     => $now->toDateTimeString(),
        ];

        $updateData = [
            'status'     => $newStatus,
            'vendor_status' => $newStatus,
            'timeline'   => json_encode($timeline),
            'updated_at' => $now,
        ];

        if ($request->filled('tracking_number')) {
            $updateData['tracking_number'] = $request->tracking_number;
        }
        if ($request->filled('tracking_carrier')) {
            $updateData['tracking_carrier'] = $request->tracking_carrier;
        }

        DB::table('order_sub_orders')->where('id', $id)->update($updateData);

        app(OrderStatusService::class)->sync($subOrder->parent_order_id);

        return redirect()->route('vendor.orders.show', $id)
            ->with('success', 'Sub-order status updated to "'.ucfirst($newStatus).'".');
    }

    // ─────────────────────────────────────────────────────────────────────
    // REPLY — vendor replies to customer message on sub-order
    // ─────────────────────────────────────────────────────────────────────
    public function reply(Request $request, int $id)
    {
        $vendor = $this->vendor();
        $request->validate(['message' => 'required|string|max:2000']);

        $subOrderResource = SubOrder::find($id);
        if (! $subOrderResource) abort(404);

        try {
            Gate::forUser($vendor)->authorize('update', $subOrderResource);
        } catch (AuthorizationException) {
            abort(404);
        }

        $subOrder = DB::table('order_sub_orders')->where('id', $id)->first();
        if (! $subOrder) abort(404);

        $parentOrder = DB::table('orders')->where('id', $subOrder->parent_order_id)->first();
        if (! $parentOrder) abort(404);

        DB::table('order_messages')->insert([
            'order_id'          => $subOrder->parent_order_id,
            'sub_order_id'      => $id,
            'customer_id'       => $parentOrder->customer_id,
            'vendor_id'         => $vendor->id,
            'sender_type'       => 'vendor',
            'message'           => $request->message,
            'is_vendor_response'=> true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return back()->with('success', 'Reply sent to customer.');
    }

}

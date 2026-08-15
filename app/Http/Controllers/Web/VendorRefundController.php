<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class VendorRefundController extends Controller
{
    private function vendorId(): int
    {
        return (int) auth('vendor_web')->id();
    }

    public function index(Request $request)
    {
        $status = $request->input('status', '');
        $type   = $request->input('type', '');

        $query = DB::table('refund_requests as r')
            ->where('r.vendor_id', $this->vendorId())
            ->leftJoin('orders as o', 'o.id', '=', 'r.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.customer_id')
            ->orderByDesc('r.id');

        if ($status) $query->where('r.status', $status);
        if ($type)   $query->where('r.type', $type);

        $refunds = $query->paginate(20, [
            'r.*',
            'o.final_total as order_total',
            'u.name as customer_name',
            'u.email as customer_email',
        ])->withQueryString();

        return view('web.vendor.refunds', compact('refunds', 'status', 'type'));
    }

    public function show(int $id)
    {
        $vendor = auth('vendor_web')->user();
        $refundResource = RefundRequest::find($id);
        if (! $refundResource) abort(404);

        try {
            Gate::forUser($vendor)->authorize('manageAsVendor', $refundResource);
        } catch (AuthorizationException) {
            abort(404);
        }

        $refund = DB::table('refund_requests as r')
            ->where('r.id', $id)
            ->leftJoin('orders as o', 'o.id', '=', 'r.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.customer_id')
            ->first([
                'r.*',
                'o.final_total as order_total',
                'o.status as order_status',
                'o.date_created as order_date',
                'u.name as customer_name',
                'u.email as customer_email',
                'u.phone as customer_phone',
            ]);

        if (! $refund) abort(404);

        return view('web.vendor.refund-show', compact('refund'));
    }
}

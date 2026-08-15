<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\PaymentConfig;
use App\Models\SubOrder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Services\OrderStatusService;

class PaymentReviewController extends Controller
{
    public function reviewAsAdmin(Request $request, int $id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        abort_if(!$order, 404);

        return $this->review($request, $order, 'admin:' . auth()->id(), auth()->id());
    }

    public function reviewAsVendor(Request $request, int $id)
    {
        $vendor = auth('vendor_web')->user();
        $subOrderResource = SubOrder::find($id);
        abort_if(!$subOrderResource, 404);

        try {
            Gate::forUser($vendor)->authorize('reviewPayment', $subOrderResource);
        } catch (AuthorizationException) {
            abort(404);
        }

        $subOrder = DB::table('order_sub_orders')->where('id', $id)->first();
        abort_if(!$subOrder, 404);

        $order = DB::table('orders')->where('id', $subOrder->parent_order_id)->first();
        abort_if(!$order, 404);

        return $this->review($request, $order, 'vendor:' . $vendor->id, $vendor->id, route('vendor.orders.show', $id));
    }

    private function review(Request $request, object $order, string $reviewer, ?int $reviewerId = null, ?string $redirect = null)
    {
        $data = $request->validate([
            'decision' => 'required|in:confirm,reject',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($data['decision'] === 'reject' && trim($data['rejection_reason'] ?? '') === '') {
            return back()->with('error', 'Please provide a reason when rejecting a receipt.');
        }

        if (!PaymentConfig::isManualMethod($order->payment_method) || $order->payment_status !== 'pending_verification') {
            return back()->with('error', 'This order is not waiting for manual payment verification.');
        }

        $receipt = DB::table('payment_receipts')
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();

        if (!$receipt) {
            return back()->with('error', 'There is no pending receipt to review.');
        }

        $now = now();
        $confirmed = $data['decision'] === 'confirm';
        $newPaymentStatus = $confirmed ? 'confirmed' : 'rejected';
        $note = $confirmed ? 'Payment receipt approved.' : 'Payment receipt rejected: ' . trim($data['rejection_reason']);
        $timeline = json_decode($order->timeline ?? '[]', true) ?: [];
        $timeline[] = [
            'status' => $newPaymentStatus,
            'note' => $note,
            'by' => $reviewer,
            'at' => $now->toDateTimeString(),
        ];

        DB::transaction(function () use ($receipt, $order, $reviewerId, $data, $now, $confirmed, $newPaymentStatus, $timeline) {
            DB::table('payment_receipts')->where('id', $receipt->id)->update([
                'status' => $newPaymentStatus,
                'rejection_reason' => $confirmed ? null : trim($data['rejection_reason']),
                'reviewed_by' => $reviewerId,
                'reviewed_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('orders')->where('id', $order->id)->update([
                'payment_status' => $newPaymentStatus,
                'set_paid' => $confirmed,
                'needs_payment' => !$confirmed,
                'date_paid' => $confirmed ? $now : null,
                'date_paid_gmt' => $confirmed ? $now->toDateTimeString() : '',
                'payment_reviewed_at' => $now,
                'payment_reviewed_by' => $reviewerId,
                'payment_rejection_reason' => $confirmed ? null : trim($data['rejection_reason']),
                'timeline' => json_encode($timeline),
                'date_modified' => $now,
                'updated_at' => $now,
            ]);

            app(OrderStatusService::class)->sync($order->id);
        });

        return redirect($redirect ?: route('admin.orders.detail', $order->id))
            ->with('success', $confirmed ? 'Payment confirmed.' : 'Receipt rejected. The customer can upload a new receipt.');
    }
}
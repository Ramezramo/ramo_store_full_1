<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\PaymentConfig;
use App\Models\SubOrder;
use App\Services\OrderPaymentExpiryService;
use App\Services\OrderStatusService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

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
        $vendor = app(\App\Services\VendorWorkspace::class)->vendor();
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
            'receipt_id' => 'nullable|integer|min:1',
        ]);

        if ($data['decision'] === 'reject' && trim($data['rejection_reason'] ?? '') === '') {
            return back()->with('error', 'Please provide a reason when rejecting a receipt.');
        }

        $confirmed = $data['decision'] === 'confirm';
        $result = DB::transaction(function () use ($order, $reviewer, $reviewerId, $data, $confirmed) {
            // Receipt uploads and the expiry job also lock the order before changing payment state.
            $lockedOrder = DB::table('orders')->where('id', $order->id)->lockForUpdate()->first();
            abort_if(!$lockedOrder, 404);

            if (
                !PaymentConfig::isManualMethod($lockedOrder->payment_method)
                || $lockedOrder->payment_status !== 'pending_verification'
                || in_array($lockedOrder->status, ['cancelled', 'canceled', 'refunded'], true)
            ) {
                return ['error' => 'This order is not waiting for manual payment verification.'];
            }

            if ($lockedOrder->payment_receipt_path !== $order->payment_receipt_path) {
                return ['error' => 'The customer uploaded another receipt. Open the latest receipt before reviewing it.'];
            }

            $receipt = DB::table('payment_receipts')
                ->where('order_id', $lockedOrder->id)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->first();
            if (!$receipt) {
                return ['error' => 'There is no pending receipt to review.'];
            }
            if (isset($data['receipt_id']) && (int) $data['receipt_id'] !== (int) $receipt->id) {
                return ['error' => 'The customer uploaded another receipt. Open the latest receipt before reviewing it.'];
            }

            $now = now();
            $newPaymentStatus = $confirmed ? 'confirmed' : 'rejected';
            $reason = $confirmed ? null : trim($data['rejection_reason']);
            $timeline = json_decode($lockedOrder->timeline ?? '[]', true) ?: [];
            $timeline[] = [
                'status' => $newPaymentStatus,
                'note' => $confirmed ? 'Payment receipt approved.' : 'Payment receipt rejected: '.$reason,
                'by' => $reviewer,
                'at' => $now->toDateTimeString(),
            ];
            // Only the receipt selected for review receives the final decision.
            // Older pending uploads are no longer awaiting review after this
            // order-level decision, so mark them as superseded instead of
            // leaving a misleading Pending status in the customer's history.
            DB::table('payment_receipts')
                ->where('order_id', $lockedOrder->id)
                ->where('status', 'pending')
                ->where('id', '<>', $receipt->id)
                ->update([
                    'status' => 'superseded',
                    'updated_at' => $now,
                ]);

            DB::table('payment_receipts')->where('id', $receipt->id)->update([
                'status' => $newPaymentStatus,
                'rejection_reason' => $reason,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('orders')->where('id', $lockedOrder->id)->update([
                'payment_status' => $newPaymentStatus,
                'set_paid' => $confirmed,
                'needs_payment' => !$confirmed,
                'date_paid' => $confirmed ? $now : null,
                'date_paid_gmt' => $confirmed ? $now->toDateTimeString() : '',
                'payment_reviewed_at' => $now,
                'payment_reviewed_by' => $reviewerId,
                'payment_rejection_reason' => $reason,
                'timeline' => json_encode($timeline),
                'date_modified' => $now,
                'updated_at' => $now,
            ]);

            if (!$confirmed) {
                $lockedOrder->payment_status = $newPaymentStatus;
                $lockedOrder->timeline = json_encode($timeline);
                if (app(OrderPaymentExpiryService::class)->expireLockedOrder($lockedOrder)) {
                    return ['message' => 'Receipt rejected. The payment deadline has passed and the order was cancelled.'];
                }
            }

            app(OrderStatusService::class)->sync($lockedOrder->id);

            return ['message' => $confirmed ? 'Payment confirmed.' : 'Receipt rejected. The customer can upload a new receipt.'];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return redirect($redirect ?: route('admin.orders.detail', $order->id))
            ->with('success', $result['message']);
    }
}

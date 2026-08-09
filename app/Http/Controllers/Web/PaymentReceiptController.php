<?php

namespace App\Http\Controllers\Web;

use App\Helpers\PaymentConfig;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\OrderStatusService;

class PaymentReceiptController extends Controller
{
    public function uploadForAccount(Request $request, int $id)
    {
        $order = DB::table('orders')
            ->where('id', $id)
            ->where('customer_id', auth()->id())
            ->first();

        abort_if(!$order, 404);

        return $this->upload($request, $order, auth()->id());
    }

    public function uploadForGuest(Request $request, int $id)
    {
        $request->validate(['email' => 'required|email|max:255']);
        $order = DB::table('orders')->where('id', $id)->first();
        abort_if(!$order, 404);

        $billing = json_decode($order->billing ?? '{}', true) ?: [];
        abort_unless(
            strtolower(trim($billing['email'] ?? '')) === strtolower(trim($request->email)),
            403
        );

        return $this->upload($request, $order, null);
    }

    private function upload(Request $request, object $order, ?int $userId)
    {
        if (!PaymentConfig::isManualMethod($order->payment_method)) {
            return back()->with('error', 'This order does not use a manual payment method.');
        }

        if ($order->payment_status === 'confirmed') {
            return back()->with('error', 'This payment has already been confirmed.');
        }

        $request->validate([
            'receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $path = $request->file('receipt')->store('payment-receipts', 'public');
        $now = now();

        DB::transaction(function () use ($order, $userId, $path, $request, $now) {
            DB::table('payment_receipts')->insert([
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'file_path' => $path,
                'original_name' => $request->file('receipt')->getClientOriginalName(),
                'status' => 'pending',
                'uploaded_by' => $userId,
                'uploaded_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $timeline = json_decode($order->timeline ?? '[]', true) ?: [];
            $timeline[] = [
                'status' => 'pending_verification',
                'note' => 'Payment receipt uploaded for review.',
                'at' => $now->toDateTimeString(),
            ];

            DB::table('orders')->where('id', $order->id)->update([
                'payment_status' => 'pending_verification',
                'payment_receipt_path' => $path,
                'payment_receipt_name' => $request->file('receipt')->getClientOriginalName(),
                'payment_receipt_uploaded_at' => $now,
                'payment_rejection_reason' => null,
                'timeline' => json_encode($timeline),
                'date_modified' => $now,
                'updated_at' => $now,
            ]);

            app(OrderStatusService::class)->sync($order->id);
        });

        return back()->with('success', 'Receipt uploaded. Your payment is now pending verification.');
    }

    public static function history(int $orderId)
    {
        return DB::table('payment_receipts as receipts')
            ->leftJoin('users as uploaders', 'uploaders.id', '=', 'receipts.uploaded_by')
            ->leftJoin('users as reviewers', 'reviewers.id', '=', 'receipts.reviewed_by')
            ->where('receipts.order_id', $orderId)
            ->select([
                'receipts.*',
                'uploaders.name as uploader_name',
                'uploaders.email as uploader_email',
                'reviewers.name as reviewer_name',
                'reviewers.email as reviewer_email',
            ])
            ->orderByDesc('receipts.id')
            ->get();
    }
}
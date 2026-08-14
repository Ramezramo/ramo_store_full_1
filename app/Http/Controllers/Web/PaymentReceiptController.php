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
    public const MAX_UPLOADS_PER_ORDER = 3;

    private function localized(string $english, string $arabic): string
    {
        return session('locale') === 'ar' ? $arabic : $english;
    }
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
        $request->validate([
            'receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $result = DB::transaction(function () use ($order, $userId, $request) {
            // Every upload path locks the same order row before it counts or
            // creates receipt records, so concurrent requests cannot exceed
            // the customer-facing three-upload limit.
            $lockedOrder = DB::table('orders')->where('id', $order->id)->lockForUpdate()->first();
            abort_if(!$lockedOrder, 404);

            if (!PaymentConfig::isManualMethod($lockedOrder->payment_method)) {
                return ['error' => $this->localized('This order does not use a manual payment method.', 'الطلب ده مش بيستخدم طريقة دفع يدوي.')];
            }

            if ($lockedOrder->payment_status === 'confirmed') {
                return ['error' => $this->localized('This payment has already been confirmed.', 'الدفع ده تم تأكيده بالفعل.')];
            }

            $uploadCount = DB::table('payment_receipts')->where('order_id', $lockedOrder->id)->count();
            if ($uploadCount >= self::MAX_UPLOADS_PER_ORDER) {
                return ['error' => $this->localized(
                    'You have reached the maximum of 3 receipt uploads for this order. Please wait for payment review.',
                    'وصلت للحد الأقصى وهو 3 إيصالات للطلب ده. من فضلك استنى مراجعة الدفع.'
                )];
            }

            $path = $request->file('receipt')->store('payment-receipts', 'public');
            $now = now();

            DB::table('payment_receipts')->insert([
                'order_id' => $lockedOrder->id,
                'payment_method' => $lockedOrder->payment_method,
                'file_path' => $path,
                'original_name' => $request->file('receipt')->getClientOriginalName(),
                'status' => 'pending',
                'uploaded_by' => $userId,
                'uploaded_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $timeline = json_decode($lockedOrder->timeline ?? '[]', true) ?: [];
            $timeline[] = [
                'status' => 'pending_verification',
                'note' => 'Payment receipt uploaded for review.',
                'at' => $now->toDateTimeString(),
            ];

            DB::table('orders')->where('id', $lockedOrder->id)->update([
                'payment_status' => 'pending_verification',
                'payment_receipt_path' => $path,
                'payment_receipt_name' => $request->file('receipt')->getClientOriginalName(),
                'payment_receipt_uploaded_at' => $now,
                'payment_rejection_reason' => null,
                'timeline' => json_encode($timeline),
                'date_modified' => $now,
                'updated_at' => $now,
            ]);

            app(OrderStatusService::class)->sync($lockedOrder->id);

            return ['success' => true];
        });

        if (!empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', $this->localized('Receipt uploaded. Your payment is now pending verification.', 'الإيصال اترفع. الدفع دلوقتي في انتظار المراجعة.'));
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
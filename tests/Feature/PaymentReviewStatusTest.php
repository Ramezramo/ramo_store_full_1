<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\PaymentReceiptController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentReviewStatusTest extends TestCase
{
    public function test_confirming_a_receipt_supersedes_other_pending_uploads(): void
    {
        $admin = User::create([
            'name' => 'Receipt Review Admin',
            'email' => 'receipt-review-admin-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
        ]);
        // role is intentionally not mass assignable; seed the test privilege
        // through the same trusted server-side path used by administration.
        DB::table('users')->where('id', $admin->id)->update(['role' => json_encode(['admin'])]);
        $admin->refresh();
        $orderId = null;

        try {
            $now = now();
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => null,
                'status' => 'pending',
                'payment_status' => 'pending_verification',
                'currency' => 'EGP',
                'currency_symbol' => 'ج.م',
                'payment_method' => 'vodafone_cash',
                'payment_method_title' => 'Vodafone Cash',
                'billing' => '{}',
                'shipping' => '{}',
                'line_items' => '[]',
                'original_total' => 0,
                'final_total' => 0,
                'discount_total' => 0,
                'order_key' => 'test_receipt_review_' . uniqid(),
                'date_created' => $now,
                'date_modified' => $now,
                'date_created_gmt' => $now->toDateTimeString(),
                'date_modified_gmt' => $now->toDateTimeString(),
                'date_paid_gmt' => '',
                'date_completed_gmt' => '',
                'created_at' => $now,
                'updated_at' => $now,
                'payment_url' => '',
                'is_editable' => true,
                'needs_payment' => false,
                'needs_processing' => true,
                'set_paid' => false,
                'number' => 0,
                'timeline' => '[]',
                'created_via' => 'website',
                'cart_hash' => '',
                'parent_id' => 0,
                'shipping_total' => 0,
                'shipping_tax' => 0,
                'cart_tax' => 0,
                'total_tax' => 0,
            ]);
            DB::table('orders')->where('id', $orderId)->update(['number' => $orderId]);

            $receiptIds = [];
            foreach (range(1, 3) as $attempt) {
                $receiptIds[] = DB::table('payment_receipts')->insertGetId([
                    'order_id' => $orderId,
                    'payment_method' => 'vodafone_cash',
                    'file_path' => "payment-receipts/review-{$attempt}.png",
                    'original_name' => "review-{$attempt}.png",
                    'status' => 'pending',
                    'uploaded_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->actingAs($admin)
                ->withSession(['_token' => 'receipt-review-test-token'])
                ->post(route('admin.orders.payment-review', $orderId), [
                    '_token' => 'receipt-review-test-token',
                    'decision' => 'confirm',
                ])
                ->assertRedirect(route('admin.orders.detail', $orderId))
                ->assertSessionHas('success', 'Payment confirmed.');

            $this->assertDatabaseHas('payment_receipts', [
                'id' => $receiptIds[2],
                'status' => 'confirmed',
            ]);
            $this->assertDatabaseHas('payment_receipts', [
                'id' => $receiptIds[0],
                'status' => 'superseded',
            ]);
            $this->assertDatabaseHas('payment_receipts', [
                'id' => $receiptIds[1],
                'status' => 'superseded',
            ]);
            $this->assertDatabaseHas('orders', [
                'id' => $orderId,
                'payment_status' => 'confirmed',
                'set_paid' => true,
            ]);
        } finally {
            if ($orderId) {
                DB::table('payment_receipts')->where('order_id', $orderId)->delete();
                DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
                DB::table('orders')->where('id', $orderId)->delete();
            }
            $admin->delete();
        }
    }

    public function test_confirmed_orders_do_not_display_legacy_pending_receipts_as_pending(): void
    {
        $now = now();
        $orderId = DB::table('orders')->insertGetId([
            'customer_id' => null,
            'status' => 'pending',
            'payment_status' => 'confirmed',
            'currency' => 'EGP',
            'currency_symbol' => 'ج.م',
            'payment_method' => 'vodafone_cash',
            'payment_method_title' => 'Vodafone Cash',
            'billing' => '{}',
            'shipping' => '{}',
            'line_items' => '[]',
            'original_total' => 0,
            'final_total' => 0,
            'discount_total' => 0,
            'order_key' => 'test_receipt_history_' . uniqid(),
            'date_created' => $now,
            'date_modified' => $now,
            'date_created_gmt' => $now->toDateTimeString(),
            'date_modified_gmt' => $now->toDateTimeString(),
            'date_paid_gmt' => $now->toDateTimeString(),
            'date_completed_gmt' => '',
            'created_at' => $now,
            'updated_at' => $now,
            'payment_url' => '',
            'is_editable' => true,
            'needs_payment' => false,
            'needs_processing' => true,
            'set_paid' => true,
            'number' => 0,
            'timeline' => '[]',
            'created_via' => 'website',
            'cart_hash' => '',
            'parent_id' => 0,
            'shipping_total' => 0,
            'shipping_tax' => 0,
            'cart_tax' => 0,
            'total_tax' => 0,
        ]);
        DB::table('orders')->where('id', $orderId)->update(['number' => $orderId]);
        DB::table('payment_receipts')->insert([
            'order_id' => $orderId,
            'payment_method' => 'vodafone_cash',
            'file_path' => 'payment-receipts/legacy-pending.png',
            'original_name' => 'legacy-pending.png',
            'status' => 'pending',
            'uploaded_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        try {
            $history = PaymentReceiptController::history($orderId);

            $this->assertCount(1, $history);
            $this->assertSame('superseded', $history->first()->status);
            $this->assertSame('pending', DB::table('payment_receipts')->where('order_id', $orderId)->value('status'));
        } finally {
            DB::table('payment_receipts')->where('order_id', $orderId)->delete();
            DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
        }
    }
}


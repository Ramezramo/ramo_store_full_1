<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentReceiptUploadLimitTest extends TestCase
{
    public function test_customer_cannot_upload_a_fourth_payment_receipt(): void
    {
        Storage::fake('public');

        $customer = User::create([
            'name' => 'Receipt Limit Customer',
            'email' => 'receipt-limit-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
            'role' => json_encode(['customer']),
        ]);
        $orderId = null;

        try {
            $now = now();
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $customer->id,
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
                'order_key' => 'test_receipt_limit_' . uniqid(),
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

            foreach (range(1, 3) as $attempt) {
                DB::table('payment_receipts')->insert([
                    'order_id' => $orderId,
                    'payment_method' => 'vodafone_cash',
                    'file_path' => "payment-receipts/test-receipt-{$attempt}.png",
                    'original_name' => "test-receipt-{$attempt}.png",
                    'status' => 'pending',
                    'uploaded_by' => $customer->id,
                    'uploaded_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->actingAs($customer)
                ->get(route('account.order', $orderId))
                ->assertOk()
                ->assertSee('You have reached the receipt upload limit.', false)
                ->assertSee('All 3 of 3 upload attempts have been used. Please wait for payment review.', false)
                ->assertDontSee('Upload replacement receipt', false);

            $this->actingAs($customer)
                ->withSession(['_token' => 'receipt-limit-test-token'])
                ->post(route('account.order.payment-receipt', $orderId), [
                    '_token' => 'receipt-limit-test-token',
                    'receipt' => UploadedFile::fake()->create('fourth-receipt.png', 10, 'image/png'),
                ])
                ->assertRedirect(route('account.order', $orderId))
                ->assertSessionHas('error', 'You have reached the maximum of 3 receipt uploads for this order. Please wait for payment review.');

            $this->assertSame(3, DB::table('payment_receipts')->where('order_id', $orderId)->count());
        } finally {
            if ($orderId) {
                DB::table('payment_receipts')->where('order_id', $orderId)->delete();
                DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
                DB::table('orders')->where('id', $orderId)->delete();
            }
            $customer->delete();
        }
    }
}

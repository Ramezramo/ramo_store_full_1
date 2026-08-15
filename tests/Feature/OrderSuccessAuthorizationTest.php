<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderSuccessAuthorizationTest extends TestCase
{
    public function test_order_success_receipt_is_scoped_to_its_authenticated_customer(): void
    {
        $owner = User::create([
            'name' => 'Order Receipt Owner',
            'email' => 'order-receipt-owner-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
            'role' => json_encode(['customer']),
        ]);
        $otherCustomer = User::create([
            'name' => 'Order Receipt Other Customer',
            'email' => 'order-receipt-other-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
            'role' => json_encode(['customer']),
        ]);
        $orderId = null;

        try {
            $now = now();
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $owner->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'currency' => 'EGP',
                'currency_symbol' => 'ج.م',
                'payment_method' => 'vodafone_cash',
                'payment_method_title' => 'Vodafone Cash',
                'payment_receipt_path' => 'payment-receipts/already-uploaded.png',
                'payment_receipt_name' => 'already-uploaded.png',
                'billing' => '{}',
                'shipping' => '{}',
                'line_items' => '[]',
                'original_total' => 0,
                'final_total' => 0,
                'discount_total' => 0,
                'order_key' => 'test_receipt_' . uniqid(),
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

            $this->get(route('order.success', $orderId))->assertNotFound();
            $this->actingAs($otherCustomer)->get(route('order.success', $orderId))->assertNotFound();
            $this->actingAs($otherCustomer)->get(route('account.order', $orderId))->assertNotFound();
            $this->actingAs($owner)->get(route('order.success', $orderId))->assertOk();
            $this->actingAs($owner)->get(route('account.order', $orderId))
                ->assertOk()
                ->assertSee('Your receipt has been uploaded and is pending review.', false)
                ->assertSee('Choose a replacement image only if you need to change the current receipt.', false)
                ->assertSee('Upload replacement receipt', false)
                ->assertSee('this.form.requestSubmit()', false);
        } finally {
            if ($orderId) {
                DB::table('order_sub_orders')->where('parent_order_id', $orderId)->delete();
                DB::table('orders')->where('id', $orderId)->delete();
            }
            $otherCustomer->delete();
            $owner->delete();
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IdorAuthorizationTest extends TestCase
{
    public function test_customer_cannot_attach_a_note_to_another_customers_order(): void
    {
        $owner = $this->createCustomer('idor-note-owner');
        $otherCustomer = $this->createCustomer('idor-note-other');
        $orderId = $this->createOrder($owner->id);

        try {
            $this->actingAs($otherCustomer, 'sanctum')
                ->postJson('/api/user/create-user-note', [
                    'order_id' => $orderId,
                    'note' => 'Unauthorized note',
                ])
                ->assertForbidden();

            $this->assertDatabaseMissing('user_notes', [
                'order_id' => $orderId,
                'user_id' => $otherCustomer->id,
            ]);
        } finally {
            DB::table('user_notes')->where('order_id', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_customer_cannot_read_another_customers_order_notes(): void
    {
        $owner = $this->createCustomer('idor-read-owner');
        $otherCustomer = $this->createCustomer('idor-read-other');
        $orderId = $this->createOrder($owner->id);

        try {
            DB::table('user_notes')->insert([
                [
                    'user_id' => $owner->id,
                    'order_id' => $orderId,
                    'note' => 'Customer-visible note',
                    'customer_note' => true,
                ],
                [
                    'user_id' => $owner->id,
                    'order_id' => $orderId,
                    'note' => 'Internal operational note',
                    'customer_note' => false,
                ],
            ]);

            $this->actingAs($otherCustomer, 'sanctum')
                ->getJson('/api/user/get-order-notes?order_id=' . $orderId)
                ->assertForbidden();

            $response = $this->actingAs($owner, 'sanctum')
                ->getJson('/api/user/get-order-notes?order_id=' . $orderId)
                ->assertOk();

            $response->assertJsonCount(1, 'data');
            $response->assertJsonPath('data.0.note', 'Customer-visible note');
        } finally {
            DB::table('user_notes')->where('order_id', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_guest_payment_receipt_upload_uses_the_order_lookup_rate_limiter(): void
    {
        $route = Route::getRoutes()->getByName('guest.order.payment-receipt');

        $this->assertNotNull($route);
        $this->assertContains('throttle:order-lookup', $route->middleware());
    }

    public function test_customer_cannot_read_another_customers_refund_request(): void
    {
        $owner = $this->createCustomer('idor-refund-owner');
        $otherCustomer = $this->createCustomer('idor-refund-other');
        $orderId = $this->createOrder($owner->id);
        $refundId = DB::table('refund_requests')->insertGetId([
            'order_id' => $orderId,
            'customer_id' => $owner->id,
            'vendor_id' => null,
            'type' => 'refund',
            'reason' => 'damaged',
            'description' => 'Test refund request',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->actingAs($otherCustomer)
                ->get('/account/refunds/' . $refundId)
                ->assertNotFound();

            $this->actingAs($owner)
                ->get('/account/refunds/' . $refundId)
                ->assertOk();
        } finally {
            DB::table('refund_requests')->where('id', $refundId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_customer_cannot_cancel_another_customers_refund_request(): void
    {
        $owner = $this->createCustomer('idor-cancel-owner');
        $otherCustomer = $this->createCustomer('idor-cancel-other');
        $orderId = $this->createOrder($owner->id);
        $refundId = DB::table('refund_requests')->insertGetId([
            'order_id' => $orderId,
            'customer_id' => $owner->id,
            'vendor_id' => null,
            'type' => 'refund',
            'reason' => 'changed_mind',
            'description' => null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

            $this->actingAs($otherCustomer)
                ->patch('/account/refunds/' . $refundId . '/cancel')
                ->assertNotFound();

            $this->assertDatabaseHas('refund_requests', [
                'id' => $refundId,
                'customer_id' => $owner->id,
                'status' => 'pending',
            ]);
        } finally {
            DB::table('refund_requests')->where('id', $refundId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_customer_cannot_update_another_customers_cart_item(): void
    {
        $owner = $this->createCustomer('idor-cart-update-owner');
        $otherCustomer = $this->createCustomer('idor-cart-update-other');
        $cartItemId = DB::table('cart_items')->insertGetId([
            'user_id' => $owner->id,
            'product_id' => 999999,
            'variation_id' => null,
            'qty' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->actingAs($otherCustomer, 'sanctum')
                ->putJson('/api/cart/' . $cartItemId, ['qty' => 3])
                ->assertNotFound();

            $this->assertDatabaseHas('cart_items', [
                'id' => $cartItemId,
                'user_id' => $owner->id,
                'qty' => 1,
            ]);
        } finally {
            DB::table('cart_items')->where('id', $cartItemId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_customer_cannot_remove_another_customers_cart_item(): void
    {
        $owner = $this->createCustomer('idor-cart-remove-owner');
        $otherCustomer = $this->createCustomer('idor-cart-remove-other');
        $cartItemId = DB::table('cart_items')->insertGetId([
            'user_id' => $owner->id,
            'product_id' => 999999,
            'variation_id' => null,
            'qty' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->actingAs($otherCustomer, 'sanctum')
                ->deleteJson('/api/cart/' . $cartItemId)
                ->assertNotFound();

            $this->assertDatabaseHas('cart_items', [
                'id' => $cartItemId,
                'user_id' => $owner->id,
                'qty' => 1,
            ]);
        } finally {
            DB::table('cart_items')->where('id', $cartItemId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_cart_update_keeps_the_owner_filter_on_the_mutating_query(): void
    {
        $owner = $this->createCustomer('idor-cart-owner');
        $otherCustomer = $this->createCustomer('idor-cart-other');
        $cartItemId = DB::table('cart_items')->insertGetId([
            'user_id' => $owner->id,
            'product_id' => 999999,
            'variation_id' => null,
            'qty' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->actingAs($otherCustomer, 'sanctum')
                ->putJson('/api/cart/' . $cartItemId, ['qty' => 3])
                ->assertNotFound();

            $this->assertDatabaseHas('cart_items', [
                'id' => $cartItemId,
                'user_id' => $owner->id,
                'qty' => 1,
            ]);

            $this->actingAs($owner, 'sanctum')
                ->putJson('/api/cart/' . $cartItemId, ['qty' => 2])
                ->assertOk();

            $this->assertDatabaseHas('cart_items', [
                'id' => $cartItemId,
                'user_id' => $owner->id,
                'qty' => 2,
            ]);
        } finally {
            DB::table('cart_items')->where('id', $cartItemId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    private function createCustomer(string $prefix): User
    {
        return User::create([
            'name' => 'IDOR Test Customer',
            'email' => $prefix . '-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
        ]);
    }

    private function createOrder(int $customerId): int
    {
        $now = now();

        $orderId = DB::table('orders')->insertGetId([
            'customer_id' => $customerId,
            'status' => 'pending',
            'payment_status' => 'pending',
            'currency' => 'EGP',
            'currency_symbol' => 'ج.م',
            'payment_method' => 'cash_on_delivery',
            'payment_method_title' => 'Cash on Delivery',
            'billing' => '{}',
            'shipping' => '{}',
            'line_items' => '[]',
            'original_total' => 0,
            'final_total' => 0,
            'discount_total' => 0,
            'order_key' => 'idor_' . uniqid(),
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

        return $orderId;
    }
}

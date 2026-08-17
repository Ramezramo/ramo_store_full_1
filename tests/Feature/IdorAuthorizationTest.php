<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\ProductReview;
use App\Models\RefundRequest;
use App\Models\SubOrder;
use App\Models\User;
use App\Models\VendorUser;
use App\Policies\ProductReviewPolicy;
use App\Policies\RefundRequestPolicy;
use App\Policies\SubOrderPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IdorAuthorizationTest extends TestCase
{
    public function test_vendor_cannot_probe_foreign_order_status_through_noop_response(): void
    {
        $owner = $this->createVendor('idor-vendor-owner');
        $otherVendor = $this->createVendor('idor-vendor-other');
        $customer = $this->createCustomer('idor-vendor-customer');
        $orderId = $this->createOrder($customer->id);
        DB::table('orders')->where('id', $orderId)->update([
            'status' => 'pending',
            'parent_vendors_ids' => json_encode([$owner->id]),
        ]);

        try {
            $this->actingAs($otherVendor, 'sanctum')
                ->postJson('/api/vendor/update-order-state', [
                    'order_id' => $orderId,
                    'status' => 'pending',
                ])
                ->assertForbidden();

            $this->actingAs($owner, 'sanctum')
                ->postJson('/api/vendor/update-order-state', [
                    'order_id' => $orderId,
                    'status' => 'pending',
                ])
                ->assertStatus(400);
        } finally {
            DB::table('orders')->where('id', $orderId)->delete();
            $customer->delete();
            $otherVendor->delete();
            $owner->delete();
        }
    }

    public function test_vendor_coupon_crud_is_scoped_to_the_authenticated_vendor(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $owner = $this->createVendor('idor-coupon-owner');
        $otherVendor = $this->createVendor('idor-coupon-other');
        $foreignCouponId = $this->createCoupon($owner->id, 'idor-foreign-coupon');
        $globalCouponId = $this->createCoupon(null, 'idor-global-coupon');
        $createdCouponCode = 'idor-created-coupon-'.uniqid();

        try {
            $this->actingAs($otherVendor, 'sanctum')
                ->getJson('/api/ramo/coupons/get/'.$foreignCouponId)
                ->assertNotFound();

            $this->actingAs($otherVendor, 'sanctum')
                ->putJson('/api/ramo/coupons/update/'.$foreignCouponId, ['amount' => 25])
                ->assertNotFound();

            $this->actingAs($otherVendor, 'sanctum')
                ->deleteJson('/api/ramo/coupons/remove/'.$foreignCouponId)
                ->assertNotFound();

            $this->actingAs($otherVendor, 'sanctum')
                ->getJson('/api/ramo/coupons/get/'.$globalCouponId)
                ->assertNotFound();

            $this->actingAs($otherVendor, 'sanctum')
                ->putJson('/api/ramo/coupons/update/'.$globalCouponId, ['amount' => 25])
                ->assertNotFound();

            $this->actingAs($otherVendor, 'sanctum')
                ->deleteJson('/api/ramo/coupons/remove/'.$globalCouponId)
                ->assertNotFound();

            $this->actingAs($otherVendor, 'sanctum')
                ->getJson('/api/ramo/coupons/show')
                ->assertOk()
                ->assertJsonMissing(['vendor_id' => $owner->id])
                ->assertJsonMissing(['vendor_id' => null]);

            $this->actingAs($owner, 'sanctum')
                ->getJson('/api/ramo/coupons/get/'.$foreignCouponId)
                ->assertOk();

            $this->actingAs($owner, 'sanctum')
                ->postJson('/api/ramo/coupons/store', [
                    'code' => $createdCouponCode,
                    'amount' => 10,
                    'status' => 'draft',
                    'discount_type' => 'percent',
                ])
                ->assertCreated();

            $this->assertDatabaseHas('coupons', [
                'code' => $createdCouponCode,
                'vendor_id' => $owner->id,
            ]);
        } finally {
            DB::table('coupons')->whereIn('id', [$foreignCouponId, $globalCouponId])->delete();
            DB::table('coupons')->where('code', $createdCouponCode)->delete();
            $otherVendor->delete();
            $owner->delete();
        }
    }

    public function test_coupon_validate_uses_authenticated_user_id_not_payload(): void
    {
        $customer = $this->createCustomer('coupon-authenticated-user');
        $spoofedUser = $this->createCustomer('coupon-spoofed-user');
        $couponCode = 'idor-validate-'.uniqid();
        $productId = null;
        $variationId = null;
        $couponId = DB::table('coupons')->insertGetId([
            'code' => $couponCode,
            'vendor_id' => null,
            'amount' => 10,
            'status' => 'publish',
            'discount_type' => 'percent',
            'usage_count' => 0,
            'usage_limit' => 0,
            'usage_limit_per_user' => 1,
            'individual_use' => true,
        ]);
        $now = now();
        $productId = DB::table('products_data')->insertGetId([
            'name' => 'IDOR coupon validate product',
            'slug' => 'idor-coupon-validate-'.uniqid(),
            'search_text' => 'IDOR coupon validate product',
            'status' => 'publish',
            'acceptance_status' => 'approved',
            'minimum_order_qty' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'main_variation' => true,
            'status' => 'publish',
            'stock_status' => 'instock',
            'attributes' => '{}',
            'price' => 100,
            'regular_price' => 100,
            'sale_price' => 100,
            'stock_quantity' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('cart_items')->insert([
            'user_id' => $customer->id,
            'product_id' => $productId,
            'variation_id' => $variationId,
            'qty' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        try {
            $this->actingAs($customer, 'sanctum')
                ->postJson('/api/ramo/coupons/validate', [
                    'code' => $couponCode,
                    'cart_total' => 100,
                    'user_id' => $spoofedUser->id,
                ])
                ->assertOk()
                ->assertJsonPath('success', true);
        } finally {
            DB::table('coupons')->where('id', $couponId)->delete();
            DB::table('cart_items')->where('user_id', $customer->id)->delete();
            if ($variationId) {
                DB::table('product_variations')->where('id', $variationId)->delete();
            }
            if ($productId) {
                DB::table('products_data')->where('id', $productId)->delete();
            }
            $spoofedUser->delete();
            $customer->delete();
        }
    }

    public function test_coupon_apply_uses_authenticated_user_id_not_payload(): void
    {
        $customer = $this->createCustomer('coupon-apply-authenticated');
        $spoofedUser = $this->createCustomer('coupon-apply-spoofed');
        $couponCode = 'idor-apply-'.uniqid();
        $productId = null;
        $variationId = null;
        $couponId = DB::table('coupons')->insertGetId([
            'code' => $couponCode,
            'vendor_id' => null,
            'amount' => 10,
            'status' => 'publish',
            'discount_type' => 'percent',
            'usage_count' => 0,
            'usage_limit' => 0,
            'usage_limit_per_user' => 1,
            'individual_use' => true,
        ]);
        $now = now();
        $productId = DB::table('products_data')->insertGetId([
            'name' => 'IDOR coupon apply product',
            'slug' => 'idor-coupon-apply-'.uniqid(),
            'search_text' => 'IDOR coupon apply product',
            'status' => 'publish',
            'acceptance_status' => 'approved',
            'minimum_order_qty' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'main_variation' => true,
            'status' => 'publish',
            'stock_status' => 'instock',
            'attributes' => '{}',
            'price' => 100,
            'regular_price' => 100,
            'sale_price' => 100,
            'stock_quantity' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('cart_items')->insert([
            'user_id' => $customer->id,
            'product_id' => $productId,
            'variation_id' => $variationId,
            'qty' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        try {
            $this->actingAs($customer, 'sanctum')
                ->postJson('/api/ramo/coupons/apply', [
                    'code' => $couponCode,
                    'cart_total' => 100,
                    'user_id' => $spoofedUser->id,
                ])
                ->assertOk()
                ->assertJsonPath('success', true);

            $this->assertDatabaseMissing('coupon_user_limits', [
                'coupon_id' => $couponId,
                'user_id' => $customer->id,
            ]);
            $this->assertDatabaseMissing('coupon_user_limits', [
                'coupon_id' => $couponId,
                'user_id' => $spoofedUser->id,
            ]);
        } finally {
            DB::table('coupon_user_limits')->where('coupon_id', $couponId)->delete();
            DB::table('coupons')->where('id', $couponId)->delete();
            if ($customer) {
                DB::table('cart_items')->where('user_id', $customer->id)->delete();
            }
            if ($variationId) {
                DB::table('product_variations')->where('id', $variationId)->delete();
            }
            if ($productId) {
                DB::table('products_data')->where('id', $productId)->delete();
            }
            $spoofedUser->delete();
            $customer->delete();
        }
    }

    public function test_owner_ids_are_not_mass_assignable_on_cart_and_refund_models(): void
    {
        $cartItem = new CartItem([
            'user_id' => 12345,
            'product_id' => 1,
            'qty' => 1,
        ]);
        $refund = new RefundRequest([
            'order_id' => 1,
            'customer_id' => 12345,
            'vendor_id' => 54321,
            'reason' => 'Test reason',
        ]);

        $this->assertNull($cartItem->user_id);
        $this->assertNull($refund->customer_id);
        $this->assertSame(54321, $refund->vendor_id);
        $this->assertSame(1, $cartItem->product_id);
        $this->assertSame(1, $cartItem->qty);
        $this->assertSame(1, $refund->order_id);
    }

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
                ->assertNotFound();

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
                ->assertNotFound();

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

    public function test_customer_cannot_fetch_another_customers_order_via_query_param(): void
    {
        $owner = $this->createCustomer('idor-api-order-owner');
        $otherCustomer = $this->createCustomer('idor-api-order-other');
        $orderId = $this->createOrder($owner->id);

        try {
            $this->actingAs($otherCustomer, 'sanctum')
                ->getJson('/api/user/get-all-user-orders?order_id='.$orderId)
                ->assertNotFound();
        } finally {
            DB::table('orders')->where('id', $orderId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_customer_cannot_message_another_customers_order(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $owner = $this->createCustomer('idor-msg-owner');
        $otherCustomer = $this->createCustomer('idor-msg-other');
        $orderId = $this->createOrder($owner->id);

        try {
            $this->actingAs($otherCustomer)
                ->post('/account/orders/'.$orderId.'/messages', ['message' => 'hi'])
                ->assertForbidden();

            $this->assertDatabaseMissing('order_messages', [
                'order_id' => $orderId,
                'customer_id' => $otherCustomer->id,
            ]);
        } finally {
            DB::table('order_messages')->where('order_id', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    public function test_customer_cannot_upload_a_receipt_to_another_customers_order(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $owner = $this->createCustomer('idor-receipt-owner');
        $otherCustomer = $this->createCustomer('idor-receipt-other');
        $orderId = $this->createOrder($owner->id);
        DB::table('orders')->where('id', $orderId)->update(['payment_method' => 'vodafone_cash']);

        try {
            $this->actingAs($otherCustomer)
                ->post('/account/orders/'.$orderId.'/payment-receipt', [
                    'receipt' => \Illuminate\Http\UploadedFile::fake()->create('r.jpg', 10, 'image/jpeg'),
                ])
                ->assertNotFound();

            $this->assertDatabaseMissing('payment_receipts', [
                'order_id' => $orderId,
            ]);
        } finally {
            DB::table('payment_receipts')->where('order_id', $orderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
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

    public function test_suborder_policy_is_scoped_to_the_authenticated_vendor(): void
    {
        $owner = $this->createVendor('policy-suborder-owner');
        $otherVendor = $this->createVendor('policy-suborder-other');
        $customer = $this->createCustomer('policy-suborder-customer');
        $orderId = $this->createOrder($customer->id);
        $subOrderId = DB::table('order_sub_orders')->insertGetId([
            'parent_order_id' => $orderId,
            'vendor_id' => $owner->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
            'line_items' => json_encode([]),
            'subtotal' => 0,
            'discount_total' => 0,
            'total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $subOrder = SubOrder::findOrFail($subOrderId);
            $policy = app(SubOrderPolicy::class);

            $this->assertTrue($policy->view($owner, $subOrder));
            $this->assertTrue($policy->update($owner, $subOrder));
            $this->assertTrue($policy->reviewPayment($owner, $subOrder));
            $this->assertFalse($policy->view($otherVendor, $subOrder));
            $this->assertFalse($policy->update($otherVendor, $subOrder));
            $this->assertFalse($policy->reviewPayment($otherVendor, $subOrder));
        } finally {
            DB::table('order_sub_orders')->where('id', $subOrderId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $customer->delete();
            $otherVendor->delete();
            $owner->delete();
        }
    }

    public function test_vendor_refund_policy_is_scoped_to_the_assigned_vendor(): void
    {
        $owner = $this->createVendor('policy-refund-owner');
        $otherVendor = $this->createVendor('policy-refund-other');
        $customer = $this->createCustomer('policy-refund-customer');
        $orderId = $this->createOrder($customer->id);
        $refundId = DB::table('refund_requests')->insertGetId([
            'order_id' => $orderId,
            'customer_id' => $customer->id,
            'vendor_id' => $owner->id,
            'type' => 'refund',
            'reason' => 'damaged',
            'description' => 'Policy fixture',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $refund = RefundRequest::findOrFail($refundId);
            $policy = app(RefundRequestPolicy::class);

            $this->assertTrue($policy->manageAsVendor($owner, $refund));
            $this->assertFalse($policy->manageAsVendor($otherVendor, $refund));
        } finally {
            DB::table('refund_requests')->where('id', $refundId)->delete();
            DB::table('orders')->where('id', $orderId)->delete();
            $customer->delete();
            $otherVendor->delete();
            $owner->delete();
        }
    }

    public function test_product_review_policy_is_scoped_to_the_reviewer_or_admin(): void
    {
        $owner = $this->createCustomer('policy-review-owner');
        $otherCustomer = $this->createCustomer('policy-review-other');
        $reviewId = DB::table('product_reviews')->insertGetId([
            'product_id' => 999999,
            'user_id' => $owner->id,
            'rating' => 5,
            'title' => 'Policy fixture',
            'body' => 'Review policy fixture body',
            'approved' => true,
            'is_verified_purchase' => false,
            'helpful_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $review = ProductReview::findOrFail($reviewId);
            $policy = app(ProductReviewPolicy::class);

            $this->assertTrue($policy->delete($owner, $review));
            $this->assertFalse($policy->delete($otherCustomer, $review));
        } finally {
            DB::table('product_reviews')->where('id', $reviewId)->delete();
            $otherCustomer->delete();
            $owner->delete();
        }
    }

    private function createCoupon(?int $vendorId, string $codePrefix): int
    {
        return DB::table('coupons')->insertGetId([
            'code' => $codePrefix.'-'.uniqid(),
            'vendor_id' => $vendorId,
            'amount' => 10,
            'status' => 'draft',
            'discount_type' => 'percent',
            'usage_count' => 0,
        ]);
    }

    private function createVendor(string $prefix): VendorUser
    {
        $vendor = new VendorUser;
        $vendor->forceFill([
            'first_name' => 'IDOR',
            'last_name' => 'Vendor',
            'phone' => sprintf('01%09d', abs(crc32($prefix)) % 1000000000),
            'email' => $prefix . '-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
            'shop_name' => 'IDOR Test Shop ' . $prefix,
            'shop_address' => 'Test address',
            'status' => 'approved',
            'auth_token' => 'idor-test-token-' . uniqid(),
            'holder_name' => 'Test Holder',
            'bank_name' => 'Test Bank',
            'branch' => 'Test Branch',
        ]);
        $vendor->save();

        return $vendor;
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

<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicLookupProtectionTest extends TestCase
{
    public function test_order_tracking_returns_the_same_generic_error_for_unknown_and_mismatched_orders(): void
    {
        $orderId = null;
        $genericError = 'We could not find an order with those details. Please check them and try again.';

        try {
            $csrfToken = 'tracking-lookup-csrf';
            $unknownResponse = $this->withSession(['_token' => $csrfToken])->post(route('order.track.submit'), [
                '_token' => $csrfToken,
                'order_id' => 999999999,
                'phone' => '01000000001',
            ]);

            $unknownResponse->assertOk()
                ->assertSee($genericError)
                ->assertDontSee('was not found')
                ->assertDontSee('does not match our records');

            $orderId = DB::table('orders')->insertGetId([
                'status' => 'pending',
                'billing' => json_encode(['phone' => '01000000001', 'email' => 'lookup-owner@ramostore.local']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $mismatchResponse = $this->withSession(['_token' => $csrfToken])->post(route('order.track.submit'), [
                '_token' => $csrfToken,
                'order_id' => $orderId,
                'phone' => '01000000002',
            ]);

            $mismatchResponse->assertOk()
                ->assertSee($genericError)
                ->assertDontSee('was not found')
                ->assertDontSee('does not match our records');
        } finally {
            if ($orderId) {
                DB::table('orders')->where('id', $orderId)->delete();
            }
        }
    }

    public function test_guest_lookup_uses_the_same_generic_error_for_unknown_and_mismatched_orders(): void
    {
        $orderId = null;
        $genericError = 'We could not find an order with those details. Please check them and try again.';

        try {
            $csrfToken = 'guest-lookup-csrf';
            $this->withHeader('Referer', route('guest.order'))->withSession(['_token' => $csrfToken])->post(route('guest.order.lookup'), [
                '_token' => $csrfToken,
                'order_id' => 999999998,
                'email' => 'lookup-owner@ramostore.local',
            ])->assertRedirect(route('guest.order'))
                ->assertSessionHas('error', $genericError);

            $orderId = DB::table('orders')->insertGetId([
                'status' => 'pending',
                'billing' => json_encode(['phone' => '01000000001', 'email' => 'lookup-owner@ramostore.local']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->withHeader('Referer', route('guest.order'))->withSession(['_token' => $csrfToken])->post(route('guest.order.lookup'), [
                '_token' => $csrfToken,
                'order_id' => $orderId,
                'email' => 'another-customer@ramostore.local',
            ])->assertRedirect(route('guest.order'))
                ->assertSessionHas('error', $genericError);
        } finally {
            if ($orderId) {
                DB::table('orders')->where('id', $orderId)->delete();
            }
        }
    }

    public function test_successful_tracking_lookup_keeps_the_submitted_phone_visible(): void
    {
        $orderId = null;

        try {
            $orderId = DB::table('orders')->insertGetId([
                'status' => 'pending',
                'billing' => json_encode(['phone' => '01000000003']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $csrfToken = 'tracking-success-csrf';
            $response = $this->withSession(['_token' => $csrfToken])->post(route('order.track.submit'), [
                '_token' => $csrfToken,
                'order_id' => $orderId,
                'phone' => '01000000003',
            ]);

            $response->assertOk()
                ->assertSee('Order #' . $orderId)
                ->assertSee('value="01000000003"', false);
        } finally {
            if ($orderId) {
                DB::table('orders')->where('id', $orderId)->delete();
            }
        }
    }

    public function test_public_route_limiters_have_bounded_thresholds(): void
    {
        $request = Request::create('/search', 'GET', server: ['REMOTE_ADDR' => '198.51.100.42']);

        $expectedMaxAttempts = [
            'login-web' => 5,
            'cart-mutation' => 40,
            'coupon-check' => 10,
            'checkout-place' => 6,
            'search' => 90,
            'order-lookup' => 6,
        ];

        foreach ($expectedMaxAttempts as $name => $maxAttempts) {
            $limiter = RateLimiter::limiter($name);
            $this->assertNotNull($limiter, "The {$name} limiter must be registered.");

            $limit = $limiter($request);
            $this->assertSame($maxAttempts, $limit->maxAttempts);
        }
    }
}

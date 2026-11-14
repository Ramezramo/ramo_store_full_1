<?php

namespace Tests\Feature;

use App\Jobs\ClawBackReferralCommission;
use App\Jobs\ProcessReferralCommission;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\ReferralCommissionAudit;
use App\Models\User;
use App\Models\VendorUser;
use App\Services\ReferralSettingsService;
use App\Services\VendorShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\UsesIsolatedSecurityDatabase;
use Tests\TestCase;

class ReferralLifecycleSecurityTest extends TestCase
{
    use UsesIsolatedSecurityDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(ReferralSettingsService::class)->save([
            'referral_enabled' => true, 'referral_min_order_amount' => 700,
            'referral_commission_type' => 'percentage', 'referral_commission_value' => 10,
        ]);
    }

    private function customer(bool $verified = true): User
    {
        $user = User::create(['name' => 'Referral fixture', 'email' => Str::uuid().'@example.test',
            'phone' => '+2010'.random_int(10000000, 99999999), 'password' => 'test-password', 'shipping' => '{}']);
        if ($verified) $user->forceFill(['email_verified_at' => now()])->save();
        return $user;
    }

    private function vendor(): VendorUser
    {
        $vendor = new VendorUser;
        $vendor->forceFill([
            'first_name' => 'Fixture', 'last_name' => 'Vendor', 'phone' => '+2010'.random_int(10000000, 99999999),
            'email' => Str::uuid().'@example.test', 'password' => 'test-password', 'status' => 'approved',
            'shop_name' => 'Fixture shop', 'shop_address' => 'Test', 'auth_token' => 'fixture',
            'holder_name' => 'Test', 'bank_name' => 'Test', 'branch' => 'Test',
        ])->save();
        return $vendor;
    }

    private function order(bool $verified = true, array $changes = []): array
    {
        $referrer = $this->customer(); $customer = $this->customer($verified);
        $referral = Referral::create(['referrer_id' => $referrer->id, 'referred_id' => $customer->id, 'status' => 'pending']);
        $order = new Order;
        $order->forceFill(array_replace(['customer_id' => $customer->id, 'status' => 'completed',
            'platform_payment_verified_at' => now(), 'platform_payment_verified_by' => 1,
            'payment_status' => 'confirmed', 'currency' => 'EGP', 'final_total' => 1000, 'timeline' => []], $changes))->save();
        return [$order, $referral, $customer, $referrer];
    }

    private function process(Order $order): ?ReferralCommission
    {
        (new ProcessReferralCommission($order->id))->handle(app(ReferralSettingsService::class));
        return ReferralCommission::where('order_id', $order->id)->first();
    }

    private function shipment(Order $order, VendorUser $vendor, string $status = 'pending'): int
    {
        return DB::table('order_sub_orders')->insertGetId([
            'parent_order_id' => $order->id, 'vendor_id' => $vendor->id, 'customer_id' => $order->customer_id,
            'status' => $status, 'vendor_status' => $status, 'total' => 500, 'timeline' => '[]',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function refund(Order $order, string $status = 'approved', ?int $vendorId = null): int
    {
        return DB::table('refund_requests')->insertGetId([
            'order_id' => $order->id, 'customer_id' => $order->customer_id, 'vendor_id' => $vendorId,
            'type' => 'refund', 'reason' => 'damaged', 'status' => $status, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function updateRefund(int $id, string $status): void
    {
        app(\App\Http\Controllers\Admin\AdminDashboardController::class)->updateRefund(
            Request::create('/admin/refunds/'.$id, 'PATCH', ['status' => $status, 'admin_note' => 'Reviewed']), $id
        );
    }

    public function test_an_unpaid_completed_order_cannot_generate_a_commission(): void
    {
        [$order] = $this->order(changes: ['payment_status' => 'pending_payment']);
        $this->assertNull($this->process($order));
        $this->assertSame(0, ReferralCommissionAudit::count());
    }

    public function test_cod_delivery_needs_platform_confirmation_of_collected_cash(): void
    {
        [$order] = $this->order(changes: ['payment_method' => 'cod', 'platform_payment_verified_at' => null, 'platform_payment_verified_by' => null]);
        $this->assertNull($this->process($order));
        $admin = $this->customer(); $admin->role = '["admin"]'; $admin->save();
        $this->actingAs($admin)->post(route('admin.orders.payment-collected', $order->id), [
            'payment_received' => 1, 'reference' => 'COD settlement fixture',
        ])->assertRedirect();
        $this->assertSame($admin->id, (int) $order->fresh()->platform_payment_verified_by);
        $this->assertNotNull($order->fresh()->date_paid);
        $this->assertSame('100.00', $this->process($order)->amount);
    }

    public function test_a_vendor_or_customer_cannot_certify_platform_payment(): void
    {
        [$order] = $this->order(changes: ['platform_payment_verified_at' => null, 'platform_payment_verified_by' => null]);
        $this->actingAs($this->customer())->post(route('admin.orders.payment-collected', $order->id), [
            'payment_received' => 1, 'reference' => 'Forged collection',
        ])->assertForbidden();
        $this->assertNull($order->fresh()->platform_payment_verified_at);
        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_vendor_api_cannot_complete_an_unpaid_parent_or_process_an_unpaid_shipment(): void
    {
        [$order] = $this->order(changes: ['status' => 'pending', 'payment_status' => 'pending_payment']);
        $vendor = $this->vendor(); $this->shipment($order, $vendor);
        $this->actingAs($vendor, 'sanctum')->postJson('/api/vendor/update-order-state', ['order_id' => $order->id, 'status' => 'completed'])->assertUnprocessable();
        $this->postJson('/api/vendor/update-order-state', ['order_id' => $order->id, 'status' => 'processing'])->assertUnprocessable();
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_vendor_updates_only_its_own_shipment_and_parent_waits_for_other_vendors(): void
    {
        [$order] = $this->order(changes: ['status' => 'shipped']);
        $first = $this->vendor(); $second = $this->vendor();
        $firstId = $this->shipment($order, $first, 'shipped'); $otherId = $this->shipment($order, $second);
        $this->actingAs($first, 'sanctum')->postJson('/api/vendor/update-order-state', ['order_id' => $order->id, 'status' => 'completed'])->assertOk();
        $this->assertDatabaseHas('order_sub_orders', ['id' => $firstId, 'vendor_status' => 'delivered']);
        $this->assertDatabaseHas('order_sub_orders', ['id' => $otherId, 'vendor_status' => 'pending']);
        $this->assertSame('partially_delivered', $order->fresh()->status);
        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_normal_paid_delivery_creates_one_audited_commission(): void
    {
        [$order] = $this->order(changes: ['status' => 'pending']);
        $vendor = $this->vendor(); $id = $this->shipment($order, $vendor);
        foreach (['processing', 'shipped', 'delivered'] as $status) {
            app(VendorShipmentService::class)->update($vendor, $id, $status);
        }
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->date_completed);
        $commission = $this->process($order);
        $this->assertSame('100.00', $commission->amount);
        $this->assertSame(1, ReferralCommission::where('order_id', $order->id)->count());
        $this->assertSame(1, ReferralCommissionAudit::where('action', 'created')->count());
    }

    public function test_cancelled_order_is_reloaded_inside_the_creation_transaction(): void
    {
        [$order] = $this->order(); $job = new ProcessReferralCommission($order->id);
        $order->status = 'cancelled'; $order->save();
        (new ClawBackReferralCommission($order->id))->handle();
        $job->handle(app(ReferralSettingsService::class));
        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_full_refund_and_reversal_commit_together_and_are_idempotent(): void
    {
        [$order] = $this->order(); $commission = $this->process($order);
        $commission->update(['status' => 'approved', 'approved_at' => now()]);
        $admin = $this->customer(); $admin->role = '["admin"]'; $admin->save(); $this->actingAs($admin);
        $id = $this->refund($order); $this->updateRefund($id, 'completed'); $this->updateRefund($id, 'completed');
        $this->assertSame('refunded', $order->fresh()->payment_status);
        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertSame('clawed_back', $commission->fresh()->status);
        $this->assertNull($commission->fresh()->approved_at);
        $audit = ReferralCommissionAudit::where('action', 'clawed_back')->sole();
        $this->assertSame($admin->id, (int) $audit->actor_id);
        $this->assertSame('approved', $audit->old_status);
    }

    public function test_partial_vendor_refund_never_retains_the_full_parent_reward(): void
    {
        [$order] = $this->order(); $first = $this->vendor(); $second = $this->vendor();
        $firstId = $this->shipment($order, $first, 'delivered'); $this->shipment($order, $second, 'delivered');
        $commission = $this->process($order); $commission->update(['status' => 'approved']);
        $this->updateRefund($this->refund($order, vendorId: $first->id), 'completed');
        $this->assertDatabaseHas('order_sub_orders', ['id' => $firstId, 'vendor_status' => 'returned']);
        $this->assertSame('partially_cancelled', $order->fresh()->status);
        $this->assertSame('confirmed', $order->fresh()->payment_status);
        $this->assertSame('clawed_back', $commission->fresh()->status);
    }

    public function test_rejected_refund_can_resume_qualification_without_permanent_clawback(): void
    {
        [$order] = $this->order(); $refund = $this->refund($order, 'pending');
        $this->assertNull($this->process($order));
        $this->updateRefund($refund, 'rejected');
        $this->assertSame('pending', ReferralCommission::where('order_id', $order->id)->sole()->status);
    }

    public function test_a_return_voids_a_previously_approved_reward(): void
    {
        [$order] = $this->order(); $vendor = $this->vendor(); $id = $this->shipment($order, $vendor, 'delivered');
        $commission = $this->process($order); $commission->update(['status' => 'approved']);
        app(VendorShipmentService::class)->update($vendor, $id, 'returned');
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('clawed_back', $commission->fresh()->status);
        $this->assertSame(1, ReferralCommissionAudit::where('action', 'clawed_back')->count());
    }

    public function test_first_order_reward_survives_reverse_queue_execution(): void
    {
        [$first, $referral, $customer] = $this->order();
        $referral->created_at = now()->subMinutes(10); $referral->save();
        $first->date_completed = now()->subMinutes(2); $first->save();
        $second = new Order; $second->forceFill(['customer_id' => $customer->id, 'status' => 'completed',
            'platform_payment_verified_at' => now(), 'platform_payment_verified_by' => 1,
            'payment_status' => 'confirmed', 'final_total' => 1500, 'date_completed' => now()->subMinute()])->save();
        $this->assertNull($this->process($second));
        $this->assertSame('100.00', $this->process($first)->amount);
        $this->assertNull($this->process($second));
        $this->assertSame(1, ReferralCommission::count());
    }

    public function test_expiration_is_enforced_even_without_a_scheduler_run(): void
    {
        [$order, $referral] = $this->order();
        $referral->created_at = now()->subDays(91); $referral->save();
        $this->assertNull($this->process($order));
        $this->assertSame('expired', $referral->fresh()->status);
    }

    public function test_unverified_identity_cannot_earn_until_verification_is_completed(): void
    {
        [$order, $referral, $customer] = $this->order(verified: false);
        $this->assertNull($this->process($order));
        $this->assertSame('pending', $referral->fresh()->status);
        $customer->forceFill(['email_verified_at' => now()])->save();
        $this->assertSame(1, ReferralCommission::where('order_id', $order->id)->count());
        $this->assertSame('100.00', $this->process($order)->amount);
    }

    public function test_late_job_cannot_expire_an_on_time_first_completion(): void
    {
        [$first, $referral, $customer] = $this->order();
        $referral->created_at = now()->subDays(91); $referral->save();
        $first->created_at = now()->subDays(3); $first->date_completed = now()->subDays(2); $first->save();
        $late = new Order; $late->forceFill(['customer_id' => $customer->id, 'status' => 'completed',
            'payment_status' => 'confirmed', 'final_total' => 1500, 'date_completed' => now(),
            'platform_payment_verified_at' => now(), 'platform_payment_verified_by' => 1])->save();
        $this->assertNull($this->process($late));
        $this->assertSame('pending', $referral->fresh()->status);
        $this->assertSame('100.00', $this->process($first)->amount);
    }

    public function test_all_orders_mode_catches_up_after_a_late_job_arrives_first(): void
    {
        app(ReferralSettingsService::class)->save(['referral_commission_scope' => 'all_orders']);
        [$first, $referral, $customer] = $this->order();
        $referral->created_at = now()->subDays(91); $referral->save();
        $first->created_at = now()->subDays(3); $first->date_completed = now()->subDays(2); $first->save();
        $late = new Order; $late->forceFill(['customer_id' => $customer->id, 'status' => 'completed',
            'payment_status' => 'confirmed', 'final_total' => 1500, 'date_completed' => now(),
            'platform_payment_verified_at' => now(), 'platform_payment_verified_by' => 1])->save();
        $this->process($late);
        $this->assertSame('qualified', $referral->fresh()->status);
        $this->assertSame(2, ReferralCommission::where('referral_id', $referral->id)->count());
    }

    public function test_system_expiry_can_recover_only_a_real_on_time_completion(): void
    {
        [$order, $referral] = $this->order();
        $referral->created_at = now()->subDays(91); $referral->status = 'expired';
        $referral->rejection_reason = 'expired_no_qualifying_order'; $referral->save();
        $order->created_at = now()->subDays(3); $order->date_completed = now()->subDays(2); $order->save();
        $this->assertSame('100.00', $this->process($order)->amount);
        $this->assertSame('qualified', $referral->fresh()->status);
        $this->assertTrue(ReferralCommissionAudit::where('action', 'created')->sole()->metadata['recovered_scheduled_expiry']);
    }

    public function test_sweeper_preserves_an_on_time_order_waiting_for_identity_proof(): void
    {
        [$order, $referral, $customer] = $this->order(verified: false);
        $referral->created_at = now()->subDays(91); $referral->save();
        $order->created_at = now()->subDays(3); $order->date_completed = now()->subDays(2); $order->save();
        $this->artisan('referrals:expire-stale')->assertSuccessful();
        $this->assertSame('pending', $referral->fresh()->status);
        $customer->forceFill(['is_phone_verified' => true])->save();
        $this->assertSame('qualified', $referral->fresh()->status);
        $this->assertSame(1, ReferralCommission::where('order_id', $order->id)->count());
    }

    public function test_manual_parent_override_cannot_bypass_undelivered_shipment_checks(): void
    {
        [$order] = $this->order(changes: ['general_order_status_override' => 'completed']);
        $this->shipment($order, $this->vendor(), 'pending');
        $this->assertNull($this->process($order));
    }

    public function test_already_paid_reversal_records_manual_recovery_requirement(): void
    {
        [$order] = $this->order(); $commission = $this->process($order);
        $commission->update(['status' => 'paid', 'paid_at' => now()]);
        $order->status = 'cancelled'; $order->save();
        (new ClawBackReferralCommission($order->id))->handle();
        $this->assertSame('clawed_back', $commission->fresh()->status);
        $this->assertStringContainsString('manual_recovery_required', $commission->fresh()->clawback_reason);
        $this->assertTrue(ReferralCommissionAudit::where('action', 'clawed_back')->sole()->metadata['manual_recovery_required']);
    }
}

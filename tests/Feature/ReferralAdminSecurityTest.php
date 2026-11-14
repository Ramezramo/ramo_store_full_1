<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ReferralAdminController;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\ReferralCommissionAudit;
use App\Models\User;
use App\Services\ReferralSettingsService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Support\UsesIsolatedSecurityDatabase;

/** These tests never connect to, migrate, or seed the application's database. */
class ReferralAdminSecurityTest extends TestCase
{
    use UsesIsolatedSecurityDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = $this->user();
        $admin->forceFill(['role' => json_encode(['admin'])])->save();
        $this->actingAs($admin, 'web')->withSession(['_token' => 'isolated-referral-test']);
        app(ReferralSettingsService::class)->save([
            'referral_enabled' => true, 'referral_commission_type' => 'percentage',
            'referral_commission_value' => 10, 'referral_min_order_amount' => 700,
        ]);
    }

    private function user(): User
    {
        $user = User::create([
            'name' => 'Isolated referral admin fixture', 'email' => Str::uuid().'@example.test',
            'phone' => '010'.random_int(10000000, 99999999), 'password' => 'test-password-123',
            'shipping' => '{}',
        ]);
        $user->forceFill(['is_phone_verified' => true])->save();
        return $user->fresh();
    }

    private function fixture(): array
    {
        $referrer = $this->user();
        $customer = $this->user();
        $customer->forceFill(['referred_by' => $referrer->id])->save();
        $order = new Order;
        $order->forceFill([
            'customer_id' => $customer->id, 'status' => 'completed', 'payment_status' => 'confirmed',
            'final_total' => 1000, 'shipping' => [], 'timeline' => [],
            'platform_payment_verified_at' => now(), 'platform_payment_verified_by' => auth()->id(),
        ])->save();
        DB::table('order_sub_orders')->insert([
            'parent_order_id' => $order->id, 'status' => 'completed', 'vendor_status' => 'delivered',
        ]);
        $referral = Referral::create([
            'referrer_id' => $referrer->id, 'referred_id' => $customer->id,
            'status' => 'qualified', 'qualifying_order_id' => $order->id,
        ]);
        $commission = ReferralCommission::create([
            'referral_id' => $referral->id, 'order_id' => $order->id,
            'amount' => 100, 'status' => 'pending',
        ]);
        return [$commission, $referral, $order, $customer];
    }

    public function test_stale_approval_cannot_resurrect_a_clawed_back_commission(): void
    {
        [$stale, , $order] = $this->fixture();
        $order->forceFill(['status' => 'refunded'])->save();
        DB::table('referral_commissions')->where('id', $stale->id)->update([
            'status' => 'clawed_back', 'clawed_back_at' => now(),
        ]);
        app(ReferralAdminController::class)->approveCommission($stale);
        $this->assertSame('clawed_back', $stale->fresh()->status);
        $this->assertNull($stale->fresh()->approved_at);
        $this->assertSame(0, ReferralCommissionAudit::count());
    }

    public function test_stale_amount_edit_and_rejection_cannot_modify_terminal_states(): void
    {
        [$stale] = $this->fixture();
        DB::table('referral_commissions')->where('id', $stale->id)->update(['status' => 'paid']);
        app(ReferralAdminController::class)->updateCommission(Request::create('/', 'PATCH', ['amount' => 999]), $stale);
        app(ReferralAdminController::class)->rejectCommission($stale);
        $this->assertSame('paid', $stale->fresh()->status);
        $this->assertSame('100.00', $stale->fresh()->amount);
        $this->assertSame(0, ReferralCommissionAudit::count());
    }

    public function test_approval_rechecks_current_payment_referral_identity_fulfillment_and_refunds(): void
    {
        foreach (['unpaid', 'rejected', 'unverified', 'undelivered', 'refund_pending', 'refund_completed'] as $case) {
            [$commission, $referral, $order, $customer] = $this->fixture();
            match ($case) {
                'unpaid' => $order->forceFill(['payment_status' => 'pending_payment'])->save(),
                'rejected' => $referral->update(['status' => 'rejected']),
                'unverified' => $customer->forceFill(['is_phone_verified' => false, 'email_verified_at' => null])->save(),
                'undelivered' => DB::table('order_sub_orders')->where('parent_order_id', $order->id)->update(['vendor_status' => 'processing']),
                'refund_pending' => DB::table('refund_requests')->insert(['order_id' => $order->id, 'customer_id' => $customer->id, 'reason' => 'fixture', 'status' => 'approved']),
                'refund_completed' => DB::table('refund_requests')->insert(['order_id' => $order->id, 'customer_id' => $customer->id, 'reason' => 'fixture', 'status' => 'completed']),
            };
            app(ReferralAdminController::class)->approveCommission($commission);
            $this->assertSame('pending', $commission->fresh()->status, $case);
        }
        $this->assertSame(0, ReferralCommissionAudit::count());
    }

    public function test_valid_approval_and_amount_change_record_actor_and_before_after_values(): void
    {
        [$commission] = $this->fixture();
        $controller = app(ReferralAdminController::class);
        $controller->updateCommission(Request::create('/', 'PATCH', ['amount' => 125.75]), $commission);
        $controller->approveCommission($commission);
        $controller->approveCommission($commission); // An old model cannot approve twice.
        $this->assertSame('approved', $commission->fresh()->status);
        $this->assertSame('125.75', $commission->fresh()->amount);
        $events = ReferralCommissionAudit::orderBy('id')->get();
        $this->assertCount(2, $events);
        $this->assertSame('amount_changed', $events[0]->action);
        $this->assertSame('100.00', $events[0]->old_amount);
        $this->assertSame('125.75', $events[0]->new_amount);
        $this->assertSame('approved', $events[1]->action);
        $this->assertSame('pending', $events[1]->old_status);
        $this->assertSame('approved', $events[1]->new_status);
        $this->assertSame(auth()->id(), (int) $events[1]->actor_id);
    }

    public function test_rejection_has_an_audit_record_and_cannot_be_reapproved(): void
    {
        [$commission] = $this->fixture();
        $controller = app(ReferralAdminController::class);
        $controller->rejectCommission($commission);
        $controller->approveCommission($commission);
        $this->assertSame('rejected', $commission->fresh()->status);
        $this->assertSame('admin_rejected', ReferralCommissionAudit::sole()->reason);
    }

    public function test_audit_events_reject_raw_database_changes_and_deletes(): void
    {
        [$commission] = $this->fixture();
        app(ReferralAdminController::class)->approveCommission($commission);
        $event = ReferralCommissionAudit::sole();
        foreach (['update', 'delete'] as $operation) {
            try {
                $query = DB::table('referral_commission_audits')->where('id', $event->id);
                $operation === 'update' ? $query->update(['new_amount' => 1]) : $query->delete();
                $this->fail('Financial audit history was mutable.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('immutable', $e->getMessage());
            }
        }
        $this->assertSame('100.00', $event->fresh()->new_amount);
    }

    public function test_approval_rolls_back_if_its_audit_record_cannot_be_written(): void
    {
        [$commission] = $this->fixture();
        Schema::drop('referral_commission_audits');
        try {
            app(ReferralAdminController::class)->approveCommission($commission);
            $this->fail('Approval succeeded without its financial audit record.');
        } catch (QueryException) {
            $this->assertSame('pending', $commission->fresh()->status);
            $this->assertNull($commission->fresh()->approved_at);
        }
    }

    public function test_referral_link_cannot_be_edited_or_cancelled_after_a_commission_appears(): void
    {
        [$commission, $referral] = $this->fixture();
        $referral->setRelation('commission', null); // Simulate old route-bound relation data.
        $controller = app(ReferralAdminController::class);
        $controller->destroy($referral);
        $controller->update(Request::create('/', 'PATCH', ['referrer_id' => $referral->referrer_id, 'status' => 'rejected']), $referral);
        $this->assertSame('qualified', $referral->fresh()->status);
        $this->assertSame('pending', $commission->fresh()->status);
    }

    public function test_generic_settings_editor_uses_shared_validation_and_immediate_cache_invalidation(): void
    {
        $settings = app(ReferralSettingsService::class);
        $this->assertSame(10.0, $settings->commissionValue());
        $id = DB::table('app_configs')->where('config_key', 'referral_settings')->value('id');
        $this->putJson('/admin/configs/'.$id, ['value' => json_encode(['referral_commission_value' => 20]), 'is_public' => true])->assertOk();
        $this->assertSame(20.0, $settings->commissionValue());
        $this->assertFalse((bool) DB::table('app_configs')->where('id', $id)->value('is_public'));
        $this->putJson('/admin/configs/'.$id, ['value' => json_encode(['referral_commission_value' => 101])])->assertUnprocessable();
        $this->assertSame(20.0, $settings->commissionValue());
    }

    public function test_generic_settings_editor_rejects_nonobjects_unknown_fields_and_localized_overrides(): void
    {
        $id = DB::table('app_configs')->where('config_key', 'referral_settings')->value('id');
        $this->putJson('/admin/configs/'.$id, ['value' => '[]'])->assertUnprocessable();
        $this->putJson('/admin/configs/'.$id, ['value' => '{"unexpected_setting":true}'])->assertUnprocessable();
        $this->postJson('/admin/configs', ['config_key' => 'referral_settings', 'config_group' => 'referral', 'lang' => 'ar', 'value' => '{}'])->assertUnprocessable();
        $this->deleteJson('/admin/configs/'.$id)->assertOk();
        $this->assertFalse(app(ReferralSettingsService::class)->isEnabled());
    }

    public function test_customer_cannot_approve_a_commission_or_change_referral_settings(): void
    {
        [$commission] = $this->fixture();
        $this->actingAs($this->user(), 'web');
        $this->patchJson('/admin/referral/commissions/'.$commission->id.'/approve')->assertForbidden();
        $this->putJson('/admin/referral/settings', [])->assertForbidden();
        $this->assertSame('pending', $commission->fresh()->status);
    }
}

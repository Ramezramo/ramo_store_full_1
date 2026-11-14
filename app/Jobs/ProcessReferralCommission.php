<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\ReferralCommissionAudit;
use App\Services\ReferralEligibility;
use App\Services\ReferralFraudChecker;
use App\Services\ReferralSettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessReferralCommission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(ReferralSettingsService $settings, ?ReferralFraudChecker $fraudChecker = null): void
    {
        if (! $settings->isEnabled()) return;

        DB::transaction(function () use ($settings): void {
            // All creation, approval, refund and reversal paths lock the order first.
            // Never use an order snapshot loaded before acquiring this lock.
            $order = Order::whereKey($this->orderId)->lockForUpdate()->first();
            $eligibility = app(ReferralEligibility::class);
            if (! $order || ! $settings->isEnabled() || $eligibility->orderIssue($order)) return;
            if ((float) $order->final_total < $settings->minOrderAmount()) return;

            $referral = Referral::where('referred_id', $order->customer_id)->lockForUpdate()->first();
            if (! $referral) return;
            $scheduledExpiry = $referral->status === 'expired' && $referral->rejection_reason === 'expired_no_qualifying_order';
            if (! in_array($referral->status, ['pending', 'qualified'], true) && ! $scheduledExpiry) return;
            if (! $settings->isAllOrders() && $referral->status !== 'pending' && ! $scheduledExpiry) return;

            $completedAt = $order->date_completed ?? $order->created_at;
            $deadline = $referral->created_at->copy()->addDays($settings->expiryDays());
            if (! $settings->isAllOrders()) {
                // A queue backlog can leave several completed orders waiting. Only the
                // actual first paid completion wins, irrespective of job execution order.
                $firstOrderId = Order::where('customer_id', $order->customer_id)
                    ->where('status', 'completed')->where('payment_status', 'confirmed')
                    ->orderByRaw('COALESCE(date_completed, created_at) ASC')->orderBy('id')->value('id');
                if ((int) $firstOrderId !== (int) $order->id) return;
            }

            if (! $referral->qualifying_order_id && (! $completedAt || $completedAt->gt($deadline))) {
                // A late job is not evidence that no on-time qualifying order exists.
                $onTime = Order::where('customer_id', $order->customer_id)->where('status', 'completed')
                    ->where('payment_status', 'confirmed')->where('final_total', '>=', $settings->minOrderAmount())
                    ->whereRaw('COALESCE(date_completed, created_at) >= ?', [$referral->created_at])
                    ->whereRaw('COALESCE(date_completed, created_at) <= ?', [$deadline])
                    ->orderByRaw('COALESCE(date_completed, created_at) ASC')->orderBy('id')->value('id');
                if ($onTime) {
                    if ($settings->isAllOrders()) ProcessReferralCommission::dispatch((int) $onTime)->afterCommit();
                } else {
                    $referral->update(['status' => 'expired', 'rejection_reason' => 'expired_no_qualifying_order']);
                }
                return;
            }
            if ($completedAt && $completedAt->lt($referral->created_at)) return;
            // Recover only automatic expiry when the real completion was on-time.
            // An administrator's rejected/expired decision is never overridden.
            if ($scheduledExpiry) $referral->status = 'pending';

            if ($issue = $eligibility->referralIssue($referral, $order)) {
                // Verification can be completed later. Identity/address fraud cannot
                // become payable merely because a registration was once accepted.
                if (! in_array($issue, ['identity_verification_required', 'account_unavailable'], true)) {
                    $referral->update(['status' => 'rejected', 'rejection_reason' => substr($issue, 0, 100)]);
                    $priorOrders = ReferralCommission::where('referral_id', $referral->id)->pluck('order_id');
                    foreach ($priorOrders as $priorOrder) {
                        ClawBackReferralCommission::dispatch((int) $priorOrder, 'referral_no_longer_eligible')->afterCommit();
                    }
                }
                return;
            }
            if (ReferralCommission::where('order_id', $order->id)->exists()) return;
            $amount = $settings->calculateCommission((float) $order->final_total);
            if ($amount <= 0) return;

            $commission = ReferralCommission::create([
                'referral_id' => $referral->id, 'order_id' => $order->id,
                'amount' => $amount, 'status' => 'pending',
            ]);
            $firstQualification = ! $referral->qualifying_order_id;
            $referral->update([
                'status' => 'qualified',
                'qualifying_order_id' => $referral->qualifying_order_id ?: $order->id,
                'rejection_reason' => $scheduledExpiry ? null : $referral->rejection_reason,
            ]);
            ReferralCommissionAudit::record($commission, 'created', metadata: [
                'commission_type' => $settings->commissionType(), 'commission_value' => $settings->commissionValue(),
                'commission_scope' => $settings->commissionScope(), 'order_total' => (float) $order->final_total,
                'recovered_scheduled_expiry' => $scheduledExpiry,
            ]);
            $timeline = is_array($order->timeline) ? $order->timeline : [];
            $timeline[] = [
                'timestamp' => now()->toIso8601String(), 'event' => 'referral_commission_created',
                'referral_id' => $referral->id, 'commission_id' => $commission->id,
                'amount' => $amount, 'status' => 'pending',
            ];
            $order->update(['timeline' => $timeline]);
            if ($firstQualification && $settings->isAllOrders()) {
                Order::where('customer_id', $order->customer_id)->where('status', 'completed')
                    ->where('payment_status', 'confirmed')->where('id', '!=', $order->id)
                    ->whereNotExists(function ($query): void {
                        $query->selectRaw('1')->from('referral_commissions')->whereColumn('referral_commissions.order_id', 'orders.id');
                    })->select('orders.id')->chunkById(100, function ($batch): void {
                        foreach ($batch as $candidate) ProcessReferralCommission::dispatch((int) $candidate->id)->afterCommit();
                    });
            }
        });
    }
}

<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\ReferralCommissionAudit;
use App\Services\ReferralEligibility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ClawBackReferralCommission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId, public string $reason = 'order_cancelled_or_refunded', public ?int $actorId = null) {}

    public function handle(): void
    {
        DB::transaction(function (): void {
            $order = Order::whereKey($this->orderId)->lockForUpdate()->first();
            if (! $order) return;
            $referral = Referral::where('referred_id', $order->customer_id)->lockForUpdate()->first();
            $commission = ReferralCommission::where('order_id', $order->id)->lockForUpdate()->first();
            if (! $commission || in_array($commission->status, ['clawed_back', 'rejected'], true)) return;

            $orderIssue = app(ReferralEligibility::class)->orderIssue($order);
            $referralRejected = ! $referral || in_array($referral->status, ['rejected', 'expired'], true);
            // An open refund puts approval on hold, but is not proof of a completed
            // refund. A rejected refund must not permanently destroy an earned reward.
            if (! $referralRejected && in_array($orderIssue, [null, 'refund_under_review', 'platform_payment_verification_required'], true)) return;

            $before = $commission->only(['status', 'amount']);
            $wasPaid = $commission->status === 'paid' || $commission->paid_at !== null;
            $commission->update([
                'status' => 'clawed_back', 'approved_at' => null, 'clawed_back_at' => now(),
                'clawback_reason' => substr($wasPaid ? $this->reason.'_manual_recovery_required' : $this->reason, 0, 100),
            ]);
            ReferralCommissionAudit::record($commission, 'clawed_back', $before, actorId: $this->actorId, reason: $commission->clawback_reason, metadata: [
                'eligibility_issue' => $referralRejected ? 'referral_ineligible' : $orderIssue,
                'manual_recovery_required' => $wasPaid,
            ]);
            $timeline = is_array($order->timeline) ? $order->timeline : [];
            $timeline[] = [
                'timestamp' => now()->toIso8601String(), 'event' => 'referral_commission_clawed_back',
                'commission_id' => $commission->id, 'amount' => (float) $commission->amount,
                'status' => 'clawed_back', 'manual_recovery_required' => $wasPaid,
                'reason' => $commission->clawback_reason,
            ];
            $order->update(['timeline' => $timeline]);
        });
    }
}

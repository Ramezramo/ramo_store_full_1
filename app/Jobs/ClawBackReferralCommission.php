<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ReferralCommission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ClawBackReferralCommission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId, public string $reason = 'order_cancelled_or_refunded')
    {
    }

    public function handle(): void
    {
        $order = Order::find($this->orderId);
        if (! $order || ! in_array($order->status, ['refunded', 'cancelled'], true)) {
            return;
        }

        DB::transaction(function () use ($order): void {
            $commission = ReferralCommission::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();
            if (! $commission || $commission->status === 'clawed_back') {
                return;
            }

            $wasPaid = $commission->status === 'paid';
            $commission->update([
                'status' => 'clawed_back',
                'clawed_back_at' => now(),
                'clawback_reason' => $wasPaid
                    ? $this->reason.'_manual_recovery_required'
                    : $this->reason,
            ]);

            $freshOrder = $order->fresh();
            $timeline = is_array($freshOrder?->timeline) ? $freshOrder->timeline : [];
            $timeline[] = [
                'timestamp' => now()->toIso8601String(),
                'event' => 'referral_commission_clawed_back',
                'commission_id' => $commission->id,
                'amount' => (float) $commission->amount,
                'status' => 'clawed_back',
                'manual_recovery_required' => $wasPaid,
                'reason' => $commission->clawback_reason,
            ];
            $freshOrder?->update(['timeline' => $timeline]);
        });
    }
}

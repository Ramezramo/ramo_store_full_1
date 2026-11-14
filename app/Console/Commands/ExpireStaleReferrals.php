<?php

namespace App\Console\Commands;

use App\Jobs\ProcessReferralCommission;
use App\Models\Order;
use App\Models\Referral;
use App\Services\ReferralEligibility;
use App\Services\ReferralSettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExpireStaleReferrals extends Command
{
    protected $signature = 'referrals:expire-stale
                            {--days= : Override the age cutoff for this run}';
    protected $description = 'Reconcile on-time completed orders before expiring stale pending referrals';

    public function handle(ReferralSettingsService $settings): int
    {
        $days = $this->option('days') !== null ? (int) $this->option('days') : $settings->expiryDays();
        if ($days < 1 || $days > 3650) {
            $this->error('The expiration window must be between 1 and 3650 days.');
            return self::FAILURE;
        }
        $expired = 0;
        Referral::where('created_at', '<=', now()->subDays($days))
            ->where(function ($query): void {
                $query->where('status', 'pending')->orWhere(function ($systemExpired): void {
                    $systemExpired->where('status', 'expired')->where('rejection_reason', 'expired_no_qualifying_order');
                });
            })->orderBy('id')->chunkById(100, function ($batch) use ($settings, &$expired): void {
                foreach ($batch as $candidate) {
                    // Reconcile before taking the referral lock: the award job must
                    // always acquire order -> referral, never the reverse.
                    $orders = Order::where('customer_id', $candidate->referred_id)->get();
                    foreach ($this->onTimeOrders($candidate, $orders, $settings) as $order) {
                        (new ProcessReferralCommission((int) $order->id))->handle($settings);
                    }
                    $expired += DB::transaction(function () use ($candidate, $settings): int {
                        // Serialize with real completion/refund writes before changing
                        // a referral. Existing customer orders are locked by ID first.
                        $orders = Order::where('customer_id', $candidate->referred_id)->orderBy('id')->lockForUpdate()->get();
                        $referral = Referral::whereKey($candidate->id)->lockForUpdate()->first();
                        if (! $referral || (int) $referral->referred_id !== (int) $candidate->referred_id) return 0;
                        $systemExpired = $referral->status === 'expired' && $referral->rejection_reason === 'expired_no_qualifying_order';
                        if ($referral->status !== 'pending' && ! $systemExpired) return 0;
                        if ($this->onTimeOrders($referral, $orders, $settings)->isNotEmpty()) {
                            // An on-time completion may await contact verification,
                            // platform payment proof or refund review. Never erase it.
                            if ($systemExpired) $referral->update(['status' => 'pending', 'rejection_reason' => null]);
                            return 0;
                        }
                        if ($systemExpired) return 0;
                        $referral->update(['status' => 'expired', 'rejection_reason' => 'expired_no_qualifying_order']);
                        return 1;
                    });
                }
            });
        $this->info("Expired {$expired} stale pending referral(s) using a {$days}-day window.");
        return self::SUCCESS;
    }

    /** The scan cutoff can be overridden; payout qualification still follows program policy. */
    private function onTimeOrders(Referral $referral, Collection $orders, ReferralSettingsService $settings): Collection
    {
        $completed = $orders->filter(fn (Order $order) => $order->status === 'completed' && $order->payment_status === 'confirmed')
            ->sort(function (Order $first, Order $second): int {
                $firstDate = $first->date_completed ?? $first->created_at;
                $secondDate = $second->date_completed ?? $second->created_at;
                if (! $firstDate || ! $secondDate || $firstDate->equalTo($secondDate)) return $first->id <=> $second->id;
                return $firstDate->lt($secondDate) ? -1 : 1;
            });
        if (! $settings->isAllOrders()) $completed = $completed->take(1);
        $deadline = $referral->created_at->copy()->addDays($settings->expiryDays());
        $eligibility = app(ReferralEligibility::class);
        return $completed->filter(function (Order $order) use ($referral, $settings, $deadline, $eligibility): bool {
            $completedAt = $order->date_completed ?? $order->created_at;
            if (! $completedAt || $completedAt->lt($referral->created_at) || $completedAt->gt($deadline)
                || (float) $order->final_total < $settings->minOrderAmount()) return false;
            return in_array($eligibility->orderIssue($order), [null, 'refund_under_review', 'platform_payment_verification_required'], true);
        });
    }
}

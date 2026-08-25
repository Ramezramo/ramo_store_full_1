<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\User;
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

    public function __construct(public int $orderId)
    {
    }

    public function handle(ReferralSettingsService $settings): void
    {
        $order = Order::find($this->orderId);
        if (! $order || $order->status !== 'completed' || ! $settings->isEnabled()) {
            return;
        }

        if ((float) $order->final_total < $settings->minOrderAmount() || ! $order->customer_id) {
            return;
        }

        DB::transaction(function () use ($order, $settings): void {
            $referralQuery = Referral::query()
                ->where('referred_id', (int) $order->customer_id)
                ->when(
                    $settings->isAllOrders(),
                    fn ($query) => $query->whereIn('status', ['pending', 'qualified']),
                    fn ($query) => $query->where('status', 'pending'),
                )
                ->lockForUpdate();
            $referral = $referralQuery->first();

            if (! $referral) {
                return;
            }

            if (! $settings->isAllOrders()) {
                $priorCompleted = Order::query()
                    ->where('customer_id', (int) $order->customer_id)
                    ->where('status', 'completed')
                    ->where('id', '!=', $order->id)
                    ->exists();
                if ($priorCompleted) {
                    return;
                }
            }

            $referrer = $referral->referrer;
            if ($this->couponBelongsToReferrer($order, $referrer)) {
                $referral->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'referrer_coupon_used',
                ]);
                return;
            }

            if (ReferralCommission::query()->where('order_id', $order->id)->exists()) {
                return;
            }

            $amount = $settings->calculateCommission((float) $order->final_total);
            if ($amount <= 0) {
                return;
            }

            try {
                $commission = ReferralCommission::create([
                    'referral_id' => $referral->id,
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                return;
            }

            $referralUpdate = ['status' => 'qualified'];
            if (! $referral->qualifying_order_id) {
                $referralUpdate['qualifying_order_id'] = $order->id;
            }
            $referral->update($referralUpdate);

            $freshOrder = $order->fresh();
            $timeline = is_array($freshOrder?->timeline) ? $freshOrder->timeline : [];
            $timeline[] = [
                'timestamp' => now()->toIso8601String(),
                'event' => 'referral_commission_created',
                'referral_id' => $referral->id,
                'commission_id' => $commission->id,
                'amount' => $amount,
                'status' => 'pending',
            ];
            $freshOrder?->update(['timeline' => $timeline]);
        });
    }

    private function couponBelongsToReferrer(Order $order, ?User $referrer): bool
    {
        if (! $referrer || ! $order->coupon_code) {
            return false;
        }

        $vendor = DB::table('coupons as c')
            ->join('vendor_users as v', 'v.id', '=', 'c.vendor_id')
            ->whereRaw('LOWER(c.code) = ?', [strtolower(trim((string) $order->coupon_code))])
            ->first(['v.email', 'v.phone']);

        if (! $vendor) {
            return false;
        }

        $referrerEmail = strtolower(trim((string) $referrer->email));
        $vendorEmail = strtolower(trim((string) $vendor->email));
        if ($referrerEmail !== '' && $referrerEmail === $vendorEmail) {
            return true;
        }

        return $this->normalizePhone($referrer->phone) !== ''
            && $this->normalizePhone($referrer->phone) === $this->normalizePhone($vendor->phone);
    }

    private function normalizePhone(mixed $phone): string
    {
        $digits = preg_replace('/\\D+/', '', (string) $phone) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '20')) {
            $digits = substr($digits, 2);
        }
        return ltrim($digits, '0');
    }
}

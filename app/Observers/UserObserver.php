<?php

namespace App\Observers;

use App\Models\Referral;
use App\Models\User;
use App\Models\Order;
use App\Jobs\ProcessReferralCommission;
use App\Services\ReferralFraudChecker;
use App\Services\ReferralSettingsService;
use App\Support\EgyptianPhoneNumber;
use Illuminate\Support\Str;
class UserObserver
{
    /** @var array<int, array{referrer_id:int, status:string, reason:?string}> */
    private static array $captures = [];

    public function __construct(private ReferralFraudChecker $fraudChecker)
    {
    }

    public function creating(User $user): void
    {
        // Never accept a referral code supplied by a registration payload.
        $user->referral_code = $this->generateUniqueCode();
        $user->referral_lock_ip = $user->referral_lock_ip ?: request()->ip();

        $code = strtoupper(trim((string) request()->cookie('ref_code', request()->query('ref', ''))));
        if ($code === '' || ! preg_match('/^[A-Z0-9]{4,20}$/', $code)) {
            return;
        }

        $referrer = User::whereRaw('LOWER(referral_code) = ?', [strtolower($code)])->first();
        if (! $referrer || (int) $referrer->getKey() === (int) ($user->getKey() ?? 0)) {
            return;
        }

        $fraud = $this->fraudChecker->evaluate($referrer, $user, request()->ip());
        if ($fraud['hard_reject']) {
            self::$captures[spl_object_id($user)] = [
                'referrer_id' => (int) $referrer->getKey(),
                'status' => 'rejected',
                'reason' => $this->compactReason($fraud['reasons']),
            ];
            return;
        }

        $user->referred_by = $referrer->getKey();
        $user->referral_lock_ip = request()->ip();
        self::$captures[spl_object_id($user)] = [
            'referrer_id' => (int) $referrer->getKey(),
            'status' => 'pending',
            'reason' => $fraud['review_reason'],
        ];
    }

    public function created(User $user): void
    {
        $key = spl_object_id($user);
        $capture = self::$captures[$key] ?? null;
        unset(self::$captures[$key]);

        if (! $capture) {
            return;
        }

        Referral::create([
            'referrer_id' => $capture['referrer_id'],
            'referred_id' => (int) $user->getKey(),
            'status' => $capture['status'],
            'rejection_reason' => $capture['reason'],
        ]);
    }

    public function updated(User $user): void
    {
        $verifiedPhone = $user->wasChanged('is_phone_verified')
            && ! (bool) $user->getRawOriginal('is_phone_verified')
            && (bool) $user->is_phone_verified
            && EgyptianPhoneNumber::normalize((string) $user->phone);
        $email = strtolower((string) $user->email);
        $verifiedEmail = $user->wasChanged('email_verified_at')
            && ! $user->getRawOriginal('email_verified_at') && $user->email_verified_at
            && filter_var($email, FILTER_VALIDATE_EMAIL)
            && ! str_ends_with($email, '@ramostore.local')
            && ! str_ends_with($email, '@phone.user');
        if (! $verifiedPhone && ! $verifiedEmail) return;

        $settings = app(ReferralSettingsService::class);
        if (! $settings->isEnabled() || ! Referral::where('referred_id', $user->id)
            ->where(function ($query): void {
                $query->whereIn('status', ['pending', 'qualified'])
                    ->orWhere(function ($expired): void {
                        $expired->where('status', 'expired')->where('rejection_reason', 'expired_no_qualifying_order');
                    });
            })->exists()) return;

        $orders = Order::where('customer_id', $user->id)
            ->where('status', 'completed')->where('payment_status', 'confirmed');
        if (! $settings->isAllOrders()) {
            $first = $orders->orderByRaw('COALESCE(date_completed, created_at) ASC')->orderBy('id')->value('id');
            if ($first) ProcessReferralCommission::dispatch((int) $first)->afterCommit();
            return;
        }

        // Query in bounded batches. The jobs recheck identity and order facts after
        // commit; a proof that is subsequently removed cannot release a reward.
        $orders->whereNotExists(function ($query): void {
            $query->selectRaw('1')->from('referral_commissions')
                ->whereColumn('referral_commissions.order_id', 'orders.id');
        })->select('orders.id')->chunkById(100, function ($batch): void {
            foreach ($batch as $order) ProcessReferralCommission::dispatch((int) $order->id)->afterCommit();
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    private function compactReason(array $reasons): string
    {
        return Str::limit(implode(',', array_values(array_unique($reasons))), 100, '');
    }
}

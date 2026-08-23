<?php

namespace App\Observers;

use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralFraudChecker;
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

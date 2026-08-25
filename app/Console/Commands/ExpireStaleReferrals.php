<?php

namespace App\Console\Commands;

use App\Models\Referral;
use App\Services\ReferralSettingsService;
use Illuminate\Console\Command;

class ExpireStaleReferrals extends Command
{
    protected $signature = 'referrals:expire-stale
                            {--days= : Override the configured expiration window for this run}';

    protected $description = 'Expire pending referrals that have no qualifying completed order within the configured window';

    public function handle(ReferralSettingsService $settings): int
    {
        $configuredDays = $settings->expiryDays();
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : $configuredDays;

        if ($days < 1 || $days > 3650) {
            $this->error('The expiration window must be between 1 and 3650 days.');
            return self::FAILURE;
        }

        $expired = Referral::query()
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subDays($days))
            ->update([
                'status' => 'expired',
                'rejection_reason' => 'expired_no_qualifying_order',
                'updated_at' => now(),
            ]);

        $this->info("Expired {$expired} stale pending referral(s) using a {$days}-day window.");

        return self::SUCCESS;
    }
}

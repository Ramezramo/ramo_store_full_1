<?php

namespace App\Services;

use App\Jobs\ClawBackReferralCommission;
use App\Jobs\ProcessReferralCommission;

class ReferralOrderLifecycle
{
    public function dispatchForTransition(int $orderId, ?string $oldStatus, ?string $newStatus): void
    {
        if ($oldStatus === $newStatus || $newStatus === null) {
            return;
        }

        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            ProcessReferralCommission::dispatch($orderId)->afterCommit();
        }

        if (in_array($newStatus, ['refunded', 'cancelled'], true)) {
            ClawBackReferralCommission::dispatch($orderId)->afterCommit();
        }
    }
}

<?php

namespace App\Services;

use App\Jobs\ClawBackReferralCommission;
use App\Jobs\ProcessReferralCommission;

class ReferralOrderLifecycle
{
    public function dispatchForTransition(int $orderId, ?string $oldStatus, ?string $newStatus): void
    {
        if ($newStatus === null) {
            return;
        }

        // Reconcile actual payment/refund/shipment facts even when a manual
        // parent-status override keeps the visible status unchanged.
        ClawBackReferralCommission::dispatch($orderId)->afterCommit();
        if ($newStatus === 'completed') {
            ProcessReferralCommission::dispatch($orderId)->afterCommit();
        }

    }
}

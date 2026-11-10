<?php

namespace App\Console\Commands;

use App\Services\OrderPaymentExpiryService;
use Illuminate\Console\Command;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid';

    protected $description = 'Cancel orders that remain unpaid after their payment deadline';

    public function handle(OrderPaymentExpiryService $expiry): int
    {
        $count = $expiry->expireDueOrders();
        $this->info("Cancelled {$count} expired unpaid order(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneIdempotencyKeys extends Command
{
    protected $signature = 'idempotency:prune
                            {--hours=24 : Delete keys older than this many hours}';

    protected $description = 'Delete idempotency_keys rows that are older than the TTL window (default 24 h).';

    public function handle(): int
    {
        $hours   = (int) $this->option('hours');
        $cutoff  = now()->subHours($hours);

        $deleted = DB::table('idempotency_keys')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} idempotency key(s) older than {$hours} hour(s) (cutoff: {$cutoff}).");

        return self::SUCCESS;
    }
}

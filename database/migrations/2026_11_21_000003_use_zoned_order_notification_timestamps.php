<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // The existing trigger used the database's local clock. Preserve the
        // corresponding instant and let future writes keep the zone offset.
        DB::statement(<<<'SQL'
            ALTER TABLE order_notifications
                ALTER COLUMN created_at TYPE timestamptz
                USING created_at AT TIME ZONE current_setting('TimeZone')
            SQL);

        // App checkout stores the placement date in UTC. It is also the
        // source of the older receipts that were backfilled before the trigger.
        DB::statement(<<<'SQL'
            UPDATE order_notifications AS notice
               SET created_at = orders.date_created AT TIME ZONE 'UTC'
              FROM orders
             WHERE notice.order_id = orders.id
               AND notice.event = 'placed'
               AND orders.date_created IS NOT NULL
            SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE order_notifications
                ALTER COLUMN created_at TYPE timestamp
                USING created_at AT TIME ZONE current_setting('TimeZone')
            SQL);
    }
};

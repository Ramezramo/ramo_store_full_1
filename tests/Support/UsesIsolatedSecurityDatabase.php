<?php

namespace Tests\Support;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Security fixtures must never touch the configured application database. */
trait UsesIsolatedSecurityDatabase
{
    public function createApplication(): Application
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $app['config']->set([
            'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array', 'session.driver' => 'array', 'queue.default' => 'sync',
            'mail.default' => 'array', 'logging.default' => 'stderr', 'app.force_https' => false,
            'view.compiled' => sys_get_temp_dir().'/ramo-security-test-views',
        ]);
        DB::purge('sqlite');
        if (DB::getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new \RuntimeException('Security tests require an isolated in-memory database.');
        }
        $this->migrateSecuritySchema();

        return $app;
    }

    protected function migrateSecuritySchema(): void
    {
        foreach ([
            '2024_01_01_000001_create_ramo_store_schema.php',
            '2026_05_04_000001_add_auth_fields_and_otp_verifications.php',
            '2026_05_03_012000_create_order_sub_orders_table.php',
            '2026_05_03_011946_create_refund_requests_table.php',
            '2026_08_08_000001_add_manual_payment_verification.php',
            '2026_08_09_000001_add_computed_order_statuses.php',
            '2026_08_10_000001_add_vendor_id_to_coupons_table.php',
            '2026_08_23_000001_create_referral_program_tables.php',
            '2026_08_26_000001_normalize_and_unique_user_phones.php',
            '2026_05_12_000001_add_status_columns_to_product_variations_table.php',
            '2026_05_12_000002_create_idempotency_keys_table.php',
            '2026_11_06_000002_add_model_number_to_product_variations.php',
            '2026_11_08_000001_create_order_notifications.php',
            '2026_11_10_000001_add_payment_due_at_to_orders.php',
            '2026_11_13_000001_create_referral_commission_audits.php',
            '2026_11_13_000002_create_email_verification_tokens.php',
            '2026_11_13_000003_unique_external_provider_identity.php',
            '2026_11_13_000004_add_platform_payment_verification.php',
        ] as $migration) {
            if ($migration === '2026_11_08_000001_create_order_notifications.php' && DB::getDriverName() === 'sqlite') {
                // The production migration installs a PostgreSQL-only trigger.
                // Cart compatibility tests only require its notification table.
                Schema::create('order_notifications', function (Blueprint $table): void {
                    $table->id(); $table->unsignedBigInteger('customer_id'); $table->unsignedBigInteger('order_id');
                    $table->string('event', 24); $table->string('status', 200)->nullable();
                    $table->timestamp('read_at')->nullable(); $table->timestamp('created_at')->useCurrent();
                });
                continue;
            }
            (require base_path('database/migrations/'.$migration))->up();
        }
        Schema::table('users', function (Blueprint $table): void {
            foreach (['address', 'city', 'state', 'address_note', 'latitude', 'longitude'] as $column) {
                if (! Schema::hasColumn('users', $column)) $table->string($column)->nullable();
            }
        });
    }
}

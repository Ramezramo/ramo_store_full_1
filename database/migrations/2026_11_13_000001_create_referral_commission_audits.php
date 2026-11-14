<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commission_audits', function (Blueprint $table): void {
            $table->id();
            // History must survive an account/order deletion; do not cascade it away.
            $table->unsignedBigInteger('commission_id')->index();
            $table->unsignedBigInteger('referral_id')->index();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('action', 40);
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->decimal('old_amount', 12, 2)->nullable();
            $table->decimal('new_amount', 12, 2);
            $table->string('reason', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
CREATE FUNCTION prevent_referral_audit_mutation() RETURNS trigger AS $$
BEGIN
    RAISE EXCEPTION 'Referral financial audit events are immutable';
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER referral_audit_immutable BEFORE UPDATE OR DELETE
ON referral_commission_audits FOR EACH ROW EXECUTE FUNCTION prevent_referral_audit_mutation();
SQL);
        } elseif (DB::getDriverName() === 'sqlite') {
            foreach (['UPDATE', 'DELETE'] as $operation) {
                $name = strtolower($operation);
                DB::unprepared("CREATE TRIGGER referral_audit_no_{$name} BEFORE {$operation} ON referral_commission_audits BEGIN SELECT RAISE(ABORT, 'Referral financial audit events are immutable'); END");
            }
        } elseif (DB::getDriverName() === 'mysql') {
            foreach (['UPDATE', 'DELETE'] as $operation) {
                $name = strtolower($operation);
                DB::unprepared("CREATE TRIGGER referral_audit_no_{$name} BEFORE {$operation} ON referral_commission_audits FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Referral financial audit events are immutable'");
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commission_audits');
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP FUNCTION IF EXISTS prevent_referral_audit_mutation()');
        }
    }
};

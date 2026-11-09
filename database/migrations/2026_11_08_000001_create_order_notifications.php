<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('event', 24);
            $table->string('status', 200)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['customer_id', 'created_at', 'id']);
            $table->index(['customer_id', 'read_at']);
        });

        // Existing orders have a reliable placement date. Include those receipts
        // without counting them as new alerts or inferring past status changes.
        DB::statement(<<<'SQL'
            INSERT INTO order_notifications (customer_id, order_id, event, read_at, created_at)
            SELECT customer_id, id, 'placed', CURRENT_TIMESTAMP,
                   COALESCE(date_created, created_at, CURRENT_TIMESTAMP)
            FROM orders
            WHERE customer_id > 0
            SQL);

        // Web checkout uses the query builder, while the API uses Eloquent.
        // Recording changes on the PostgreSQL table covers both write paths
        // and keeps the event in the same transaction as the order change.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION record_order_notification() RETURNS trigger AS $$
            BEGIN
                IF NEW.customer_id IS NULL OR NEW.customer_id <= 0 THEN
                    RETURN NEW;
                END IF;

                IF TG_OP = 'INSERT' THEN
                    INSERT INTO order_notifications (customer_id, order_id, event)
                    VALUES (NEW.customer_id, NEW.id, 'placed');
                    RETURN NEW;
                END IF;

                IF NEW.status IS DISTINCT FROM OLD.status AND NULLIF(NEW.status, '') IS NOT NULL THEN
                    INSERT INTO order_notifications (customer_id, order_id, event, status)
                    VALUES (NEW.customer_id, NEW.id, 'order_status', NEW.status);
                END IF;

                IF NEW.payment_status IS DISTINCT FROM OLD.payment_status AND NULLIF(NEW.payment_status, '') IS NOT NULL THEN
                    INSERT INTO order_notifications (customer_id, order_id, event, status)
                    VALUES (NEW.customer_id, NEW.id, 'payment_status', NEW.payment_status);
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER order_notifications_on_change
            AFTER INSERT OR UPDATE OF status, payment_status ON orders
            FOR EACH ROW EXECUTE FUNCTION record_order_notification();
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS order_notifications_on_change ON orders;
            DROP FUNCTION IF EXISTS record_order_notification();
            SQL);
        Schema::dropIfExists('order_notifications');
    }
};

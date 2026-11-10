<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'payment_due_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('payment_due_at')->nullable();
                $table->index(['payment_status', 'payment_due_at'], 'orders_payment_due_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'payment_due_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_payment_due_idx');
                $table->dropColumn('payment_due_at');
            });
        }
    }
};

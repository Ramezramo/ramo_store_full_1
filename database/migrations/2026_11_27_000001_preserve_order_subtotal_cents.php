<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('original_total', 18, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        // Financial precision cannot safely be reverted to integer storage.
        throw new RuntimeException('Keep orders.original_total as decimal to avoid losing cents.');
    }
};

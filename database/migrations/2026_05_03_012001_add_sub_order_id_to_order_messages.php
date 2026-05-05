<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_messages')) return;
        if (Schema::hasColumn('order_messages', 'sub_order_id')) return;

        Schema::table('order_messages', function (Blueprint $t) {
            $t->unsignedBigInteger('sub_order_id')->nullable()->after('order_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_messages', 'sub_order_id')) {
            Schema::table('order_messages', function (Blueprint $t) {
                $t->dropColumn('sub_order_id');
            });
        }
    }
};

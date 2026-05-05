<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('refund_requests')) return;

        Schema::create('refund_requests', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('order_id');
            $t->unsignedBigInteger('customer_id');
            $t->unsignedBigInteger('vendor_id')->nullable();
            $t->string('type')->default('refund');
            $t->string('reason');
            $t->text('description')->nullable();
            $t->string('status')->default('pending');
            $t->text('admin_note')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};

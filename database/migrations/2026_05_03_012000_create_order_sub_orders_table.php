<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_sub_orders')) return;

        Schema::create('order_sub_orders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('parent_order_id');
            $t->unsignedBigInteger('vendor_id')->nullable();
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->string('status')->default('pending');
            $t->text('line_items')->nullable();
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('discount_total', 12, 2)->default(0);
            $t->decimal('total', 12, 2)->default(0);
            $t->string('tracking_number')->nullable();
            $t->string('tracking_carrier')->nullable();
            $t->text('timeline')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sub_orders');
    }
};

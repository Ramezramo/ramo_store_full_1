<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_messages')) return;

        Schema::create('order_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('order_id');
            $t->unsignedBigInteger('customer_id');
            $t->unsignedBigInteger('vendor_id')->nullable();
            $t->enum('sender_type', ['customer', 'vendor']);
            $t->text('message');
            $t->boolean('is_vendor_response')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_messages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 36);          // UUID sent by the client
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable(); // null while in-flight
            $table->timestamp('created_at')->useCurrent();

            // One key is unique per user; different users may reuse the same UUID
            $table->unique(['key', 'user_id']);
            $table->index('created_at');        // for TTL cleanup queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};

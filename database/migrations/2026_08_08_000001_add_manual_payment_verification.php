<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 40)->default('confirmed')->after('payment_method_title');
            $table->string('payment_receipt_path')->nullable()->after('payment_status');
            $table->string('payment_receipt_name')->nullable()->after('payment_receipt_path');
            $table->timestamp('payment_receipt_uploaded_at')->nullable()->after('payment_receipt_name');
            $table->timestamp('payment_reviewed_at')->nullable()->after('payment_receipt_uploaded_at');
            $table->unsignedBigInteger('payment_reviewed_by')->nullable()->after('payment_reviewed_at');
            $table->text('payment_rejection_reason')->nullable()->after('payment_reviewed_by');
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->string('payment_method', 50);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_receipt_path',
                'payment_receipt_name',
                'payment_receipt_uploaded_at',
                'payment_reviewed_at',
                'payment_reviewed_by',
                'payment_rejection_reason',
            ]);
        });
    }
};
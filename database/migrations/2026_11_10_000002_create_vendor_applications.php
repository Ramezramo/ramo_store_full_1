<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_users', function (Blueprint $table) {
            $table->foreignId('store_category_id')->nullable()->constrained('categories2')->nullOnDelete();
        });
        Schema::create('vendor_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->unique()->constrained('vendor_users')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->text('personal_data')->nullable();
            $table->text('marketplace_draft')->nullable();
            $table->longText('identity_document')->nullable();
            $table->string('identity_mime', 50)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'submitted_at']);
        });

        Schema::create('vendor_application_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('vendor_applications')->cascadeOnDelete();
            $table->string('status', 20);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_application_notifications');
        Schema::dropIfExists('vendor_applications');
        Schema::table('vendor_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_category_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add provider / provider_id to users if not already there
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider', 30)->nullable()->after('registration_method');
            }
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }
        });

        // Create otp_verifications table
        if (!Schema::hasTable('otp_verifications')) {
            Schema::create('otp_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('phone', 30)->index();
                $table->string('otp_code', 10);
                $table->timestamp('expires_at');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->unsignedTinyInteger('resend_count')->default(0);
                $table->timestamp('resend_window_start')->nullable();
                $table->boolean('verified')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('provider');
            $table->dropColumnIfExists('provider_id');
        });
    }
};

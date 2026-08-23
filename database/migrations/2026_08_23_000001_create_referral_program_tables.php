<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'referral_code')) {
                    $table->string('referral_code', 20)->nullable()->unique();
                }
                if (! Schema::hasColumn('users', 'referred_by')) {
                    $table->unsignedBigInteger('referred_by')->nullable()->index();
                }
                if (! Schema::hasColumn('users', 'referral_lock_ip')) {
                    $table->string('referral_lock_ip', 45)->nullable();
                }
            });

            DB::table('users')
                ->select('id')
                ->whereNull('referral_code')
                ->orderBy('id')
                ->chunkById(100, function ($users): void {
                    foreach ($users as $user) {
                        do {
                            $code = Str::upper(Str::random(8));
                        } while (DB::table('users')->where('referral_code', $code)->exists());

                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['referral_code' => $code]);
                    }
                });
        }

        if (! Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('referrer_id');
                $table->unsignedBigInteger('referred_id')->unique();
                $table->string('status', 20)->default('pending');
                $table->unsignedInteger('qualifying_order_id')->nullable();
                $table->string('rejection_reason', 100)->nullable();
                $table->timestamps();
                $table->index('referrer_id');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('referral_commissions')) {
            Schema::create('referral_commissions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('referral_id');
                $table->unsignedInteger('order_id');
                $table->decimal('amount', 10, 2);
                $table->string('status', 20)->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('clawed_back_at')->nullable();
                $table->string('clawback_reason', 100)->nullable();
                $table->timestamps();
                $table->unique('order_id');
                $table->index('referral_id');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('referrals');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasColumn('users', 'referral_code')) {
                    $table->dropUnique(['referral_code']);
                    $table->dropColumn('referral_code');
                }
                if (Schema::hasColumn('users', 'referred_by')) {
                    $table->dropIndex(['referred_by']);
                    $table->dropColumn('referred_by');
                }
                if (Schema::hasColumn('users', 'referral_lock_ip')) {
                    $table->dropColumn('referral_lock_ip');
                }
            });
        }
    }
};

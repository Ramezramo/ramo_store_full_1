<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('referrals')
            ->where('status', 'rejected')
            ->where('rejection_reason', 'registration_ip_matches_referrer')
            ->update([
                'status' => 'pending',
                'rejection_reason' => 'registration_ip_requires_review',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('referrals')
            ->where('status', 'pending')
            ->where('rejection_reason', 'registration_ip_requires_review')
            ->update([
                'status' => 'rejected',
                'rejection_reason' => 'registration_ip_matches_referrer',
                'updated_at' => now(),
            ]);
    }
};

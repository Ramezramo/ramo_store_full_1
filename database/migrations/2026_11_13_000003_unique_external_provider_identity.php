<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('users')->select('provider', 'provider_id')
            ->whereNotNull('provider')->whereNotNull('provider_id')
            ->groupBy('provider', 'provider_id')->havingRaw('COUNT(*) > 1')->exists();
        if ($duplicate) {
            throw new RuntimeException('Duplicate external sign-in identities must be reviewed by an administrator before applying users_provider_identity_unique. No accounts have been merged or deleted.');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique(['provider', 'provider_id'], 'users_provider_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_provider_identity_unique');
        });
    }
};

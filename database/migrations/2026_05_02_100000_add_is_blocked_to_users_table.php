<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Already present in 2024_01_01_000001 on fresh installs. Guard to be safe.
        if (!Schema::hasColumn('users', 'is_blocked')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('is_phone_verified');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_blocked')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_blocked');
            });
        }
    }
};

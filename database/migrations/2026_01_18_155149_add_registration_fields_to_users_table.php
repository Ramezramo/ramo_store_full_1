<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These columns are already present when 2024_01_01_000001 ran on a fresh install.
        // Guard each column so this migration is safe to run in both cases.
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'registration_method')) {
                $table->string('registration_method')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_phone_verified')) {
                $table->boolean('is_phone_verified')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'registration_method')) $cols[] = 'registration_method';
            if (Schema::hasColumn('users', 'is_phone_verified'))   $cols[] = 'is_phone_verified';
            if ($cols) $table->dropColumn($cols);
        });
    }
};

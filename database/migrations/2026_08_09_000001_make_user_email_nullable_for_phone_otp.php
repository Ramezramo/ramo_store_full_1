<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'email')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Older OTP accounts were given a synthetic email. It was only an
        // implementation detail and should not be shown or stored as contact
        // information.
        DB::table('users')
            ->where('email', 'like', 'otp_%@ramostore.local')
            ->update(['email' => null]);
    }

    public function down(): void
    {
        // Existing NULL values cannot safely be converted back to a required
        // email, so this migration intentionally has no destructive rollback.
    }
};
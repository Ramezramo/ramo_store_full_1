<?php

use App\Support\EgyptianPhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $seen = [];

        DB::table('users')
            ->select(['id', 'phone'])
            ->whereNotNull('phone')
            ->orderBy('id')
            ->get()
            ->each(function (object $user) use (&$seen): void {
                $raw = trim((string) $user->phone);
                if ($raw === '') {
                    return;
                }

                $normalized = EgyptianPhoneNumber::normalize($raw);
                $dedupeKey = $normalized ?: $raw;

                if (isset($seen[$dedupeKey])) {
                    // Preserve the first record deterministically and clear later
                    // duplicates; the partial unique index ignores empty values.
                    DB::table('users')->where('id', $user->id)->update(['phone' => '']);
                    return;
                }

                $seen[$dedupeKey] = true;
                if ($normalized && $raw !== $normalized) {
                    DB::table('users')->where('id', $user->id)->update(['phone' => $normalized]);
                }
            });

        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS users_phone_unique_nonempty ON users (phone) WHERE phone IS NOT NULL AND phone <> ''");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_phone_unique_nonempty');
    }
};

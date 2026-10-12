<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DebugAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('app.debug')) {
            return;
        }

        $email = (string) config('demo_accounts.admin.email');
        $password = (string) config('demo_accounts.admin.password');
        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing) {
            // Migrate only the old starter password; never overwrite a custom one.
            if (
                is_string($existing->password)
                &&
                ! Hash::check($password, $existing->password)
                && Hash::check('Admin@12345', $existing->password)
            ) {
                DB::table('users')->where('id', $existing->id)->update([
                    'password' => Hash::make($password),
                    'updated_at' => now(),
                ]);
            }

            return;
        }

        DB::table('users')->insertOrIgnore([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'phone' => '',
            'nicename' => '',
            'registered' => '',
            'firstname' => '',
            'lastname' => '',
            'description' => '',
            'capabilities' => '',
            'shipping' => '',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

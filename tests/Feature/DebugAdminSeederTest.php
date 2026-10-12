<?php

namespace Tests\Feature;

use Database\Seeders\DebugAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DebugAdminSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_debug_admin_credentials_can_sign_in_through_the_admin_form(): void
    {
        config(['app.debug' => true]);

        $credentials = config('demo_accounts.admin');
        DB::table('users')->where('email', $credentials['email'])->delete();

        $this->seed(DebugAdminSeeder::class);

        $this->post(route('admin.login.post'), $credentials)
            ->assertRedirect(route('admin.dashboard'));
    }
}

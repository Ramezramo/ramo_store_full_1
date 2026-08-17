<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class CsrfAndApiAuthTest extends TestCase
{
    public function test_customer_login_requires_csrf_token(): void
    {
        $this->post(route('login'), [
            'email' => 'customer@example.com',
            'password' => 'password',
        ])->assertStatus(419);
    }

    public function test_vendor_login_route_names_do_not_collide(): void
    {
        $this->assertStringEndsWith('/vendor-login', route('vendor.login'));
        $this->assertStringEndsWith('/api/vendor/login', route('vendor.api.login'));
    }

    public function test_vendor_login_requires_csrf_token(): void
    {
        $this->post(route('vendor.login.submit'), [
            'email' => 'vendor@example.com',
            'password' => 'password',
        ])->assertStatus(419);
    }

    public function test_admin_login_requires_csrf_token(): void
    {
        $this->post(route('admin.login.post'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertStatus(419);
    }

    public function test_config_upload_requires_authentication(): void
    {
        $this->postJson('/api/ramo/config-storing', [
            'config' => ['test' => true],
        ])->assertUnauthorized();
    }

    public function test_config_upload_requires_an_admin_role_at_the_route_boundary(): void
    {
        $user = $this->createUser(['customer']);

        try {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/ramo/config-storing', [
                    'config' => ['test' => true],
                ])
                ->assertForbidden();
        } finally {
            $user->delete();
        }
    }

    public function test_admin_config_upload_reaches_controller_validation(): void
    {
        $admin = $this->createUser(['admin']);

        try {
            $this->actingAs($admin, 'sanctum')
                ->postJson('/api/ramo/config-storing')
                ->assertUnprocessable()
                ->assertJsonPath('message', 'Validation error');
        } finally {
            $admin->delete();
        }
    }

    public function test_api_logout_no_longer_accepts_get(): void
    {
        $user = $this->createUser(['customer']);

        try {
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/user/logout')
                ->assertStatus(405);
        } finally {
            $user->delete();
        }
    }

    public function test_password_reset_token_testing_endpoint_is_removed(): void
    {
        $this->postJson('/api/user/generateTokenTesting', [
            'email' => 'customer@example.com',
        ])->assertNotFound();
    }

    public function test_dead_api_v2_route_registration_is_removed(): void
    {
        $this->assertFalse(file_exists(base_path('routes/api2.php')));
    }

    /** @param array<int, string> $roles */
    private function createUser(array $roles): User
    {
        $user = User::create([
            'name' => 'CSRF API Auth Test',
            'email' => 'csrf-api-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
        ]);

        $user->role = json_encode($roles);
        $user->save();

        return $user;
    }
}

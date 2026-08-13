<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_probe_returns_a_non_sensitive_success_response(): void
    {
        $this->get(route('health'))
            ->assertOk()
            ->assertExactJson(['status' => 'ok'])
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_health_probe_returns_service_unavailable_when_database_is_down(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->andThrow(new \RuntimeException('database unavailable'));

        $this->get(route('health'))
            ->assertStatus(503)
            ->assertExactJson(['status' => 'unavailable'])
            ->assertHeader('Cache-Control', 'no-store, private');
    }
}

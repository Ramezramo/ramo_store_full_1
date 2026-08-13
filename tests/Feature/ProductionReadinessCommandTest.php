<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionReadinessCommandTest extends TestCase
{
    public function test_it_passes_for_a_safe_shared_production_configuration(): void
    {
        config([
            'app.debug' => false,
            'app.url' => 'https://store.example.com',
            'session.secure' => true,
            'session.driver' => 'redis',
            'cache.default' => 'redis',
            'queue.default' => 'redis',
            'filesystems.default' => 's3',
            'sms.driver' => 'log',
        ]);

        $this->artisan('production:check')
            ->expectsOutputToContain('OTP is still using the visible log-driver development fallback.')
            ->assertExitCode(0);
    }

    public function test_it_fails_when_debug_mode_is_enabled(): void
    {
        config([
            'app.debug' => true,
            'app.url' => 'https://store.example.com',
            'session.secure' => true,
            'session.driver' => 'redis',
            'cache.default' => 'redis',
            'queue.default' => 'redis',
            'filesystems.default' => 's3',
        ]);

        $this->artisan('production:check')
            ->expectsOutputToContain('Production configuration check failed.')
            ->assertExitCode(1);
    }
}

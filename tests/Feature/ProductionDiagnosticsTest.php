<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionDiagnosticsTest extends TestCase
{
    public function test_debugbar_is_disabled_in_the_testing_environment(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertCookieMissing('PHPDEBUGBAR_STACK_DATA')
            ->assertDontSee('id="phpdebugbar"', false)
            ->assertDontSee('/_debugbar/', false)
            ->assertDontSee('Laravel Debugbar');
    }

    public function test_storefront_does_not_disclose_php_version_through_response_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeaderMissing('X-Powered-By');
    }
}

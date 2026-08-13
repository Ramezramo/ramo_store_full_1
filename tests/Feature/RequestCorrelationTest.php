<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RequestCorrelationTest extends TestCase
{
    public function test_a_request_id_is_generated_and_returned_when_one_is_not_supplied(): void
    {
        Config::set('app.debug', false);

        $response = $this->get(route('shop'))
            ->assertOk();

        $requestId = (string) $response->headers->get('X-Request-ID');
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $requestId);
    }

    public function test_a_safe_client_request_id_is_returned_unchanged(): void
    {
        Config::set('app.debug', false);

        $this->withHeader('X-Request-ID', 'storefront-test-20260813')
            ->get(route('shop'))
            ->assertOk()
            ->assertHeader('X-Request-ID', 'storefront-test-20260813');
    }

    public function test_an_unsafe_request_id_is_replaced_before_it_can_reach_logs(): void
    {
        Config::set('app.debug', false);

        $response = $this->withHeader('X-Request-ID', 'bad id with spaces')
            ->get(route('shop'))
            ->assertOk();

        $this->assertNotSame('bad id with spaces', $response->headers->get('X-Request-ID'));
    }
}

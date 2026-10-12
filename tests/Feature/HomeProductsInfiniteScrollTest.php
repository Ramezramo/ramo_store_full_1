<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeProductsInfiniteScrollTest extends TestCase
{
    public function test_scroll_endpoint_returns_product_markup_without_count_metadata(): void
    {
        $response = $this->getJson(route('home.products'));

        $response->assertOk()
            ->assertJsonStructure(['html', 'hasMore', 'nextPage']);

        $payload = $response->json();
        self::assertIsString($payload['html']);
        self::assertIsBool($payload['hasMore']);
        self::assertTrue($payload['nextPage'] === null || is_int($payload['nextPage']));
        self::assertArrayNotHasKey('total', $payload);
    }
}

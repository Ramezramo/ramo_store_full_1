<?php

namespace Tests\Feature;

use Tests\TestCase;

class SqlInjectionRegressionTest extends TestCase
{
    /**
     * These payloads must remain data values, never executable SQL text.
     * The test deliberately combines them with each SQL-sensitive filter so a
     * future change that interpolates request input causes a visible failure.
     *
     * @var list<string>
     */
    private const SQLI_PAYLOADS = [
        "' OR '1'='1",
        '1; DROP TABLE users--',
        "' UNION SELECT 1,2,3--",
        '1;SELECT pg_sleep(5)--',
    ];

    public function test_search_query_parameters_neutralize_common_sqli_payloads(): void
    {
        foreach (self::SQLI_PAYLOADS as $payload) {
            $response = $this->get(route('search', [
                'q' => $payload,
                'category' => $payload,
                'sort' => $payload,
                'min_price' => $payload,
                'max_price' => $payload,
            ]));

            $response->assertOk();
            $this->assertResponseDoesNotExposeDatabaseError($response->getContent());
        }
    }

    public function test_shop_query_parameters_neutralize_common_sqli_payloads(): void
    {
        foreach (self::SQLI_PAYLOADS as $payload) {
            $response = $this->get(route('shop', [
                'search' => $payload,
                'brand' => $payload,
                'category' => $payload,
                'sort' => $payload,
            ]));

            $response->assertOk();
            $this->assertResponseDoesNotExposeDatabaseError($response->getContent());
        }
    }

    public function test_native_database_errors_are_logged_and_not_serialized_to_clients(): void
    {
        $source = file_get_contents(base_path('routes/ramo-native-php/config/app-config.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("error_log('Native app-config database error: ' . \$e->getMessage())", $source);
        $this->assertStringNotContainsString("'message' => \$e->getMessage()", $source);
    }

    public function test_raw_sql_guardrail_covers_native_routes_and_database_sinks(): void
    {
        $source = file_get_contents(base_path('scripts/check_raw_sql_interpolation.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("\$root . '/routes'", $source);
        $this->assertStringContainsString('(?:query|exec)', $source);
        $this->assertStringContainsString('pg_query|mysqli_query', $source);
    }

    private function assertResponseDoesNotExposeDatabaseError(string $content): void
    {
        $normalizedContent = strtolower($content);

        foreach (['sqlstate', 'pdoexception', 'postgresql error', 'syntax error at or near'] as $marker) {
            $this->assertStringNotContainsString($marker, $normalizedContent);
        }
    }
}

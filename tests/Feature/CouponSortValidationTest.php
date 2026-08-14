<?php

namespace Tests\Feature;

use Tests\TestCase;

class CouponSortValidationTest extends TestCase
{
    public function test_coupon_index_rejects_a_sql_injection_payload_in_sort_by(): void
    {
        $response = $this->withoutMiddleware()->getJson('/api/ramo/coupons/show?sort_by=1%29%29%3B%20DROP%20TABLE%20coupons%3B--&sort_dir=desc');

        $response->assertUnprocessable();
        $this->assertStringNotContainsString('sqlstate', strtolower($response->getContent()));
        $this->assertStringNotContainsString('pdoexception', strtolower($response->getContent()));
    }

    public function test_coupon_index_rejects_a_sql_injection_payload_in_sort_direction(): void
    {
        $response = $this->withoutMiddleware()->getJson('/api/ramo/coupons/show?sort_by=id&sort_dir=desc%3B%20DROP%20TABLE%20coupons%3B--');

        $response->assertUnprocessable();
    }
}

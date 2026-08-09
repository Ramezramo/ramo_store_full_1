<?php

namespace Tests\Unit;

use App\Services\OrderStatusService;
use PHPUnit\Framework\TestCase;

class OrderStatusServiceTest extends TestCase
{
    private OrderStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderStatusService();
    }

    public function test_unconfirmed_payment_keeps_order_pending(): void
    {
        $this->assertSame('pending', $this->service->compute('pending_verification', ['delivered']));
        $this->assertSame('pending', $this->service->compute('rejected', ['cancelled']));
    }

    public function test_all_vendor_status_combinations_are_derived(): void
    {
        $this->assertSame('cancelled', $this->service->compute('confirmed', ['cancelled', 'cancelled']));
        $this->assertSame('partially_cancelled', $this->service->compute('confirmed', ['cancelled', 'shipped']));
        $this->assertSame('completed', $this->service->compute('confirmed', ['delivered', 'delivered']));
        $this->assertSame('partially_delivered', $this->service->compute('confirmed', ['delivered', 'shipped']));
        $this->assertSame('shipped', $this->service->compute('confirmed', ['shipped', 'shipped']));
        $this->assertSame('partially_shipped', $this->service->compute('confirmed', ['shipped', 'pending']));
        $this->assertSame('processing', $this->service->compute('confirmed', ['processing', 'pending']));
        $this->assertSame('pending', $this->service->compute('confirmed', ['pending', 'returned']));
    }

    public function test_legacy_completed_vendor_status_is_delivered(): void
    {
        $this->assertSame('completed', $this->service->compute('confirmed', ['completed', 'completed']));
    }
}
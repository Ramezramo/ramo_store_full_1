<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\StorefrontLabels;
use Tests\TestCase;

class StorefrontLabelsTest extends TestCase
{
    public function test_it_localizes_known_customer_category_labels_only_in_arabic(): void
    {
        $this->assertSame('جينز رجالي', StorefrontLabels::category('Jeans Man', true));
        $this->assertSame('Bags', StorefrontLabels::category('Bags', false));
        $this->assertSame('بليزرات', StorefrontLabels::category('Blazers-ramo', true));
        $this->assertSame('فساتين', StorefrontLabels::category('Dresses', true));
        $this->assertSame('أحذية', StorefrontLabels::category('Shoes', true));
        $this->assertSame('هواتف', StorefrontLabels::category('mobile-phones', true));
        $this->assertSame('Merchant Category', StorefrontLabels::category('Merchant Category', true));
    }

    public function test_it_localizes_known_customer_colour_labels_only_in_arabic(): void
    {
        $this->assertSame('أسود', StorefrontLabels::color('Black', true));
        $this->assertSame('Tan', StorefrontLabels::color('Tan', false));
        $this->assertSame('Merchant Shade', StorefrontLabels::color('Merchant Shade', true));
    }
}

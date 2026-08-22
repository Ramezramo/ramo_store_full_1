<?php

namespace Tests\Feature;

use Tests\TestCase;

class VendorStorefrontMobileTest extends TestCase
{
    public function test_vendor_storefront_has_a_compact_two_column_mobile_product_grid(): void
    {
        $view = file_get_contents(resource_path('views/web/vendor.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString('class="product-grid vendor-products-grid"', $view);
        $this->assertStringContainsString('.vendor-products-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important;', $view);
        $this->assertStringContainsString('@media(max-width:600px)', $view);
        $this->assertStringContainsString('.vendor-products-grid .product-card-img{aspect-ratio:1 / 1.08}', $view);
        $this->assertStringContainsString('.vendor-products-grid .card-add-btn,.vendor-products-grid .card-details-btn', $view);
        $this->assertStringContainsString('@media(max-width:360px)', $view);
    }
}

// Keep this test scoped to the customer-facing vendor storefront; seller/admin
// portal templates are intentionally not covered or modified here.

// @phpstan-ignore-next-line
// The source assertion above intentionally verifies the responsive contract.

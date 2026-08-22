<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileCheckoutShippingOrderTest extends TestCase
{
    public function test_shipping_section_is_prioritized_at_the_top_of_the_mobile_checkout_form(): void
    {
        $template = file_get_contents(resource_path('views/web/checkout.blade.php'));

        $this->assertIsString($template);
        $this->assertStringContainsString(
            '<div class="ck-section checkout-shipping-section">',
            $template,
        );
        $this->assertStringContainsString(
            '@media(max-width:900px){.checkout-page form{display:flex;flex-direction:column}.checkout-page .checkout-shipping-section{order:-1}}',
            $template,
        );
    }
}


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
        $this->assertStringContainsString(
            '<div id="checkout-location-empty" class="ck-location-empty" hidden>',
            $template,
        );
        $this->assertStringContainsString(
            '<div id="checkout-location-map-panel" class="ck-location-map-panel">',
            $template,
        );
        $this->assertStringContainsString(
            "// Always show and initialize the map so a new customer can choose a pin immediately.\n    loadMap();",
            $template,
        );
        $this->assertStringContainsString(
            'id="manual-location-mode-btn" aria-pressed="true"',
            $template,
        );
        $this->assertStringContainsString(
            "const setManualLocationMode = (enabled) => {",
            $template,
        );
        $this->assertStringContainsString(
            "enabled ? marker.dragging.enable() : marker.dragging.disable();",
            $template,
        );
        $this->assertStringContainsString(
            "if (!manualLocationEnabled) {\n        setStatus(checkoutText.autoLocked);\n        return;\n      }",
            $template,
        );
        $this->assertStringContainsString(
            "setManualLocationMode(false);\n        updateFields(latitude, longitude);",
            $template,
        );
        $this->assertStringContainsString(
            'manualModeBtn?.addEventListener(\'click\'',
            $template,
        );
    }
}


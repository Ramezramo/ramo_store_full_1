<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductSelectionResetTest extends TestCase
{
    public function test_product_page_resets_all_selections_after_successful_cart_addition(): void
    {
        $product = file_get_contents(resource_path('views/web/product.blade.php'));
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertIsString($product);
        $this->assertIsString($layout);
        $this->assertStringContainsString('window.productCartAddFinished = function (success)', $product);
        $this->assertStringContainsString('function resetProductSelections()', $product);
        $this->assertStringContainsString('selectedAttrs = {};', $product);
        $this->assertStringContainsString('selectedColorValues = new Set();', $product);
        $this->assertStringContainsString('colorQuantities = {};', $product);
        $this->assertStringContainsString('currentVariation = null;', $product);
        $this->assertStringContainsString("document.querySelectorAll('[data-attr-key].selected')", $product);
        $this->assertStringContainsString('updateSelectedSummary();', $product);
        $this->assertStringContainsString('updateAddButtonState();', $product);
    }

    public function test_cart_callbacks_reset_only_after_a_successful_response_and_block_duplicate_requests(): void
    {
        $product = file_get_contents(resource_path('views/web/product.blade.php'));
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertIsString($product);
        $this->assertIsString($layout);
        $this->assertStringContainsString('if (success) resetProductSelections();', $product);
        $this->assertStringContainsString('if (productCartRequestPending) return;', $product);
        $this->assertStringContainsString('productCartRequestPending = true;', $product);
        $this->assertStringContainsString('notifyProductCartAddFinished(true);', $layout);
        $this->assertStringContainsString('notifyProductCartAddFinished(false);', $layout);
    }
}

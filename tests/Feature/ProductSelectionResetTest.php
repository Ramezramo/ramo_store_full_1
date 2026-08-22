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

    public function test_product_price_block_explains_original_discount_and_selection_prices(): void
    {
        $product = file_get_contents(resource_path('views/web/product.blade.php'));

        $this->assertIsString($product);
        $this->assertStringContainsString('@if($hasDisc && $isRange)', $product);
        $this->assertStringContainsString('ليه فيه سعرين؟', $product);
        $this->assertStringContainsString('السعر بيتغير حسب اللون أو المقاس اللي هتختاره.', $product);
        $this->assertStringContainsString('pi-price-orig', $product);
        $this->assertStringContainsString('id="price-range-note"', $product);
        $this->assertStringContainsString("rangeNote.style.display = v ? 'none' : ''", $product);
        $this->assertStringContainsString('Why are there two prices?', $product);
        $this->assertStringContainsString('pi-price-explanation', $product);
    }

    public function test_color_variation_card_is_clickable_without_interfering_with_quantity_controls(): void
    {
        $product = file_get_contents(resource_path('views/web/product.blade.php'));

        $this->assertIsString($product);
        $this->assertStringContainsString('class="var-color-option"', $product);
        $this->assertStringContainsString('role="button"', $product);
        $this->assertStringContainsString('tabindex="0"', $product);
        $this->assertStringContainsString('onclick="selectColorCard(event, this)"', $product);
        $this->assertStringContainsString("event.target.closest('.var-swatch') || event.target.closest('.color-qty-stepper')", $product);
        $this->assertStringContainsString('function selectColorCard(event, card)', $product);
    }
}

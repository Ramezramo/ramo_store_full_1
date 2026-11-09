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
        $this->assertStringContainsString('window.productCartAddFinished = function (success, failedItems = [])', $product);
        $this->assertStringContainsString('function resetProductSelections()', $product);
        $this->assertStringContainsString('selectedAttrs = {};', $product);
        $this->assertStringContainsString('selectedColorValues = new Set();', $product);
        $this->assertStringContainsString('bulkSelection.clear();', $product);
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

    public function test_bulk_cart_selection_summary_tracks_selected_items(): void
    {
        $product = file_get_contents(resource_path('views/web/product.blade.php'));

        $this->assertIsString($product);
        $this->assertStringNotContainsString('id="product-sel-summary"', $product);
        $this->assertStringContainsString('id="cart-selection-summary"', $product);
        $this->assertStringContainsString('What will be added to cart', $product);
        $this->assertStringContainsString('اللي هيتضاف للسلة', $product);
        $this->assertStringContainsString('function updateCartSelectionSummary()', $product);
        $this->assertStringContainsString('if (!multiVariantMode)', $product);
        $this->assertStringContainsString('Array.from(bulkSelection, ([id, qty]) => ({ variation_id: Number(id), qty }))', $product);
        $this->assertStringContainsString('updateCartSelectionSummary();', $product);
        $this->assertStringContainsString('changeQty(delta)', $product);
    }

    public function test_add_to_cart_button_keeps_a_compact_mobile_height(): void
    {
        $product = file_get_contents(resource_path('views/web/product.blade.php'));

        $this->assertIsString($product);
        $this->assertStringContainsString('id="single-qty-controls"', $product);
        $this->assertStringContainsString('.product-page .pi-cart-row .pi-atc-btn', $product);
        $this->assertStringContainsString('min-block-size:52px', $product);
        $this->assertStringContainsString('flex:1 1 126px', $product);
    }

    public function test_color_selection_and_bulk_quantity_use_separate_native_controls(): void
    {
        $product = file_get_contents(resource_path('views/web/product.blade.php'));

        $this->assertIsString($product);
        $this->assertStringContainsString('<button type="button" class="product-color-choice"', $product);
        $this->assertStringContainsString('onclick="selectAttr(this.dataset.attrKey,this.dataset.attrVal)"', $product);
        $this->assertStringContainsString('class="bulk-qty-stepper" hidden', $product);
        $this->assertStringContainsString('onclick="toggleMultiVariantMode(this)"', $product);
        $this->assertStringContainsString('let multiVariantMode = false;', $product);
    }
}

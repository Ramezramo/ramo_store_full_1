<?php

namespace Tests\Feature;

use Tests\TestCase;

class WishlistVariationSelectionTest extends TestCase
{
    public function test_wishlist_loads_variations_for_the_same_product_card_widget(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Web/WishlistController.php'));
        $view = file_get_contents(resource_path('views/web/wishlist.blade.php'));
        $card = file_get_contents(resource_path('views/web/partials/product-card.blade.php'));

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($card);
        $this->assertStringContainsString('$cardVariations = DB::table(\'product_variations\')', $controller);
        $this->assertStringContainsString("->whereIn('product_id', ".'$products->pluck(\'id\')->all()'.')', $controller);
        $this->assertStringContainsString("->groupBy('product_id')", $controller);
        $this->assertStringContainsString("'cardVariations' => ".'$cardVariations[$p->id] ?? []', $view);
        $this->assertStringContainsString("data-vars='@json(".'$jsVars'.")'", $card);
    }

    public function test_wishlist_add_to_cart_uses_the_selected_variation_id(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $card = file_get_contents(resource_path('views/web/partials/product-card.blade.php'));

        $this->assertIsString($layout);
        $this->assertIsString($card);
        $this->assertStringContainsString("const varId   = card.dataset.selVar   ? parseInt(card.dataset.selVar) : null;", $layout);
        $this->assertStringContainsString("addToCart(pid, name, price, curImg, varId, 1);", $layout);
        $this->assertStringContainsString("_pcSelectInitialVariation(pid, card);", $layout);
        $this->assertStringContainsString("card.dataset.selVar   = match.id;", $layout);
        $this->assertStringContainsString('onclick="pcAddToCart({{ $pid }},this)"', $card);
    }
}

<?php

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class VendorStorefrontMobileTest extends TestCase
{
    public function test_vendor_storefront_reuses_the_shop_card_with_its_sellable_variation(): void
    {
        session(['locale' => 'en']);
        $product = $this->productFixture(7001);
        $html = view('web.vendor', [
            'vendor' => (object) [
                'id' => 457, 'shop_name' => 'Test store', 'banner_url' => null,
                'logo_url' => null, 'rating' => 0, 'rating_count' => 0,
                'product_count' => 1, 'shop_address' => 'Cairo',
            ],
            'products' => new LengthAwarePaginator([$product], 1, 12),
            'productVariations' => [7001 => $this->variationFixtures()],
        ])->render();

        $this->assertShopCard($html, 'vendor-7001', 'vendor-products-grid');
    }

    public function test_related_recommendations_reuse_the_shop_card_with_the_correct_price_and_cart_selection(): void
    {
        session(['locale' => 'en']);
        $html = view('web.product', [
            'product' => $this->productFixture(7000),
            'productCanBePurchased' => true,
            'variations' => collect(),
            'related' => collect([$this->productFixture(7001)]),
            'relatedVariations' => [7001 => $this->variationFixtures()],
            'vendor' => null, 'vendorProducts' => collect(),
            'reviews' => collect(), 'distribution' => collect(),
            'userReviewed' => false, 'userReview' => null,
            'helpfulVoted' => [], 'inWishlist' => false, 'cartQuantities' => [],
        ])->render();

        $this->assertShopCard($html, 'related-7001', 'related-products-grid');
    }

    private function assertShopCard(string $html, string $cardId, string $gridClass): void
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML($html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($dom);
        $card = $dom->getElementById($cardId);
        $this->assertInstanceOf(DOMElement::class, $card);
        $this->assertContains('shop-product-card', explode(' ', $card->getAttribute('class')));
        $this->assertNotContains('product-card--shared', explode(' ', $card->getAttribute('class')));
        $this->assertContains('shop-results-grid', explode(' ', $card->parentNode->getAttribute('class')));
        $this->assertContains($gridClass, explode(' ', $card->parentNode->getAttribute('class')));

        // The base price is deliberately stale, and the first variation is sold
        // out. Both screens must preserve the usable Shop-card selection.
        $this->assertSame('70012', $card->getAttribute('data-sel-var'));
        $this->assertSame('Blue', $card->getAttribute('data-sel-color'));
        $this->assertSame('Large', $card->getAttribute('data-sel-size'));
        $payload = json_decode($card->getAttribute('data-vars'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(2, $payload);
        $this->assertSame([70011, 70012], array_column($payload, 'id'));
        $this->assertSame('95.00 EGP', trim($dom->getElementById(str_replace('-7001', '-price-7001', $cardId))->textContent));
        $add = $dom->getElementById(str_replace('-7001', '-add-7001', $cardId));
        $this->assertInstanceOf(DOMElement::class, $add);
        $this->assertFalse($add->hasAttribute('disabled'));
        $this->assertSame('pcAddToCart(7001,this)', $add->getAttribute('onclick'));
        $this->assertSame(2, $xpath->query('.//button[@data-color]', $card)->length);
        $this->assertSame(2, $xpath->query('.//button[@data-size]', $card)->length);

        $this->assertStringContainsString('/css/shop-product-card.css', $html);
        $this->assertLessThan(
            strpos($html, '/css/product-card-sizing.css'),
            strpos($html, '/css/shop-product-card.css'),
            'The shared Shop presentation must load before the shared sizing overrides.'
        );
    }

    private function productFixture(int $id): object
    {
        return (object) [
            'id' => $id, 'name' => 'Test product', 'description' => '',
            'thumbnail_url' => null, 'gallery_urls' => [], 'unit_label' => null,
            'price' => 10, 'sale_price' => 0, 'on_sale' => false,
            'display_price' => 10, 'stock_quantity' => 99, 'currency' => 'EGP',
            'discount_percentage' => 0, 'minimum_order_qty' => 1,
            'max_orders_per_person' => 0, 'sold_individually' => false,
        ];
    }

    private function variationFixtures(): array
    {
        return [
            (object) [
                'id' => 70011, 'price' => 80, 'sale_price' => 0,
                'stock_quantity' => 0, 'status' => 'publish', 'stock_status' => 'outofstock',
                'attributes' => ['Color' => 'Red', 'Size' => 'Small'], 'images' => [],
            ],
            (object) [
                'id' => 70012, 'price' => 120, 'sale_price' => 95,
                'stock_quantity' => 5, 'status' => 'publish', 'stock_status' => 'instock',
                'attributes' => ['Color' => 'Blue', 'Size' => 'Large'], 'images' => [],
            ],
        ];
    }
}

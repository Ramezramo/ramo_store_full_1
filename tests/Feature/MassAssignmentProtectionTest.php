<?php

namespace Tests\Feature;

use App\Models\AttributesModel;
use App\Models\Product;
use App\Models\ProductData;
use App\Models\VideosModel;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Tests\TestCase;

class MassAssignmentProtectionTest extends TestCase
{
    public function test_attributes_model_ignores_a_caller_supplied_primary_key(): void
    {
        $attribute = new AttributesModel;

        $attribute->fill([
            'id' => 999999999,
            'name' => 'Secure attribute',
            'slug' => 'secure-attribute',
            'type' => 'select',
            'order_by' => 'menu_order',
            'has_archives' => 0,
            'is_visible' => 1,
            '_links' => '[]',
        ]);

        $this->assertNull($attribute->getAttribute('id'));
        $this->assertSame('Secure attribute', $attribute->name);
    }

    public function test_product_model_does_not_allow_primary_key_mass_assignment(): void
    {
        $product = new Product;

        $product->fill([
            'id' => 999999999,
            'name' => 'Secure product',
        ]);

        $this->assertNull($product->getAttribute('id'));
        $this->assertSame('Secure product', $product->name);
    }

    public function test_legacy_models_explicitly_deny_all_mass_assignment(): void
    {
        $productData = new ProductData;
        $video = new VideosModel;

        $this->assertTrue($productData->isGuarded('name'));
        $this->assertTrue($video->isGuarded('title'));

        try {
            $productData->fill(['name' => 'Unapproved write']);
            $this->fail('ProductData must reject unapproved mass-assignment fields.');
        } catch (MassAssignmentException) {
            $this->addToAssertionCount(1);
        }

        try {
            $video->fill(['title' => 'Unapproved write']);
            $this->fail('VideosModel must reject unapproved mass-assignment fields.');
        } catch (MassAssignmentException) {
            $this->addToAssertionCount(1);
        }
    }
}

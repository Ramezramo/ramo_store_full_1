<?php

namespace Tests\Feature;

use App\Models\AttributesModel;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductData;
use App\Models\User;
use App\Models\VendorUser;
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

    public function test_product_model_blocks_primary_key_ownership_and_moderation_mass_assignment(): void
    {
        $product = new Product;

        $product->fill([
            'id' => 999999999,
            'vendor_id' => 123,
            'status' => 'publish',
            'acceptance_status' => 'approved',
            'name' => 'Secure product',
        ]);

        $this->assertNull($product->getAttribute('id'));
        $this->assertNull($product->getAttribute('vendor_id'));
        $this->assertNull($product->getAttribute('status'));
        $this->assertNull($product->getAttribute('acceptance_status'));
        $this->assertSame('Secure product', $product->name);
    }

    public function test_user_model_blocks_privilege_mass_assignment(): void
    {
        $user = new User;

        $user->fill([
            'name' => 'Customer profile',
            'email' => 'customer@example.test',
            'role' => '["admin"]',
            'capabilities' => '{"admin":true}',
        ]);

        $this->assertSame('Customer profile', $user->name);
        $this->assertSame('customer@example.test', $user->email);
        $this->assertNull($user->getAttribute('role'));
        $this->assertNull($user->getAttribute('capabilities'));
    }

    public function test_vendor_model_blocks_system_managed_mass_assignment(): void
    {
        $vendor = new VendorUser;

        $vendor->fill([
            'shop_name' => 'Editable shop name',
            'status' => 'approved',
            'auth_token' => 'attacker-controlled-token',
            'sales_commission_percentage' => 0,
            'product_count' => 999,
            'orders_count' => 999,
            'account_no' => 'unapproved-account',
            'bank_name' => 'unapproved-bank',
        ]);

        $this->assertSame('Editable shop name', $vendor->shop_name);
        $this->assertNull($vendor->getAttribute('status'));
        $this->assertNull($vendor->getAttribute('auth_token'));
        $this->assertNull($vendor->getAttribute('sales_commission_percentage'));
        $this->assertNull($vendor->getAttribute('product_count'));
        $this->assertNull($vendor->getAttribute('orders_count'));
        $this->assertNull($vendor->getAttribute('account_no'));
        $this->assertNull($vendor->getAttribute('bank_name'));
    }

    public function test_order_model_blocks_ownership_and_paid_state_mass_assignment(): void
    {
        $order = new Order;

        $order->fill([
            'customer_id' => 123,
            'set_paid' => true,
            'payment_status' => 'paid',
            'payment_reviewed_by' => 999,
            'customer_note' => 'Allowed customer input',
        ]);

        $this->assertNull($order->getAttribute('customer_id'));
        $this->assertNull($order->getAttribute('set_paid'));
        $this->assertNull($order->getAttribute('payment_status'));
        $this->assertNull($order->getAttribute('payment_reviewed_by'));
        $this->assertSame('Allowed customer input', $order->customer_note);
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

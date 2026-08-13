<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add lookup indexes used by customer catalog browsing, carts, accounts,
     * order history, and order-shipment retrieval. PostgreSQL IF NOT EXISTS
     * keeps the migration safe for installations that received an equivalent
     * index through a prior operational change.
     */
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS products_data_public_catalog_idx ON products_data (status, acceptance_status, id DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS product_category_category_product_idx ON product_category (category_id, product_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS product_variations_sellable_product_idx ON product_variations (product_id) WHERE regular_price > 0');
        DB::statement('CREATE INDEX IF NOT EXISTS product_variations_main_product_idx ON product_variations (product_id) WHERE main_variation = TRUE');
        DB::statement('CREATE INDEX IF NOT EXISTS categories2_parent_menu_order_idx ON categories2 (parent, menu_order, id)');

        DB::statement('CREATE INDEX IF NOT EXISTS cart_items_user_updated_idx ON cart_items (user_id, updated_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS wishlists_user_product_idx ON wishlists (user_id, product_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_customer_created_idx ON orders (customer_id, created_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_status_created_idx ON orders (status, created_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS order_sub_orders_parent_idx ON order_sub_orders (parent_order_id, id)');
        DB::statement('CREATE INDEX IF NOT EXISTS order_sub_orders_tracking_number_idx ON order_sub_orders (tracking_number) WHERE tracking_number IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS order_sub_orders_tracking_number_idx');
        DB::statement('DROP INDEX IF EXISTS order_sub_orders_parent_idx');
        DB::statement('DROP INDEX IF EXISTS orders_status_created_idx');
        DB::statement('DROP INDEX IF EXISTS orders_customer_created_idx');
        DB::statement('DROP INDEX IF EXISTS wishlists_user_product_idx');
        DB::statement('DROP INDEX IF EXISTS cart_items_user_updated_idx');
        DB::statement('DROP INDEX IF EXISTS categories2_parent_menu_order_idx');
        DB::statement('DROP INDEX IF EXISTS product_variations_main_product_idx');
        DB::statement('DROP INDEX IF EXISTS product_variations_sellable_product_idx');
        DB::statement('DROP INDEX IF EXISTS product_category_category_product_idx');
        DB::statement('DROP INDEX IF EXISTS products_data_public_catalog_idx');
    }
};

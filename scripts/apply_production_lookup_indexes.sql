BEGIN;

CREATE INDEX IF NOT EXISTS products_data_public_catalog_idx ON products_data (status, acceptance_status, id DESC);
CREATE INDEX IF NOT EXISTS product_category_category_product_idx ON product_category (category_id, product_id);
CREATE INDEX IF NOT EXISTS product_variations_sellable_product_idx ON product_variations (product_id) WHERE regular_price > 0;
CREATE INDEX IF NOT EXISTS product_variations_main_product_idx ON product_variations (product_id) WHERE main_variation = TRUE;
CREATE INDEX IF NOT EXISTS categories2_parent_menu_order_idx ON categories2 (parent, menu_order, id);
CREATE INDEX IF NOT EXISTS cart_items_user_updated_idx ON cart_items (user_id, updated_at DESC);
CREATE INDEX IF NOT EXISTS wishlists_user_product_idx ON wishlists (user_id, product_id);
CREATE INDEX IF NOT EXISTS orders_customer_created_idx ON orders (customer_id, created_at DESC);
CREATE INDEX IF NOT EXISTS orders_status_created_idx ON orders (status, created_at DESC);
CREATE INDEX IF NOT EXISTS order_sub_orders_parent_idx ON order_sub_orders (parent_order_id, id);
CREATE INDEX IF NOT EXISTS order_sub_orders_tracking_number_idx ON order_sub_orders (tracking_number) WHERE tracking_number IS NOT NULL;

COMMIT;

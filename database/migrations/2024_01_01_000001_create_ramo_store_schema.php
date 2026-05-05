<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip on existing installations where the schema is already present.
        // This migration is intended for fresh deployments only.
        if (Schema::hasTable('users') && Schema::hasTable('products_data')) {
            return;
        }

        // ── users ─────────────────────────────────────────────────
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
            $t->string('user_login')->nullable();
            $t->string('username')->nullable();
            $t->string('user_nicename')->nullable();
            $t->string('display_name')->nullable();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->text('url')->nullable();
            $t->text('avatar')->nullable();
            $t->text('phone')->default('');
            $t->string('role')->default('normal_user');
            $t->text('nicename')->default('');
            $t->text('registered')->default('');
            $t->text('firstname')->default('');
            $t->text('lastname')->default('');
            $t->text('description')->default('');
            $t->text('capabilities')->default('');
            $t->text('shipping')->default('');
            $t->string('registration_method')->nullable();
            $t->boolean('is_phone_verified')->default(false);
            $t->boolean('is_blocked')->default(false);
        });

        // ── password_reset_tokens ──────────────────────────────────
        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });

        // ── personal_access_tokens ────────────────────────────────
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $t) {
                $t->id();
                $t->morphs('tokenable');
                $t->string('name');
                $t->string('token', 64)->unique();
                $t->text('abilities')->nullable();
                $t->timestamp('last_used_at')->nullable();
                $t->timestamp('expires_at')->nullable();
                $t->timestamps();
            });
        }

        // ── device_access_tokens ──────────────────────────────────
        Schema::create('device_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->string('device_id');
            $t->unsignedBigInteger('tokenable_id')->default(0);
            $t->string('name')->default('');
            $t->string('token');
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
            $t->string('key_pass');
            $t->string('identifier');
            $t->integer('blocked')->default(0);
            $t->text('about_device')->default('');
        });

        // ── failed_jobs ───────────────────────────────────────────
        Schema::create('failed_jobs', function (Blueprint $t) {
            $t->id();
            $t->string('uuid')->unique();
            $t->text('connection');
            $t->text('queue');
            $t->text('payload');
            $t->text('exception');
            $t->timestamp('failed_at')->useCurrent();
        });

        // ── brands ────────────────────────────────────────────────
        Schema::create('brands', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
        });

        // ── categories2 ───────────────────────────────────────────
        Schema::create('categories2', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->nullable();
            $t->integer('parent')->nullable();
            $t->string('description')->nullable();
            $t->string('display')->nullable();
            $t->text('image')->nullable();
            $t->integer('menu_order')->nullable();
            $t->integer('count')->nullable();
            $t->double('has_children')->nullable();
            $t->text('_links')->nullable();
        });

        // ── tags ──────────────────────────────────────────────────
        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('slug')->nullable();
            $t->string('description')->nullable();
            $t->integer('count')->nullable();
            $t->boolean('is_visible')->nullable();
            $t->text('_links')->nullable();
            $t->timestamps();
        });

        // ── products_data ─────────────────────────────────────────
        Schema::create('products_data', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug');
            $t->text('search_text')->default('');
            $t->string('permalink')->default('')->nullable();
            $t->string('date_created')->default('')->nullable();
            $t->string('date_created_gmt')->default('')->nullable();
            $t->string('date_modified')->default('')->nullable();
            $t->string('date_modified_gmt')->default('')->nullable();
            $t->string('type')->default('')->nullable();
            $t->string('status')->default('')->nullable();
            $t->boolean('featured')->default(false)->nullable();
            $t->string('catalog_visibility')->default('')->nullable();
            $t->text('description')->nullable();
            $t->text('discount_percentage')->default('');
            $t->text('short_description')->nullable();
            $t->text('sku')->nullable();
            $t->timestamp('date_on_sale_from')->nullable();
            $t->timestamp('date_on_sale_from_gmt')->nullable();
            $t->timestamp('date_on_sale_to')->nullable();
            $t->timestamp('date_on_sale_to_gmt')->nullable();
            $t->boolean('on_sale')->default(false)->nullable();
            $t->boolean('purchasable')->default(false)->nullable();
            $t->integer('total_sales')->default(0)->nullable();
            $t->boolean('virtual')->default(false)->nullable();
            $t->boolean('downloadable')->default(false)->nullable();
            $t->text('downloads')->default('[]')->nullable();
            $t->integer('download_limit')->default(0)->nullable();
            $t->integer('download_expiry')->default(0)->nullable();
            $t->text('external_url')->nullable();
            $t->string('button_text')->default('')->nullable();
            $t->boolean('manage_stock')->default(false)->nullable();
            $t->integer('stock_quantity')->default(0)->nullable();
            $t->string('backorders')->default('')->nullable();
            $t->boolean('backorders_allowed')->default(false)->nullable();
            $t->boolean('backordered')->default(false)->nullable();
            $t->integer('low_stock_amount')->default(0)->nullable();
            $t->boolean('sold_individually')->default(false)->nullable();
            $t->text('dimensions')->default('[]')->nullable();
            $t->boolean('shipping_required')->default(false)->nullable();
            $t->boolean('shipping_taxable')->default(false)->nullable();
            $t->string('shipping_class')->default('')->nullable();
            $t->integer('shipping_class_id')->default(0)->nullable();
            $t->boolean('reviews_allowed')->default(false)->nullable();
            $t->string('average_rating')->default('')->nullable();
            $t->integer('rating_count')->default(0)->nullable();
            $t->text('upsell_ids')->default('[]')->nullable();
            $t->text('cross_sell_ids')->default('[]')->nullable();
            $t->integer('parent_id')->default(0)->nullable();
            $t->string('purchase_note')->default('')->nullable();
            $t->text('categories')->default('[]')->nullable();
            $t->text('tags')->default('[]')->nullable();
            $t->text('images')->default('[]')->nullable();
            $t->text('attributes')->default('[]')->nullable();
            $t->text('default_attributes')->default('[]')->nullable();
            $t->text('variations')->default('[]')->nullable();
            $t->text('grouped_products')->default('[]')->nullable();
            $t->integer('menu_order')->default(0)->nullable();
            $t->text('related_ids')->default('[]')->nullable();
            $t->text('meta_data')->default('[]')->nullable();
            $t->string('stock_status')->default('')->nullable();
            $t->boolean('has_options')->default(false)->nullable();
            $t->boolean('has_variations')->default(false);
            $t->string('global_unique_id')->default('')->nullable();
            $t->text('better_featured_image')->nullable();
            $t->boolean('is_purchased')->default(false)->nullable();
            $t->text('attributesData')->default('[]')->nullable();
            $t->boolean('is_wallet_product')->default(false)->nullable();
            $t->text('_links')->default('[]')->nullable();
            $t->text('lang')->default('');
            $t->string('min_price')->default('0')->nullable();
            $t->string('brand_id')->default('');
            $t->string('max_price')->default('0')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->integer('minimum_order_qty')->default(0);
            $t->integer('max_orders_per_person')->default(0);
            $t->text('product_type')->default('physical')->nullable();
            $t->unsignedBigInteger('vendor_id')->nullable();
            $t->text('translations')->default('');
            $t->text('acceptance_status')->default('pending');
            $t->text('unit')->default('');
            $t->text('whatsapp')->default('');
        });

        // ── products_data_main ────────────────────────────────────
        Schema::create('products_data_main', function (Blueprint $t) {
            $t->id();
            $t->text('name');
            $t->string('slug');
            $t->string('permalink')->default('')->nullable();
            $t->string('date_created')->default('')->nullable();
            $t->string('date_created_gmt')->default('')->nullable();
            $t->string('date_modified')->default('')->nullable();
            $t->string('date_modified_gmt')->default('')->nullable();
            $t->string('type')->default('')->nullable();
            $t->string('status')->default('')->nullable();
            $t->boolean('featured')->default(false)->nullable();
            $t->string('catalog_visibility')->default('')->nullable();
            $t->text('description')->default('')->nullable();
            $t->text('discount')->default('');
            $t->text('short_description')->default('')->nullable();
            $t->text('sku')->default('')->nullable();
            $t->integer('price')->default(0)->nullable();
            $t->integer('regular_price')->default(0)->nullable();
            $t->integer('sale_price')->default(0)->nullable();
            $t->timestamp('date_on_sale_from')->nullable()->useCurrent();
            $t->timestamp('date_on_sale_from_gmt')->nullable()->useCurrent();
            $t->timestamp('date_on_sale_to')->nullable()->useCurrent();
            $t->timestamp('date_on_sale_to_gmt')->nullable()->useCurrent();
            $t->boolean('on_sale')->default(false)->nullable();
            $t->boolean('purchasable')->default(false)->nullable();
            $t->integer('total_sales')->default(0)->nullable();
            $t->boolean('virtual')->default(false)->nullable();
            $t->boolean('downloadable')->default(false)->nullable();
            $t->text('downloads')->default('{}')->nullable();
            $t->integer('download_limit')->default(0)->nullable();
            $t->integer('download_expiry')->default(0)->nullable();
            $t->text('external_url')->default('')->nullable();
            $t->string('button_text')->default('')->nullable();
            $t->boolean('manage_stock')->default(false)->nullable();
            $t->integer('stock_quantity')->default(0)->nullable();
            $t->string('backorders')->default('')->nullable();
            $t->boolean('backorders_allowed')->default(false)->nullable();
            $t->boolean('backordered')->default(false)->nullable();
            $t->integer('low_stock_amount')->default(0)->nullable();
            $t->boolean('sold_individually')->default(false)->nullable();
            $t->text('dimensions')->default('{}')->nullable();
            $t->boolean('shipping_required')->default(false)->nullable();
            $t->boolean('shipping_taxable')->default(false)->nullable();
            $t->string('shipping_class')->default('')->nullable();
            $t->integer('shipping_class_id')->default(0)->nullable();
            $t->boolean('reviews_allowed')->default(false)->nullable();
            $t->string('average_rating')->default('')->nullable();
            $t->integer('rating_count')->default(0)->nullable();
            $t->text('upsell_ids')->default('{}')->nullable();
            $t->text('cross_sell_ids')->default('{}')->nullable();
            $t->integer('parent_id')->default(0)->nullable();
            $t->string('purchase_note')->default('')->nullable();
            $t->text('categories')->default('{}')->nullable();
            $t->text('tags')->default('{}')->nullable();
            $t->text('images')->default('{}')->nullable();
            $t->text('attributes')->default('{}')->nullable();
            $t->text('default_attributes')->default('{}')->nullable();
            $t->text('variations')->default('{}')->nullable();
            $t->text('grouped_products')->default('{}')->nullable();
            $t->integer('menu_order')->default(0)->nullable();
            $t->text('price_html')->default('')->nullable();
            $t->text('related_ids')->default('{}')->nullable();
            $t->text('meta_data')->default('{}')->nullable();
            $t->string('stock_status')->default('')->nullable();
            $t->boolean('has_options')->default(false)->nullable();
            $t->string('post_password')->default('')->nullable();
            $t->string('global_unique_id')->default('')->nullable();
            $t->text('better_featured_image')->default('')->nullable();
            $t->boolean('is_purchased')->default(false)->nullable();
            $t->text('attributesData')->default('{}')->nullable();
            $t->boolean('is_wallet_product')->default(false)->nullable();
            $t->text('_links')->default('{}')->nullable();
            $t->text('lang')->default('');
            $t->string('min_price')->default('0')->nullable();
            $t->string('brand_id')->default('');
            $t->string('max_price')->default('0')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->integer('minimum_order_qty')->nullable();
            $t->integer('max_orders_per_person')->nullable();
            $t->text('product_type')->default('physical')->nullable();
            $t->unsignedBigInteger('vendor_id')->nullable();
            $t->text('translations')->default('');
            $t->text('acceptance_status')->default('pending');
            $t->text('unit')->default('');
        });

        // ── product_category ──────────────────────────────────────
        Schema::create('product_category', function (Blueprint $t) {
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('category_id');
            $t->primary(['product_id', 'category_id']);
        });

        // ── product_variations ────────────────────────────────────
        Schema::create('product_variations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->boolean('main_variation')->default(false);
            $t->text('attributes');
            $t->decimal('price', 10, 2);
            $t->decimal('regular_price', 10, 2);
            $t->decimal('sale_price', 10, 2)->nullable();
            $t->integer('stock_quantity')->default(0);
            $t->text('images')->nullable();
            $t->timestamps();
            $t->index('product_id');
        });

        // ── product_reviews ───────────────────────────────────────
        Schema::create('product_reviews', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('user_id');
            $t->smallInteger('rating');
            $t->string('title', 150)->nullable();
            $t->text('body');
            $t->timestamps();
            $t->boolean('approved')->default(true);
            $t->boolean('is_verified_purchase')->default(false);
            $t->integer('helpful_count')->default(0);
        });

        // ── orders ────────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('parent_id')->default(0)->nullable();
            $t->text('parent_vendors_ids')->nullable();
            $t->text('parent_vendors_data')->nullable();
            $t->string('status', 200)->default('pending')->nullable();
            $t->string('currency', 10)->default('USD')->nullable();
            $t->string('version', 10)->nullable();
            $t->boolean('prices_include_tax')->default(false)->nullable();
            $t->timestamp('date_created')->nullable();
            $t->timestamp('date_modified')->nullable();
            $t->decimal('discount_total', 10, 2)->default(0)->nullable();
            $t->decimal('discount_tax', 10, 2)->default(0)->nullable();
            $t->decimal('shipping_total', 10, 2)->default(0)->nullable();
            $t->decimal('shipping_tax', 10, 2)->default(0)->nullable();
            $t->decimal('cart_tax', 10, 2)->default(0)->nullable();
            $t->string('coupon_code', 50)->nullable();
            $t->decimal('final_total', 10, 2)->nullable();
            $t->integer('original_total')->default(0);
            $t->integer('coupon_applied')->default(0);
            $t->decimal('total_tax', 10, 2)->default(0)->nullable();
            $t->integer('customer_id')->nullable();
            $t->string('order_key', 50)->nullable();
            $t->text('billing')->nullable();
            $t->text('shipping')->nullable();
            $t->string('payment_method', 50)->nullable();
            $t->string('payment_method_title', 100)->nullable();
            $t->string('transaction_id', 100)->nullable();
            $t->string('customer_ip_address', 45)->nullable();
            $t->string('customer_user_agent')->nullable();
            $t->string('created_via', 50)->nullable();
            $t->text('customer_note')->nullable();
            $t->timestamp('date_completed')->nullable();
            $t->timestamp('date_paid')->nullable();
            $t->string('cart_hash', 100)->nullable();
            $t->text('meta_data')->nullable();
            $t->text('line_items')->nullable();
            $t->text('tax_lines')->nullable();
            $t->text('shipping_lines')->nullable();
            $t->text('fee_lines')->nullable();
            $t->text('coupon_lines')->nullable();
            $t->text('refunds')->nullable();
            $t->string('payment_url')->default('');
            $t->boolean('is_editable')->default(true);
            $t->boolean('needs_payment')->default(false);
            $t->boolean('needs_processing')->default(true);
            $t->text('bacs_info')->nullable();
            $t->string('currency_symbol', 10)->default('ج.م');
            $t->text('_links')->nullable();
            $t->text('date_created_gmt')->default('');
            $t->text('date_modified_gmt')->default('');
            $t->text('date_completed_gmt')->default('');
            $t->text('date_paid_gmt')->default('');
            $t->boolean('set_paid')->default(false);
            $t->integer('number')->default(0);
            $t->text('timeline')->default('[]');
            $t->timestamp('updated_at')->useCurrent();
            $t->timestamp('created_at')->useCurrent();
        });

        // ── cart_items ────────────────────────────────────────────
        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('variation_id')->nullable();
            $t->smallInteger('qty')->default(1);
            $t->timestamps();
        });

        // ── wishlists ─────────────────────────────────────────────
        Schema::create('wishlists', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('product_id');
            $t->timestamp('created_at')->nullable();
        });

        // ── coupons ───────────────────────────────────────────────
        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->decimal('amount', 10, 2);
            $t->text('status')->default('publish');
            $t->text('discount_type')->default('fixed_cart');
            $t->timestamp('date_created')->useCurrent();
            $t->timestamp('date_created_gmt')->useCurrent();
            $t->timestamp('date_modified')->useCurrent();
            $t->timestamp('date_modified_gmt')->useCurrent();
            $t->timestamp('date_expires')->nullable();
            $t->timestamp('date_expires_gmt')->nullable();
            $t->integer('usage_count')->default(0);
            $t->boolean('individual_use')->default(false);
            $t->integer('usage_limit')->nullable();
            $t->integer('usage_limit_per_user')->nullable();
            $t->integer('limit_usage_to_x_items')->nullable();
            $t->text('product_ids')->default('[]');
            $t->text('excluded_product_ids')->default('[]');
            $t->text('product_categories')->default('[]');
            $t->text('excluded_product_categories')->default('[]');
            $t->boolean('free_shipping')->default(false);
            $t->boolean('exclude_sale_items')->default(false);
            $t->decimal('minimum_amount', 10, 2)->default(0);
            $t->decimal('maximum_amount', 10, 2)->default(0);
            $t->text('email_restrictions')->default('[]');
            $t->text('used_by')->default('[]');
            $t->text('description')->nullable();
            $t->text('meta_data')->default('[]');
        });

        // ── coupon_user_limits ────────────────────────────────────
        Schema::create('coupon_user_limits', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('coupon_id');
            $t->unsignedBigInteger('user_id');
            $t->integer('use_count')->default(0);
            $t->timestamps();
        });

        // ── app_config ────────────────────────────────────────────
        Schema::create('app_config', function (Blueprint $t) {
            $t->increments('id');
            $t->text('config_json');
            $t->date('created_at')->useCurrent();
            $t->date('updated_at')->useCurrent();
        });

        // ── app_configs ───────────────────────────────────────────
        Schema::create('app_configs', function (Blueprint $t) {
            $t->id();
            $t->string('config_key', 200);
            $t->string('config_group', 50)->default('general');
            $t->string('lang', 10)->nullable();
            $t->text('value')->default('""');
            $t->string('label', 200)->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_public')->default(true);
            $t->integer('sort_order')->default(0);
            $t->timestamp('updated_at')->useCurrent();
        });

        // ── time_line_configs ─────────────────────────────────────
        Schema::create('time_line_configs', function (Blueprint $t) {
            $t->increments('id');
            $t->string('lang_code', 5);
            $t->text('config_json');
        });

        // ── shops ─────────────────────────────────────────────────
        Schema::create('shops', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('shop_name');
            $t->string('shop_address');
            $t->string('shop_logo')->nullable();
            $t->string('shop_banner')->nullable();
            $t->string('secondary_banner')->nullable();
            $t->text('status')->default('pending');
            $t->timestamps();
            $t->string('remember_token', 100)->nullable();
        });

        // ── vendor_users ──────────────────────────────────────────
        Schema::create('vendor_users', function (Blueprint $t) {
            $t->id();
            $t->string('profile_image')->nullable();
            $t->string('first_name');
            $t->string('last_name');
            $t->string('phone');
            $t->string('email');
            $t->string('password');
            $t->timestamp('email_verified_at')->nullable();
            $t->rememberToken();
            $t->timestamps();
            $t->string('shop_name');
            $t->string('shop_address');
            $t->string('shop_logo')->nullable();
            $t->string('shop_banner')->nullable();
            $t->string('secondary_banner')->nullable();
            $t->text('bottom_banner')->default('');
            $t->text('status')->default('pending')->nullable();
            $t->string('rating', 50)->default('0');
            $t->integer('rating_count')->default(0);
            $t->smallInteger('temporary_close')->default(0);
            $t->string('vacation_end_date')->default('empty');
            $t->string('vacation_start_date')->default('empty');
            $t->smallInteger('vacation_status')->default(0);
            $t->text('offer_banner')->default('empty');
            $t->integer('product_count')->nullable();
            $t->integer('orders_count')->nullable();
            $t->integer('minimum_order_amount')->nullable();
            $t->integer('free_delivery_over_amount')->nullable();
            $t->integer('free_delivery_status')->nullable();
            $t->double('sales_commission_percentage')->nullable();
            $t->string('auth_token');
            $t->string('holder_name');
            $t->integer('account_no')->nullable();
            $t->string('bank_name');
            $t->string('branch');
            $t->smallInteger('free_delivery_features_status')->nullable();
            $t->smallInteger('free_delivery_responsibility')->nullable();
            $t->smallInteger('minimum_order_amount_by_seller')->nullable();
        });

        // ── user_notes ────────────────────────────────────────────
        Schema::create('user_notes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamp('date_created')->useCurrent();
            $t->string('note');
            $t->boolean('customer_note')->default(false);
            $t->timestamps();
            $t->timestamp('date_created_gmt')->useCurrent();
            $t->integer('order_id')->default(0);
        });

        // ── countries ─────────────────────────────────────────────
        Schema::create('countries', function (Blueprint $t) {
            $t->id();
            $t->string('code')->nullable();
            $t->string('name');
            $t->timestamps();
        });

        // ── blogposts ─────────────────────────────────────────────
        Schema::create('blogposts', function (Blueprint $t) {
            $t->id();
            $t->string('date')->nullable();
            $t->string('date_gmt')->nullable();
            $t->text('guid')->nullable();
            $t->string('modified')->nullable();
            $t->string('modified_gmt')->nullable();
            $t->string('slug')->nullable();
            $t->string('status')->nullable();
            $t->string('type')->nullable();
            $t->string('link')->nullable();
            $t->text('title')->nullable();
            $t->text('content')->nullable();
            $t->text('excerpt')->nullable();
            $t->integer('author')->nullable();
            $t->integer('featured_media')->nullable();
            $t->string('comment_status')->nullable();
            $t->string('ping_status')->nullable();
            $t->boolean('sticky')->nullable();
            $t->string('template')->nullable();
            $t->string('format')->nullable();
            $t->text('meta')->nullable();
            $t->text('categories')->nullable();
            $t->text('tags')->nullable();
            $t->text('class_list')->nullable();
            $t->text('better_featured_image')->nullable();
            $t->string('image_feature')->nullable();
            $t->string('author_name')->nullable();
            $t->text('_links')->nullable();
            $t->text('_embedded')->nullable();
            $t->timestamps();
        });

        // ── attributes ────────────────────────────────────────────
        Schema::create('attributes', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->text('slug');
            $t->text('type')->default('""');
            $t->text('order_by')->default('""');
            $t->double('has_archives');
            $t->double('is_visible');
            $t->text('_links');
            $t->text('updated_at');
            $t->text('created_at');
        });

        // ── api_keys ──────────────────────────────────────────────
        Schema::create('api_keys', function (Blueprint $t) {
            $t->increments('id');
            $t->string('service_name');
            $t->text('api_key');
            $t->boolean('encrypted')->default(false)->nullable();
        });

        // ── rate_limits ───────────────────────────────────────────
        Schema::create('rate_limits', function (Blueprint $t) {
            $t->string('consumer_key', 700)->primary();
            $t->integer('request_count')->default(0)->nullable();
            $t->integer('last_request_time');
        });

        // ── version_config ────────────────────────────────────────
        Schema::create('version_config', function (Blueprint $t) {
            $t->increments('id');
            $t->text('supported_ver_from')->default('1.0.0');
            $t->text('supported_ver_to')->default('4.0.0');
        });

        // ── links ─────────────────────────────────────────────────
        Schema::create('links', function (Blueprint $t) {
            $t->id();
            $t->text('link');
            $t->text('data');
            $t->timestamps();
            $t->text('post_data')->default('Was_Get_Or_Null');
        });

        // ── links_json_res ────────────────────────────────────────
        Schema::create('links_json_res', function (Blueprint $t) {
            $t->id();
            $t->string('link');
            $t->text('data');
            $t->timestamps();
        });

        // ── link_access_logs ──────────────────────────────────────
        Schema::create('link_access_logs', function (Blueprint $t) {
            $t->increments('id');
            $t->text('link_name');
            $t->integer('usage_times')->default(0)->nullable();
            $t->text('user_call_id')->nullable();
        });

        // ── links_logs_two ────────────────────────────────────────
        Schema::create('links_logs_two', function (Blueprint $t) {
            $t->increments('id');
            $t->text('link');
            $t->text('data');
            $t->text('post_data');
            $t->text('created_at');
            $t->text('updated_at');
        });

        // ── koto ──────────────────────────────────────────────────
        Schema::create('koto', function (Blueprint $t) {
            $t->increments('id');
            $t->text('key_in');
            $t->text('identfier');
        });

        // ── getposttest ───────────────────────────────────────────
        Schema::create('getposttest', function (Blueprint $t) {
            $t->increments('id');
            $t->text('title');
            $t->text('content');
            $t->text('created_at');
            $t->text('updated_at');
        });
    }

    public function down(): void
    {
        $tables = [
            'getposttest','koto','links_logs_two','link_access_logs','links_json_res','links',
            'version_config','rate_limits','api_keys','attributes','blogposts','countries',
            'user_notes','vendor_users','shops','time_line_configs','app_configs','app_config',
            'coupon_user_limits','coupons','wishlists','cart_items','orders',
            'product_reviews','product_variations','product_category',
            'products_data_main','products_data','tags','categories2','brands',
            'failed_jobs','device_access_tokens','personal_access_tokens',
            'password_reset_tokens','users',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};

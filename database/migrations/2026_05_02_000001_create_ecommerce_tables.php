<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These tables are already created by 2024_01_01_000001_create_ramo_store_schema.php
        // on a fresh install. We guard each one individually so this migration is safe to
        // run on BOTH fresh installs (skip — already exists) and older installs that had
        // the main schema migration partially applied (create what is missing).

        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('product_id');
                $table->timestamp('created_at')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('product_reviews')) {
            Schema::create('product_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedTinyInteger('rating');
                $table->string('title', 150)->nullable();
                $table->text('body');
                $table->timestamps();
                $table->boolean('approved')->default(true);
                $table->boolean('is_verified_purchase')->default(false);
                $table->integer('helpful_count')->default(0);
            });
        }

        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variation_id')->nullable();
                $table->unsignedSmallInteger('qty')->default(1);
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('wishlists');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products_data', 'button_mode')) {
            Schema::table('products_data', function (Blueprint $table) {
                $table->string('button_mode')->default('both')->nullable()
                    ->after('button_text')
                    ->comment('Controls which action buttons show on the product card: both, cart_only, details_only');
            });
        }

        if (Schema::hasTable('products_data_main') && !Schema::hasColumn('products_data_main', 'button_mode')) {
            Schema::table('products_data_main', function (Blueprint $table) {
                $table->string('button_mode')->default('both')->nullable()
                    ->after('button_text')
                    ->comment('Controls which action buttons show on the product card: both, cart_only, details_only');
            });
        }

        // Set default for existing rows
        DB::statement("UPDATE products_data SET button_mode = 'both' WHERE button_mode IS NULL OR button_mode = ''");
    }

    public function down(): void
    {
        Schema::table('products_data', function (Blueprint $table) {
            $table->dropColumn('button_mode');
        });

        if (Schema::hasTable('products_data_main') && Schema::hasColumn('products_data_main', 'button_mode')) {
            Schema::table('products_data_main', function (Blueprint $table) {
                $table->dropColumn('button_mode');
            });
        }
    }
};

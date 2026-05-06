<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_brand_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_category_id')->nullable()->after('name');
            $table->string('parent_category_name')->nullable()->after('parent_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('category_brand_requests', function (Blueprint $table) {
            $table->dropColumn(['parent_category_id', 'parent_category_name']);
        });
    }
};

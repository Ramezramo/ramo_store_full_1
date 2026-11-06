<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variations', function (Blueprint $table): void {
            $table->string('model_number', 120)->nullable()->after('attributes');
        });
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table): void {
            $table->dropColumn('model_number');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_price_ranges', function (Blueprint $table): void {
            $table->id();
            $table->string('scope')->unique();
            $table->decimal('min_price', 12, 2)->default(0);
            $table->decimal('max_price', 12, 2)->default(0);
            $table->timestamps();
        });

        app(\App\Services\ShopPriceRangeService::class)->refreshGlobal();
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_price_ranges');
    }
};

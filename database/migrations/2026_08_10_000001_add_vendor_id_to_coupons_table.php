<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Coupons can belong to a vendor (vendor-scoped coupon) or be NULL
     * (global, platform-wide coupon). The admin coupons screen joins on
     * this column and filters by it.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('coupons', 'vendor_id')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('code');
                $table->index('vendor_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'vendor_id')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropIndex(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }
    }
};

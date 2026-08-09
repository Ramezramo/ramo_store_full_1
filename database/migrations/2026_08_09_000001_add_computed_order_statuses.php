<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'general_order_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('general_order_status', 40)->default('pending')->after('status');
                $table->string('general_order_status_override', 40)->nullable()->after('general_order_status');
                $table->text('general_order_status_override_reason')->nullable()->after('general_order_status_override');
                $table->unsignedBigInteger('general_order_status_override_by')->nullable()->after('general_order_status_override_reason');
                $table->timestamp('general_order_status_override_at')->nullable()->after('general_order_status_override_by');
            });
        }

        if (!Schema::hasColumn('order_sub_orders', 'vendor_status')) {
            Schema::table('order_sub_orders', function (Blueprint $table) {
                $table->string('vendor_status', 40)->default('pending')->after('status');
            });
        }

        // Normalize existing values from the previous UI vocabulary.
        DB::table('orders')->where('payment_status', 'pending_payment')
            ->update(['payment_status' => 'pending_verification']);

        DB::table('order_sub_orders')->update([
            'vendor_status' => DB::raw("CASE WHEN status = 'completed' THEN 'delivered' ELSE COALESCE(status, 'pending') END"),
            'status' => DB::raw("CASE WHEN status = 'completed' THEN 'delivered' ELSE COALESCE(status, 'pending') END"),
        ]);

        // Backfill the computed status from payment and every existing shipment.
        // This keeps imported orders consistent before the first new mutation.
        $statusService = new \App\Services\OrderStatusService();
        DB::table('orders')->select('id')->orderBy('id')->pluck('id')->each(function ($orderId) use ($statusService) {
            $order = DB::table('orders')->where('id', $orderId)->first(['payment_status']);
            $statuses = DB::table('order_sub_orders')
                ->where('parent_order_id', $orderId)
                ->pluck('vendor_status')
                ->all();
            $computed = $statusService->compute($order->payment_status ?? null, $statuses);
            DB::table('orders')->where('id', $orderId)->update([
                'general_order_status' => $computed,
                'status' => $computed,
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_sub_orders', 'vendor_status')) {
            Schema::table('order_sub_orders', function (Blueprint $table) {
                $table->dropColumn('vendor_status');
            });
        }

        if (Schema::hasColumn('orders', 'general_order_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn([
                    'general_order_status',
                    'general_order_status_override',
                    'general_order_status_override_reason',
                    'general_order_status_override_by',
                    'general_order_status_override_at',
                ]);
            });
        }
    }
};
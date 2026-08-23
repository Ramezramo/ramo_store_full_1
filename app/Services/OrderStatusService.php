<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    public const PAYMENT_STATUSES = [
        'pending_verification',
        'confirmed',
        'rejected',
        'refunded',
        'failed',
    ];

    public const VENDOR_STATUSES = [
        'pending',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
        'returned',
    ];

    public const GENERAL_STATUSES = [
        'pending',
        'processing',
        'partially_shipped',
        'shipped',
        'partially_delivered',
        'completed',
        'partially_cancelled',
        'cancelled',
    ];

    /**
     * Derive the customer-facing order status from payment and every shipment.
     *
     * The order of these checks is intentional: payment always gates fulfillment,
     * and delivered/cancelled states are evaluated before shipped/processing.
     */
    public function compute(?string $paymentStatus, array $vendorStatuses): string
    {
        if ($paymentStatus !== 'confirmed') {
            return 'pending';
        }

        $statuses = array_values(array_filter(array_map(
            fn ($status) => $this->normalizeVendorStatus($status),
            $vendorStatuses
        )));

        if ($statuses === []) {
            return 'pending';
        }

        $total = count($statuses);
        $cancelled = count(array_filter($statuses, fn ($status) => $status === 'cancelled'));
        $delivered = count(array_filter($statuses, fn ($status) => $status === 'delivered'));
        $shipped = count(array_filter($statuses, fn ($status) => $status === 'shipped'));

        if ($cancelled === $total) {
            return 'cancelled';
        }

        if ($cancelled > 0) {
            return 'partially_cancelled';
        }

        if ($delivered === $total) {
            return 'completed';
        }

        if ($delivered > 0) {
            return 'partially_delivered';
        }

        if ($shipped === $total) {
            return 'shipped';
        }

        if ($shipped > 0) {
            return 'partially_shipped';
        }

        if (in_array('processing', $statuses, true)) {
            return 'processing';
        }

        return 'pending';
    }

    /**
     * Recompute and persist the computed status. The manual override, if any,
     * remains separate and is intentionally not overwritten.
     */
    public function sync(int $orderId): string
    {
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->first(['payment_status', 'status']);

        if (!$order) {
            abort(404);
        }

        $vendorStatuses = DB::table('order_sub_orders')
            ->where('parent_order_id', $orderId)
            ->pluck('vendor_status')
            ->map(fn ($status) => $this->normalizeVendorStatus($status))
            ->all();

        $computed = $this->compute($order->payment_status, $vendorStatuses);
        $now = now();

        $update = [
            'general_order_status' => $computed,
            // Keep legacy consumers in sync while they migrate to the new field.
            'date_modified' => $now,
            'updated_at' => $now,
        ];

        $orderOverride = DB::table('orders')
            ->where('id', $orderId)
            ->value('general_order_status_override');
        $newStatus = $orderOverride ?: $computed;
        $update['status'] = $newStatus;

        DB::table('orders')->where('id', $orderId)->update($update);
        app(ReferralOrderLifecycle::class)->dispatchForTransition(
            $orderId,
            $order->status,
            $newStatus,
        );

        return $computed;
    }

    public function normalizeVendorStatus(?string $status): ?string
    {
        return $status === 'completed' ? 'delivered' : $status;
    }

    public function label(string $status): string
    {
        return match ($status) {
            'partially_shipped' => 'Partially Shipped',
            'partially_delivered' => 'Partially Delivered',
            'partially_cancelled' => 'Partially Cancelled',
            default => ucwords(str_replace(['_', '-'], ' ', $status)),
        };
    }
}
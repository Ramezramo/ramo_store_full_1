<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderPaymentExpiryService
{
    public const AWAITING_PAYMENT_STATUSES = ['pending_payment', 'rejected', 'failed'];

    public static function deadlinePassed(object $order): bool
    {
        return !empty($order->payment_due_at) && now()->gte($order->payment_due_at);
    }

    /** Expire eligible unpaid orders in small batches so the scheduler stays quick. */
    public function expireDueOrders(): int
    {
        $expired = 0;

        DB::table('orders')
            ->select('id')
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<=', now())
            ->whereIn('payment_status', self::AWAITING_PAYMENT_STATUSES)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$expired): void {
                foreach ($orders as $candidate) {
                    $didExpire = DB::transaction(function () use ($candidate): bool {
                        $order = DB::table('orders')
                            ->where('id', $candidate->id)
                            ->lockForUpdate()
                            ->first();

                        return $order ? $this->expireLockedOrder($order) : false;
                    });

                    $expired += $didExpire ? 1 : 0;
                }
            });

        return $expired;
    }

    /**
     * Expire an order while its row is locked inside a transaction.
     * Returns false when payment/receipt state changed or the deadline has not passed.
     */
    public function expireLockedOrder(object $order): bool
    {
        if (
            !in_array($order->payment_status ?? null, self::AWAITING_PAYMENT_STATUSES, true)
            || !self::deadlinePassed($order)
            || in_array($order->status ?? null, ['cancelled', 'canceled', 'refunded', 'completed', 'delivered'], true)
            || !empty($order->set_paid)
        ) {
            return false;
        }

        $now = now();
        $lineItems = is_string($order->line_items ?? null)
            ? (json_decode($order->line_items, true) ?: [])
            : ((array) ($order->line_items ?? []));
        $stockReturns = [];

        foreach ($lineItems as $item) {
            $variationId = (int) ($item['variation_id'] ?? 0);
            $quantity = max(0, (int) ($item['quantity'] ?? 0));
            if ($variationId > 0 && $quantity > 0) {
                $stockReturns[$variationId] = ($stockReturns[$variationId] ?? 0) + $quantity;
            }
        }

        ksort($stockReturns);
        foreach ($stockReturns as $variationId => $quantity) {
            DB::table('product_variations')
                ->where('id', $variationId)
                ->increment('stock_quantity', $quantity, [
                    'updated_at' => $now,
                ]);
        }

        // Release coupon usage because the unpaid order will no longer consume it.
        if (!empty($order->coupon_applied) && !empty($order->coupon_code)) {
            $coupon = DB::table('coupons')->where('code', $order->coupon_code)->first(['id']);
            if ($coupon) {
                DB::table('coupons')->where('id', $coupon->id)->update([
                    'usage_count' => DB::raw('CASE WHEN usage_count > 0 THEN usage_count - 1 ELSE 0 END'),
                    'date_modified' => $now,
                ]);

                if (!empty($order->customer_id)) {
                    DB::table('coupon_user_limits')
                        ->where('coupon_id', $coupon->id)
                        ->where('user_id', $order->customer_id)
                        ->update([
                            'use_count' => DB::raw('CASE WHEN use_count > 0 THEN use_count - 1 ELSE 0 END'),
                            'updated_at' => $now,
                        ]);
                }
            }
        }

        $subOrders = DB::table('order_sub_orders')
            ->where('parent_order_id', $order->id)
            ->lockForUpdate()
            ->get(['id', 'timeline']);

        foreach ($subOrders as $subOrder) {
            $timeline = is_string($subOrder->timeline)
                ? (json_decode($subOrder->timeline, true) ?: [])
                : ((array) ($subOrder->timeline ?? []));
            $timeline[] = [
                'status' => 'cancelled',
                'note' => 'Automatically cancelled because payment was not completed within 24 hours.',
                'at' => $now->toDateTimeString(),
            ];

            DB::table('order_sub_orders')->where('id', $subOrder->id)->update([
                'status' => 'cancelled',
                'vendor_status' => 'cancelled',
                'timeline' => json_encode($timeline),
                'updated_at' => $now,
            ]);
        }

        $timeline = is_string($order->timeline ?? null)
            ? (json_decode($order->timeline, true) ?: [])
            : ((array) ($order->timeline ?? []));
        $timeline[] = [
            'status' => 'expired',
            'note' => 'Automatically cancelled because payment was not completed within 24 hours.',
            'at' => $now->toDateTimeString(),
        ];

        DB::table('orders')->where('id', $order->id)->update([
            'payment_status' => 'expired',
            'status' => 'cancelled',
            'general_order_status' => 'cancelled',
            'needs_payment' => false,
            'needs_processing' => false,
            'set_paid' => false,
            'is_editable' => false,
            'timeline' => json_encode($timeline),
            'date_modified' => $now,
            'updated_at' => $now,
        ]);

        return true;
    }
}

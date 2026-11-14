<?php

namespace App\Services;

use App\Models\SubOrder;
use App\Models\VendorUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Vendors control only their shipment; the platform computes the parent order. */
class VendorShipmentService
{
    public function update(VendorUser $vendor, int $shipmentId, string $target, array $details = []): SubOrder
    {
        abort_unless($vendor->status === 'approved', 403, 'This store is not approved.');
        $target = match ($target) {
            'completed' => 'delivered', 'packed' => 'processing', 'out_for_delivery' => 'shipped',
            default => $target,
        };
        return DB::transaction(function () use ($vendor, $shipmentId, $target, $details): SubOrder {
            $identity = DB::table('order_sub_orders')->where('id', $shipmentId)->where('vendor_id', $vendor->id)->first();
            abort_unless($identity, 404);
            $order = DB::table('orders')->where('id', $identity->parent_order_id)->lockForUpdate()->first();
            abort_unless($order, 404);
            $shipment = SubOrder::whereKey($shipmentId)->where('vendor_id', $vendor->id)->lockForUpdate()->firstOrFail();
            $current = $shipment->vendor_status ?: $shipment->status;
            $current = $current === 'completed' ? 'delivered' : $current;
            if ($current === $target) return $shipment;
            $transitions = [
                'pending' => ['processing', 'shipped', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered', 'returned'],
                'delivered' => ['returned'], 'cancelled' => [], 'returned' => [],
            ];
            if (! in_array($target, $transitions[$current] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'This shipment status transition is not allowed.']);
            }
            if ($target !== 'cancelled' && $order->payment_status !== 'confirmed') {
                throw ValidationException::withMessages(['status' => 'Payment must be confirmed before processing this shipment.']);
            }
            $timeline = is_array($shipment->timeline) ? $shipment->timeline : [];
            $timeline[] = [
                'status' => $target, 'note' => strip_tags((string) ($details['note'] ?? '')),
                'by' => 'vendor:'.$vendor->id, 'at' => now()->toDateTimeString(),
            ];
            // SubOrder guards mass assignment; these fields are server-controlled.
            $shipment->forceFill([
                'status' => $target, 'vendor_status' => $target, 'timeline' => $timeline,
                'tracking_number' => $details['tracking_number'] ?? $shipment->tracking_number,
                'tracking_carrier' => $details['tracking_carrier'] ?? $details['carrier'] ?? $shipment->tracking_carrier,
            ])->save();
            app(OrderStatusService::class)->sync((int) $order->id);

            return $shipment->fresh();
        });
    }
}

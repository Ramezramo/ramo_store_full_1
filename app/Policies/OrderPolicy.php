<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Allow a customer to view only their own order.
     */
    public function view(User $user, Order $order): bool
    {
        return (int) $order->customer_id === (int) $user->id;
    }

    /**
     * Allow a vendor to manage an order when their ID is listed on the order.
     * The untyped user parameter supports both the API and vendor-web guards.
     */
    public function vendor($user, Order $order): bool
    {
        if (! $user || ! isset($user->id)) {
            return false;
        }

        $vendorIds = $order->parent_vendors_ids;
        if (is_string($vendorIds)) {
            $decoded = json_decode($vendorIds, true);
            $vendorIds = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($vendorIds)) {
            return false;
        }

        return in_array((int) $user->id, array_map('intval', $vendorIds), true);
    }
}

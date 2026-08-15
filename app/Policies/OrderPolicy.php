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
}

<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    /**
     * Allow a customer to manage only their own cart item.
     */
    public function manage(User $user, CartItem $item): bool
    {
        return (int) $item->user_id === (int) $user->id;
    }
}

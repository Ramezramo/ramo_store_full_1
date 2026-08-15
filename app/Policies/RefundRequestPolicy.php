<?php

namespace App\Policies;

use App\Models\RefundRequest;
use App\Models\User;

class RefundRequestPolicy
{
    /**
     * Allow a customer to view only their own refund request.
     */
    public function view(User $user, RefundRequest $refund): bool
    {
        return (int) $refund->customer_id === (int) $user->id;
    }

    /**
     * Allow a customer to cancel only their own pending refund request.
     */
    public function cancel(User $user, RefundRequest $refund): bool
    {
        return $this->view($user, $refund) && $refund->status === 'pending';
    }
}

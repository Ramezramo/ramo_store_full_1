<?php

namespace App\Policies;

use App\Models\RefundRequest;
use App\Models\User;
use App\Models\VendorUser;

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

    /**
     * Allow a vendor to view or manage only refunds assigned to that vendor.
     */
    public function manageAsVendor(VendorUser $vendor, RefundRequest $refund): bool
    {
        return $refund->vendor_id !== null
            && (int) $refund->vendor_id === (int) $vendor->getKey();
    }
}

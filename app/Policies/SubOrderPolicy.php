<?php

namespace App\Policies;

use App\Models\SubOrder;
use App\Models\VendorUser;

class SubOrderPolicy
{
    public function view(VendorUser $vendor, SubOrder $subOrder): bool
    {
        return $this->owns($vendor, $subOrder);
    }

    public function update(VendorUser $vendor, SubOrder $subOrder): bool
    {
        return $this->owns($vendor, $subOrder);
    }

    public function reviewPayment(VendorUser $vendor, SubOrder $subOrder): bool
    {
        return $this->owns($vendor, $subOrder);
    }

    private function owns(VendorUser $vendor, SubOrder $subOrder): bool
    {
        return $subOrder->vendor_id !== null
            && (int) $subOrder->vendor_id === (int) $vendor->getKey();
    }
}

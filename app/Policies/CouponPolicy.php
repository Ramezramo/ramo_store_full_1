<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\VendorUser;

class CouponPolicy
{
    public function view(VendorUser $vendor, Coupon $coupon): bool
    {
        return $this->owns($vendor, $coupon);
    }

    public function update(VendorUser $vendor, Coupon $coupon): bool
    {
        return $this->owns($vendor, $coupon);
    }

    public function delete(VendorUser $vendor, Coupon $coupon): bool
    {
        return $this->owns($vendor, $coupon);
    }

    private function owns(VendorUser $vendor, Coupon $coupon): bool
    {
        return $coupon->vendor_id !== null
            && (int) $coupon->vendor_id === (int) $vendor->id;
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use App\Support\EgyptianPhoneNumber;
use Illuminate\Support\Facades\DB;

/** Live, authoritative checks shared by award creation and administrator approval. */
class ReferralEligibility
{
    public function orderIssue(Order $order): ?string
    {
        if ($order->status !== 'completed') return 'order_not_completed';
        if ($order->payment_status !== 'confirmed') return 'payment_not_confirmed';
        if (! $order->platform_payment_verified_at || ! $order->platform_payment_verified_by) return 'platform_payment_verification_required';
        if (! $order->customer_id || ! is_finite((float) $order->final_total) || (float) $order->final_total <= 0) {
            return 'invalid_order_value';
        }

        $shipments = DB::table('order_sub_orders')->where('parent_order_id', $order->id)->get();
        foreach ($shipments as $shipment) {
            $status = $shipment->vendor_status ?? $shipment->status;
            if (! in_array($status, ['delivered', 'completed'], true)) return 'fulfillment_not_completed';
        }
        if (DB::table('refund_requests')->where('order_id', $order->id)->where('status', 'completed')->exists()) {
            return 'order_refunded';
        }
        if (DB::table('refund_requests')->where('order_id', $order->id)->whereIn('status', ['pending', 'approved'])->exists()) {
            return 'refund_under_review';
        }

        return null;
    }

    public function referralIssue(Referral $referral, Order $order): ?string
    {
        if (! in_array($referral->status, ['pending', 'qualified'], true)) return 'referral_ineligible';
        if ((int) $referral->referred_id !== (int) $order->customer_id
            || (int) $referral->referred_id === (int) $referral->referrer_id) return 'invalid_referral_identity';
        $referrer = User::find($referral->referrer_id);
        $customer = User::find($referral->referred_id);
        if (! $referrer || ! $customer || $referrer->is_blocked || $customer->is_blocked) return 'account_unavailable';
        if ($customer->referred_by && (int) $customer->referred_by !== (int) $referrer->id) return 'attribution_mismatch';
        $verifiedPhone = $customer->is_phone_verified && EgyptianPhoneNumber::normalize((string) $customer->phone);
        $verifiedEmail = $customer->email_verified_at && filter_var($customer->email, FILTER_VALIDATE_EMAIL)
            && ! str_ends_with(strtolower($customer->email), '@ramostore.local')
            && ! str_ends_with(strtolower($customer->email), '@phone.user');
        if (! $verifiedPhone && ! $verifiedEmail) return 'identity_verification_required';

        $fraud = app(ReferralFraudChecker::class)->evaluate($referrer, $customer);
        if ($fraud['hard_reject']) return substr(implode(',', $fraud['reasons']), 0, 100);
        if (app(ReferralFraudChecker::class)->sameShippingAddress($referrer->shipping, $order->shipping)) {
            return 'shipping_address_matches_referrer';
        }
        if ($this->couponBelongsToReferrer($order, $referrer)) return 'referrer_coupon_used';

        return null;
    }

    private function couponBelongsToReferrer(Order $order, User $referrer): bool
    {
        if (! $order->coupon_code) return false;
        $vendor = DB::table('coupons as c')->join('vendor_users as v', 'v.id', '=', 'c.vendor_id')
            ->whereRaw('LOWER(c.code) = ?', [strtolower(trim($order->coupon_code))])->first(['v.email', 'v.phone']);
        if (! $vendor) return false;
        if ($referrer->email && strtolower(trim($referrer->email)) === strtolower(trim($vendor->email))) return true;
        $phone = EgyptianPhoneNumber::normalize((string) $referrer->phone);
        return $phone !== null && $phone === EgyptianPhoneNumber::normalize((string) $vendor->phone);
    }
}

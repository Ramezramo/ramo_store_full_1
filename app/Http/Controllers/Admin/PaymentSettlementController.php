<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReferralEligibility;
use App\Services\ReferralOrderLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentSettlementController extends Controller
{
    public function store(Request $request, int $id)
    {
        $data = $request->validate(['payment_received' => ['required', 'accepted'], 'reference' => ['required', 'string', 'max:255']]);
        DB::transaction(function () use ($id, $data): void {
            $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($order->platform_payment_verified_at) return;
            $trial = clone $order;
            $trial->platform_payment_verified_at = now();
            $trial->platform_payment_verified_by = auth()->id();
            if ($issue = app(ReferralEligibility::class)->orderIssue($trial)) {
                throw ValidationException::withMessages(['payment_received' => 'The order must be paid, delivered and free of unresolved refunds before collection can be confirmed.']);
            }
            $timeline = is_array($order->timeline) ? $order->timeline : [];
            $timeline[] = ['event' => 'platform_payment_verified', 'by' => 'admin:'.auth()->id(),
                'reference' => trim($data['reference']), 'timestamp' => now()->toIso8601String()];
            $order->forceFill([
                'platform_payment_verified_at' => now(), 'platform_payment_verified_by' => auth()->id(),
                'payment_reviewed_at' => now(), 'payment_reviewed_by' => auth()->id(),
                'date_paid' => $order->date_paid ?: now(), 'timeline' => $timeline,
            ])->save();
            app(ReferralOrderLifecycle::class)->dispatchForTransition($id, $order->status, $order->status);
        });
        return back()->with('success', 'Collected payment confirmed. Eligible referral rewards will be processed.');
    }
}

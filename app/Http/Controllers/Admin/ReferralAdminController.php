<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\ReferralCommissionAudit;
use App\Models\Order;
use App\Models\User;
use App\Services\ReferralFraudChecker;
use App\Services\ReferralEligibility;
use App\Services\ReferralSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReferralAdminController extends Controller
{
    public function __construct(
        private ReferralSettingsService $settings,
        private ReferralFraudChecker $fraudChecker,
    ) {
    }

    public function index()
    {
        return view('admin.referral', [
            'settings' => $this->settings->get(),
            'users' => User::query()->select(['id', 'name', 'email', 'phone', 'referred_by'])->orderBy('id')->limit(500)->get(),
            'referrals' => Referral::with([
                'referrer:id,name,email,phone',
                'referred:id,name,email,phone',
                'commission',
            ])->latest()->paginate(25),
            'commissions' => ReferralCommission::with([
                'referral.referrer:id,name,email',
                'referral.referred:id,name,email',
                'order:id,status,final_total',
            ])->latest()->paginate(25, ['*'], 'commission_page'),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'referral_enabled' => ['nullable', 'boolean'],
            'referral_min_order_amount' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'referral_commission_type' => ['required', 'in:flat,percentage'],
            'referral_commission_value' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'referral_commission_scope' => ['required', 'in:first_order,all_orders'],
            'referral_expiry_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        if ($validated['referral_commission_type'] === 'percentage' && (float) $validated['referral_commission_value'] > 100) {
            return back()->withInput()->withErrors([
                'referral_commission_value' => 'النسبة لازم تكون بين 0 و100.',
            ]);
        }

        $this->settings->save([
            'referral_enabled' => $request->boolean('referral_enabled'),
            'referral_min_order_amount' => (float) $validated['referral_min_order_amount'],
            'referral_commission_type' => $validated['referral_commission_type'],
            'referral_commission_value' => (float) $validated['referral_commission_value'],
            'referral_commission_scope' => $validated['referral_commission_scope'],
            'referral_expiry_days' => (int) ($validated['referral_expiry_days'] ?? $this->settings->expiryDays()),
        ]);

        return back()->with('success', 'Referral settings saved successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'referrer_id' => ['required', 'integer', 'exists:users,id', 'different:referred_id'],
            'referred_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        return DB::transaction(function () use ($data): RedirectResponse {
            // Serialize first-touch assignment before checking for an existing link.
            $referred = User::whereKey($data['referred_id'])->lockForUpdate()->firstOrFail();
            $referrer = User::findOrFail($data['referrer_id']);
            if (Referral::where('referred_id', $referred->id)->exists()) {
                return back()->withInput()->withErrors(['referred_id' => 'This customer already has a referral.']);
            }
            if ($referred->referred_by && (int) $referred->referred_by !== (int) $referrer->id) {
                return back()->withInput()->withErrors(['referred_id' => 'An existing referral attribution cannot be changed.']);
            }
            $fraud = $this->fraudChecker->evaluate($referrer, $referred);
            if (! $fraud['hard_reject'] && ! $referred->referred_by) {
                $referred->referred_by = $referrer->id;
                $referred->save();
            }
            $reason = $fraud['hard_reject'] ? implode(',', $fraud['reasons']) : $fraud['review_reason'];
            Referral::create([
                'referrer_id' => $referrer->id, 'referred_id' => $referred->id,
                'status' => $fraud['hard_reject'] ? 'rejected' : 'pending',
                'rejection_reason' => $reason ? substr($reason, 0, 100) : null,
            ]);
            return back()->with('success', 'Referral link added successfully.');
        });
    }

    public function update(Request $request, Referral $referral): RedirectResponse
    {
        $data = $request->validate([
            'referrer_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', 'in:pending,qualified,rejected,expired'],
        ]);
        return DB::transaction(function () use ($referral, $data): RedirectResponse {
            [$order, $locked] = $this->lockReferral($referral->id);
            if ($locked->commissions()->lockForUpdate()->get(['id'])->isNotEmpty()) {
                return back()->withErrors(['referral' => 'A referral with financial records cannot be edited.']);
            }
            $referred = $locked->referred()->firstOrFail();
            $referrer = User::findOrFail($data['referrer_id']);
            if ((int) $referrer->id === (int) $referred->id) {
                return back()->withErrors(['referral' => 'Self-referrals are not allowed.']);
            }
            $changed = (int) $locked->referrer_id !== (int) $referrer->id;
            if ($changed && $referred->referred_by) {
                return back()->withErrors(['referral' => 'An existing referral attribution cannot be changed.']);
            }
            // A status edit cannot bypass identity checks on a previously rejected link.
            $fraud = $this->fraudChecker->evaluate($referrer, $referred);
            $status = $data['status'];
            $reason = $status === 'rejected' ? 'admin_rejected' : $fraud['review_reason'];
            if ($fraud['hard_reject']) {
                $status = 'rejected';
                $reason = substr(implode(',', $fraud['reasons']), 0, 100);
            }
            if ($status === 'qualified') {
                $eligibility = app(ReferralEligibility::class);
                if (! $order || $eligibility->orderIssue($order) || $eligibility->referralIssue($locked, $order)) {
                    return back()->withErrors(['referral' => 'A qualifying paid and delivered order is required.']);
                }
            }
            if ($changed && ! $fraud['hard_reject']) {
                $referred->referred_by = $referrer->id;
                $referred->save();
            }
            $locked->update(['referrer_id' => $referrer->id, 'status' => $status, 'rejection_reason' => $reason]);
            return back()->with('success', 'Referral updated successfully.');
        });
    }

    public function destroy(Referral $referral): RedirectResponse
    {
        return DB::transaction(function () use ($referral): RedirectResponse {
            [, $locked] = $this->lockReferral($referral->id);
            if ($locked->commissions()->lockForUpdate()->get(['id'])->isNotEmpty()) {
                return back()->withErrors(['referral' => 'A referral with financial records cannot be cancelled here.']);
            }
            $locked->update(['status' => 'rejected', 'rejection_reason' => 'admin_cancelled']);
            return back()->with('success', 'Referral cancelled successfully.');
        });
    }

    public function approveCommission(ReferralCommission $commission): RedirectResponse
    {
        return DB::transaction(function () use ($commission): RedirectResponse {
            [$order, $referral, $locked] = $this->lockCommission($commission->id);
            if ($locked->status !== 'pending') {
                return back()->withErrors(['commission' => 'This commission is no longer pending. Refresh the page.']);
            }
            $eligibility = app(ReferralEligibility::class);
            if (! $order || ! $referral || $eligibility->orderIssue($order) || $eligibility->referralIssue($referral, $order)) {
                return back()->withErrors(['commission' => 'This order or referral is no longer eligible for commission approval.']);
            }
            $this->changeCommission($locked, ['status' => 'approved', 'approved_at' => now()], ['pending'], 'approved', 'admin_approved');
            return back()->with('success', 'Commission approved.');
        });
    }

    public function rejectCommission(ReferralCommission $commission): RedirectResponse
    {
        return DB::transaction(function () use ($commission): RedirectResponse {
            [, , $locked] = $this->lockCommission($commission->id);
            if (! in_array($locked->status, ['pending', 'approved'], true)) {
                return back()->withErrors(['commission' => 'This commission can no longer be rejected. Refresh the page.']);
            }
            $this->changeCommission($locked, [
                'status' => 'rejected', 'approved_at' => null, 'clawback_reason' => 'admin_rejected',
            ], ['pending', 'approved'], 'rejected', 'admin_rejected');
            return back()->with('success', 'Commission rejected.');
        });
    }

    public function updateCommission(Request $request, ReferralCommission $commission): RedirectResponse
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99']]);
        return DB::transaction(function () use ($commission, $data): RedirectResponse {
            [, , $locked] = $this->lockCommission($commission->id);
            if ($locked->status !== 'pending') {
                return back()->withErrors(['commission' => 'Only a pending commission can be edited. Refresh the page.']);
            }
            $amount = round((float) $data['amount'], 2);
            if (abs((float) $locked->amount - $amount) >= 0.005) {
                $this->changeCommission($locked, ['amount' => $amount], ['pending'], 'amount_changed', 'admin_adjustment');
            }
            return back()->with('success', 'Commission amount updated.');
        });
    }

    /** Order -> referral -> commission is also the queue jobs' lock order. */
    private function lockCommission(int $id): array
    {
        $ids = ReferralCommission::findOrFail($id);
        $order = Order::whereKey($ids->order_id)->lockForUpdate()->first();
        $referral = Referral::whereKey($ids->referral_id)->lockForUpdate()->first();
        $commission = ReferralCommission::whereKey($id)->lockForUpdate()->firstOrFail();
        if ((int) $commission->order_id !== (int) $ids->order_id || (int) $commission->referral_id !== (int) $ids->referral_id) {
            throw ValidationException::withMessages(['commission' => 'This commission changed. Refresh the page.']);
        }
        return [$order, $referral, $commission];
    }

    private function lockReferral(int $id): array
    {
        $ids = Referral::findOrFail($id);
        $order = $ids->qualifying_order_id ? Order::whereKey($ids->qualifying_order_id)->lockForUpdate()->first() : null;
        $referral = Referral::whereKey($id)->lockForUpdate()->firstOrFail();
        if ((int) $referral->qualifying_order_id !== (int) $ids->qualifying_order_id) {
            throw ValidationException::withMessages(['referral' => 'This referral changed. Refresh the page.']);
        }
        return [$order, $referral];
    }

    private function changeCommission(ReferralCommission $commission, array $changes, array $allowed, string $action, string $reason): void
    {
        $before = $commission->only(['status', 'amount']);
        $changed = ReferralCommission::whereKey($commission->id)->whereIn('status', $allowed)->update($changes);
        if ($changed !== 1) {
            throw ValidationException::withMessages(['commission' => 'This commission changed. Refresh the page.']);
        }
        $commission->refresh();
        ReferralCommissionAudit::record($commission, $action, $before, Auth::id(), $reason);
    }
}

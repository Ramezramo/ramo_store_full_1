<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\User;
use App\Services\ReferralFraudChecker;
use App\Services\ReferralSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'referral_commission_value' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'referral_commission_scope' => ['required', 'in:first_order,all_orders'],
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
        ]);

        return back()->with('success', 'Referral settings saved successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'referrer_id' => ['required', 'integer', 'exists:users,id', 'different:referred_id'],
            'referred_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if (Referral::where('referred_id', $validated['referred_id'])->exists()) {
            return back()->withInput()->withErrors([
                'referred_id' => 'المستخدم ده مرتبط بإحالة موجودة بالفعل.',
            ]);
        }

        $referrer = User::findOrFail($validated['referrer_id']);
        $referred = User::findOrFail($validated['referred_id']);
        if ($referred->referred_by && (int) $referred->referred_by !== (int) $referrer->id) {
            return back()->withInput()->withErrors([
                'referred_id' => 'المستخدم ده مرتبط بمُحيل مختلف ومينفعش يتغير من هنا.',
            ]);
        }

        $fraud = $this->fraudChecker->evaluate($referrer, $referred);
        DB::transaction(function () use ($referrer, $referred, $fraud): void {
            $status = $fraud['hard_reject'] ? 'rejected' : 'pending';
            $reason = $fraud['hard_reject']
                ? implode(',', $fraud['reasons'])
                : $fraud['review_reason'];

            if (! $fraud['hard_reject'] && ! $referred->referred_by) {
                $referred->referred_by = $referrer->id;
                $referred->save();
            }

            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $referred->id,
                'status' => $status,
                'rejection_reason' => $reason ? substr($reason, 0, 100) : null,
            ]);
        });

        return back()->with('success', 'Referral link added successfully.');
    }

    public function update(Request $request, Referral $referral): RedirectResponse
    {
        $validated = $request->validate([
            'referrer_id' => ['required', 'integer', 'exists:users,id', 'different:referred_id'],
            'status' => ['required', 'in:pending,qualified,rejected,expired'],
        ]);

        if ($referral->commission) {
            return back()->withErrors(['referral' => 'مينفعش تعدّل إحالة مرتبطة بأي عمولة.']);
        }

        $referred = $referral->referred()->firstOrFail();
        $referrer = User::findOrFail($validated['referrer_id']);
        $referrerChanged = (int) $referral->referrer_id !== (int) $referrer->id;
        $fraud = null;

        if ($referrerChanged) {
            // First-touch attribution is immutable once the customer is linked.
            if ($referred->referred_by) {
                return back()->withErrors(['referral' => 'مينفعش تغيّر المُحيل بعد تسجيل attribution للعميل.']);
            }
            $fraud = $this->fraudChecker->evaluate($referrer, $referred);
            if (! $fraud['hard_reject'] && $validated['status'] === 'qualified' && ! $referral->qualifying_order_id) {
                return back()->withErrors(['referral' => 'مينفعش تخلي الإحالة qualified من غير طلب مؤهل.']);
            }
        }

        if ($validated['status'] === 'qualified' && ! $referral->qualifying_order_id) {
            return back()->withErrors(['referral' => 'مينفعش تخلي الإحالة qualified من غير طلب مؤهل.']);
        }

        DB::transaction(function () use ($referral, $referred, $referrer, $referrerChanged, $fraud, $validated): void {
            $status = $validated['status'];
            $reason = $status === 'rejected' ? 'admin_rejected' : null;

            if ($referrerChanged && $fraud && $fraud['hard_reject']) {
                $status = 'rejected';
                $reason = substr(implode(',', $fraud['reasons']), 0, 100);
            } elseif ($referrerChanged && $fraud && ! $fraud['hard_reject']) {
                $referred->referred_by = $referrer->id;
                $referred->save();
            }

            $referral->update([
                'referrer_id' => $referrer->id,
                'status' => $status,
                'rejection_reason' => $reason,
            ]);
        });

        return back()->with('success', 'Referral updated successfully.');
    }

    public function destroy(Referral $referral): RedirectResponse
    {
        if ($referral->commission) {
            return back()->withErrors(['referral' => 'لازم تراجع أو ترفض العمولة الأول قبل إلغاء الإحالة.']);
        }

        $referral->update([
            'status' => 'rejected',
            'rejection_reason' => 'admin_cancelled',
        ]);

        return back()->with('success', 'Referral cancelled successfully.');
    }

    public function approveCommission(ReferralCommission $commission): RedirectResponse
    {
        if ($commission->status !== 'pending') {
            return back()->withErrors(['commission' => 'العمولة دي مش في حالة تسمح بالموافقة.']);
        }

        $commission->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Commission approved.');
    }

    public function rejectCommission(ReferralCommission $commission): RedirectResponse
    {
        if (! in_array($commission->status, ['pending', 'approved'], true)) {
            return back()->withErrors(['commission' => 'العمولة دي مش في حالة تسمح بالرفض.']);
        }

        $commission->update([
            'status' => 'rejected',
            'approved_at' => null,
            'clawback_reason' => 'admin_rejected',
        ]);

        return back()->with('success', 'Commission rejected.');
    }

    public function updateCommission(Request $request, ReferralCommission $commission): RedirectResponse
    {
        if ($commission->status !== 'pending') {
            return back()->withErrors(['commission' => 'قيمة العمولة تتعدل وهي pending بس.']);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
        ]);

        $commission->update(['amount' => round((float) $validated['amount'], 2)]);

        return back()->with('success', 'Commission amount updated.');
    }
}

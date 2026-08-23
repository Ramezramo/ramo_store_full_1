@extends('web.account.layout')

@php($pageTitle = session('locale') === 'ar' ? 'برنامج الإحالة' : 'Referral Program')

@section('account-content')
@php($isAr = session('locale') === 'ar')
@php($referralStatusLabels = [
  'pending' => $isAr ? 'قيد المراجعة' : 'Pending review',
  'qualified' => $isAr ? 'مؤهلة' : 'Qualified',
  'rejected' => $isAr ? 'غير مؤهلة' : 'Not eligible',
  'expired' => $isAr ? 'منتهية' : 'Expired',
])
<style>
.referral-user-card{background:linear-gradient(135deg,#fff7ef,#fff);border:1px solid #ffe0c7;border-radius:18px;padding:22px;box-shadow:0 8px 24px rgba(199,102,42,.08)}
.referral-user-card h1{margin:0 0 7px;font-size:22px;color:#222}.referral-user-card p{margin:0;color:#786f69;line-height:1.6;font-size:13px}
.referral-link-box{display:flex;gap:8px;margin-top:18px}.referral-link-box input{flex:1;min-width:0;border:1px solid #f0c9aa;border-radius:10px;padding:11px 12px;color:#5b3a29;background:#fff;font-size:12px;direction:ltr}.referral-link-box button{border:0;border-radius:10px;background:#f06a22;color:#fff;padding:0 16px;font-weight:700;cursor:pointer}.referral-code{display:inline-block;margin-top:13px;padding:7px 11px;border-radius:8px;background:#fff0e5;color:#b84f13;font-weight:800;letter-spacing:.08em;direction:ltr}
.referral-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:16px 0}.referral-stat{background:#fff;border:1px solid #eee5df;border-radius:13px;padding:14px}.referral-stat strong{display:block;font-size:22px;color:#f06a22}.referral-stat span{display:block;margin-top:4px;color:#817871;font-size:12px}
.referral-user-table{width:100%;border-collapse:collapse;margin-top:10px;font-size:13px}.referral-user-table th,.referral-user-table td{padding:12px 8px;border-bottom:1px solid #f0ebe7;text-align:start}.referral-user-table th{color:#8b817b;font-size:11px}.referral-status{display:inline-block;padding:4px 8px;border-radius:999px;background:#f4f1ef;color:#6f625b;font-size:11px;font-weight:700}.referral-status.qualified{background:#e9f8ef;color:#167343}.referral-status.rejected{background:#fff0f0;color:#b42318}
@media(max-width:600px){.referral-user-card{padding:17px}.referral-link-box{display:block}.referral-link-box input{width:100%;box-sizing:border-box}.referral-link-box button{width:100%;height:42px;margin-top:8px}.referral-stats{gap:8px}.referral-stat{padding:11px}.referral-stat strong{font-size:19px}.referral-user-table{font-size:12px}}
</style>

<div class="referral-user-card" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <h1>{{ $isAr ? 'شارك واربح' : 'Share and earn' }}</h1>
  <p>{{ $isAr ? 'ابعت رابطك لحد من أصحابك. لما يعمل أول طلب مكتمل مستوفي الحد الأدنى، الإحالة تدخل المراجعة والعمولة يحددها الأدمن.' : 'Share your link with a friend. When their first completed order meets the minimum, the referral enters review and the admin-controlled commission is created.' }}</p>
  <div class="referral-link-box"><input id="referral-link" type="text" readonly value="{{ route('register', ['ref' => $user->referral_code]) }}"><button type="button" id="copy-referral-link">{{ $isAr ? 'نسخ الرابط' : 'Copy link' }}</button></div>
  <div class="referral-code">{{ $user->referral_code }}</div>
</div>

<div class="referral-stats" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <div class="referral-stat"><strong>{{ $referrals->count() }}</strong><span>{{ $isAr ? 'إجمالي الإحالات' : 'Total referrals' }}</span></div>
  <div class="referral-stat"><strong>{{ $qualifiedCount }}</strong><span>{{ $isAr ? 'إحالات مؤهلة' : 'Qualified referrals' }}</span></div>
  <div class="referral-stat"><strong>{{ number_format($pendingCommissionTotal, 2) }}</strong><span>{{ $isAr ? 'عمولات تحت المراجعة (ج.م)' : 'Pending commissions (EGP)' }}</span></div>
  <div class="referral-stat"><strong>{{ $referrals->where('status', 'pending')->count() }}</strong><span>{{ $isAr ? 'تحت المراجعة' : 'Under review' }}</span></div>
</div>

<div class="referral-user-card" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="padding:17px">
  <h2 style="margin:0;font-size:17px">{{ $isAr ? 'الإحالات الأخيرة' : 'Recent referrals' }}</h2>
  <div style="overflow:auto">
    <table class="referral-user-table"><thead><tr><th>{{ $isAr ? 'المستخدم' : 'Customer' }}</th><th>{{ $isAr ? 'الحالة' : 'Status' }}</th><th>{{ $isAr ? 'العمولة' : 'Commission' }}</th></tr></thead><tbody>
    @forelse($referrals as $referral)
      @php($statusKey = strtolower((string) $referral->status))
      <tr><td>{{ $isAr ? 'إحالة مسجلة' : 'Referred customer' }}</td><td><span class="referral-status {{ $statusKey }}">{{ $referralStatusLabels[$statusKey] ?? ($isAr ? 'حالة مسجلة' : 'Recorded') }}</span></td><td>{{ $referral->commission ? number_format((float) $referral->commission->amount, 2).' EGP' : '—' }}</td></tr>
    @empty<tr><td colspan="3">{{ $isAr ? 'لسه مفيش إحالات.' : 'No referrals yet.' }}</td></tr>@endforelse
    </tbody></table>
  </div>
</div>

<script>
document.getElementById('copy-referral-link')?.addEventListener('click', async function () {
  const input = document.getElementById('referral-link');
  try { await navigator.clipboard.writeText(input.value); this.textContent = @json($isAr ? 'اتنسخ' : 'Copied'); setTimeout(() => { this.textContent = @json($isAr ? 'نسخ الرابط' : 'Copy link'); }, 1800); }
  catch { input.select(); document.execCommand('copy'); this.textContent = @json($isAr ? 'اتنسخ' : 'Copied'); }
});
</script>
@endsection

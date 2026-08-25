@extends('web.account.layout')

@php
  $pageTitle = session('locale') === 'ar' ? 'برنامج الإحالة' : 'Referral Program';
@endphp

@section('account-content')
@php
  $isAr = session('locale') === 'ar';
  $referralStatusLabels = [
    'pending' => $isAr ? 'في انتظار أول طلب' : 'Waiting for first order',
    'qualified' => $isAr ? 'مؤهلة للمراجعة' : 'Qualified for review',
    'rejected' => $isAr ? 'غير مؤهلة' : 'Not eligible',
    'expired' => $isAr ? 'منتهية' : 'Expired',
  ];
@endphp
@php
  $minimumOrder = (float) ($referralSettings['referral_min_order_amount'] ?? 700);
  $commissionType = $referralSettings['referral_commission_type'] ?? 'percentage';
  $commissionValue = (float) ($referralSettings['referral_commission_value'] ?? 5);
  $exampleCommission = $commissionType === 'flat'
    ? $commissionValue
    : round($minimumOrder * $commissionValue / 100, 2);
  $referralEnabled = (bool) ($referralSettings['referral_enabled'] ?? false);
  $commissionScope = $referralSettings['referral_commission_scope'] ?? 'first_order';
  $allOrders = $commissionScope === 'all_orders';
@endphp
<style>
.referral-user-card{background:linear-gradient(135deg,#fff7ef,#fff);border:1px solid #ffe0c7;border-radius:18px;padding:22px;box-shadow:0 8px 24px rgba(199,102,42,.08)}
.referral-user-card h1{margin:0 0 7px;font-size:22px;color:#222}.referral-user-card p{margin:0;color:#786f69;line-height:1.6;font-size:13px}
.referral-link-box{display:flex;gap:8px;margin-top:18px}.referral-link-box input{flex:1;min-width:0;border:1px solid #f0c9aa;border-radius:10px;padding:11px 12px;color:#5b3a29;background:#fff;font-size:12px;direction:ltr}.referral-link-box button{border:0;border-radius:10px;background:#f06a22;color:#fff;padding:0 16px;font-weight:700;cursor:pointer}.referral-code{display:inline-block;margin-top:13px;padding:7px 11px;border-radius:8px;background:#fff0e5;color:#b84f13;font-weight:800;letter-spacing:.08em;direction:ltr}
.referral-earnings-card{display:flex;align-items:center;justify-content:space-between;gap:16px;background:linear-gradient(135deg,#fff1e5,#fffaf6);border:1px solid #ffcda9;border-radius:18px;padding:18px 20px;margin:16px 0;box-shadow:0 8px 24px rgba(199,102,42,.10)}.referral-earnings-card .eyebrow{display:block;color:#8b5a3c;font-size:13px;font-weight:700}.referral-earnings-card p{margin:5px 0 0;color:#786f69;font-size:11px;line-height:1.5}.referral-earnings-card .amount{display:block;white-space:nowrap;color:#e85f17;font-size:30px;font-weight:900;letter-spacing:-.03em}.referral-earnings-card .currency{font-size:13px;font-weight:800}.referral-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:16px 0}.referral-stat{background:#fff;border:1px solid #eee5df;border-radius:13px;padding:14px}.referral-stat strong{display:block;font-size:22px;color:#f06a22}.referral-stat span{display:block;margin-top:4px;color:#817871;font-size:12px}
.referral-user-table{width:100%;border-collapse:separate;border-spacing:0;margin-top:10px;font-size:13px;table-layout:fixed}.referral-user-table th,.referral-user-table td{padding:12px 9px;border-bottom:1px solid #f0ebe7;text-align:start;vertical-align:middle;overflow-wrap:anywhere}.referral-user-table th:nth-child(1){width:22%}.referral-user-table th:nth-child(2){width:25%}.referral-user-table th:nth-child(3){width:33%}.referral-user-table th:nth-child(4){width:20%}.referral-user-table th{color:#8b817b;font-size:11px}.referral-user-table tbody tr:last-child td{border-bottom:0}.referral-user-table td small{display:block;margin-top:4px;color:#8b817b;font-size:11px;line-height:1.45}.referral-status{display:inline-block;padding:5px 9px;border-radius:999px;background:#f4f1ef;color:#6f625b;font-size:11px;font-weight:800;line-height:1.25}.referral-status.pending{background:#fff7e8;color:#a16207}.referral-status.qualified{background:#e9f8ef;color:#167343}.referral-status.rejected{background:#fff0f0;color:#b42318}
.referral-rules-card{background:#fff;border:1px solid #eee5df;border-radius:18px;padding:0;margin:16px 0;box-shadow:0 8px 24px rgba(41,32,26,.04);overflow:hidden}.referral-rules-card summary{display:flex;align-items:center;justify-content:space-between;gap:12px;cursor:pointer;list-style:none;padding:17px 19px;color:#302822}.referral-rules-card summary::-webkit-details-marker{display:none}.referral-rules-card summary strong{display:block;font-size:18px}.referral-rules-card summary small{display:block;margin-top:5px;color:#786f69;font-size:12px;font-weight:400}.referral-rules-card summary .referral-chevron{font-size:23px;color:#f06a22;line-height:1;transition:transform .18s ease}.referral-rules-card[open] summary{border-bottom:1px solid #f0ebe7;background:#fffaf6}.referral-rules-card[open] summary .referral-chevron{transform:rotate(180deg)}.referral-rules-content{padding:0 19px 19px}.referral-rules-card .intro{margin:15px 0 14px;color:#786f69;font-size:13px;line-height:1.7}.referral-rules-table{width:100%;border-collapse:collapse;font-size:13px}.referral-rules-table td{padding:10px 6px;border-bottom:1px solid #f0ebe7;vertical-align:top}.referral-rules-table tr:last-child td{border-bottom:0}.referral-rules-table td:first-child{width:46%;color:#786f69}.referral-rules-table td:last-child{font-weight:800;color:#bd5317}.referral-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:16px}.referral-step{background:#fff8f1;border:1px solid #ffe0c7;border-radius:13px;padding:12px}.referral-step b{display:flex;align-items:center;justify-content:center;width:25px;height:25px;border-radius:50%;background:#f06a22;color:#fff;font-size:12px;margin-bottom:8px}.referral-step strong{display:block;font-size:12px;color:#3b3029;margin-bottom:4px}.referral-step span{display:block;color:#786f69;font-size:11px;line-height:1.55}.referral-note{margin-top:13px;padding:10px 12px;border-radius:10px;background:#f7f7f7;color:#68615c;font-size:12px;line-height:1.6}.referral-note.off{background:#fff7ed;color:#9a4d12}
@media(max-width:600px){.referral-user-card{padding:17px}.referral-link-box{display:block}.referral-link-box input{width:100%;box-sizing:border-box}.referral-link-box button{width:100%;height:42px;margin-top:8px}.referral-earnings-card{align-items:flex-start;padding:16px}.referral-earnings-card .amount{font-size:24px}.referral-stats{gap:8px}.referral-stat{padding:11px}.referral-stat strong{font-size:19px}.referral-user-table{font-size:12px}
.referral-rules-card summary{padding:15px 16px}.referral-rules-content{padding:0 16px 16px}.referral-rules-card summary strong{font-size:16px}.referral-rules-table td:first-child{width:42%}.referral-user-table thead{display:none}.referral-user-table,.referral-user-table tbody,.referral-user-table tr,.referral-user-table td{display:block;width:auto}.referral-user-table tbody{display:grid;gap:10px}.referral-user-table tr{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:11px 12px;padding:13px;border:1px solid #f1dfd0;border-radius:13px;background:#fffaf6}.referral-user-table td{padding:0;border:0!important;overflow-wrap:normal}.referral-user-table td::before{content:attr(data-label);display:block;margin-bottom:5px;color:#9a8b82;font-size:10px;font-weight:700}.referral-user-table td:first-child{grid-column:1/-1}.referral-user-table td:first-child::before{margin-bottom:3px}.referral-user-table td small{font-size:10px}.referral-steps{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.referral-step{padding:10px}}
</style>

<div class="referral-earnings-card" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <div><span class="eyebrow">{{ $isAr ? 'عمولتك' : 'Your commission' }}</span><p>{{ $isAr ? 'إجمالي العمولات المعتمدة التي حصلت عليها فقط.' : 'Only approved commissions earned by you.' }}</p></div>
  <strong class="amount">{{ number_format($earnedCommissionTotal, 2) }} <span class="currency">{{ $isAr ? 'جنيه' : 'EGP' }}</span></strong>
</div>

<div class="referral-user-card" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <h1>{{ $isAr ? 'شارك واربح' : 'Share and earn' }}</h1>
  <p>{{ $isAr ? ($allOrders ? 'ابعت رابطك لحد من أصحابك. كل طلب مكتمل يستوفي الحد الأدنى ممكن يحسب لك عمولة حسب الشروط الموضحة.' : 'ابعت رابطك لحد من أصحابك. لما يعمل أول طلب مكتمل مستوفي الحد الأدنى، الإحالة تتأهل والعمولة تظهر لك حسب الشروط الموضحة.') : ($allOrders ? 'Share your link with a friend. Every qualifying completed order can earn you a commission according to the rules below.' : 'Share your link with a friend. When their first completed order meets the minimum, the referral qualifies and your commission is calculated according to the rules shown below.') }}</p>
  <div class="referral-link-box"><input id="referral-link" type="text" readonly value="{{ route('register', ['ref' => $user->referral_code]) }}"><button type="button" id="copy-referral-link">{{ $isAr ? 'نسخ الرابط' : 'Copy link' }}</button></div>
  <div class="referral-code">{{ $user->referral_code }}</div>
</div>

<details class="referral-rules-card" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <summary><span><strong>{{ $isAr ? 'إزاي تاخد عمولتك؟' : 'How do you get your commission?' }}</strong><small>{{ $isAr ? 'اعرف قيمة الأوردر المطلوبة وخطوات استحقاق العمولة' : 'See the required order value and the steps to qualify' }}</small></span><span class="referral-chevron" aria-hidden="true">⌄</span></summary>
  <div class="referral-rules-content">
  <p class="intro">{{ $isAr ? ($allOrders ? 'العمولة ممكن تتحسب مع كل طلب مكتمل للعميل اللي سجل من رابطك ويستوفي الحد الأدنى، ومش بتتحسب بمجرد التسجيل.' : 'العمولة مرتبطة بأول طلب مكتمل للعميل اللي سجل من رابطك، ومش بتتحسب بمجرد التسجيل.') : ($allOrders ? 'A commission may be calculated for every completed order by the referred customer that meets the minimum. Registration alone does not create a commission.' : 'Commission is tied to the referred customer’s first completed order. Registration alone does not create a commission.') }}</p>
  <table class="referral-rules-table">
    <tr><td>{{ $isAr ? ($allOrders ? 'قيمة كل أوردر مؤهل' : 'قيمة أول أوردر مؤهل') : 'Minimum qualifying order' }}</td><td>{{ number_format($minimumOrder, 2) }} {{ $isAr ? 'جنيه أو أكثر بعد الخصم' : 'EGP or more after discounts' }}</td></tr>
    <tr><td>{{ $isAr ? 'طريقة الحساب' : 'Commission calculation' }}</td><td>{{ $commissionType === 'flat' ? number_format($commissionValue, 2).' '.($isAr ? 'جنيه ثابت' : 'EGP fixed') : number_format($commissionValue, 2).'% '.($isAr ? 'من السعر النهائي' : 'of the final total') }}</td></tr>
    <tr><td>{{ $isAr ? 'انت كدا عمولتك' : 'Your commission would be' }}</td><td>{{ number_format($exampleCommission, 2) }} {{ $isAr ? 'جنيه تقريبًا' : 'EGP approximately' }}</td></tr>
    <tr><td>{{ $isAr ? 'متى يظهر المبلغ؟' : 'When is it shown?' }}</td><td>{{ $isAr ? ($allOrders ? 'بعد اكتمال كل أوردر مستوفي للشروط' : 'بعد اكتمال أول أوردر واستيفاء الشروط') : ($allOrders ? 'After each qualifying order is completed' : 'After the first order is completed and the conditions are met') }}</td></tr>
  </table>
  <div class="referral-steps">
    <div class="referral-step"><b>1</b><strong>{{ $isAr ? 'ابعت الرابط' : 'Share the link' }}</strong><span>{{ $isAr ? 'ابعت رابط الإحالة لصاحبك.' : 'Send your referral link to your friend.' }}</span></div>
    <div class="referral-step"><b>2</b><strong>{{ $isAr ? 'يسجل من الرابط' : 'They register' }}</strong><span>{{ $isAr ? 'يسجل من نفس الرابط ويتعمل له ربط بالإحالة.' : 'They register through the same link and become attributed to you.' }}</span></div>
    <div class="referral-step"><b>3</b><strong>{{ $isAr ? ($allOrders ? 'يعمل أوردر مؤهل' : 'يعمل أول أوردر') : ($allOrders ? 'Qualifying orders' : 'First order') }}</strong><span>{{ $isAr ? ($allOrders ? 'كل طلب مكتمل بقيمة نهائية لا تقل عن الحد الأدنى ممكن يحسب عمولة.' : 'يعمل أول طلب مكتمل بقيمة نهائية لا تقل عن الحد الأدنى.') : ($allOrders ? 'Each completed order at or above the minimum final total can earn a commission.' : 'They complete their first order at or above the minimum final total.') }}</span></div>
    <div class="referral-step"><b>4</b><strong>{{ $isAr ? 'استلام العمولة' : 'Receive your commission' }}</strong><span>{{ $isAr ? 'بعد استيفاء الشروط تظهر العمولة، ويتم صرفها يدويًا حسب آلية المتجر.' : 'Once the conditions are met, the commission appears and is paid manually according to the store process.' }}</span></div>
  </div>
  <div class="referral-note {{ $referralEnabled ? '' : 'off' }}">{{ $referralEnabled ? ($isAr ? 'البرنامج مفعّل حاليًا. العمولة تظهر لك بعد استيفاء الشروط، والصرف يتم يدويًا حسب آلية المتجر.' : 'The program is currently enabled. Your commission appears after the conditions are met and is paid manually according to the store process.') : ($isAr ? 'البرنامج غير مفعّل حاليًا. الإحالات الجديدة ستظل محفوظة، لكن لن تُنشأ عمولة حتى يتم تفعيل البرنامج.' : 'The program is currently disabled. Referrals may be recorded, but no commission is created until the program is enabled.') }}</div>
  </div>
</details>

<div class="referral-stats" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <div class="referral-stat"><strong>{{ $referrals->count() }}</strong><span>{{ $isAr ? 'إجمالي الإحالات' : 'Total referrals' }}</span></div>
  <div class="referral-stat"><strong>{{ $qualifiedCount }}</strong><span>{{ $isAr ? 'إحالات مؤهلة' : 'Qualified referrals' }}</span></div>
  <div class="referral-stat"><strong>{{ number_format($pendingCommissionTotal, 2) }}</strong><span>{{ $isAr ? 'عمولات تحت المراجعة (ج.م)' : 'Pending commissions (EGP)' }}</span></div>
  <div class="referral-stat"><strong>{{ $referrals->where('status', 'pending')->count() }}</strong><span>{{ $isAr ? 'تحت المراجعة' : 'Under review' }}</span></div>
</div>

<div class="referral-user-card" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="padding:17px">
  <h2 style="margin:0;font-size:17px">{{ $isAr ? 'الإحالات الأخيرة' : 'Recent referrals' }}</h2>
  <div style="overflow:auto">
    <table class="referral-user-table"><thead><tr><th>{{ $isAr ? 'الإحالة' : 'Referral' }}</th><th>{{ $isAr ? 'الحالة' : 'Status' }}</th><th>{{ $isAr ? 'الطلب المؤهل' : 'Qualifying order' }}</th><th>{{ $isAr ? 'العمولة' : 'Commission' }}</th></tr></thead><tbody>
    @forelse($referrals as $referral)
      @php
  $statusKey = strtolower((string) $referral->status);
@endphp

      <tr><td data-label="{{ $isAr ? 'الإحالة' : 'Referral' }}">{{ $referral->referred->name ?? ($isAr ? 'مستخدم محذوف' : 'Deleted user') }}</td><td data-label="{{ $isAr ? 'الحالة' : 'Status' }}"><span class="referral-status {{ $statusKey }}">{{ $referralStatusLabels[$statusKey] ?? ($isAr ? 'حالة مسجلة' : 'Recorded') }}</span></td><td data-label="{{ $isAr ? 'الطلب المؤهل' : 'Qualifying order' }}">@if($referral->qualifyingOrder)<strong>{{ number_format((float) $referral->qualifyingOrder->final_total, 2) }} {{ $isAr ? 'جنيه' : 'EGP' }}</strong><small>{{ $isAr ? 'السعر النهائي بعد الخصم · '.$referral->qualifyingOrder->status : 'Final total after discount · '.$referral->qualifyingOrder->status }}</small>@else<span>—</span>@endif</td><td data-label="{{ $isAr ? 'العمولة' : 'Commission' }}">{{ $referral->commissions->isNotEmpty() ? number_format($referral->commissions->sum(fn ($c) => (float) $c->amount), 2).' EGP' : '—' }}</td></tr>
    @empty<tr><td colspan="4">{{ $isAr ? 'لسه مفيش إحالات.' : 'No referrals yet.' }}</td></tr>@endforelse
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

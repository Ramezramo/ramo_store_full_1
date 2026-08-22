@extends('layouts.app')

@php
  $isAr = session('locale', 'en') === 'ar';
@endphp

@section('title', $isAr ? 'العروض والكوبونات — Ramo Store' : 'Offers & Coupons — Ramo Store')
@section('meta_robots', 'index,follow')

@push('styles')
<style>
  .offers-page{max-width:980px;margin:0 auto;padding:0 18px 64px;font-family:'Inter','Cairo',sans-serif}
  .offers-page[dir="rtl"]{font-family:'Cairo','Tahoma',sans-serif}
  .offers-hero{position:relative;overflow:hidden;margin:18px 0 26px;border-radius:22px;min-height:190px;padding:34px 38px;color:#fff;background:linear-gradient(125deg,#6b00d7 0%,#a100ee 52%,#5b13d6 100%);box-shadow:0 18px 38px rgba(103,0,210,.22)}
  .offers-hero::before,.offers-hero::after{content:'';position:absolute;border-radius:50%;background:rgba(255,255,255,.08);transform:rotate(-18deg)}
  .offers-hero::before{width:300px;height:300px;right:-74px;top:-110px}
  .offers-hero::after{width:220px;height:220px;left:29%;bottom:-150px}
  .offers-hero-inner{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:20px}
  .offers-brand{font-size:34px;font-weight:900;letter-spacing:-.06em;font-style:italic;text-shadow:0 3px 0 rgba(0,0,0,.1)}
  .offers-hero-copy{text-align:right}
  [dir="ltr"] .offers-hero-copy{text-align:left}
  .offers-hero h1{font-size:30px;line-height:1.15;font-weight:900;margin:0 0 9px}
  .offers-hero p{font-size:14px;opacity:.88;margin:0}
  .offers-hero-icon{width:74px;height:74px;border-radius:50%;display:grid;place-items:center;background:#fff;color:#7020cf;font-size:35px;box-shadow:0 8px 16px rgba(0,0,0,.12);flex-shrink:0}
  .offers-heading{display:flex;justify-content:space-between;align-items:end;gap:12px;margin:0 4px 14px}
  .offers-heading h2{font-size:22px;line-height:1.2;font-weight:800}
  .offers-heading p{font-size:13px;color:#777;margin:0}
  .offers-count{color:#7d19d4;font-size:12px;font-weight:800;background:#f2e7ff;padding:7px 11px;border-radius:30px;white-space:nowrap}
  .offers-list{display:grid;gap:18px}
  .offer-card{display:grid;grid-template-columns:minmax(0,1fr) 150px;min-height:195px;overflow:hidden;border:1px solid #ece7f1;border-radius:20px;background:#fff;box-shadow:0 8px 24px rgba(39,10,62,.08);transition:transform .18s ease,box-shadow .18s ease}
  .offer-card:hover{transform:translateY(-2px);box-shadow:0 13px 30px rgba(39,10,62,.13)}
  .offer-card-content{padding:23px 25px;display:flex;flex-direction:column;justify-content:space-between;min-width:0}
  .offer-card-art{display:flex;align-items:center;justify-content:center;position:relative;background:linear-gradient(160deg,#b510f0,#7412d5);color:#fff;min-width:0}
  .offer-card-art::before{content:'✦  ·  ✧  ·  ✦';position:absolute;inset:17px 8px auto;text-align:center;font-size:17px;letter-spacing:5px;opacity:.35}
  .offer-card-art::after{content:'';position:absolute;inset:0;background-image:radial-gradient(#fff 1px,transparent 1px);background-size:18px 18px;opacity:.13}
  .offer-art-circle{width:92px;height:92px;border-radius:50%;display:grid;place-items:center;background:#fff;color:#8c20dc;font-size:43px;box-shadow:0 7px 0 rgba(75,0,130,.12);position:relative;z-index:1}
  .offer-card:nth-child(3n) .offer-card-art{background:linear-gradient(160deg,#1763ba,#13418c)}
  .offer-card:nth-child(3n) .offer-art-circle{color:#1457a5}
  .offer-card-title{font-size:22px;font-weight:800;color:#2c1939;margin-bottom:4px}
  .offer-card-desc{font-size:14px;color:#68616d;line-height:1.65;margin:0}
  .offer-card-value{color:#7e12d6;font-size:24px;font-weight:900;line-height:1.1;margin:10px 0 3px}
  .offer-card-value small{font-size:13px;font-weight:700;color:#806f8c}
  .offer-card-meta{color:#827989;font-size:12px;margin-top:4px}
  .offer-card-bottom{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:16px}
  .offer-code{display:inline-flex;align-items:center;gap:8px;background:#f3e5ff;color:#7210c6;border-radius:22px;padding:8px 13px;font-weight:900;font-size:15px;letter-spacing:.03em;direction:ltr}
  .offer-copy{border:0;border-radius:10px;background:#7808da;color:#fff;padding:9px 16px;font-family:inherit;font-size:13px;font-weight:800;cursor:pointer;transition:transform .14s ease,background .14s ease}
  .offer-copy:hover{background:#5f06ab}.offer-copy:active{transform:scale(.97)}
  .offer-copy.copied{background:#159447}
  .offers-empty{padding:48px 20px;text-align:center;border:1px dashed #d9c3eb;border-radius:18px;color:#71677c;background:#fcf9ff}
  .offers-empty-icon{font-size:40px;margin-bottom:10px}
  .offers-note{margin:22px 4px 0;padding:14px 16px;border-radius:13px;background:#fff8eb;color:#7a5a23;font-size:13px;line-height:1.7}
  @media(max-width:650px){
    .offers-page{padding:0 12px 72px}
    .offers-hero{min-height:165px;padding:26px 22px;border-radius:18px}
    .offers-brand{font-size:27px}.offers-hero h1{font-size:24px}.offers-hero p{font-size:12px}.offers-hero-icon{width:59px;height:59px;font-size:28px}
    .offers-heading{align-items:center}.offers-heading h2{font-size:19px}.offers-heading p{font-size:11px}.offers-count{font-size:11px;padding:6px 8px}
    .offer-card{grid-template-columns:minmax(0,1fr) 112px;min-height:188px;border-radius:16px}
    .offer-card-content{padding:18px 15px}.offer-card-title{font-size:18px}.offer-card-desc{font-size:12px}.offer-card-value{font-size:20px}.offer-card-meta{font-size:11px}
    .offer-card-art{min-height:188px}.offer-art-circle{width:70px;height:70px;font-size:31px}.offer-card-bottom{gap:7px;margin-top:11px}.offer-code{font-size:13px;padding:7px 9px}.offer-copy{font-size:11px;padding:8px 10px}
  }
</style>
@endpush

@section('content')
<div class="offers-page" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <section class="offers-hero" aria-labelledby="offers-title">
    <div class="offers-hero-inner">
      <div class="offers-hero-copy">
        <h1 id="offers-title">{{ $isAr ? 'هدايا وعروض ليك' : 'Gifts and offers for you' }}</h1>
        <p>{{ $isAr ? 'استخدم الكود وقت إتمام الطلب ووفر أكتر.' : 'Use a code at checkout and save more.' }}</p>
      </div>
      <div class="offers-brand" aria-label="Ramo Store">Ramo<span style="color:#ffc547">Store</span></div>
      <div class="offers-hero-icon" aria-hidden="true">🎁</div>
    </div>
  </section>

  <div class="offers-heading">
    <div>
      <h2>{{ $isAr ? 'العروض المتاحة' : 'Available offers' }}</h2>
      <p>{{ $isAr ? 'الكوبونات دي شغالة دلوقتي على المتجر.' : 'These coupons are currently active in the store.' }}</p>
    </div>
    <span class="offers-count">{{ $coupons->count() }} {{ $isAr ? 'كوبون' : 'coupons' }}</span>
  </div>

  <div class="offers-list">
    @forelse($coupons as $coupon)
      @php
        $isFreeShipping = (bool) ($coupon->free_shipping ?? false);
        $amount = (float) ($coupon->amount ?? 0);
        $expiry = $coupon->date_expires ? \Carbon\Carbon::parse($coupon->date_expires) : null;
        $title = $isFreeShipping
          ? ($isAr ? 'توصيل مجاني' : 'Free delivery')
          : ($coupon->discount_type === 'percent'
              ? ($isAr ? 'خصم '.$amount.'% على طلبك' : $amount.'% off your order')
              : ($isAr ? 'خصم '.number_format($amount, 0).' جنيه' : number_format($amount, 0).' EGP off'));
        $description = trim((string) ($coupon->description ?? ''));
        $icon = $isFreeShipping ? '🚚' : ($coupon->discount_type === 'percent' ? '％' : '🎟️');
      @endphp
      <article class="offer-card">
        <div class="offer-card-content">
          <div>
            <h3 class="offer-card-title">{{ $title }}</h3>
            <p class="offer-card-desc">{{ $description !== '' ? $description : ($isAr ? 'استخدم الكود ده وقت إتمام الطلب واستفيد من العرض.' : 'Use this code at checkout to redeem the offer.') }}</p>
            @if(!$isFreeShipping)
              <div class="offer-card-value">{{ $coupon->discount_type === 'percent' ? (int) $amount.'%' : number_format($amount, 0).' EGP' }} <small>{{ $isAr ? 'خصم' : 'discount' }}</small></div>
            @endif
            <div class="offer-card-meta">
              @if((float) ($coupon->minimum_amount ?? 0) > 0)
                {{ $isAr ? 'على طلب من ' : 'For orders from ' }}{{ number_format((float) $coupon->minimum_amount, 0) }} EGP
              @endif
              @if((float) ($coupon->maximum_amount ?? 0) > 0)
                · {{ $isAr ? 'بحد أقصى ' : 'up to ' }}{{ number_format((float) $coupon->maximum_amount, 0) }} EGP
              @endif
              @if($expiry)
                · {{ $isAr ? 'ساري لحد ' : 'Valid until ' }}{{ $expiry->format('d/m/Y') }}
              @endif
            </div>
          </div>
          <div class="offer-card-bottom">
            <span class="offer-code" aria-label="{{ $isAr ? 'كود الخصم' : 'Coupon code' }}">{{ strtoupper($coupon->code) }} <span aria-hidden="true">⧉</span></span>
            <button type="button" class="offer-copy" data-code="{{ strtoupper($coupon->code) }}" onclick="copyOfferCode(this)">{{ $isAr ? 'انسخ الكود' : 'Copy code' }}</button>
          </div>
        </div>
        <div class="offer-card-art" aria-hidden="true"><span class="offer-art-circle">{{ $icon }}</span></div>
      </article>
    @empty
      <div class="offers-empty">
        <div class="offers-empty-icon">🎁</div>
        <strong>{{ $isAr ? 'مفيش عروض متاحة دلوقتي' : 'No active offers right now' }}</strong>
        <div>{{ $isAr ? 'ارجع لنا قريب عشان تشوف العروض الجديدة.' : 'Check back soon for new offers.' }}</div>
      </div>
    @endforelse
  </div>

  @if($coupons->count())
    <p class="offers-note">{{ $isAr ? 'ملحوظة: كل كوبون له شروطه الخاصة زي الحد الأدنى للطلب أو تاريخ الانتهاء. اتأكد من التفاصيل قبل ما تستخدمه.' : 'Note: Each coupon may have its own minimum order or expiry date. Check the details before using it.' }}</p>
  @endif
</div>
@endsection

@push('scripts')
<script>
function copyOfferCode(button) {
  const code = button?.dataset.code || '';
  if (!code) return;
  const done = () => {
    const original = button.textContent;
    button.textContent = '{{ $isAr ? 'اتنسخ ✓' : 'Copied ✓' }}';
    button.classList.add('copied');
    window.setTimeout(() => { button.textContent = original; button.classList.remove('copied'); }, 1400);
  };
  if (navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(code).then(done).catch(() => fallbackCopy(code, done));
  } else {
    fallbackCopy(code, done);
  }
}
function fallbackCopy(text, done) {
  const input = document.createElement('textarea');
  input.value = text; input.style.position = 'fixed'; input.style.opacity = '0';
  document.body.appendChild(input); input.select();
  try { document.execCommand('copy'); done(); } finally { input.remove(); }
}
</script>
@endpush

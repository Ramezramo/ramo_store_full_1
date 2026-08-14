@extends('layouts.app')

@section('title', $page['title'])
@section('canonical', url('/' . $pageKey))
@section('meta_robots', 'noindex,follow')

@section('content')
<style>
  .policy-shell{max-width:900px;margin:0 auto;padding:48px 20px 68px}
  .policy-hero{padding:30px;border-radius:18px;background:linear-gradient(135deg,#fff7f1,#fff);border:1px solid #ffe0cd;margin-bottom:22px}
  .policy-kicker{margin:0 0 8px;color:var(--c-orange,#e85d26);font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
  .policy-title{margin:0;color:#1f2937;font-size:clamp(28px,5vw,42px);line-height:1.15}
  .policy-summary{margin:12px 0 0;color:#4b5563;font-size:16px;line-height:1.7}
  .policy-card{padding:28px 30px;border:1px solid #e5e7eb;border-radius:18px;background:#fff;box-shadow:0 10px 25px rgba(16,24,40,.05)}
  .policy-draft{margin:0 0 22px;padding:13px 15px;border:1px solid #f5c98e;border-radius:12px;background:#fff9ef;color:#814d08;font-size:14px;line-height:1.65}
  .policy-copy{color:#374151;font-size:16px;line-height:1.9;white-space:pre-line}
  .policy-copy p{margin:0}
  .policy-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:28px}
  .policy-actions a{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 16px;border:1px solid #d1d5db;border-radius:10px;color:#374151;font-weight:700;text-decoration:none}
  .policy-actions a:hover{border-color:var(--c-orange,#e85d26);color:var(--c-orange,#e85d26)}
  @media(max-width:600px){.policy-shell{padding:30px 14px 48px}.policy-hero,.policy-card{padding:22px 18px}.policy-copy{font-size:15px}.policy-actions a{flex:1}}
</style>

<main class="policy-shell" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <header class="policy-hero">
    <p class="policy-kicker">Ramo Store</p>
    <h1 class="policy-title">{{ $page['title'] }}</h1>
    <p class="policy-summary">{{ $page['summary'] }}</p>
  </header>

  <article class="policy-card">
    @if($isPolicyDraft)
      <p class="policy-draft">{{ $isAr ? 'تنبيه قبل الإطلاق: ده محتوى مبدئي ظاهر بشكل شفاف لحد ما مالك المتجر يعتمد النص النهائي.' : 'Pre-launch notice: this transparent interim copy remains visible until the store owner approves final policy text.' }}</p>
    @endif
    <div class="policy-copy">{{ $copy }}</div>
    <nav class="policy-actions" aria-label="{{ $isAr ? 'روابط مساعدة' : 'Helpful links' }}">
      <a href="{{ route('shop') }}">{{ $isAr ? 'كمّل تسوق' : 'Continue shopping' }}</a>
      <a href="{{ route('order.track') }}">{{ $isAr ? 'تابع طلبك' : 'Track your order' }}</a>
    </nav>
  </article>
</main>
@endsection

@extends('layouts.app')
@section('title', session('locale') === 'ar' ? 'Ramo Store — الرئيسية' : 'Ramo Store — Home')

@section('content')

<style>
@media(max-width:640px) {
  /* Banner navigation is touch-first on phones; keep product-scroll arrows untouched. */
  .tl-banner-slider .tl-arrow { display:none !important; }
}
</style>

@php
  $timelineRtl = session('locale') === 'ar';
  $isAr = $timelineRtl;
  $timelineSeeAllLabel = $timelineRtl ? 'شوف اكتر' : 'See all →';
  // Resolve managed local/legacy storage media once for all customer timeline
  // widgets. Missing local paths are hidden from shoppers, while their original
  // configuration remains untouched for administrators to repair.
  $resolveTimelineImage = static function ($image): ?string {
    $image = trim((string) $image);
    if ($image === '') return null;

    $imageParts = parse_url($image);
    $imageHost = strtolower((string) ($imageParts['host'] ?? ''));
    $appHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?? ''));
    $imagePath = (string) ($imageParts['path'] ?? $image);
    $isManagedStorageUrl = str_starts_with($imagePath, '/storage/')
      && ($imageHost === '' || $imageHost === $appHost || (bool) preg_match('/^(?:localhost|127\.|10\.|192\.168\.|172\.(?:1[6-9]|2[0-9]|3[0-1])\.)/', $imageHost));

    if (str_starts_with($image, 'storage/') || $isManagedStorageUrl) {
      return \App\Constants\AppConstants::imageUrl($image);
    }

    return $image;
  };
@endphp
<div class="timeline-widgets{{ $timelineRtl ? ' timeline-widgets--rtl' : '' }}" @if($timelineRtl) dir="rtl" @endif>
{{-- ── ANNOUNCEMENT BARS (full-width, outside page) ── --}}
@foreach($announcements as $si => $sec)
  @if(($sec['layout'] ?? '') === 'announcement')
    @php
      $aMsg    = $sec['message']    ?? 'Welcome to Ramo Store! Free shipping on orders over 500 EGP.';
      $aColor  = $sec['barColor']   ?? 'dark';
      $aDismiss = $sec['dismissableByUser'] ?? true;
      $aBg = match($aColor) {
        'orange' => '#e85d26', 'navy' => '#1a1a2e', 'white' => '#f8f8f8', default => '#111111'
      };
      $aFg = ($aColor === 'white') ? '#111' : '#fff';
    @endphp
    <div class="tl-announcement" id="announce-{{ $si }}" style="background:{{ $aBg }};color:{{ $aFg }}">
      @if($aDismiss)
      <button class="tl-announce-close" onclick="document.getElementById('announce-{{ $si }}').style.display='none'" style="color:{{ $aFg }}">×</button>
      @endif
      <div class="tl-announce-static">{{ $aMsg }}</div>
    </div>
  @endif
@endforeach

<div class="page">

  @include('web.partials.timeline-sections')

  @include('web.partials.timeline-pagination')

</div>
</div>
@endsection

@push('styles')
<style>
/* Arabic direction is deliberately scoped to Timeline widgets only. */
.timeline-widgets--rtl{direction:rtl}
.timeline-widgets--rtl .tl-flex-banner-title,
.timeline-widgets--rtl .sec-head,
.timeline-widgets--rtl .sec-title,
.timeline-widgets--rtl .tl-promo-content,
.timeline-widgets--rtl .tl-testimonial-card,
.timeline-widgets--rtl .tl-newsletter,
.timeline-widgets--rtl .tl-bundle,
.timeline-widgets--rtl .tl-loyalty,
.timeline-widgets--rtl .tl-activity,
.timeline-widgets--rtl .tl-referral,
.timeline-widgets--rtl .tl-product-card,
.timeline-widgets--rtl .product-card-body{direction:rtl;text-align:right}
.timeline-widgets--rtl input,
.timeline-widgets--rtl textarea{direction:rtl;text-align:right}
.timeline-widgets--rtl .tl-arrow.prev{left:auto;right:10px}
.timeline-widgets--rtl .tl-arrow.next{right:auto;left:10px}
.timeline-widgets--rtl .tl-scroll-arrow.prev{left:auto;right:-6px}
.timeline-widgets--rtl .tl-scroll-arrow.next{right:auto;left:-6px}
.timeline-widgets--rtl .tl-announce-close{right:auto;left:12px}
.timeline-widgets--rtl .tl-rank-badge{left:auto;right:8px}
.timeline-widgets--rtl .tl-bundle-badge{right:auto;left:16px}
.timeline-widgets--rtl .tl-loyalty-double{margin-left:0;margin-right:8px}
.timeline-widgets--rtl .tl-stat-item{border-right:none;border-left:1px solid rgba(255,255,255,.15)}
.timeline-widgets--rtl .tl-stat-item:last-child{border-left:none}

@if(request()->has('tl_preview'))
/* ── TIMELINE PREVIEW OVERLAY ── */
.tl-preview-toolbar{position:fixed;top:0;left:0;right:0;z-index:99999;background:#1a1a2e;color:#fff;display:flex;align-items:center;gap:12px;padding:0 20px;height:44px;font-size:13px;font-family:system-ui,sans-serif;box-shadow:0 2px 12px rgba(0,0,0,.4)}
.tl-preview-toolbar .tl-pt-badge{background:#e85d26;color:#fff;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;letter-spacing:.5px;text-transform:uppercase}
.tl-preview-toolbar .tl-pt-msg{color:rgba(255,255,255,.7);font-size:12px}
.tl-preview-toolbar a.tl-pt-btn{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;padding:5px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;transition:.15s}
.tl-preview-toolbar a.tl-pt-btn:hover{background:rgba(255,255,255,.2)}
.tl-preview-toolbar .tl-pt-spacer{flex:1}
.tl-pw{position:relative;outline:2px dashed transparent;transition:outline-color .15s}
.tl-pw:hover{outline-color:var(--tl-pw-color,#e85d26)}
.tl-pw-label{position:absolute;top:6px;left:6px;z-index:9999;display:flex;align-items:center;gap:6px;background:var(--tl-pw-color,#e85d26);color:#fff;font-size:11px;font-weight:700;padding:3px 10px 3px 7px;border-radius:20px;pointer-events:none;font-family:system-ui,sans-serif;box-shadow:0 2px 8px rgba(0,0,0,.25);opacity:0;transition:opacity .15s;white-space:nowrap}
.tl-pw:hover .tl-pw-label{opacity:1}
.tl-pw-label .tl-pw-idx{background:rgba(0,0,0,.2);border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:10px}
body{padding-top:44px}
@endif
.tl-stats-bar{display:flex;flex-wrap:wrap;justify-content:center;gap:0;border-radius:var(--radius-lg);padding:36px 20px;margin-top:0}
.tl-stat-item{flex:1;min-width:140px;text-align:center;padding:16px 24px;border-right:1px solid rgba(255,255,255,.15)}
.tl-stat-item:last-child{border-right:none}
.tl-stat-num{font-size:36px;font-weight:800;line-height:1;margin-bottom:6px}
.tl-stat-lbl{font-size:13px;font-weight:500;opacity:.75;letter-spacing:.5px;text-transform:uppercase}

.tl-promo-block{border-radius:var(--radius-lg);padding:64px 48px;display:flex;align-items:center;gap:48px}
.tl-promo-img-wrap{flex-shrink:0;width:260px;height:200px;border-radius:12px;overflow:hidden}
.tl-promo-img{width:100%;height:100%;object-fit:cover}
.tl-promo-content{flex:1}
.tl-promo-headline{font-size:32px;font-weight:800;letter-spacing:-.5px;margin-bottom:12px;line-height:1.2}
.tl-promo-sub{font-size:15px;line-height:1.6;margin-bottom:24px}
.tl-promo-btn{display:inline-flex;align-items:center;padding:12px 28px;border-radius:50px;font-size:14px;font-weight:700;text-decoration:none;transition:.2s}
.tl-promo-btn:hover{opacity:.88;transform:translateY(-1px)}

.tl-testimonials{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.tl-testimonial-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:22px;display:flex;flex-direction:column;gap:12px;transition:box-shadow .15s}
.tl-testimonial-card:hover{box-shadow:var(--shadow-md)}
.tl-test-stars{display:flex;gap:2px;font-size:16px}
.tl-test-comment{font-size:13.5px;color:var(--c-mid);line-height:1.65;flex:1}
.tl-test-meta{display:flex;align-items:center;gap:10px;margin-top:4px}
.tl-test-avatar{width:36px;height:36px;border-radius:50%;background:var(--c-orange);color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tl-test-name{font-size:13px;font-weight:700;color:var(--c-dark)}
.tl-test-product{font-size:11px;color:var(--c-mid);margin-top:2px}

.tl-newsletter{border-radius:var(--radius-lg);padding:56px 32px;text-align:center}
.tl-newsletter-content{max-width:520px;margin:auto}
.tl-newsletter-title{font-size:26px;font-weight:800;letter-spacing:-.4px;margin-bottom:10px}
.tl-newsletter-sub{font-size:14px;color:var(--c-mid);margin-bottom:24px;line-height:1.6}
.tl-newsletter-form{display:flex;gap:10px;max-width:420px;margin:auto}
.tl-newsletter-input{flex:1;padding:12px 18px;border:1.5px solid var(--c-light);border-radius:50px;font-size:14px;font-family:inherit;outline:none;background:#fff;transition:border-color .15s}
.tl-newsletter-input:focus{border-color:#999}
.tl-newsletter-btn{padding:12px 24px;background:var(--c-dark);color:#fff;border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;transition:.15s;white-space:nowrap}
.tl-newsletter-btn:hover{background:var(--c-orange)}
.tl-newsletter-thanks{margin-top:16px;font-size:14px;font-weight:600;color:var(--c-orange)}

@media(max-width:640px){
  .tl-stat-item{min-width:120px;padding:12px 16px}
  .tl-stat-num{font-size:28px}
  .tl-promo-block{flex-direction:column;gap:20px;padding:22px 20px;text-align:center}
  .tl-promo-img-wrap{width:min(100%,220px);height:150px}
  .tl-promo-headline{font-size:24px;margin-bottom:8px}
  .tl-promo-sub{font-size:14px;margin-bottom:16px}
  .tl-promo-btn{padding:10px 22px}
  .tl-newsletter-form{flex-direction:column}
}

/* ── ANNOUNCEMENT BAR ── */
.tl-announcement{position:relative;width:100%;padding:9px 40px 9px 16px;font-size:13px;font-weight:500;overflow:hidden;z-index:100}
.tl-announce-close{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;font-size:20px;cursor:pointer;line-height:1;padding:0}
.tl-announce-static{text-align:center}
@media(max-width:640px){
  .tl-announcement{padding:6px 34px 6px 12px;font-size:11px;line-height:1.25}
  .tl-announce-close{right:10px;font-size:17px}
}

/* ── FLASH SALE BAR ── */
.tl-flash-bar{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border-radius:12px;padding:18px 20px;margin-bottom:20px}
.tl-flash-inner{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.tl-flash-icon{font-size:28px;flex-shrink:0}
.tl-flash-text{flex:1;min-width:0}
.tl-flash-title{display:block;font-size:16px;font-weight:800}
.tl-flash-disc{display:inline-block;background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:14px;font-weight:700;margin-top:2px}
.tl-flash-min{display:block;font-size:12px;opacity:.8;margin-top:2px}
.tl-flash-countdown{display:flex;align-items:center;gap:6px;flex-shrink:0}
.tl-cd-unit{text-align:center;background:rgba(0,0,0,.25);border-radius:8px;padding:6px 10px}
.tl-cd-num{display:block;font-size:22px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}
.tl-cd-lbl{font-size:10px;opacity:.8;letter-spacing:.5px}
.tl-cd-sep{font-size:22px;font-weight:800;opacity:.6}
.tl-flash-btn{flex-shrink:0;background:#fff;color:#c0392b;padding:10px 20px;border-radius:50px;font-size:13px;font-weight:800;text-decoration:none;white-space:nowrap;transition:.15s}
.tl-flash-btn:hover{background:#ffe0e0}

/* ── TRENDING ── */
.tl-rank-badge{position:absolute;top:8px;left:8px;background:#e85d26;color:#fff;font-size:11px;font-weight:800;padding:2px 7px;border-radius:20px;z-index:2}

/* ── NEW ARRIVALS ROW ── */
.tl-arrivals-wrap{overflow-x:auto;overscroll-behavior-x:contain;scroll-snap-type:x proximity}
.tl-arrivals-track{display:flex;width:max-content;gap:14px}
.tl-arrival-card{display:flex;flex-direction:column;flex-shrink:0;width:140px;text-decoration:none;color:inherit;border:1.5px solid var(--c-light);border-radius:10px;overflow:hidden;transition:box-shadow .15s}
.tl-arrival-card{scroll-snap-align:start}
.tl-arrival-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.1)}
.tl-arrival-img{width:100%;height:120px;object-fit:cover}
.tl-arrival-placeholder{width:100%;height:120px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;font-size:28px}
.tl-arrival-body{padding:8px}
.tl-arrival-tag{display:inline-block;background:#e85d2618;color:#e85d26;font-size:10px;font-weight:700;padding:1px 6px;border-radius:20px;margin-bottom:4px}
.tl-arrival-name{font-size:11px;font-weight:600;color:var(--c-dark);line-height:1.3;margin-bottom:3px}
.tl-arrival-price{font-size:12px;font-weight:700;color:#e85d26}

/* ── BRAND LOGOS ROW ── */
.tl-brand-logos{display:flex;flex-wrap:wrap;gap:10px;margin-top:4px}
.tl-brand-logo-chip{display:flex;flex-direction:column;align-items:center;justify-content:center;border:1.5px solid var(--c-light);border-radius:10px;padding:12px 16px;text-decoration:none;color:inherit;transition:.15s;min-width:80px;background:#fff}
.tl-brand-logo-chip:hover{border-color:#e85d26;box-shadow:0 2px 10px rgba(0,0,0,.08)}
.tl-brand-letter{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#e85d26,#f59e0b);color:#fff;font-size:18px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:6px}
.tl-brand-name{font-size:11px;font-weight:600;color:var(--c-mid);text-align:center}
.tl-brand-small .tl-brand-letter{width:34px;height:34px;font-size:14px}
.tl-brand-large .tl-brand-letter{width:56px;height:56px;font-size:22px}

/* ── REVIEWS CAROUSEL ── */
.tl-revcar{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:32px;max-width:600px;margin-left:auto;margin-right:auto}
.tl-revcar-slide{flex-direction:column;gap:14px}
.tl-revcar-nav{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:20px}
.tl-revcar-nav button{background:none;border:1.5px solid var(--c-light);border-radius:50%;width:34px;height:34px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.15s}
.tl-revcar-nav button:hover{border-color:#e85d26;color:#e85d26}
.tl-revcar-dots{display:flex;gap:6px}
.tl-revcar-dot{width:8px;height:8px;border-radius:50%;background:#ddd;cursor:pointer;transition:.15s}
.tl-revcar-dot.active{background:#e85d26;width:22px;border-radius:4px}

/* ── LIVE ACTIVITY ── */
.tl-activity-bar{display:flex;align-items:center;gap:10px;background:#fff8f4;border:1.5px solid #fde8d8;border-radius:50px;padding:8px 18px;font-size:13px;font-weight:500;color:#c0392b;width:fit-content}
.tl-activity-dot{width:8px;height:8px;border-radius:50%;background:#e85d26;flex-shrink:0;animation:actPulse 1.5s ease-in-out infinite}
@keyframes actPulse{0%,100%{opacity:1}50%{opacity:.3}}
.tl-activity-msg{color:#7c3826}

/* ── BUNDLE DEAL ── */
.tl-bundle-card{background:linear-gradient(135deg,#fff8f4,#fde8d8);border:1.5px solid #fbd5bd;border-radius:var(--radius-lg);overflow:hidden;position:relative;padding:24px}
.tl-bundle-badge{position:absolute;top:16px;right:16px;background:#e85d26;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.5px}
.tl-bundle-body{display:flex;align-items:center;gap:20px;flex-wrap:wrap}
.tl-bundle-icon{font-size:44px;flex-shrink:0}
.tl-bundle-info{flex:1}
.tl-bundle-title{font-size:20px;font-weight:800;margin-bottom:6px}
.tl-bundle-desc{font-size:14px;color:var(--c-mid);line-height:1.5}
.tl-bundle-btn{flex-shrink:0;background:#e85d26;color:#fff;padding:12px 24px;border-radius:50px;font-size:14px;font-weight:700;text-decoration:none;white-space:nowrap;transition:.15s}
.tl-bundle-btn:hover{background:#c94d1a}

/* ── LOYALTY POINTS ── */
.tl-loyalty-bar{background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:var(--radius-lg);padding:20px 28px}
.tl-loyalty-inner{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.tl-loyalty-icon{font-size:32px;flex-shrink:0}
.tl-loyalty-text{flex:1;color:#fff}
.tl-loyalty-text strong{display:block;font-size:15px;margin-bottom:3px}
.tl-loyalty-text span{font-size:13px;opacity:.75}
.tl-loyalty-double{background:#f59e0b;color:#000;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:8px}
.tl-loyalty-btn{flex-shrink:0;background:#e85d26;color:#fff;padding:10px 20px;border-radius:50px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap}

/* ── SEASONAL BANNER ── */
.tl-seasonal{border-radius:var(--radius-lg);padding:48px 36px;overflow:hidden;position:relative}
.tl-seasonal-animate{animation:seasonIn .5s ease-out}
@keyframes seasonIn{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:none}}
.tl-seasonal-inner{display:flex;align-items:center;gap:32px;flex-wrap:wrap;position:relative;z-index:1}
.tl-seasonal-text{flex:1}
.tl-seasonal-title{font-size:28px;font-weight:800;margin-bottom:8px;text-shadow:0 2px 8px rgba(0,0,0,.2)}
.tl-seasonal-sub{font-size:15px;opacity:.85;line-height:1.5}
.tl-seasonal-cd{font-size:22px;font-weight:800;letter-spacing:.5px;white-space:nowrap;flex-shrink:0}
.tl-seasonal-btn{flex-shrink:0;background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.5);color:#fff;padding:12px 24px;border-radius:50px;font-size:14px;font-weight:700;text-decoration:none;backdrop-filter:blur(4px);transition:.15s}
.tl-seasonal-btn:hover{background:rgba(255,255,255,.35)}

/* ── REFERRAL ── */
.tl-referral-card{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1.5px solid #bae6fd;border-radius:var(--radius-lg);padding:28px}
.tl-referral-inner{display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap}
.tl-referral-icon{font-size:40px;flex-shrink:0}
.tl-referral-body{flex:1}
.tl-referral-title{font-size:18px;font-weight:800;margin-bottom:6px;color:#0c4a6e}
.tl-referral-desc{font-size:13px;color:#0369a1;line-height:1.5;margin-bottom:14px}
.tl-referral-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.tl-referral-link{flex:1;padding:9px 14px;border:1.5px solid #bae6fd;border-radius:8px;font-size:12px;font-family:monospace;background:#fff;cursor:text;min-width:180px;color:#0c4a6e}
.tl-referral-wa-btn{display:inline-flex;align-items:center;gap:6px;background:#25d366;color:#fff;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;transition:.15s}
.tl-referral-wa-btn:hover{background:#1fb958}

/* ── COMPLETE THE LOOK ── */
.tl-complete-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:24px}
.tl-complete-inner{display:flex;align-items:center;gap:20px;flex-wrap:wrap}

/* ── PRODUCT CUSTOMIZER WIDGET ── */
.pcw-card{display:grid;grid-template-columns:1fr 1fr;gap:40px;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);overflow:hidden;padding:0}
.pcw-img-wrap{display:block;position:relative;overflow:hidden;background:#f8f8f8;min-height:360px}
.pcw-img{width:100%;height:100%;object-fit:cover;min-height:360px;display:block;transition:transform .3s}
.pcw-img-wrap:hover .pcw-img{transform:scale(1.03)}
.pcw-img-placeholder{width:100%;min-height:360px;display:flex;align-items:center;justify-content:center;font-size:60px;color:#ccc}
.pcw-info{padding:36px 36px 36px 0;display:flex;flex-direction:column;gap:14px;justify-content:center}
.pcw-title-row{display:flex;align-items:flex-start;gap:12px}
.pcw-title{font-size:22px;font-weight:800;letter-spacing:-.4px;line-height:1.25;flex:1;margin:0}
.pcw-wish-btn{width:38px;height:38px;border-radius:50%;border:1.5px solid var(--c-light);background:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:.15s;color:#bbb}
.pcw-wish-btn:hover,.pcw-wish-btn.wished{border-color:#e85d26;color:#e85d26}
.pcw-rating-row{display:flex;align-items:center;gap:6px;font-size:13px}
.pcw-stars{display:flex;gap:1px;font-size:15px}
.pcw-rating-val{font-weight:700;color:#333}
.pcw-rating-cnt{color:var(--c-mid);text-decoration:none}
.pcw-rating-cnt:hover{color:#e85d26}
.pcw-rating-none{color:var(--c-mid);font-size:13px}
.pcw-stock-ok{color:#22c55e;font-size:13px;font-weight:600}
.pcw-stock-no{color:#ef4444;font-size:13px;font-weight:600}
.pcw-price-block{display:flex;flex-direction:column;gap:6px}
.pcw-price-row{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap}
.pcw-price{font-size:26px;font-weight:800;color:#222}
.pcw-price.on-sale{color:#e85d26}
.pcw-price-orig{font-size:16px;color:var(--c-mid);text-decoration:line-through}
.pcw-disc-badge{background:#e85d26;color:#fff;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px}
.pcw-sale-note{font-size:12px;color:#22c55e;font-weight:600;border:1px solid #dcfce7;background:#f0fdf4;padding:6px 12px;border-radius:8px;display:inline-block}
.pcw-variations{display:flex;flex-direction:column;gap:10px}
.pcw-var-group{display:flex;flex-direction:column;gap:6px}
.pcw-var-label{font-size:11px;font-weight:700;color:var(--c-mid);text-transform:uppercase;letter-spacing:.5px}
.pcw-var-opts{display:flex;flex-wrap:wrap;gap:6px}
.pcw-swatch{display:inline-block;width:28px;height:28px;border-radius:50%;border:2px solid rgba(0,0,0,.12);cursor:default}
.pcw-chip{display:inline-block;padding:4px 12px;border:1.5px solid var(--c-light);border-radius:6px;font-size:12px;font-weight:600;color:#444;cursor:default}
.pcw-cart-row{display:flex;gap:12px;align-items:center}
.pcw-atc-btn{flex:1;background:#111;color:#fff;border:none;border-radius:10px;padding:14px 20px;font-size:14px;font-weight:700;cursor:pointer;transition:.15s}
.pcw-atc-btn:hover{background:#e85d26}
.pcw-coupon-wrap{border:1.5px dashed var(--c-light);border-radius:10px;padding:14px 16px;background:#fafafa}
.pcw-coupon-label{font-size:13px;font-weight:600;color:#555;margin-bottom:8px}
.pcw-coupon-row{display:flex;gap:8px}
.pcw-coupon-input{flex:1;padding:9px 14px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:inherit;outline:none;background:#fff;transition:border-color .15s}
.pcw-coupon-input:focus{border-color:#e85d26}
.pcw-coupon-btn{padding:9px 18px;background:#111;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;transition:.15s}
.pcw-coupon-btn:hover{background:#e85d26}
.pcw-coupon-msg{font-size:12px;margin-top:6px;font-weight:600}
.pcw-view-link{font-size:13px;color:#e85d26;font-weight:700;text-decoration:none;margin-top:2px}
.pcw-view-link:hover{text-decoration:underline}
@media(max-width:700px){
  .pcw-card{grid-template-columns:1fr}
  .pcw-img-wrap{min-height:240px}
  .pcw-img{min-height:240px}
  .pcw-img-placeholder{min-height:240px}
  .pcw-info{padding:24px}
}

/* ── Flexible Banner Grid (admin timeline widget) ─────────────── */
.tl-flex-banner-section{margin:0 0 28px}
.tl-flex-banner-title{margin:0 0 14px;font-size:24px;line-height:1.2;font-weight:800;letter-spacing:-.45px;color:#151515}
.tl-flex-banner-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:var(--fbg-gap,12px)}
.tl-flex-banner{display:block;position:relative;overflow:hidden;border-radius:var(--fbg-radius,14px);background:#f3f4f6;box-shadow:0 2px 12px rgba(17,24,39,.08);isolation:isolate}
.tl-flex-banner--full{grid-column:span 4;aspect-ratio:3.25/1}
.tl-flex-banner--half{grid-column:span 2;aspect-ratio:1.55/1}
.tl-flex-banner--quarter{grid-column:span 1;aspect-ratio:1/1}
a.tl-flex-banner{cursor:pointer}
a.tl-flex-banner:focus-visible{outline:3px solid #e85d26;outline-offset:3px}
.tl-flex-banner img{width:100%;height:100%;display:block;object-fit:cover;transition:transform .28s ease}
a.tl-flex-banner:hover img{transform:scale(1.035)}
@media(max-width:640px){
  .tl-flex-banner-title{font-size:20px;margin-bottom:10px}
  .tl-flex-banner-grid.mobile-one{grid-template-columns:1fr}
  .tl-flex-banner-grid.mobile-one .tl-flex-banner{grid-column:span 1;aspect-ratio:1.75/1}
  .tl-flex-banner-grid.mobile-two{grid-template-columns:repeat(2,minmax(0,1fr))}
  .tl-flex-banner-grid.mobile-two .tl-flex-banner--full{grid-column:span 2;aspect-ratio:2.25/1}
  .tl-flex-banner-grid.mobile-two .tl-flex-banner--half,.tl-flex-banner-grid.mobile-two .tl-flex-banner--quarter{grid-column:span 1;aspect-ratio:1/1}
}

@media(max-width:640px){
  .tl-flash-inner{gap:10px}
  .tl-cd-num{font-size:18px}
  .tl-seasonal{padding:32px 20px}
  .tl-seasonal-title{font-size:22px}
  .tl-loyalty-inner{gap:12px}
  .tl-bundle-body{gap:14px}
  .tl-arrival-card{width:120px}
}
</style>
@endpush

@push('scripts')
<script>
function applyPcwCoupon(inputId, msgId) {
  const code = document.getElementById(inputId)?.value?.trim();
  const msgEl = document.getElementById(msgId);
  if (!code) { if(msgEl){msgEl.style.color='#e85d26';msgEl.textContent='Please enter a coupon code.';} return; }
  if(msgEl){msgEl.style.color='var(--c-mid)';msgEl.textContent='Checking…';}
  fetch('/cart/coupon', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
    body: JSON.stringify({ coupon_code: code })
  }).then(r=>r.json()).then(d=>{
    if(msgEl){
      if(d.success){
        msgEl.style.color='#22c55e';
        msgEl.textContent = '✓ ' + (d.message || 'Coupon applied!');
      } else {
        msgEl.style.color='#e85d26';
        msgEl.textContent = d.message || 'Invalid coupon code.';
      }
    }
  }).catch(()=>{ if(msgEl){msgEl.style.color='#e85d26';msgEl.textContent='Could not apply coupon.';} });
}

function nlSubmit(e, form) {
  e.preventDefault();
  form.style.display = 'none';
  form.nextElementSibling.style.display = 'block';
}

function copyCoupon(btn, code) {
  navigator.clipboard.writeText(code).then(() => {
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    btn.style.background = 'rgba(255,255,255,.55)';
    setTimeout(() => { btn.textContent = orig; btn.style.background = ''; }, 2000);
  }).catch(() => {
    const el = document.createElement('textarea');
    el.value = code;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    btn.textContent = 'Copied!';
    setTimeout(() => { btn.textContent = 'Copy'; }, 2000);
  });
}

// ── SLIDER LOGIC ──────────────────────────────────────────────────
const sliderState = {};

function initSlider(slider) {
  const id = slider.id;
  const count = Number(slider.dataset.slideCount);
  if (count < 2) return;
  sliderState[id] = { current: 0, count };
  if (slider && !slider.dataset.touchBound) {
    slider.dataset.touchBound = 'true';
    let startX = 0;
    let startY = 0;
    let tracking = false;
    let horizontalMove = false;
    let suppressClick = false;

    slider.addEventListener('touchstart', (event) => {
      if (!window.matchMedia('(max-width: 640px)').matches || event.touches.length !== 1) return;
      const touch = event.touches[0];
      startX = touch.clientX;
      startY = touch.clientY;
      tracking = true;
      horizontalMove = false;
      slider.classList.add('is-touching');
    }, { passive: true });

    slider.addEventListener('touchmove', (event) => {
      if (!tracking || event.touches.length !== 1) return;
      const touch = event.touches[0];
      const deltaX = touch.clientX - startX;
      const deltaY = touch.clientY - startY;
      if (Math.abs(deltaX) > 8 && Math.abs(deltaX) > Math.abs(deltaY)) {
        horizontalMove = true;
      }
    }, { passive: true });

    const finishTouch = (event) => {
      if (!tracking) return;
      const touch = event.changedTouches?.[0];
      const deltaX = touch ? touch.clientX - startX : 0;
      const deltaY = touch ? touch.clientY - startY : 0;
      tracking = false;
      slider.classList.remove('is-touching');

      if (!window.matchMedia('(max-width: 640px)').matches || !horizontalMove || Math.abs(deltaX) < 45 || Math.abs(deltaX) <= Math.abs(deltaY)) return;
      if (deltaX < 0) slideNext(id);
      else slidePrev(id);
      suppressClick = true;
      window.setTimeout(() => { suppressClick = false; }, 450);
    };

    slider.addEventListener('touchend', finishTouch, { passive: true });
    slider.addEventListener('touchcancel', finishTouch, { passive: true });
    slider.addEventListener('click', (event) => {
      if (!suppressClick) return;
      event.preventDefault();
      event.stopImmediatePropagation();
      suppressClick = false;
    }, true);
  }
}

function slideTo(id, idx) {
  const state = sliderState[id];
  if (!state) return;
  state.current = (idx + state.count) % state.count;
  const track = document.getElementById(id + '-track');
  if (track) track.style.transform = `translateX(-${state.current * 100}%)`;
  document.querySelectorAll(`#${id}-dots .tl-dot`).forEach((d, i) => {
    d.classList.toggle('active', i === state.current);
  });
}

function slideNext(id) {
  const s = sliderState[id];
  if (s) slideTo(id, s.current + 1);
}

function slidePrev(id) {
  const s = sliderState[id];
  if (s) slideTo(id, s.current - 1);
}

// Horizontal product-scroll arrows (Shop by Look, etc.)
// Note: the *track* (flex row of cards) doesn't scroll itself — its parent
// .tl-scroll-section is the actual overflow-x:auto element, so that's what
// we need to scrollBy().
function updateScrollArrows(scroller) {
  if (!scroller) return;
  const wrap = scroller.closest('.tl-scroll-wrap');
  if (!wrap) return;
  const prev = wrap.querySelector('.tl-scroll-arrow.prev');
  const next = wrap.querySelector('.tl-scroll-arrow.next');
  if (prev) prev.disabled = scroller.scrollLeft <= 4;
  if (next) next.disabled = scroller.scrollLeft >= scroller.scrollWidth - scroller.clientWidth - 4;
}

function scrollProducts(id, dir) {
  const track = document.getElementById(id);
  if (!track) return;
  const scroller = track.closest('.tl-scroll-section') || track;
  scroller.scrollBy({ left: dir * scroller.clientWidth * 0.8, behavior: 'smooth' });
  setTimeout(() => updateScrollArrows(scroller), 350);
}

function initializeTimelineWidgets(root = document.querySelector('[data-catalog-active="1"]') || document) {
  root.querySelectorAll('.tl-scroll-wrap .tl-scroll-section').forEach(scroller => {
    updateScrollArrows(scroller);
    if (!scroller.dataset.timelineScrollBound) {
      scroller.dataset.timelineScrollBound = 'true';
      scroller.addEventListener('scroll', () => updateScrollArrows(scroller));
    }
  });
  root.querySelectorAll('.tl-banner-slider[data-slide-count]').forEach(slider => {
    if (!sliderState[slider.id]) initSlider(slider);
  });
  if (typeof window._pcInitializeVariations === 'function') window._pcInitializeVariations(root);
}
initializeTimelineWidgets();
window.addEventListener('resize', () => {
  document.querySelectorAll('.tl-scroll-wrap .tl-scroll-section').forEach(updateScrollArrows);
});

@if(request()->has('tl_preview'))
(function(){
  // ── SECTION COLORS BY TYPE ──
  const COLORS = {
    bannerImage:'#3b82f6', flexBannerGrid:'#7c3aed', category:'#8b5cf6', twoColumn:'#22c55e',
    saleImages:'#f59e0b', seupermarketstars:'#ec4899', topVendors:'#f97316',
    brands:'#06b6d4', coupons:'#eab308', statsBar:'#6366f1',
    promoBlock:'#e85d26', testimonials:'#10b981', newsletter:'#0ea5e9',
    bundle:'#22c55e', loyalty:'#f59e0b', activity:'#ef4444',
    referral:'#0ea5e9', recent:'#8b5cf6', recommended:'#6366f1',
    complete:'#ec4899', trending:'#ef4444', arrivals:'#8b5cf6',
    brandLogos:'#0ea5e9', reviewsCarousel:'#f59e0b', announcement:'#111111',
    flash:'#ef4444', seasonal:'#22c55e', spacer:'#6b7280', divider:'#6b7280',
  };
  const TYPE_LABEL = {
    bannerImage:'Banner / Slider', flexBannerGrid:'Flexible Banner Grid', category:'Categories Strip', twoColumn:'Products Grid',
    saleImages:'Products Scroll', seupermarketstars:'Featured Items', topVendors:'Top Vendors',
    brands:'Brands', coupons:'Coupons Strip', statsBar:'Stats Bar',
    promoBlock:'Promo Block', testimonials:'Testimonials', newsletter:'Newsletter',
    bundle:'Bundle Deal', loyalty:'Loyalty Points', activity:'Live Activity',
    referral:'Referral Widget', recent:'Recently Viewed', recommended:'Recommended',
    complete:'Complete the Look', trending:'Trending Now', arrivals:'New Arrivals',
    brandLogos:'Brand Logos', reviewsCarousel:'Reviews Carousel',
    flash:'Flash Sale Timer', seasonal:'Seasonal Banner', spacer:'Spacer', divider:'Divider',
  };

  // Inject the toolbar
  var toolbar = document.createElement('div');
  toolbar.className = 'tl-preview-toolbar';
  toolbar.innerHTML =
    '<span class="tl-pt-badge">Preview Mode</span>' +
    '<span class="tl-pt-msg">Hover any section to see its widget type</span>' +
    '<div class="tl-pt-spacer"></div>' +
    '<a href="/admin/timeline" class="tl-pt-btn">← Back to Timeline Editor</a>';
  document.body.prepend(toolbar);

  // Decorate each .tl-pw wrapper + wire click → postMessage to parent
  document.querySelectorAll('.tl-pw').forEach(function(el) {
    var layout = el.dataset.layout || '';
    var name   = el.dataset.name   || layout;
    var si     = el.dataset.si     || '';
    var color  = COLORS[layout] || '#e85d26';
    var typeLabel = TYPE_LABEL[layout] || layout;

    el.style.setProperty('--tl-pw-color', color);

    var label = document.createElement('div');
    label.className = 'tl-pw-label';
    var indexEl = document.createElement('span');
    indexEl.className = 'tl-pw-idx';
    indexEl.textContent = String(parseInt(si, 10) + 1);
    var nameEl = document.createElement('span');
    nameEl.textContent = name;
    var typeEl = document.createElement('span');
    typeEl.style.cssText = 'opacity:.65;font-size:10px;font-weight:500;margin-left:2px';
    typeEl.textContent = '· ' + typeLabel;
    var editEl = document.createElement('span');
    editEl.style.cssText = 'opacity:.8;font-size:10px;margin-left:8px;background:rgba(232,93,38,.25);border:1px solid rgba(232,93,38,.5);padding:1px 7px;border-radius:8px;color:#e85d26;font-weight:700';
    editEl.textContent = '✏ Edit';
    label.append(indexEl, nameEl, typeEl, editEl);
    el.prepend(label);

    el.addEventListener('click', function(e) {
      e.stopPropagation();
      var siNum = parseInt(si);
      // notify parent (Live Preview Editor)
      if (window.parent && window.parent !== window) {
        window.parent.postMessage({ type: 'tlSectionClick', si: siNum }, '*');
      }
      // highlight in this page
      document.querySelectorAll('.tl-pw').forEach(function(x) { x.style.outline = ''; });
      el.style.outline = '3px solid ' + color;
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  });

  // Listen for highlight command from parent
  window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'tlHighlight') {
      var target = document.querySelector('.tl-pw[data-si="' + e.data.si + '"]');
      if (target) {
        document.querySelectorAll('.tl-pw').forEach(function(x) { x.style.outline = ''; });
        target.style.outline = '3px solid #e85d26';
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
    if (e.data && e.data.type === 'tlReload') {
      window.location.reload();
    }
  });
})();
@endif
</script>
@endpush

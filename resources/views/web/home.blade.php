@extends('layouts.app')
@section('title', session('locale') === 'ar' ? 'Ramo Store — الرئيسية' : 'Ramo Store — Home')

@section('content')

<style>
@media (max-width:480px) {
  .timeline-widgets .product-grid .product-card {
    min-height:0 !important;
    border:1px solid #ececec !important;
    border-radius:14px !important;
    background:#fff !important;
    box-shadow:0 4px 16px rgba(25,25,25,.06) !important;
    overflow:hidden !important;
  }
  .timeline-widgets .product-grid .product-card-img {
    width:100% !important;
    height:auto !important;
    aspect-ratio:1 / 1 !important;
    border-radius:0 !important;
    background:#f7f7f7 !important;
  }
  .timeline-widgets .product-grid .product-card-body {
    display:flex !important;
    flex-direction:column !important;
    align-items:stretch !important;
    min-width:0 !important;
    padding:9px 8px 10px !important;
    gap:0 !important;
    text-align:center !important;
  }
  .timeline-widgets .product-grid .product-card-name {
    display:-webkit-box !important;
    -webkit-box-orient:vertical !important;
    -webkit-line-clamp:2 !important;
    min-height:31px !important;
    margin:0 !important;
    overflow:hidden !important;
    color:#202020 !important;
    font-size:12px !important;
    font-weight:700 !important;
    line-height:1.3 !important;
    text-align:center !important;
  }
  .timeline-widgets .product-grid .pc-swatches,
  .timeline-widgets .product-grid .pc-sizes {
    justify-content:center !important;
    min-height:19px !important;
    margin:5px 0 0 !important;
    gap:4px !important;
  }
  .timeline-widgets .product-grid .pc-swatches:empty,
  .timeline-widgets .product-grid .pc-sizes:empty { display:none !important; }
  .timeline-widgets .product-grid .pc-swatch {
    width:15px !important;
    height:15px !important;
    border:2px solid #fff !important;
    box-shadow:0 0 0 1px #d6d6d6 !important;
  }
  .timeline-widgets .product-grid .pc-swatch.selected {
    box-shadow:0 0 0 2px var(--c-orange),0 0 0 3px #fff !important;
  }
  .timeline-widgets .product-grid .pc-size {
    min-width:22px !important;
    padding:3px 5px !important;
    border:1px solid #e1e1e1 !important;
    border-radius:6px !important;
    background:#fafafa !important;
    color:#555 !important;
    font-size:9px !important;
    font-weight:700 !important;
    line-height:1 !important;
  }
  .timeline-widgets .product-grid .pc-size.selected {
    border-color:var(--c-dark) !important;
    background:var(--c-dark) !important;
    color:#fff !important;
  }
  .timeline-widgets .product-grid .pc-selected {
    min-height:13px !important;
    max-height:13px !important;
    margin:3px 0 0 !important;
    overflow:hidden !important;
    color:#8b8b8b !important;
    font-size:9px !important;
    line-height:1.4 !important;
    text-align:center !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
  }
  .timeline-widgets .product-grid .product-card-price {
    display:flex !important;
    align-items:baseline !important;
    justify-content:center !important;
    min-height:21px !important;
    margin:6px 0 0 !important;
    gap:4px !important;
    line-height:1 !important;
  }
  .timeline-widgets .product-grid .price-main {
    color:#171717 !important;
    font-size:14px !important;
    font-weight:800 !important;
    white-space:nowrap !important;
  }
  .timeline-widgets .product-grid .price-main.sale { color:var(--c-orange) !important; }
  .timeline-widgets .product-grid .price-old {
    color:#9a9a9a !important;
    font-size:9px !important;
    white-space:nowrap !important;
  }
  .timeline-widgets .product-grid .pc-actions {
    display:block !important;
    margin:7px 0 0 !important;
  }
  .timeline-widgets .product-grid .card-add-btn {
    width:100% !important;
    min-height:31px !important;
    margin:0 !important;
    padding:7px 4px !important;
    border:0 !important;
    border-radius:8px !important;
    background:var(--c-dark) !important;
    color:#fff !important;
    font-size:10px !important;
    font-weight:800 !important;
    line-height:1 !important;
    text-align:center !important;
    white-space:nowrap !important;
  }
  .timeline-widgets .product-grid .card-details-btn {
    display:block !important;
    width:100% !important;
    margin:5px 0 0 !important;
    padding:3px 0 0 !important;
    border:0 !important;
    background:transparent !important;
    color:#777 !important;
    font-size:9px !important;
    font-weight:700 !important;
    line-height:1.2 !important;
    text-align:center !important;
    text-decoration:none !important;
  }
  .timeline-widgets .product-grid .wish-btn {
    top:7px !important;
    right:7px !important;
    width:26px !important;
    height:26px !important;
    border:1px solid rgba(0,0,0,.08) !important;
    background:rgba(255,255,255,.94) !important;
    font-size:14px !important;
  }
  .timeline-widgets .product-grid .pc-coupon-bar { display:none !important; }
}
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
@foreach($sections as $si => $sec)
  @if(($sec['layout'] ?? '') === 'announcement')
    @php
      $aMsg    = $sec['message']    ?? 'Welcome to Ramo Store! Free shipping on orders over 500 EGP.';
      $aSpeed  = $sec['speed']      ?? 'normal';
      $aColor  = $sec['barColor']   ?? 'dark';
      $aDismiss = $sec['dismissableByUser'] ?? true;
      $aBg = match($aColor) {
        'orange' => '#e85d26', 'navy' => '#1a1a2e', 'white' => '#f8f8f8', default => '#111111'
      };
      $aFg = ($aColor === 'white') ? '#111' : '#fff';
      $aSpeed2 = match($aSpeed) { 'slow' => '40s', 'fast' => '15s', 'static' => 'none', default => '25s' };
    @endphp
    <div class="tl-announcement" id="announce-{{ $si }}" style="background:{{ $aBg }};color:{{ $aFg }}">
      @if($aDismiss)
      <button class="tl-announce-close" onclick="document.getElementById('announce-{{ $si }}').style.display='none'" style="color:{{ $aFg }}">×</button>
      @endif
      @if($aSpeed === 'static')
        <div class="tl-announce-static">{{ $aMsg }}</div>
      @else
        <div class="tl-announce-scroll-wrap"><div class="tl-announce-scroll" style="animation-duration:{{ $aSpeed2 }}">
          {{ $aMsg }} &nbsp;·&nbsp; {{ $aMsg }} &nbsp;·&nbsp; {{ $aMsg }}
        </div></div>
      @endif
    </div>
  @endif
@endforeach

<div class="page">

  {{-- ── DYNAMIC TIMELINE SECTIONS ── --}}
  @php
    $inPreview = request()->has('tl_preview');
    $tlSolo    = request()->has('tl_solo') ? (int) request('tl_solo') : null;
  @endphp
  @forelse($sections as $si => $sec)
    @php
      $layout   = $sec['layout'] ?? '';
      $tlNoWrap = in_array($layout, ['logo', 'announcement']);
      $tlName   = $sec['name'] ?? $sec['headerText'] ?? $sec['title'] ?? ucfirst($layout);
      // Responsive dimension config (screen-width based, not device based)
      $tlResp   = $sec['responsive'] ?? [];
      $tlBp     = (int)($tlResp['breakpoint'] ?? 768);
      $tlDesk   = $tlResp['desktop'] ?? [];
      $tlMob    = $tlResp['mobile']  ?? [];
    @endphp
    @if($sec['hidden'] ?? false) @continue @endif
    @if($tlSolo !== null && $si !== $tlSolo) @continue @endif
    @if(!$tlNoWrap)
      {{-- Emit per-section CSS custom properties with media-query breakpoint --}}
      @if($tlDesk || $tlMob)
      <style>
        #tl-sec-{{ $si }} {
          --tl-pad-top:{{ $tlDesk['paddingTop'] ?? 0 }}px;
          --tl-pad-bottom:{{ $tlDesk['paddingBottom'] ?? 0 }}px;
          @if($layout==='bannerImage')
          --tl-banner-h:{{ $tlDesk['bannerHeight'] ?? ($sec['bannerHeight'] ?? 420) }}px;
          --tl-radius:{{ $tlDesk['radius'] ?? ($sec['radius'] ?? 2) }}px;
          @elseif($layout==='categoryCards')
          --tl-columns:{{ $tlDesk['columns'] ?? ($sec['columns'] ?? 3) }};
          --tl-card-h:{{ $tlDesk['cardHeight'] ?? ($sec['cardHeight'] ?? 220) }}px;
          --tl-card-r:{{ $tlDesk['cardBorderRadius'] ?? ($sec['cardBorderRadius'] ?? 14) }}px;
          @elseif(in_array($layout,['twoColumn','saleImages','seupermarketstars']))
          --tl-prod-w:{{ $tlDesk['productWidth'] ?? ($sec['productWidth'] ?? 200) }}px;
          --tl-card-h:{{ (($tlDesk['cardHeight'] ?? ($sec['cardHeight'] ?? 0)) > 0) ? (($tlDesk['cardHeight'] ?? ($sec['cardHeight'] ?? 0)).'px') : 'auto' }};
          --tl-img-w:{{ (($tlDesk['imageWidth'] ?? ($sec['imageWidth'] ?? 0)) > 0) ? (($tlDesk['imageWidth'] ?? ($sec['imageWidth'] ?? 0)).'px') : '100%' }};
          --tl-img-h:{{ $tlDesk['imageHeight'] ?? ($sec['imageHeight'] ?? 200) }}px;
          --tl-element-spacing:{{ max(0, min(40, (int)($tlDesk['elementSpacing'] ?? ($sec['elementSpacing'] ?? 0)))) }}px;
          --tl-card-r:{{ $tlDesk['cardBorderRadius'] ?? ($sec['cardBorderRadius'] ?? 10) }}px;
          @elseif($layout==='spacer')
          --tl-spacer-h:{{ $tlDesk['height'] ?? ($sec['height'] ?? 24) }}px;
          @endif
        }
        @if($tlMob)
        @@media (max-width:{{ $tlBp }}px) {
          #tl-sec-{{ $si }} {
            --tl-pad-top:{{ $tlMob['paddingTop'] ?? 0 }}px;
            --tl-pad-bottom:{{ $tlMob['paddingBottom'] ?? 0 }}px;
            @if($layout==='bannerImage')
            --tl-banner-h:{{ $tlMob['bannerHeight'] ?? ($sec['bannerHeight'] ?? 420) }}px;
            --tl-radius:{{ $tlMob['radius'] ?? ($sec['radius'] ?? 2) }}px;
            @elseif($layout==='categoryCards')
            --tl-columns:{{ $tlMob['columns'] ?? ($sec['columns'] ?? 3) }};
            --tl-card-h:{{ $tlMob['cardHeight'] ?? ($sec['cardHeight'] ?? 220) }}px;
            --tl-card-r:{{ $tlMob['cardBorderRadius'] ?? ($sec['cardBorderRadius'] ?? 14) }}px;
            @elseif(in_array($layout,['twoColumn','saleImages','seupermarketstars']))
            --tl-prod-w:{{ $tlMob['productWidth'] ?? ($sec['productWidth'] ?? 200) }}px;
            --tl-card-h:{{ (($tlMob['cardHeight'] ?? ($sec['cardHeight'] ?? 0)) > 0) ? (($tlMob['cardHeight'] ?? ($sec['cardHeight'] ?? 0)).'px') : 'auto' }};
            --tl-img-w:{{ (($tlMob['imageWidth'] ?? ($sec['imageWidth'] ?? 0)) > 0) ? (($tlMob['imageWidth'] ?? ($sec['imageWidth'] ?? 0)).'px') : '100%' }};
            --tl-img-h:{{ $tlMob['imageHeight'] ?? ($sec['imageHeight'] ?? 200) }}px;
            --tl-element-spacing:{{ max(0, min(40, (int)($tlMob['elementSpacing'] ?? ($sec['elementSpacing'] ?? 0)))) }}px;
            --tl-card-r:{{ $tlMob['cardBorderRadius'] ?? ($sec['cardBorderRadius'] ?? 10) }}px;
            @elseif($layout==='spacer')
            --tl-spacer-h:{{ $tlMob['height'] ?? ($sec['height'] ?? 24) }}px;
            @endif
          }
        }
        @endif
      </style>
      @endif
      {{-- Section wrapper — always present so CSS vars cascade to children --}}
      @if($inPreview)
        <div id="tl-sec-{{ $si }}" class="tl-pw" data-si="{{ $si }}" data-layout="{{ $layout }}" data-name="{{ htmlspecialchars($tlName, ENT_QUOTES) }}" style="padding-top:var(--tl-pad-top,0px);padding-bottom:var(--tl-pad-bottom,0px)">
      @else
        <div id="tl-sec-{{ $si }}" style="padding-top:var(--tl-pad-top,0px);padding-bottom:var(--tl-pad-bottom,0px)">
      @endif
    @endif

    {{-- LOGO — skip (web has its own header) --}}
    @if($layout === 'logo')
      {{-- intentionally skipped --}}

    {{-- ANNOUNCEMENT — already rendered above the page wrapper --}}
    @elseif($layout === 'announcement')
      {{-- intentionally skipped (rendered above .page div) --}}

    {{-- FLASH SALE TIMER --}}
    @elseif($layout === 'flash')
      @php
        $fTitle    = $sec['title']    ?? ($timelineRtl ? 'تخفيضات سريعة' : 'Flash Sale');
        $fDiscount = $sec['discount'] ?? 20;
        $fDuration = (int)($sec['duration'] ?? 4) * 3600;
        $fMinOrder = $sec['minOrder'] ?? 0;
        $fSeconds  = $sec['showCountdownSeconds'] ?? true;
        $fEndTime  = isset($sec['endTime']) && (int)$sec['endTime'] > 0 ? (int)$sec['endTime'] : 0;
      @endphp
      <div class="tl-flash-bar" id="flash-{{ $si }}">
        <div class="tl-flash-inner">
          <span class="tl-flash-icon">⚡</span>
          <div class="tl-flash-text">
            <span class="tl-flash-title">{{ $fTitle }}</span>
            <span class="tl-flash-disc">{{ $fDiscount }}% {{ $timelineRtl ? 'خصم' : 'OFF' }}</span>
            @if($fMinOrder > 0)<span class="tl-flash-min">{{ $timelineRtl ? 'الحد الأدنى للطلب' : 'Min. order' }} {{ number_format($fMinOrder, 0) }} EGP</span>@endif
          </div>
          <div class="tl-flash-countdown" id="flash-cd-{{ $si }}">
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fh-{{ $si }}">00</span><span class="tl-cd-lbl">{{ $timelineRtl ? 'ساعة' : 'HRS' }}</span></div>
            <span class="tl-cd-sep">:</span>
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fm-{{ $si }}">00</span><span class="tl-cd-lbl">{{ $timelineRtl ? 'دقيقة' : 'MIN' }}</span></div>
            @if($fSeconds)
            <span class="tl-cd-sep">:</span>
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fs-{{ $si }}">00</span><span class="tl-cd-lbl">{{ $timelineRtl ? 'ثانية' : 'SEC' }}</span></div>
            @endif
          </div>
          <a href="/shop" class="tl-flash-btn">{{ $timelineRtl ? 'اتسوّق دلوقتي ←' : 'Shop Now →' }}</a>
        </div>
      </div>
      <script>
      (function(){
        var endTime = {{ $fEndTime }};
        var end = endTime > 0 ? endTime : (Date.now() + {{ $fDuration }} * 1000);
        var showSec = {{ $fSeconds ? 'true' : 'false' }};
        var autoDismiss = {{ !empty($sec['autoDismissWhenExpired']) ? 'true' : 'false' }};
        function tick() {
          var rem = Math.max(0, Math.floor((end - Date.now()) / 1000));
          document.getElementById('fh-{{ $si }}').textContent = String(Math.floor(rem/3600)).padStart(2,'0');
          document.getElementById('fm-{{ $si }}').textContent = String(Math.floor((rem%3600)/60)).padStart(2,'0');
          if (showSec) document.getElementById('fs-{{ $si }}').textContent = String(rem%60).padStart(2,'0');
          if (rem === 0) { if (autoDismiss) { var el = document.getElementById('flash-{{ $si }}'); if (el) el.style.display='none'; } return; }
          setTimeout(tick, 1000);
        }
        tick();
      })();
      </script>

    {{-- SPACER --}}
    @elseif($layout === 'spacer')
      <div class="tl-spacer" style="height:var(--tl-spacer-h,{{ $sec['height'] ?? 24 }}px)"></div>

    {{-- DIVIDER --}}
    @elseif($layout === 'divider')
      <hr class="tl-divider">

    {{-- BANNER IMAGE (Slider or Static) --}}
    @elseif($layout === 'bannerImage')
      @php
        $items        = collect($sec['items'] ?? [])->map(function ($item) use ($resolveTimelineImage) {
          if (!is_array($item)) return null;
          $url = $resolveTimelineImage($item['image'] ?? null);
          return $url ? array_replace($item, ['image' => $url]) : null;
        })->filter()->values()->all();
        $isSlider     = ($sec['design'] ?? 'default') !== 'static';
        $radius       = $sec['radius'] ?? 2;
        $sliderId     = 'slider-'.$si;
        $bannerHeight = (int)($sec['bannerHeight'] ?? 420);
      @endphp
      @if(count($items))
        @if($isSlider)
        <div class="tl-banner-slider" id="{{ $sliderId }}" style="border-radius:var(--tl-radius,{{ $radius }}px);margin-bottom:28px;max-height:var(--tl-banner-h,{{ $bannerHeight }}px)">
          <div class="tl-slides" id="{{ $sliderId }}-track">
            @foreach($items as $bi => $item)
              @php
                $url = $item['image'] ?? '';
                $catId = $item['category'] ?? null;
                $href  = $catId ? route('shop', ['category' => $catId]) : '#';
              @endphp
              <div class="tl-slide">
                <a href="{{ $href }}" class="tl-slide-link">
                  <img src="{{ $url }}" alt="Banner {{ $bi+1 }}" loading="{{ $bi===0?'eager':'lazy' }}" style="height:var(--tl-banner-h,{{ $bannerHeight }}px);max-height:var(--tl-banner-h,{{ $bannerHeight }}px)">
                </a>
              </div>
            @endforeach
          </div>
          @if(count($items) > 1)
          <button class="tl-arrow prev" onclick="slidePrev('{{ $sliderId }}')">‹</button>
          <button class="tl-arrow next" onclick="slideNext('{{ $sliderId }}')">›</button>
          <div class="tl-dots" id="{{ $sliderId }}-dots">
            @foreach($items as $bi => $item)
            <div class="tl-dot {{ $bi===0?'active':'' }}" onclick="slideTo('{{ $sliderId }}',{{ $bi }})"></div>
            @endforeach
          </div>
          @endif
        </div>
        @else
          @foreach($items as $item)
            @php
              $url   = $item['image'] ?? '';
              $catId = $item['category'] ?? null;
              $href  = $catId ? route('shop', ['category' => $catId]) : '#';
            @endphp
            <a href="{{ $href }}" class="tl-static-banner" style="border-radius:{{ $radius }}px;overflow:hidden;display:block;margin-bottom:20px">
              <img src="{{ $url }}" alt="Banner" style="width:100%;object-fit:cover;height:var(--tl-banner-h,{{ $bannerHeight }}px);max-height:var(--tl-banner-h,{{ $bannerHeight }}px);display:block">
            </a>
          @endforeach
        @endif
      @endif

    {{-- FLEXIBLE BANNER GRID — separately configurable linked cards --}}
    @elseif($layout === 'flexBannerGrid')
      @php
        $gridItems = collect($sec['items'] ?? [])->map(function ($item) use ($resolveTimelineImage) {
          if (!is_array($item)) return null;
          $url = $resolveTimelineImage($item['image'] ?? null);
          return $url ? array_replace($item, ['image' => $url]) : null;
        })->filter()->values();
        $gridGap = max(0, min(40, (int)($sec['gap'] ?? 12)));
        $gridRadius = max(0, min(40, (int)($sec['radius'] ?? 14)));
        $gridMobileColumns = (int)($sec['mobileColumns'] ?? 2) === 1 ? 1 : 2;
        $gridTitle = trim((string)($sec['headerText'] ?? ''));
      @endphp
      @if($gridItems->isNotEmpty())
        <section class="tl-flex-banner-section">
          @if($gridTitle)<h2 class="tl-flex-banner-title">{{ $gridTitle }}</h2>@endif
          <div class="tl-flex-banner-grid mobile-{{ $gridMobileColumns }}" style="--fbg-gap:{{ $gridGap }}px;--fbg-radius:{{ $gridRadius }}px">
            @foreach($gridItems as $bi => $item)
              @php
                $bannerWidth = in_array(($item['width'] ?? 'half'), ['full', 'half', 'quarter'], true) ? $item['width'] : 'half';
                $requestedLink = trim((string)($item['link'] ?? ''));
                $href = (str_starts_with($requestedLink, '/') || str_starts_with($requestedLink, 'https://') || str_starts_with($requestedLink, 'http://')) ? $requestedLink : '';
                $alt = trim((string)($item['alt'] ?? '')) ?: 'Promotional banner '.($bi + 1);
              @endphp
              @if($href)
                <a href="{{ $href }}" class="tl-flex-banner tl-flex-banner--{{ $bannerWidth }}" aria-label="{{ $alt }}">
                  <img src="{{ $item['image'] }}" alt="{{ $alt }}" loading="{{ $bi === 0 ? 'eager' : 'lazy' }}">
                </a>
              @else
                <div class="tl-flex-banner tl-flex-banner--{{ $bannerWidth }}" role="img" aria-label="{{ $alt }}">
                  <img src="{{ $item['image'] }}" alt="{{ $alt }}" loading="{{ $bi === 0 ? 'eager' : 'lazy' }}">
                </div>
              @endif
            @endforeach
          </div>
        </section>
      @endif

    {{-- CATEGORY STRIP --}}
    @elseif($layout === 'category')
      @php $catItems = $sec['items'] ?? []; @endphp
      @if(count($catItems))
      <div class="tl-cat-strip">
        @foreach($catItems as $ci)
          @php
            $cid    = $ci['category'] ?? null;
            $label  = $ci['label'] ?? ($allCategories[$cid]->name ?? '');
            $displayLabel = \App\Support\StorefrontLabels::category($label, $timelineRtl);
            $img    = $resolveTimelineImage($ci['image'] ?? null);
            $color  = ($ci['colors'][0] ?? '#e85d26');
            $href   = $cid ? route('shop', ['category' => $cid]) : route('shop');
          @endphp
          <a href="{{ $href }}" class="tl-cat-item">
            <div class="tl-cat-img-wrap" style="border-color:{{ $color }}22">
              @if($img)
                <img src="{{ $img }}" alt="{{ $displayLabel }}" class="tl-cat-img" loading="lazy" onerror="this.onerror=null;this.style.display='none';var fallback=this.parentElement.querySelector('.tl-cat-chip');if(fallback)fallback.style.display='flex';">
                <div class="tl-cat-chip" style="background:{{ $color }}22;display:none">🛍️</div>
              @else
                <div class="tl-cat-chip" style="background:{{ $color }}22">🛍️</div>
              @endif
            </div>
            <span class="tl-cat-label">{{ $displayLabel }}</span>
          </a>
        @endforeach
      </div>
      @endif

    {{-- CATEGORY CARDS GRID --}}
    @elseif($layout === 'categoryCards')
      @php
        $cats       = $sectionCategoryCards[$si] ?? collect();
        $title      = $sec['headerText'] ?? 'Shop by Category';
        $columns    = max(2, min(4, (int)($sec['columns'] ?? 3)));
        $cardHeight = (int)($sec['cardHeight'] ?? 220);
        $radius     = isset($sec['cardBorderRadius']) ? (int)$sec['cardBorderRadius'] : 14;
        $showCount  = $sec['showCount'] ?? true;
        $bgPalette  = ['#e85d26','#1a1a2e','#22c55e','#8b5cf6','#f59e0b','#ec4899','#06b6d4','#ef4444'];
      @endphp
      @if($cats->count())
      <div class="sec-head">
        <h2 class="sec-title">{{ $title }}</h2>
        <a href="{{ route('shop') }}" class="sec-link">{{ $timelineSeeAllLabel }}</a>
      </div>
      <div style="display:grid;grid-template-columns:repeat(var(--tl-columns,{{ $columns }}),1fr);gap:16px;margin-bottom:44px">
        @foreach($cats as $ci => $cat)
          @php
            $href      = route('shop', ['category' => $cat->id]);
            $bg        = $cat->thumbnail_url ?? null;
            $fallColor = $bgPalette[$ci % count($bgPalette)];
          @endphp
          <a href="{{ $href }}"
             class="cc-card"
             style="border-radius:var(--tl-card-r,{{ $radius }}px);height:var(--tl-card-h,{{ $cardHeight }}px);background:{{ $bg ? '#111' : $fallColor }}">
            @if($bg)
              <img src="{{ $bg }}" alt="{{ \App\Support\StorefrontLabels::category($cat->name, $timelineRtl) }}" loading="lazy" class="cc-img" onerror="this.onerror=null;this.style.display='none';var fallback=this.parentElement.querySelector('.cc-placeholder');if(fallback)fallback.style.display='flex';">
              <div class="cc-placeholder" style="background:linear-gradient(135deg,{{ $fallColor }},{{ $fallColor }}99);display:none">🛍️</div>
            @else
              <div class="cc-placeholder" style="background:linear-gradient(135deg,{{ $fallColor }},{{ $fallColor }}99)">🛍️</div>
            @endif
            <div class="cc-overlay"></div>
            <div class="cc-label">
              <div class="cc-name">{{ \App\Support\StorefrontLabels::category($cat->name, $timelineRtl) }}</div>
              @if($showCount && $cat->product_count > 0)
                <div class="cc-count">{{ number_format($cat->product_count) }} items</div>
              @endif
            </div>
          </a>
        @endforeach
      </div>
      @endif

    {{-- TWO-COLUMN PRODUCTS GRID --}}
    @elseif($layout === 'twoColumn')
      @php
        $products    = $sectionProducts[$si] ?? collect();
        $title       = $sec['headerText'] ?? $sec['name'] ?? 'Products';
        $catId       = $sec['category'] ?? null;
        $prodWidth   = (int)($sec['productWidth'] ?? 230);
        $imgHeight   = isset($sec['imageHeight']) ? (int)$sec['imageHeight'] : (isset($sec['imageRatio']) ? round($prodWidth * (float)$sec['imageRatio']) : 230);
        $cardRadius  = isset($sec['cardBorderRadius']) ? (int)$sec['cardBorderRadius'] : 12;
        $cardHeight  = max(0, (int)($sec['cardHeight'] ?? 0));
        $imageWidth  = max(0, (int)($sec['imageWidth'] ?? 0));
        $elementSpacing = max(0, min(40, (int)($sec['elementSpacing'] ?? 0)));
          $cardStyle   = '--pc-card-height:var(--tl-card-h,'.($cardHeight > 0 ? $cardHeight.'px' : 'auto').');--pc-image-width:var(--tl-img-w,'.($imageWidth > 0 ? $imageWidth.'px' : '100%').');--pc-image-height:var(--tl-img-h,'.$imgHeight.'px);--pc-element-spacing:var(--tl-element-spacing,'.$elementSpacing.'px)';
        $secId       = 'sg-'.$si;
        $cardOptions = [
          'idPrefix'      => 'tl-'.$si,
          'cardStyle'     => $cardStyle,
          'showBadge'     => $sec['showBadge']     ?? true,
          'showWishlist'  => $sec['showWishlist']  ?? true,
          'showSwatches'  => $sec['showSwatches']  ?? true,
          'showSizes'     => $sec['showSizes']     ?? true,
          'showOldPrice'  => $sec['showOldPrice']  ?? true,
          'showAddToCart' => $sec['showAddToCart'] ?? true,
          'showDetails'   => $sec['showDetails']   ?? true,
          'showCoupon'    => $sec['showCoupon']    ?? true,
          'showRating'    => $sec['showRating']    ?? false,
        ];
      @endphp
      @php $uniformHeight = !empty($sec['uniformHeight']); @endphp
      @if($products->count())
      <style>
        #{{ $secId }} .product-card { border-radius: var(--tl-card-r,{{ $cardRadius }}px) }
        @media(max-width:600px){
          #{{ $secId }} { grid-template-columns:repeat(2,minmax(0,1fr)) !important; gap:10px !important; }
        }
        @if($imgHeight)
        #{{ $secId }} .product-card-img { aspect-ratio: unset; height: var(--tl-img-h,{{ $imgHeight }}px) }
        @endif
        @if($uniformHeight)
        #{{ $secId }} { align-items: stretch }
        #{{ $secId }} .product-card { height: 100%; display: flex; flex-direction: column }
        #{{ $secId }} .product-card-body { flex: 1; display: flex; flex-direction: column }
        #{{ $secId }} .card-add-btn { margin-top: auto }
        @endif
      </style>
      <div class="sec-head">
        <h2 class="sec-title">{{ $title }}</h2>
        <a href="{{ route('shop', array_filter(['category' => $catId])) }}" class="sec-link">{{ $timelineSeeAllLabel }}</a>
      </div>
      <div class="product-grid" id="{{ $secId }}" style="grid-template-columns:repeat(auto-fill,minmax(var(--tl-prod-w,{{ $prodWidth }}px),1fr));margin-bottom:40px">
        @foreach($products as $p)
        @include('web.partials.product-card', ['p' => $p, 'cardVariations' => $sectionVariations[$p->id] ?? [], 'cardOptions' => $cardOptions])
        @endforeach
      </div>
      @endif

    {{-- SALE IMAGES — Horizontal scroll of products --}}
    @elseif($layout === 'saleImages')
      @php
        $products       = $sectionProducts[$si] ?? collect();
        $title          = $sec['headerText'] ?? 'Products';
        $catId          = $sec['category'] ?? null;
        $prodWidth      = (int)($sec['productWidth'] ?? 140);
        $imgHeight      = isset($sec['imageHeight']) ? max(60, (int)$sec['imageHeight']) : (isset($sec['imageRatio']) ? max(60, round($prodWidth * (float)$sec['imageRatio'])) : 196);
        $cardRadius     = isset($sec['cardBorderRadius']) ? (int)$sec['cardBorderRadius'] : 10;
        $cardHeight     = max(0, (int)($sec['cardHeight'] ?? 0));
        $imageWidth     = max(0, (int)($sec['imageWidth'] ?? 0));
        $elementSpacing = max(0, min(40, (int)($sec['elementSpacing'] ?? 0)));
        $cardStyle      = '--pc-card-height:var(--tl-card-h,'.($cardHeight > 0 ? $cardHeight.'px' : 'auto').');--pc-image-width:var(--tl-img-w,'.($imageWidth > 0 ? $imageWidth.'px' : '100%').');--pc-image-height:var(--tl-img-h,'.$imgHeight.'px);--pc-element-spacing:var(--tl-element-spacing,'.$elementSpacing.'px)';
        $secId          = 'sg-'.$si;
        $uniformHeight  = !empty($sec['uniformHeight']);
        $cardOptions = [
          'cardStyle'      => $cardStyle,
          'showBadge'     => $sec['showBadge']     ?? true,
          'showWishlist'  => $sec['showWishlist']  ?? true,
          'showSwatches'  => $sec['showSwatches']  ?? true,
          'showSizes'     => $sec['showSizes']     ?? true,
          'showOldPrice'  => $sec['showOldPrice']  ?? true,
          'showAddToCart' => $sec['showAddToCart'] ?? true,
          'showDetails'   => $sec['showDetails']   ?? true,
          'showCoupon'    => $sec['showCoupon']    ?? true,
          'showRating'    => $sec['showRating']    ?? false,
        ];
      @endphp
      @if($products->count())
      <style>
        #{{ $secId }} .product-card { border-radius: var(--tl-card-r,{{ $cardRadius }}px) }
        #{{ $secId }} .product-card-img { aspect-ratio: unset; height: var(--tl-img-h,{{ $imgHeight }}px) }
        @media(max-width:480px){
          #{{ $secId }} .tl-scroll-card { min-width:0; }
          #{{ $secId }} .product-card {
            min-height:0 !important;
            border:1px solid #ececec !important;
            border-radius:14px !important;
            background:#fff !important;
            box-shadow:0 4px 16px rgba(25,25,25,.06) !important;
            overflow:hidden !important;
          }
          #{{ $secId }} .product-card-img {
            width:100% !important;
            height:auto !important;
            aspect-ratio:1 / 1 !important;
            border-radius:0 !important;
            background:#f7f7f7 !important;
          }
          #{{ $secId }} .product-card-body {
            display:flex !important;
            flex-direction:column !important;
            align-items:stretch !important;
            min-width:0 !important;
            padding:9px 8px 10px !important;
            gap:0 !important;
            text-align:center !important;
          }
          #{{ $secId }} .product-card-name {
            display:-webkit-box !important;
            -webkit-box-orient:vertical !important;
            -webkit-line-clamp:2 !important;
            min-height:31px !important;
            margin:0 !important;
            overflow:hidden !important;
            color:#202020 !important;
            font-size:12px !important;
            font-weight:700 !important;
            line-height:1.3 !important;
            text-align:center !important;
          }
          #{{ $secId }} .pc-swatches,
          #{{ $secId }} .pc-sizes {
            justify-content:center !important;
            min-height:19px !important;
            margin:5px 0 0 !important;
            gap:4px !important;
          }
          #{{ $secId }} .pc-swatches:empty,
          #{{ $secId }} .pc-sizes:empty { display:none !important; }
          #{{ $secId }} .pc-swatch {
            width:15px !important;
            height:15px !important;
            border:2px solid #fff !important;
            box-shadow:0 0 0 1px #d6d6d6 !important;
          }
          #{{ $secId }} .pc-swatch.selected {
            box-shadow:0 0 0 2px var(--c-orange),0 0 0 3px #fff !important;
          }
          #{{ $secId }} .pc-size {
            min-width:22px !important;
            padding:3px 5px !important;
            border:1px solid #e1e1e1 !important;
            border-radius:6px !important;
            background:#fafafa !important;
            color:#555 !important;
            font-size:9px !important;
            font-weight:700 !important;
            line-height:1 !important;
          }
          #{{ $secId }} .pc-size.selected {
            border-color:var(--c-dark) !important;
            background:var(--c-dark) !important;
            color:#fff !important;
          }
          #{{ $secId }} .pc-selected {
            min-height:13px !important;
            max-height:13px !important;
            margin:3px 0 0 !important;
            overflow:hidden !important;
            color:#8b8b8b !important;
            font-size:9px !important;
            line-height:1.4 !important;
            text-align:center !important;
            text-overflow:ellipsis !important;
            white-space:nowrap !important;
          }
          #{{ $secId }} .product-card-price {
            display:flex !important;
            align-items:baseline !important;
            justify-content:center !important;
            min-height:21px !important;
            margin:6px 0 0 !important;
            gap:4px !important;
            line-height:1 !important;
          }
          #{{ $secId }} .price-main {
            color:#171717 !important;
            font-size:14px !important;
            font-weight:800 !important;
            white-space:nowrap !important;
          }
          #{{ $secId }} .price-main.sale { color:var(--c-orange) !important; }
          #{{ $secId }} .price-old {
            color:#9a9a9a !important;
            font-size:9px !important;
            white-space:nowrap !important;
          }
          #{{ $secId }} .pc-actions {
            display:block !important;
            margin:7px 0 0 !important;
          }
          #{{ $secId }} .card-add-btn {
            width:100% !important;
            min-height:31px !important;
            margin:0 !important;
            padding:7px 4px !important;
            border:0 !important;
            border-radius:8px !important;
            background:var(--c-dark) !important;
            color:#fff !important;
            font-size:10px !important;
            font-weight:800 !important;
            line-height:1 !important;
            text-align:center !important;
            white-space:nowrap !important;
          }
          #{{ $secId }} .card-details-btn {
            display:block !important;
            width:100% !important;
            margin:5px 0 0 !important;
            padding:3px 0 0 !important;
            border:0 !important;
            background:transparent !important;
            color:#777 !important;
            font-size:9px !important;
            font-weight:700 !important;
            line-height:1.2 !important;
            text-align:center !important;
            text-decoration:none !important;
          }
          #{{ $secId }} .wish-btn {
            top:7px !important;
            right:7px !important;
            width:26px !important;
            height:26px !important;
            border:1px solid rgba(0,0,0,.08) !important;
            background:rgba(255,255,255,.94) !important;
            font-size:14px !important;
          }
          #{{ $secId }} .pc-coupon-bar { display:none !important; }
        }
        @if($uniformHeight)
        #{{ $secId }} { align-items: stretch }
        #{{ $secId }} .tl-scroll-card { display: flex; flex-direction: column }
        #{{ $secId }} .product-card { height: 100%; display: flex; flex-direction: column }
        #{{ $secId }} .product-card-body { flex: 1; display: flex; flex-direction: column }
        #{{ $secId }} .card-add-btn { margin-top: auto }
        @endif
      </style>
      <div class="sec-head">
        <h2 class="sec-title">{{ $title }}</h2>
        <a href="{{ route('shop', array_filter(['category' => $catId])) }}" class="sec-link">{{ $timelineSeeAllLabel }}</a>
      </div>
      <div class="tl-scroll-wrap" style="margin-bottom:36px">
        <button type="button" class="tl-scroll-arrow prev" aria-label="Scroll left" onclick="scrollProducts('{{ $secId }}', -1)">&#8249;</button>
        <div class="tl-scroll-section">
          <div class="tl-scroll-track" id="{{ $secId }}">
            @foreach($products as $p)
            <div class="tl-scroll-card" style="width:var(--tl-prod-w,{{ $prodWidth }}px)">
              @include('web.partials.product-card', [
                'p'            => $p,
                'cardVariations' => $sectionVariations[$p->id] ?? [],
                'cardOptions'  => $cardOptions,
              ])
            </div>
            @endforeach
          </div>
        </div>
        <button type="button" class="tl-scroll-arrow next" aria-label="Scroll right" onclick="scrollProducts('{{ $secId }}', 1)">&#8250;</button>
      </div>
      @endif

    {{-- SUPERMARKET STARS — product grid by category --}}
    @elseif($layout === 'seupermarketstars')
      @php
        $products   = $sectionProducts[$si] ?? collect();
        $title      = $sec['name'] ?? $sec['headerText'] ?? 'Featured';
        $catId      = $sec['category'] ?? null;
        $prodWidth  = (int)($sec['productWidth'] ?? 200);
        $imgHeight  = isset($sec['imageHeight']) ? (int)$sec['imageHeight'] : (isset($sec['imageRatio']) ? round($prodWidth * (float)$sec['imageRatio']) : 200);
        $cardRadius = isset($sec['cardBorderRadius']) ? (int)$sec['cardBorderRadius'] : 10;
        $cardHeight = max(0, (int)($sec['cardHeight'] ?? 0));
        $imageWidth = max(0, (int)($sec['imageWidth'] ?? 0));
        $elementSpacing = max(0, min(40, (int)($sec['elementSpacing'] ?? 0)));
        $cardStyle  = '--pc-card-height:var(--tl-card-h,'.($cardHeight > 0 ? $cardHeight.'px' : 'auto').');--pc-image-width:var(--tl-img-w,'.($imageWidth > 0 ? $imageWidth.'px' : '100%').');--pc-image-height:var(--tl-img-h,'.$imgHeight.'px);--pc-element-spacing:var(--tl-element-spacing,'.$elementSpacing.'px)';
        $secId      = 'sg-'.$si;
        $cardOptions = [
          'idPrefix'      => 'tl-'.$si,
          'cardStyle'     => $cardStyle,
          'showBadge'     => $sec['showBadge']     ?? true,
          'showWishlist'  => $sec['showWishlist']  ?? true,
          'showSwatches'  => $sec['showSwatches']  ?? true,
          'showSizes'     => $sec['showSizes']     ?? true,
          'showOldPrice'  => $sec['showOldPrice']  ?? true,
          'showAddToCart' => $sec['showAddToCart'] ?? true,
          'showDetails'   => $sec['showDetails']   ?? true,
          'showCoupon'    => $sec['showCoupon']    ?? true,
          'showRating'    => $sec['showRating']    ?? false,
        ];
      @endphp
      @php $uniformHeight = !empty($sec['uniformHeight']); @endphp
      @if($products->count())
      <style>
        #{{ $secId }} .product-card { border-radius: var(--tl-card-r,{{ $cardRadius }}px) }
        @media(max-width:600px){
          #{{ $secId }} { grid-template-columns:repeat(2,minmax(0,1fr)) !important; gap:10px !important; }
        }
        @if($imgHeight)
        #{{ $secId }} .product-card-img { aspect-ratio: unset; height: var(--tl-img-h,{{ $imgHeight }}px) }
        @endif
        @if($uniformHeight)
        #{{ $secId }} { align-items: stretch }
        #{{ $secId }} .product-card { height: 100%; display: flex; flex-direction: column }
        #{{ $secId }} .product-card-body { flex: 1; display: flex; flex-direction: column }
        #{{ $secId }} .card-add-btn { margin-top: auto }
        @endif
      </style>
      <div class="sec-head">
        <h2 class="sec-title">{{ $title }}</h2>
        <a href="{{ route('shop', array_filter(['category' => $catId])) }}" class="sec-link">{{ $timelineSeeAllLabel }}</a>
      </div>
      <div class="product-grid" id="{{ $secId }}" style="grid-template-columns:repeat(auto-fill,minmax(var(--tl-prod-w,{{ $prodWidth }}px),1fr));margin-bottom:40px">
        @foreach($products as $p)
        @include('web.partials.product-card', ['p' => $p, 'cardVariations' => $sectionVariations[$p->id] ?? [], 'cardOptions' => $cardOptions])
        @endforeach
      </div>
      @endif

    {{-- TOP VENDORS ──────────────────────────────────────────────── --}}
    @elseif($layout === 'topVendors')
      @php $vendors = $sectionVendors[$si] ?? collect(); @endphp
      @if($vendors->count())
      <div class="sec-head" style="margin-bottom:16px">
        <h2 class="sec-title">{{ $sec['headerText'] ?? 'Top Sellers' }}</h2>
        <a href="{{ route('shop') }}" class="sec-link">{{ $isAr ? 'شوف الكل ←' : 'Browse all →' }}</a>
      </div>
      <div class="tl-scroll-section" style="margin-bottom:40px">
        <div class="tl-scroll-track" style="gap:14px">
          @foreach($vendors as $v)
          <a href="{{ route('vendor.store', $v->id) }}" class="vendor-card">
            <div class="vendor-card-logo">
              @if($v->logo_url)
                <img src="{{ $v->logo_url }}" alt="{{ $v->shop_name }}" loading="lazy">
              @else
                <div class="vendor-card-logo-placeholder">🏪</div>
              @endif
            </div>
            <div class="vendor-card-name">{{ Str::limit($v->shop_name, 20) }}</div>
            @if($v->product_count > 0)
            <div class="vendor-card-count">{{ $v->product_count }} {{ $timelineRtl ? 'منتج' : 'items' }}</div>
            @endif
            @if((float)$v->rating > 0)
            <div class="vendor-card-rating">
              <span style="color:#f5a623">★</span> {{ number_format((float)$v->rating, 1) }}
            </div>
            @endif
          </a>
          @endforeach
        </div>
      </div>
      @endif

    {{-- COUPONS STRIP --}}
    @elseif($layout === 'coupons')
      @php
        $couponsData = $sectionCoupons[$si] ?? collect();
        $headerText  = $sec['headerText'] ?? ($timelineRtl ? 'عروض الأسبوع' : "This Week's Deals");
        $subLabel    = $sec['subLabel']   ?? ($timelineRtl ? 'استخدم الكود وقت إتمام الطلب' : 'Use code at checkout');
        $hideEmpty   = $sec['hideWhenEmpty'] ?? true;
      @endphp
      @if($couponsData->count() || !$hideEmpty)
      <div class="promo-section">
        <div class="sec-head">
          <h2 class="sec-title">{{ $headerText }}</h2>
          @if($subLabel)
          <span style="font-size:13px;color:var(--c-mid)">{{ $subLabel }}</span>
          @endif
        </div>
        @if($couponsData->count())
        <div class="promo-scroll">
          @foreach($couponsData as $ci => $coupon)
          <div class="coupon-card coupon-card-{{ $ci % 6 }}">
            <div class="coupon-pct">
              @if($coupon->discount_type === 'percent')
                {{ (int)$coupon->amount }}<sup>%</sup><div class="coupon-desc">{{ $timelineRtl ? 'خصم على طلبك' : 'Off your order' }}</div>
              @else
                {{ number_format($coupon->amount, 0) }}<sup> EGP</sup><div class="coupon-desc">{{ $timelineRtl ? 'خصم على طلبك' : 'Off your order' }}</div>
              @endif
            </div>
            <div class="coupon-code-row">
              <span class="coupon-code">{{ strtoupper($coupon->code) }}</span>
              <button class="coupon-copy-btn" onclick="copyCoupon(this,'{{ strtoupper($coupon->code) }}')">{{ $timelineRtl ? 'انسخ' : 'Copy' }}</button>
            </div>
            @if($coupon->minimum_amount > 0)
            <div class="coupon-min">{{ $timelineRtl ? 'الحد الأدنى للطلب' : 'Min. order' }} {{ number_format($coupon->minimum_amount, 0) }} EGP</div>
            @endif
          </div>
          @endforeach
        </div>
        @else
        <p style="color:var(--c-mid);font-size:14px;text-align:center;padding:20px 0">{{ $timelineRtl ? 'مفيش أكواد خصم شغالة دلوقتي.' : 'No active coupons at the moment.' }}</p>
        @endif
      </div>
      @endif

    {{-- STATS BAR --}}
    @elseif($layout === 'statsBar')
      @php
        $stats    = $sectionStats[$si] ?? [];
        $bgColor  = $sec['bgColor'] ?? '#111111';
        $txtColor = $sec['textColor'] ?? '#ffffff';
        $items    = $sec['items'] ?? [
          ['key'=>'products',   'label'=>'Products'],
          ['key'=>'vendors',    'label'=>'Vendors'],
          ['key'=>'categories', 'label'=>'Categories'],
          ['key'=>'brands',     'label'=>'Brands'],
        ];
      @endphp
      @if(!empty($stats))
      <div class="tl-stats-bar" style="background:{{ $bgColor }};color:{{ $txtColor }};margin-bottom:36px">
        @foreach($items as $item)
          @php $val = $stats[$item['key']] ?? 0; @endphp
          <div class="tl-stat-item">
            <div class="tl-stat-num">{{ number_format($val) }}+</div>
            <div class="tl-stat-lbl">{{ $item['label'] }}</div>
          </div>
        @endforeach
      </div>
      @endif

    {{-- PROMO BLOCK --}}
    @elseif($layout === 'promoBlock')
      @php
        $bgColor  = $sec['bgColor'] ?? '#111111';
        $txtColor = $sec['textColor'] ?? '#ffffff';
        $headline = $sec['headline'] ?? ($timelineRtl ? 'عرض مميز' : 'Special Offer');
        $subtext  = $sec['subtext'] ?? '';
        $btnText  = $sec['btnText'] ?? ($timelineRtl ? 'اتسوّق دلوقتي' : 'Shop Now');
        $btnLink  = $sec['btnLink'] ?? route('shop');
        $btnColor = $sec['btnColor'] ?? '#e85d26';
        $align    = $sec['align'] ?? 'center';
        $imgUrl   = $sec['image'] ?? '';
      @endphp
      <div class="tl-promo-block" style="background:{{ $bgColor }};color:{{ $txtColor }};text-align:{{ $align }};margin-bottom:36px;position:relative;overflow:hidden">
        @if($imgUrl)
        <div class="tl-promo-img-wrap">
          <img src="{{ $imgUrl }}" alt="" class="tl-promo-img">
        </div>
        @endif
        <div class="tl-promo-content">
          <h2 class="tl-promo-headline" style="color:{{ $txtColor }}">{{ $headline }}</h2>
          @if($subtext)
          <p class="tl-promo-sub" style="color:{{ $txtColor }}80">{{ $subtext }}</p>
          @endif
          <a href="{{ $btnLink }}" class="tl-promo-btn" style="background:{{ $btnColor }};color:#fff">{{ $btnText }}</a>
        </div>
      </div>

    {{-- TESTIMONIALS --}}
    @elseif($layout === 'testimonials')
      @php
        $reviews = $sectionTestimonials[$si] ?? collect();
        $title   = $sec['headerText'] ?? 'What Our Customers Say';
      @endphp
      @if($reviews->count())
      <div class="sec-head" style="margin-bottom:20px">
        <h2 class="sec-title">{{ $title }}</h2>
      </div>
      <div class="tl-testimonials" style="margin-bottom:44px">
        @foreach($reviews as $rev)
        <div class="tl-testimonial-card">
          <div class="tl-test-stars">
            @for($s=1;$s<=5;$s++)
              <span style="color:{{ $s<=$rev->rating ? '#f5a623' : '#ddd' }}">★</span>
            @endfor
          </div>
          <p class="tl-test-comment">@if($rev->comment)"{{ Str::limit($rev->comment, 160) }}"@else<em style="opacity:.5">{{ $isAr ? 'من غير تعليق' : 'No comment' }}</em>@endif</p>
          <div class="tl-test-meta">
            <div class="tl-test-avatar">{{ strtoupper(substr($rev->reviewer_name, 0, 1)) }}</div>
            <div>
              <div class="tl-test-name">{{ $rev->reviewer_name }}</div>
              @if($rev->product_name)
              <div class="tl-test-product">{{ Str::limit($rev->product_name, 30) }}</div>
              @endif
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif

    {{-- NEWSLETTER --}}
    @elseif($layout === 'newsletter')
      @php
        $bgColor  = $sec['bgColor'] ?? '#f0ede8';
        $headline = $sec['headline'] ?? 'Stay in the Loop';
        $subtext  = $sec['subtext'] ?? 'Get the latest deals and new arrivals delivered to your inbox.';
        $btnText  = $sec['btnText'] ?? 'Subscribe';
        $placeholder = $sec['placeholder'] ?? 'Your email address';
      @endphp
      <div class="tl-newsletter" style="background:{{ $bgColor }};margin-bottom:36px">
        <div class="tl-newsletter-content">
          <h2 class="tl-newsletter-title">{{ $headline }}</h2>
          <p class="tl-newsletter-sub">{{ $subtext }}</p>
          <form class="tl-newsletter-form" onsubmit="nlSubmit(event,this)">
            <input type="email" class="tl-newsletter-input" placeholder="{{ $placeholder }}" required>
            <button type="submit" class="tl-newsletter-btn">{{ $btnText }}</button>
          </form>
          <div class="tl-newsletter-thanks" style="display:none">🎉 Thanks for subscribing!</div>
        </div>
      </div>

    {{-- BRANDS --}}
    @elseif($layout === 'brands')
      @if($brands->count())
      <div class="sec-head" style="margin-bottom:12px">
        <h2 class="sec-title">{{ $sec['name'] ?? 'Brands' }}</h2>
      </div>
      <div class="brand-strip" style="margin-bottom:36px">
        @foreach($brands as $brand)
        <a href="{{ route('shop', ['brand' => $brand->name]) }}" class="brand-chip">{{ $brand->name }}</a>
        @endforeach
      </div>
      @endif

    {{-- TRENDING NOW --}}
    @elseif($layout === 'trending')
      @php $products = $sectionTrending[$si] ?? collect(); $title = $sec['headerText'] ?? 'Trending Now'; @endphp
      @if($products->count())
      <div class="sec-head"><h2 class="sec-title">🔥 {{ $title }}</h2><a href="{{ route('shop') }}" class="sec-link">{{ $timelineSeeAllLabel }}</a></div>
      <div class="tl-scroll-section" style="margin-bottom:36px"><div class="tl-scroll-track">
        @foreach($products as $idx => $p)
        <div class="tl-scroll-card" style="position:relative">
          @if($sec['showRankBadge'] ?? true)<span class="tl-rank-badge">#{{ $loop->iteration }}</span>@endif
          @include('web.partials.product-card', [
            'p'              => $p,
            'cardVariations' => [],
            'cardOptions'    => ['idPrefix' => 'tl-'.$si, 'compact' => true, 'nameLimit' => 28, 'showWishlist' => false, 'showAddToCart' => false, 'showDetails' => false, 'showCoupon' => false, 'showOldPrice' => false],
          ])
        </div>
        @endforeach
      </div></div>
      @endif

    {{-- NEW ARRIVALS TICKER --}}
    @elseif($layout === 'arrivals')
      @php
        $products = $sectionArrivals[$si] ?? collect();
        $tag      = $sec['tag']   ?? 'Just Arrived';
        $loop2    = $sec['loopInfinitely'] ?? true;
        $pause    = $sec['pauseOnHover']   ?? true;
        $tickerId = 'ticker-'.$si;
      @endphp
      @if($products->count())
      <div class="sec-head"><h2 class="sec-title">✨ {{ $sec['headerText'] ?? 'New Arrivals' }}</h2><a href="{{ route('shop') }}" class="sec-link">{{ $timelineSeeAllLabel }}</a></div>
      <div class="tl-arrivals-wrap" id="{{ $tickerId }}" style="margin-bottom:36px" {{ $pause ? 'onmouseenter="this.style.animationPlayState=\'paused\'" onmouseleave="this.style.animationPlayState=\'running\'"' : '' }}>
        <div class="tl-arrivals-track" style="{{ $loop2 ? '' : 'animation:none' }}">
          @foreach(array_merge($products->all(), $products->all()) as $p)
          @php $arrivalName = $p->timeline_name ?? $p->name; @endphp
          <a href="{{ route('product', $p->id) }}" class="tl-arrival-card">
            @if($p->thumbnail_url)<img src="{{ $p->thumbnail_url }}" alt="{{ $arrivalName }}" class="tl-arrival-img">@else<div class="tl-arrival-placeholder">🛍️</div>@endif
            <div class="tl-arrival-body">
              @if($sec['showCategoryChip'] ?? false)<span class="tl-arrival-tag">{{ $tag }}</span>@endif
              <div class="tl-arrival-name">{{ Str::limit($arrivalName, 22) }}</div>
              <div class="tl-arrival-price">{{ number_format($p->on_sale ? $p->sale_price : $p->price, 0) }} EGP</div>
            </div>
          </a>
          @endforeach
        </div>
      </div>
      @endif

    {{-- BRAND LOGOS ROW --}}
    @elseif($layout === 'brandLogos')
      @php
        $bNames = array_filter(array_map('trim', explode(',', $sec['brands'] ?? '')));
        if (empty($bNames)) $bNames = $brands->pluck('name')->take(10)->toArray();
        $bSize = $sec['size'] ?? 'medium';
      @endphp
      @if(!empty($bNames))
      <div class="sec-head" style="margin-bottom:12px"><h2 class="sec-title">{{ $sec['headerText'] ?? 'Shop by Brand' }}</h2></div>
      <div class="tl-brand-logos" style="margin-bottom:36px">
        @foreach($bNames as $bn)
        <a href="{{ route('shop', ['brand' => urlencode($bn)]) }}" class="tl-brand-logo-chip tl-brand-{{ $bSize }}" title="{{ $bn }}">
          <span class="tl-brand-letter">{{ strtoupper(substr($bn, 0, 1)) }}</span>
          @if($sec['showNameBelowLogo'] ?? true)<span class="tl-brand-name">{{ $bn }}</span>@endif
        </a>
        @endforeach
      </div>
      @endif

    {{-- REVIEWS CAROUSEL --}}
    @elseif($layout === 'reviewsCarousel')
      @php
        $revs     = $sectionReviewsCarousel[$si] ?? collect();
        $interval = (int)($sec['interval'] ?? 4) * 1000;
        $manualNav = $sec['allowManualNavigation'] ?? true;
        $carId    = 'revcar-'.$si;
      @endphp
      @if($revs->count())
      <div class="sec-head" style="margin-bottom:20px"><h2 class="sec-title">{{ $sec['headerText'] ?? 'Customer Reviews' }}</h2></div>
      <div class="tl-revcar" id="{{ $carId }}" style="margin-bottom:44px">
        <div class="tl-revcar-track" id="{{ $carId }}-track">
          @foreach($revs as $ri => $rev)
          <div class="tl-revcar-slide" id="{{ $carId }}-slide-{{ $ri }}" style="display:{{ $ri===0 ? 'flex' : 'none' }}">
            <div class="tl-test-stars">@for($s=1;$s<=5;$s++)<span style="color:{{ $s<=$rev->rating ? '#f5a623' : '#ddd' }}">★</span>@endfor</div>
            <p class="tl-test-comment">@if($rev->comment)"{{ Str::limit($rev->comment, 200) }}"@endif</p>
            <div class="tl-test-meta">
              <div class="tl-test-avatar">{{ strtoupper(substr($rev->reviewer_name, 0, 1)) }}</div>
              <div><div class="tl-test-name">{{ $rev->reviewer_name }}</div>
              @if(($sec['showProductReviewed'] ?? true) && $rev->product_name)<div class="tl-test-product">{{ Str::limit($rev->product_name, 30) }}</div>@endif</div>
            </div>
          </div>
          @endforeach
        </div>
        @if($manualNav && $revs->count() > 1)
        <div class="tl-revcar-nav">
          <button onclick="revcarPrev('{{ $carId }}',{{ $revs->count() }})">‹</button>
          <div class="tl-revcar-dots">
            @foreach($revs as $ri => $rev)<span class="tl-revcar-dot {{ $ri===0 ? 'active' : '' }}" onclick="revcarGo('{{ $carId }}',{{ $ri }},{{ $revs->count() }})"></span>@endforeach
          </div>
          <button onclick="revcarNext('{{ $carId }}',{{ $revs->count() }})">›</button>
        </div>
        @endif
      </div>
      <script>
      (function(){
        var id='{{ $carId }}', total={{ $revs->count() }}, cur=0, iv={{ $interval }};
        function go(n){ document.getElementById(id+'-slide-'+cur).style.display='none'; cur=(n+total)%total; document.getElementById(id+'-slide-'+cur).style.display='flex'; document.querySelectorAll('#'+id+' .tl-revcar-dot').forEach(function(d,i){d.classList.toggle('active',i===cur);}); }
        window.revcarNext=function(i,t){if(i===id)go(cur+1);}; window.revcarPrev=function(i,t){if(i===id)go(cur-1);}; window.revcarGo=function(i,n,t){if(i===id)go(n);};
        if(total>1)setInterval(function(){go(cur+1);}, iv);
      })();
      </script>
      @endif

    {{-- LIVE ACTIVITY BANNER --}}
    @elseif($layout === 'activity')
      @php
        $count   = $sectionActivity[$si] ?? 0;
        $minC    = (int)($sec['minCount'] ?? 1);
        $tpl     = $sec['messageTemplate'] ?? '{n} people shopped with us recently';
        $message = str_replace('{n}', $count, $tpl);
        $rand    = $sec['randomizeSlightly'] ?? false;
        if ($rand && $count > 0) $message = str_replace($count, rand(max(1,$count-3), $count+5), $message);
      @endphp
      @if($count >= $minC)
      <div class="tl-activity-bar" style="margin-bottom:16px">
        <span class="tl-activity-dot"></span>
        <span class="tl-activity-msg">{{ $message }}</span>
      </div>
      @endif

    {{-- RECENTLY VIEWED (JS-only, localStorage) --}}
    @elseif($layout === 'recent')
      @php
        $maxP  = (int)($sec['maxProducts']  ?? 8);
        $guests = $sec['showForGuests']     ?? true;
        $loggedOnly = $sec['showOnlyLoggedIn'] ?? false;
      @endphp
      @if(!$loggedOnly || auth()->check())
      <div id="recent-section-{{ $si }}" style="display:none;margin-bottom:36px">
        <div class="sec-head"><h2 class="sec-title">{{ $timelineRtl ? 'شوفتها قبل كده' : 'Recently Viewed' }}</h2></div>
        <div class="tl-scroll-section"><div class="tl-scroll-track" id="recent-track-{{ $si }}"></div></div>
      </div>
      <script>
      (function(){
        var max={{ $maxP }}, sid={{ $si }};
        try {
          var viewed = JSON.parse(localStorage.getItem('rv_products')||'[]');
          if (!viewed.length) return;
          var track = document.getElementById('recent-track-'+sid);
          var section = document.getElementById('recent-section-'+sid);
          viewed.slice(0, max).forEach(function(p){
            var productId = Number(p && p.id);
            if (!Number.isInteger(productId) || productId < 1) return;

            var card = document.createElement('div');
            card.className = 'tl-scroll-card';
            var link = document.createElement('a');
            link.href = '/product/' + productId;
            link.style.textDecoration = 'none';
            var content = document.createElement('div');
            content.style.cssText = 'width:120px;text-align:center';
            var image = document.createElement('img');
            var imageUrl = typeof p.img === 'string' && /^(?:https?:\\/\\/|\\/)/i.test(p.img) ? p.img : '';
            if (imageUrl) image.src = imageUrl;
            image.style.cssText = 'width:110px;height:110px;object-fit:cover;border-radius:8px';
            image.addEventListener('error', function(){ image.style.display = 'none'; });
            var name = document.createElement('div');
            name.style.cssText = 'font-size:11px;color:#333;margin-top:5px;line-height:1.3';
            name.textContent = String(p.name ?? '');
            var price = document.createElement('div');
            price.style.cssText = 'font-size:12px;font-weight:700;color:#e85d26;margin-top:2px';
            price.textContent = String(p.price ?? '') + ' EGP';
            content.append(image, name, price);
            link.appendChild(content);
            card.appendChild(link);
            track.appendChild(card);
          });
          if (track.children.length) section.style.display = 'block';
        } catch(e) {}
      })();
      </script>
      @endif

    {{-- BUNDLE DEAL --}}
    @elseif($layout === 'bundle')
      @php
        $bTitle    = $sec['title']         ?? ($timelineRtl ? 'عرض الباكدج' : 'Bundle Deal');
        $bMinQty   = $sec['minQty']        ?? 2;
        $bFreeItems = $sec['freeItems']    ?? 1;
        $bCat      = $sec['category']      ?? '';
        $bSavings  = $sec['showSavingsBadge'] ?? true;
      @endphp
      <div class="tl-bundle-card" style="margin-bottom:36px">
        @if($bSavings)<div class="tl-bundle-badge">{{ $timelineRtl ? 'عرض مميز' : 'Special Deal' }}</div>@endif
        <div class="tl-bundle-body">
          <div class="tl-bundle-icon">🎁</div>
          <div class="tl-bundle-info">
            <div class="tl-bundle-title">{{ $bTitle }}</div>
            <div class="tl-bundle-desc">{{ $timelineRtl ? 'اشتري' : 'Buy' }} <strong>{{ $bMinQty }}</strong> {{ $timelineRtl ? 'منتجات وخد' : 'items, get' }} <strong>{{ $bFreeItems }}</strong> {{ $timelineRtl ? 'مجانًا' : 'FREE' }}{{ $bCat ? ($timelineRtl ? ' من '.$bCat : ' from '.$bCat) : '' }}!</div>
          </div>
          <a href="/shop" class="tl-bundle-btn">{{ $timelineRtl ? 'اتسوّق دلوقتي' : 'Shop Now' }}</a>
        </div>
      </div>

    {{-- LOYALTY POINTS BANNER --}}
    @elseif($layout === 'loyalty')
      @php
        $lRate    = $sec['rate']      ?? 10;
        $lMin     = $sec['minRedeem'] ?? 100;
        $lConv    = $sec['convRate']  ?? '100 pts = 5 EGP';
        $lDouble  = $sec['doublePointsWeekends'] ?? false;
        $isWeekend = in_array(now()->dayOfWeek, [0, 6]);
      @endphp
      <div class="tl-loyalty-bar" style="margin-bottom:36px">
        <div class="tl-loyalty-inner">
          <span class="tl-loyalty-icon">⭐</span>
          <div class="tl-loyalty-text">
            <strong>{{ $timelineRtl ? 'اكسب' : 'Earn' }} {{ $lRate }} {{ $timelineRtl ? 'نقطة مع كل جنيه بتصرفه!' : 'points per EGP spent!' }}</strong>
            <span>{{ $timelineRtl ? 'استبدل من' : 'Redeem from' }} {{ $lMin }} {{ $timelineRtl ? 'نقطة' : 'pts' }} · {{ $lConv }}
            @if($lDouble && $isWeekend) <span class="tl-loyalty-double">{{ $timelineRtl ? 'ويك إند بنقط مضاعفة ×2!' : '2× Points Weekend!' }}</span>@endif
            </span>
          </div>
          <a href="/shop" class="tl-loyalty-btn">{{ $timelineRtl ? 'ابدأ تجمع نقاط' : 'Start Earning' }}</a>
        </div>
      </div>

    {{-- SEASONAL BANNER --}}
    @elseif($layout === 'seasonal')
      @php
        $sTitle    = $sec['title']     ?? ($timelineRtl ? 'موسم مميز' : 'Special Season');
        $sSub      = $sec['subtitle']  ?? ($timelineRtl ? 'عروض لفترة محدودة الموسم ده' : 'Limited-time offers for this season');
        $sStart    = $sec['startDate'] ?? null;
        $sEnd      = $sec['endDate']   ?? null;
        $sTheme    = $sec['theme']     ?? 'Gold & Purple';
        $sCountdown = $sec['showCountdownToEvent'] ?? false;
        $sAnimate  = $sec['animateEntrance'] ?? true;
        $now2      = now();
        $inRange   = (!$sStart || $now2->gte(\Carbon\Carbon::parse($sStart)))
                  && (!$sEnd   || $now2->lte(\Carbon\Carbon::parse($sEnd)));
        $themeCss  = match($sTheme) {
          'Green & White'  => 'background:linear-gradient(135deg,#1a7a3a,#2ecc71);color:#fff',
          'Red & Gold'     => 'background:linear-gradient(135deg,#c0392b,#f39c12);color:#fff',
          'Gold & Purple'  => 'background:linear-gradient(135deg,#6c3483,#f9ca24);color:#fff',
          default          => 'background:linear-gradient(135deg,#1a1a2e,#e85d26);color:#fff',
        };
      @endphp
      @if($inRange)
      <div class="tl-seasonal {{ $sAnimate ? 'tl-seasonal-animate' : '' }}" style="{{ $themeCss }};margin-bottom:36px">
        <div class="tl-seasonal-inner">
          <div class="tl-seasonal-text">
            <h2 class="tl-seasonal-title">{{ $sTitle }}</h2>
            <p class="tl-seasonal-sub">{{ $sSub }}</p>
          </div>
          @if($sCountdown && $sEnd)
          <div class="tl-seasonal-cd" id="seas-cd-{{ $si }}">
            <span id="sd-d-{{ $si }}">00</span>{{ $timelineRtl ? ' يوم' : 'd' }} <span id="sd-h-{{ $si }}">00</span>{{ $timelineRtl ? ' س' : 'h' }} <span id="sd-m-{{ $si }}">00</span>{{ $timelineRtl ? ' د' : 'm' }}
          </div>
          <script>
          (function(){
            var end=new Date('{{ $sEnd }}T23:59:59').getTime();
            function tick2(){var r=Math.max(0,Math.floor((end-Date.now())/1000));document.getElementById('sd-d-{{ $si }}').textContent=String(Math.floor(r/86400)).padStart(2,'0');document.getElementById('sd-h-{{ $si }}').textContent=String(Math.floor((r%86400)/3600)).padStart(2,'0');document.getElementById('sd-m-{{ $si }}').textContent=String(Math.floor((r%3600)/60)).padStart(2,'0');if(r>0)setTimeout(tick2,30000);}tick2();
          })();
          </script>
          @endif
          <a href="/shop" class="tl-seasonal-btn">{{ $timelineRtl ? 'اتسوّق دلوقتي ←' : 'Shop Now →' }}</a>
        </div>
      </div>
      @endif

    {{-- REFERRAL WIDGET --}}
    @elseif($layout === 'referral')
      @php
        $rRef   = $sec['rewardReferrer'] ?? 50;
        $rNew   = $sec['rewardNewUser']  ?? 30;
        $rMin   = $sec['minOrder']       ?? 200;
        $rCta   = $sec['ctaText']        ?? ($timelineRtl ? 'اعزم صحابك واكسب!' : 'Invite Friends & Earn!');
        $rWa    = $sec['shareViaWhatsApp'] ?? true;
      @endphp
      <div class="tl-referral-card" style="margin-bottom:36px">
        <div class="tl-referral-inner">
          <div class="tl-referral-icon">🎁</div>
          <div class="tl-referral-body">
            <div class="tl-referral-title">{{ $rCta }}</div>
            <div class="tl-referral-desc">{{ $timelineRtl ? 'هتاخد' : 'You earn' }} <strong>{{ $rRef }} EGP</strong> {{ $timelineRtl ? 'وصاحبك هياخد خصم' : 'and your friend gets' }} <strong>{{ $rNew }} EGP</strong> {{ $timelineRtl ? 'على أول طلب فوق' : 'off their first order over' }} {{ $rMin }} EGP!</div>
            <div class="tl-referral-actions">
              <input type="text" class="tl-referral-link" value="{{ url('/') }}/ref/{{ auth()->id() ?? 'YOURCODE' }}" readonly onclick="this.select()">
              @if($rWa)
              <a href="https://wa.me/?text={{ urlencode($timelineRtl ? 'انضم لـ Ramo Store وخد خصم '.($rNew).' جنيه على أول طلب! '.url('/').'/ref/'.(auth()->id() ?? 'YOURCODE') : 'Join Ramo Store and get '.($rNew).' EGP off your first order! '.url('/').'/ref/'.(auth()->id() ?? 'YOURCODE')) }}" target="_blank" class="tl-referral-wa-btn">{{ $timelineRtl ? 'شارك على واتساب' : 'Share via WhatsApp' }}</a>
              @endif
            </div>
          </div>
        </div>
      </div>

    {{-- COMPLETE THE LOOK --}}
    @elseif($layout === 'complete')
      @php
        $cStrategy = $sec['strategy'] ?? 'Same category';
        $cDisc     = $sec['showDiscountIfBoughtTogether'] ?? false;
      @endphp
      <div class="tl-complete-card" style="margin-bottom:36px">
        <div class="tl-complete-inner">
          <span style="font-size:28px">👗</span>
          <div>
            <div style="font-weight:700;font-size:16px;margin-bottom:4px">{{ $timelineRtl ? 'كمّل اللوك' : 'Complete the Look' }}</div>
            <div style="font-size:13px;color:var(--c-mid)">{{ $timelineRtl ? 'شوف منتجات راكبة على بعض بشكل حلو.' : 'Find '.strtolower($cStrategy).' items that go perfectly together.' }}@if($cDisc) {{ $timelineRtl ? 'الخصم بيتحسب وقت إتمام الطلب!' : 'Bundle discount applied at checkout!' }}@endif</div>
          </div>
          <a href="/shop" class="btn btn-primary" style="font-size:13px;padding:10px 20px">{{ $timelineRtl ? 'تصفّح التشكيلات ←' : 'Browse Collections →' }}</a>
        </div>
      </div>

    {{-- RECOMMENDED FOR YOU --}}
    @elseif($layout === 'recommended')
      @php
        $products = $sectionTrending[$si] ?? collect();
        $recLabel = $sec['personalizedLabel'] ?? false;
        $title    = $recLabel ? ($timelineRtl ? 'مختارينهم ليك' : 'Picked For You') : ($sec['headerText'] ?? ($timelineRtl ? 'مقترحات ليك' : 'Recommended For You'));
      @endphp
      @if($products->count())
      <div class="sec-head"><h2 class="sec-title">{{ $title }}</h2><a href="{{ route('shop') }}" class="sec-link">{{ $timelineSeeAllLabel }}</a></div>
      <div class="tl-scroll-section" style="margin-bottom:36px"><div class="tl-scroll-track">
        @foreach($products as $p)
        <div class="tl-scroll-card">
          @include('web.partials.product-card', [
            'p'              => $p,
            'cardVariations' => [],
            'cardOptions'    => ['idPrefix' => 'tl-'.$si, 'compact' => true, 'nameLimit' => 28, 'showWishlist' => false, 'showAddToCart' => false, 'showDetails' => false, 'showCoupon' => false, 'showOldPrice' => false],
          ])
        </div>
        @endforeach
      </div></div>
      @endif

    {{-- PRODUCT CUSTOMIZER WIDGET --}}
    @elseif($layout === 'productCustomizer')
      @php
        $fp  = $sectionFeaturedProduct[$si]    ?? null;
        $fv  = $sectionFeaturedVariations[$si] ?? collect();
        $st  = $sec['sectionTitle'] ?? '';
        $showWishlist   = $sec['showWishlist']   ?? true;
        $showRating     = $sec['showRating']     ?? true;
        $showVariations = $sec['showVariations'] ?? true;
        $showCoupon     = $sec['showCoupon']     ?? true;
      @endphp
      @if($fp)
        @php
          $pcwInWishlist = false;
          if (auth()->check()) {
            $pcwInWishlist = DB::table('wishlists')->where('user_id', auth()->id())->where('product_id', $fp->id)->exists();
          } else {
            $ws = session('ramo_wishlist', []);
            $pcwInWishlist = isset($ws[$fp->id]);
          }
          $pcwRevs  = DB::table('product_reviews')->where('product_id', $fp->id)->where('approved', true)->get(['rating']);
          $pcwTotal = $pcwRevs->count();
          $pcwAvg   = $pcwTotal ? round($pcwRevs->avg('rating'), 1) : 0;
          $pcwName     = ($timelineRtl && !empty($fp->tl_display_name)) ? $fp->tl_display_name : ($fp->timeline_name ?? $fp->name);
          $pcwDiscPct  = (float)($fp->discount_percentage ?? 0);
          $pcwHasDisc  = $pcwDiscPct > 0;
          $pcwMinEff   = $fv->min('price') ?? $fp->display_price;
          $pcwMaxEff   = $fv->max('price') ?? $fp->display_price;
          $pcwMinReg   = $fv->min('regular_price') ?? $pcwMinEff;
          $pcwIsRange  = $fv->count() > 0 && round((float)$pcwMinEff,2) !== round((float)$pcwMaxEff,2);
          $pcwAttrMap  = [];
          $pcwMinimumOrderQty = max(1, (int) ($fp->minimum_order_qty ?? 1));
          $pcwMaximumOrderQty = max(0, (int) ($fp->stock_quantity ?? 0));
          $pcwConfiguredMaximum = (int) ($fp->max_orders_per_person ?? 0);
          if ($pcwConfiguredMaximum > 0) $pcwMaximumOrderQty = min($pcwMaximumOrderQty, $pcwConfiguredMaximum);
          if ($fp->sold_individually ?? false) $pcwMaximumOrderQty = min($pcwMaximumOrderQty, 1);
          // This widget cannot select a variation, so only one-unit orders can be quick-added safely.
          $pcwCanQuickAdd = $pcwMinimumOrderQty === 1 && $pcwMaximumOrderQty >= 1;
          foreach ($fv as $v) {
            foreach (($v->attributes ?? []) as $k => $val) {
              if (!isset($pcwAttrMap[$k])) $pcwAttrMap[$k] = [];
              if (!in_array($val, $pcwAttrMap[$k])) $pcwAttrMap[$k][] = $val;
            }
          }
        @endphp
        @if($st)
        <div class="sec-head" style="margin-bottom:20px"><h2 class="sec-title">{{ $st }}</h2></div>
        @endif
        <div class="pcw-card" style="margin-bottom:44px">
          <a href="{{ route('product', $fp->id) }}" class="pcw-img-wrap">
            @if($fp->thumbnail_url)
              <img src="{{ $fp->thumbnail_url }}" alt="{{ $pcwName }}" class="pcw-img" loading="lazy">
            @else
              <div class="pcw-img-placeholder">🛍️</div>
            @endif
            @if($pcwHasDisc)<span class="badge-sale">{{ round($pcwDiscPct) }}% {{ $timelineRtl ? 'خصم' : 'OFF' }}</span>@endif
          </a>
          <div class="pcw-info">
            <div class="pcw-title-row">
              <h2 class="pcw-title"><a href="{{ route('product', $fp->id) }}" style="color:inherit;text-decoration:none">{{ $pcwName }}</a></h2>
              @if($showWishlist)
              <button class="pcw-wish-btn {{ $pcwInWishlist ? 'wished' : '' }}"
                      data-wishlist-product-id="{{ $fp->id }}"
                      onclick="toggleWishlist(this, {{ $fp->id }})"
                      title="{{ $pcwInWishlist ? ($timelineRtl ? 'شيل من المفضلة' : 'Remove from Wishlist') : ($timelineRtl ? 'ضيف للمفضلة' : 'Add to Wishlist') }}">{{ $pcwInWishlist ? '♥' : '♡' }}</button>
              @endif
            </div>
            @if($showRating)
            <div class="pcw-rating-row">
              <div class="pcw-stars">
                @for($s=1;$s<=5;$s++)<span style="color:{{ $s<=round($pcwAvg)?'#f5a623':'#ddd' }}">★</span>@endfor
              </div>
              @if($pcwTotal)
                <span class="pcw-rating-val">{{ $pcwAvg }}</span>
                <a href="{{ route('product', $fp->id) }}#reviews" class="pcw-rating-cnt">({{ $pcwTotal }} {{ $timelineRtl ? 'تقييم' : 'review'.($pcwTotal!=1?'s':'') }})</a>
              @else
                <span class="pcw-rating-none">{{ $timelineRtl ? 'مفيش تقييمات لسه' : 'No reviews yet' }}</span>
              @endif
            </div>
            @endif
            <div class="pcw-stock">
              @if($fp->stock_quantity > 0)
                <span class="pcw-stock-ok">✓ {{ $timelineRtl ? 'متاح' : 'In Stock' }} ({{ number_format($fp->stock_quantity) }} {{ $timelineRtl ? 'قطعة' : 'available' }})</span>
              @else
                <span class="pcw-stock-no">{{ $timelineRtl ? 'مش متاح دلوقتي' : 'Out of Stock' }}</span>
              @endif
            </div>
            <div class="pcw-price-block">
              <div class="pcw-price-row">
                @if($pcwIsRange)
                  <span class="pcw-price on-sale">{{ number_format((float)$pcwMinEff,2) }} – {{ number_format((float)$pcwMaxEff,2) }} EGP</span>
                @elseif($pcwHasDisc)
                  <span class="pcw-price on-sale">{{ number_format((float)$pcwMinEff,2) }} EGP</span>
                  <span class="pcw-price-orig">{{ number_format((float)$pcwMinReg,2) }} EGP</span>
                @else
                  <span class="pcw-price">{{ number_format((float)$pcwMinEff,2) }} EGP</span>
                @endif
                @if($pcwHasDisc)<span class="pcw-disc-badge">{{ round($pcwDiscPct) }}% {{ $timelineRtl ? 'خصم' : 'OFF' }}</span>@endif
              </div>
              @if($pcwHasDisc)
              <div class="pcw-sale-note">🏷️ {{ $timelineRtl ? 'سعر العرض — وفّرت' : 'Sale price — you save' }} {{ round($pcwDiscPct) }}% {{ $timelineRtl ? 'من السعر الأصلي' : 'off the original price' }}</div>
              @endif
            </div>
            @if($showVariations && !empty($pcwAttrMap))
            <div class="pcw-variations">
              @foreach($pcwAttrMap as $attrKey => $attrValues)
                @php $isColor = strtolower($attrKey) === 'color'; @endphp
                <div class="pcw-var-group">
                  <div class="pcw-var-label">{{ strtoupper($attrKey) }}</div>
                  <div class="pcw-var-opts">
                    @foreach($attrValues as $val)
                      @if($isColor)
                        <span class="pcw-swatch" title="{{ $val }}" style="background:var(--swatch-{{ Str::slug($val) }},#999)"></span>
                      @else
                        <span class="pcw-chip">{{ $val }}</span>
                      @endif
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
            @endif
            <div class="pcw-cart-row">
              @if($pcwCanQuickAdd)
                <div class="qty-input">
                  <button type="button" onclick="this.nextElementSibling.value=Math.max(1,+this.nextElementSibling.value-1)">−</button>
                  <input type="number" value="1" min="1" max="1" id="pcw-qty-{{ $si }}" readonly aria-label="{{ $timelineRtl ? 'الكمية' : 'Quantity' }}">
                  <button type="button" disabled aria-label="{{ $timelineRtl ? 'وصلت لأقصى كمية' : 'Maximum quantity reached' }}">+</button>
                </div>
                <button class="pcw-atc-btn"
                        onclick="addToCart({{ $fp->id }}, '{{ addslashes($pcwName) }}', {{ (float)$pcwMinEff }}, '{{ $fp->thumbnail_url }}', null, 1)">
                  🛒 {{ $timelineRtl ? 'ضيف للسلة' : 'Add to Cart' }}
                </button>
              @else
                <a class="pcw-atc-btn" href="{{ route('product', $fp->id) }}" style="text-align:center;text-decoration:none">
                  {{ $pcwMaximumOrderQty < $pcwMinimumOrderQty ? ($timelineRtl ? 'مش متاح' : 'Unavailable') : ($timelineRtl ? 'اختار الكمية (الحد الأدنى '.$pcwMinimumOrderQty.')' : 'Select quantity (min '.$pcwMinimumOrderQty.')') }}
                </a>
              @endif
            </div>
            @if($showCoupon)
            <div class="pcw-coupon-wrap">
              <div class="pcw-coupon-label">🏷️ {{ $timelineRtl ? 'معاك كود خصم؟' : 'Have a coupon?' }}</div>
              <div class="pcw-coupon-row">
                <input type="text" id="pcw-coupon-{{ $si }}" class="pcw-coupon-input" placeholder="{{ $timelineRtl ? 'اكتب كود الخصم' : 'Enter promo code' }}" maxlength="50">
                <button class="pcw-coupon-btn" onclick="applyPcwCoupon('pcw-coupon-{{ $si }}','pcw-msg-{{ $si }}')">{{ $timelineRtl ? 'طبّق' : 'Apply' }}</button>
              </div>
              <div id="pcw-msg-{{ $si }}" class="pcw-coupon-msg"></div>
            </div>
            @endif
            <a href="{{ route('product', $fp->id) }}" class="pcw-view-link">{{ $timelineRtl ? 'شوف التفاصيل كلها ←' : 'View full details →' }}</a>
          </div>
        </div>
      @endif

    {{-- FLASH / ANNOUNCEMENT: already rendered above, skip here --}}
    @elseif($layout === 'flash' || $layout === 'announcement')
      {{-- intentionally skipped — rendered as full-width elements above --}}

    @endif
    @if(!$tlNoWrap)
      </div>
    @endif

  @empty
    {{-- Fallback when no timeline config --}}
    <div class="hero">
      <div class="hero-text">
        <div class="hero-eyebrow">{{ $timelineRtl ? 'الموسم الجديد' : 'New Season' }}</div>
        <h1 class="hero-title">{{ $timelineRtl ? 'ستايل بيعبّر<br>عنك.' : 'Style that speaks<br>for itself.' }}</h1>
        <p class="hero-sub">{{ $timelineRtl ? 'اكتشف أحدث التشكيلات — جودة عالية لحد باب البيت.' : 'Discover the latest collections — premium quality, delivered to your door.' }}</p>
        <a href="{{ route('shop') }}" class="btn btn-white">{{ $timelineRtl ? 'اتسوّق دلوقتي ←' : 'Shop Now →' }}</a>
      </div>
    </div>
  @endforelse

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
  .tl-promo-block{flex-direction:column;padding:36px 24px;text-align:center}
  .tl-promo-img-wrap{width:100%;height:180px}
  .tl-newsletter-form{flex-direction:column}
}

/* ── ANNOUNCEMENT BAR ── */
.tl-announcement{position:relative;width:100%;padding:9px 40px 9px 16px;font-size:13px;font-weight:500;overflow:hidden;z-index:100}
.tl-announce-close{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;font-size:20px;cursor:pointer;line-height:1;padding:0}
.tl-announce-static{text-align:center}
.tl-announce-scroll-wrap{overflow:hidden;white-space:nowrap}
.tl-announce-scroll{display:inline-block;animation:scrollTicker 25s linear infinite}
@keyframes scrollTicker{from{transform:translateX(0)}to{transform:translateX(-33.333%)}}

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

/* ── NEW ARRIVALS TICKER ── */
.tl-arrivals-wrap{overflow:hidden;cursor:pointer}
.tl-arrivals-track{display:flex;gap:14px;animation:arrivalsScroll 30s linear infinite}
@keyframes arrivalsScroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.tl-arrival-card{display:flex;flex-direction:column;flex-shrink:0;width:140px;text-decoration:none;color:inherit;border:1.5px solid var(--c-light);border-radius:10px;overflow:hidden;transition:box-shadow .15s}
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

function initSlider(id, count, autoPlay) {
  sliderState[id] = { current: 0, count, timer: null };
  const slider = document.getElementById(id);
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
  if (autoPlay && count > 1) {
    sliderState[id].timer = setInterval(() => slideNext(id), 4000);
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
  // Reset timer on manual navigation
  if (state.timer) {
    clearInterval(state.timer);
    state.timer = setInterval(() => slideNext(id), 4000);
  }
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

document.querySelectorAll('.tl-scroll-wrap .tl-scroll-section').forEach(scroller => {
  updateScrollArrows(scroller);
  scroller.addEventListener('scroll', () => updateScrollArrows(scroller));
});
window.addEventListener('resize', () => {
  document.querySelectorAll('.tl-scroll-wrap .tl-scroll-section').forEach(updateScrollArrows);
});

// Init all sliders declared in page
@foreach($sections as $si => $sec)
  @if(($sec['layout'] ?? '') === 'bannerImage')
    @php
      $items    = $sec['items'] ?? [];
      $isSlider = ($sec['design'] ?? 'default') !== 'static' && count($items) > 1;
      $autoPlay = $sec['autoPlay'] ?? true;
    @endphp
    @if($isSlider && count($items) > 1)
      initSlider('slider-{{ $si }}', {{ count($items) }}, {{ $autoPlay ? 'true' : 'false' }});
    @endif
  @endif
@endforeach

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

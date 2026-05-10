@extends('layouts.app')
@section('title', 'Ramo Store — Home')

@section('content')

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
      $layout    = $sec['layout'] ?? '';
      $tlNoWrap  = in_array($layout, ['logo', 'announcement']);
      $tlName    = $sec['name'] ?? $sec['headerText'] ?? $sec['title'] ?? ucfirst($layout);
    @endphp
    @if($sec['hidden'] ?? false) @continue @endif
    @if($tlSolo !== null && $si !== $tlSolo) @continue @endif
    @if($inPreview && !$tlNoWrap)<div class="tl-pw" data-si="{{ $si }}" data-layout="{{ $layout }}" data-name="{{ htmlspecialchars($tlName, ENT_QUOTES) }}">@endif

    {{-- LOGO — skip (web has its own header) --}}
    @if($layout === 'logo')
      {{-- intentionally skipped --}}

    {{-- ANNOUNCEMENT — already rendered above the page wrapper --}}
    @elseif($layout === 'announcement')
      {{-- intentionally skipped (rendered above .page div) --}}

    {{-- FLASH SALE TIMER --}}
    @elseif($layout === 'flash')
      @php
        $fTitle    = $sec['title']    ?? 'Flash Sale';
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
            <span class="tl-flash-disc">{{ $fDiscount }}% OFF</span>
            @if($fMinOrder > 0)<span class="tl-flash-min">Min. order {{ number_format($fMinOrder, 0) }} EGP</span>@endif
          </div>
          <div class="tl-flash-countdown" id="flash-cd-{{ $si }}">
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fh-{{ $si }}">00</span><span class="tl-cd-lbl">HRS</span></div>
            <span class="tl-cd-sep">:</span>
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fm-{{ $si }}">00</span><span class="tl-cd-lbl">MIN</span></div>
            @if($fSeconds)
            <span class="tl-cd-sep">:</span>
            <div class="tl-cd-unit"><span class="tl-cd-num" id="fs-{{ $si }}">00</span><span class="tl-cd-lbl">SEC</span></div>
            @endif
          </div>
          <a href="/shop" class="tl-flash-btn">Shop Now →</a>
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
      <div class="tl-spacer" style="height:{{ $sec['height'] ?? 24 }}px"></div>

    {{-- DIVIDER --}}
    @elseif($layout === 'divider')
      <hr class="tl-divider">

    {{-- BANNER IMAGE (Slider or Static) --}}
    @elseif($layout === 'bannerImage')
      @php
        $items        = $sec['items'] ?? [];
        $isSlider     = ($sec['design'] ?? 'default') !== 'static';
        $radius       = $sec['radius'] ?? 2;
        $sliderId     = 'slider-'.$si;
        $bannerHeight = (int)($sec['bannerHeight'] ?? 420);
      @endphp
      @if(count($items))
        @if($isSlider)
        <div class="tl-banner-slider" id="{{ $sliderId }}" style="border-radius:{{ $radius }}px;margin-bottom:28px;max-height:{{ $bannerHeight }}px">
          <div class="tl-slides" id="{{ $sliderId }}-track">
            @foreach($items as $bi => $item)
              @php
                $url = $item['image'] ?? '';
                $catId = $item['category'] ?? null;
                $href  = $catId ? route('shop', ['category' => $catId]) : '#';
              @endphp
              <div class="tl-slide">
                <a href="{{ $href }}" class="tl-slide-link">
                  <img src="{{ $url }}" alt="Banner {{ $bi+1 }}" loading="{{ $bi===0?'eager':'lazy' }}" style="height:{{ $bannerHeight }}px;max-height:{{ $bannerHeight }}px">
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
              <img src="{{ $url }}" alt="Banner" style="width:100%;object-fit:cover;height:{{ $bannerHeight }}px;max-height:{{ $bannerHeight }}px;display:block">
            </a>
          @endforeach
        @endif
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
            $img    = $ci['image'] ?? '';
            $color  = ($ci['colors'][0] ?? '#e85d26');
            $href   = $cid ? route('shop', ['category' => $cid]) : route('shop');
          @endphp
          <a href="{{ $href }}" class="tl-cat-item">
            <div class="tl-cat-img-wrap" style="border-color:{{ $color }}22">
              @if($img)
                <img src="{{ $img }}" alt="{{ $label }}" class="tl-cat-img" loading="lazy">
              @else
                <div class="tl-cat-chip" style="background:{{ $color }}22">🛍️</div>
              @endif
            </div>
            <span class="tl-cat-label">{{ $label }}</span>
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
        <a href="{{ route('shop') }}" class="sec-link">See all →</a>
      </div>
      <div style="display:grid;grid-template-columns:repeat({{ $columns }},1fr);gap:16px;margin-bottom:44px">
        @foreach($cats as $ci => $cat)
          @php
            $href      = route('shop', ['category' => $cat->id]);
            $bg        = $cat->thumbnail_url ?? null;
            $fallColor = $bgPalette[$ci % count($bgPalette)];
          @endphp
          <a href="{{ $href }}"
             class="cc-card"
             style="border-radius:{{ $radius }}px;height:{{ $cardHeight }}px;background:{{ $bg ? '#111' : $fallColor }}">
            @if($bg)
              <img src="{{ $bg }}" alt="{{ $cat->name }}" loading="lazy" class="cc-img">
            @else
              <div class="cc-placeholder" style="background:linear-gradient(135deg,{{ $fallColor }},{{ $fallColor }}99)">🛍️</div>
            @endif
            <div class="cc-overlay"></div>
            <div class="cc-label">
              <div class="cc-name">{{ $cat->name }}</div>
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
        $secId       = 'sg-'.$si;
        $cardOptions = [
          'showBadge'     => $sec['showBadge']     ?? true,
          'showWishlist'  => $sec['showWishlist']  ?? true,
          'showSwatches'  => $sec['showSwatches']  ?? true,
          'showSizes'     => $sec['showSizes']     ?? true,
          'showOldPrice'  => $sec['showOldPrice']  ?? true,
          'showAddToCart' => $sec['showAddToCart'] ?? true,
          'showCoupon'    => $sec['showCoupon']    ?? true,
          'showRating'    => $sec['showRating']    ?? false,
        ];
      @endphp
      @if($products->count())
      <style>
        #{{ $secId }} .product-card { border-radius: {{ $cardRadius }}px }
        @if($imgHeight)
        #{{ $secId }} .product-card-img { aspect-ratio: unset; height: {{ $imgHeight }}px }
        @endif
      </style>
      <div class="sec-head">
        <h2 class="sec-title">{{ $title }}</h2>
        <a href="{{ route('shop', array_filter(['category' => $catId])) }}" class="sec-link">View all →</a>
      </div>
      <div class="product-grid" id="{{ $secId }}" style="grid-template-columns:repeat(auto-fill,minmax({{ $prodWidth }}px,1fr));margin-bottom:40px">
        @foreach($products as $p)
        @include('web.partials.product-card', ['p' => $p, 'cardVariations' => $sectionVariations[$p->id] ?? [], 'cardOptions' => $cardOptions])
        @endforeach
      </div>
      @endif

    {{-- SALE IMAGES — Horizontal scroll of products --}}
    @elseif($layout === 'saleImages')
      @php
        $products   = $sectionProducts[$si] ?? collect();
        $title      = $sec['headerText'] ?? 'Products';
        $catId      = $sec['category'] ?? null;
        $prodWidth  = (int)($sec['productWidth'] ?? 140);
        $imgHeight  = isset($sec['imageHeight']) ? max(60, (int)$sec['imageHeight']) : (isset($sec['imageRatio']) ? max(60, round($prodWidth * (float)$sec['imageRatio'])) : 196);
        $cardRadius = isset($sec['cardBorderRadius']) ? (int)$sec['cardBorderRadius'] : 10;
        $cardOptions = [
          'showBadge'     => $sec['showBadge']     ?? true,
          'showWishlist'  => $sec['showWishlist']  ?? true,
          'showSwatches'  => $sec['showSwatches']  ?? true,
          'showSizes'     => $sec['showSizes']     ?? true,
          'showOldPrice'  => $sec['showOldPrice']  ?? true,
          'showAddToCart' => $sec['showAddToCart'] ?? true,
          'showCoupon'    => $sec['showCoupon']    ?? true,
          'showRating'    => $sec['showRating']    ?? false,
        ];
      @endphp
      @if($products->count())
      <div class="sec-head">
        <h2 class="sec-title">{{ $title }}</h2>
        <a href="{{ route('shop', array_filter(['category' => $catId])) }}" class="sec-link">See all →</a>
      </div>
      <div class="tl-scroll-section" style="margin-bottom:36px">
        <div class="tl-scroll-track">
          @foreach($products as $p)
          <div class="tl-scroll-card" style="width:{{ $prodWidth }}px">
            @include('web.partials.product-card', [
              'p'            => $p,
              'cardVariations' => $sectionVariations[$p->id] ?? [],
              'cardOptions'  => $cardOptions,
            ])
          </div>
          @endforeach
        </div>
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
        $secId      = 'sg-'.$si;
        $cardOptions = [
          'showBadge'     => $sec['showBadge']     ?? true,
          'showWishlist'  => $sec['showWishlist']  ?? true,
          'showSwatches'  => $sec['showSwatches']  ?? true,
          'showSizes'     => $sec['showSizes']     ?? true,
          'showOldPrice'  => $sec['showOldPrice']  ?? true,
          'showAddToCart' => $sec['showAddToCart'] ?? true,
          'showCoupon'    => $sec['showCoupon']    ?? true,
          'showRating'    => $sec['showRating']    ?? false,
        ];
      @endphp
      @if($products->count())
      <style>
        #{{ $secId }} .product-card { border-radius: {{ $cardRadius }}px }
        @if($imgHeight)
        #{{ $secId }} .product-card-img { aspect-ratio: unset; height: {{ $imgHeight }}px }
        @endif
      </style>
      <div class="sec-head">
        <h2 class="sec-title">{{ $title }}</h2>
        <a href="{{ route('shop', array_filter(['category' => $catId])) }}" class="sec-link">View all →</a>
      </div>
      <div class="product-grid" id="{{ $secId }}" style="grid-template-columns:repeat(auto-fill,minmax({{ $prodWidth }}px,1fr));margin-bottom:40px">
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
        <a href="{{ route('shop') }}" class="sec-link">Browse all →</a>
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
            <div class="vendor-card-count">{{ $v->product_count }} items</div>
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
        $headerText  = $sec['headerText'] ?? "This Week's Deals";
        $subLabel    = $sec['subLabel']   ?? 'Use code at checkout';
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
                {{ (int)$coupon->amount }}<sup>%</sup><div class="coupon-desc">Off your order</div>
              @else
                {{ number_format($coupon->amount, 0) }}<sup> EGP</sup><div class="coupon-desc">Off your order</div>
              @endif
            </div>
            <div class="coupon-code-row">
              <span class="coupon-code">{{ strtoupper($coupon->code) }}</span>
              <button class="coupon-copy-btn" onclick="copyCoupon(this,'{{ strtoupper($coupon->code) }}')">Copy</button>
            </div>
            @if($coupon->minimum_amount > 0)
            <div class="coupon-min">Min. order {{ number_format($coupon->minimum_amount, 0) }} EGP</div>
            @endif
          </div>
          @endforeach
        </div>
        @else
        <p style="color:var(--c-mid);font-size:14px;text-align:center;padding:20px 0">No active coupons at the moment.</p>
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
        $headline = $sec['headline'] ?? 'Special Offer';
        $subtext  = $sec['subtext'] ?? '';
        $btnText  = $sec['btnText'] ?? 'Shop Now';
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
          <p class="tl-test-comment">@if($rev->comment)"{{ Str::limit($rev->comment, 160) }}"@else<em style="opacity:.5">No comment</em>@endif</p>
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
        <span class="brand-chip">{{ $brand->name }}</span>
        @endforeach
      </div>
      @endif

    {{-- TRENDING NOW --}}
    @elseif($layout === 'trending')
      @php $products = $sectionTrending[$si] ?? collect(); $title = $sec['headerText'] ?? 'Trending Now'; @endphp
      @if($products->count())
      <div class="sec-head"><h2 class="sec-title">🔥 {{ $title }}</h2><a href="{{ route('shop') }}" class="sec-link">View all →</a></div>
      <div class="tl-scroll-section" style="margin-bottom:36px"><div class="tl-scroll-track">
        @foreach($products as $idx => $p)
        <div class="tl-scroll-card" style="position:relative">
          @if($sec['showRankBadge'] ?? true)<span class="tl-rank-badge">#{{ $loop->iteration }}</span>@endif
          <div class="product-card">
            <a href="{{ route('product', $p->id) }}" class="product-card-img" style="height:180px">
              @if($p->thumbnail_url)<img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" loading="lazy">@else<div class="placeholder">🛍️</div>@endif
              @if($p->on_sale)<span class="badge-sale">SALE</span>@endif
            </a>
            <div class="product-card-body" style="padding:8px">
              <a href="{{ route('product', $p->id) }}" class="product-card-name" style="font-size:12px">{{ Str::limit($p->name, 28) }}</a>
              @if(($sec['showSoldToday'] ?? false) && $p->total_sales > 0)<div style="font-size:11px;color:#e85d26;font-weight:600">{{ $p->total_sales }}+ sold</div>@endif
              <div class="product-card-price"><span class="price-main {{ $p->on_sale ? 'sale' : '' }}" style="font-size:12px">{{ number_format($p->on_sale ? $p->sale_price : $p->price, 0) }} EGP</span></div>
            </div>
          </div>
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
      <div class="sec-head"><h2 class="sec-title">✨ {{ $sec['headerText'] ?? 'New Arrivals' }}</h2><a href="{{ route('shop') }}" class="sec-link">See all →</a></div>
      <div class="tl-arrivals-wrap" id="{{ $tickerId }}" style="margin-bottom:36px" {{ $pause ? 'onmouseenter="this.style.animationPlayState=\'paused\'" onmouseleave="this.style.animationPlayState=\'running\'"' : '' }}>
        <div class="tl-arrivals-track" style="{{ $loop2 ? '' : 'animation:none' }}">
          @foreach(array_merge($products->all(), $products->all()) as $p)
          <a href="{{ route('product', $p->id) }}" class="tl-arrival-card">
            @if($p->thumbnail_url)<img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" class="tl-arrival-img">@else<div class="tl-arrival-placeholder">🛍️</div>@endif
            <div class="tl-arrival-body">
              @if($sec['showCategoryChip'] ?? false)<span class="tl-arrival-tag">{{ $tag }}</span>@endif
              <div class="tl-arrival-name">{{ Str::limit($p->name, 22) }}</div>
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
        <div class="sec-head"><h2 class="sec-title">Recently Viewed</h2></div>
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
            track.innerHTML += '<div class="tl-scroll-card"><a href="/product/'+p.id+'" style="text-decoration:none"><div style="width:120px;text-align:center"><img src="'+p.img+'" style="width:110px;height:110px;object-fit:cover;border-radius:8px" onerror="this.style.display=\'none\'">'+'<div style="font-size:11px;color:#333;margin-top:5px;line-height:1.3">'+p.name+'</div><div style="font-size:12px;font-weight:700;color:#e85d26;margin-top:2px">'+p.price+' EGP</div></div></a></div>';
          });
          if (track.children.length) section.style.display = 'block';
        } catch(e) {}
      })();
      </script>
      @endif

    {{-- BUNDLE DEAL --}}
    @elseif($layout === 'bundle')
      @php
        $bTitle    = $sec['title']         ?? 'Bundle Deal';
        $bMinQty   = $sec['minQty']        ?? 2;
        $bFreeItems = $sec['freeItems']    ?? 1;
        $bCat      = $sec['category']      ?? '';
        $bSavings  = $sec['showSavingsBadge'] ?? true;
      @endphp
      <div class="tl-bundle-card" style="margin-bottom:36px">
        @if($bSavings)<div class="tl-bundle-badge">Special Deal</div>@endif
        <div class="tl-bundle-body">
          <div class="tl-bundle-icon">🎁</div>
          <div class="tl-bundle-info">
            <div class="tl-bundle-title">{{ $bTitle }}</div>
            <div class="tl-bundle-desc">Buy <strong>{{ $bMinQty }}</strong> items, get <strong>{{ $bFreeItems }}</strong> FREE{{ $bCat ? ' from '.$bCat : '' }}!</div>
          </div>
          <a href="/shop" class="tl-bundle-btn">Shop Now</a>
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
            <strong>Earn {{ $lRate }} points per EGP spent!</strong>
            <span>Redeem from {{ $lMin }} pts · {{ $lConv }}
            @if($lDouble && $isWeekend) <span class="tl-loyalty-double">2× Points Weekend!</span>@endif
            </span>
          </div>
          <a href="/shop" class="tl-loyalty-btn">Start Earning</a>
        </div>
      </div>

    {{-- SEASONAL BANNER --}}
    @elseif($layout === 'seasonal')
      @php
        $sTitle    = $sec['title']     ?? 'Special Season';
        $sSub      = $sec['subtitle']  ?? 'Limited-time offers for this season';
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
            <span id="sd-d-{{ $si }}">00</span>d <span id="sd-h-{{ $si }}">00</span>h <span id="sd-m-{{ $si }}">00</span>m
          </div>
          <script>
          (function(){
            var end=new Date('{{ $sEnd }}T23:59:59').getTime();
            function tick2(){var r=Math.max(0,Math.floor((end-Date.now())/1000));document.getElementById('sd-d-{{ $si }}').textContent=String(Math.floor(r/86400)).padStart(2,'0');document.getElementById('sd-h-{{ $si }}').textContent=String(Math.floor((r%86400)/3600)).padStart(2,'0');document.getElementById('sd-m-{{ $si }}').textContent=String(Math.floor((r%3600)/60)).padStart(2,'0');if(r>0)setTimeout(tick2,30000);}tick2();
          })();
          </script>
          @endif
          <a href="/shop" class="tl-seasonal-btn">Shop Now →</a>
        </div>
      </div>
      @endif

    {{-- REFERRAL WIDGET --}}
    @elseif($layout === 'referral')
      @php
        $rRef   = $sec['rewardReferrer'] ?? 50;
        $rNew   = $sec['rewardNewUser']  ?? 30;
        $rMin   = $sec['minOrder']       ?? 200;
        $rCta   = $sec['ctaText']        ?? 'Invite Friends & Earn!';
        $rWa    = $sec['shareViaWhatsApp'] ?? true;
      @endphp
      <div class="tl-referral-card" style="margin-bottom:36px">
        <div class="tl-referral-inner">
          <div class="tl-referral-icon">🎁</div>
          <div class="tl-referral-body">
            <div class="tl-referral-title">{{ $rCta }}</div>
            <div class="tl-referral-desc">You earn <strong>{{ $rRef }} EGP</strong> and your friend gets <strong>{{ $rNew }} EGP</strong> off their first order over {{ $rMin }} EGP!</div>
            <div class="tl-referral-actions">
              <input type="text" class="tl-referral-link" value="{{ url('/') }}/ref/{{ auth()->id() ?? 'YOURCODE' }}" readonly onclick="this.select()">
              @if($rWa)
              <a href="https://wa.me/?text={{ urlencode('Join Ramo Store and get '.($rNew).' EGP off your first order! '.url('/').'/ref/'.(auth()->id() ?? 'YOURCODE')) }}" target="_blank" class="tl-referral-wa-btn">Share via WhatsApp</a>
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
            <div style="font-weight:700;font-size:16px;margin-bottom:4px">Complete the Look</div>
            <div style="font-size:13px;color:var(--c-mid)">Find {{ strtolower($cStrategy) }} items that go perfectly together.@if($cDisc) Bundle discount applied at checkout!@endif</div>
          </div>
          <a href="/shop" class="btn btn-primary" style="font-size:13px;padding:10px 20px">Browse Collections →</a>
        </div>
      </div>

    {{-- RECOMMENDED FOR YOU --}}
    @elseif($layout === 'recommended')
      @php
        $products = $sectionTrending[$si] ?? collect();
        $recLabel = $sec['personalizedLabel'] ?? false;
        $title    = $recLabel ? 'Picked For You' : ($sec['headerText'] ?? 'Recommended For You');
      @endphp
      @if($products->count())
      <div class="sec-head"><h2 class="sec-title">{{ $title }}</h2><a href="{{ route('shop') }}" class="sec-link">See all →</a></div>
      <div class="tl-scroll-section" style="margin-bottom:36px"><div class="tl-scroll-track">
        @foreach($products as $p)
        <div class="tl-scroll-card"><div class="product-card">
          <a href="{{ route('product', $p->id) }}" class="product-card-img" style="height:180px">
            @if($p->thumbnail_url)<img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" loading="lazy">@else<div class="placeholder">🛍️</div>@endif
            @if($p->on_sale)<span class="badge-sale">SALE</span>@endif
          </a>
          <div class="product-card-body" style="padding:8px">
            <a href="{{ route('product', $p->id) }}" class="product-card-name" style="font-size:12px">{{ Str::limit($p->name, 28) }}</a>
            <div class="product-card-price"><span class="price-main {{ $p->on_sale ? 'sale' : '' }}" style="font-size:12px">{{ number_format($p->on_sale ? $p->sale_price : $p->price, 0) }} EGP</span></div>
          </div>
        </div></div>
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
          $pcwDiscPct  = (float)($fp->discount_percentage ?? 0);
          $pcwHasDisc  = $pcwDiscPct > 0;
          $pcwMinEff   = $fv->min('price') ?? $fp->display_price;
          $pcwMaxEff   = $fv->max('price') ?? $fp->display_price;
          $pcwMinReg   = $fv->min('regular_price') ?? $pcwMinEff;
          $pcwIsRange  = $fv->count() > 0 && round((float)$pcwMinEff,2) !== round((float)$pcwMaxEff,2);
          $pcwAttrMap  = [];
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
              <img src="{{ $fp->thumbnail_url }}" alt="{{ $fp->name }}" class="pcw-img" loading="lazy">
            @else
              <div class="pcw-img-placeholder">🛍️</div>
            @endif
            @if($pcwHasDisc)<span class="badge-sale">{{ round($pcwDiscPct) }}% OFF</span>@endif
          </a>
          <div class="pcw-info">
            <div class="pcw-title-row">
              <h2 class="pcw-title"><a href="{{ route('product', $fp->id) }}" style="color:inherit;text-decoration:none">{{ $fp->name }}</a></h2>
              @if($showWishlist)
              <button class="pcw-wish-btn {{ $pcwInWishlist ? 'wished' : '' }}"
                      onclick="toggleWishlist(this, {{ $fp->id }})"
                      title="{{ $pcwInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">{{ $pcwInWishlist ? '♥' : '♡' }}</button>
              @endif
            </div>
            @if($showRating)
            <div class="pcw-rating-row">
              <div class="pcw-stars">
                @for($s=1;$s<=5;$s++)<span style="color:{{ $s<=round($pcwAvg)?'#f5a623':'#ddd' }}">★</span>@endfor
              </div>
              @if($pcwTotal)
                <span class="pcw-rating-val">{{ $pcwAvg }}</span>
                <a href="{{ route('product', $fp->id) }}#reviews" class="pcw-rating-cnt">({{ $pcwTotal }} review{{ $pcwTotal!=1?'s':'' }})</a>
              @else
                <span class="pcw-rating-none">No reviews yet</span>
              @endif
            </div>
            @endif
            <div class="pcw-stock">
              @if($fp->stock_quantity > 0)
                <span class="pcw-stock-ok">✓ In Stock ({{ number_format($fp->stock_quantity) }} available)</span>
              @else
                <span class="pcw-stock-no">Out of Stock</span>
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
                @if($pcwHasDisc)<span class="pcw-disc-badge">{{ round($pcwDiscPct) }}% OFF</span>@endif
              </div>
              @if($pcwHasDisc)
              <div class="pcw-sale-note">🏷️ Sale price — you save {{ round($pcwDiscPct) }}% off the original price</div>
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
              <div class="qty-input">
                <button type="button" onclick="this.nextElementSibling.value=Math.max(1,+this.nextElementSibling.value-1)">−</button>
                <input type="number" value="1" min="1" max="{{ $fp->stock_quantity ?: 99 }}" id="pcw-qty-{{ $si }}">
                <button type="button" onclick="this.previousElementSibling.value=Math.min({{ $fp->stock_quantity ?: 99 }},+this.previousElementSibling.value+1)">+</button>
              </div>
              <button class="pcw-atc-btn"
                      onclick="addToCart({{ $fp->id }}, '{{ addslashes($fp->name) }}', {{ (float)$pcwMinEff }}, '{{ $fp->thumbnail_url }}', parseInt(document.getElementById('pcw-qty-{{ $si }}').value)||1)">
                🛒 Add to Cart
              </button>
            </div>
            @if($showCoupon)
            <div class="pcw-coupon-wrap">
              <div class="pcw-coupon-label">🏷️ Have a coupon?</div>
              <div class="pcw-coupon-row">
                <input type="text" id="pcw-coupon-{{ $si }}" class="pcw-coupon-input" placeholder="Enter promo code" maxlength="50">
                <button class="pcw-coupon-btn" onclick="applyPcwCoupon('pcw-coupon-{{ $si }}','pcw-msg-{{ $si }}')">Apply</button>
              </div>
              <div id="pcw-msg-{{ $si }}" class="pcw-coupon-msg"></div>
            </div>
            @endif
            <a href="{{ route('product', $fp->id) }}" class="pcw-view-link">View full details →</a>
          </div>
        </div>
      @endif

    {{-- FLASH / ANNOUNCEMENT: already rendered above, skip here --}}
    @elseif($layout === 'flash' || $layout === 'announcement')
      {{-- intentionally skipped — rendered as full-width elements above --}}

    @endif
    @if($inPreview && !$tlNoWrap)</div>@endif

  @empty
    {{-- Fallback when no timeline config --}}
    <div class="hero">
      <div class="hero-text">
        <div class="hero-eyebrow">New Season</div>
        <h1 class="hero-title">Style that speaks<br>for itself.</h1>
        <p class="hero-sub">Discover the latest collections — premium quality, delivered to your door.</p>
        <a href="{{ route('shop') }}" class="btn btn-white">Shop Now →</a>
      </div>
    </div>
  @endforelse

</div>
@endsection

@push('styles')
<style>
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
    bannerImage:'#3b82f6', category:'#8b5cf6', twoColumn:'#22c55e',
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
    bannerImage:'Banner / Slider', category:'Categories Strip', twoColumn:'Products Grid',
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
    label.innerHTML =
      '<span class="tl-pw-idx">' + (parseInt(si) + 1) + '</span>' +
      '<span>' + name + '</span>' +
      '<span style="opacity:.65;font-size:10px;font-weight:500;margin-left:2px">· ' + typeLabel + '</span>' +
      '<span style="opacity:.8;font-size:10px;margin-left:8px;background:rgba(232,93,38,.25);border:1px solid rgba(232,93,38,.5);padding:1px 7px;border-radius:8px;color:#e85d26;font-weight:700">✏ Edit</span>';
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

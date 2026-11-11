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
          --tl-product-card-max-h:{{ (($tlDesk['cardHeight'] ?? ($sec['cardHeight'] ?? 0)) > 0) ? (($tlDesk['cardHeight'] ?? ($sec['cardHeight'] ?? 0)).'px') : '34rem' }};
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
            --tl-product-card-max-h:{{ (($tlMob['cardHeight'] ?? ($sec['cardHeight'] ?? 0)) > 0) ? (($tlMob['cardHeight'] ?? ($sec['cardHeight'] ?? 0)).'px') : '34rem' }};
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
      @if(!empty($sec['testDemo']))
        <div style="margin:0 0 8px;color:#805500;font-size:12px;font-weight:700">{{ $timelineRtl ? 'أداة تجريبية — للاختبار فقط' : 'TEST WIDGET — FOR DEMONSTRATION ONLY' }}</div>
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
        <div class="tl-banner-slider" id="{{ $sliderId }}" data-slide-count="{{ count($items) }}" style="border-radius:var(--tl-radius,{{ $radius }}px);margin-bottom:28px;max-height:var(--tl-banner-h,{{ $bannerHeight }}px)">
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
          $cardStyle   = '--pc-card-max-height:var(--tl-product-card-max-h,'.($cardHeight > 0 ? $cardHeight.'px' : '34rem').');--pc-image-width:var(--tl-img-w,'.($imageWidth > 0 ? $imageWidth.'px' : '100%').');--pc-image-height:var(--tl-img-h,'.$imgHeight.'px);--pc-element-spacing:var(--tl-element-spacing,'.$elementSpacing.'px)';
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
      @if($products->count())
      <style>
        #{{ $secId }} .product-card { border-radius: var(--tl-card-r,{{ $cardRadius }}px) }
        @media(max-width:600px){
          #{{ $secId }} { grid-template-columns:repeat(2,minmax(0,1fr)) !important; gap:10px !important; }
        }
        @if($imgHeight)
        #{{ $secId }} .pc-media { block-size:var(--tl-img-h,{{ $imgHeight }}px) }
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

    {{-- ALL PRODUCTS — required final, paginated timeline section --}}
    @elseif($layout === 'allProducts')
      @php
        $products = $sectionProducts[$si] ?? collect();
        $paginator = $sectionProductPaginators[$si] ?? null;
        $title = $sec['headerText'] ?? ($timelineRtl ? 'منتجات قد تعجبك' : 'Products you may like');
        $cardOptions = ['idPrefix' => 'tl-all-products-'.$si];
      @endphp
      <style>
        #tl-sec-{{ $si }} .all-products-grid {
          display:grid;
          width:100%;
          min-width:0;
          grid-template-columns:repeat(5,minmax(0,1fr));
          gap:24px 20px;
          align-items:start;
          margin-bottom:32px;
        }
        #tl-sec-{{ $si }} .all-products-infinite { display:flex;flex-direction:column;align-items:center;gap:10px;margin:8px 0 44px;min-height:24px }
        #tl-sec-{{ $si }} .all-products-infinite-status { margin:0;color:#777;font-size:13px;text-align:center }
        #tl-sec-{{ $si }} .all-products-load-more { padding:10px 20px;border:1px solid #e8e5df;border-radius:999px;background:#fff;color:#242424;font:inherit;font-size:13px;font-weight:600;cursor:pointer }
        #tl-sec-{{ $si }} .all-products-load-more:hover:not(:disabled) { border-color:#e85d26;color:#e85d26 }
        #tl-sec-{{ $si }} .all-products-load-more:disabled { opacity:.6;cursor:wait }
        #tl-sec-{{ $si }} .all-products-infinite [hidden] { display:none !important }
        #tl-sec-{{ $si }} .all-products-sentinel { width:100%;height:1px }
        @media(min-width:1440px) {
          #tl-sec-{{ $si }} .all-products-grid { grid-template-columns:repeat(6,minmax(0,1fr));gap:28px 22px }
        }
        @media(max-width:1199px) {
          #tl-sec-{{ $si }} .all-products-grid { grid-template-columns:repeat(4,minmax(0,1fr));gap:22px 16px }
        }
        @media(max-width:899px) {
          #tl-sec-{{ $si }} .all-products-grid { grid-template-columns:repeat(3,minmax(0,1fr));gap:20px 14px }
        }
        @media(max-width:600px) {
          #tl-sec-{{ $si }} .all-products-grid { grid-template-columns:repeat(2,minmax(0,1fr));gap:18px 12px;margin-bottom:24px }
        }
      </style>
      <div class="sec-head" style="align-items:center;margin-bottom:18px">
        <h2 class="sec-title" style="margin:0">{{ $title }}</h2>
      </div>
      @if($products->isNotEmpty())
        <div class="product-grid product-grid--showcase all-products-grid" id="all-products-grid-{{ $si }}">
          @foreach($products as $p)
            @include('web.partials.product-card', [
              'p' => $p,
              'cardVariations' => $sectionVariations[$p->id] ?? [],
              'cardOptions' => $cardOptions,
            ])
          @endforeach
        </div>
      @else
        <div style="padding:28px 16px;margin-bottom:28px;text-align:center;color:#777;background:#faf9f7;border:1px solid #eee;border-radius:12px">
          {{ $timelineRtl ? 'لا توجد منتجات حالياً' : 'No products are available right now.' }}
        </div>
      @endif
      @if($paginator && $paginator->hasMorePages())
        <div
          class="all-products-infinite"
          id="all-products-infinite-{{ $si }}"
          data-all-products-loader
          data-url="{{ route('home.products', [], false) }}"
          data-next-page="{{ $paginator->currentPage() + 1 }}"
          data-loading-label="{{ $timelineRtl ? 'جارٍ تحميل المزيد من المنتجات...' : 'Loading more products…' }}"
          data-scroll-label="{{ $timelineRtl ? 'تابع التمرير لاكتشاف المزيد' : 'Keep scrolling to discover more' }}"
          data-end-label="{{ $timelineRtl ? 'لقد شاهدت كل المنتجات.' : 'You’ve seen them all.' }}"
          data-error-label="{{ $timelineRtl ? 'تعذر تحميل المزيد الآن.' : 'Couldn’t load more right now.' }}"
          data-retry-label="{{ $timelineRtl ? 'إعادة المحاولة' : 'Try again' }}"
          data-load-label="{{ $timelineRtl ? 'تحميل المزيد من المنتجات' : 'Load more products' }}"
          aria-busy="false"
        >
          <p class="all-products-infinite-status" data-infinite-status role="status" aria-live="polite">{{ $timelineRtl ? 'تابع التمرير لاكتشاف المزيد' : 'Keep scrolling to discover more' }}</p>
          <button class="all-products-load-more" type="button" data-infinite-button aria-controls="all-products-grid-{{ $si }}" hidden>{{ $timelineRtl ? 'تحميل المزيد من المنتجات' : 'Load more products' }}</button>
          <div class="all-products-sentinel" data-infinite-sentinel aria-hidden="true"></div>
        </div>
        <noscript>
          <nav class="all-products-pagination" aria-label="{{ $timelineRtl ? 'المزيد من المنتجات' : 'More products' }}">
            @if(!$paginator->onFirstPage())
              <a href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ $timelineRtl ? 'السابق' : 'Previous' }}</a>
            @endif
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $timelineRtl ? 'عرض المزيد من المنتجات' : 'View more products' }}</a>
          </nav>
        </noscript>
        <script>
        (function () {
          const loader = document.getElementById('all-products-infinite-{{ $si }}');
          if (!loader) return;

          const grid = document.getElementById('all-products-grid-{{ $si }}');
          const sentinel = loader.querySelector('[data-infinite-sentinel]');
          const status = loader.querySelector('[data-infinite-status]');
          const button = loader.querySelector('[data-infinite-button]');
          if (!grid || !sentinel || !status || !button) return;
          const catalogView = loader.closest('[data-catalog-view]');
          const isActive = () => !catalogView || catalogView.dataset.catalogActive === '1';

          let nextPage = Number(loader.dataset.nextPage) || 2;
          let loading = false;
          let hasMore = true;
          let observer = null;
          const supportsObserver = 'IntersectionObserver' in window;

          async function loadNextPage() {
            if (loading || !hasMore || !isActive()) return;
            loading = true;
            loader.setAttribute('aria-busy', 'true');
            status.textContent = loader.dataset.loadingLabel;
            button.disabled = true;

            const url = new URL(loader.dataset.url, window.location.href);
            url.searchParams.set('all_products_page', String(nextPage));

            try {
              const response = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
              });
              if (!response.ok) throw new Error('Product page request failed');

              const data = await response.json();
              if (typeof data.html !== 'string') throw new Error('Invalid product page response');

              const template = document.createElement('template');
              template.innerHTML = data.html.trim();
              template.content.querySelectorAll('style').forEach(style => style.remove());
              const appendedCards = template.content.querySelectorAll('.product-card').length;
              if (data.hasMore && appendedCards === 0) throw new Error('The next page contained no products');

              grid.append(template.content);
              if (typeof window._pcInitializeVariations === 'function') {
                window._pcInitializeVariations(grid);
              }
              if (typeof window.refreshWishlistState === 'function') {
                window.refreshWishlistState();
              }

              hasMore = Boolean(data.hasMore);
              nextPage = Number(data.nextPage) || nextPage + 1;
              status.textContent = hasMore ? loader.dataset.scrollLabel : loader.dataset.endLabel;
              button.textContent = loader.dataset.loadLabel;
              button.hidden = supportsObserver || !hasMore;

              if (hasMore) {
                observer?.observe(sentinel);
              } else {
                observer?.disconnect();
                sentinel.hidden = true;
                button.hidden = true;
              }
            } catch (error) {
              status.textContent = loader.dataset.errorLabel;
              button.textContent = loader.dataset.retryLabel;
              button.hidden = false;
              observer?.unobserve(sentinel);
            } finally {
              loading = false;
              loader.setAttribute('aria-busy', 'false');
              button.disabled = false;
            }
          }

          button.addEventListener('click', loadNextPage);
          if (supportsObserver) {
            observer = new IntersectionObserver(entries => {
              if (entries.some(entry => entry.isIntersecting)) loadNextPage();
            }, { rootMargin: `0px 0px ${Math.min(2400, Math.max(1200, window.innerHeight * 2))}px 0px` });
            observer.observe(sentinel);
            document.addEventListener('catalog:shown', () => {
              if (isActive() && hasMore) { observer.unobserve(sentinel); observer.observe(sentinel); }
            });
          } else {
            button.hidden = false;
          }
        })();
        </script>
      @endif

    {{-- SALE IMAGES — Horizontal scroll of products --}}
    @elseif($layout === 'saleImages')
      @php
        $products       = $sectionProducts[$si] ?? collect();
        $title          = $sec['headerText'] ?? 'Products';
        $catId          = $sec['category'] ?? null;
        $prodWidth      = max(210, (int)($sec['productWidth'] ?? 224));
        $secId          = 'sg-'.$si;
        $cardHeight     = max(0, (int)($sec['cardHeight'] ?? 0));
        $cardStyle      = '--pc-card-max-height:var(--tl-product-card-max-h,'.($cardHeight > 0 ? $cardHeight.'px' : '34rem').')';
        $cardOptions = [
          'cardStyle'      => $cardStyle,
          'variant'        => 'vertical',
          'idPrefix'       => 'tl-'.$si,
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
        #{{ $secId }} {
          align-items:flex-start;
          gap:16px;
          padding:4px 14px 12px;
          scroll-snap-type:inline proximity;
          scroll-padding-inline:14px;
        }
        #{{ $secId }} .tl-scroll-card {
          display:grid;
          flex:0 0 var(--tl-prod-w,{{ $prodWidth }}px);
          min-inline-size:0;
          scroll-snap-align:start;
        }
        @media(max-width:720px){
          #{{ $secId }} { gap:14px; }
          #{{ $secId }} .tl-scroll-card { flex-basis:clamp(180px, 67%, 256px); }
        }
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
            <div class="tl-scroll-card">
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
        $cardStyle  = '--pc-card-max-height:var(--tl-product-card-max-h,'.($cardHeight > 0 ? $cardHeight.'px' : '34rem').');--pc-image-width:var(--tl-img-w,'.($imageWidth > 0 ? $imageWidth.'px' : '100%').');--pc-image-height:var(--tl-img-h,'.$imgHeight.'px);--pc-element-spacing:var(--tl-element-spacing,'.$elementSpacing.'px)';
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
      @if($products->count())
      <style>
        #{{ $secId }} .product-card { border-radius: var(--tl-card-r,{{ $cardRadius }}px) }
        @media(max-width:600px){
          #{{ $secId }} { grid-template-columns:repeat(2,minmax(0,1fr)) !important; gap:10px !important; }
        }
        @if($imgHeight)
        #{{ $secId }} .pc-media { block-size:var(--tl-img-h,{{ $imgHeight }}px) }
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

    {{-- NEW ARRIVALS ROW --}}
    @elseif($layout === 'arrivals')
      @php
        $products = $sectionArrivals[$si] ?? collect();
        $tag      = $sec['tag']   ?? 'Just Arrived';
      @endphp
      @if($products->count())
      <div class="sec-head"><h2 class="sec-title">✨ {{ $sec['headerText'] ?? 'New Arrivals' }}</h2><a href="{{ route('shop') }}" class="sec-link">{{ $timelineSeeAllLabel }}</a></div>
      <div class="tl-arrivals-wrap" style="margin-bottom:36px">
        <div class="tl-arrivals-track">
            @foreach($products as $p)
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
        @if($revs->count() > 1)
        <div class="tl-revcar-nav">
          <button type="button" data-review-step="-1">‹</button>
          <div class="tl-revcar-dots">
            @foreach($revs as $ri => $rev)<span class="tl-revcar-dot {{ $ri===0 ? 'active' : '' }}" data-review-index="{{ $ri }}"></span>@endforeach
          </div>
          <button type="button" data-review-step="1">›</button>
        </div>
        @endif
      </div>
      <script>
      (function(){
        const carousel = document.getElementById('{{ $carId }}');
        const slides = carousel.querySelectorAll('.tl-revcar-slide');
        const dots = carousel.querySelectorAll('.tl-revcar-dot');
        let current = 0;
        carousel.querySelector('.tl-revcar-nav')?.addEventListener('click', event => {
          const control = event.target.closest('[data-review-step], [data-review-index]');
          if (!control) return;
          const next = Number(control.dataset.reviewIndex ?? current + Number(control.dataset.reviewStep));
          slides[current].style.display = 'none';
          current = (next + slides.length) % slides.length;
          slides[current].style.display = 'flex';
          dots.forEach((dot, index) => dot.classList.toggle('active', index === current));
        });
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

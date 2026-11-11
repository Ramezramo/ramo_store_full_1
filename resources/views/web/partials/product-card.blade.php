@php
  $pid = $p->id;
  $vars = collect($cardVariations ?? []);
  $co = $cardOptions ?? [];

  /* ── Display options ───────────────────────────────────── */
  $coShowBadge      = $co['showBadge']      ?? true;
  $coShowWishlist   = $co['showWishlist']   ?? true;
  $coShowSwatches   = $co['showSwatches']   ?? true;
  $coShowSizes      = $co['showSizes']      ?? true;
  $coShowOldPrice   = $co['showOldPrice']   ?? true;
  $coShowAddToCart  = $co['showAddToCart']  ?? true;
  $coShowDetails    = $co['showDetails']    ?? true;
  $coShowCoupon     = $co['showCoupon']     ?? true;
  $coShowRating     = $co['showRating']     ?? false;

  /* ── Layout / style options ────────────────────────────── */
  $cardVariant      = $co['variant'] ?? (!empty($co['compact']) ? 'compact' : 'auto');
  $cardVariant      = in_array($cardVariant, ['auto', 'compact', 'horizontal', 'vertical', 'shop'], true) ? $cardVariant : 'auto';
  $cardPresentation = $cardVariant === 'shop' ? 'shop-product-card' : 'product-card--shared pc-'.$cardVariant;
  $coNameLimit      = (int)($co['nameLimit'] ?? 0);    // 0 = no limit
  $coImgHeight      = $co['imgHeight']      ?? null;   // e.g. '180px'
  $coIdPrefix       = $co['idPrefix']       ?? 'pc';   // id prefix on outer div
  $domPrefix        = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $coIdPrefix);
  $cardDomId        = $domPrefix.'-'.$pid;
  $coRemoveWishlist = $co['removeWishlist'] ?? false;  // show remove-from-wishlist form
  $cardRtl = session('locale', 'en') === 'ar';
  $cardCopy = $cardRtl ? [
    'wishlist' => 'ضيف للمفضلة', 'add' => 'ضيف للسلة', 'details' => 'شوف التفاصيل',
    'unavailable' => 'مش متاح', 'selectQty' => 'اختار كمية (الحد الأدنى ',
    'removeWishlist' => 'شيل من المفضلة', 'coupon' => 'بكود', 'sale' => 'خصم',
  ] : [];

  /* ── Per-product button_mode overrides section defaults ── */
  $buttonMode = $p->button_mode ?? 'both';
  if ($buttonMode === 'cart_only')    { $coShowDetails   = false; }
  if ($buttonMode === 'details_only') { $coShowAddToCart = false; }

  /* ── Build color & size maps ────────────────────────────── */
  $colorMap  = [];
  $sizeList  = [];
  $swatchHex = [
    'white'=>'#f5f5f5','black'=>'#222','green'=>'#22a35c','red'=>'#e53e3e',
    'blue'=>'#3182ce','yellow'=>'#f0c200','gray'=>'#a0aec0','grey'=>'#a0aec0',
    'navy'=>'#1a365d','pink'=>'#ed64a6','orange'=>'#ed8936','purple'=>'#805ad5',
    'brown'=>'#c05621','beige'=>'#f5e6cc','cream'=>'#fffdd0','maroon'=>'#800000',
    'gold'=>'#d4af37','silver'=>'#c0c0c0','teal'=>'#319795','indigo'=>'#5a67d8',
    'cyan'=>'#00b5d8','lime'=>'#68d391','rose'=>'#fc8181','mint'=>'#98d8c8',
    'khaki'=>'#c3b091','charcoal'=>'#36454f','coral'=>'#ff7f50','turquoise'=>'#40e0d0',
  ];
  foreach ($vars as $v) {
    $attrs = (array)($v->attributes ?? []);
    $imgs  = (array)($v->images ?? []);
    $imgUrl = $imgs ? \App\Constants\AppConstants::imageUrl($imgs[0]) : null;
    if (isset($attrs['Color'])) {
      $c = $attrs['Color'];
      if (!isset($colorMap[$c])) {
        $colorMap[$c] = [
          'img' => $imgUrl,
          'hex' => ($swatchHex[strtolower($c)] ?? '#ccc'),
          'display' => \App\Support\StorefrontLabels::color($c, $cardRtl),
        ];
      }
    }
    if (isset($attrs['Size']) && !in_array($attrs['Size'], $sizeList)) {
      $sizeList[] = $attrs['Size'];
    }
  }

  /* ── Resolve display image ──────────────────────────────── */
  $displayImg = $p->thumbnail_url ?? null;
  if (!$displayImg && count($colorMap)) {
    $first = array_values($colorMap)[0];
    $displayImg = $first['img'] ?? null;
  }

  /* ── Slim variation payload for JS ─────────────────────── */
  $jsVars = $vars->map(fn($v) => [
    'id'    => $v->id,
    'price' => (float)$v->price,
    'sale'  => (float)$v->sale_price,
    'stock' => (int)$v->stock_quantity,
    'stockStatus' => strtolower(trim((string) ($v->stock_status ?? 'instock'))),
    'status' => strtolower(trim((string) ($v->status ?? 'publish'))),
    'attrs' => (array)($v->attributes ?? []),
    'img'   => (!empty($v->images) && is_array($v->images)) ? \App\Constants\AppConstants::imageUrl($v->images[0]) : null,
  ])->values()->toArray();

  // Render the first sellable variation as the card's initial choice. Keeping
  // this in the HTML means the selected option is visible before the card JS
  // runs, and still works for cards inserted by lazy/AJAX sections.
  $initialVariation = collect($jsVars)->first(fn($v) =>
    ($v['status'] ?? '') === 'publish'
    && in_array(preg_replace('/[\s_-]+/', '', (string)($v['stockStatus'] ?? '')), ['instock', 'available'], true)
    && (int)($v['stock'] ?? 0) > 0
  );
  $initialAttrs = $initialVariation['attrs'] ?? [];
  $initialColor = $initialAttrs['Color'] ?? '';
  $initialSize = $initialAttrs['Size'] ?? '';

  $baseImg = $displayImg;
  $displayImg = $initialVariation['img'] ?? $baseImg;
  $fallbackImg = asset('images/product-placeholder.svg');
  $basePrice = $p->on_sale ? $p->sale_price : $p->price;
  $regularPrice = (float) ($initialVariation['price'] ?? $p->price);
  $salePrice = (float) ($initialVariation['sale'] ?? $p->sale_price ?? 0);
  $discounted = $salePrice > 0 && $salePrice < $regularPrice;
  $displayPrice = $discounted ? $salePrice : $regularPrice;
  $discount = $discounted ? (int) round(100 * ($regularPrice - $salePrice) / $regularPrice) : 0;
  $currency = $p->currency ?? 'EGP';
  $quickAddMinimum = max(1, (int) ($p->minimum_order_qty ?? 1));
  // Variation rows are the authoritative inventory source when they exist.
  // This prevents a stale product-level stock value from showing Add to Cart
  // after every sellable variation has reached zero stock.
  $variationStockTotal = null;
  if ($vars->isNotEmpty()) {
    $variationStockTotal = $vars->sum(function ($v) {
      $variationStatus = strtolower(trim((string) ($v->status ?? 'publish')));
      $stockStatus = strtolower(trim((string) ($v->stock_status ?? 'instock')));
      $isSellable = $variationStatus === 'publish'
        && in_array(preg_replace('/[\s_-]+/', '', $stockStatus), ['instock', 'available'], true);
      return $isSellable ? max(0, (int) ($v->stock_quantity ?? 0)) : 0;
    });
  }
  $quickAddMaximum = max(0, (int) ($variationStockTotal ?? ($p->stock_quantity ?? 0)));
  $configuredQuickAddMaximum = (int) ($p->max_orders_per_person ?? 0);
  if ($configuredQuickAddMaximum > 0) $quickAddMaximum = min($quickAddMaximum, $configuredQuickAddMaximum);
  if ($p->sold_individually ?? false) $quickAddMaximum = min($quickAddMaximum, 1);
  // Cards have no quantity selector, so they may quick-add only products whose seller minimum is one.
  $canQuickAdd = $quickAddMinimum === 1 && $quickAddMaximum >= 1;
  // Keep the quantity prompt for products with a minimum greater than one.
  $hasAvailableStock = $quickAddMaximum > 0;
  $hasColors = count($colorMap) > 0;
  $hasSizes  = count($sizeList) > 0;
  $hasCardOptions = ($coShowSwatches && $hasColors) || ($coShowSizes && $hasSizes);
  // Quick-add requires a sellable variation whenever there are options.
  $requiresVariationSelection = $hasAvailableStock && ($hasColors || $hasSizes);

  $cardStyle = $co['cardStyle'] ?? '';
  if ($coImgHeight) {
    $cardStyle .= ";--pc-image-height:{$coImgHeight}";
  }
  $optionsCount = $coShowSizes && $hasSizes ? count($sizeList) : ($coShowSwatches && $hasColors ? count($colorMap) : 0);
  $stockLabel = $cardRtl ? 'نفد المخزون' : 'Out of stock';

  /* ── Displayed product name ─────────────────────────────── */
  // $cardNameHtml can be passed as a variable for raw HTML (e.g. search highlights)
  $productDisplayName = $p->timeline_name ?? $p->tl_display_name ?? $p->name;
  $displayName = $coNameLimit > 0 ? Str::limit($productDisplayName, $coNameLimit) : $productDisplayName;
@endphp

<div class="product-card {{ $cardPresentation }}{{ $hasAvailableStock ? '' : ' pc-out-of-stock' }}" id="{{ $cardDomId }}" @if($cardStyle) style="{{ $cardStyle }}" @endif
     data-pid="{{ $pid }}"
     data-base-img="{{ $baseImg }}"
     data-base-price="{{ $basePrice }}"
     data-currency="{{ $currency }}"
     data-sale-label="{{ $cardRtl ? $cardCopy['sale'] : 'SALE' }}"
     data-has-available-stock="{{ $hasAvailableStock ? '1' : '0' }}"
     data-requires-variation-selection="{{ $requiresVariationSelection ? '1' : '0' }}"
     data-sel-color="{{ $initialColor }}"
     data-sel-color-label="{{ $initialColor ? ($colorMap[$initialColor]['display'] ?? $initialColor) : '' }}"
     data-sel-size="{{ $initialSize }}"
     data-sel-var="{{ $initialVariation['id'] ?? '' }}"
     data-vars='@json($jsVars)'>

  <div class="pc-media">
    <a href="{{ route('product', $pid) }}" class="product-card-img">
      <img src="{{ $displayImg ?: $fallbackImg }}" alt="{{ $productDisplayName }}" loading="lazy"
           id="{{ $domPrefix }}-img-{{ $pid }}" data-fallback="{{ $fallbackImg }}"
           onerror="if (this.getAttribute('src') !== this.dataset.fallback) this.src = this.dataset.fallback">
    </a>
    @if($coShowBadge)
      @if(!empty($p->flash_sale))
        <span class="badge-sale badge-flash">⚡ {{ $cardRtl ? $cardCopy['sale'].' '.round($p->flash_discount_pct).'%' : round($p->flash_discount_pct).'% OFF' }}</span>
      @else
        <span class="badge-sale" data-pc-discount @if(!$discounted) hidden @endif dir="auto">{{ $discount ? '-'.$discount.'%' : ($cardRtl ? $cardCopy['sale'] : 'SALE') }}</span>
      @endif
    @endif
    @if($coShowWishlist)
      <button type="button" class="wish-btn" data-wishlist-product-id="{{ $pid }}" aria-pressed="false"
              onclick="toggleWishlist(this,{{ $pid }})"
              aria-label="{{ $cardRtl ? $cardCopy['wishlist'] : 'Add to Wishlist' }}"
              title="{{ $cardRtl ? $cardCopy['wishlist'] : 'Add to Wishlist' }}">♡</button>
    @endif
  </div>

  <div class="product-card-body">
    <a href="{{ route('product', $pid) }}" class="product-card-name" title="{{ $productDisplayName }}">
      @if(!empty($cardNameSegments))
        @foreach($cardNameSegments as $nameSegment)
          @if($nameSegment['match'] ?? false)<mark class="search-hl">{{ $nameSegment['text'] }}</mark>@else{{ $nameSegment['text'] }}@endif
        @endforeach
      @else
        {{ $displayName }}
      @endif
    </a>

    @if($coShowRating)
    @php
      $__avgRating = \Illuminate\Support\Facades\DB::table('product_reviews')
        ->where('product_id', $pid)->where('approved', true)->avg('rating') ?? 0;
      $__avgRating = round((float)$__avgRating, 1);
    @endphp
    @if($__avgRating > 0)
    <div class="pc-rating-row">
      @for($__s=1;$__s<=5;$__s++)<span style="color:{{ $__s<=round($__avgRating)?'#f5a623':'#ddd' }};font-size:11px">★</span>@endfor
      <span style="font-size:11px;color:#666;margin-left:3px">{{ $__avgRating }}</span>
    </div>
    @endif
    @endif

    @if($hasCardOptions)
    <div class="pc-option-area">
    {{-- Color swatches --}}
    @if($coShowSwatches && $hasColors)
    <div class="pc-swatches" role="group" aria-label="{{ $cardRtl ? 'الألوان' : 'Colors' }}" id="{{ $domPrefix }}-swatches-{{ $pid }}">
      @foreach($colorMap as $colorName => $cdata)
      <button type="button" class="pc-swatch{{ $initialColor === $colorName ? ' selected' : '' }}"
              title="{{ $cdata['display'] ?? $colorName }}"
              aria-label="{{ $cdata['display'] ?? $colorName }}"
              data-color="{{ $colorName }}"
              data-display="{{ $cdata['display'] ?? $colorName }}"
              data-img="{{ $cdata['img'] ?? '' }}"
              aria-pressed="{{ $initialColor === $colorName ? 'true' : 'false' }}"
              style="--pc-swatch-color:{{ $cdata['hex'] }}"
              onclick="event.preventDefault();pcPickColor({{ $pid }},this.dataset.color,this)">
      </button>
      @endforeach
    </div>
    @endif

    <div class="pc-selected" id="{{ $domPrefix }}-selected-{{ $pid }}" aria-live="polite"></div>

    {{-- Size pills --}}
    @if($coShowSizes && $hasSizes)
    <div class="pc-sizes" role="group" aria-label="{{ $cardRtl ? 'الخيارات' : 'Options' }}" id="{{ $domPrefix }}-sizes-{{ $pid }}">
      @foreach($sizeList as $sz)
      <button type="button" class="pc-size{{ $initialSize === $sz ? ' selected' : '' }}"
              data-size="{{ $sz }}" aria-pressed="{{ $initialSize === $sz ? 'true' : 'false' }}"
              onclick="event.preventDefault();pcPickSize({{ $pid }},this.dataset.size,this)">
        {{ $sz }}
      </button>
      @endforeach
    </div>
    @endif
    </div>

    @endif
    @if($optionsCount)
      <a href="{{ route('product', $pid) }}" class="pc-option-link">{{ $cardRtl ? '+'.$optionsCount.' خيارات' : '+'.$optionsCount.' options' }}</a>
    @endif

    <div class="product-card-price" aria-live="polite">
      <bdi class="price-main{{ $discounted ? ' sale' : '' }}" id="{{ $domPrefix }}-price-{{ $pid }}">{{ number_format($displayPrice, 2) }} {{ $currency }}</bdi>
      @if($coShowOldPrice)
        <bdi class="price-old" id="{{ $domPrefix }}-orig-{{ $pid }}" @if(!$discounted) hidden @endif
             aria-label="{{ $cardRtl ? 'السعر قبل الخصم' : 'Original price' }}">{{ $discounted ? number_format($regularPrice, 2) : '' }}</bdi>
      @endif
    </div>

    @if(!$hasAvailableStock)
      <span class="pc-stock-label">{{ $stockLabel }}</span>
    @endif

    @if(($coShowAddToCart && $hasAvailableStock) || $coShowDetails || $coRemoveWishlist)
    <div class="pc-actions">
      @if(($coShowAddToCart && $hasAvailableStock) || $coRemoveWishlist)
      <div class="pc-add-row">
        @if($coShowAddToCart && $hasAvailableStock)
          @if($canQuickAdd)
            <button type="button" class="card-add-btn" id="{{ $domPrefix }}-add-{{ $pid }}"
                    data-name="{{ $productDisplayName }}" data-img="{{ $displayImg }}"
                    aria-label="{{ $cardRtl ? $cardCopy['add'] : 'Add to Cart' }}: {{ $productDisplayName }}"
                    @disabled($requiresVariationSelection && !$initialVariation)
                    onclick="pcAddToCart({{ $pid }},this)">
              {{ $cardRtl ? $cardCopy['add'] : 'Add to Cart' }}
            </button>
          @else
            <a class="card-add-btn" href="{{ route('product', $pid) }}">
              {{ $quickAddMaximum < $quickAddMinimum
                  ? ($cardRtl ? $cardCopy['unavailable'] : 'Unavailable')
                  : ($cardRtl ? $cardCopy['selectQty'].$quickAddMinimum.')' : 'Select quantity (min '.$quickAddMinimum.')') }}
            </a>
          @endif
        @endif
        @if($coRemoveWishlist)
          <form action="{{ route('wishlist.remove', $pid) }}" method="POST">
            @csrf @method('DELETE')
            <button class="btn btn-outline" aria-label="{{ $cardRtl ? $cardCopy['removeWishlist'] : 'Remove from wishlist' }}">✕</button>
          </form>
        @endif
      </div>
      @endif
      @if($coShowDetails)
        <a href="{{ route('product', $pid) }}" class="card-details-btn">
          {{ $cardRtl ? $cardCopy['details'] : 'See details' }}
        </a>
      @endif
    </div>
    @endif

    {{-- Coupon banner --}}
    @if($coShowCoupon && !empty($p->coupon))
    @php
      $__coupon = $p->coupon;
      $__base   = $p->on_sale ? $p->sale_price : $p->price;
      $__cprice = $__coupon->discount_type === 'percent'
          ? $__base * (1 - (float)$__coupon->amount / 100)
          : max(0, $__base - (float)$__coupon->amount);
    @endphp
    <a href="{{ route('cart') }}" class="pc-coupon-bar" onclick="event.preventDefault();saveCouponAndGo('{{ strtoupper($__coupon->code) }}','{{ route('cart') }}')" title="Click to apply this coupon at checkout">
      <span class="pc-coupon-left">
        🏷️ {{ $cardRtl ? $cardCopy['coupon'] : 'WITH CODE' }} <strong class="pc-coupon-code">{{ strtoupper($__coupon->code) }}</strong>
      </span>
      <span class="pc-coupon-right">
        ↓ {{ number_format($__cprice, 0) }} EGP
      </span>
    </a>
    @endif

  </div>
</div>

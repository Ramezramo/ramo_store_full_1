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
  $coCompact        = $co['compact']        ?? false;  // smaller card for scroll rows
  $coNameLimit      = (int)($co['nameLimit'] ?? 0);    // 0 = no limit
  $coImgHeight      = $co['imgHeight']      ?? null;   // e.g. '180px'
  $coIdPrefix       = $co['idPrefix']       ?? 'pc';   // id prefix on outer div
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
  $displayImg = $p->thumbnail_url;
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
    'img'   => (is_array($v->images) && count($v->images)) ? \App\Constants\AppConstants::imageUrl($v->images[0]) : null,
  ])->values()->toArray();

  $basePrice = $p->on_sale ? $p->sale_price : $p->price;
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
        && !in_array($stockStatus, ['outofstock', 'out_of_stock', 'out of stock'], true);
      return $isSellable ? max(0, (int) ($v->stock_quantity ?? 0)) : 0;
    });
  }
  $quickAddMaximum = max(0, (int) ($variationStockTotal ?? ($p->stock_quantity ?? 0)));
  $configuredQuickAddMaximum = (int) ($p->max_orders_per_person ?? 0);
  if ($configuredQuickAddMaximum > 0) $quickAddMaximum = min($quickAddMaximum, $configuredQuickAddMaximum);
  if ($p->sold_individually ?? false) $quickAddMaximum = min($quickAddMaximum, 1);
  // Cards have no quantity selector, so they may quick-add only products whose seller minimum is one.
  $canQuickAdd = $quickAddMinimum === 1 && $quickAddMaximum >= 1;
  // Do not render an Add to Cart action when the effective available stock is zero.
  // Products with stock below their minimum order quantity keep the existing
  // product-link prompt so the customer can review the requirement.
  $hasAvailableStock = $quickAddMaximum > 0;
  $hasColors = count($colorMap) > 1;
  $hasSizes  = count($sizeList) > 0;
  // Variation cards require an explicit color/size choice before quick-add.
  // Keep the action in the DOM so JavaScript can reveal it after a sellable
  // variation is selected, but do not show it on the initial card render.
  $requiresVariationSelection = $hasAvailableStock && ($hasColors || $hasSizes);

  /* ── Compact-mode style helpers ─────────────────────────── */
  $imgStyle  = $coCompact ? 'height:180px' : '';
  if ($coImgHeight) $imgStyle = "height:{$coImgHeight}";
  $bodyPad   = $coCompact ? 'padding:8px' : '';
  $nameStyle = $coCompact ? 'font-size:12px' : '';
  $priceStyle= $coCompact ? 'font-size:12px' : '';

  /* ── Timeline-controlled dimensions / spacing ───────────── */
  $cardStyle = $co['cardStyle'] ?? '';

  /* ── Displayed product name ─────────────────────────────── */
  // $cardNameHtml can be passed as a variable for raw HTML (e.g. search highlights)
  $productDisplayName = $p->timeline_name ?? $p->tl_display_name ?? $p->name;
  $displayName = $coNameLimit > 0 ? Str::limit($productDisplayName, $coNameLimit) : $productDisplayName;
@endphp

<div class="product-card" id="{{ $coIdPrefix }}-{{ $pid }}" @if($cardStyle) style="{{ $cardStyle }}" @endif
     data-pid="{{ $pid }}"
     data-base-img="{{ $displayImg }}"
     data-base-price="{{ $basePrice }}"
     data-has-available-stock="{{ $hasAvailableStock ? '1' : '0' }}"
     data-requires-variation-selection="{{ $requiresVariationSelection ? '1' : '0' }}"
     data-vars='@json($jsVars)'>

  <a href="{{ route('product', $pid) }}" class="product-card-img" @if($imgStyle) style="{{ $imgStyle }}" @endif>
    @if($displayImg)
      <img src="{{ $displayImg }}" alt="{{ $productDisplayName }}" loading="lazy" id="pc-img-{{ $pid }}"
           onerror="this.onerror=null;this.style.display='none';this.parentElement.querySelector('.pc-img-fallback')?.style.setProperty('display','flex')">
      <div class="pc-img-fallback" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:#f7f7f7;color:#ccc;font-size:32px">🛍️</div>
    @else
      <div class="placeholder" id="pc-img-{{ $pid }}">🛍️</div>
    @endif
    @if($coShowBadge && $p->on_sale)
      @if(!empty($p->flash_sale))
        <span class="badge-sale badge-flash">⚡ {{ $cardRtl ? $cardCopy['sale'].' '.round($p->flash_discount_pct).'%' : round($p->flash_discount_pct).'% OFF' }}</span>
      @elseif($p->discount_percentage > 0)
        <span class="badge-sale">-{{ round($p->discount_percentage) }}%</span>
      @else
        <span class="badge-sale">{{ $cardRtl ? $cardCopy['sale'] : 'SALE' }}</span>
      @endif
    @endif
    @if($coShowWishlist)
    <button class="wish-btn" data-wishlist-product-id="{{ $pid }}" onclick="event.preventDefault();toggleWishlist(this,{{ $pid }})" title="{{ $cardRtl ? $cardCopy['wishlist'] : 'Add to Wishlist' }}">♡</button>
    @endif
  </a>

  <div class="product-card-body" @if($bodyPad) style="{{ $bodyPad }}" @endif>
    <a href="{{ route('product', $pid) }}" class="product-card-name" @if($nameStyle) style="{{ $nameStyle }}" @endif>
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

    {{-- Color swatches (only when 2+ colors exist) --}}
    @if($coShowSwatches && $hasColors)
    <div class="pc-swatches" id="pc-swatches-{{ $pid }}">
      @foreach($colorMap as $colorName => $cdata)
      <button class="pc-swatch"
              title="{{ $cdata['display'] ?? $colorName }}"
              aria-label="{{ $cdata['display'] ?? $colorName }}"
              data-color="{{ $colorName }}"
              data-display="{{ $cdata['display'] ?? $colorName }}"
              data-img="{{ $cdata['img'] ?? '' }}"
              style="background:{{ $cdata['hex'] }};{{ $cdata['hex'] === '#f5f5f5' ? 'border-color:#bbb' : '' }}"
              onclick="event.preventDefault();pcPickColor({{ $pid }},'{{ addslashes($colorName) }}',this)">
      </button>
      @endforeach
    </div>
    @endif

    <div class="pc-selected" id="pc-selected-{{ $pid }}" aria-live="polite"></div>

    {{-- Size pills --}}
    @if($coShowSizes && $hasSizes)
    <div class="pc-sizes" id="pc-sizes-{{ $pid }}">
      @foreach($sizeList as $sz)
      <button class="pc-size"
              data-size="{{ $sz }}"
              onclick="event.preventDefault();pcPickSize({{ $pid }},'{{ addslashes($sz) }}',this)">
        {{ $sz }}
      </button>
      @endforeach
    </div>
    @endif

    {{-- Price --}}
    <div class="product-card-price">
      @if($p->on_sale)
        <span class="price-main sale" id="pc-price-{{ $pid }}"{{ $priceStyle ? ' style="'.$priceStyle.'"' : '' }}>{{ number_format($p->sale_price, 2) }} EGP</span>
        @if($coShowOldPrice)
        <span class="price-old" id="pc-orig-{{ $pid }}" aria-label="{{ $cardRtl ? 'السعر قبل الخصم' : 'Original price' }}">{{ number_format($p->price, 2) }} EGP</span>
        @endif
      @else
        <span class="price-main" id="pc-price-{{ $pid }}"{{ $priceStyle ? ' style="'.$priceStyle.'"' : '' }}>{{ number_format($p->price, 2) }} EGP</span>
      @endif
    </div>

    @if(($coShowAddToCart && ($hasAvailableStock || $requiresVariationSelection)) || $coRemoveWishlist)
    <div class="pc-actions" style="{{ $coRemoveWishlist ? 'display:flex;gap:8px;margin-top:4px' : (($requiresVariationSelection && !$coRemoveWishlist) ? 'display:none' : '') }}">
      @if($coShowAddToCart && ($hasAvailableStock || $requiresVariationSelection))
        @if($canQuickAdd)
        <button class="card-add-btn{{ $coRemoveWishlist ? '' : '' }}" id="pc-add-{{ $pid }}"
                data-name="{{ addslashes($productDisplayName) }}"
                data-img="{{ $displayImg }}"
                style="{{ $coRemoveWishlist ? 'flex:1' : (($requiresVariationSelection && !$coRemoveWishlist) ? 'display:none' : '') }}"
                onclick="pcAddToCart({{ $pid }})">
          {{ $cardRtl ? $cardCopy['add'] : 'Add to Cart' }}
        </button>
        @else
        <a class="card-add-btn{{ $coRemoveWishlist ? '' : '' }}" href="{{ route('product', $pid) }}"
           style="{{ $coRemoveWishlist ? 'flex:1' : '' }};text-align:center;text-decoration:none">
          {{ $quickAddMaximum < $quickAddMinimum
              ? ($cardRtl ? $cardCopy['unavailable'] : 'Unavailable')
              : ($cardRtl ? $cardCopy['selectQty'].$quickAddMinimum.')' : 'Select quantity (min '.$quickAddMinimum.')') }}
        </a>
        @endif
      @endif
      @if($coRemoveWishlist)
      <form action="{{ route('wishlist.remove', $pid) }}" method="POST" style="flex-shrink:0">
        @csrf @method('DELETE')
        <button class="btn btn-outline" style="padding:9px 12px;font-size:13px;border-radius:8px;color:#e02020;border-color:#e02020;height:100%" title="{{ $cardRtl ? $cardCopy['removeWishlist'] : 'Remove from wishlist' }}">✕</button>
      </form>
      @endif
    </div>
    @endif

    @if($coShowDetails)
    <a href="{{ route('product', $pid) }}" class="card-details-btn">
      {{ $cardRtl ? $cardCopy['details'] : 'See details' }}
    </a>
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

@once
<style>
.product-card{min-height:var(--pc-card-height,auto)}
/* The editor's Element Spacing value controls every vertical gap. At 0px these rules
   also neutralise the original product-card margins and top/bottom body padding. */
.product-card .product-card-img{width:var(--pc-image-width,100%);height:var(--pc-image-height,auto);max-width:100%;align-self:center;margin-bottom:var(--pc-element-spacing,0px)}
.product-card .product-card-body{padding:var(--pc-element-spacing,0px) 4px;gap:var(--pc-element-spacing,0px)}
.product-card .product-card-name,.product-card .pc-swatches,.product-card .pc-sizes,.product-card .pc-selected,.product-card .product-card-price,.product-card .pc-actions,.product-card .card-add-btn,.product-card .card-details-btn{margin:0}
.product-card .product-card-price{padding-top:0}
.product-card .pc-coupon-bar{margin:0}
.pc-coupon-bar{display:flex;text-decoration:none;border-radius:0 0 10px 10px;overflow:hidden;margin:10px -14px -14px;font-size:11px;font-weight:700;line-height:1}
.pc-coupon-left{flex:1;background:#7c3aed;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pc-coupon-code{background:rgba(255,255,255,.2);border-radius:4px;padding:1px 5px;letter-spacing:.03em}
.pc-coupon-right{background:#5b21b6;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:4px;white-space:nowrap;flex-shrink:0}
</style>
@endonce
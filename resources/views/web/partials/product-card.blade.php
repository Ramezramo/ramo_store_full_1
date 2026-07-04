@php
  $pid = $p->id;
  $vars = collect($cardVariations ?? []);
  $co = $cardOptions ?? [];
  $coShowBadge     = $co['showBadge']     ?? true;
  $coShowWishlist  = $co['showWishlist']  ?? true;
  $coShowSwatches  = $co['showSwatches']  ?? true;
  $coShowSizes     = $co['showSizes']     ?? true;
  $coShowOldPrice  = $co['showOldPrice']  ?? true;
  $coShowAddToCart = $co['showAddToCart'] ?? true;
  $coShowDetails   = $co['showDetails']   ?? true;
  $coShowCoupon    = $co['showCoupon']    ?? true;
  $coShowRating    = $co['showRating']    ?? false;

  /* ── Build color & size maps ────────────────── */
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
        $colorMap[$c] = ['img' => $imgUrl, 'hex' => ($swatchHex[strtolower($c)] ?? '#ccc')];
      }
    }
    if (isset($attrs['Size']) && !in_array($attrs['Size'], $sizeList)) {
      $sizeList[] = $attrs['Size'];
    }
  }

  /* ── Resolve display image upfront ─────────── */
  $displayImg = $p->thumbnail_url;
  if (!$displayImg && count($colorMap)) {
    $first = array_values($colorMap)[0];
    $displayImg = $first['img'] ?? null;
  }

  /* ── Slim variation payload for JS ─────────── */
  $jsVars = $vars->map(fn($v) => [
    'id'    => $v->id,
    'price' => (float)$v->price,
    'sale'  => (float)$v->sale_price,
    'stock' => (int)$v->stock_quantity,
    'attrs' => (array)($v->attributes ?? []),
    'img'   => (is_array($v->images) && count($v->images)) ? \App\Constants\AppConstants::imageUrl($v->images[0]) : null,
  ])->values()->toArray();

  $basePrice = $p->on_sale ? $p->sale_price : $p->price;
  $hasColors = count($colorMap) > 1;
  $hasSizes  = count($sizeList) > 0;
@endphp

<div class="product-card" id="pc-{{ $pid }}"
     data-pid="{{ $pid }}"
     data-base-img="{{ $displayImg }}"
     data-base-price="{{ $basePrice }}"
     data-vars='@json($jsVars)'>

  <a href="{{ route('product', $pid) }}" class="product-card-img">
    @if($displayImg)
      <img src="{{ $displayImg }}" alt="{{ $p->name }}" loading="lazy" id="pc-img-{{ $pid }}">
    @else
      <div class="placeholder" id="pc-img-{{ $pid }}">🛍️</div>
    @endif
    @if($coShowBadge && $p->on_sale)
      @if(!empty($p->flash_sale))
        <span class="badge-sale badge-flash">⚡ {{ round($p->flash_discount_pct) }}% OFF</span>
      @elseif($p->discount_percentage > 0)
        <span class="badge-sale">-{{ round($p->discount_percentage) }}%</span>
      @else
        <span class="badge-sale">SALE</span>
      @endif
    @endif
    @if($coShowWishlist)
    <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,{{ $pid }})" title="Wishlist">♡</button>
    @endif
  </a>

  <div class="product-card-body">
    <a href="{{ route('product', $pid) }}" class="product-card-name">{{ $p->name }}</a>

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
              title="{{ $colorName }}"
              data-color="{{ $colorName }}"
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
        <span class="price-main sale" id="pc-price-{{ $pid }}">{{ number_format($p->sale_price, 2) }} EGP</span>
        @if($coShowOldPrice)
        <span class="price-old" id="pc-orig-{{ $pid }}">{{ number_format($p->price, 2) }}</span>
        @endif
      @else
        <span class="price-main" id="pc-price-{{ $pid }}">{{ number_format($p->price, 2) }} EGP</span>
      @endif
    </div>

    @if($coShowAddToCart)
    <button class="card-add-btn" id="pc-add-{{ $pid }}"
            data-name="{{ addslashes($p->name) }}"
            data-img="{{ $displayImg }}"
            onclick="pcAddToCart({{ $pid }})">
      Add to Cart
    </button>
    @endif

    @if($coShowDetails)
    <a href="{{ route('product', $pid) }}" class="card-details-btn">
      See details
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
        🏷️ WITH CODE <strong class="pc-coupon-code">{{ strtoupper($__coupon->code) }}</strong>
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
.pc-coupon-bar{display:flex;text-decoration:none;border-radius:0 0 10px 10px;overflow:hidden;margin:10px -14px -14px;font-size:11px;font-weight:700;line-height:1}
.pc-coupon-left{flex:1;background:#7c3aed;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pc-coupon-code{background:rgba(255,255,255,.2);border-radius:4px;padding:1px 5px;letter-spacing:.03em}
.pc-coupon-right{background:#5b21b6;color:#fff;padding:8px 10px;display:flex;align-items:center;gap:4px;white-space:nowrap;flex-shrink:0}
</style>
@endonce

@extends('layouts.app')
@php
  $isAr = session('locale') === 'ar';
  $displayProductName = $product->tl_display_name ?? $product->timeline_name ?? $product->name;
  $displayProductDescription = $product->tl_display_description ?? $product->timeline_description ?? $product->description;
  $productText = [
    'isAr' => $isAr,
    'minimum' => $isAr ? 'الحد الأدنى' : 'Minimum',
    'maximum' => $isAr ? 'الحد الأقصى' : 'Maximum',
    'perOrder' => $isAr ? 'في الطلب' : 'per order',
    'notEnough' => $isAr ? 'المنتج ده مش متوفر منه كمية كفاية للحد الأدنى' : 'This product does not have enough stock to meet its minimum order quantity of',
    'inStock' => $isAr ? 'متوفر' : 'In Stock',
    'available' => $isAr ? 'متاح' : 'available',
    'outOfStock' => $isAr ? 'مش متوفر' : 'Out of Stock',
    'variationComingSoon' => $isAr ? 'الاختيار ده هيتوفر قريب' : 'This variation will be available soon.',
    'addToCart' => $isAr ? 'ضيف للسلة' : 'Add to Cart',
    'unavailable' => $isAr ? 'غير متاح' : 'Unavailable',
    'select' => $isAr ? 'اختار' : 'Please select a',
  ];
@endphp
@section('title', $displayProductName . ' — Ramo Store')

@section('content')
<div class="page product-page {{ $isAr ? 'product-page-ar' : '' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

  <div class="breadcrumb">
    <a href="{{ route('home') }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a><span>/</span>
    <a href="{{ route('shop') }}">{{ $isAr ? 'المتجر' : 'Shop' }}</a><span>/</span>
    <strong>{{ Str::limit($displayProductName, 40) }}</strong>
  </div>

  <div class="product-layout">

    {{-- GALLERY --}}
    <div>
      @php
        // gallery_urls already contains other_images + natural_images; avoid duplicating them
        $allImages = array_values(array_unique(array_filter(array_merge(
          $product->thumbnail_url ? [$product->thumbnail_url] : [],
          $product->gallery_urls ?? []
        ))));
      @endphp
      <div class="gallery-wrap">
        <div class="gallery-thumbs" id="gallery-thumbs">
          @foreach($allImages as $i => $url)
          <div class="gallery-thumb {{ $i === 0 ? 'active' : '' }}" onclick="switchImg(this,'{{ $url }}')">
            <img src="{{ $url }}" alt="{{ $isAr ? 'صورة' : 'Image' }} {{ $i+1 }}" loading="lazy"
                 onerror="handleThumbError(this)">
          </div>
          @endforeach
        </div>
        <div class="gallery-main" id="gallery-main-wrap">
          @if($product->thumbnail_url)
            <img src="{{ $product->thumbnail_url }}" alt="{{ $displayProductName }}" id="main-img"
                 onerror="handleImgError(this)">
          @else
            <img src="" alt="{{ $displayProductName }}" id="main-img" style="display:none"
                 onerror="handleImgError(this)">
            <div id="main-img-placeholder" class="img-placeholder-box" style="width:100%;height:100%">
              <span class="img-placeholder-icon">🖼️</span>
              <span class="img-placeholder-text">{{ $isAr ? 'مفيش صورة' : 'No image' }}</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- INFO --}}
    <div class="product-info">

      {{-- Title + Wishlist --}}
      <div class="pi-title-row">
        <h1 class="pi-title">{{ $displayProductName }}</h1>
        <button class="pi-wish-btn {{ $inWishlist ? 'wished' : '' }}" id="wish-btn"
                data-wishlist-product-id="{{ $product->id }}"
                onclick="toggleWishlist(this, {{ $product->id }})"
                title="{{ $inWishlist ? ($isAr ? 'شيل من المفضلة' : 'Remove from Wishlist') : ($isAr ? 'ضيف للمفضلة' : 'Add to Wishlist') }}">
          {{ $inWishlist ? '♥' : '♡' }}
        </button>
      </div>

      @if($displayProductDescription || $product->unit_label)
      <div class="desc-block pi-desc">
        @if($displayProductDescription)
          @php
            // Split on bullet characters (•, -, or *) to detect list items
            $raw = $displayProductDescription;
            // Separate a leading sentence (before first bullet) from the bullet list
            $parts = preg_split('/\s*[•]\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY);
            $hasBullets = str_contains($raw, '•') && count($parts) > 1;
          @endphp
          @if($hasBullets)
            @php
              // First segment before the first bullet is the intro text
              $firstBulletPos = strpos($raw, '•');
              $intro = $firstBulletPos > 0 ? trim(substr($raw, 0, $firstBulletPos)) : null;
              // $parts[0] is the intro when there's text before the first •; skip it
              $bullets = array_values(array_filter(array_map('trim',
                ($intro !== null && $intro !== '') ? array_slice($parts, 1) : $parts
              )));
            @endphp
            @if($intro)
              <p class="desc-intro">{{ $intro }}</p>
            @endif
            <ul class="desc-bullets">
              @foreach($bullets as $bullet)
                <li>{{ $bullet }}</li>
              @endforeach
            </ul>
          @else
            <p>{{ $raw }}</p>
          @endif
        @endif
        @if($product->unit_label)<p style="margin-top:10px;font-size:13px"><strong>{{ $isAr ? 'الوحدة:' : 'Unit:' }}</strong> {{ $product->unit_label }}</p>@endif
      </div>
      @endif

      {{-- Rating summary — always visible --}}
      @php
        $totalRev = $reviews->count();
        $avgRating = $totalRev ? round($reviews->avg('rating'), 1) : 0;
      @endphp
      <div class="pi-rating-row">
        <div class="pi-stars">
          @for($s=1;$s<=5;$s++)
            <span class="{{ $s <= round($avgRating) ? 'pi-star-filled' : 'pi-star-empty' }}">★</span>
          @endfor
        </div>
        @if($totalRev)
          <span class="pi-rating-val">{{ $avgRating }}</span>
          <a href="#reviews" class="pi-rating-count">({{ $totalRev }} {{ $isAr ? 'تقييم' : 'review'.($totalRev!=1?'s':'') }})</a>
        @else
          <span class="pi-rating-none">{{ $isAr ? 'لسه مفيش تقييمات' : 'No reviews yet' }}</span>
        @endif
      </div>

      {{-- Stock badge --}}
      <div id="stock-display" class="pi-stock">
        @if($product->stock_quantity > 0)
          <span class="badge-stock-ok">✓ {{ $isAr ? 'متوفر' : 'In Stock' }} ({{ number_format($product->stock_quantity) }} {{ $isAr ? 'متاح' : 'available' }})</span>
        @else
          <span class="badge-stock-no">{{ $isAr ? 'مش متوفر' : 'Out of Stock' }}</span>
        @endif
      </div>

      {{-- Price block --}}
      @php
        $discPct  = (float)($product->discount_percentage ?? 0);
        $hasDisc  = $discPct > 0;
        $varEffPrices = $variations->map(function ($v) use ($discPct) {
          $reg = (float)$v->regular_price;
          $eff = (float)$v->price;
          if ($discPct > 0 && $reg > 0 && $eff >= $reg) {
            return round($reg * (1 - $discPct / 100), 2);
          }
          return $eff;
        })->sort()->values();
        $varRegPrices = $variations->pluck('regular_price')->map(fn($p) => (float)$p)->sort()->values();
        $minEff = $varEffPrices->first() ?? $product->display_price;
        $maxEff = $varEffPrices->last()  ?? $product->display_price;
        $minReg = $varRegPrices->first() ?? $minEff;
        $isRange = $variations->count() > 0 && round($minEff, 2) !== round($maxEff, 2);
      @endphp

      <div class="pi-price-block" id="price-block">
        <div class="pi-price-row">
          @if($isRange)
            <span class="pi-price-main on-sale" id="price-display">{{ number_format($minEff,2) }} – {{ number_format($maxEff,2) }} EGP</span>
            <span class="pi-price-orig" id="orig-display" style="display:none"></span>
          @elseif($hasDisc)
            <span class="pi-price-main on-sale" id="price-display">{{ number_format($minEff,2) }} EGP</span>
            <span class="pi-price-orig" id="orig-display">{{ number_format($minReg,2) }} EGP</span>
          @else
            <span class="pi-price-main" id="price-display">{{ number_format($minEff,2) }} EGP</span>
            <span class="pi-price-orig" id="orig-display" style="display:none"></span>
          @endif
          @if($hasDisc)
            <span class="pi-disc-badge" id="disc-badge">{{ $isAr ? 'خصم ' . round($discPct) . '%' : round($discPct) . '% OFF' }}</span>
          @else
            <span class="pi-disc-badge" id="disc-badge" style="display:none"></span>
          @endif
        </div>
        @if($hasDisc && $isRange)
        <div class="pi-sale-note" id="price-range-note" role="note" style="display:none">
          <span class="pi-sale-note-title">{{ $isAr ? 'ليه فيه سعرين؟' : 'Why are there two prices?' }}</span>
          <span>{{ $isAr ? 'السعر بيتغير حسب اللون أو المقاس اللي هتختاره. الأسعار المعروضة هي نطاق أسعار الاختيارات المتاحة.' : 'The price changes according to the color or size you choose. The displayed amount is the price range for the available options.' }}</span>
          <span class="pi-sale-saving">🏷️ {{ $isAr ? 'بتوفّر ' . round($discPct) . '% من السعر الأصلي' : 'You save ' . round($discPct) . '% off the original price' }}</span>
        </div>
        @elseif($isRange)
        <div class="pi-price-explanation" id="price-range-note" role="note" style="display:none">
          <span class="pi-price-note-title">{{ $isAr ? 'السعر حسب الاختيار' : 'Price depends on your selection' }}</span>
          <span>{{ $isAr ? 'السعر بيتغير حسب اللون أو المقاس اللي هتختاره.' : 'The price changes according to the color or size you choose.' }}</span>
        </div>
        @endif
      </div>

      <div class="var-selected-label" id="product-sel-summary" aria-live="polite" style="margin:4px 0 12px;font-size:13px;color:var(--c-mid)"></div>

      {{-- VARIATIONS ENGINE --}}
      @php
        $varData = $variations->map(fn($v) => [
          'id'     => $v->id,
          'reg'    => (float)$v->regular_price,
          'price'  => (float)$v->price,
          'sale'   => (float)$v->sale_price,
          'stock'  => (int)$v->stock_quantity,
          'attrs'  => is_array($v->attributes) ? $v->attributes : [],
          'main'   => (bool)$v->main_variation,
          'images' => array_values(array_map(
              fn($p) => \App\Constants\AppConstants::imageUrl($p),
              array_filter($v->images ?? [], fn($p) => \Illuminate\Support\Facades\Storage::disk('public')->exists($p))
          )),
        ])->values();

        $attrMap = [];
        $colorCodeMap = [];
        foreach ($varData as $v) {
          $attrs = $v['attrs'] ?? [];
          foreach ($attrs as $k => $val) {
            if (str_starts_with((string)$k, '_')) continue;
            if (!isset($attrMap[$k])) $attrMap[$k] = [];
            if (!in_array($val, $attrMap[$k], true)) $attrMap[$k][] = $val;
          }
          if (isset($attrs['Color'], $attrs['_color_code']) && preg_match('/^#[0-9a-fA-F]{6}$/', (string)$attrs['_color_code'])) {
            $colorCodeMap[(string)$attrs['Color']] = strtoupper((string)$attrs['_color_code']);
          }
        }
      @endphp

      @if(!empty($attrMap))
      <div class="pi-variations-wrap">
        @foreach($attrMap as $attrKey => $attrValues)
          @php
            $isColor = strtolower($attrKey) === 'color';
            $attrLabel = $isAr ? match(strtolower($attrKey)) {
              'color' => 'اللون', 'size' => 'المقاس', default => $attrKey,
            } : $attrKey;
          @endphp
          <div class="pi-var-group">
            <div class="var-label">
              {{ $attrLabel }}
              @if($isColor) <span class="var-selected-label" id="sel-{{ Str::slug($attrKey) }}"></span>@endif
            </div>
            <div class="var-options" id="opts-{{ Str::slug($attrKey) }}">
              @foreach($attrValues as $val)
                @if($isColor)
                  <div class="var-color-option"
                       data-attr-key="{{ $attrKey }}"
                       data-attr-val="{{ $val }}"
                       role="button"
                       tabindex="0"
                       aria-label="{{ $isAr ? 'اختيار اللون: ' : 'Select color: ' }}{{ \App\Support\StorefrontLabels::color($val, $isAr) }}"
                       onclick="selectColorCard(event, this)"
                       onkeydown="if(event.key === 'Enter' || event.key === ' '){event.preventDefault();selectColorCard(event, this)}">
                    <button class="var-swatch"
                            data-attr-key="{{ $attrKey }}"
                            data-attr-val="{{ $val }}"
                            data-attr-display="{{ \App\Support\StorefrontLabels::color($val, $isAr) }}"
                            onclick="selectAttr('{{ $attrKey }}','{{ $val }}',this)"
                            onmouseenter="previewColorImage('{{ $attrKey }}','{{ $val }}')"
                            onmouseleave="restoreImage()"
                            title="{{ \App\Support\StorefrontLabels::color($val, $isAr) }}"
                            aria-label="{{ $isAr ? 'اللون: ' : 'Color: ' }}{{ \App\Support\StorefrontLabels::color($val, $isAr) }}"
                            style="background-color: {{ $colorCodeMap[$val] ?? 'var(--swatch-' . Str::slug($val) . ', #999)' }}">
                    </button>
                    <span class="var-color-name">{{ \App\Support\StorefrontLabels::color($val, $isAr) }}</span>
                    <div class="color-qty-stepper" id="color-qty-{{ Str::slug($attrKey) }}-{{ Str::slug($val) }}" hidden></div>
                  </div>
                @else
                  <button class="var-btn"
                          data-attr-key="{{ $attrKey }}"
                          data-attr-val="{{ $val }}"
                          onclick="selectAttr('{{ $attrKey }}','{{ $val }}',this)">{{ $val }}</button>
                @endif
              @endforeach
            </div>
            <div class="var-hint" id="hint-{{ Str::slug($attrKey) }}"></div>
          </div>
        @endforeach
      </div>
      @endif

      {{-- ADD TO CART + WISHLIST --}}
      @php
        $minimumOrderQty = max(1, (int) ($product->minimum_order_qty ?? 1));
        $configuredMaximumOrderQty = (int) ($product->max_orders_per_person ?? 0);
        $initialStockQty = max(0, (int) ($product->stock_quantity ?? 0));
        $maximumOrderQty = $configuredMaximumOrderQty > 0
          ? min($initialStockQty, $configuredMaximumOrderQty)
          : $initialStockQty;
        if ($product->sold_individually ?? false) $maximumOrderQty = min($maximumOrderQty, 1);
        $quantityIsOrderable = $maximumOrderQty >= $minimumOrderQty;
      @endphp
      <div class="pi-cart-row">
        <div id="single-qty-controls">
          <div class="qty-input">
            <button type="button" onclick="changeQty(-1)">−</button>
            <input type="number" id="qty" value="{{ $quantityIsOrderable ? $minimumOrderQty : 1 }}"
                   min="{{ $minimumOrderQty }}" max="{{ max(1, $maximumOrderQty) }}">
            <button type="button" onclick="changeQty(1)">+</button>
          </div>
          <div id="quantity-limit-hint" style="margin-top:7px;font-size:12px;color:var(--c-mid)">
            {{ $isAr ? 'الحد الأدنى ' . $minimumOrderQty . ' · الحد الأقصى ' . $maximumOrderQty . ' في الطلب' : 'Minimum ' . $minimumOrderQty . ' · Maximum ' . $maximumOrderQty . ' per order' }}
          </div>
        </div>
        <button class="add-to-cart-btn pi-atc-btn" id="add-to-cart-btn" {{ $quantityIsOrderable ? '' : 'disabled' }}
                onclick="handleAddToCart({{ $product->id }}, '{{ addslashes($displayProductName) }}', {{ $product->display_price }}, '{{ $product->thumbnail_url }}')">
          {{ $quantityIsOrderable ? ($isAr ? '🛒 ضيف للسلة' : '🛒 Add to Cart') : ($isAr ? 'مش متوفر' : 'Unavailable') }}
        </button>
      </div>


    </div>

  </div>

  @if($variations->count())
    @php
      $debugVariationRows = $variations->map(function ($variation) use ($discPct, $isAr, $minimumOrderQty, $configuredMaximumOrderQty, $product) {
        $attrs = is_array($variation->attributes) ? $variation->attributes : [];
        $color = null;
        $size = null;
        $otherAttributes = [];
        foreach ($attrs as $key => $value) {
          $normalizedKey = strtolower((string) $key);
          if ($normalizedKey === 'color') {
            $color = $value;
          } elseif ($normalizedKey === 'size') {
            $size = $value;
          } elseif (str_starts_with($normalizedKey, '_')) {
            continue;
          } else {
            $otherAttributes[] = ((string) $key) . ': ' . ((string) $value);
          }
        }

        $originalPrice = (float) ($variation->regular_price ?? 0);
        $currentPrice = (float) ($variation->price ?? 0);
        if ($discPct > 0 && $originalPrice > 0 && $currentPrice >= $originalPrice) {
          $currentPrice = round($originalPrice * (1 - $discPct / 100), 2);
        }
        $discountAmount = max(0, $originalPrice - $currentPrice);
        $discountPercent = $originalPrice > 0 && $discountAmount > 0
          ? round(($discountAmount / $originalPrice) * 100)
          : 0;

        $stock = max(0, (int) ($variation->stock_quantity ?? 0));
        $maximumOrderQty = $configuredMaximumOrderQty > 0
          ? min($stock, $configuredMaximumOrderQty)
          : $stock;
        if ($product->sold_individually ?? false) {
          $maximumOrderQty = min($maximumOrderQty, 1);
        }

        return [
          'id' => $variation->id,
          'color' => $color !== null ? \App\Support\StorefrontLabels::color($color, $isAr) : '—',
          'size' => $size !== null ? (string) $size : '—',
          'other' => $otherAttributes ? implode(' · ', $otherAttributes) : '—',
          'original_price' => $originalPrice,
          'current_price' => $currentPrice,
          'discount_percent' => $discountPercent,
          'stock' => $stock,
          'minimum_order_qty' => $minimumOrderQty,
          'maximum_order_qty' => max(0, $maximumOrderQty),
        ];
      });
    @endphp
    <section class="debug-variation-widget" aria-label="{{ $isAr ? 'تفاصيل المتغيرات' : 'Variation details' }}">
      <div class="debug-variation-header">
        <div>
          <div class="debug-variation-kicker">{{ $isAr ? 'تفاصيل المنتج' : 'VARIATION DETAILS' }}</div>
          <h2>{{ $isAr ? 'كل الاختيارات المتاحة' : 'Available variations' }}</h2>
        </div>
        <span class="debug-variation-count">{{ $debugVariationRows->count() }} {{ $isAr ? 'متغير' : 'variations' }}</span>
      </div>
      <div class="debug-variation-scroll">
        <table class="debug-variation-table">
          <thead>
            <tr>
              <th>{{ $isAr ? 'اللون' : 'Color' }}</th>
              <th>{{ $isAr ? 'المقاس' : 'Size' }}</th>
              <th>{{ $isAr ? 'بيانات إضافية' : 'Other attributes' }}</th>
              <th>{{ $isAr ? 'السعر الأصلي' : 'Original price' }}</th>
              <th>{{ $isAr ? 'السعر الحالي' : 'Current price' }}</th>
              <th>{{ $isAr ? 'الخصم' : 'Discount' }}</th>
              <th>{{ $isAr ? 'الحد الأدنى' : 'Min / order' }}</th>
              <th>{{ $isAr ? 'الحد الأقصى' : 'Max / order' }}</th>
              <th>{{ $isAr ? 'المتاح' : 'Available' }}</th>
              <th>ID</th>
            </tr>
          </thead>
          <tbody>
            @foreach($debugVariationRows as $row)
              <tr class="{{ $row['stock'] > 0 ? '' : 'is-out-of-stock' }}">
                <td>{{ $row['color'] }}</td>
                <td>{{ $row['size'] }}</td>
                <td>{{ $row['other'] }}</td>
                <td>{{ number_format($row['original_price'], 2) }} EGP</td>
                <td class="debug-current-price">{{ number_format($row['current_price'], 2) }} EGP</td>
                <td>
                  @if($row['discount_percent'] > 0)
                    <span class="debug-discount-badge">-{{ $row['discount_percent'] }}%</span>
                  @else
                    —
                  @endif
                </td>
                <td>{{ number_format($row['minimum_order_qty']) }}</td>
                <td>{{ number_format($row['maximum_order_qty']) }}</td>
                <td>{{ number_format($row['stock']) }}</td>
                <td><code>{{ $row['id'] }}</code></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
    <style>
      .debug-variation-widget{max-width:1200px;margin:28px auto 0;padding:18px;background:#fff7ed;border:1px solid #fed7aa;border-radius:16px;box-shadow:0 5px 18px rgba(120,53,15,.06);}
      .debug-variation-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px;}
      .debug-variation-header h2{margin:3px 0 0;color:#7c2d12;font-size:18px;}
      .debug-variation-kicker{color:#c2410c;font-size:10px;font-weight:850;letter-spacing:.12em;}
      .debug-variation-count{padding:6px 10px;border-radius:999px;background:#ffedd5;color:#9a3412;font-size:12px;font-weight:800;white-space:nowrap;}
      .debug-variation-scroll{overflow-x:auto;border:1px solid #fed7aa;border-radius:11px;background:#fff;}
      .debug-variation-table{width:100%;min-width:980px;border-collapse:collapse;font-size:12px;color:#431407;}
      .debug-variation-table th,.debug-variation-table td{padding:10px 11px;border-bottom:1px solid #ffedd5;text-align:start;vertical-align:middle;white-space:nowrap;}
      .debug-variation-table th{background:#ffedd5;color:#7c2d12;font-size:11px;font-weight:850;}
      .debug-variation-table tbody tr:last-child td{border-bottom:0;}
      .debug-variation-table tbody tr.is-out-of-stock{background:#fff1f2;color:#9f1239;}
      .debug-current-price{font-weight:850;color:#166534;}
      .debug-discount-badge{display:inline-flex;padding:3px 6px;border-radius:6px;background:#dcfce7;color:#166534;font-weight:850;}
      .debug-variation-table code{font-size:11px;color:#7c2d12;}
      @media (max-width:700px){.debug-variation-widget{margin-top:20px;padding:12px;border-radius:12px}.debug-variation-header h2{font-size:15px}.debug-variation-table{min-width:920px;font-size:11px}.debug-variation-table th,.debug-variation-table td{padding:8px 9px}}
    </style>
  @endif

  {{-- ═══ REVIEWS SECTION --}}
  @php
    $totalReviews = $reviews->count();
    $avgRating    = $totalReviews ? round($reviews->avg('rating'), 1) : 0;
    $avatarColors = ['#e85d26','#3b82f6','#22c55e','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
  @endphp
  <div class="reviews-section" id="reviews">

    {{-- ── Overview ── --}}
    <div class="rv-overview">
      <div class="rv-score-box">
        <div class="rv-big-num">{{ $totalReviews ? $avgRating : '—' }}</div>
        <div class="rv-big-stars">
          @for($s=1;$s<=5;$s++)
            <span style="color:{{ $s <= round($avgRating) ? '#f5a623' : '#e0e0e0' }}">★</span>
          @endfor
        </div>
        <div class="rv-total-label">{{ $totalReviews }} {{ $isAr ? 'تقييم' : 'review'.($totalReviews!=1?'s':'') }}</div>
      </div>

      <div class="rv-distribution">
        @foreach([5,4,3,2,1] as $star)
          @php $cnt = $distribution->get($star)?->cnt ?? 0; $pct = $totalReviews ? round($cnt / $totalReviews * 100) : 0; @endphp
          <div class="dist-row">
            <span class="dist-label">{{ $star }} ★</span>
            <div class="dist-bar-wrap"><div class="dist-bar-fill" style="width:{{ $pct }}%"></div></div>
            <span class="dist-num">{{ $cnt }}</span>
          </div>
        @endforeach
      </div>

      @auth
        @if(!$userReviewed)
        <button class="rv-write-btn" onclick="document.getElementById('review-form-wrap').scrollIntoView({behavior:'smooth'})">
          {{ $isAr ? 'اكتب تقييم' : 'Write a Review' }}
        </button>
        @else
        <div class="rv-wrote-badge">✓ {{ $isAr ? 'إنت قيّمت المنتج ده' : 'You reviewed this product' }}</div>
        @endif
      @else
        <a href="{{ route('login') }}" class="rv-write-btn">{{ $isAr ? 'سجّل دخول عشان تقيّم' : 'Sign in to Review' }}</a>
      @endauth
    </div>

    {{-- Flash messages --}}
    @if(session('success'))<div class="rv-flash rv-flash-ok">✓ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="rv-flash rv-flash-err">⚠ {{ session('error') }}</div>@endif

    {{-- ── Sort bar ── --}}
    @if($totalReviews > 0)
    <div class="rv-toolbar">
      <span class="rv-toolbar-count">{{ $totalReviews }} {{ $isAr ? 'تقييم' : 'Review'.($totalReviews!=1?'s':'') }}</span>
      <select class="rv-sort-select" onchange="sortReviews(this.value)">
        <option value="newest">{{ $isAr ? 'الأحدث الأول' : 'Newest First' }}</option>
        <option value="highest">{{ $isAr ? 'الأعلى تقييمًا' : 'Highest Rated' }}</option>
        <option value="lowest">{{ $isAr ? 'الأقل تقييمًا' : 'Lowest Rated' }}</option>
        <option value="helpful">{{ $isAr ? 'الأكثر فائدة' : 'Most Helpful' }}</option>
      </select>
    </div>
    @endif

    {{-- ── Review cards ── --}}
    <div id="review-list">
      @forelse($reviews as $review)
      @php
        $initial   = strtoupper(substr($review->reviewer_name ?? 'C', 0, 1));
        $avatarBg  = $avatarColors[ord(strtolower($initial)) % count($avatarColors)];
        $isOwn     = Auth::check() && Auth::id() === (int)$review->user_id;
        $alreadyHelpful = in_array($review->id, $helpfulVoted ?? []);
      @endphp
      <div class="rv-card" data-rating="{{ $review->rating }}" data-helpful="{{ $review->helpful_count }}" data-ts="{{ strtotime($review->created_at) }}">
        <div class="rv-card-head">
          <div class="rv-avatar" style="background:{{ $avatarBg }}">{{ $initial }}</div>
          <div class="rv-card-meta">
            <div class="rv-name-row">
              <span class="rv-name">{{ $review->reviewer_name }}</span>
              @if($review->is_verified_purchase)
                <span class="rv-verified">✓ {{ $isAr ? 'شراء مؤكد' : 'Verified Purchase' }}</span>
              @endif
              @if($isOwn)
                <span class="rv-own-badge">{{ $isAr ? 'تقييمك' : 'Your review' }}</span>
              @endif
            </div>
            <div class="rv-date">{{ $isAr ? \Carbon\Carbon::parse($review->created_at)->locale('ar')->translatedFormat('j F Y') : \Carbon\Carbon::parse($review->created_at)->format('M d, Y') }}</div>
          </div>
          <div class="rv-card-stars">
            @for($s=1;$s<=5;$s++)<span style="color:{{ $s<=$review->rating?'#f5a623':'#e0e0e0' }}">★</span>@endfor
          </div>
        </div>

        @if($review->title)
          <div class="rv-review-title">{{ $review->title }}</div>
        @endif
        <div class="rv-review-body">{{ $review->body }}</div>

        <div class="rv-card-foot">
          <button class="rv-helpful {{ $alreadyHelpful ? 'voted' : '' }}"
                  onclick="markHelpful(this, {{ $review->id }})"
                  {{ $alreadyHelpful ? 'disabled' : '' }}>
            👍 {{ $isAr ? 'مفيد' : 'Helpful' }} <span class="rv-helpful-cnt">({{ $review->helpful_count ?: 0 }})</span>
          </button>
          @if($isOwn)
            <button class="rv-delete" onclick="deleteReview(this, {{ $review->id }}, {{ $product->id }})">{{ $isAr ? 'حذف' : 'Delete' }}</button>
          @endif
        </div>
      </div>
      @empty
        <div class="rv-empty">
          <div style="font-size:48px;margin-bottom:12px">✍️</div>
          <p>{{ $isAr ? 'لسه مفيش تقييمات — كن أول واحد يشارك رأيه!' : 'No reviews yet — be the first to share your thoughts!' }}</p>
        </div>
      @endforelse
    </div>

    {{-- ── Write Review Form ── --}}
    <div id="review-form-wrap" class="{{ $userReviewed ? 'hidden' : '' }}">
      @auth
        @if(!$userReviewed)
        <div class="rv-form-card">
          <h3 class="rv-form-title">{{ $isAr ? 'اكتب تقييم' : 'Write a Review' }}</h3>
          <p class="rv-form-sub">{{ $isAr ? 'شاركنا تجربتك الحقيقية مع المنتج ده' : 'Share your honest experience with this product' }}</p>
          <form method="POST" action="{{ route('review.store') }}" id="review-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            {{-- Star Picker --}}
            <div class="rv-form-row">
              <label class="rv-form-label">{{ $isAr ? 'تقييمك' : 'Your Rating' }} *</label>
              <div class="rv-star-picker" id="star-picker">
                @for($s=1;$s<=5;$s++)
                  <span class="rv-star" data-val="{{ $s }}"
                        onmouseenter="hoverStar({{ $s }})"
                        onmouseleave="resetStarHover()"
                        onclick="setRating({{ $s }})">★</span>
                @endfor
              </div>
              <input type="hidden" name="rating" id="rating-input" value="">
              <span class="rv-star-label" id="star-label">{{ $isAr ? 'اختار تقييم' : 'Click to rate' }}</span>
              @error('rating')<span class="rv-field-err">{{ $message }}</span>@enderror
            </div>

            {{-- Title --}}
            <div class="rv-form-row">
              <label class="rv-form-label">{{ $isAr ? 'عنوان التقييم' : 'Review Title' }}</label>
              <input type="text" name="title" class="rv-input" maxlength="150"
                     placeholder="{{ $isAr ? 'لخّص تجربتك في سطر' : 'Sum up your experience in one line' }}"
                     value="{{ old('title') }}">
              @error('title')<span class="rv-field-err">{{ $message }}</span>@enderror
            </div>

            {{-- Body --}}
            <div class="rv-form-row">
              <label class="rv-form-label">{{ $isAr ? 'تقييمك' : 'Your Review' }} *</label>
              <textarea name="body" class="rv-textarea" rows="5" id="review-body"
                        maxlength="1000" required
                        placeholder="{{ $isAr ? 'إيه رأيك؟ الجودة والمقاس والسعر…' : 'What did you think? Quality, fit, value for money…' }}">{{ old('body') }}</textarea>
              <div class="rv-char-counter"><span id="char-count">0</span> / 1000</div>
              @error('body')<span class="rv-field-err">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="rv-submit" id="rv-submit-btn" onclick="return validateReviewForm()">
              {{ $isAr ? 'انشر التقييم' : 'Publish Review' }}
            </button>
          </form>
        </div>
        @endif
      @else
        <div class="rv-signin-prompt">
          <div style="font-size:36px;margin-bottom:12px">⭐</div>
          <p>{{ $isAr ? 'عندك المنتج ده؟ شاركنا تجربتك!' : 'Have this product? Share your experience!' }}</p>
          <a href="{{ route('login') }}" class="rv-write-btn" style="display:inline-flex;margin-top:16px">{{ $isAr ? 'سجّل دخول عشان تكتب تقييم' : 'Sign in to Write a Review' }}</a>
        </div>
      @endauth
    </div>

  </div>

  {{-- VENDOR BANNER + MORE FROM THIS VENDOR --}}
  @if($vendor)
  <div class="vendor-section" style="margin-top:56px">
    <div class="vendor-banner-card">
      <div class="vendor-banner-left">
        @if($vendor->logo_url)
          <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->shop_name }}" class="vendor-banner-logo">
        @else
          <div class="vendor-banner-logo-ph">🏪</div>
        @endif
        <div class="vendor-banner-info">
          <div class="vendor-banner-name">{{ $vendor->shop_name }}</div>
          <div class="vendor-banner-meta">
            @if((float)$vendor->rating > 0)
              <span style="color:#f5a623">★</span> {{ number_format((float)$vendor->rating,1) }} · 
            @endif
            {{ $vendor->shop_address }}
          </div>
        </div>
      </div>
      <a href="{{ route('vendor.store', $vendor->id) }}" class="vendor-banner-btn">{{ $isAr ? 'شوف المتجر ←' : 'Visit Store →' }}</a>
    </div>

    @if($vendorProducts->count())
    <div class="sec-head" style="margin-top:28px;margin-bottom:16px">
      <h2 class="sec-title">{{ $isAr ? 'منتجات تانية من' : 'More from' }} {{ Str::limit($vendor->shop_name, 24) }}</h2>
      <a href="{{ route('vendor.store', $vendor->id) }}" class="sec-link">{{ $isAr ? 'شوف أكتر ←' : 'See all →' }}</a>
    </div>
    <div class="tl-scroll-section" style="margin-bottom:8px">
      <div class="tl-scroll-track">
        @foreach($vendorProducts as $p)
        <div class="tl-scroll-card">
          @include('web.partials.product-card', [
            'p'              => $p,
            'cardVariations' => [],
            'cardOptions'    => ['compact' => true, 'nameLimit' => 28, 'showWishlist' => false, 'showAddToCart' => false, 'showDetails' => false, 'showCoupon' => false],
          ])
        </div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
  @endif

  {{-- RELATED --}}
  @if($related->count())
  <div class="related-products-section" style="margin-top:64px">
    <div class="sec-head related-products-head">
      <h2 class="sec-title">{{ $isAr ? 'ممكن يعجبك كمان' : 'You may also like' }}</h2>
      <a href="{{ route('shop') }}" class="sec-link">{{ $isAr ? 'شوف أكتر ←' : 'See all →' }}</a>
    </div>
    <div class="product-grid cols-4 related-products-grid">
      @foreach($related as $p)
        @include('web.partials.product-card', ['p' => $p, 'cardVariations' => []])
      @endforeach
    </div>
  </div>
  @endif

{{-- ── Sticky Add-to-Cart bar ── --}}
<div id="sticky-atc-bar" class="sticky-atc-bar" aria-hidden="true">
  <div class="sticky-atc-inner">
    <div class="sticky-atc-info">
      @if($product->thumbnail_url)
        <img src="{{ $product->thumbnail_url }}" alt="" class="sticky-atc-thumb" onerror="this.style.display='none'">
      @endif
      <div class="sticky-atc-meta">
        <div class="sticky-atc-name">{{ Str::limit($displayProductName, 48) }}</div>
        <div class="sticky-atc-price" id="sticky-price">{{ number_format($product->display_price, 2) }} EGP</div>
      </div>
    </div>
    <button class="sticky-atc-btn"
            onclick="handleAddToCart({{ $product->id }}, '{{ addslashes($displayProductName) }}', {{ $product->display_price }}, '{{ $product->thumbnail_url }}')">
      {{ $isAr ? 'ضيف للسلة' : 'Add to Cart' }}
    </button>
  </div>
</div>

</div>
@endsection

@push('scripts')
<style>
/* ── Sticky ATC bar ── */
.sticky-atc-bar {
  position: fixed; bottom: 0; left: 0; right: 0; z-index: 999;
  background: #fff; border-top: 1px solid #e8e8e4;
  box-shadow: 0 -4px 20px rgba(0,0,0,.10);
  transform: translateY(100%);
  transition: transform .3s cubic-bezier(.4,0,.2,1);
  will-change: transform;
}
.sticky-atc-bar.visible { transform: translateY(0); }
.sticky-atc-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between;
  gap: 16px; padding: 12px 24px;
}
.sticky-atc-info { display: flex; align-items: center; gap: 12px; min-width: 0; }
.sticky-atc-thumb {
  width: 48px; height: 48px; border-radius: 8px;
  object-fit: contain; background: #f5f5f3; flex-shrink: 0;
}
.sticky-atc-meta { min-width: 0; }
.sticky-atc-name {
  font-size: 14px; font-weight: 700; color: #1a1a1a;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sticky-atc-price { font-size: 14px; color: #555; margin-top: 2px; }
.sticky-atc-btn {
  flex-shrink: 0;
  background: #1a1a1a; color: #fff;
  border: none; border-radius: 100px;
  padding: 12px 32px; font-size: 15px; font-weight: 700;
  cursor: pointer; transition: background .18s, transform .15s;
  white-space: nowrap;
}
.sticky-atc-btn:hover { background: #333; transform: scale(1.02); }
@media(max-width:768px){
  /* Stay directly above the mobile navigation, including Android viewport changes. */
  .sticky-atc-bar { bottom:calc(var(--mobile-nav-height, 58px) + var(--mobile-nav-viewport-offset, 0px)); border-radius: 12px 12px 0 0; }
}
@media(max-width:600px){
  .sticky-atc-inner { padding: 10px 16px; }
  .sticky-atc-thumb { display: none; }
  .sticky-atc-name { font-size: 13px; }
  .sticky-atc-btn { padding: 11px 20px; font-size: 14px; }
}

/* Gallery image switching */
#main-img { transition: opacity 0.18s ease; }
#main-img.img-switching { opacity: 0; }
#gallery-thumbs:empty { display: none; }

/* ── Image placeholder ── */
.img-placeholder-box {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  background: #f5f5f3; border-radius: 12px; gap: 8px; min-height: 260px;
}
.img-placeholder-icon { font-size: 56px; opacity: .45; }
.img-placeholder-text { font-size: 13px; color: #aaa; font-weight: 500; }

/* ── Product image sections ── */
.product-image-sections { margin-top: 20px; display: flex; flex-direction: column; gap: 20px; }

.img-section { border: 1px solid #eee; border-radius: 14px; overflow: hidden; background: #fff; }

.img-section-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px; background: #fafaf8; border-bottom: 1px solid #eee;
}
.img-section-label { font-size: 13px; font-weight: 700; color: #333; }
.img-section-count { font-size: 12px; color: #aaa; }

.img-section-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
  gap: 8px; padding: 12px;
}
.img-section-item {
  aspect-ratio: 1; border-radius: 10px; overflow: hidden;
  background: #f5f5f3; cursor: pointer; border: 2px solid transparent;
  transition: border-color .18s, transform .15s; position: relative;
}
.img-section-item:hover { border-color: var(--c-orange, #e85d26); transform: scale(1.04); }

.img-section-item img {
  width: 100%; height: 100%; object-fit: cover; display: block;
}
.img-section-item .section-img-placeholder {
  width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
  font-size: 28px; color: #ccc;
}

/* ── Thumb error placeholder ── */
.gallery-thumb.img-error { background: #f5f5f3; }
.gallery-thumb.img-error img { display: none !important; }
.gallery-thumb.img-error::after {
  content: '🖼️'; font-size: 22px; display: flex;
  align-items: center; justify-content: center;
  width: 100%; height: 100%; opacity: .4;
}

.var-options { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-start; }
.var-color-option {
  display:flex; flex-direction:column; align-items:center; gap:8px;
  cursor:pointer;
  flex:0 0 112px; min-width:112px; padding:10px 8px 9px;
  border:1px solid #ebe7e2; border-radius:14px; background:#fff;
  box-shadow:0 2px 8px rgba(28,25,23,.04);
  transition:border-color .18s, box-shadow .18s, transform .18s;
}
.var-color-option:has(.var-swatch.selected) {
  border-color:var(--c-orange, #e85d26);
  box-shadow:0 4px 14px rgba(232,93,38,.15);
  transform:translateY(-1px);
}
.var-swatch {
  width:46px; height:46px; border:3px solid #fff; border-radius:50%;
  outline:1px solid #d8d4cf; cursor:pointer; box-shadow:0 2px 5px rgba(0,0,0,.12);
  transition:transform .18s, outline-color .18s, box-shadow .18s;
}
.var-swatch:hover { transform:scale(1.06); }
.var-color-option:focus-visible { outline:3px solid var(--c-orange, #e85d26); outline-offset:3px; }
.color-qty-stepper { cursor:default; }
.var-swatch.selected { outline:3px solid #171717; outline-offset:2px; box-shadow:0 3px 8px rgba(0,0,0,.2); }
.var-swatch[disabled] { cursor:not-allowed; opacity:.35; filter:grayscale(1); }
.var-swatch.out-of-stock { opacity:.35; filter:grayscale(1); }
.color-qty-stepper { display:grid; grid-template-columns:28px 1fr 28px; align-items:center; gap:4px; width:100%; min-height:32px; }
.color-qty-stepper button {
  width:28px; height:30px; border:1px solid #dedad5; border-radius:8px;
  background:#faf9f7; color:#252525; cursor:pointer; font-size:16px; font-weight:700; line-height:1;
}
.color-qty-stepper button:hover:not(:disabled) { border-color:var(--c-orange, #e85d26); color:var(--c-orange, #e85d26); }
.color-qty-stepper button:disabled { opacity:.4; cursor:not-allowed; }
.color-qty-stepper input {
  width:100%; height:30px; border:1px solid #dedad5; border-radius:8px;
  text-align:center; font-size:13px; font-weight:700; padding:0; color:#252525; background:#fff;
}
.color-qty-hint { display:none; }
.var-hint { min-height:18px; margin-top:8px; font-size:12px; color:var(--c-mid, #78716c); }
@media (max-width:680px) {
  .var-options { gap:9px; }
  .var-color-option { flex-basis:100px; min-width:100px; padding:8px 6px; }
  .var-swatch { width:42px; height:42px; }
}

/* Narrow-phone selector: keep the variation choices compact without hiding controls. */
@media (max-width:520px) {
  .pi-variations-wrap { margin:12px 0 12px; gap:10px; }
  .pi-var-group { padding-bottom:9px; }
  .pi-var-group .var-label { margin-bottom:7px; font-size:12px; }
  .var-options { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:7px; margin-bottom:10px; }
  .var-color-option {
    min-width:0; flex:initial; padding:6px 5px 5px; gap:4px; border-radius:11px;
    box-shadow:0 1px 5px rgba(28,25,23,.045);
  }
  .var-color-option:has(.var-swatch.selected) { transform:none; box-shadow:0 2px 8px rgba(232,93,38,.13); }
  .var-swatch { width:34px; height:34px; border-width:2px; outline-offset:2px; }
  .var-swatch.selected { outline-width:2px; }
  .var-color-name { font-size:10px; line-height:1.15; }
  .color-qty-stepper { grid-template-columns:24px 1fr 24px; gap:3px; min-height:27px; }
  .color-qty-stepper button { width:24px; height:27px; border-radius:7px; font-size:14px; }
  .color-qty-stepper input { height:27px; border-radius:7px; font-size:12px; }
  .pi-var-group .var-btn { min-width:40px; height:32px; padding:0 10px; border-radius:8px; font-size:12px; }
  .var-hint { min-height:14px; margin-top:5px; font-size:10px; }
}

/* Color swatch CSS variables */

:root {
  --swatch-white:#f5f5f5; --swatch-black:#1a1a1a; --swatch-green:#22a35c;
  --swatch-red:#e53e3e; --swatch-blue:#3182ce; --swatch-yellow:#f6e05e;
  --swatch-gray:#a0aec0; --swatch-grey:#a0aec0; --swatch-navy:#1a365d;
  --swatch-pink:#ed64a6; --swatch-orange:#ed8936; --swatch-purple:#805ad5;
  --swatch-brown:#c05621; --swatch-beige:#f5e6cc; --swatch-cream:#fffdd0;
  --swatch-maroon:#800000; --swatch-gold:#d4af37; --swatch-silver:#c0c0c0;
  --swatch-teal:#319795; --swatch-indigo:#5a67d8; --swatch-cyan:#00b5d8;
  --swatch-lime:#68d391; --swatch-rose:#fc8181;
}

/* Arabic detail-page direction and alignment without affecting the English view. */
.product-page-ar { font-family: 'Cairo', 'Tahoma', sans-serif; }
.product-page-ar .product-info,
.product-page-ar .desc-block,
.product-page-ar .reviews-section,
.product-page-ar .vendor-section { text-align: right; }
.product-page-ar .gallery-thumbs { direction: rtl; }
.product-page-ar .rv-card-head,
.product-page-ar .rv-card-foot,
.product-page-ar .sticky-atc-inner { direction: rtl; }
.product-page-ar .rv-card-stars { margin-right: auto; margin-left: 0; }
.product-page-ar .pi-cart-row,
.product-page-ar .pi-coupon-row { direction: rtl; }
@media (max-width: 680px) {
  .product-page-ar .product-layout { direction: rtl; }
  .product-page-ar .sticky-atc-inner { padding-right: 14px; padding-left: 14px; }
}

/* ── Narrow-phone product gallery polish ── */
@media (max-width: 600px) {
  .product-page .product-layout {
    gap:18px;
  }
  .product-page .gallery-wrap {
    width:100%;
    gap:10px;
  }
  .product-page .gallery-main {
    width:100%;
    aspect-ratio:1 / 1;
    border:1px solid #e8e8e4;
    border-radius:18px;
    background:#fafaf8;
    box-shadow:0 5px 18px rgba(25,25,25,.06);
  }
  .product-page .gallery-main img {
    display:block;
    width:100%;
    height:100%;
    padding:8px;
    object-fit:contain;
    background:#fafaf8;
  }
  .product-page #gallery-thumbs:not(:has(.gallery-thumb:nth-child(2))) {
    display:none !important;
  }
  .product-page .gallery-thumbs {
    width:100%;
    justify-content:flex-start;
    gap:8px;
    padding:2px 2px 5px;
    scroll-snap-type:x proximity;
  }
  .product-page .gallery-thumb {
    width:58px;
    height:58px;
    border:1.5px solid #e1e1de;
    border-radius:10px;
    scroll-snap-align:start;
    background:#fafaf8;
  }
  .product-page .gallery-thumb.active {
    border-color:#e85d26;
    box-shadow:0 0 0 2px rgba(232,93,38,.14);
  }
  .product-page .gallery-thumb img,
  .product-page .gallery-thumb:hover img,
  .product-page .gallery-thumb.active img {
    filter:none;
  }
  .product-page .pi-title-row {
    align-items:flex-start;
    gap:10px;
    margin-bottom:12px;
    padding-top:2px;
  }
  .product-page .pi-title {
    font-size:20px;
    line-height:1.28;
  }
  .product-page .pi-wish-btn {
    width:40px;
    height:40px;
    margin-top:0;
    border:1px solid #e7e2dd;
    border-radius:12px;
    background:#fff8f3;
    color:#e85d26;
    box-shadow:0 3px 10px rgba(232,93,38,.10);
    font-family:Arial,"Segoe UI Symbol",sans-serif;
    font-size:23px;
    font-weight:700;
    line-height:1;
    font-variant-emoji:text;
  }
  .product-page .pi-wish-btn:hover,
  .product-page .pi-wish-btn:focus-visible {
    border-color:#e85d26;
    color:#e85d26;
    background:#fff3eb;
    transform:none;
  }
  .product-page .pi-wish-btn.wished {
    background:#e85d26;
    border-color:#e85d26;
    color:#fff;
  }
}
/* ── Suggested products mobile card polish ── */
.related-products-section {
  border-top:1px solid #ece9e5;
  padding-top:24px;
}
.related-products-head { margin-bottom:16px !important; }
@media (max-width:600px) {
  .product-page .related-products-section {
    margin-top:36px !important;
    padding-top:18px;
  }
  .product-page .related-products-head {
    align-items:center;
    margin-bottom:12px !important;
  }
  .product-page .related-products-head .sec-title {
    font-size:18px;
    line-height:1.25;
  }
  .product-page .related-products-head .sec-link {
    font-size:11px;
    white-space:nowrap;
  }
  .product-page .related-products-grid {
    grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    gap:10px !important;
  }
  .product-page .related-products-grid .product-card {
    min-width:0;
    min-height:0 !important;
    overflow:hidden;
    border:1px solid #ececec;
    border-radius:14px;
    background:#fff;
    box-shadow:0 4px 16px rgba(25,25,25,.06);
  }
  .product-page .related-products-grid .product-card-img {
    width:100%;
    height:auto !important;
    aspect-ratio:1 / 1;
    border-radius:0;
    background:#f7f7f7;
  }
  .product-page .related-products-grid .product-card-body {
    display:flex;
    flex-direction:column;
    align-items:stretch;
    min-width:0;
    padding:8px 7px 9px !important;
    text-align:center;
  }
  .product-page .related-products-grid .product-card-name {
    display:-webkit-box;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:2;
    min-height:30px;
    margin:0;
    overflow:hidden;
    color:#202020;
    font-size:11px;
    font-weight:700;
    line-height:1.35;
    text-align:center;
  }
  .product-page .related-products-grid .pc-swatches,
  .product-page .related-products-grid .pc-sizes {
    justify-content:center;
    margin:5px 0 0;
    gap:4px;
  }
  .product-page .related-products-grid .pc-swatches:empty,
  .product-page .related-products-grid .pc-sizes:empty { display:none; }
  .product-page .related-products-grid .pc-swatch {
    width:15px;
    height:15px;
    border-width:2px;
  }
  .product-page .related-products-grid .pc-size {
    min-width:21px;
    padding:3px 5px;
    border-radius:6px;
    font-size:9px;
    line-height:1;
  }
  .product-page .related-products-grid .pc-selected {
    min-height:13px;
    max-height:13px;
    margin:3px 0 0;
    overflow:hidden;
    color:#8b8b8b;
    font-size:9px;
    line-height:1.4;
    text-align:center;
    text-overflow:ellipsis;
    white-space:nowrap;
  }
  .product-page .related-products-grid .product-card-price {
    display:flex;
    align-items:baseline;
    justify-content:center;
    min-height:21px;
    margin:6px 0 0;
    gap:4px;
    line-height:1;
  }
  .product-page .related-products-grid .price-main {
    font-size:13px;
    font-weight:800;
    white-space:nowrap;
  }
  .product-page .related-products-grid .price-main.sale { color:var(--c-orange); }
  .product-page .related-products-grid .price-old {
    color:#9a9a9a;
    font-size:9px;
    white-space:nowrap;
  }
  .product-page .related-products-grid .pc-actions {
    display:block;
    margin:7px 0 0;
  }
  .product-page .related-products-grid .card-add-btn {
    width:100%;
    min-height:30px;
    margin:0;
    padding:7px 4px;
    border:0;
    border-radius:8px;
    background:var(--c-dark);
    color:#fff;
    font-size:10px;
    font-weight:800;
    line-height:1;
    white-space:nowrap;
  }
  .product-page .related-products-grid .card-details-btn {
    display:block;
    width:100%;
    margin:5px 0 0;
    padding:2px 0 0;
    border:0;
    background:transparent;
    color:#777;
    font-size:9px;
    font-weight:700;
    line-height:1.2;
    text-align:center;
    text-decoration:none;
  }
  .product-page .related-products-grid .wish-btn {
    top:7px;
    right:7px;
    width:26px;
    height:26px;
    border:1px solid rgba(0,0,0,.08);
    background:rgba(255,255,255,.94);
    font-size:14px;
  }
  .product-page.product-page-ar .related-products-grid .wish-btn {
    right:7px;
    left:auto;
  }
  .product-page .related-products-grid .pc-coupon-bar { display:none; }
}
</style>
<script>
// ── Variation Engine ──────────────────────────────────────────────────
const VAR_DATA  = @json($varData);
const DISC_PCT  = {{ (float)($product->discount_percentage ?? 0) }};
const MIN_ORDER_QTY = {{ $minimumOrderQty }};
const CONFIGURED_MAX_ORDER_QTY = {{ $configuredMaximumOrderQty }};
const SOLD_INDIVIDUALLY = {{ ($product->sold_individually ?? false) ? 'true' : 'false' }};
const PRODUCT_STOCK_QTY = {{ $initialStockQty }};
const PRODUCT_TEXT = @json($productText);
const ATTR_KEYS = [...new Set(VAR_DATA.flatMap(v => Object.keys(v.attrs)))];
const INITIAL_GALLERY_HTML = document.getElementById('gallery-thumbs')?.innerHTML || '';
let productCartRequestPending = false;
let selectedAttrs = {};
let selectedColorValues = new Set();
let colorQuantities = {};
let currentVariation = null;
let _lockedImgUrl = null;
const COLOR_ATTR_KEY = ATTR_KEYS.find(k => k.toLowerCase() === 'color') || null;

function colorQuantityKey(value) {
  const selected = Object.keys(selectedAttrs).sort().map(key => [key, selectedAttrs[key]]);
  return JSON.stringify([selected, [COLOR_ATTR_KEY || 'color', value]]);
}

// Returns valid values for `key` considering only selections from keys that come
// before `key` in ATTR_KEYS order. This ensures e.g. Color is never hidden by a
// selected Size, but Size IS filtered by the selected Color.
function validValuesFor(key) {
  const keyIndex = ATTR_KEYS.indexOf(key);
  const precedingSelected = Object.fromEntries(
    Object.entries(selectedAttrs).filter(([k]) => ATTR_KEYS.indexOf(k) < keyIndex)
  );
  return new Set(
    VAR_DATA
      .filter(v => Object.entries(precedingSelected).every(([k, sv]) => v.attrs[k] === sv))
      .filter(v => {
        if (!COLOR_ATTR_KEY || key.toLowerCase() === 'color' || selectedColorValues.size === 0) return true;
        return selectedColorValues.has(v.attrs[COLOR_ATTR_KEY]);
      })
      .map(v => v.attrs[key])
      .filter(v => v !== undefined)
  );
}

function variationForColor(colorValue) {
  return VAR_DATA.find(v => {
    if (COLOR_ATTR_KEY && v.attrs[COLOR_ATTR_KEY] !== colorValue) return false;
    return Object.entries(selectedAttrs).every(([k, value]) => v.attrs[k] === value);
  }) || null;
}

// A color remains selectable when it has at least one orderable variation,
// even if the currently selected non-color attribute (for example, Size=XL)
// is not available for that color. The selection handler can then clear or
// update the incompatible attribute after the color is chosen.
function orderableVariationForColor(colorValue) {
  return VAR_DATA.find(v => {
    if (COLOR_ATTR_KEY && v.attrs[COLOR_ATTR_KEY] !== colorValue) return false;
    return Number(v.stock) > 0 && maximumOrderQuantity(v.stock) >= MIN_ORDER_QTY;
  }) || null;
}

function selectedColorVariations() {
  return [...selectedColorValues].map(value => ({ value, variation: variationForColor(value) }));
}

function updateAvailability() {
  ATTR_KEYS.forEach(key => {
    const valid = validValuesFor(key);
    document.querySelectorAll(`[data-attr-key="${key}"]`).forEach(btn => {
      const isValid = valid.has(btn.dataset.attrVal);
      btn.style.display = isValid ? '' : 'none';
      if (key.toLowerCase() === 'color') {
        const variation = orderableVariationForColor(btn.dataset.attrVal);
        const outOfStock = !variation;
        // Keep an unavailable color tappable so the customer gets a clear
        // availability message instead of a silent disabled control.
        btn.disabled = !isValid;
        btn.setAttribute('aria-disabled', outOfStock ? 'true' : 'false');
        btn.classList.toggle('out-of-stock', outOfStock);
      }
    });

    // Keep the existing single-select convenience for non-color attributes only.
    if (key.toLowerCase() !== 'color' && selectedAttrs[key] === undefined && valid.size === 1) {
      const onlyVal = [...valid][0];
      const onlyBtn = document.querySelector(`[data-attr-key="${key}"][data-attr-val="${onlyVal}"]`);
      if (onlyBtn) {
        selectedAttrs[key] = onlyVal;
        onlyBtn.classList.add('selected');
      }
    }
  });
  updateColorSteppers();
}

// Auto-select the main variation only when it can actually be ordered.
// If it is unavailable, prefer the first orderable variation instead.
(function () {
  const isOrderable = (variation) => variation
    && Number(variation.stock) > 0
    && maximumOrderQuantity(variation.stock) >= MIN_ORDER_QTY;
  const preferred = VAR_DATA.find(v => v.main && isOrderable(v))
    || VAR_DATA.find(v => isOrderable(v));
  const main = preferred || VAR_DATA.find(v => v.main) || VAR_DATA[0] || null;

  if (main) {
    // Seed non-color attributes first so initial color quantities are keyed to
    // the complete selected variation (for example, Size + Color).
    if (preferred) {
      Object.entries(main.attrs).forEach(([k, val]) => {
        if (k.toLowerCase() === 'color') return;
        const btn = document.querySelector(`[data-attr-key="${k}"][data-attr-val="${val}"]`);
        if (!btn) return;
        selectedAttrs[k] = val;
        btn.classList.add('selected');
      });
      Object.entries(main.attrs).forEach(([k, val]) => {
        if (k.toLowerCase() !== 'color') return;
        const btn = document.querySelector(`[data-attr-key="${k}"][data-attr-val="${val}"]`);
        if (!btn) return;
        selectedColorValues.add(val);
        const quantityKey = colorQuantityKey(val);
        if (!(quantityKey in colorQuantities)) colorQuantities[quantityKey] = MIN_ORDER_QTY;
        btn.classList.add('selected');
      });
    }

    // Keep the preferred/main variation image on load without selecting an
    // unavailable variation.
    if (main.images && main.images.length > 0) {
      _lockedImgUrl = main.images[0];
      const img = document.getElementById('main-img');
      if (img) img.dataset.originalSrc = img.src;
      setMainImg(main.images[0]);
      const colorKey = Object.keys(main.attrs).find(k => k.toLowerCase() === 'color');
      if (colorKey) updateVariationThumbs(colorKey, main.attrs[colorKey]);
    }
    currentVariation = preferred ? main : null;
    renderPriceStock(preferred ? main : null);
    updateSelectedLabels();
  }
  updateAvailability();
  tryFindVariation();
})();

function selectColorCard(event, card) {
  if (event && event.type === 'click' && (event.target.closest('.var-swatch') || event.target.closest('.color-qty-stepper'))) return;
  const swatch = card?.querySelector('.var-swatch');
  if (!swatch || swatch.disabled || swatch.classList.contains('out-of-stock')) return;
  selectAttr(swatch.dataset.attrKey, swatch.dataset.attrVal, swatch);
}

function selectAttr(key, value, btn) {
  if (key.toLowerCase() === 'color') {
    if (selectedColorValues.has(value)) {
      selectedColorValues.delete(value);
      delete colorQuantities[colorQuantityKey(value)];
      btn.classList.remove('selected');
    } else {
      const variation = orderableVariationForColor(value);
      if (!variation) {
        showQuantityError(PRODUCT_TEXT.variationComingSoon);
        return;
      }
      selectedColorValues.add(value);
      const quantityKey = colorQuantityKey(value);
      if (!(quantityKey in colorQuantities)) colorQuantities[quantityKey] = MIN_ORDER_QTY;
      btn.classList.add('selected');
      const colorImg = getColorImage(key, value);
      if (colorImg) {
        _lockedImgUrl = colorImg;
        const img = document.getElementById('main-img');
        if (img && !img.dataset.originalSrc) img.dataset.originalSrc = img.src;
        setMainImg(colorImg, true);
        updateVariationThumbs(key, value);
      }
    }

    // A non-color choice must remain valid for every selected color. Clear it if not.
    ATTR_KEYS.filter(otherKey => otherKey.toLowerCase() !== 'color').forEach(otherKey => {
      const current = selectedAttrs[otherKey];
      if (current !== undefined && selectedColorValues.size > 0 && !selectedColorVariations().every(({ variation }) => variation && variation.attrs[otherKey] === current)) {
        delete selectedAttrs[otherKey];
        document.querySelectorAll(`[data-attr-key="${otherKey}"]`).forEach(b => b.classList.remove('selected'));
      }
    });
  } else {
    if (selectedAttrs[key] === value) {
      delete selectedAttrs[key];
      btn.classList.remove('selected');
    } else {
      document.querySelectorAll(`[data-attr-key="${key}"]`).forEach(b => b.classList.remove('selected'));
      selectedAttrs[key] = value;
      btn.classList.add('selected');
    }
  }

  updateAvailability();
  tryFindVariation();
  updateSelectedLabels();
  updateSelectedSummary();
  if (currentVariation && maximumOrderQuantity(currentVariation.stock) < MIN_ORDER_QTY) {
    showQuantityError(PRODUCT_TEXT.variationComingSoon);
  }
}

function tryFindVariation() {
  const nonColorKeys = ATTR_KEYS.filter(k => k.toLowerCase() !== 'color');
  const nonColorComplete = nonColorKeys.every(k => selectedAttrs[k] !== undefined);
  if (COLOR_ATTR_KEY && selectedColorValues.size === 1 && nonColorComplete) {
    currentVariation = selectedColorVariations()[0]?.variation || null;
  } else if (!COLOR_ATTR_KEY && ATTR_KEYS.every(k => selectedAttrs[k] !== undefined)) {
    currentVariation = VAR_DATA.find(v => Object.entries(selectedAttrs).every(([k, sv]) => v.attrs[k] === sv)) || null;
  } else {
    currentVariation = null;
  }
  renderPriceStock(currentVariation);
  updateHints();
}

function maximumOrderQuantity(stock) {
  let maximum = Math.max(0, Number(stock) || 0);
  if (CONFIGURED_MAX_ORDER_QTY > 0) maximum = Math.min(maximum, CONFIGURED_MAX_ORDER_QTY);
  if (SOLD_INDIVIDUALLY) maximum = Math.min(maximum, 1);
  return maximum;
}

function syncQuantityBounds(stock) {
  const input = document.getElementById('qty');
  const hint = document.getElementById('quantity-limit-hint');
  const maximum = maximumOrderQuantity(stock);
  if (!input) return maximum;

  input.min = MIN_ORDER_QTY;
  input.max = Math.max(1, maximum);
  if (maximum >= MIN_ORDER_QTY) {
    const current = Number.parseInt(input.value, 10) || MIN_ORDER_QTY;
    input.value = Math.max(MIN_ORDER_QTY, Math.min(maximum, current));
  }
  if (hint) hint.textContent = PRODUCT_TEXT.isAr
    ? `${PRODUCT_TEXT.minimum} ${MIN_ORDER_QTY} · ${PRODUCT_TEXT.maximum} ${maximum} ${PRODUCT_TEXT.perOrder}`
    : `Minimum ${MIN_ORDER_QTY} · Maximum ${maximum} per order`;
  return maximum;
}

function quantityValidationMessage(quantity, maximum) {
  if (maximum < MIN_ORDER_QTY) return PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.notEnough} ${MIN_ORDER_QTY}.` : `This product does not have enough stock to meet its minimum order quantity of ${MIN_ORDER_QTY}.`;
  if (quantity < MIN_ORDER_QTY) return PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.minimum} ${MIN_ORDER_QTY}.` : `Minimum order quantity is ${MIN_ORDER_QTY}.`;
  if (quantity > maximum) return PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.maximum} ${maximum}.` : `Maximum order quantity is ${maximum}.`;
  return null;
}

function showQuantityError(message) {
  if (typeof window.showToast === 'function') window.showToast(message, 'error');
  else window.alert(message);
}

function renderPriceStock(v) {
  const priceEl = document.getElementById('price-display');
  const origEl = document.getElementById('orig-display');
  const badgeEl = document.getElementById('disc-badge');
  const stockEl = document.getElementById('stock-display');
  const singleQty = document.getElementById('single-qty-controls');
  const rangeNote = document.getElementById('price-range-note');
  if (rangeNote) rangeNote.style.display = v ? 'none' : '';

  function effectivePrice(item) {
    const reg = item.reg > 0 ? item.reg : item.price;
    if (DISC_PCT > 0 && reg > 0 && item.price >= reg) return Math.round(reg * (1 - DISC_PCT / 100) * 100) / 100;
    return item.price;
  }
  function showDiscount(effective, original) {
    if (priceEl) {
      priceEl.textContent = effective.toFixed(2) + ' EGP';
      priceEl.classList.toggle('sale-price', effective < original);
      priceEl.classList.toggle('on-sale', effective < original);
    }
    if (origEl) {
      origEl.textContent = original > effective ? original.toFixed(2) + ' EGP' : '';
      origEl.style.display = original > effective ? '' : 'none';
    }
    if (badgeEl) {
      badgeEl.textContent = DISC_PCT > 0 && effective < original ? Math.round(DISC_PCT) + '% OFF' : '';
      badgeEl.style.display = DISC_PCT > 0 && effective < original ? '' : 'none';
    }
  }

  if (singleQty) singleQty.style.display = COLOR_ATTR_KEY ? 'none' : '';
  if (v) {
    showDiscount(effectivePrice(v), v.reg > 0 ? v.reg : v.price);
    if (stockEl) stockEl.innerHTML = v.stock > 0
      ? `<span class="badge-stock-ok">✓ ${PRODUCT_TEXT.inStock} (${v.stock.toLocaleString()} ${PRODUCT_TEXT.available})</span>`
      : `<span class="badge-stock-no">${PRODUCT_TEXT.outOfStock}</span>`;
  } else {
    const active = COLOR_ATTR_KEY && selectedColorValues.size > 0
      ? selectedColorVariations().map(({ variation }) => variation).filter(Boolean)
      : VAR_DATA;
    if (active.length && priceEl) {
      const prices = active.map(effectivePrice);
      const min = Math.min(...prices), max = Math.max(...prices);
      priceEl.textContent = min === max ? `${min.toFixed(2)} EGP` : `${min.toFixed(2)} – ${max.toFixed(2)} EGP`;
      priceEl.classList.toggle('sale-price', DISC_PCT > 0);
    }
    if (origEl) origEl.style.display = 'none';
    if (badgeEl) {
      badgeEl.textContent = DISC_PCT > 0 ? Math.round(DISC_PCT) + '% OFF' : '';
      badgeEl.style.display = DISC_PCT > 0 ? '' : 'none';
    }
    if (stockEl && COLOR_ATTR_KEY && selectedColorValues.size > 0) {
      stockEl.innerHTML = `<span class="badge-stock-ok">✓ ${selectedColorValues.size} ${PRODUCT_TEXT.isAr ? 'ألوان مختارة' : 'colors selected'}</span>`;
    }
  }
  if (!COLOR_ATTR_KEY) syncQuantityBounds(v ? v.stock : PRODUCT_STOCK_QTY);
  updateAddButtonState();
}

function displayAttributeKey(key) {
  if (!PRODUCT_TEXT.isAr) return key;
  return key.toLowerCase() === 'color' ? 'اللون' : (key.toLowerCase() === 'size' ? 'المقاس' : key);
}

function displayAttributeValue(key, value) {
  const btn = document.querySelector(`[data-attr-key="${key}"][data-attr-val="${value}"]`);
  return btn?.dataset.attrDisplay || value;
}

function updateSelectedSummary() {
  const el = document.getElementById('product-sel-summary');
  if (!el) return;
  const parts = [];
  if (COLOR_ATTR_KEY && selectedColorValues.size) {
    parts.push(`${displayAttributeKey(COLOR_ATTR_KEY)}: ${[...selectedColorValues].map(v => displayAttributeValue(COLOR_ATTR_KEY, v)).join(', ')}`);
  }
  Object.entries(selectedAttrs).forEach(([key, value]) => {
    parts.push(`${displayAttributeKey(key)}: ${displayAttributeValue(key, value)}`);
  });
  el.textContent = parts.length ? parts.join(' • ') : '';
}

function updateHints() {
  ATTR_KEYS.forEach(key => {
    const el = document.getElementById('hint-' + slugify(key));
    if (!el) return;
    if (key.toLowerCase() === 'color') {
      el.textContent = selectedColorValues.size ? '' : (PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.select} ${displayAttributeKey(key)}` : `Please select a ${key}`);
      return;
    }
    const missing = ATTR_KEYS.filter(k => k.toLowerCase() !== 'color' && selectedAttrs[k] === undefined);
    el.textContent = missing.includes(key) && (selectedColorValues.size || Object.keys(selectedAttrs).length)
      ? (PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.select} ${displayAttributeKey(key)}` : `Please select a ${key}`)
      : '';
  });
}

function updateSelectedLabels() {
  ATTR_KEYS.forEach(key => {
    const el = document.getElementById('sel-' + slugify(key));
    if (!el) return;
    const values = key.toLowerCase() === 'color' ? [...selectedColorValues] : (selectedAttrs[key] === undefined ? [] : [selectedAttrs[key]]);
    el.textContent = values.length ? ': ' + values.map(value => displayAttributeValue(key, value)).join(', ') : '';
  });
}

function updateColorSteppers() {
  document.querySelectorAll('.color-qty-stepper').forEach(stepper => { stepper.hidden = true; stepper.innerHTML = ''; });
  if (!COLOR_ATTR_KEY) return;
  selectedColorValues.forEach(value => {
    const variation = variationForColor(value);
    const stepper = document.getElementById(`color-qty-${slugify(COLOR_ATTR_KEY)}-${slugify(value)}`);
    if (!stepper || !variation) return;
    const maximum = maximumOrderQuantity(variation.stock);
    const quantityKey = colorQuantityKey(value);
    const quantity = Math.max(MIN_ORDER_QTY, Math.min(maximum, Number(colorQuantities[quantityKey] || MIN_ORDER_QTY)));
    colorQuantities[quantityKey] = quantity;
    stepper.hidden = false;
    stepper.innerHTML = `<button type="button" onclick="changeColorQty('${String(COLOR_ATTR_KEY).replace(/'/g, "\'")}','${String(value).replace(/'/g, "\'")}',-1,event)" aria-label="Decrease">−</button><input type="number" min="${MIN_ORDER_QTY}" max="${Math.max(1, maximum)}" value="${quantity}" aria-label="${PRODUCT_TEXT.quantity || 'Quantity'}"><button type="button" onclick="changeColorQty('${String(COLOR_ATTR_KEY).replace(/'/g, "\'")}','${String(value).replace(/'/g, "\'")}',1,event)" aria-label="Increase">+</button>`;
    const input = stepper.querySelector('input');
    input.addEventListener('click', (event) => event.stopPropagation());
    input.addEventListener('change', (event) => {
      event.stopPropagation();
      setColorQty(COLOR_ATTR_KEY, value, input.value);
    });
  });
}

function setColorQty(key, value, rawQty) {
  const variation = variationForColor(value);
  if (!variation) return;
  const maximum = maximumOrderQuantity(variation.stock);
  const next = Math.max(MIN_ORDER_QTY, Math.min(maximum, Number.parseInt(rawQty, 10) || MIN_ORDER_QTY));
  colorQuantities[colorQuantityKey(value)] = next;
  updateColorSteppers();
  updateAddButtonState();
}

function changeColorQty(key, value, delta, event) {
  event?.preventDefault();
  event?.stopPropagation();
  setColorQty(key, value, (colorQuantities[colorQuantityKey(value)] || MIN_ORDER_QTY) + delta);
}

function selectionPrompt() {
  const missing = ATTR_KEYS.find((key) => {
    if (key.toLowerCase() === 'color') return COLOR_ATTR_KEY && selectedColorValues.size === 0;
    return selectedAttrs[key] === undefined;
  });
  if (!missing) return PRODUCT_TEXT.isAr ? 'اختار اختيار' : 'Select an option';
  return PRODUCT_TEXT.isAr
    ? `${PRODUCT_TEXT.select} ${displayAttributeKey(missing)}`
    : `Select a ${missing}`;
}

function updateAddButtonState() {
  const addBtn = document.getElementById('add-to-cart-btn');
  const stickyBtn = document.querySelector('.sticky-atc-btn');
  let validCount = 0;
  let totalQty = 0;
  if (COLOR_ATTR_KEY) {
    selectedColorVariations().forEach(({ value, variation }) => {
      if (!variation) return;
      const max = maximumOrderQuantity(variation.stock);
      const qty = Number(colorQuantities[colorQuantityKey(value)] || MIN_ORDER_QTY);
      if (qty >= MIN_ORDER_QTY && qty <= max && max >= MIN_ORDER_QTY) { validCount++; totalQty += qty; }
    });
    const label = totalQty > 0 ? `${PRODUCT_TEXT.addToCart} (${totalQty})` : PRODUCT_TEXT.addToCart;
    if (addBtn) { addBtn.disabled = validCount === 0; addBtn.textContent = validCount ? label : selectionPrompt(); }
    if (stickyBtn) { stickyBtn.disabled = validCount === 0; stickyBtn.textContent = validCount ? label : selectionPrompt(); }
  } else if (addBtn) {
    const canOrder = currentVariation ? maximumOrderQuantity(currentVariation.stock) >= MIN_ORDER_QTY : maximumOrderQuantity(PRODUCT_STOCK_QTY) >= MIN_ORDER_QTY;
    addBtn.disabled = !canOrder;
  }
}

function slugify(str) {
  return str.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function resetProductSelections() {
  selectedAttrs = {};
  selectedColorValues = new Set();
  colorQuantities = {};
  currentVariation = null;
  _lockedImgUrl = null;

  document.querySelectorAll('[data-attr-key].selected').forEach((button) => button.classList.remove('selected'));

  const gallery = document.getElementById('gallery-thumbs');
  if (gallery && INITIAL_GALLERY_HTML) gallery.innerHTML = INITIAL_GALLERY_HTML;

  const mainImage = document.getElementById('main-img');
  const originalImage = mainImage?.dataset.originalSrc;
  if (originalImage) setMainImg(originalImage, false);

  updateAvailability();
  renderPriceStock(null);
  const stockDisplay = document.getElementById('stock-display');
  if (stockDisplay) stockDisplay.innerHTML = PRODUCT_STOCK_QTY > 0
    ? `<span class="badge-stock-ok">✓ ${PRODUCT_TEXT.inStock} (${PRODUCT_STOCK_QTY.toLocaleString()} ${PRODUCT_TEXT.available})</span>`
    : `<span class="badge-stock-no">${PRODUCT_TEXT.outOfStock}</span>`;
  syncQuantityBounds(PRODUCT_STOCK_QTY);
  const quantityInput = document.getElementById('qty');
  if (quantityInput) quantityInput.value = Math.max(MIN_ORDER_QTY, Math.min(maximumOrderQuantity(PRODUCT_STOCK_QTY) || MIN_ORDER_QTY, MIN_ORDER_QTY));
  updateSelectedLabels();
  updateSelectedSummary();
  updateHints();
  updateAddButtonState();
  if (ATTR_KEYS.length > 0) {
    const addBtn = document.getElementById('add-to-cart-btn');
    const stickyBtn = document.querySelector('.sticky-atc-btn');
    if (addBtn) { addBtn.disabled = true; addBtn.textContent = selectionPrompt(); }
    if (stickyBtn) { stickyBtn.disabled = true; stickyBtn.textContent = selectionPrompt(); }
  }
}

window.productCartAddFinished = function (success) {
  productCartRequestPending = false;
  if (success) resetProductSelections();
  else updateAddButtonState();
};

// ── Cart integration ──────────────────────────────────────────────────
function handleAddToCart(id, name, basePrice, image) {
  if (productCartRequestPending) return;
  if (COLOR_ATTR_KEY) {
    const items = selectedColorVariations().map(({ value, variation }) => {
      const maximum = variation ? maximumOrderQuantity(variation.stock) : 0;
      const qty = Number(colorQuantities[colorQuantityKey(value)] || MIN_ORDER_QTY);
      return { variation_id: variation?.id, qty, value, maximum };
    }).filter(item => item.variation_id && item.qty >= MIN_ORDER_QTY && item.qty <= item.maximum && item.maximum >= MIN_ORDER_QTY);
    if (!items.length) {
      showQuantityError(PRODUCT_TEXT.select + ' ' + displayAttributeKey(COLOR_ATTR_KEY));
      return;
    }
    const missing = ATTR_KEYS.filter(key => key.toLowerCase() !== 'color' && selectedAttrs[key] === undefined);
    if (missing.length) {
      missing.forEach(key => {
        const el = document.getElementById('hint-' + slugify(key));
        if (el) { el.textContent = PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.select} ${displayAttributeKey(key)}` : `Please select a ${key}`; el.style.color = 'var(--c-orange)'; }
      });
      return;
    }
    productCartRequestPending = true;
    const addBtn = document.getElementById('add-to-cart-btn');
    const stickyBtn = document.querySelector('.sticky-atc-btn');
    if (addBtn) addBtn.disabled = true;
    if (stickyBtn) stickyBtn.disabled = true;
    addMultipleToCart(id, items.map(({ variation_id, qty }) => ({ variation_id, qty })));
    return;
  }

  if (ATTR_KEYS.length > 0 && !currentVariation) {
    const missing = ATTR_KEYS.filter(k => !selectedAttrs[k]);
    missing.forEach(k => {
      const el = document.getElementById('hint-' + slugify(k));
      if (el) { el.textContent = PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.select} ${displayAttributeKey(k)}` : `Please select a ${k}`; el.style.color = 'var(--c-orange)'; }
    });
    return;
  }

  const qtyInput = document.getElementById('qty');
  const qty = parseInt(qtyInput?.value, 10) || MIN_ORDER_QTY;
  const maximumQuantity = maximumOrderQuantity(currentVariation ? currentVariation.stock : PRODUCT_STOCK_QTY);
  const quantityError = quantityValidationMessage(qty, maximumQuantity);
  if (quantityError) { showQuantityError(quantityError); return; }
  const varId = currentVariation ? currentVariation.id : null;
  let price = basePrice;
  let oldPrice = null;
  if (currentVariation) {
    const reg = currentVariation.reg > 0 ? currentVariation.reg : currentVariation.price;
    price = currentVariation.price;
    if (DISC_PCT > 0 && reg > 0 && price >= reg) price = Math.round(reg * (1 - DISC_PCT / 100) * 100) / 100;
    if (reg > price) oldPrice = reg;
  }
  const varLabel = currentVariation ? Object.entries(currentVariation.attrs).filter(([k]) => !String(k).startsWith('_')).map(([k,v]) => `${k}: ${v}`).join(', ') : null;
  productCartRequestPending = true;
  const addBtn = document.getElementById('add-to-cart-btn');
  const stickyBtn = document.querySelector('.sticky-atc-btn');
  if (addBtn) addBtn.disabled = true;
  if (stickyBtn) stickyBtn.disabled = true;
  addToCart(id, name, price, image, varId, qty, varLabel, oldPrice);
}

// ── Gallery ───────────────────────────────────────────────────────────

function setMainImg(url, fade) {
  const img = document.getElementById('main-img');
  const placeholder = document.getElementById('main-img-placeholder');
  if (!img || !url) return;
  if (fade) {
    img.classList.add('img-switching');
    setTimeout(() => {
      img.src = url;
      img.style.display = '';
      if (placeholder) placeholder.style.display = 'none';
      img.classList.remove('img-switching');
    }, 160);
  } else {
    img.src = url;
    img.style.display = '';
    if (placeholder) placeholder.style.display = 'none';
  }
}

function switchImg(thumb, url) {
  setMainImg(url, true);
  document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

// ── Image error / placeholder handlers ───────────────────────────────

function handleImgError(img) {
  img.style.display = 'none';
  const placeholder = document.getElementById('main-img-placeholder');
  if (placeholder) {
    placeholder.style.display = 'flex';
  } else {
    const wrap = document.getElementById('gallery-main-wrap');
    if (wrap) {
      const ph = document.createElement('div');
      ph.id = 'main-img-placeholder';
      ph.className = 'img-placeholder-box';
      ph.style.width = '100%';
      ph.style.height = '100%';
      ph.innerHTML = '<span class="img-placeholder-icon">🖼️</span><span class="img-placeholder-text">Image unavailable</span>';
      wrap.appendChild(ph);
    }
  }
}

function handleThumbError(img) {
  const thumb = img.closest('.gallery-thumb');
  if (thumb) thumb.classList.add('img-error');
}

function handleSectionImgError(img) {
  img.style.display = 'none';
  const item = img.closest('.img-section-item');
  if (item) {
    const ph = document.createElement('div');
    ph.className = 'section-img-placeholder';
    ph.textContent = '🖼️';
    item.appendChild(ph);
    item.style.cursor = 'default';
    item.onclick = null;
  }
}

function switchImgFromSection(url) {
  setMainImg(url, true);
  document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
  window.scrollTo({ top: document.getElementById('gallery-main-wrap')?.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
}

// ── Color swatch image preview ────────────────────────────────────────

function getColorImages(attrKey, attrVal) {
  const v = VAR_DATA.find(v => v.attrs[attrKey] === attrVal);
  return v ? (v.images || []) : [];
}

function getColorImage(attrKey, attrVal) {
  const imgs = getColorImages(attrKey, attrVal);
  return imgs.length > 0 ? imgs[0] : null;
}

function updateVariationThumbs(attrKey, attrVal) {
  const strip = document.getElementById('gallery-thumbs');
  if (!strip) return;
  const imgs = getColorImages(attrKey, attrVal);
  if (imgs.length === 0) return;
  strip.innerHTML = '';
  imgs.forEach((url, i) => {
    const div = document.createElement('div');
    div.className = 'gallery-thumb' + (i === 0 ? ' active' : '');
    div.addEventListener('click', () => {
      setMainImg(url, true);
      strip.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
      div.classList.add('active');
    });
    const im = document.createElement('img');
    im.src = url;
    im.alt = attrVal + ' ' + (i + 1);
    im.loading = 'lazy';
    div.appendChild(im);
    strip.appendChild(div);
  });
}

function previewColorImage(attrKey, attrVal) {
  const url = getColorImage(attrKey, attrVal);
  if (!url) return;
  const img = document.getElementById('main-img');
  if (img && !img.dataset.originalSrc) img.dataset.originalSrc = img.src;
  setMainImg(url, false);
}

function restoreImage() {
  const target = _lockedImgUrl || document.getElementById('main-img')?.dataset.originalSrc;
  if (target) setMainImg(target, false);
}

// ── Quantity ──────────────────────────────────────────────────────────
function changeQty(delta) {
  const input = document.getElementById('qty');
  if (!input) return;
  const minimum = parseInt(input.min, 10) || MIN_ORDER_QTY;
  const maximum = parseInt(input.max, 10) || minimum;
  input.value = Math.max(minimum, Math.min(maximum, (parseInt(input.value, 10) || minimum) + delta));
}

// ── Star rating picker ────────────────────────────────────────────────
let _selectedRating = 0;
const starLabels = ['','Terrible','Poor','OK','Good','Excellent'];

function hoverStar(val) {
  document.querySelectorAll('.rv-star').forEach((s,i) => s.classList.toggle('hover', i < val));
}
function resetStarHover() {
  document.querySelectorAll('.rv-star').forEach(s => s.classList.remove('hover'));
}
function setRating(val) {
  _selectedRating = val;
  document.getElementById('rating-input').value = val;
  document.querySelectorAll('.rv-star').forEach((s,i) => {
    s.classList.toggle('lit', i < val);
    s.classList.remove('hover');
  });
  const lbl = document.getElementById('star-label');
  if (lbl) lbl.textContent = starLabels[val] || '';
}

// ── Review form validation ─────────────────────────────────────────────
function validateReviewForm() {
  if (!_selectedRating) {
    const lbl = document.getElementById('star-label');
    if (lbl) { lbl.textContent = 'Please pick a rating!'; lbl.style.color = '#e85d26'; }
    document.getElementById('star-picker').scrollIntoView({behavior:'smooth', block:'center'});
    return false;
  }
  return true;
}

// ── Character counter ─────────────────────────────────────────────────
document.getElementById('review-body')?.addEventListener('input', function() {
  const el = document.getElementById('char-count');
  if (el) el.textContent = this.value.length;
});

// ── Sort reviews ──────────────────────────────────────────────────────
function sortReviews(by) {
  const list = document.getElementById('review-list');
  if (!list) return;
  const cards = Array.from(list.querySelectorAll('.rv-card'));
  cards.sort((a, b) => {
    if (by === 'newest')  return parseInt(b.dataset.ts) - parseInt(a.dataset.ts);
    if (by === 'highest') return parseInt(b.dataset.rating) - parseInt(a.dataset.rating);
    if (by === 'lowest')  return parseInt(a.dataset.rating) - parseInt(b.dataset.rating);
    if (by === 'helpful') return parseInt(b.dataset.helpful) - parseInt(a.dataset.helpful);
    return 0;
  });
  cards.forEach(c => list.appendChild(c));
}

// ── Helpful vote ──────────────────────────────────────────────────────
function markHelpful(btn, id) {
  if (btn.disabled) return;
  btn.disabled = true;
  fetch(`/reviews/${id}/helpful`, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json'},
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const cnt = btn.querySelector('.rv-helpful-cnt');
      if (cnt) cnt.textContent = `(${data.count})`;
      btn.classList.add('voted');
      btn.title = 'Thanks for your feedback!';
      const card = btn.closest('.rv-card');
      if (card) card.dataset.helpful = data.count;
    } else {
      btn.disabled = true;
      btn.classList.add('voted');
    }
  })
  .catch(() => { btn.disabled = false; });
}

// ── Delete review ─────────────────────────────────────────────────────
function deleteReview(btn, id, productId) {
  if (!confirm(PRODUCT_TEXT.isAr ? 'تحذف تقييمك؟ مش هتقدر ترجعه.' : 'Delete your review? This cannot be undone.')) return;
  fetch(`/reviews/${id}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'Accept': 'application/json',
    },
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.closest('.rv-card')?.remove();
      // Show write form again
      const fw = document.getElementById('review-form-wrap');
      if (fw) fw.classList.remove('hidden');
      // Optionally reload to update stats
      setTimeout(() => location.reload(), 600);
    } else {
      alert(data.message || 'Could not delete review.');
    }
  });
}

// ── Wishlist btn initial state ────────────────────────────────────────
// State already rendered server-side via $inWishlist — nothing to do here.

</script>
<style>
@keyframes shake {
  0%,100%{transform:translateX(0)} 20%{transform:translateX(-6px)} 40%{transform:translateX(6px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)}
}

/* ── Product Info Panel (pi-*) ─────────────────────────────────────── */

/* Title row with wishlist heart */
.pi-title-row {
  display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px; min-width: 0;
}
.pi-title {
  flex: 1; min-width: 0; font-size: 22px; font-weight: 800; color: #1a1a1a;
  line-height: 1.3; margin: 0; overflow-wrap:anywhere;
}
.pi-wish-btn {
  flex-shrink: 0; width: 42px; height: 42px; border-radius: 50%;
  border: 2px solid #e0e0e0; background: #fff; font-size: 20px;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  color: #ccc; transition: border-color .2s, color .2s, transform .15s;
  margin-top: 2px;
}
.pi-wish-btn:hover { border-color: #e85d26; color: #e85d26; transform: scale(1.1); }
.pi-wish-btn.wished { border-color: #e85d26; color: #e85d26; }

/* Rating row */
.pi-rating-row {
  display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
}
.pi-stars { display: flex; gap: 2px; }
.pi-star-filled { color: #f5a623; font-size: 15px; }
.pi-star-empty  { color: #ddd;    font-size: 15px; }
.pi-rating-val  { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.pi-rating-count { font-size: 13px; color: var(--c-mid, #888); text-decoration: underline; text-underline-offset: 2px; }
.pi-rating-count:hover { color: #e85d26; }
.pi-rating-none { font-size: 13px; color: #bbb; font-style: italic; }

/* Stock */
.pi-stock { margin-bottom: 14px; }

/* Price block */
.pi-price-block { margin-bottom: 16px; }
.pi-price-row {
  display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 4px; min-width: 0;
}
.pi-price-main {
  font-size: 28px; font-weight: 800; color: #1a1a1a; letter-spacing: -.5px;
}
.pi-price-main.on-sale { color: #e85d26; }
.pi-price-orig {
  font-size: 16px; color: #aaa; text-decoration: line-through; font-weight: 400;
}
.pi-disc-badge {
  background: #e85d26; color: #fff; font-size: 12px; font-weight: 700;
  padding: 3px 9px; border-radius: 20px; letter-spacing: .03em;
}
.pi-sale-note,
.pi-price-explanation {
  display: flex; flex-direction: column; gap: 3px;
  font-size: 12px; color: #166534; font-weight: 600;
  background: #f0fdf4; border: 1px solid #bbf7d0;
  padding: 8px 12px; border-radius: 8px; margin-top: 7px;
  line-height: 1.55; max-width: 100%;
}
.pi-sale-note-title,
.pi-price-note-title { font-weight: 800; color: #14532d; }
.pi-sale-saving { font-weight: 700; }
@media(max-width:640px) {
  .pi-sale-note,
  .pi-price-explanation { font-size: 11px; padding: 8px 10px; }
}

/* Variations wrapper */
.pi-variations-wrap { margin:20px 0 18px; display:grid; gap:14px; }
.pi-var-group { margin:0; padding:0 0 14px; border-bottom:1px solid #eeeae5; }
.pi-var-group:last-child { border-bottom:0; padding-bottom:0; }
.pi-var-group .var-label { display:flex; align-items:center; gap:6px; margin-bottom:10px; font-size:13px; font-weight:800; color:#292524; }
.pi-var-group .var-selected-label { color:var(--c-orange, #e85d26); font-weight:700; }
.var-color-name { max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:11px; font-weight:700; color:#57534e; }
.pi-var-group .var-btn { min-width:46px; height:38px; padding:0 14px; border-radius:10px; }

/* Cart row */
.pi-cart-row {
  display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; min-width: 0;
}
.pi-atc-btn {
  flex: 1 1 160px; min-width: 0; font-size: 15px; font-weight: 700;
  padding: 14px 20px; border-radius: 12px;
}

/* Amount box: keep quantity controls compact, balanced, and easy to tap. */
.product-page .pi-cart-row { align-items: start; }
.product-page .pi-cart-row #single-qty-controls { flex: 0 0 auto; min-width: 0; }
.product-page .pi-cart-row .qty-input {
  display: grid;
  grid-template-columns: 44px 58px 44px;
  align-items: center;
  width: 146px;
  min-height: 52px;
  border: 1px solid #dcd8d3;
  border-radius: 14px;
  background: #fafaf8;
  box-shadow: 0 3px 10px rgba(24,24,24,.045);
  overflow: hidden;
}
.product-page .pi-cart-row .qty-input button {
  display: grid;
  place-items: center;
  width: 44px;
  height: 52px;
  border: 0;
  background: transparent;
  color: #292524;
  font-size: 20px;
  font-weight: 500;
  line-height: 1;
  cursor: pointer;
  transition: background .15s, color .15s;
}
.product-page .pi-cart-row .qty-input button:hover,
.product-page .pi-cart-row .qty-input button:focus-visible {
  background: #f0ede9;
  color: var(--c-orange, #e85d26);
  outline: none;
}
.product-page .pi-cart-row .qty-input input {
  width: 58px;
  height: 52px;
  padding: 0;
  border: 0;
  background: transparent;
  color: #1a1a1a;
  text-align: center;
  font-size: 16px;
  font-weight: 850;
  outline: none;
  -moz-appearance: textfield;
}
.product-page .pi-cart-row .qty-input input::-webkit-outer-spin-button,
.product-page .pi-cart-row .qty-input input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.product-page .pi-cart-row #quantity-limit-hint {
  max-width: 146px;
  margin-top: 7px !important;
  text-align: center;
  line-height: 1.35;
}
.product-page .pi-cart-row .pi-atc-btn {
  min-height: 52px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
}
@media (max-width: 520px) {
  .product-page .pi-cart-row { gap: 8px; }
  .product-page .pi-cart-row .qty-input {
    grid-template-columns: 40px 52px 40px;
    width: 132px;
  }
  .product-page .pi-cart-row .qty-input button { width: 40px; }
  .product-page .pi-cart-row .qty-input input { width: 52px; }
  .product-page .pi-cart-row #quantity-limit-hint { max-width: 132px; font-size: 11px !important; }
}


/* Description */
.pi-desc { margin-top: 4px; }

/* ═══ Reviews Section ═══════════════════════════════════════════════ */
.reviews-section { margin-top: 64px; }

/* Overview panel */
.rv-overview {
  display: flex; align-items: flex-start; gap: 32px; flex-wrap: wrap;
  background: #fafaf8; border: 1px solid #eee; border-radius: 16px;
  padding: 28px 32px; margin-bottom: 28px;
}
.rv-score-box { text-align: center; min-width: 90px; }
.rv-big-num { font-size: 52px; font-weight: 800; line-height: 1; color: #1a1a1a; }
.rv-big-stars { font-size: 20px; margin: 6px 0 4px; letter-spacing: 2px; }
.rv-total-label { font-size: 12px; color: var(--c-mid); white-space: nowrap; }

.rv-distribution { flex: 1; min-width: 180px; display: flex; flex-direction: column; gap: 6px; justify-content: center; }
.dist-row { display: flex; align-items: center; gap: 8px; }
.dist-label { font-size: 12px; color: #666; width: 30px; text-align: right; white-space: nowrap; }
.dist-bar-wrap { flex: 1; height: 8px; background: #e8e8e8; border-radius: 99px; overflow: hidden; }
.dist-bar-fill { height: 100%; background: linear-gradient(90deg, #f5a623, #e85d26); border-radius: 99px; transition: width .4s; }
.dist-num { font-size: 12px; color: #888; width: 24px; }

.rv-write-btn {
  display: inline-flex; align-items: center; justify-content: center;
  background: var(--c-orange); color: #fff; font-size: 14px; font-weight: 700;
  padding: 12px 22px; border-radius: 10px; border: none; cursor: pointer;
  text-decoration: none; white-space: nowrap; align-self: center;
  transition: background .2s, transform .1s;
}
.rv-write-btn:hover { background: #d44f1a; transform: translateY(-1px); }
.rv-wrote-badge {
  font-size: 13px; font-weight: 600; color: #22c55e;
  background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px 16px;
  border-radius: 10px; align-self: center;
}

/* Flash */
.rv-flash { padding: 12px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; margin-bottom: 16px; }
.rv-flash-ok { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.rv-flash-err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

/* Sort bar */
.rv-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  padding-bottom: 16px; border-bottom: 1px solid #eee; margin-bottom: 20px;
}
.rv-toolbar-count { font-size: 15px; font-weight: 700; color: #1a1a1a; }
.rv-sort-select {
  font-size: 13px; padding: 7px 12px; border: 1px solid #e0e0e0; border-radius: 8px;
  background: #fff; color: #333; cursor: pointer; outline: none;
}
.rv-sort-select:focus { border-color: var(--c-orange); }

/* Review card */
.rv-card {
  border: 1px solid #eee; border-radius: 14px; padding: 20px 22px;
  margin-bottom: 14px; background: #fff;
  transition: box-shadow .2s;
}
.rv-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.07); }
.rv-card-head { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
.rv-avatar {
  width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 16px; font-weight: 800; letter-spacing: -.5px;
}
.rv-card-meta { flex: 1; min-width: 0; }
.rv-name-row { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.rv-name { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.rv-verified { font-size: 11px; font-weight: 600; color: #22c55e; background: #f0fdf4; padding: 2px 8px; border-radius: 99px; border: 1px solid #bbf7d0; }
.rv-own-badge { font-size: 11px; font-weight: 600; color: #3b82f6; background: #eff6ff; padding: 2px 8px; border-radius: 99px; border: 1px solid #bfdbfe; }
.rv-date { font-size: 12px; color: #aaa; margin-top: 2px; }
.rv-card-stars { font-size: 16px; letter-spacing: 1px; flex-shrink: 0; }
.rv-review-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
.rv-review-body { font-size: 14px; color: #444; line-height: 1.65; }
.rv-card-foot { display: flex; align-items: center; gap: 12px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f0f0f0; }
.rv-helpful {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 13px; color: #666; background: #f5f5f5; border: 1px solid #e0e0e0;
  padding: 5px 12px; border-radius: 8px; cursor: pointer; transition: all .2s;
}
.rv-helpful:hover:not(:disabled) { background: #fff0e8; border-color: var(--c-orange); color: var(--c-orange); }
.rv-helpful.voted { background: #fff0e8; border-color: var(--c-orange); color: var(--c-orange); cursor: default; }
.rv-delete {
  font-size: 13px; color: #ef4444; background: none; border: 1px solid #fecaca;
  padding: 5px 12px; border-radius: 8px; cursor: pointer; transition: all .2s; margin-left: auto;
}
.rv-delete:hover { background: #fef2f2; }

.rv-empty { text-align: center; padding: 48px 24px; color: var(--c-mid); }

/* Write review form */
.rv-form-card {
  background: #fff; border: 1px solid #eee; border-radius: 16px;
  padding: 32px; margin-top: 32px;
}
.rv-form-title { font-size: 20px; font-weight: 800; color: #1a1a1a; margin: 0 0 4px; }
.rv-form-sub { font-size: 13px; color: var(--c-mid); margin: 0 0 24px; }
.rv-form-row { margin-bottom: 18px; }
.rv-form-label { display: block; font-size: 13px; font-weight: 700; color: #333; margin-bottom: 8px; }

.rv-star-picker { display: flex; gap: 4px; }
.rv-star {
  font-size: 32px; cursor: pointer; color: #e0e0e0;
  transition: color .1s, transform .1s;
}
.rv-star.lit, .rv-star.hover { color: #f5a623; }
.rv-star:hover { transform: scale(1.15); }
.rv-star-label { font-size: 13px; color: #888; margin-left: 8px; line-height: 32px; vertical-align: top; }
.rv-field-err { display: block; font-size: 12px; color: #ef4444; margin-top: 4px; }

.rv-input {
  width: 100%; padding: 11px 14px; border: 1px solid #e0e0e0; border-radius: 10px;
  font-size: 14px; outline: none; background: #fafafa; box-sizing: border-box;
  transition: border-color .2s, background .2s;
}
.rv-input:focus { border-color: var(--c-orange); background: #fff; }
.rv-textarea {
  width: 100%; padding: 11px 14px; border: 1px solid #e0e0e0; border-radius: 10px;
  font-size: 14px; resize: vertical; outline: none; background: #fafafa;
  box-sizing: border-box; transition: border-color .2s, background .2s; font-family: inherit;
}
.rv-textarea:focus { border-color: var(--c-orange); background: #fff; }
.rv-char-counter { font-size: 12px; color: #aaa; text-align: right; margin-top: 4px; }

.rv-submit {
  background: var(--c-orange); color: #fff; font-size: 15px; font-weight: 700;
  padding: 14px 32px; border: none; border-radius: 12px; cursor: pointer;
  transition: background .2s, transform .1s; width: 100%;
}
.rv-submit:hover { background: #d44f1a; transform: translateY(-1px); }

.rv-signin-prompt {
  text-align: center; padding: 48px 24px; background: #fafaf8;
  border: 1px dashed #ddd; border-radius: 16px; margin-top: 32px;
  color: var(--c-mid); font-size: 15px;
}

.hidden { display: none !important; }

@media(max-width:640px) {
  .pi-title { font-size: 21px; }
  .pi-price-main { font-size: 25px; }
  .pi-price-orig { font-size: 14px; }
  .pi-atc-btn { flex-basis: 150px; }
  .rv-overview { flex-direction: column; gap: 20px; padding: 20px; }
  .rv-score-box { display: flex; align-items: center; gap: 12px; }
  .rv-big-num { font-size: 40px; }
  .rv-form-card { padding: 20px; }
}
</style>
<script>
// ── Sticky ATC bar visibility ──────────────────────────────────────────
(function () {
  const bar     = document.getElementById('sticky-atc-bar');
  const mainBtn = document.getElementById('add-to-cart-btn');
  if (!bar || !mainBtn) return;

  // Keep the sticky price in sync whenever the variation engine updates it
  const priceEl     = document.getElementById('price-display');
  const stickyPrice = document.getElementById('sticky-price');
  if (priceEl && stickyPrice) {
    new MutationObserver(() => {
      stickyPrice.textContent = priceEl.textContent;
    }).observe(priceEl, { childList: true, characterData: true, subtree: true });
  }

  function setBar(show) {
    bar.classList.toggle('visible', show);
    bar.setAttribute('aria-hidden', String(!show));
  }

  // IntersectionObserver is reliable in iframes and all scroll contexts.
  // Show the bar when the button has scrolled ABOVE the viewport (top < 0).
  // threshold:0 fires as soon as any part of the button leaves the viewport.
  const observer = new IntersectionObserver(([entry]) => {
    // isIntersecting: button is (at least partially) visible
    // !isIntersecting + top < 0: button scrolled off the top → show bar
    // !isIntersecting + top > 0: button not yet reached → keep bar hidden
    const aboveViewport = !entry.isIntersecting && entry.boundingClientRect.top < 0;
    setBar(aboveViewport);
  }, { threshold: 0 });

  observer.observe(mainBtn);
})();
</script>
@endpush

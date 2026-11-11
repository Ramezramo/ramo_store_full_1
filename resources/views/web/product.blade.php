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
    'quantity' => $isAr ? 'الكمية' : 'Quantity',
    'adding' => $isAr ? 'بنضيف للسلة…' : 'Adding…',
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
        <div class="gallery-thumbs" id="gallery-thumbs" role="group" aria-label="{{ $isAr ? 'صور المنتج' : 'Product images' }}">
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
      </div>

      {{-- VARIATIONS ENGINE --}}
      @php
        $varData = $variations->map(fn($v) => [
          'id'     => $v->id,
          'reg'    => (float)$v->regular_price,
          'price'  => (float)$v->price,
          'sale'   => (float)$v->sale_price,
          'model_number' => $v->model_number ?? null,
          'stock'  => (int)$v->stock_quantity,
          'can_sell' => ($v->status ?? 'publish') === 'publish' && ($v->stock_status ?? 'instock') === 'instock',
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

      @php
        $minimumOrderQty = max(1, (int) ($product->minimum_order_qty ?? 1));
        $configuredMaximumOrderQty = (int) ($product->max_orders_per_person ?? 0);
        $initialStockQty = max(0, (int) ($product->stock_quantity ?? 0));
        $maximumOrderQty = $configuredMaximumOrderQty > 0
          ? min($initialStockQty, $configuredMaximumOrderQty)
          : $initialStockQty;
        if ($product->sold_individually ?? false) $maximumOrderQty = min($maximumOrderQty, 1);
        $quantityIsOrderable = $maximumOrderQty >= $minimumOrderQty;
        $colorAttrKey = collect(array_keys($attrMap))->first(fn ($key) => strtolower($key) === 'color');
        $bulkVariations = $varData->filter(fn ($variant) => array_intersect_key($variant['attrs'], $attrMap))
          ->sortBy(fn ($variant) => $colorAttrKey ? (string) ($variant['attrs'][$colorAttrKey] ?? '') : '');
      @endphp
      @if($bulkVariations->count() > 1)
        <div class="product-order-switch">
          <button type="button" id="bulk-variant-toggle" class="bulk-variant-toggle" aria-expanded="false"
                  aria-controls="bulk-variations-panel"
                  data-single-label="{{ $isAr ? 'اختار اختيار واحد' : 'Choose one option' }}"
                  data-multiple-label="{{ $isAr ? 'اطلب أكتر من اختيار' : 'Order multiple options' }}"
                  onclick="toggleMultiVariantMode(this)">{{ $isAr ? 'اطلب أكتر من اختيار' : 'Order multiple options' }}</button>
        </div>
      @endif

      @if(!empty($attrMap))
      <div id="single-variation-options" class="pi-variations-wrap">
        @foreach($attrMap as $attrKey => $attrValues)
          @php
            $isColor = strtolower($attrKey) === 'color';
            $attrLabel = $isAr ? match(strtolower($attrKey)) {
              'color' => 'اللون', 'size' => 'المقاس', default => $attrKey,
            } : $attrKey;
          @endphp
          <div class="pi-var-group">
            <div class="var-label">
              <span>{{ $attrLabel }}<span class="var-selected-label" id="sel-{{ Str::slug($attrKey) }}"></span></span>
            </div>
            <div class="var-options" id="opts-{{ Str::slug($attrKey) }}" role="group" aria-label="{{ $attrLabel }}" aria-describedby="hint-{{ Str::slug($attrKey) }}">
              @foreach($attrValues as $val)
                @if($isColor)
                  @php $colorName = \App\Support\StorefrontLabels::color($val, $isAr); @endphp
                  <div class="var-color-option">
                    <button type="button" class="product-color-choice"
                            data-attr-key="{{ $attrKey }}" data-attr-val="{{ $val }}" data-attr-display="{{ $colorName }}"
                            aria-pressed="false" title="{{ $colorName }}" onclick="selectAttr(this.dataset.attrKey,this.dataset.attrVal)"
                            onmouseenter="previewColorImage(this.dataset.attrKey,this.dataset.attrVal)" onmouseleave="restoreImage()">
                      <span class="product-color-dot" aria-hidden="true"
                            style="--variation-color:{{ $colorCodeMap[$val] ?? 'var(--swatch-' . Str::slug($val) . ', #999)' }}"></span>
                      <span class="product-color-name">{{ $colorName }}</span>
                    </button>
                  </div>
                @else
                  <button type="button" class="var-btn" aria-pressed="false"
                          data-attr-key="{{ $attrKey }}" data-attr-val="{{ $val }}"
                          onclick="selectAttr(this.dataset.attrKey,this.dataset.attrVal)">{{ $val }}</button>
                @endif
              @endforeach
            </div>
            <div class="var-hint" id="hint-{{ Str::slug($attrKey) }}"></div>
          </div>
        @endforeach
      </div>
      @endif

      <div id="selected-model-number" class="selected-model-number" hidden aria-live="polite"></div>

      @if($bulkVariations->count() > 1)
      <div id="bulk-variations-panel" class="bulk-variations-panel" hidden>
        <p>{{ $isAr ? 'اختار التركيبات اللي عايزها وحدد الكمية لكل اختيار.' : 'Select the combinations you want and set a quantity for each.' }}</p>
        <div class="bulk-variation-list">
          @foreach($bulkVariations as $variant)
            @php
              $label = collect(array_intersect_key($variant['attrs'], $attrMap))
                ->map(fn ($value, $key) => strtolower($key) === 'color' ? \App\Support\StorefrontLabels::color($value, $isAr) : $value)
                ->implode(' · ');
              $color = $colorAttrKey ? ($variant['attrs'][$colorAttrKey] ?? null) : null;
              $orderLimit = max(0, $variant['stock']);
              if ($configuredMaximumOrderQty > 0) $orderLimit = min($orderLimit, $configuredMaximumOrderQty);
              if ($product->sold_individually ?? false) $orderLimit = min($orderLimit, 1);
              if (!$variant['can_sell']) $orderLimit = 0;
              $inCart = $cartQuantities[$variant['id']] ?? 0;
              $available = min(999, $orderLimit - $inCart) >= max(1, $minimumOrderQty - $inCart);
              $variantPrice = $variant['reg'] > 0 && $discPct > 0 && $variant['price'] >= $variant['reg']
                ? round($variant['reg'] * (1 - $discPct / 100), 2)
                : $variant['price'];
            @endphp
            <div class="bulk-variation-row" data-variant-id="{{ $variant['id'] }}" data-option-label="{{ $label }}"
                 data-order-limit="{{ $orderLimit }}">
              <label class="bulk-variation-choice">
                <input type="checkbox" @disabled(!$available) onchange="toggleBulkVariation(this)">
                @if($color !== null)
                  <span class="product-color-dot" aria-hidden="true"
                        style="--variation-color:{{ $colorCodeMap[$color] ?? 'var(--swatch-' . Str::slug($color) . ', #999)' }}"></span>
                @endif
                <span class="bulk-variation-label">
                  <strong>{{ $label }}</strong>
                  <small>{{ $orderLimit > 0 ? number_format($variantPrice, 2).' EGP' : $productText['unavailable'] }}</small>
                  <small class="bulk-cart-quantity" hidden></small>
                </span>
              </label>
              <div class="bulk-qty-stepper" hidden>
                <button type="button" aria-label="{{ $isAr ? 'قلل الكمية' : 'Decrease quantity' }}: {{ $label }}"
                        onclick="changeBulkQuantity(this,-1)">−</button>
                <input type="number" inputmode="numeric" aria-label="{{ $productText['quantity'] }}: {{ $label }}"
                       onchange="changeBulkQuantity(this,0)">
                <button type="button" aria-label="{{ $isAr ? 'زود الكمية' : 'Increase quantity' }}: {{ $label }}"
                        onclick="changeBulkQuantity(this,1)">+</button>
              </div>
            </div>
          @endforeach
        </div>
        <p id="bulk-variation-limit" hidden>{{ $isAr ? 'تقدر تضيف لحد ٢٠ اختيار في المرة.' : 'You can add up to 20 different options at a time.' }}</p>
      </div>
      @endif

      {{-- ADD TO CART --}}
      <div id="cart-selection-summary" class="cart-selection-summary" aria-live="polite" hidden>
        <div class="cart-selection-summary-head"><strong id="cart-selection-summary-title"></strong><span id="cart-selection-summary-total"></span></div>
        <div id="cart-selection-summary-content"></div>
      </div>
      <div id="product-cart-feedback" class="product-cart-feedback" role="alert" hidden>
        <ul></ul>
      </div>
      <div class="pi-cart-row">
        <div id="single-qty-controls">
          <div class="qty-input" role="group" aria-label="{{ $productText['quantity'] }}">
            <button type="button" aria-label="{{ $isAr ? 'قلل الكمية' : 'Decrease quantity' }}" onclick="changeQty(-1)">−</button>
            <input type="number" id="qty" inputmode="numeric" aria-label="{{ $productText['quantity'] }}"
                   value="{{ $quantityIsOrderable ? $minimumOrderQty : 1 }}"
                   min="{{ $minimumOrderQty }}" max="{{ max(1, $maximumOrderQty) }}" onchange="changeQty(0)">
            <button type="button" aria-label="{{ $isAr ? 'زود الكمية' : 'Increase quantity' }}"
                    data-increase-label="{{ $isAr ? 'زود الكمية' : 'Increase quantity' }}" onclick="changeQty(1)">+</button>
          </div>
        </div>
        <button type="button" class="add-to-cart-btn pi-atc-btn" id="add-to-cart-btn" {{ $quantityIsOrderable ? '' : 'disabled' }}
                onclick="handleAddToCart({{ $product->id }}, {{ \Illuminate\Support\Js::from($displayProductName) }}, {{ $product->display_price }}, {{ \Illuminate\Support\Js::from($product->thumbnail_url) }})">
          {{ $quantityIsOrderable ? $productText['addToCart'] : $productText['unavailable'] }}
        </button>
        <p id="quantity-limit-hint" hidden></p>
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
        @if($review->body)<div class="rv-review-body">{{ $review->body }}</div>@endif

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
          <form method="POST" action="{{ route('review.store') }}" id="review-form" onsubmit="return validateReviewForm()">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            {{-- Star Picker --}}
            <div class="rv-form-row">
              <label class="rv-form-label">{{ $isAr ? 'تقييمك' : 'Your Rating' }} *</label>
              <div class="rv-star-picker" id="star-picker">
                @for($s=1;$s<=5;$s++)
                  <button type="button" class="rv-star" data-val="{{ $s }}" aria-label="{{ $isAr ? $s.' نجوم' : $s.' '.($s === 1 ? 'star' : 'stars') }}" aria-pressed="false"
                        onmouseenter="hoverStar({{ $s }})"
                        onmouseleave="resetStarHover()"
                        onclick="setRating({{ $s }})">★</button>
                @endfor
              </div>
              <input type="hidden" name="rating" id="rating-input" value="{{ old('rating') }}">
              <span class="rv-star-label" id="star-label">{{ $isAr ? 'اختار تقييم' : 'Click to rate' }}</span>
              @error('rating')<span class="rv-field-err">{{ $message }}</span>@enderror
            </div>

            {{-- Body --}}
            <div class="rv-form-row">
              <label class="rv-form-label" for="review-body">{{ $isAr ? 'تعليقك (اختياري)' : 'Your comment (optional)' }}</label>
              <textarea name="body" class="rv-textarea" rows="5" id="review-body"
                        maxlength="1000"
                        placeholder="{{ $isAr ? 'إيه رأيك؟ الجودة والمقاس والسعر…' : 'What did you think? Quality, fit, value for money…' }}">{{ old('body') }}</textarea>
              <div class="rv-char-counter"><span id="char-count">{{ mb_strlen((string) old('body', '')) }}</span> / 1000</div>
              @error('body')<span class="rv-field-err">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="rv-submit" id="rv-submit-btn">
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
    <div class="product-grid cols-4 product-grid--showcase">
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
    <button type="button" class="sticky-atc-btn"
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
.product-page .gallery-wrap { flex-direction:column-reverse; align-items:stretch; gap:10px; }
.product-page .gallery-main {
  flex:0 0 auto;
  height:min(80vw, 320px);
  aspect-ratio:auto;
}
.product-page .gallery-main img { object-fit:cover; }
.product-page .gallery-thumbs {
  display:flex; flex-direction:row; width:100%; max-height:none;
  overflow-x:auto; overflow-y:hidden; overscroll-behavior-x:contain;
  -webkit-overflow-scrolling:touch; touch-action:pan-x;
  scroll-snap-type:x proximity; scrollbar-width:thin;
}
.product-page .gallery-thumb { flex-shrink:0; padding:0; color:inherit; scroll-snap-align:start; }

/* Keep the product photo balanced on tablet portrait screens. */
@media (min-width:601px) and (max-width:900px) {
  .product-page .product-layout { gap:6px; }
  .product-page .gallery-wrap { gap:6px; }
  .product-page .gallery-wrap { width:100%; max-width:none; margin-inline:0; }
  .product-page .gallery-main {
    flex:0 0 auto; width:100%; max-width:none; aspect-ratio:1 / 0.9;
    margin-inline:auto;
  }
  /* The tablet frame is wider than the source photo. Fit the complete photo
     instead of zooming/cropping it to fill the panoramic frame. */
  .product-page .gallery-main { background:var(--c-white, #fff); }
  .product-page .gallery-main img {
    object-fit:contain;
    object-position:center;
    background:var(--c-white, #fff);
  }
}

/* Keep the PC gallery at the phone-sized height without cropping or stretching
   the source photo to fill the wider desktop column. */
@media (min-width:901px) {
  .product-page .product-layout { column-gap:32px; }
  .product-page .gallery-wrap { align-items:stretch; }
  .product-page .gallery-main {
    width:80%;
    height:auto;
    max-width:none;
    aspect-ratio:auto;
    margin-inline:auto;
    background:var(--c-white, #fff);
  }
  .product-page .gallery-thumbs { width:80%; margin-inline:auto; }
  .product-page .gallery-main img {
    display:block;
    width:100%;
    height:auto;
    object-fit:contain;
    object-position:center;
    background:var(--c-white, #fff);
  }
  .product-page .pi-title-row { margin-bottom:6px; }
  .product-page .pi-desc { margin-top:8px; padding-top:8px; }
  .product-page .pi-stock { margin-bottom:4px; }
  .product-page :is(.badge-stock-ok,.badge-stock-no) { margin-bottom:8px; }
  .product-page .pi-price-block { margin-bottom:6px; }
  .product-page .pi-variations-wrap { gap:6px; margin:6px 0 4px; }
  .product-page .pi-var-group .var-label { margin-bottom:4px; }
  .product-page .bulk-variant-toggle { min-block-size:28px; }
  .product-page .pi-cart-row { gap:6px; margin-block-end:8px; }
}

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
    gap:6px;
  }
  .product-page .gallery-wrap {
    width:100%;
    gap:6px;
  }
  .product-page .product-info {
    padding-top:0;
    margin-top:0;
  }
  .product-page .gallery-main {
    width:100%;
    aspect-ratio:1 / 0.9;
    border:1px solid #e8e8e4;
    border-radius:18px;
    background:#fafaf8;
    box-shadow:0 5px 18px rgba(25,25,25,.06);
  }
  .product-page .gallery-main img {
    display:block;
    width:100%;
    height:100%;
    padding:0;
    object-fit:cover;
    background:#fff;
  }
  .product-page .gallery-thumbs {
    width:100%;
    justify-content:flex-start;
    gap:8px;
    padding:2px 2px 2px;
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
/* Related-products layout; card styling is shared with the home showcase grid. */
.related-products-section{border-top:1px solid #ece9e5;padding-top:28px}
.related-products-head{margin-bottom:20px !important}
@media(max-width:600px){
  .product-page .related-products-section{margin-top:36px !important;padding-top:18px}
  .product-page .related-products-head{align-items:center;margin-bottom:12px !important}
  .product-page .related-products-head .sec-title{font-size:18px;line-height:1.25}
  .product-page .related-products-head .sec-link{font-size:11px;white-space:nowrap}
  .product-page .product-grid--showcase{grid-template-columns:repeat(2,minmax(0,1fr)) !important;gap:10px !important}
}</style>
<script>
// ── Variation Engine ──────────────────────────────────────────────────
const VAR_DATA  = @json($varData);
const DISC_PCT  = {{ (float)($product->discount_percentage ?? 0) }};
const MIN_ORDER_QTY = {{ $minimumOrderQty }};
const CONFIGURED_MAX_ORDER_QTY = {{ $configuredMaximumOrderQty }};
const SOLD_INDIVIDUALLY = {{ ($product->sold_individually ?? false) ? 'true' : 'false' }};
const PRODUCT_STOCK_QTY = {{ $initialStockQty }};
const PRODUCT_TEXT = @json($productText);
const PRODUCT_ID = {{ (int) $product->id }};
let cartQuantities = @json((object) $cartQuantities);
const ATTR_KEYS = [...new Set(VAR_DATA.flatMap(v => Object.keys(v.attrs)).filter(key => !String(key).startsWith('_')))];
const INITIAL_GALLERY_HTML = document.getElementById('gallery-thumbs')?.innerHTML || '';
let productCartRequestPending = false;
let selectedAttrs = {};
let selectedColorValues = new Set();
let multiVariantMode = false;
let bulkSelectionStarted = false;
const BULK_VARIANT_LIMIT = 20;
const bulkSelection = new Map();
const bulkRows = new Map([...document.querySelectorAll('.bulk-variation-row')]
  .map(row => [row.dataset.variantId, row]));
let currentVariation = null;
let _lockedImgUrl = null;
const COLOR_ATTR_KEY = ATTR_KEYS.find(k => k.toLowerCase() === 'color') || null;

// Returns valid values for `key` considering only selections from keys that come
// before `key` in ATTR_KEYS order. This ensures e.g. Color is never hidden by a
// selected Size, but Size IS filtered by the selected Color.
function validValuesFor(key) {
  const keyIndex = ATTR_KEYS.indexOf(key);
  const precedingSelected = key === COLOR_ATTR_KEY ? {} : Object.fromEntries(
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

function attributeButtons(key) {
  return document.querySelectorAll(`.pi-variations-wrap button[data-attr-key="${CSS.escape(key)}"]`);
}

function attributeButton(key, value) {
  return [...attributeButtons(key)].find(button => button.dataset.attrVal === String(value));
}

function updateAvailability() {
  ATTR_KEYS.forEach(key => {
    const valid = validValuesFor(key);
    if (key !== COLOR_ATTR_KEY && selectedAttrs[key] === undefined && valid.size === 1) {
      selectedAttrs[key] = [...valid][0];
    }

    attributeButtons(key).forEach(button => {
      const value = button.dataset.attrVal;
      const isValid = valid.has(value);
      const selected = key === COLOR_ATTR_KEY ? selectedColorValues.has(value) : selectedAttrs[key] === value;
      button.classList.toggle('selected', selected);
      button.setAttribute('aria-pressed', String(selected));
      if (key === COLOR_ATTR_KEY) {
        button.closest('.var-color-option').hidden = !isValid;
        const outOfStock = !orderableVariationForColor(value);
        button.setAttribute('aria-disabled', String(outOfStock));
        button.classList.toggle('out-of-stock', outOfStock);
      } else {
        button.hidden = !isValid;
      }
    });
  });
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
    if (preferred) {
      Object.entries(main.attrs).forEach(([k, val]) => {
        if (k.toLowerCase() === 'color') return;
        const btn = attributeButton(k, val);
        if (!btn) return;
        selectedAttrs[k] = val;
        btn.classList.add('selected');
      });
      Object.entries(main.attrs).forEach(([k, val]) => {
        if (k.toLowerCase() !== 'color') return;
        const btn = attributeButton(k, val);
        if (!btn) return;
        selectedColorValues.add(val);
        btn.classList.add('selected');
      });
    }

    // Show every variation photo in the gallery as soon as the product loads.
    // The selected color only controls which photo is active in the large view.
    const colorKey = Object.keys(main.attrs).find(k => k.toLowerCase() === 'color');
    const selectedImage = main.images?.[0]
      || (colorKey ? getColorImage(colorKey, main.attrs[colorKey]) : null);
    if (selectedImage) {
      _lockedImgUrl = selectedImage;
      const img = document.getElementById('main-img');
      if (img && !img.dataset.originalSrc) img.dataset.originalSrc = img.src;
      setMainImg(selectedImage);
    }
    if (colorKey) updateVariationThumbs(colorKey, main.attrs[colorKey], selectedImage);
    currentVariation = preferred ? main : null;
    renderPriceStock(preferred ? main : null);
    updateSelectedLabels();
  } else if (VAR_DATA.length) {
    // Even without a preferred variation, keep every variation photo browseable.
    const colorKey = ATTR_KEYS.find(key => key.toLowerCase() === 'color');
    const firstColor = colorKey ? VAR_DATA.find(item => item.attrs?.[colorKey])?.attrs[colorKey] : null;
    if (colorKey && firstColor) updateVariationThumbs(colorKey, firstColor);
  }
  updateAvailability();
  tryFindVariation();
})();

function toggleMultiVariantMode(button) {
  if (productCartRequestPending) return;
  clearProductCartErrors();
  clearSelectionGuide();
  multiVariantMode = !multiVariantMode;
  button.setAttribute('aria-expanded', String(multiVariantMode));
  button.textContent = multiVariantMode ? button.dataset.singleLabel : button.dataset.multipleLabel;
  document.getElementById('single-variation-options').hidden = multiVariantMode;
  document.getElementById('bulk-variations-panel').hidden = !multiVariantMode;

  if (multiVariantMode && !bulkSelectionStarted) {
    bulkSelectionStarted = true;
    const id = String(currentVariation?.id);
    const row = bulkRows.get(id);
    if (row) {
      const { minimum, maximum } = bulkQuantityBounds(row);
      if (maximum >= minimum) {
        const qty = Number.parseInt(document.getElementById('qty').value, 10) || minimum;
        bulkSelection.set(id, Math.max(minimum, Math.min(maximum, qty)));
      }
    }
  }

  updateBulkControls();
  renderPriceStock(multiVariantMode ? null : currentVariation);
  renderSelectedModelNumber();
}

function selectAttr(key, value) {
  if (productCartRequestPending || multiVariantMode) return;
  if (key === COLOR_ATTR_KEY) {
    if (selectedColorValues.has(value)) return;
    if (!orderableVariationForColor(value)) {
      showQuantityError(PRODUCT_TEXT.variationComingSoon);
      return;
    }

    selectedColorValues = new Set([value]);
    if (!variationForColor(value)) selectedAttrs = {};

    const colorImg = getColorImage(key, value);
    if (colorImg) {
      _lockedImgUrl = colorImg;
      const img = document.getElementById('main-img');
      if (img && !img.dataset.originalSrc) img.dataset.originalSrc = img.src;
      setMainImg(colorImg, true);
      updateVariationThumbs(key, value);
    }
  } else {
    selectedAttrs[key] = value;
  }

  clearSelectionGuide();
  updateAvailability();
  tryFindVariation();
  updateSelectedLabels();
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
  renderSelectedModelNumber();
}

function renderSelectedModelNumber() {
  const el = document.getElementById('selected-model-number');
  if (!el) return;
  el.replaceChildren();
  if (multiVariantMode) {
    el.hidden = true;
    return;
  }

  const otherKeys = ATTR_KEYS.filter(key => key.toLowerCase() !== 'color');
  if (otherKeys.some(key => selectedAttrs[key] === undefined)) {
    el.hidden = true;
    return;
  }

  let selected = [];
  if (COLOR_ATTR_KEY && selectedColorValues.size) {
    selected = selectedColorVariations().map(({ value, variation }) => ({ value, variation }));
  } else if (currentVariation) {
    selected = [{ value: null, variation: currentVariation }];
  } else if (!ATTR_KEYS.length) {
    const variation = VAR_DATA.find(item => item.main) || VAR_DATA[0];
    if (variation) selected = [{ value: null, variation }];
  }

  selected = selected.filter(item => item.variation?.model_number);
  if (!selected.length) {
    el.hidden = true;
    return;
  }

  selected.forEach(({ value, variation }, index) => {
    if (index) el.appendChild(document.createTextNode(' · '));
    if (value !== null) {
      const color = document.createElement('strong');
      color.textContent = `${displayAttributeValue(COLOR_ATTR_KEY, value)}: `;
      el.appendChild(color);
    }
    const label = document.createElement('span');
    label.textContent = `${PRODUCT_TEXT.isAr ? 'رقم الموديل' : 'Model number'} `;
    const number = document.createElement('code');
    number.textContent = variation.model_number;
    el.append(label, number);
  });
  el.hidden = false;
}

function maximumOrderQuantity(stock) {
  let maximum = Math.max(0, Number(stock) || 0);
  if (CONFIGURED_MAX_ORDER_QTY > 0) maximum = Math.min(maximum, CONFIGURED_MAX_ORDER_QTY);
  if (SOLD_INDIVIDUALLY) maximum = Math.min(maximum, 1);
  return maximum;
}

function setQuantityControl(input, minimum, maximum, value) {
  const canOrder = maximum >= minimum;
  input.min = minimum;
  input.max = Math.max(minimum, maximum);
  input.value = Math.max(minimum, Math.min(maximum, Number.parseInt(value, 10) || minimum));
  input.disabled = !canOrder || productCartRequestPending;
  input.previousElementSibling.disabled = input.disabled || Number(input.value) <= minimum;
  input.nextElementSibling.disabled = input.disabled || Number(input.value) >= maximum;
}

function additionalQuantityBounds(orderLimit, variationId) {
  const inCart = Math.max(0, Number(cartQuantities[variationId]) || 0);
  return {
    inCart,
    minimum: Math.max(1, MIN_ORDER_QTY - inCart),
    maximum: Math.max(0, Math.min(999, orderLimit - inCart)),
  };
}

function singleQuantityBounds() {
  const variation = currentVariation || (!ATTR_KEYS.length ? VAR_DATA.find(option => option.main) : null);
  const stock = variation ? (variation.can_sell ? variation.stock : 0) : (ATTR_KEYS.length ? 0 : PRODUCT_STOCK_QTY);
  return additionalQuantityBounds(maximumOrderQuantity(stock), variation?.id);
}

function bulkQuantityBounds(row) {
  return additionalQuantityBounds(Number(row.dataset.orderLimit), row.dataset.variantId);
}

function cartQuantityNote({ inCart, maximum, minimum }) {
  if (PRODUCT_TEXT.isAr) {
    return maximum >= minimum ? `${inCart} في السلة · تقدر تضيف ${maximum} كمان` : `${inCart} في السلة · مش ممكن تضيف أكتر`;
  }
  return maximum >= minimum ? `${inCart} in cart · ${maximum} more can be added` : `${inCart} in cart · No more can be added`;
}

function syncQuantityBounds() {
  const input = document.getElementById('qty');
  const hint = document.getElementById('quantity-limit-hint');
  const bounds = singleQuantityBounds();
  const { minimum, maximum, inCart } = bounds;
  setQuantityControl(input, minimum, maximum, input.value);
  const plus = input.nextElementSibling;
  const canGuide = !multiVariantMode && !productCartRequestPending && Boolean(requiredSelectionControl());
  if (canGuide) plus.disabled = false;
  plus.setAttribute('aria-label', canGuide ? selectionPrompt() : plus.dataset.increaseLabel);
  hint.hidden = multiVariantMode || (!inCart && maximum < minimum);
  hint.textContent = inCart ? cartQuantityNote(bounds) : minimum > 1
    ? `${PRODUCT_TEXT.minimum} ${minimum} · ${PRODUCT_TEXT.maximum} ${maximum} ${PRODUCT_TEXT.perOrder}`
    : `${PRODUCT_TEXT.maximum} ${maximum} ${PRODUCT_TEXT.perOrder}`;
}

function quantityValidationMessage(quantity, bounds) {
  const { minimum, maximum, inCart } = bounds;
  if (quantity > maximum && inCart) return cartQuantityNote(bounds);
  if (maximum < minimum) return PRODUCT_TEXT.isAr ? `${PRODUCT_TEXT.notEnough} ${minimum}.` : `This product does not have enough stock to meet its minimum order quantity of ${minimum}.`;
  if (quantity < minimum) return `${PRODUCT_TEXT.minimum} ${minimum}.`;
  if (quantity > maximum) return `${PRODUCT_TEXT.maximum} ${maximum}.`;
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

  if (singleQty) singleQty.hidden = multiVariantMode;
  if (stockEl) stockEl.hidden = multiVariantMode || (!v && ATTR_KEYS.length > 0);
  if (v) {
    showDiscount(effectivePrice(v), v.reg > 0 ? v.reg : v.price);
    if (stockEl) stockEl.innerHTML = v.stock > 0
      ? `<span class="badge-stock-ok">✓ ${PRODUCT_TEXT.inStock} (${v.stock.toLocaleString()} ${PRODUCT_TEXT.available})</span>`
      : `<span class="badge-stock-no">${PRODUCT_TEXT.outOfStock}</span>`;
  } else {
    const orderable = VAR_DATA.filter(variant => variant.can_sell && variant.stock > 0);
    const active = orderable.length ? orderable : VAR_DATA;
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
  }
  syncQuantityBounds();
  updateAddButtonState();
}

function displayAttributeKey(key) {
  if (!PRODUCT_TEXT.isAr) return key;
  return key.toLowerCase() === 'color' ? 'اللون' : (key.toLowerCase() === 'size' ? 'المقاس' : key);
}

function displayAttributeValue(key, value) {
  const btn = attributeButton(key, value);
  return btn?.dataset.attrDisplay || value;
}

function updateCartSelectionSummary() {
  const card = document.getElementById('cart-selection-summary');
  const title = document.getElementById('cart-selection-summary-title');
  const total = document.getElementById('cart-selection-summary-total');
  const content = document.getElementById('cart-selection-summary-content');
  if (!card || !title || !total || !content) return;
  if (!multiVariantMode) {
    card.hidden = true;
    return;
  }

  content.replaceChildren();
  const items = bulkItems();
  const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
  if (!totalQty) {
    card.hidden = true;
    return;
  }

  title.textContent = PRODUCT_TEXT.isAr ? 'اللي هيتضاف للسلة' : 'What will be added to cart';
  total.textContent = PRODUCT_TEXT.isAr ? `الإجمالي: ${totalQty} قطعة` : `Total: ${totalQty} item${totalQty === 1 ? '' : 's'}`;
  items.forEach(({ variation_id, qty }) => {
    const item = document.createElement('span');
    item.className = 'cart-selection-item';
    const label = document.createElement('b');
    label.textContent = bulkRows.get(String(variation_id)).dataset.optionLabel;
    item.append(label, document.createTextNode(` × ${qty}`));
    content.appendChild(item);
  });
  card.hidden = false;
}

function updateSelectedSummary() {
  updateCartSelectionSummary();
}

function updateHints() {
  const missing = missingSelectionKey();
  ATTR_KEYS.forEach(key => {
    const hint = document.getElementById('hint-' + slugify(key));
    if (hint) hint.textContent = key === missing ? selectionPrompt(key) : '';
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

function bulkItems() {
  return Array.from(bulkSelection, ([id, qty]) => ({ variation_id: Number(id), qty }));
}

function updateBulkControls() {
  for (const id of bulkSelection.keys()) {
    const { minimum, maximum } = bulkQuantityBounds(bulkRows.get(id));
    if (maximum < minimum) bulkSelection.delete(id);
  }
  const atLimit = bulkSelection.size >= BULK_VARIANT_LIMIT;
  bulkRows.forEach((row, id) => {
    const selected = bulkSelection.has(id);
    const bounds = bulkQuantityBounds(row);
    const { inCart, minimum, maximum } = bounds;
    const checkbox = row.querySelector('input[type="checkbox"]');
    checkbox.checked = selected;
    checkbox.disabled = productCartRequestPending || maximum < minimum || (atLimit && !selected);
    const note = row.querySelector('.bulk-cart-quantity');
    note.hidden = !inCart;
    note.textContent = inCart ? cartQuantityNote(bounds) : '';
    const stepper = row.querySelector('.bulk-qty-stepper');
    stepper.hidden = !selected;
    if (selected) {
      const input = stepper.querySelector('input');
      setQuantityControl(input, minimum, maximum, bulkSelection.get(id));
      bulkSelection.set(id, Number(input.value));
    }
  });
  const limit = document.getElementById('bulk-variation-limit');
  if (limit) limit.hidden = !atLimit;
}

function toggleBulkVariation(checkbox) {
  if (!multiVariantMode || productCartRequestPending) return;
  const row = checkbox.closest('.bulk-variation-row');
  const id = row.dataset.variantId;
  const { minimum, maximum } = bulkQuantityBounds(row);
  if (!checkbox.checked) {
    bulkSelection.delete(id);
  } else if (bulkSelection.size < BULK_VARIANT_LIMIT && maximum >= minimum) {
    bulkSelection.set(id, minimum);
  }
  clearSelectionGuide();
  updateBulkControls();
  updateAddButtonState();
}

function changeBulkQuantity(control, delta) {
  if (!multiVariantMode || productCartRequestPending) return;
  const row = control.closest('.bulk-variation-row');
  const id = row.dataset.variantId;
  if (!bulkSelection.has(id)) return;
  const input = row.querySelector('.bulk-qty-stepper input');
  const { minimum, maximum } = bulkQuantityBounds(row);
  const next = (Number.parseInt(input.value, 10) || minimum) + delta;
  setQuantityControl(input, minimum, maximum, next);
  bulkSelection.set(id, Number(input.value));
  updateAddButtonState();
}

function missingSelectionKey() {
  return ATTR_KEYS.find(key => key === COLOR_ATTR_KEY ? !selectedColorValues.size : selectedAttrs[key] === undefined);
}

function selectionPrompt(key = missingSelectionKey()) {
  if (!key) return PRODUCT_TEXT.isAr ? 'اختار اختيار' : 'Select an option';
  if (PRODUCT_TEXT.isAr) return PRODUCT_TEXT.select + ' ' + displayAttributeKey(key);
  if (key === COLOR_ATTR_KEY) return 'Select a color';
  if (key.toLowerCase() === 'size') return 'Select a size';
  return 'Select ' + key;
}

function requiredSelectionControl() {
  if (multiVariantMode) {
    return bulkSelection.size ? null : document.querySelector('#bulk-variations-panel input[type="checkbox"]:enabled');
  }
  const key = missingSelectionKey();
  return key ? [...attributeButtons(key)].find(button =>
    !button.closest('[hidden]') && !button.disabled && button.getAttribute('aria-disabled') !== 'true'
  ) : null;
}

function clearSelectionGuide() {
  document.querySelector('.product-page .needs-selection')?.classList.remove('needs-selection');
}

function guideToRequiredSelection() {
  const control = requiredSelectionControl();
  if (!control) return false;
  clearSelectionGuide();
  control.closest('.pi-var-group, .bulk-variation-row').classList.add('needs-selection');
  control.focus({ preventScroll: true });
  control.scrollIntoView({
    block: 'center',
    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
  });
  return true;
}

function updateAddButtonState() {
  const items = multiVariantMode ? bulkItems() : [];
  const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
  const { inCart, minimum, maximum } = singleQuantityBounds();
  const canOrder = multiVariantMode ? items.length > 0 : maximum >= minimum;
  const canGuide = Boolean(requiredSelectionControl());
  let label = canGuide ? selectionPrompt() : PRODUCT_TEXT.unavailable;
  if (multiVariantMode && canGuide) label = PRODUCT_TEXT.isAr ? 'اختار الخيارات' : 'Select options';
  else if (!canOrder && inCart) label = PRODUCT_TEXT.isAr ? 'موجود في السلة' : 'In cart';
  if (canOrder) label = totalQty > 0 ? `${PRODUCT_TEXT.addToCart} (${totalQty})` : PRODUCT_TEXT.addToCart;
  if (productCartRequestPending) label = PRODUCT_TEXT.adding;

  [document.getElementById('add-to-cart-btn'), document.querySelector('.sticky-atc-btn')].forEach(button => {
    if (!button) return;
    button.disabled = (!canOrder && !canGuide) || productCartRequestPending;
    button.textContent = label;
    button.setAttribute('aria-busy', String(productCartRequestPending));
  });
  updateCartSelectionSummary();
}

function slugify(str) {
  return str.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function resetProductSelections() {
  clearSelectionGuide();
  selectedAttrs = {};
  selectedColorValues = new Set();
  bulkSelection.clear();
  bulkSelectionStarted = multiVariantMode;
  updateBulkControls();
  currentVariation = null;
  _lockedImgUrl = null;

  document.querySelectorAll('[data-attr-key].selected').forEach((button) => button.classList.remove('selected'));

  const gallery = document.getElementById('gallery-thumbs');
  if (gallery && INITIAL_GALLERY_HTML) {
    gallery.innerHTML = INITIAL_GALLERY_HTML;
    const defaultColor = COLOR_ATTR_KEY ? VAR_DATA.find(item => item.attrs?.[COLOR_ATTR_KEY])?.attrs[COLOR_ATTR_KEY] : null;
    if (defaultColor) updateVariationThumbs(COLOR_ATTR_KEY, defaultColor, gallery.querySelector('img')?.src || null);
  }

  const mainImage = document.getElementById('main-img');
  const originalImage = mainImage?.dataset.originalSrc;
  if (originalImage) setMainImg(originalImage, false);

  updateAvailability();
  renderPriceStock(null);
  const stockDisplay = document.getElementById('stock-display');
  if (stockDisplay) stockDisplay.innerHTML = PRODUCT_STOCK_QTY > 0
    ? `<span class="badge-stock-ok">✓ ${PRODUCT_TEXT.inStock} (${PRODUCT_STOCK_QTY.toLocaleString()} ${PRODUCT_TEXT.available})</span>`
    : `<span class="badge-stock-no">${PRODUCT_TEXT.outOfStock}</span>`;
  const quantityInput = document.getElementById('qty');
  if (quantityInput) quantityInput.value = MIN_ORDER_QTY;
  syncQuantityBounds();
  updateSelectedLabels();
  updateSelectedSummary();
  renderSelectedModelNumber();
  updateHints();
  updateAddButtonState();
}

window.productCartAddFinished = function (success, failedItems = []) {
  productCartRequestPending = false;
  if (success && multiVariantMode && failedItems.length) {
    const failedIds = new Set(failedItems.map(item => String(item.variation_id)));
    for (const id of bulkSelection.keys()) {
      if (!failedIds.has(id)) bulkSelection.delete(id);
    }
  } else {
    if (success) resetProductSelections();
  }
  updateBulkControls();
  syncQuantityBounds();
  updateAddButtonState();
};

function refreshProductQuantityControls() {
  updateBulkControls();
  syncQuantityBounds();
  updateAddButtonState();
}

window.addEventListener('cart:updated', ({ detail }) => {
  if (!Array.isArray(detail?.items)) return;
  cartQuantities = {};
  detail.items.forEach(item => {
    if (Number(item.product_id) === PRODUCT_ID && item.variation_id) {
      const id = item.variation_id;
      cartQuantities[id] = (cartQuantities[id] || 0) + (Number(item.qty) || 0);
    }
  });
  refreshProductQuantityControls();
});

window.addEventListener('pageshow', async event => {
  if (!event.persisted) return;
  try {
    const response = await fetch(`/cart/count?product_id=${PRODUCT_ID}`, { headers: { Accept: 'application/json' } });
    if (!response.ok) return;
    const data = await response.json();
    cartQuantities = data.quantities || {};
    refreshProductQuantityControls();
  } catch {
    // A failed refresh leaves the last known quantities in place.
  }
});

function clearProductCartErrors() {
  const feedback = document.getElementById('product-cart-feedback');
  feedback.hidden = true;
  feedback.querySelector('ul').replaceChildren();
}

window.showProductCartErrors = function (messages) {
  if (!productCartRequestPending) return false;
  const feedback = document.getElementById('product-cart-feedback');
  const list = feedback.querySelector('ul');
  list.replaceChildren();
  messages.forEach(message => {
    const item = document.createElement('li');
    item.textContent = message;
    list.appendChild(item);
  });
  feedback.hidden = false;
  return true;
};

// ── Cart integration ──────────────────────────────────────────────────
function handleAddToCart(id, name, basePrice, image) {
  if (productCartRequestPending) return;
  clearProductCartErrors();
  if (guideToRequiredSelection()) return;
  if (multiVariantMode) {
    const items = bulkItems();
    if (!items.length) {
      showQuantityError(PRODUCT_TEXT.isAr ? 'اختار التركيبات اللي عايزها' : 'Select the options you want to order.');
      return;
    }
    productCartRequestPending = true;
    updateBulkControls();
    updateAddButtonState();
    addMultipleToCart(id, items);
    return;
  }

  if (ATTR_KEYS.length > 0 && !currentVariation) return;

  const qtyInput = document.getElementById('qty');
  const bounds = singleQuantityBounds();
  const qty = Number.parseInt(qtyInput?.value, 10) || bounds.minimum;
  const quantityError = quantityValidationMessage(qty, bounds);
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
  syncQuantityBounds();
  updateAddButtonState();
  addToCart(id, name, price, currentVariation?.images?.[0] || image, varId, qty, varLabel, oldPrice);
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

function updateVariationThumbs(attrKey, attrVal, selectedImageUrl = null) {
  const strip = document.getElementById('gallery-thumbs');
  if (!strip) return;
  // Start with the product gallery, then append every unique variation photo.
  // This keeps all color photos available in the swipeable strip while selecting
  // a color still jumps the large image to that color's first photo.
  strip.innerHTML = INITIAL_GALLERY_HTML;
  const seen = new Set([...strip.querySelectorAll('img')].map(img => new URL(img.src, document.baseURI).href));

  VAR_DATA.forEach(variation => {
    const colorValue = variation.attrs?.[attrKey];
    (variation.images || []).forEach((url, index) => {
      if (!url) return;
      const absoluteUrl = new URL(url, document.baseURI).href;
      if (seen.has(absoluteUrl)) return;
      seen.add(absoluteUrl);

      const thumb = document.createElement('button');
      thumb.type = 'button';
      thumb.className = 'gallery-thumb';
      thumb.setAttribute('aria-label', `${colorValue || attrVal} image ${index + 1}`);
      thumb.addEventListener('click', () => {
        _lockedImgUrl = url;
        setMainImg(url, true);
        strip.querySelectorAll('.gallery-thumb').forEach(item => item.classList.remove('active'));
        thumb.classList.add('active');
      });

      const image = document.createElement('img');
      image.src = url;
      image.alt = `${colorValue || attrVal} ${index + 1}`;
      image.loading = 'lazy';
      image.onerror = () => handleThumbError(image);
      thumb.appendChild(image);
      strip.appendChild(thumb);
    });
  });

  const activeUrl = selectedImageUrl || getColorImage(attrKey, attrVal);
  if (activeUrl) {
    const activeAbsoluteUrl = new URL(activeUrl, document.baseURI).href;
    const activeThumb = [...strip.querySelectorAll('.gallery-thumb')].find(thumb => {
      const image = thumb.querySelector('img');
      return image && new URL(image.src, document.baseURI).href === activeAbsoluteUrl;
    });
    strip.querySelectorAll('.gallery-thumb').forEach(item => item.classList.toggle('active', item === activeThumb));
    activeThumb?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
  }
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
  if (productCartRequestPending) return;
  if (delta > 0 && guideToRequiredSelection()) return;
  const input = document.getElementById('qty');
  if (!input || input.disabled) return;
  const minimum = Number.parseInt(input.min, 10) || MIN_ORDER_QTY;
  const maximum = Number.parseInt(input.max, 10) || minimum;
  setQuantityControl(input, minimum, maximum, (Number.parseInt(input.value, 10) || minimum) + delta);
}

// ── Star rating picker ────────────────────────────────────────────────
let _selectedRating = 0;
const starLabels = PRODUCT_TEXT.isAr ? ['', 'سيئ جدًا', 'سيئ', 'مقبول', 'جيد', 'ممتاز'] : ['', 'Terrible', 'Poor', 'OK', 'Good', 'Excellent'];

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
    s.setAttribute('aria-pressed', String(i + 1 === val));
  });
  const lbl = document.getElementById('star-label');
  if (lbl) { lbl.textContent = starLabels[val] || ''; lbl.style.color = ''; }
}
if (Number(document.getElementById('rating-input')?.value) >= 1 && Number(document.getElementById('rating-input')?.value) <= 5) {
  setRating(Number(document.getElementById('rating-input').value));
}

// ── Review form validation ─────────────────────────────────────────────
function validateReviewForm() {
  if (!_selectedRating) {
    const lbl = document.getElementById('star-label');
    if (lbl) { lbl.textContent = PRODUCT_TEXT.isAr ? 'اختار عدد النجوم' : 'Please pick a rating!'; lbl.style.color = '#e85d26'; }
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
.pi-stock { margin-bottom: 10px; }

/* Price block */
.pi-price-block { margin-bottom: 10px; }
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
/* Variations wrapper */
.pi-variations-wrap { margin:12px 0 10px; display:grid; gap:8px; }
.selected-model-number{margin:0 0 10px;padding:7px 10px;border:1px solid #eee8e2;border-radius:8px;background:#faf8f6;color:#625b55;font-size:12px;line-height:1.5}
.selected-model-number strong{color:#282522;font-weight:650}.selected-model-number code{font-family:inherit;font-size:12px;font-weight:600;color:#e85d26}
.pi-var-group { margin:0; padding:0 0 8px; border-bottom:1px solid #eeeae5; }
.pi-var-group:last-child { border-bottom:0; padding-bottom:0; }
.pi-var-group .var-label { display:flex; align-items:center; gap:6px; margin-bottom:7px; font-size:13px; font-weight:800; color:#292524; }
.pi-var-group .var-selected-label { color:var(--c-orange, #e85d26); font-weight:700; }
.var-color-name { max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:11px; font-weight:700; color:#57534e; }
.pi-var-group .var-btn { min-width:46px; height:38px; padding:0 14px; border-radius:10px; }

/* Purchase controls */
.product-page .pi-variations-wrap{
  display:grid;grid-template-columns:minmax(0,1fr);gap:12px;
  margin:14px 0;padding:0;border:0;background:none;
}
.product-page .pi-var-group{min-inline-size:0;margin:0;padding:0;border:0}
.product-page .var-label{
  display:flex;align-items:baseline;justify-content:space-between;flex-wrap:wrap;
  gap:4px 10px;margin:0 0 6px;font-size:.75rem;font-weight:800;
}
.product-page .var-selected-label{color:var(--c-mid);font-weight:650;text-transform:none}
.product-page .var-options{display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;margin:0}
.product-page .var-color-option{display:grid;min-inline-size:0;max-inline-size:100%;gap:5px}
.product-page :is(.product-color-choice,.var-btn){
  display:inline-flex;align-items:center;justify-content:center;gap:7px;
  min-inline-size:44px;min-block-size:44px;height:auto;max-inline-size:100%;padding:6px 12px;
  border:1px solid #e5e2df;border-radius:10px;background:#fff;color:#292524;
  font:inherit;font-size:.875rem;font-weight:650;line-height:1.25;overflow-wrap:anywhere;cursor:pointer;
}
.product-page :is(.product-color-choice,.var-btn).selected{border-color:#1a1a1a;box-shadow:inset 0 0 0 1px #1a1a1a;background:#f7f7f6}
.product-page .product-color-choice.out-of-stock{opacity:.48;cursor:not-allowed}
.product-page .product-color-choice{inline-size:auto}
.product-page .product-color-name{
  min-inline-size:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.product-page .product-color-dot{
  flex:none;inline-size:22px;block-size:22px;border-radius:50%;
  background:var(--variation-color);box-shadow:inset 0 0 0 1px #0002;
}
.product-page .var-hint{margin-block-start:4px;color:var(--c-mid);font-size:.75rem}
.product-page .var-hint:empty{display:none}
.product-page .needs-selection{
  outline:2px solid var(--c-orange);outline-offset:4px;border-radius:8px;
}
.product-page .needs-selection .var-hint{color:#9f3d18;font-weight:700}
.product-page .bulk-variant-toggle{
  min-block-size:36px;padding:0;border:0;background:none;color:var(--c-mid);
  font:inherit;font-size:.75rem;text-transform:none;text-decoration:underline;cursor:pointer;
}
.product-page .bulk-variant-toggle[aria-expanded="true"]{color:var(--c-dark);font-weight:800}
.product-page .product-order-switch{text-align:end}
.product-page .bulk-variations-panel{margin:4px 0 12px}
.product-page .bulk-variations-panel p{margin:0 0 8px;color:var(--c-mid);font-size:.8125rem}
.product-page .bulk-variation-list{display:grid;gap:6px}
.product-page .bulk-variation-row{
  display:flex;align-items:center;flex-wrap:wrap;gap:0 8px;padding:0 8px;
  border:1px solid #e5e2df;border-radius:10px;
}
.product-page .bulk-variation-row:has(:checked){border-color:#292524;background:#faf9f8}
.product-page .bulk-variation-choice{
  display:flex;flex:1 1 110px;align-items:center;gap:8px;min-inline-size:0;
  min-block-size:52px;cursor:pointer;
}
.product-page .bulk-variation-choice input{margin:0;inline-size:18px;block-size:18px;flex:none;accent-color:#171717}
.product-page .bulk-variation-label{display:grid;min-inline-size:0;overflow-wrap:anywhere;font-size:.875rem;line-height:1.3}
.product-page .bulk-variation-label small{color:var(--c-mid);font-size:.75rem}
.product-page .bulk-variation-choice:has(:disabled):not(:has(:checked)){color:var(--c-mid)}
.product-page .bulk-qty-stepper{margin-block:4px}
.product-page .bulk-qty-stepper,.product-page .pi-cart-row .qty-input{
  display:grid;grid-template-columns:44px minmax(32px,1fr) 44px;align-items:center;
  inline-size:124px;min-block-size:52px;gap:0;
  border:1px solid #dedbd7;border-radius:11px;background:#f9f8f7;
}
.product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) :is(button,input){
  display:grid;place-items:center;inline-size:100%;block-size:50px;min-inline-size:0;
  border:0;padding:0;background:transparent;color:#292524;text-align:center;font:inherit;
}
.product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) button{font-size:1.25rem;cursor:pointer}
.product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) button:disabled{opacity:.4;cursor:default}
.product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) input{font-weight:750;appearance:textfield}
.product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) input::-webkit-inner-spin-button,
.product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) input::-webkit-outer-spin-button{appearance:none;margin:0}
.product-page :is(.pi-variations-wrap,.pi-cart-row,.bulk-variations-panel,.product-order-switch) :is(button,input):focus-visible{
  outline:2px solid var(--c-orange);outline-offset:-2px;
}
.product-page :is(.var-btn,.var-color-option,.bulk-qty-stepper,.bulk-variations-panel,.pi-variations-wrap,#single-qty-controls,#quantity-limit-hint,.cart-selection-summary)[hidden]{
  display:none;
}
.product-page .product-cart-feedback[hidden]{display:none}
.product-page .product-cart-feedback{
  margin-block-end:8px;padding:9px 12px;border:1px solid #efc8bd;border-radius:9px;
  background:#fff5f2;color:#7d291c;font-size:.8125rem;line-height:1.45;overflow-wrap:anywhere;
}
.product-page .product-cart-feedback ul{margin:0;padding-inline-start:18px}
.product-page .product-cart-feedback li + li{margin-block-start:4px}
.product-page .cart-selection-summary{
  margin-block-end:8px;padding:8px 10px;border-radius:9px;background:#f7f7f6;font-size:.8125rem;
}
.cart-selection-summary-head{display:flex;flex-wrap:wrap;justify-content:space-between;gap:4px 10px}
#cart-selection-summary-content{display:flex;flex-wrap:wrap;gap:3px 10px;color:var(--c-mid)}
.cart-selection-item{overflow-wrap:anywhere}
.product-page .pi-cart-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-block-end:12px}
.product-page .pi-cart-row #single-qty-controls{flex:none}
.product-page .pi-cart-row .pi-atc-btn{
  flex:1 1 126px;min-inline-size:0;min-block-size:52px;margin:0;padding:8px;
  border-radius:11px;font-size:.9375rem;line-height:1.2;white-space:normal;
}
.product-page .pi-cart-row #quantity-limit-hint{flex:1 0 100%;margin:0;color:var(--c-mid);font-size:.75rem}

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
  font-size: 32px; cursor: pointer; color: #e0e0e0; background: transparent; border: 0; padding: 0; line-height: 1;
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

/* Compact the mobile product controls while preserving their layout. */
@media(max-width:600px) {
  .product-page .pi-title { font-size:16.8px; }
  .product-page .pi-wish-btn { width:34px; height:34px; font-size:16px; }
  .product-page .pi-desc { font-size:11.2px; padding-top:16px; margin-top:16px; }
  .product-page .pi-rating-row { gap:5px; margin-bottom:10px; }
  .product-page :is(.pi-star-filled,.pi-star-empty) { font-size:12px; }
  .product-page :is(.pi-rating-none,.pi-rating-count) { font-size:10.4px; }
  .product-page .pi-stock { margin-bottom:8px; }
  .product-page :is(.badge-stock-ok,.badge-stock-no) { font-size:9.6px; padding:3px 10px; margin-bottom:14px; }
  .product-page .pi-price-main { font-size:20px; }
  .product-page .pi-price-orig { font-size:11.2px; }
  .product-page .pi-price-row { gap:8px; }
  .product-page .pi-disc-badge { font-size:9.6px; padding:2px 7px; }
  .product-page .pi-variations-wrap { gap:10px; margin:11px 0 8px; }
  .product-page .var-label { font-size:.6rem; margin-bottom:5px; }
  .product-page .var-options { gap:6px; }
  .product-page :is(.product-color-choice,.var-btn) {
    min-inline-size:36px; min-block-size:36px; padding:5px 10px;
    gap:6px; border-radius:8px; font-size:11.2px;
  }
  .product-page .product-color-dot { inline-size:18px; block-size:18px; }
  .product-page .var-hint { font-size:.6rem; }
  .product-page .bulk-variant-toggle { min-block-size:29px; font-size:.6rem; }
  .product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) {
    inline-size:100px; min-block-size:42px; grid-template-columns:35px minmax(26px,1fr) 35px;
  }
  .product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) :is(button,input) { block-size:40px; }
  .product-page :is(.bulk-qty-stepper,.pi-cart-row .qty-input) button { font-size:1rem; }
  .product-page .pi-cart-row { gap:6px; margin-block-end:10px; }
  .product-page .pi-cart-row .pi-atc-btn {
    min-block-size:42px; padding:6px; border-radius:9px; font-size:.75rem;
  }
  .product-page .pi-cart-row #quantity-limit-hint { font-size:.6rem; }
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

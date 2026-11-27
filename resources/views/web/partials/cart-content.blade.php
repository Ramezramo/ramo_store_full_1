@php
  $cartRtl = session('locale', 'en') === 'ar';
  $itemCount = count($cart);
  $afterDiscount = max(0, (float) $subtotal - (float) $discount);
  $freeShippingProgress = ($freeShippingEnabled && $freeShippingThreshold > 0)
      ? min(100, ($afterDiscount / $freeShippingThreshold) * 100)
      : 100;
  $freeShippingRemaining = max(0, $freeShippingThreshold - $afterDiscount);
  $checkoutUrl = auth()->check() ? route('checkout') : route('login', ['checkout' => 1]);
  $hasUnavailableItems = collect($cart)->contains(fn ($item) => !empty($item['is_unavailable']));
@endphp
<div class="cart-screen" dir="{{ $cartRtl ? 'rtl' : 'ltr' }}" data-free-shipping-enabled="{{ $freeShippingEnabled ? '1' : '0' }}" data-free-shipping-threshold="{{ $freeShippingThreshold }}" data-discount="{{ $discount }}">
  <div id="cart-request-overlay" class="cart-request-overlay" hidden role="status" aria-live="polite">
    <div class="cart-request-dialog">
      <div class="cart-request-spinner" aria-hidden="true"></div>
      <p>{{ $cartRtl ? 'جاري تحديث السلة…' : 'Updating your cart…' }}</p>
    </div>
  </div>
  <header class="cart-screen-header">
    <a href="{{ route('shop') }}" class="cart-screen-back" aria-label="{{ $cartRtl ? 'ارجع' : 'Go back' }}">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="cart-screen-title">{{ $cartRtl ? 'سلتك' : 'Your Cart' }} <span>({{ $itemCount }} {{ $cartRtl ? ($itemCount === 1 ? 'منتج' : 'منتجات') : ($itemCount === 1 ? 'item' : 'items') }})</span></h1>
  </header>

  <main class="cart-screen-body">
    @if(session('error') && !session('coupon_error'))
      <div class="alert-box alert-err">{{ session('error') }}</div>
    @endif
    @if(session('success'))
      <div class="alert-box alert-ok">{{ session('success') }}</div>
    @endif

    @if(empty($cart))
      <section class="cart-empty-state" aria-live="polite">
        <div class="cart-empty-icon" aria-hidden="true">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
        </div>
        <h2>{{ $cartRtl ? 'سلتك فاضية' : 'Your cart is empty' }}</h2>
        @auth
          @if($hasPreviousOrders)
            <p>{{ $cartRtl ? 'عندك أوردرات سابقة؟ شوف آخر تحديثاتها من هنا.' : 'Have previous orders? See their latest updates here.' }}</p>
          @else
            <p>{{ $cartRtl ? 'أول أوردر ليك؟ اختار حاجة تعجبك وابدأ تجربتك مع رامو.' : 'Ready for your first order? Find something you love and start your Ramo experience.' }}</p>
          @endif
        @else
          <p>{{ $cartRtl ? 'اختار اللي يعجبك وهتلاقيه هنا.' : 'Find something you love and it will appear here.' }}</p>
        @endauth
        <div class="cart-empty-actions">
          <a href="{{ route('shop') }}" class="btn btn-dark">{{ $cartRtl ? 'كمّل تسوّق' : 'Continue Shopping' }}</a>
          @auth
            @if($hasPreviousOrders)
              <a href="{{ route('account.orders') }}" class="btn cart-empty-order-link">{{ $cartRtl ? 'شوف أوردراتك' : 'View Your Orders' }}</a>
            @endif
          @else
            <a href="{{ route('order.track') }}" class="btn cart-empty-order-link">{{ $cartRtl ? 'تابع طلبك' : 'Track Your Order' }}</a>
          @endauth
        </div>
      </section>
    @else
      <div class="cart-screen-grid">
        <section class="cart-items-panel" aria-labelledby="cart-items-heading">
                        <div class="cart-items-heading">

            <h2 id="cart-items-heading">{{ $cartRtl ? 'المنتجات اللي في سلتك' : 'Items in your cart' }}</h2>
            <span>{{ $itemCount }} {{ $cartRtl ? ($itemCount === 1 ? 'منتج' : 'منتجات') : ($itemCount === 1 ? 'item' : 'items') }}</span>
          </div>

          <div id="cart-items-wrap">
            @foreach($cart as $rowId => $item)
              <article class="cart-item-card{{ !empty($item['is_unavailable']) ? ' is-unavailable' : '' }}" id="row-{{ $rowId }}" data-row-id="{{ $rowId }}" data-unit-price="{{ $item['price'] }}" data-out-of-stock="{{ !empty($item['is_unavailable']) ? '1' : '0' }}">
                <a href="{{ route('product', $item['product_id']) }}" class="cart-item-thumb" aria-label="{{ $cartRtl ? 'عرض' : 'View' }} {{ $item['display_name'] ?? $item['name'] }}">
                  @if(!empty($item['image']))
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                  @else
                    <span class="cart-item-placeholder" aria-hidden="true">👕</span>
                  @endif
                </a>
                <div class="cart-item-main">
                  <a href="{{ route('product', $item['product_id']) }}" class="cart-item-name">{{ $item['display_name'] ?? $item['name'] }}</a>
                  @if(!empty($item['is_unavailable']))
                    <div class="cart-item-unavailable" role="status">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5M12 16h.01"/></svg>
                      {{ $cartRtl ? 'المنتج غير متوفر' : 'Out of stock' }}
                    </div>
                  @endif
                  @if(!empty($item['attrs']) || !empty($item['sku']) || !empty($item['model_number']))
                    <div class="cart-item-variants" aria-label="{{ $cartRtl ? 'الاختيارات المحددة' : 'Selected options' }}">
                      @if(!empty($item['sku']))<span class="cart-item-variant cart-item-reference"><strong>{{ $cartRtl ? 'كود المنتج' : 'SKU' }}</strong> {{ $item['sku'] }}</span>@endif
                      @if(!empty($item['model_number']))<span class="cart-item-variant cart-item-reference"><strong>{{ $cartRtl ? 'رقم الموديل' : 'Model number' }}</strong> {{ $item['model_number'] }}</span>@endif
                      @foreach(\App\Support\ProductAttributes::visible($item['attrs'] ?? []) as $k => $v)
                        @php
                          $attributeLabel = $cartRtl ? (['color' => 'اللون', 'size' => 'المقاس'][strtolower($k)] ?? $k) : ucfirst($k);
                          $attributeValue = $cartRtl ? (['black' => 'أسود', 'white' => 'أبيض', 'nude' => 'بيج', 'navy' => 'كحلي', 'red' => 'أحمر', 'blue' => 'أزرق', 'grey' => 'رمادي', 'gray' => 'رمادي', 'brown' => 'بني', 'khaki' => 'كاكي', 'sand' => 'رملي', 'green' => 'أخضر', 'pink' => 'وردي'][strtolower($v)] ?? $v) : $v;
                        @endphp
                        <span class="cart-item-variant"><strong>{{ $attributeLabel }}</strong> {{ $attributeValue }}</span>
                      @endforeach
                    </div>
                  @endif
                  <div class="cart-item-pricing">
                    <span class="cart-item-unit">{{ number_format($item['price'], 2) }} EGP {{ $cartRtl ? 'للقطعة' : 'each' }}</span>
                    <strong class="cart-item-line" id="sub-{{ $rowId }}">{{ number_format($item['price'] * $item['qty'], 2) }} EGP</strong>
                    <span class="cart-item-old" id="sub-old-{{ $rowId }}" style="{{ (!empty($item['regular_price']) && $item['regular_price'] > $item['price']) ? '' : 'display:none' }}">{{ !empty($item['regular_price']) ? number_format($item['regular_price'] * $item['qty'], 2) . ' EGP' : '' }}</span>
                  </div>
                  @if(!empty($item['sku']) || !empty($item['model_number']))
                    <details class="cart-item-references">
                      <summary>{{ $cartRtl ? 'تفاصيل المنتج' : 'Product details' }}</summary>
                      @if(!empty($item['sku']))<p>SKU: {{ $item['sku'] }}</p>@endif
                      @if(!empty($item['model_number']))<p>{{ $cartRtl ? 'رقم الموديل' : 'Model number' }}: {{ $item['model_number'] }}</p>@endif
                    </details>
                  @endif
                </div>
                <div class="cart-item-controls">
                  @php
                    $itemMinimum = (int) ($item['minimum_qty'] ?? 1);
                    $itemMaximum = max($itemMinimum, (int) ($item['maximum_qty'] ?? $item['stock']));
                  @endphp
                  <div class="cart-qty-stepper{{ !empty($item['is_unavailable']) ? ' is-disabled' : '' }}" aria-label="{{ $cartRtl ? 'الكمية لـ' : 'Quantity for' }} {{ $item['display_name'] ?? $item['name'] }}">
                    <form method="POST" action="{{ route('cart.update', $rowId) }}" class="cart-qty-form" data-cart-request>
                      @csrf
                      <input type="hidden" name="qty" value="{{ max($itemMinimum, $item['qty'] - 1) }}">
                      <button type="submit" aria-label="{{ $cartRtl ? 'قلّل الكمية' : 'Decrease quantity' }}" @disabled(!empty($item['is_unavailable']) || $item['qty'] <= $itemMinimum)>−</button>
                    </form>
                    <span class="cart-qty-value" aria-label="{{ $cartRtl ? 'الكمية' : 'Quantity' }}">{{ $item['qty'] }}</span>
                    <form method="POST" action="{{ route('cart.update', $rowId) }}" class="cart-qty-form" data-cart-request>
                      @csrf
                      <input type="hidden" name="qty" value="{{ min($itemMaximum, $item['qty'] + 1) }}">
                      <button type="submit" aria-label="{{ $cartRtl ? 'زوّد الكمية' : 'Increase quantity' }}" @disabled(!empty($item['is_unavailable']) || $item['qty'] >= $itemMaximum)>+</button>
                    </form>
                  </div>
                  <form method="POST" action="{{ route('cart.remove', $rowId) }}" class="cart-remove-form" data-cart-request>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="cart-remove-icon{{ !empty($item['is_unavailable']) ? ' is-priority' : '' }}" aria-label="{{ $cartRtl ? 'شيل' : 'Remove' }} {{ $item['display_name'] ?? $item['name'] }}" title="{{ $cartRtl ? 'شيل المنتج' : 'Remove item' }}">
                      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                  </form>
                </div>
              </article>
            @endforeach
          </div>

          <div class="cart-items-actions">
            <a href="{{ route('shop') }}" class="btn btn-outline">{{ $cartRtl ? 'كمّل تسوّق →' : '← Continue Shopping' }}</a>
            <form action="{{ route('cart.clear') }}" method="POST" data-cart-request>
              @csrf @method('DELETE')
              <button type="submit" class="cart-clear-btn" onclick="return confirm('{{ $cartRtl ? 'عايز تفضّي السلة كلها؟' : 'Clear entire cart?' }}')">{{ $cartRtl ? 'فضّي السلة' : 'Clear Cart' }}</button>
            </form>
          </div>
        </section>

        <aside class="cart-summary-panel" aria-labelledby="cart-summary-heading">
          <div class="cart-summary-head">
            <h2 id="cart-summary-heading">{{ $cartRtl ? 'ملخص الطلب' : 'Order Summary' }}</h2>
            <span class="cart-summary-count">{{ $itemCount }} {{ $cartRtl ? ($itemCount === 1 ? 'منتج' : 'منتجات') : ($itemCount === 1 ? 'item' : 'items') }}</span>
          </div>

          @if($freeShippingEnabled)
            <div class="cart-shipping-progress" id="cart-shipping-progress" aria-live="polite">
              <div class="cart-shipping-copy">
                <span id="cart-shipping-message">
                  @if($freeShippingRemaining > 0)
                    {{ $cartRtl ? 'زوّد' : 'Add' }} <strong>{{ number_format($freeShippingRemaining, 2) }} EGP</strong> {{ $cartRtl ? 'جنيه عشان الشحن يبقى مجاني' : 'more for free shipping' }}
                  @else
                    <span class="cart-shipping-done">{{ $cartRtl ? 'الشحن بقى مجاني ليك.' : 'You unlocked free shipping.' }}</span>
                  @endif
                </span>
                <strong id="cart-shipping-percent">{{ round($freeShippingProgress) }}%</strong>
              </div>
              <div class="cart-shipping-bar"><div id="cart-shipping-fill" class="cart-shipping-fill" style="width:{{ $freeShippingProgress }}%"></div></div>
            </div>
          @endif

          @php
            $couponError = session('coupon_error');
            $couponErrorCode = session('coupon_error_code');
          @endphp
          <div class="cart-coupon-widget" aria-live="polite">
            @if($couponError)
              <div class="cart-coupon-error" role="alert">
                <div class="cart-coupon-error-title">
                  <span>{{ $cartRtl ? 'مشكلة في الكوبون' : 'Coupon issue' }}</span>
                  @if($couponErrorCode)<strong>{{ strtoupper($couponErrorCode) }}</strong>@endif
                </div>
                <p>{{ $couponError }}</p>
              </div>
            @endif
            @if($coupon)
              <div class="cart-applied-coupon">
                <span>{{ !empty($coupon['free_shipping']) && $cartRtl ? 'كوبون توصيل مجاني' : (!empty($coupon['free_shipping']) ? 'Free shipping coupon' : ($cartRtl ? 'كود الخصم' : 'Coupon')) }} <strong>{{ strtoupper($coupon['code']) }}</strong> {{ $cartRtl ? 'اتطبّق' : 'applied' }}</span>
                <form action="{{ route('cart.coupon.remove') }}" method="POST" data-cart-request>@csrf @method('DELETE')<button type="submit">{{ $cartRtl ? 'شيل' : 'Remove' }}</button></form>
              </div>
            @else
              <details class="cart-promo" @if($couponError) open @endif>
                <summary>{{ $cartRtl ? 'معاك كود خصم؟' : 'Have a promo code?' }}</summary>
                <form class="cart-promo-form" action="{{ route('cart.coupon') }}" method="POST" data-cart-request>
                  @csrf
                  <input type="text" name="code" value="{{ $couponErrorCode }}" placeholder="{{ $cartRtl ? 'اكتب كود الخصم' : 'Enter promo code' }}" autocomplete="off" required>
                  <button type="submit">{{ $cartRtl ? 'طبّق' : 'Apply' }}</button>
                </form>
              </details>
            @endif
          </div>

          <div class="cart-summary-row"><span>{{ $cartRtl ? 'الإجمالي الفرعي' : 'Subtotal' }}</span><strong id="cart-subtotal">{{ number_format($subtotal, 2) }} EGP</strong></div>
          @if($coupon && $discount > 0)
            <div class="cart-summary-row cart-discount"><span>{{ $cartRtl ? 'الخصم' : 'Discount' }}</span><strong id="cart-discount">−{{ number_format($discount, 2) }} EGP</strong></div>
          @elseif($coupon && !empty($coupon['free_shipping']))
            <div class="cart-summary-row cart-discount"><span>{{ $cartRtl ? 'ميزة الكوبون' : 'Coupon benefit' }}</span><strong id="cart-discount">{{ $cartRtl ? 'توصيل مجاني' : 'Free shipping' }}</strong></div>
          @endif
          <div class="cart-summary-row"><span>{{ $cartRtl ? 'الشحن' : 'Shipping' }}</span><strong id="cart-shipping" class="{{ $shippingFee == 0 ? 'summary-shipping-free' : '' }}">{{ $shippingFee > 0 ? number_format($shippingFee, 2) . ' EGP' : ($cartRtl ? 'مجاني' : 'Free') }}</strong></div>
          <div class="cart-summary-row"><span>{{ $cartRtl ? 'الضرايب' : 'Tax' }}</span><strong style="color:var(--c-mid)">{{ $cartRtl ? 'هيتحدد' : 'TBA' }}</strong></div>
          <hr class="cart-summary-divider">
          <div class="cart-total-row"><span>{{ $cartRtl ? 'الإجمالي' : 'Total' }}</span><strong id="cart-total">{{ number_format($total, 2) }} EGP</strong></div>
          <p class="cart-summary-note">{{ $cartRtl ? 'الضرايب وتفاصيل التوصيل النهائية بتتأكد وقت إتمام الطلب.' : 'Final taxes and delivery details are confirmed during checkout.' }}</p>
          <div class="cart-checkout-warning" data-cart-checkout-warning @if(!$hasUnavailableItems) hidden @endif>{{ $cartRtl ? 'فيه منتج غير متوفر. شيله من السلة عشان تقدر تكمل الطلب.' : 'One or more items are out of stock. Remove them from your cart to continue.' }}</div>
          <a @unless($hasUnavailableItems) href="{{ $checkoutUrl }}" @endunless class="cart-checkout-button cart-summary-checkout{{ $hasUnavailableItems ? ' is-blocked' : '' }}" data-cart-checkout @if($hasUnavailableItems) aria-disabled="true" tabindex="-1" @endif>{{ $cartRtl ? 'إتمام الطلب' : 'Checkout' }} <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </aside>
      </div>

      <div class="cart-checkout-bar">
        <div class="cart-checkout-warning cart-checkout-warning-bar" data-cart-checkout-warning @if(!$hasUnavailableItems) hidden @endif>{{ $cartRtl ? 'فيه منتج غير متوفر. شيله من السلة عشان تقدر تكمل الطلب.' : 'Remove out-of-stock items to continue.' }}</div>
        <div class="cart-checkout-total"><span>{{ $cartRtl ? 'الإجمالي' : 'Total' }}</span><strong id="cart-sticky-total">{{ number_format($total, 2) }} EGP</strong></div>
        <a @unless($hasUnavailableItems) href="{{ $checkoutUrl }}" @endunless class="cart-checkout-button{{ $hasUnavailableItems ? ' is-blocked' : '' }}" data-cart-checkout @if($hasUnavailableItems) aria-disabled="true" tabindex="-1" @endif>{{ $cartRtl ? 'إتمام الطلب' : 'Checkout' }} <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      </div>
    @endif
  </main>
</div>

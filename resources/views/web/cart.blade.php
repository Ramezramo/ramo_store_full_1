@extends('layouts.app')
@section('title', 'Cart — Ramo Store')

@section('content')
<div id="cart-loading-overlay" class="cart-loading-overlay"><div class="cart-spinner"></div></div>
<div class="page">

  @if(session('error'))
    <div class="alert-box alert-err">{{ session('error') }}</div>
  @endif

  @if(empty($cart))
    <div class="empty" style="padding:100px 20px">
      <div class="empty-icon">🛒</div>
      <h3>Your cart is empty</h3>
      <p>Looks like you haven't added anything yet.</p>
      <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:24px">Start Shopping</a>
    </div>
  @else
  <a href="{{ route('shop') }}" class="cart-back-link">← Back</a>
  <div class="cart-title-row">
    <h1 class="cart-title">Your Cart</h1>
    <span class="cart-count-badge">{{ count($cart) }} Item{{ count($cart) === 1 ? '' : 's' }}</span>
  </div>

  <div class="cart-layout">

    {{-- CART ITEMS --}}
    <div id="cart-items-wrap">

      @foreach($cart as $rowId => $item)
      <div class="cart-row" id="row-{{ $rowId }}">

        {{-- Trash / remove button — top-right corner --}}
        <button class="cart-remove-btn" onclick="removeItem('{{ $rowId }}')" title="Remove item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </button>

        <div class="cart-prod">
          {{-- Product image --}}
          <a href="{{ route('product', $item['product_id']) }}" style="flex-shrink:0">
            @if($item['image'])
              <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
            @else
              <div class="cart-img-placeholder">👕</div>
            @endif
          </a>

          <div class="cart-prod-info">
            <a href="{{ route('product', $item['product_id']) }}" class="cart-name">{{ $item['name'] }}</a>

            {{-- Attribute pills --}}
            @if(!empty($item['attrs']) || !empty($item['sku']))
            <div class="cart-attr-pills">
              @if(!empty($item['sku']))
                <span class="cart-attr-pill"><span class="cart-attr-pill-key">SKU</span> {{ $item['sku'] }}</span>
              @endif
              @foreach($item['attrs'] ?? [] as $k => $v)
                <span class="cart-attr-pill"><span class="cart-attr-pill-key">{{ ucfirst($k) }}</span> {{ $v }}</span>
              @endforeach
            </div>
            @endif

            {{-- Qty control + unit price --}}
            <div class="cart-row-actions">
              <div class="qty-pill">
                <button type="button" onclick="updateQty('{{ $rowId }}', -1)">−</button>
                <input type="number" id="qty-{{ $rowId }}" value="{{ $item['qty'] }}" min="1" max="{{ $item['stock'] }}" onchange="setQty('{{ $rowId }}', this.value)">
                <button type="button" onclick="updateQty('{{ $rowId }}', 1)">+</button>
              </div>
              <span class="cart-unit-price">{{ number_format($item['price'], 2) }} EGP each</span>
            </div>
          </div>
        </div>

        {{-- Price --}}
        <div class="cart-row-price">
          <div class="cart-sub" id="sub-{{ $rowId }}">{{ number_format($item['price'] * $item['qty'], 2) }} EGP</div>
          <div class="cart-sub-old" id="sub-old-{{ $rowId }}" style="{{ (!empty($item['regular_price']) && $item['regular_price'] > $item['price']) ? '' : 'display:none' }}">
            {{ !empty($item['regular_price']) ? number_format($item['regular_price'] * $item['qty'], 2) : '' }} EGP
          </div>
        </div>
      </div>
      @endforeach

      <div class="cart-actions" style="margin-top:16px">
        <a href="{{ route('shop') }}" class="btn btn-outline">← Continue Shopping</a>
        <form action="{{ route('cart.clear') }}" method="POST" style="display:inline">
          @csrf @method('DELETE')
          <button class="cart-clear-btn" onclick="return confirm('Clear entire cart?')">🗑 Clear Cart</button>
        </form>
      </div>
    </div>

    {{-- SUMMARY --}}
    <div class="cart-summary">
      <div class="cart-summary-header">
        <h3>Cart Summary</h3>
        <span class="cart-summary-badge">{{ count($cart) }} item{{ count($cart) === 1 ? '' : 's' }}</span>
      </div>

      @if($coupon)
        <div class="applied-coupon-row">
          <span>🏷️ Coupon <strong>{{ strtoupper($coupon['code']) }}</strong> applied</span>
          <form action="{{ route('cart.coupon.remove') }}" method="POST">
            @csrf @method('DELETE')
            <button class="coupon-remove-btn">Remove ✕</button>
          </form>
        </div>
      @else
        <div class="coupon-box">
          <span class="coupon-icon">🏷️</span>
          <input type="text" id="coupon-input" placeholder="Add a promo code" class="coupon-input">
          <button onclick="applyCoupon()" class="coupon-apply-btn">Apply</button>
        </div>
        <div id="coupon-msg" style="font-size:12.5px;margin-top:-8px;margin-bottom:12px"></div>
      @endif

      <div class="summary-divider"></div>

      <div class="summary-row"><span>Subtotal</span><span id="cart-subtotal">{{ number_format($subtotal, 2) }} EGP</span></div>
      <div class="summary-row">
        <span>Shipping</span>
        <span id="cart-shipping" class="{{ $shippingFee == 0 ? 'summary-shipping-free' : '' }}">{{ $shippingFee > 0 ? number_format($shippingFee, 2) . ' EGP' : 'Free' }}</span>
      </div>

      @if($coupon)
        <div class="summary-row discount-row">
          <span>Coupon ({{ strtoupper($coupon['code']) }})</span>
          <span>−{{ number_format($discount, 2) }} EGP</span>
        </div>
      @endif

      <div class="summary-row"><span>Sales Tax</span><span style="color:var(--c-mid);font-weight:500">TBA</span></div>

      <div class="summary-divider"></div>

      <div class="summary-row total-row">
        <span>Estimated Total</span>
        <span id="cart-total">{{ number_format($total, 2) }} EGP</span>
      </div>

      <a href="{{ route('checkout') }}" class="btn checkout-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        Proceed to Checkout
      </a>

      <div class="payment-icons">
        <span class="payment-chip">💵 COD</span>
        <span class="payment-chip">📱 Vodafone Cash</span>
        <span class="payment-chip">💳 Card</span>
      </div>
    </div>

  </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function showCartLoading() {
  const overlay = document.getElementById('cart-loading-overlay');
  if (overlay) overlay.classList.add('active');
}

function hideCartLoading() {
  const overlay = document.getElementById('cart-loading-overlay');
  if (overlay) overlay.classList.remove('active');
}

async function updateQty(rowId, delta) {
  const input = document.getElementById('qty-' + rowId);
  const newVal = Math.max(1, parseInt(input.value) + delta);
  input.value = newVal;
  await setQty(rowId, newVal);
}

async function setQty(rowId, val) {
  showCartLoading();
  try {
    const res = await fetch(`/cart/update/${rowId}`, {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
      body: JSON.stringify({ qty: parseInt(val) })
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('sub-' + rowId).textContent = data.item_subtotal + ' EGP';
      const oldEl = document.getElementById('sub-old-' + rowId);
      if (oldEl) {
        if (data.item_subtotal_old) {
          oldEl.textContent = data.item_subtotal_old + ' EGP';
          oldEl.style.display = '';
        } else {
          oldEl.style.display = 'none';
        }
      }
      document.getElementById('cart-subtotal').textContent = data.cart_subtotal + ' EGP';
      const shipEl = document.getElementById('cart-shipping');
      if (shipEl) shipEl.textContent = data.shipping_fee ? (data.shipping_fee + ' EGP') : 'Free';
      document.getElementById('cart-total').textContent = data.cart_total + ' EGP';
      updateNavCount(data.count);
    }
  } finally {
    hideCartLoading();
  }
}

async function removeItem(rowId) {
  showCartLoading();
  try {
    const res = await fetch(`/cart/remove/${rowId}`, {
      method: 'DELETE',
      headers: {'X-CSRF-TOKEN': CSRF}
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('row-' + rowId).remove();
      document.getElementById('cart-subtotal').textContent = data.cart_subtotal + ' EGP';
      document.getElementById('cart-total').textContent = data.cart_total + ' EGP';
      updateNavCount(data.count);
      if (data.count === 0) location.reload();
    }
  } finally {
    hideCartLoading();
  }
}

async function applyCoupon(code) {
  const input = document.getElementById('coupon-input');
  const msg   = document.getElementById('coupon-msg');
  if (!input) return;
  const useCode = (code || input.value).trim();
  if (!useCode) return;
  input.value = useCode;
  const res = await fetch('/cart/coupon', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
    body: JSON.stringify({ code: useCode })
  });
  const data = await res.json();
  msg.textContent = data.message;
  msg.style.color = data.success ? '#22a35c' : '#e02020';
  if (data.reload) setTimeout(() => location.reload(), 800);
}

(function autoApplyPendingCoupon() {
  try {
    const code = localStorage.getItem('pending_coupon');
    if (!code) return;
    localStorage.removeItem('pending_coupon');
    const input = document.getElementById('coupon-input');
    if (!input) return;
    input.value = code;
    const notice = document.getElementById('coupon-msg');
    if (notice) { notice.textContent = 'Applying coupon "' + code + '"…'; notice.style.color = '#7c3aed'; }
    setTimeout(() => applyCoupon(code), 400);
  } catch(e) {}
})();

function updateNavCount(n) {
  const badge = document.getElementById('cart-badge');
  if (badge) badge.textContent = n;
}
</script>
@endpush

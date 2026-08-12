@extends('layouts.app')
@section('title', 'Cart — Ramo Store')

@push('styles')
<style>
/* Cart phone layout: compact cards, clear hierarchy, and no horizontal overflow. */
@media (max-width: 600px) {
  .cart-page-shell{max-width:100%;}
  .cart-back-link{margin:2px 0 14px;font-size:13px;}
  .cart-title-row{gap:9px;margin-bottom:18px;align-items:center;}
  .cart-title{font-size:25px;letter-spacing:-.5px;}
  .cart-count-badge{font-size:12px;background:var(--c-tag);border-radius:99px;padding:5px 9px;}
  .cart-layout{display:flex;flex-direction:column;gap:16px;}
  #cart-items-wrap{display:flex;flex-direction:column;gap:10px;}
  .cart-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px 8px;align-items:center;padding:14px;border:1px solid #e8e8e8;border-radius:16px;background:#fff;box-shadow:0 4px 16px rgba(24,24,24,.045);}
  .cart-prod{grid-column:1 / -1;width:100%;gap:12px;min-width:0;}
  .cart-prod img,.cart-img-placeholder{width:78px;height:78px;border-radius:12px;}
  .cart-img-placeholder{font-size:28px;}
  .cart-prod-info{padding:0 28px 0 0;min-width:0;}
  .cart-name{font-size:14px;line-height:1.32;margin-bottom:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
  .cart-attr-pills{gap:4px;margin-bottom:0;max-height:25px;overflow:hidden;}
  .cart-attr-pill{font-size:10px;padding:3px 7px;}
  .cart-row-actions{grid-column:1;grid-row:2;display:flex;align-items:center;gap:8px;margin:0;min-width:0;}
  .qty-pill{height:34px;}
  .qty-pill button{width:30px;height:32px;}
  .qty-pill input{width:30px;height:32px;font-size:13px;}
  .cart-unit-price{font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .cart-row-price{grid-column:2;grid-row:2;min-width:0;padding:0;margin:0;text-align:right;align-self:center;}
  .cart-sub{font-size:14px;white-space:nowrap;}
  .cart-sub-old{font-size:10px;}
  .cart-remove-btn{top:11px;right:11px;width:27px;height:27px;border-radius:8px;}
  .cart-remove-btn svg{width:13px;height:13px;}
  .cart-actions{gap:8px;margin-top:4px;display:grid;grid-template-columns:1fr 1fr;}
  .cart-actions .btn,.cart-clear-btn{width:100%;padding:11px 8px;font-size:11.5px;white-space:nowrap;}
  .cart-summary{order:2;position:static;width:100%;padding:16px;border-radius:18px;border:1px solid #e8e8e8;box-shadow:0 5px 20px rgba(24,24,24,.055);}
  .cart-summary-header{margin-bottom:15px;}
  .cart-summary-header h3{font-size:16px;}
  .cart-summary-badge{font-size:11px;padding:4px 9px;}
  .coupon-box{min-height:42px;padding:5px 5px 5px 11px;margin-bottom:12px;border-radius:11px;}
  .coupon-input{font-size:12px;min-width:0;}
  .coupon-apply-btn{padding:9px 13px;font-size:11px;border-radius:8px;}
  .summary-row{font-size:12px;margin-bottom:10px;}
  .summary-divider{margin:11px 0;}
  .total-row{font-size:14px;padding:12px 13px;margin-left:-4px;margin-right:-4px;border-radius:11px;}
  .total-row span:last-child{font-size:17px;}
  .checkout-btn{min-height:48px;margin-top:13px;border-radius:12px;font-size:13px;}
  .payment-icons{gap:5px;margin-top:11px;}
  .payment-chip{font-size:9.5px;padding:4px 6px;}
}
</style>
@endpush

@section('content')
<div id="cart-loading-overlay" class="cart-loading-overlay"><div class="cart-spinner"></div></div>
<div class="page">

  @if(session('error'))
    <div class="alert-box alert-err">{{ session('error') }}</div>
  @endif
  <div id="cart-quantity-error" class="alert-box alert-err" style="display:none"></div>

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
                <div>
                  <div class="qty-pill">
                    <button type="button" onclick="updateQty('{{ $rowId }}', -1)">−</button>
                    <input type="number" id="qty-{{ $rowId }}" value="{{ $item['qty'] }}"
                           min="{{ $item['minimum_qty'] ?? 1 }}" max="{{ max(1, $item['maximum_qty'] ?? $item['stock']) }}"
                           data-approved-qty="{{ $item['qty'] }}" onchange="setQty('{{ $rowId }}', this.value)">
                    <button type="button" onclick="updateQty('{{ $rowId }}', 1)">+</button>
                  </div>
                  <div style="font-size:11.5px;color:var(--c-mid);margin-top:5px">
                    Minimum {{ $item['minimum_qty'] ?? 1 }} · Maximum {{ $item['maximum_qty'] ?? $item['stock'] }} per order
                  </div>
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

function showCartQuantityError(message) {
  const error = document.getElementById('cart-quantity-error');
  if (!error) return;
  error.textContent = message;
  error.style.display = '';
  error.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function clearCartQuantityError() {
  const error = document.getElementById('cart-quantity-error');
  if (error) error.style.display = 'none';
}

async function updateQty(rowId, delta) {
  const input = document.getElementById('qty-' + rowId);
  if (!input) return;
  const minimum = parseInt(input.min, 10) || 1;
  const maximum = parseInt(input.max, 10) || minimum;
  const current = parseInt(input.value, 10) || minimum;
  const newVal = Math.max(minimum, Math.min(maximum, current + delta));
  if (newVal === current) return;
  input.value = newVal;
  await setQty(rowId, newVal, current);
}

async function setQty(rowId, val, previousVal = null) {
  const input = document.getElementById('qty-' + rowId);
  if (!input) return;
  const approved = parseInt(input.dataset.approvedQty, 10) || parseInt(input.value, 10) || 1;
  const requested = parseInt(val, 10);
  const restoreValue = previousVal ?? approved;
  const minimum = parseInt(input.min, 10) || 1;
  const maximum = parseInt(input.max, 10) || minimum;

  if (!Number.isInteger(requested) || requested < minimum || requested > maximum) {
    input.value = approved;
    showCartQuantityError(`Choose a quantity from ${minimum} to ${maximum} for this item.`);
    return;
  }

  clearCartQuantityError();
  showCartLoading();
  try {
    const res = await fetch(`/cart/update/${rowId}`, {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
      body: JSON.stringify({ qty: requested })
    });
    const data = await res.json();
    if (!data.success) {
      input.value = restoreValue;
      showCartQuantityError(data.message || 'Unable to update this quantity.');
      return;
    }

    input.value = requested;
    input.dataset.approvedQty = requested;
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
  } catch (error) {
    input.value = restoreValue;
    showCartQuantityError('Unable to update this quantity. Please try again.');
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

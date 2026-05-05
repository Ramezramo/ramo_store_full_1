@extends('layouts.app')
@section('title', 'Cart — Ramo Store')

@section('content')
<div class="page">

  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span><strong>Cart</strong>
  </div>

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
  <div class="cart-layout">

    {{-- CART ITEMS --}}
    <div id="cart-items-wrap">
      <div class="cart-head-row">
        <span>Product</span><span>Price</span><span>Qty</span><span>Subtotal</span><span></span>
      </div>

      @foreach($cart as $rowId => $item)
      <div class="cart-row" id="row-{{ $rowId }}">
        <div class="cart-prod">
          @if($item['image'])
            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
          @else
            <div class="cart-img-placeholder">👕</div>
          @endif
          <div>
            <a href="{{ route('product', $item['product_id']) }}" class="cart-name">{{ $item['name'] }}</a>
            @if(!empty($item['attrs']))
              <div class="cart-attrs">
                @foreach($item['attrs'] as $k => $v)
                  <span>{{ $k }}: {{ $v }}</span>
                @endforeach
              </div>
            @endif
          </div>
        </div>
        <div class="cart-row-bottom">
          <div class="cart-price">{{ number_format($item['price'], 2) }} EGP</div>
          <div class="cart-qty">
            <div class="qty-input">
              <button type="button" onclick="updateQty('{{ $rowId }}', -1)">−</button>
              <input type="number" id="qty-{{ $rowId }}" value="{{ $item['qty'] }}" min="1" max="{{ $item['stock'] }}" onchange="setQty('{{ $rowId }}', this.value)">
              <button type="button" onclick="updateQty('{{ $rowId }}', 1)">+</button>
            </div>
          </div>
          <div class="cart-sub" id="sub-{{ $rowId }}">{{ number_format($item['price'] * $item['qty'], 2) }} EGP</div>
          <div class="cart-del">
            <button onclick="removeItem('{{ $rowId }}')" title="Remove">✕</button>
          </div>
        </div>
      </div>
      @endforeach

      <div class="cart-actions">
        <a href="{{ route('shop') }}" class="btn btn-outline">← Continue Shopping</a>
        <form action="{{ route('cart.clear') }}" method="POST" style="display:inline">
          @csrf @method('DELETE')
          <button class="btn btn-outline" style="color:#e02020;border-color:#e02020" onclick="return confirm('Clear entire cart?')">Clear Cart</button>
        </form>
      </div>
    </div>

    {{-- SUMMARY --}}
    <div class="cart-summary">
      <h3>Order Summary</h3>

      <div class="summary-row"><span>Subtotal</span><span id="cart-subtotal">{{ number_format($subtotal, 2) }} EGP</span></div>

      @if($coupon)
        <div class="summary-row discount-row">
          <span>Coupon ({{ $coupon['code'] }})</span>
          <span>−{{ number_format($discount, 2) }} EGP</span>
        </div>
        <form action="{{ route('cart.coupon.remove') }}" method="POST">
          @csrf @method('DELETE')
          <button class="coupon-remove-btn">Remove coupon ✕</button>
        </form>
      @else
        <div class="coupon-box">
          <input type="text" id="coupon-input" placeholder="Coupon code" class="coupon-input">
          <button onclick="applyCoupon()" class="btn btn-outline coupon-btn">Apply</button>
        </div>
        <div id="coupon-msg" style="font-size:13px;margin-top:6px"></div>
      @endif

      <div class="summary-divider"></div>
      <div class="summary-row total-row">
        <span>Total</span>
        <span id="cart-total">{{ number_format($total, 2) }} EGP</span>
      </div>

      <a href="{{ route('checkout') }}" class="btn btn-dark checkout-btn">Proceed to Checkout →</a>
      <div class="payment-icons">
        <span title="Cash on Delivery">💵 COD</span>
        <span title="Vodafone Cash">📱 Vodafone Cash</span>
        <span title="Credit Card">💳 Card</span>
      </div>
    </div>

  </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

async function updateQty(rowId, delta) {
  const input = document.getElementById('qty-' + rowId);
  const newVal = Math.max(1, parseInt(input.value) + delta);
  input.value = newVal;
  await setQty(rowId, newVal);
}

async function setQty(rowId, val) {
  const res = await fetch(`/cart/update/${rowId}`, {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
    body: JSON.stringify({ qty: parseInt(val) })
  });
  const data = await res.json();
  if (data.success) {
    document.getElementById('sub-' + rowId).textContent = data.item_subtotal + ' EGP';
    document.getElementById('cart-subtotal').textContent = data.cart_subtotal + ' EGP';
    document.getElementById('cart-total').textContent = data.cart_total + ' EGP';
    updateNavCount(data.count);
  }
}

async function removeItem(rowId) {
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

@extends('layouts.app')
@section('title', 'Cart — Ramo Store')

@php
  $itemCount = count($cart);
  $afterDiscount = max(0, (float) $subtotal - (float) $discount);
  $freeShippingProgress = ($freeShippingEnabled && $freeShippingThreshold > 0)
      ? min(100, ($afterDiscount / $freeShippingThreshold) * 100)
      : 100;
  $freeShippingRemaining = max(0, $freeShippingThreshold - $afterDiscount);
@endphp

@push('styles')
<style>
/* Full-screen cart foundation */
.cart-screen{min-height:calc(100vh - 74px);background:var(--c-bg);}
.cart-screen-header{display:flex;align-items:center;gap:14px;max-width:1180px;margin:0 auto;padding:22px 24px 18px;}
.cart-screen-back{width:42px;height:42px;border:1px solid var(--c-light);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:var(--c-dark);background:var(--c-white);text-decoration:none;transition:transform .18s,background .18s;flex-shrink:0;}
.cart-screen-back:hover{background:var(--c-tag);transform:translateX(-2px);}
.cart-screen-title{margin:0;font-size:28px;line-height:1.1;letter-spacing:-.6px;color:var(--c-dark);font-weight:850;}
.cart-screen-title span{font-size:14px;color:var(--c-mid);font-weight:600;letter-spacing:0;}
.cart-screen-body{max-width:1180px;margin:0 auto;padding:0 24px 40px;}
.cart-screen-grid{display:grid;grid-template-columns:minmax(0,1fr) 390px;gap:26px;align-items:start;}
.cart-items-panel,.cart-summary-panel{background:var(--c-white);border:1px solid var(--c-light);border-radius:20px;box-shadow:0 6px 24px rgba(24,24,24,.045);}
.cart-items-panel{padding:18px;}
.cart-items-heading{display:flex;justify-content:space-between;align-items:center;margin:0 0 12px;padding:0 2px;}
.cart-items-heading h2,.cart-summary-panel h2{margin:0;font-size:16px;font-weight:850;color:var(--c-dark);}
.cart-items-heading span{font-size:12px;color:var(--c-mid);}
.cart-item-card{display:grid;grid-template-columns:72px minmax(0,1fr) auto;gap:13px;align-items:center;padding:14px 0;border-bottom:1px solid #ededed;transition:opacity .2s,transform .2s,max-height .25s;}
.cart-item-card:first-of-type{padding-top:6px;}
.cart-item-card:last-of-type{border-bottom:0;}
.cart-item-card.is-updating{opacity:.58;}
.cart-item-card.is-removing{opacity:0;transform:translateX(16px);}
.cart-item-thumb{display:block;width:72px;height:72px;overflow:hidden;border-radius:14px;background:var(--c-bg);}
.cart-item-thumb img{display:block;width:100%;height:100%;object-fit:cover;}
.cart-item-placeholder{width:100%;height:100%;display:grid;place-items:center;font-size:26px;color:var(--c-mid);}
.cart-item-main{min-width:0;}
.cart-item-name{display:block;margin:0 0 5px;color:var(--c-dark);font-size:14px;font-weight:800;line-height:1.3;text-decoration:none;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.cart-item-name:hover{color:var(--c-orange);}
.cart-item-variants{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:7px;min-height:0;}
.cart-item-variant{font-size:10.5px;line-height:1.2;color:var(--c-mid);background:var(--c-tag);border:1px solid var(--c-light);padding:4px 7px;border-radius:7px;}
.cart-item-variant strong{color:var(--c-dark);font-weight:750;}
.cart-item-pricing{display:flex;align-items:baseline;gap:7px;flex-wrap:wrap;}
.cart-item-unit{font-size:11px;color:var(--c-mid);}
.cart-item-line{font-size:14px;color:var(--c-dark);font-weight:850;white-space:nowrap;}
.cart-item-old{font-size:10.5px;color:var(--c-mid);text-decoration:line-through;white-space:nowrap;}
.cart-item-controls{display:flex;flex-direction:column;align-items:flex-end;gap:9px;}
.cart-qty-stepper{display:inline-flex;align-items:center;border:1px solid #d9d9d9;border-radius:12px;overflow:hidden;background:var(--c-white);}
.cart-qty-stepper button,.cart-qty-stepper input{width:44px;height:44px;border:0;background:transparent;color:var(--c-dark);font:inherit;display:grid;place-items:center;}
.cart-qty-stepper button{font-size:20px;cursor:pointer;transition:background .15s;}
.cart-qty-stepper button:hover,.cart-qty-stepper button:focus-visible{background:var(--c-tag);outline:none;}
.cart-qty-stepper input{width:36px;text-align:center;font-size:14px;font-weight:800;outline:none;-moz-appearance:textfield;}
.cart-qty-stepper input::-webkit-outer-spin-button,.cart-qty-stepper input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0;}
.cart-remove-icon{width:44px;height:44px;display:grid;place-items:center;border:0;background:transparent;color:#a0a0a0;border-radius:11px;cursor:pointer;transition:color .15s,background .15s;}
.cart-remove-icon:hover,.cart-remove-icon:focus-visible{color:#d52b2b;background:#fff0f0;outline:none;}
.cart-item-limit{font-size:10px;color:var(--c-mid);text-align:right;}
.cart-items-actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid #ededed;}
.cart-items-actions .btn,.cart-items-actions button{font-size:12px;padding:10px 14px;border-radius:10px;}
.cart-summary-panel{padding:20px;position:sticky;top:86px;}
.cart-summary-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.cart-summary-count{font-size:11px;color:var(--c-mid);background:var(--c-tag);padding:5px 9px;border-radius:99px;font-weight:700;}
.cart-shipping-progress{padding:12px 13px;border-radius:13px;background:#f5fbf7;border:1px solid #d5f0dd;margin-bottom:15px;}
.cart-shipping-copy{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;font-size:11px;line-height:1.4;color:#277446;}
.cart-shipping-copy strong{font-weight:850;color:#176b39;}
.cart-shipping-bar{height:7px;border-radius:99px;background:#d9eee0;overflow:hidden;margin-top:9px;}
.cart-shipping-fill{height:100%;border-radius:inherit;background:#36ab62;transition:width .28s ease;}
.cart-shipping-done{font-weight:800;color:#176b39;}
.cart-promo{border-bottom:1px solid var(--c-light);padding-bottom:14px;margin-bottom:15px;}
.cart-promo summary{cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between;color:var(--c-dark);font-size:12px;font-weight:750;}
.cart-promo summary::-webkit-details-marker{display:none;}
.cart-promo summary::after{content:'+';font-size:18px;font-weight:400;color:var(--c-mid);}
.cart-promo[open] summary::after{content:'−';}
.cart-promo-form{display:flex;gap:7px;margin-top:10px;}
.cart-promo-form input{min-width:0;flex:1;border:1px solid var(--c-light);border-radius:9px;padding:10px 11px;font:inherit;font-size:12px;background:var(--c-bg);outline:none;}
.cart-promo-form input:focus{border-color:var(--c-dark);background:#fff;}
.cart-promo-form button{border:0;border-radius:9px;background:var(--c-dark);color:#fff;padding:0 14px;font-size:11px;font-weight:800;cursor:pointer;}
.cart-applied-coupon{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:10px;padding:10px 11px;font-size:11px;color:#166534;margin-bottom:15px;}
.cart-applied-coupon button{border:0;background:none;color:#b42323;font-weight:750;cursor:pointer;padding:4px;}
.cart-summary-row{display:flex;justify-content:space-between;align-items:center;gap:14px;font-size:13px;color:var(--c-mid);margin:0 0 12px;}
.cart-summary-row strong{color:var(--c-dark);font-weight:750;}
.cart-summary-divider{border:0;border-top:1px solid var(--c-light);margin:14px 0;}
.cart-discount{color:#208a4b!important;}
.cart-total-row{display:flex;align-items:center;justify-content:space-between;gap:15px;color:var(--c-dark);font-size:15px;font-weight:850;background:var(--c-tag);border-radius:12px;padding:13px;margin-top:2px;}
.cart-total-row strong{font-size:18px;}
.cart-summary-note{font-size:10.5px;line-height:1.45;color:var(--c-mid);margin:12px 0 0;}
.cart-empty-state{max-width:470px;margin:42px auto 0;padding:54px 24px;text-align:center;background:var(--c-white);border:1px solid var(--c-light);border-radius:22px;box-shadow:0 6px 24px rgba(24,24,24,.045);}
.cart-empty-icon{width:74px;height:74px;display:grid;place-items:center;margin:0 auto 16px;border-radius:22px;background:var(--c-tag);color:var(--c-orange);font-size:34px;}
.cart-empty-state h2{margin:0 0 7px;font-size:22px;color:var(--c-dark);}
.cart-empty-state p{margin:0;color:var(--c-mid);font-size:13px;}
.cart-empty-state .btn{margin-top:22px;border-radius:11px;padding:12px 20px;}
.cart-checkout-bar{max-width:1180px;margin:18px auto 0;padding:15px 18px;border:1px solid var(--c-light);border-radius:17px;background:rgba(255,255,255,.96);display:flex;align-items:center;justify-content:space-between;gap:18px;box-shadow:0 7px 24px rgba(24,24,24,.07);}
.cart-checkout-total{display:flex;flex-direction:column;gap:3px;min-width:0;}
.cart-checkout-total span{font-size:11px;color:var(--c-mid);}
.cart-checkout-total strong{font-size:18px;color:var(--c-dark);white-space:nowrap;}
.cart-checkout-button{min-height:52px;min-width:190px;display:inline-flex;align-items:center;justify-content:center;gap:9px;border:0;border-radius:13px;background:var(--c-dark);color:#fff;font-size:14px;font-weight:850;text-decoration:none;transition:transform .18s,background .18s;}
.cart-checkout-button:hover{background:#111;color:#fff;transform:translateY(-1px);}
.cart-checkout-button svg{flex-shrink:0;}
.cart-toast{position:fixed;left:50%;bottom:82px;z-index:10001;transform:translate(-50%,15px);opacity:0;pointer-events:none;background:var(--c-dark);color:#fff;border-radius:11px;padding:10px 14px;font-size:12px;box-shadow:0 9px 24px rgba(0,0,0,.2);transition:opacity .2s,transform .2s;}
.cart-toast.show{opacity:1;transform:translate(-50%,0);}
@media(max-width:900px){
  .cart-screen-grid{grid-template-columns:1fr;}
  .cart-summary-panel{position:static;}
}
@media(max-width:600px){
  .cart-screen{min-height:calc(100svh - 58px);}
  .cart-screen-header{position:sticky;top:0;z-index:20;padding:calc(10px + env(safe-area-inset-top)) 14px 11px;border-bottom:1px solid var(--c-light);background:rgba(255,255,255,.96);backdrop-filter:blur(12px);}
  .cart-screen-back{width:44px;height:44px;border-radius:12px;}
  .cart-screen-title{font-size:20px;letter-spacing:-.35px;}
  .cart-screen-title span{font-size:12px;}
  .cart-screen-body{padding:14px 14px calc(132px + env(safe-area-inset-bottom));}
  .cart-screen-grid{display:flex;flex-direction:column;gap:14px;}
  .cart-items-panel,.cart-summary-panel{border-radius:17px;box-shadow:0 4px 18px rgba(24,24,24,.04);}
  .cart-items-panel{padding:13px 14px;}
  .cart-items-heading{margin-bottom:7px;}
  .cart-items-heading h2{font-size:14px;}
  .cart-item-card{grid-template-columns:68px minmax(0,1fr);gap:10px 11px;padding:13px 0;align-items:start;}
  .cart-item-card:first-of-type{padding-top:8px;}
  .cart-item-thumb{width:68px;height:68px;border-radius:12px;}
  .cart-item-name{font-size:13px;padding-right:30px;}
  .cart-item-variant{font-size:10px;padding:4px 6px;}
  .cart-item-pricing{gap:5px;}
  .cart-item-unit{font-size:10px;}
  .cart-item-line{font-size:13px;}
  .cart-item-controls{grid-column:1 / -1;grid-row:2;flex-direction:row;align-items:center;justify-content:space-between;padding-left:79px;margin-top:-2px;}
  .cart-item-limit{font-size:9.5px;text-align:left;}
  .cart-remove-icon{width:44px;height:44px;}
  .cart-items-actions{grid-template-columns:1fr 1fr;display:grid;}
  .cart-items-actions .btn,.cart-items-actions button{padding:10px 7px;font-size:11px;}
  .cart-summary-panel{padding:15px;}
  .cart-summary-panel h2{font-size:15px;}
  .cart-checkout-bar{position:fixed;left:0;right:0;bottom:58px;z-index:9998;margin:0;padding:10px 14px calc(10px + env(safe-area-inset-bottom));border-radius:16px 16px 0 0;border-left:0;border-right:0;border-bottom:0;box-shadow:0 -6px 24px rgba(24,24,24,.13);}
  .cart-checkout-total strong{font-size:16px;}
  .cart-checkout-button{min-width:0;flex:1;min-height:50px;font-size:13px;border-radius:12px;}
  .cart-empty-state{margin:12px auto 0;padding:52px 20px;border-radius:18px;}
}
@media(max-width:340px){
  .cart-screen-title{font-size:18px;}
  .cart-item-controls{padding-left:0;}
  .cart-checkout-total span{font-size:10px;}
  .cart-checkout-button{padding:0 13px;}
}
</style>
@endpush

@section('content')
<div id="cart-loading-overlay" class="cart-loading-overlay"><div class="cart-spinner"></div></div>
<div class="cart-screen" data-free-shipping-enabled="{{ $freeShippingEnabled ? '1' : '0' }}" data-free-shipping-threshold="{{ $freeShippingThreshold }}" data-discount="{{ $discount }}">
  <header class="cart-screen-header">
    <a href="{{ route('shop') }}" class="cart-screen-back" onclick="return cartGoBack(event)" aria-label="Go back">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="cart-screen-title">Your Cart <span>({{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }})</span></h1>
  </header>

  <main class="cart-screen-body">
    @if(session('error'))
      <div class="alert-box alert-err">{{ session('error') }}</div>
    @endif
    <div id="cart-quantity-error" class="alert-box alert-err" style="display:none"></div>

    @if(empty($cart))
      <section class="cart-empty-state" aria-live="polite">
        <div class="cart-empty-icon" aria-hidden="true">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
        </div>
        <h2>Your cart is empty</h2>
        <p>Find something you love and it will appear here.</p>
        <a href="{{ route('shop') }}" class="btn btn-dark">Continue Shopping</a>
      </section>
    @else
      <div class="cart-screen-grid">
        <section class="cart-items-panel" aria-labelledby="cart-items-heading">
          <div class="cart-items-heading">
            <h2 id="cart-items-heading">Items in your cart</h2>
            <span>{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span>
          </div>

          <div id="cart-items-wrap">
            @foreach($cart as $rowId => $item)
              <article class="cart-item-card" id="row-{{ $rowId }}" data-row-id="{{ $rowId }}" data-unit-price="{{ $item['price'] }}">
                <a href="{{ route('product', $item['product_id']) }}" class="cart-item-thumb" aria-label="View {{ $item['name'] }}">
                  @if($item['image'])
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                  @else
                    <span class="cart-item-placeholder" aria-hidden="true">👕</span>
                  @endif
                </a>
                <div class="cart-item-main">
                  <a href="{{ route('product', $item['product_id']) }}" class="cart-item-name">{{ $item['name'] }}</a>
                  @if(!empty($item['attrs']) || !empty($item['sku']))
                    <div class="cart-item-variants" aria-label="Selected options">
                      @if(!empty($item['sku']))<span class="cart-item-variant"><strong>SKU</strong> {{ $item['sku'] }}</span>@endif
                      @foreach($item['attrs'] ?? [] as $k => $v)
                        <span class="cart-item-variant"><strong>{{ ucfirst($k) }}</strong> {{ $v }}</span>
                      @endforeach
                    </div>
                  @endif
                  <div class="cart-item-pricing">
                    <span class="cart-item-unit">{{ number_format($item['price'], 2) }} EGP each</span>
                    <strong class="cart-item-line" id="sub-{{ $rowId }}">{{ number_format($item['price'] * $item['qty'], 2) }} EGP</strong>
                    <span class="cart-item-old" id="sub-old-{{ $rowId }}" style="{{ (!empty($item['regular_price']) && $item['regular_price'] > $item['price']) ? '' : 'display:none' }}">{{ !empty($item['regular_price']) ? number_format($item['regular_price'] * $item['qty'], 2) . ' EGP' : '' }}</span>
                  </div>
                </div>
                <div class="cart-item-controls">
                  <div class="cart-qty-stepper" aria-label="Quantity for {{ $item['name'] }}">
                    <button type="button" onclick="updateQty('{{ $rowId }}', -1)" aria-label="Decrease quantity">−</button>
                    <input type="number" id="qty-{{ $rowId }}" value="{{ $item['qty'] }}" min="{{ $item['minimum_qty'] ?? 1 }}" max="{{ max(1, $item['maximum_qty'] ?? $item['stock']) }}" data-approved-qty="{{ $item['qty'] }}" onchange="setQty('{{ $rowId }}', this.value)" aria-label="Quantity">
                    <button type="button" onclick="updateQty('{{ $rowId }}', 1)" aria-label="Increase quantity">+</button>
                  </div>
                  <button type="button" class="cart-remove-icon" onclick="removeItem('{{ $rowId }}')" aria-label="Remove {{ $item['name'] }}" title="Remove item">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  </button>
                  <span class="cart-item-limit">{{ $item['minimum_qty'] ?? 1 }}–{{ $item['maximum_qty'] ?? $item['stock'] }} per order</span>
                </div>
              </article>
            @endforeach
          </div>

          <div class="cart-items-actions">
            <a href="{{ route('shop') }}" class="btn btn-outline">← Continue Shopping</a>
            <form action="{{ route('cart.clear') }}" method="POST">
              @csrf @method('DELETE')
              <button type="submit" class="cart-clear-btn" onclick="return confirm('Clear entire cart?')">Clear Cart</button>
            </form>
          </div>
        </section>

        <aside class="cart-summary-panel" aria-labelledby="cart-summary-heading">
          <div class="cart-summary-head">
            <h2 id="cart-summary-heading">Order Summary</h2>
            <span class="cart-summary-count">{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span>
          </div>

          @if($freeShippingEnabled)
            <div class="cart-shipping-progress" id="cart-shipping-progress" aria-live="polite">
              <div class="cart-shipping-copy">
                <span id="cart-shipping-message">
                  @if($freeShippingRemaining > 0)
                    Add <strong>{{ number_format($freeShippingRemaining, 2) }} EGP</strong> more for free shipping
                  @else
                    <span class="cart-shipping-done">You unlocked free shipping.</span>
                  @endif
                </span>
                <strong id="cart-shipping-percent">{{ round($freeShippingProgress) }}%</strong>
              </div>
              <div class="cart-shipping-bar"><div id="cart-shipping-fill" class="cart-shipping-fill" style="width:{{ $freeShippingProgress }}%"></div></div>
            </div>
          @endif

          @if($coupon)
            <div class="cart-applied-coupon">
              <span>Coupon <strong>{{ strtoupper($coupon['code']) }}</strong> applied</span>
              <form action="{{ route('cart.coupon.remove') }}" method="POST">@csrf @method('DELETE')<button type="submit">Remove</button></form>
            </div>
          @else
            <details class="cart-promo">
              <summary>Have a promo code?</summary>
              <form class="cart-promo-form" onsubmit="applyCoupon(event)">
                <input type="text" id="coupon-input" placeholder="Enter promo code" autocomplete="off">
                <button type="submit">Apply</button>
              </form>
              <div id="coupon-msg" style="font-size:11px;margin-top:7px"></div>
            </details>
          @endif

          <div class="cart-summary-row"><span>Subtotal</span><strong id="cart-subtotal">{{ number_format($subtotal, 2) }} EGP</strong></div>
          @if($coupon)<div class="cart-summary-row cart-discount"><span>Discount</span><strong id="cart-discount">−{{ number_format($discount, 2) }} EGP</strong></div>@endif
          <div class="cart-summary-row"><span>Shipping</span><strong id="cart-shipping" class="{{ $shippingFee == 0 ? 'summary-shipping-free' : '' }}">{{ $shippingFee > 0 ? number_format($shippingFee, 2) . ' EGP' : 'Free' }}</strong></div>
          <div class="cart-summary-row"><span>Tax</span><strong style="color:var(--c-mid)">TBA</strong></div>
          <hr class="cart-summary-divider">
          <div class="cart-total-row"><span>Total</span><strong id="cart-total">{{ number_format($total, 2) }} EGP</strong></div>
          <p class="cart-summary-note">Final taxes and delivery details are confirmed during checkout.</p>
        </aside>
      </div>

      <div class="cart-checkout-bar">
        <div class="cart-checkout-total"><span>Subtotal</span><strong id="cart-sticky-total">{{ number_format($total, 2) }} EGP</strong></div>
        <a href="{{ route('checkout') }}" class="cart-checkout-button">Checkout <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      </div>
    @endif
  </main>
</div>
<div id="cart-toast" class="cart-toast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const cartScreen = document.querySelector('.cart-screen');

function showCartLoading(){ document.getElementById('cart-loading-overlay')?.classList.add('active'); }
function hideCartLoading(){ document.getElementById('cart-loading-overlay')?.classList.remove('active'); }
function showCartToast(message){
  const toast = document.getElementById('cart-toast');
  if(!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(window.cartToastTimer);
  window.cartToastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
}
function cartGoBack(event){
  if(window.history.length > 1){ event.preventDefault(); window.history.back(); return false; }
  return true;
}
function showCartQuantityError(message){
  const el = document.getElementById('cart-quantity-error');
  if(!el) return;
  el.textContent = message;
  el.style.display = '';
  el.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function clearCartQuantityError(){ const el = document.getElementById('cart-quantity-error'); if(el) el.style.display='none'; }
function updateNavCount(n){ const badge=document.getElementById('cart-badge'); if(badge) badge.textContent=n; }
function money(value){ return Number(value || 0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' EGP'; }
function updateCartSummary(data){
  const subtotal = document.getElementById('cart-subtotal');
  const shipping = document.getElementById('cart-shipping');
  const total = document.getElementById('cart-total');
  const stickyTotal = document.getElementById('cart-sticky-total');
  if(subtotal && data.cart_subtotal !== undefined) subtotal.textContent = data.cart_subtotal + ' EGP';
  if(shipping) { shipping.textContent = data.shipping_fee ? data.shipping_fee + ' EGP' : 'Free'; shipping.classList.toggle('summary-shipping-free', !data.shipping_fee); }
  if(total && data.cart_total !== undefined) total.textContent = data.cart_total + ' EGP';
  if(stickyTotal && data.cart_total !== undefined) stickyTotal.textContent = data.cart_total + ' EGP';
  const discount = document.getElementById('cart-discount');
  if(discount && data.cart_discount !== undefined) discount.textContent = '−' + data.cart_discount + ' EGP';
  const fill = document.getElementById('cart-shipping-fill');
  const percent = document.getElementById('cart-shipping-percent');
  const message = document.getElementById('cart-shipping-message');
  if(fill && data.free_shipping_progress !== undefined) fill.style.width = Math.min(100, Number(data.free_shipping_progress)) + '%';
  if(percent && data.free_shipping_progress !== undefined) percent.textContent = Math.round(Number(data.free_shipping_progress)) + '%';
  if(message && data.free_shipping_remaining !== undefined){
    const remaining = Number(String(data.free_shipping_remaining).replace(/,/g,''));
    message.innerHTML = remaining > 0 ? 'Add <strong>' + money(remaining) + '</strong> more for free shipping' : '<span class="cart-shipping-done">You unlocked free shipping.</span>';
  }
}
function optimisticLineTotal(rowId, qty){
  const row = document.getElementById('row-' + rowId);
  const unit = Number(row?.dataset.unitPrice || 0);
  const line = document.getElementById('sub-' + rowId);
  if(line) line.textContent = money(unit * qty);
}
async function updateQty(rowId, delta){
  const input = document.getElementById('qty-' + rowId);
  if(!input) return;
  const current = parseInt(input.value,10) || parseInt(input.min,10) || 1;
  const minimum = parseInt(input.min,10) || 1;
  const maximum = parseInt(input.max,10) || minimum;
  const requested = Math.max(minimum, Math.min(maximum, current + delta));
  if(requested !== current) await setQty(rowId, requested, current);
}
async function setQty(rowId, val, previousVal = null){
  const input = document.getElementById('qty-' + rowId);
  const row = document.getElementById('row-' + rowId);
  if(!input || !row) return;
  const approved = parseInt(input.dataset.approvedQty,10) || parseInt(input.value,10) || 1;
  const requested = parseInt(val,10);
  const restore = previousVal ?? approved;
  const minimum = parseInt(input.min,10) || 1;
  const maximum = parseInt(input.max,10) || minimum;
  if(!Number.isInteger(requested) || requested < minimum || requested > maximum){ input.value=approved; showCartQuantityError(`Choose a quantity from ${minimum} to ${maximum} for this item.`); return; }
  clearCartQuantityError();
  input.value = requested;
  optimisticLineTotal(rowId, requested);
  row.classList.add('is-updating');
  try{
    const res = await fetch('/cart/update/' + encodeURIComponent(rowId), {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify({qty:requested})});
    const data = await res.json();
    if(!data.success) throw new Error(data.message || 'Unable to update this quantity.');
    input.dataset.approvedQty = requested;
    const oldEl=document.getElementById('sub-old-' + rowId);
    if(oldEl){ if(data.item_subtotal_old){oldEl.textContent=data.item_subtotal_old+' EGP';oldEl.style.display='';}else oldEl.style.display='none'; }
    updateCartSummary(data); updateNavCount(data.count); showCartToast('Quantity updated');
  }catch(error){
    input.value = restore; optimisticLineTotal(rowId, restore); showCartQuantityError(error.message || 'Unable to update this quantity. Please try again.');
  }finally{ row.classList.remove('is-updating'); }
}
async function removeItem(rowId){
  const row=document.getElementById('row-' + rowId);
  if(!row || !confirm('Remove this item from your cart?')) return;
  row.classList.add('is-removing');
  try{
    const res=await fetch('/cart/remove/' + encodeURIComponent(rowId),{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}});
    const data=await res.json();
    if(!data.success) throw new Error(data.message || 'Unable to remove this item.');
    setTimeout(()=>row.remove(),220); updateCartSummary(data); updateNavCount(data.count); showCartToast('Item removed');
    if(data.count === 0) setTimeout(()=>location.reload(),260);
  }catch(error){ row.classList.remove('is-removing'); showCartQuantityError(error.message || 'Unable to remove this item. Please try again.'); }
}
async function applyCoupon(event){
  event?.preventDefault();
  const input=document.getElementById('coupon-input'); const msg=document.getElementById('coupon-msg');
  const code=(input?.value || '').trim(); if(!code) return;
  if(msg){msg.textContent='Applying…';msg.style.color='var(--c-mid)';}
  try{
    const res=await fetch('/cart/coupon',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify({code})});
    const data=await res.json();
    if(msg){msg.textContent=data.message || '';msg.style.color=data.success?'#208a4b':'#c02020';}
    if(data.reload) setTimeout(()=>location.reload(),650);
  }catch(error){if(msg){msg.textContent='Unable to apply the code. Try again.';msg.style.color='#c02020';}}
}
</script>
@endpush

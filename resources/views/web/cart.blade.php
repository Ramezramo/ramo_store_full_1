@extends('layouts.app')
@section('title', session('locale', 'en') === 'ar' ? 'سلتك — Ramo Store' : 'Cart — Ramo Store')

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

@push('styles')
<style>
.cart-screen [aria-busy="true"]{opacity:.6;cursor:wait}
.cart-screen form[aria-busy="true"] button[type="submit"]::after{content:'';display:inline-block;width:12px;height:12px;margin-inline-start:6px;border:2px solid currentColor;border-inline-end-color:transparent;border-radius:50%;animation:cart-request-spin .7s linear infinite}
@media(prefers-reduced-motion:reduce){.cart-screen form[aria-busy="true"] button[type="submit"]::after{animation:none}}
.cart-screen [data-cart-checkout][aria-busy="true"]{pointer-events:none}
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
  .cart-request-overlay{position:fixed;inset:0;z-index:10050;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(255,255,255,.76);backdrop-filter:blur(5px);}
  .cart-request-overlay[hidden]{display:none;}
  .cart-request-dialog{min-width:210px;padding:22px 24px;border:1px solid var(--c-light);border-radius:18px;background:var(--c-white);box-shadow:0 14px 42px rgba(17,17,17,.16);text-align:center;color:var(--c-dark);}
  .cart-request-spinner{width:32px;height:32px;margin:0 auto 12px;border:3px solid #e5e5e5;border-top-color:var(--c-dark);border-radius:50%;animation:cart-request-spin .8s linear infinite;}
  .cart-request-dialog p{font-size:13px;font-weight:750;}
  @keyframes cart-request-spin{to{transform:rotate(360deg);}}
  .cart-item-card.is-updating{opacity:.58;}

  .cart-item-card.is-removing{opacity:0;transform:translateX(16px);}
  .cart-item-card.is-unavailable{padding-inline:10px;border:1px solid #fecaca;border-radius:14px;background:#fff8f8;}
  .cart-item-unavailable{display:inline-flex;align-items:center;gap:5px;margin:0 0 7px;padding:5px 8px;border:1px solid #fecaca;border-radius:999px;background:#fff0f0;color:#b42318;font-size:10.5px;font-weight:850;line-height:1.2;}
  .cart-item-unavailable svg{flex-shrink:0;}
  .cart-qty-stepper.is-disabled{border-color:#f0d7d7;background:#f8eeee;opacity:.62;}
  .cart-qty-stepper.is-disabled button,.cart-qty-stepper.is-disabled input{color:#9f7777;cursor:not-allowed;}
  .cart-remove-icon.is-priority{color:#c02020;background:#fff0f0;}

.cart-item-thumb{display:block;width:72px;height:72px;overflow:hidden;border-radius:14px;background:var(--c-bg);}
.cart-item-thumb img{display:block;width:100%;height:100%;object-fit:cover;}
.cart-item-placeholder{width:100%;height:100%;display:grid;place-items:center;font-size:26px;color:var(--c-mid);}
.cart-item-main{min-width:0;}
.cart-item-references{display:none;}
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
  .cart-qty-stepper button,.cart-qty-stepper .cart-qty-value{width:44px;height:44px;border:0;background:transparent;color:var(--c-dark);font:inherit;display:grid;place-items:center;}
  .cart-qty-stepper .cart-qty-form,.cart-remove-form{display:contents;}
  .cart-qty-stepper .cart-qty-value{width:36px;text-align:center;font-size:14px;font-weight:800;}
  .cart-qty-stepper button{font-size:20px;cursor:pointer;transition:background .15s;}

  .cart-qty-stepper button:hover,.cart-qty-stepper button:focus-visible{background:var(--c-tag);outline:none;}

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
  .cart-coupon-widget{margin-bottom:15px;}
  .cart-coupon-error{display:flex;flex-direction:column;gap:6px;margin-bottom:9px;padding:11px 12px;border:1px solid #f3b5b5;border-radius:12px;background:#fff5f5;color:#a61b1b;font-size:11px;line-height:1.55;}
  .cart-coupon-error-title{display:flex;align-items:center;justify-content:space-between;gap:10px;font-weight:850;}
  .cart-coupon-error-title strong{direction:ltr;color:#8d1717;font-size:11px;letter-spacing:.2px;}
  .cart-coupon-error p{margin:0;font-weight:650;}
  .cart-applied-coupon{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:10px;padding:10px 11px;font-size:11px;color:#166534;margin-bottom:0;}
  .cart-applied-coupon button{border:0;background:none;color:#b42323;font-weight:750;cursor:pointer;padding:4px;}

.cart-summary-row{display:flex;justify-content:space-between;align-items:center;gap:14px;font-size:13px;color:var(--c-mid);margin:0 0 12px;}
.cart-summary-row strong{color:var(--c-dark);font-weight:750;}
.cart-summary-divider{border:0;border-top:1px solid var(--c-light);margin:14px 0;}
.cart-discount{color:#208a4b!important;}
.cart-total-row{display:flex;align-items:center;justify-content:space-between;gap:15px;color:var(--c-dark);font-size:15px;font-weight:850;background:var(--c-tag);border-radius:12px;padding:13px;margin-top:2px;}
.cart-total-row strong{font-size:18px;}
.cart-summary-note{font-size:10.5px;line-height:1.45;color:var(--c-mid);margin:12px 0 0;}
  .cart-summary-checkout{width:100%;margin-top:16px;}
  .cart-checkout-button.is-blocked{background:#c8c8c3;color:#fff;cursor:not-allowed;opacity:.78;box-shadow:none;transform:none;}
  .cart-checkout-warning{margin-top:12px;padding:10px 11px;border:1px solid #fecaca;border-radius:11px;background:#fff7f7;color:#b42318;font-size:11px;font-weight:750;line-height:1.5;}
  .cart-checkout-warning[hidden]{display:none;}

.cart-empty-state{max-width:470px;margin:42px auto 0;padding:54px 24px;text-align:center;background:var(--c-white);border:1px solid var(--c-light);border-radius:22px;box-shadow:0 6px 24px rgba(24,24,24,.045);}
.cart-empty-icon{width:74px;height:74px;display:grid;place-items:center;margin:0 auto 16px;border-radius:22px;background:var(--c-tag);color:var(--c-orange);font-size:34px;}
.cart-empty-state h2{margin:0 0 7px;font-size:22px;color:var(--c-dark);}
.cart-empty-state p{margin:0;color:var(--c-mid);font-size:13px;}
.cart-empty-actions{display:flex;flex-direction:column;align-items:center;gap:10px;margin-top:22px;}
.cart-empty-state .cart-empty-actions .btn{margin:0;min-width:182px;border-radius:11px;padding:12px 20px;}
.cart-empty-order-link{background:var(--c-white)!important;color:var(--c-dark)!important;border:1.5px solid var(--c-dark)!important;}
.cart-empty-order-link:hover,.cart-empty-order-link:focus-visible{background:var(--c-tag)!important;outline:none;}
  .cart-checkout-bar{max-width:1180px;margin:18px auto 0;padding:15px 18px;border:1px solid var(--c-light);border-radius:17px;background:rgba(255,255,255,.96);display:none;align-items:center;justify-content:space-between;gap:18px;box-shadow:0 7px 24px rgba(24,24,24,.07);}
  .cart-checkout-warning-bar{flex-basis:100%;margin:0;}

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
  .cart-screen{min-height:calc(100svh - 58px);background:linear-gradient(180deg,#fafaf9 0%,var(--c-bg) 42%);}
  .cart-screen-header{padding:12px 16px;border-bottom:1px solid var(--c-light);background:var(--c-white);}
  .cart-screen-back{width:44px;height:44px;border-radius:14px;background:#fff;box-shadow:0 3px 12px rgba(24,24,24,.05);}
  .cart-screen-title{font-size:18px;letter-spacing:-.35px;}
  .cart-screen-title span{font-size:12px;}
  .cart-screen-body{padding:12px 12px calc(170px + env(safe-area-inset-bottom));}
  .cart-screen-grid{display:flex;flex-direction:column;align-items:stretch;gap:16px;}
  .cart-items-panel,.cart-summary-panel{box-sizing:border-box;width:100%;min-width:0;margin:0;border:1px solid var(--c-light);border-radius:16px;box-shadow:0 3px 16px rgba(24,24,24,.04);background:var(--c-white);}
  .cart-items-panel{padding:15px 16px;}
  .cart-summary-panel{padding:16px;}
  .cart-items-heading{margin-bottom:4px;padding:0 0 3px;}
  .cart-items-heading h2{font-size:15px;letter-spacing:-.1px;}
  .cart-items-heading span{padding:5px 8px;border-radius:999px;background:#f5f5f3;font-size:10px;font-weight:750;}
  .cart-item-card{grid-template-columns:82px minmax(0,1fr);gap:12px;padding:16px 0 14px;align-items:start;}
  .cart-item-card:first-of-type{padding-top:10px;}
  .cart-item-thumb{width:82px;height:82px;border-radius:16px;border:1px solid rgba(24,24,24,.08);box-shadow:0 4px 12px rgba(24,24,24,.06);}
  .cart-item-main{padding-top:1px;}
  .cart-item-name{font-size:13.5px;line-height:1.35;padding:0;margin-bottom:6px;}
  .cart-item-variants{gap:5px;margin-bottom:8px;}
  .cart-item-variant{font-size:10px;padding:4px 6px;border-radius:7px;background:var(--c-tag);overflow-wrap:anywhere;}
  .cart-item-reference{display:none;}
  .cart-item-references{display:block;margin:6px 0 0;color:var(--c-mid);font-size:11px;overflow-wrap:anywhere;}
  .cart-item-references summary{min-height:44px;align-content:center;cursor:pointer;}
  .cart-item-references p{margin:4px 0;}
  .cart-item-pricing{gap:5px;align-items:baseline;}
  .cart-item-unit{font-size:11px;color:#606060;}
  .cart-item-line{font-size:14.5px;color:var(--c-dark);white-space:normal;overflow-wrap:anywhere;}
  .cart-item-old{font-size:11px;color:#606060;}
  .cart-item-controls{grid-column:1 / -1;grid-row:2;flex-direction:row;align-items:center;justify-content:space-between;padding:2px 0 0;margin-top:0;}
  .cart-item-limit{font-size:9.5px;text-align:left;}
  .cart-qty-stepper{border-color:#d8d8d5;border-radius:14px;box-shadow:0 3px 10px rgba(24,24,24,.04);}
  .cart-qty-stepper button,.cart-qty-stepper input{width:48px;height:48px;}
  .cart-qty-stepper input{width:38px;font-size:15px;}
  .cart-remove-icon{width:44px;height:44px;border:1px solid #ffd9d9;background:#fff7f7;color:#cf5757;border-radius:13px;}
  .cart-items-actions{display:flex;flex-wrap:nowrap;align-items:stretch;gap:10px;margin-top:2px;padding-top:15px;border-top:1px solid #ededeb;}
  .cart-items-actions .btn,.cart-items-actions button{min-height:44px;padding:0 9px;font-size:10.5px;white-space:nowrap;border-radius:11px;}
  .cart-items-actions .btn{flex:1;background:#fff;}
  .cart-items-actions form{flex:1;}
  .cart-items-actions .cart-clear-btn{width:100%;border:1px solid #fecaca;background:#fff7f7;color:#c24141;font:inherit;font-weight:800;cursor:pointer;}
  .cart-summary-panel h2{font-size:16px;}
  .cart-summary-head{margin-bottom:14px;}
  .cart-shipping-progress{margin-bottom:14px;}
  .cart-summary-row{margin-bottom:10px;font-size:12.5px;}
  .cart-summary-note{font-size:12px;color:#606060;}
  .cart-total-row{padding:14px;border:1px solid rgba(24,24,24,.06);border-radius:15px;background:linear-gradient(135deg,#f7f7f5 0%,#efefec 100%);}
  .cart-total-row strong{font-size:19px;}
  .cart-summary-checkout{display:none;}
  .cart-checkout-bar{position:fixed;inset-inline:0;bottom:calc(58px + env(safe-area-inset-bottom));z-index:9998;margin:0;padding:10px 14px;border:1px solid var(--c-light);border-radius:0;box-shadow:0 -4px 18px rgba(24,24,24,.08);background:var(--c-white);display:grid;grid-template-columns:minmax(0,1fr) minmax(120px,1fr);gap:8px 12px;align-items:center;}
  .cart-checkout-warning-bar{grid-column:1 / -1;}
  .cart-checkout-total{padding:0;gap:2px;}
  .cart-checkout-total span{font-size:10px;font-weight:700;}
  .cart-checkout-total strong{font-size:clamp(13px,4vw,17px);white-space:normal;overflow-wrap:anywhere;line-height:1.25;}
  .cart-checkout-button{min-width:0;min-height:44px;font-size:12.5px;border-radius:11px;box-shadow:none;padding:0 10px;}
  .cart-empty-state{margin:12px auto 0;padding:52px 20px;border-radius:18px;}
}
/* Egyptian Arabic / RTL cart layout */
.cart-screen[dir="rtl"]{text-align:right;}
.cart-screen[dir="rtl"] .cart-screen-back:hover{transform:translateX(2px);}
.cart-screen[dir="rtl"] .cart-screen-back svg,.cart-screen[dir="rtl"] .cart-checkout-button svg{transform:scaleX(-1);}
.cart-screen[dir="rtl"] .cart-item-card.is-removing{transform:translateX(-16px);}
.cart-screen[dir="rtl"] .cart-qty-stepper{direction:ltr;}
.cart-screen[dir="rtl"] .cart-item-pricing,.cart-screen[dir="rtl"] .cart-total-row strong,.cart-screen[dir="rtl"] .cart-summary-row strong,.cart-screen[dir="rtl"] .cart-checkout-total strong{direction:ltr;unicode-bidi:embed;}
@media(max-width:600px){
  .cart-screen[dir="rtl"] .cart-item-name{padding:0;}
  .cart-screen[dir="rtl"] .cart-item-limit{text-align:right;}
}
@media(max-width:340px){
  .cart-screen-title{font-size:18px;}
  .cart-item-controls{padding-left:0;}
  .cart-screen[dir="rtl"] .cart-item-controls{padding-right:0;}
  .cart-checkout-total span{font-size:10px;}
  .cart-checkout-button{padding:0 13px;}
}
</style>
@endpush

@section('content')
@include('web.partials.cart-content')
<div id="cart-toast" class="cart-toast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/cart-page.js') }}?v={{ filemtime(public_path('js/cart-page.js')) }}" defer></script>
@endpush

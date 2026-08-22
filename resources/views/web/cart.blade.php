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
  $guestOtpCheckout = !auth()->check() && (bool) ($authConfig['phone_otp_login'] ?? false);
  $hasUnavailableItems = collect($cart)->contains(fn ($item) => !empty($item['is_unavailable']));
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
.cart-applied-coupon{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:10px;padding:10px 11px;font-size:11px;color:#166534;margin-bottom:15px;}
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
.cart-auth-modal{position:fixed;inset:0;z-index:10020;display:grid;place-items:center;box-sizing:border-box;width:100%;height:100%;padding:20px;overflow:hidden;overscroll-behavior:contain;background:rgba(12,12,12,.58);backdrop-filter:blur(7px);}
.cart-auth-modal[hidden]{display:none;}
.cart-auth-dialog{position:relative;box-sizing:border-box;width:min(100%,390px);max-width:100%;min-width:0;max-height:calc(100svh - 92px);overflow:auto;padding:26px;border:1px solid rgba(24,24,24,.08);border-radius:24px;background:var(--c-white);box-shadow:0 24px 80px rgba(0,0,0,.28);overscroll-behavior:contain;}
.cart-auth-close{position:absolute;top:14px;right:14px;width:40px;height:40px;border:1px solid rgba(24,24,24,.06);border-radius:14px;background:#f5f5f3;color:var(--c-dark);font-size:24px;line-height:1;cursor:pointer;transition:background .16s,transform .16s;}
.cart-auth-close:hover,.cart-auth-close:focus-visible{background:#ecece8;outline:none;transform:rotate(4deg);}
.cart-auth-dialog[dir="rtl"] .cart-auth-close{right:auto;left:14px;}
.cart-auth-heading{padding-inline-end:48px;}
.cart-auth-kicker{display:inline-flex;align-items:center;min-height:24px;margin-bottom:9px;padding:4px 9px;border:1px solid #ffe0cf;border-radius:999px;background:#fff7f2;color:var(--c-orange);font-size:10px;font-weight:850;letter-spacing:.04em;text-transform:uppercase;}
.cart-auth-dialog h3{margin:0;color:var(--c-dark);font-size:22px;line-height:1.25;font-weight:900;letter-spacing:-.25px;}
.cart-auth-dialog p{margin:9px 0 18px;color:var(--c-mid);font-size:12px;line-height:1.65;}
.cart-auth-field-heading{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:0 0 7px;}
.cart-auth-label{color:var(--c-dark);font-size:12px;font-weight:850;}
.cart-auth-helper{color:var(--c-mid);font-size:10px;}
.cart-auth-phone-row{display:flex;align-items:stretch;gap:8px;min-width:0;direction:ltr;}
.cart-auth-phone-row input{min-width:0;flex:1;height:52px;box-sizing:border-box;border:1px solid #deded9;border-radius:13px;padding:12px 13px;background:#fafaf8;color:var(--c-dark);font:inherit;font-size:15px;outline:none;transition:border-color .16s,box-shadow .16s,background .16s;}
.cart-auth-phone-row input:focus{border-color:var(--c-orange);background:var(--c-white);box-shadow:0 0 0 4px rgba(232,99,39,.11);}
.cart-auth-prefix{display:flex;align-items:center;justify-content:center;min-width:70px;height:52px;box-sizing:border-box;padding:0 10px;border:1px solid #deded9;border-radius:13px;background:#f7f7f4;color:var(--c-dark);font-size:12px;font-weight:850;white-space:nowrap;}
.cart-auth-submit{width:100%;min-height:52px;margin-top:13px;border:0;border-radius:14px;background:var(--c-dark);color:#fff;font:inherit;font-size:14px;font-weight:900;cursor:pointer;transition:transform .16s,background .16s,box-shadow .16s;box-shadow:0 7px 16px rgba(24,24,24,.14);}
.cart-auth-submit:hover,.cart-auth-submit:focus-visible{background:#222;outline:none;box-shadow:0 9px 20px rgba(24,24,24,.2);}
.cart-auth-submit:active{transform:scale(.985);}
.cart-auth-submit:disabled{opacity:.58;cursor:wait;}
.cart-auth-message{min-height:18px;margin:9px 0 0!important;text-align:center;color:var(--c-mid);font-size:11px!important;}
.cart-auth-code-boxes{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:clamp(4px,2vw,8px);width:100%;margin:18px 0 10px;direction:ltr;}
.guest-otp-code-input{width:100%;min-width:0;height:clamp(42px,13vw,52px);box-sizing:border-box;border:2px solid #deded9;border-radius:12px;background:#fafaf8;color:var(--c-dark);font:700 20px/1 inherit;text-align:center;outline:none;transition:border-color .16s,box-shadow .16s,background .16s;}
.guest-otp-code-input:focus{border-color:var(--c-orange);background:var(--c-white);box-shadow:0 0 0 4px rgba(232,99,39,.11);}
.guest-otp-code-input.filled{border-color:#22c55e;background:#f4fff7;}
.cart-auth-sent-copy{text-align:center!important;margin-bottom:5px!important;}
.cart-auth-sent-copy strong{display:block;margin-top:3px;color:var(--c-dark);direction:ltr;unicode-bidi:embed;}
.cart-auth-error{color:#c02020!important;}
.cart-auth-success{color:#208a4b!important;}
.cart-auth-resend-row{display:flex;justify-content:center;align-items:center;gap:5px;min-height:24px;margin-top:8px;color:var(--c-mid);font-size:12px;}
.cart-auth-resend-row button,.cart-auth-change-phone{border:0;background:transparent;color:var(--c-dark);font:inherit;font-size:12px;font-weight:700;text-decoration:underline;cursor:pointer;}
.cart-auth-change-phone{display:block;margin:8px auto 0;color:var(--c-mid);}
.cart-auth-dev-box{margin-top:14px;padding:11px 12px;border:1.5px dashed #f59e0b;border-radius:10px;background:#fffbeb;text-align:center;color:#92400e;}
.cart-auth-dev-box strong{display:block;margin:3px 0;font:800 24px/1.2 monospace;letter-spacing:5px;}
.cart-auth-dev-box small{display:block;color:#b45309;font-size:10px;line-height:1.4;}
.cart-auth-dialog[dir="rtl"]{text-align:right;}
@media(prefers-reduced-motion:no-preference){.cart-auth-dialog{animation:cartAuthDialogIn .22s cubic-bezier(.23,1,.32,1);}@keyframes cartAuthDialogIn{from{opacity:0;transform:translateY(12px) scale(.985);}to{opacity:1;transform:none;}}}
@media(max-width:600px){.cart-auth-modal{align-items:end;padding:12px 8px calc(70px + env(safe-area-inset-bottom));}.cart-auth-dialog{width:100%;max-width:390px;padding:28px 17px 20px;border-radius:24px 24px 18px 18px;}.cart-auth-dialog::before{content:'';display:block;width:38px;height:4px;margin:-15px auto 17px;border-radius:999px;background:#d9d9d4;}.cart-auth-dialog h3{font-size:20px;}.cart-auth-phone-row input{padding-inline:12px;}}
@media(max-width:900px){
  .cart-screen-grid{grid-template-columns:1fr;}
  .cart-summary-panel{position:static;}
}
@media(max-width:600px){
  .cart-screen{min-height:calc(100svh - 58px);background:linear-gradient(180deg,#fafaf9 0%,var(--c-bg) 42%);}
  .cart-screen-header{position:sticky;top:0;z-index:20;padding:calc(10px + env(safe-area-inset-top)) 14px 11px;border-bottom:1px solid rgba(24,24,24,.08);background:rgba(255,255,255,.96);backdrop-filter:blur(12px);}
  .cart-screen-back{width:44px;height:44px;border-radius:14px;background:#fff;box-shadow:0 3px 12px rgba(24,24,24,.05);}
  .cart-screen-title{font-size:20px;letter-spacing:-.35px;}
  .cart-screen-title span{font-size:12px;}
  .cart-screen-body{padding:16px 14px calc(128px + env(safe-area-inset-bottom));}
  .cart-screen-grid{display:flex;flex-direction:column;align-items:stretch;gap:16px;}
  .cart-items-panel,.cart-summary-panel{box-sizing:border-box;width:100%;margin:0;border:1px solid rgba(24,24,24,.08);border-radius:20px;box-shadow:0 8px 26px rgba(24,24,24,.055);background:rgba(255,255,255,.98);}
  .cart-items-panel{padding:15px 16px;}
  .cart-summary-panel{padding:16px;}
  .cart-items-heading{margin-bottom:4px;padding:0 0 3px;}
  .cart-items-heading h2{font-size:15px;letter-spacing:-.1px;}
  .cart-items-heading span{padding:5px 8px;border-radius:999px;background:#f5f5f3;font-size:10px;font-weight:750;}
  .cart-item-card{grid-template-columns:82px minmax(0,1fr);gap:12px;padding:16px 0 14px;align-items:start;}
  .cart-item-card:first-of-type{padding-top:10px;}
  .cart-item-thumb{width:82px;height:82px;border-radius:16px;border:1px solid rgba(24,24,24,.08);box-shadow:0 4px 12px rgba(24,24,24,.06);}
  .cart-item-main{padding-top:1px;}
  .cart-item-name{font-size:14px;line-height:1.35;padding:0;margin-bottom:7px;}
  .cart-item-variants{gap:5px;margin-bottom:8px;}
  .cart-item-variant{font-size:10px;padding:5px 7px;border-radius:8px;background:#fafafa;}
  .cart-item-pricing{gap:5px;align-items:baseline;}
  .cart-item-unit{font-size:10px;}
  .cart-item-line{font-size:15px;color:var(--c-orange);}
  .cart-item-old{font-size:10px;}
  .cart-item-controls{grid-column:1 / -1;grid-row:2;flex-direction:row;align-items:center;justify-content:space-between;padding:12px 0 0;margin-top:2px;border-top:1px dashed #ececea;}
  .cart-item-limit{font-size:9.5px;text-align:left;}
  .cart-qty-stepper{border-color:#d8d8d5;border-radius:14px;box-shadow:0 3px 10px rgba(24,24,24,.04);}
  .cart-qty-stepper button,.cart-qty-stepper input{width:48px;height:48px;}
  .cart-qty-stepper input{width:38px;font-size:15px;}
  .cart-remove-icon{width:44px;height:44px;border:1px solid #ffd9d9;background:#fff7f7;color:#cf5757;border-radius:13px;}
  .cart-items-actions{display:flex;flex-wrap:nowrap;align-items:stretch;gap:10px;margin-top:2px;padding-top:15px;border-top:1px solid #ededeb;}
  .cart-items-actions .btn,.cart-items-actions button{min-height:46px;padding:0 10px;font-size:11.5px;white-space:nowrap;border-radius:13px;}
  .cart-items-actions .btn{flex:1;background:#fff;}
  .cart-items-actions form{flex:1;}
  .cart-items-actions .cart-clear-btn{width:100%;border:1px solid #fecaca;background:#fff7f7;color:#c24141;font:inherit;font-weight:800;cursor:pointer;}
  .cart-summary-panel h2{font-size:16px;}
  .cart-summary-head{margin-bottom:14px;}
  .cart-shipping-progress{margin-bottom:14px;}
  .cart-summary-row{margin-bottom:10px;font-size:12.5px;}
  .cart-total-row{padding:14px;border:1px solid rgba(24,24,24,.06);border-radius:15px;background:linear-gradient(135deg,#f7f7f5 0%,#efefec 100%);}
  .cart-total-row strong{font-size:19px;}
  .cart-summary-checkout{display:none;}
  .cart-checkout-bar{position:fixed;left:10px;right:10px;bottom:calc(58px + 8px + env(safe-area-inset-bottom));z-index:9998;margin:0;padding:10px;border:1px solid rgba(24,24,24,.1);border-radius:18px;box-shadow:0 8px 28px rgba(24,24,24,.16);background:rgba(255,255,255,.98);backdrop-filter:blur(14px);display:flex;flex-wrap:wrap;gap:10px;}
  .cart-checkout-total{padding:0 3px;gap:2px;}
  .cart-checkout-total span{font-size:10px;font-weight:700;}
  .cart-checkout-total strong{font-size:17px;}
  .cart-checkout-button{min-width:0;flex:0 0 56%;min-height:48px;font-size:13px;border-radius:13px;box-shadow:0 4px 12px rgba(0,0,0,.12);}
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
<div class="cart-screen" dir="{{ $cartRtl ? 'rtl' : 'ltr' }}" data-free-shipping-enabled="{{ $freeShippingEnabled ? '1' : '0' }}" data-free-shipping-threshold="{{ $freeShippingThreshold }}" data-discount="{{ $discount }}">
  <header class="cart-screen-header">
    <a href="{{ route('shop') }}" class="cart-screen-back" aria-label="{{ $cartRtl ? 'ارجع' : 'Go back' }}">
      <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="cart-screen-title">{{ $cartRtl ? 'سلتك' : 'Your Cart' }} <span>({{ $itemCount }} {{ $cartRtl ? ($itemCount === 1 ? 'منتج' : 'منتجات') : ($itemCount === 1 ? 'item' : 'items') }})</span></h1>
  </header>

  <main class="cart-screen-body">
    @if(session('error'))
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
                  @if(!empty($item['attrs']) || !empty($item['sku']))
                    <div class="cart-item-variants" aria-label="{{ $cartRtl ? 'الاختيارات المحددة' : 'Selected options' }}">
                      @if(!empty($item['sku']))<span class="cart-item-variant"><strong>{{ $cartRtl ? 'كود المنتج' : 'SKU' }}</strong> {{ $item['sku'] }}</span>@endif
                      @foreach($item['attrs'] ?? [] as $k => $v)
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
                </div>
                <div class="cart-item-controls">
                  @php
                    $itemMinimum = (int) ($item['minimum_qty'] ?? 1);
                    $itemMaximum = max($itemMinimum, (int) ($item['maximum_qty'] ?? $item['stock']));
                  @endphp
                  <div class="cart-qty-stepper{{ !empty($item['is_unavailable']) ? ' is-disabled' : '' }}" aria-label="{{ $cartRtl ? 'الكمية لـ' : 'Quantity for' }} {{ $item['display_name'] ?? $item['name'] }}">
                    <form method="POST" action="{{ route('cart.update', $rowId) }}" class="cart-qty-form">
                      @csrf
                      <input type="hidden" name="qty" value="{{ max($itemMinimum, $item['qty'] - 1) }}">
                      <button type="submit" aria-label="{{ $cartRtl ? 'قلّل الكمية' : 'Decrease quantity' }}" @disabled(!empty($item['is_unavailable']) || $item['qty'] <= $itemMinimum)>−</button>
                    </form>
                    <span class="cart-qty-value" aria-label="{{ $cartRtl ? 'الكمية' : 'Quantity' }}">{{ $item['qty'] }}</span>
                    <form method="POST" action="{{ route('cart.update', $rowId) }}" class="cart-qty-form">
                      @csrf
                      <input type="hidden" name="qty" value="{{ min($itemMaximum, $item['qty'] + 1) }}">
                      <button type="submit" aria-label="{{ $cartRtl ? 'زوّد الكمية' : 'Increase quantity' }}" @disabled(!empty($item['is_unavailable']) || $item['qty'] >= $itemMaximum)>+</button>
                    </form>
                  </div>
                  <form method="POST" action="{{ route('cart.remove', $rowId) }}" class="cart-remove-form">
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
            <form action="{{ route('cart.clear') }}" method="POST">
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

          @if($coupon)
            <div class="cart-applied-coupon">
              <span>{{ !empty($coupon['free_shipping']) && $cartRtl ? 'كوبون توصيل مجاني' : (!empty($coupon['free_shipping']) ? 'Free shipping coupon' : ($cartRtl ? 'كود الخصم' : 'Coupon')) }} <strong>{{ strtoupper($coupon['code']) }}</strong> {{ $cartRtl ? 'اتطبّق' : 'applied' }}</span>
              <form action="{{ route('cart.coupon.remove') }}" method="POST">@csrf @method('DELETE')<button type="submit">{{ $cartRtl ? 'شيل' : 'Remove' }}</button></form>
            </div>
          @else
            <details class="cart-promo">
              <summary>{{ $cartRtl ? 'معاك كود خصم؟' : 'Have a promo code?' }}</summary>
              <form class="cart-promo-form" action="{{ route('cart.coupon') }}" method="POST">
                @csrf
                <input type="text" name="code" placeholder="{{ $cartRtl ? 'اكتب كود الخصم' : 'Enter promo code' }}" autocomplete="off" required>
                <button type="submit">{{ $cartRtl ? 'طبّق' : 'Apply' }}</button>
              </form>
              <div id="coupon-msg" style="font-size:11px;margin-top:7px"></div>
            </details>
          @endif

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
          <a href="{{ auth()->check() ? route('checkout') : route('login', ['checkout' => 1]) }}" class="cart-checkout-button cart-summary-checkout{{ $hasUnavailableItems ? ' is-blocked' : '' }}" data-cart-checkout @if($hasUnavailableItems) aria-disabled="true" tabindex="-1" @endif @if($guestOtpCheckout) data-guest-otp-checkout @endif>{{ $cartRtl ? 'إتمام الطلب' : 'Checkout' }} <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
        </aside>
      </div>

      <div class="cart-checkout-bar">
        <div class="cart-checkout-warning cart-checkout-warning-bar" data-cart-checkout-warning @if(!$hasUnavailableItems) hidden @endif>{{ $cartRtl ? 'فيه منتج غير متوفر. شيله من السلة عشان تقدر تكمل الطلب.' : 'Remove out-of-stock items to continue.' }}</div>
        <div class="cart-checkout-total"><span>{{ $cartRtl ? 'الإجمالي' : 'Total' }}</span><strong id="cart-sticky-total">{{ number_format($total, 2) }} EGP</strong></div>
        <a href="{{ auth()->check() ? route('checkout') : route('login', ['checkout' => 1]) }}" class="cart-checkout-button{{ $hasUnavailableItems ? ' is-blocked' : '' }}" data-cart-checkout @if($hasUnavailableItems) aria-disabled="true" tabindex="-1" @endif @if($guestOtpCheckout) data-guest-otp-checkout @endif>{{ $cartRtl ? 'إتمام الطلب' : 'Checkout' }} <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      </div>
    @endif
  </main>
</div>
<div id="cart-toast" class="cart-toast" role="status" aria-live="polite"></div>
@if($guestOtpCheckout)
<div id="guest-otp-checkout" class="cart-auth-modal" hidden role="dialog" aria-modal="true" aria-labelledby="guest-otp-title">
  <div class="cart-auth-dialog" dir="{{ $cartRtl ? 'rtl' : 'ltr' }}">
    <button type="button" class="cart-auth-close" id="guest-otp-close" aria-label="{{ $cartRtl ? 'إغلاق' : 'Close' }}">×</button>
    <div id="guest-otp-phone-step" class="cart-auth-step">
      <div class="cart-auth-heading">
        <span class="cart-auth-kicker">{{ $cartRtl ? 'إتمام الطلب' : 'Complete your order' }}</span>
        <h3 id="guest-otp-title">{{ $cartRtl ? 'اكتب رقم موبايلك' : 'Enter your mobile number' }}</h3>
      </div>
      <p>{{ $cartRtl ? 'هنبعتلك كود تأكيد عشان تكمل الطلب بسرعة.' : 'We will send you a verification code so you can continue securely.' }}</p>
      <div class="cart-auth-field-heading">
        <label class="cart-auth-label" for="guest-otp-phone">{{ $cartRtl ? 'رقم الموبايل' : 'Mobile number' }}</label>
        <span class="cart-auth-helper">{{ $cartRtl ? 'من غير +20' : 'Without +20' }}</span>
      </div>
      <div class="cart-auth-phone-row">
        <span class="cart-auth-prefix">🇪🇬 +20</span>
        <input type="tel" id="guest-otp-phone" inputmode="tel" maxlength="11" placeholder="01xxxxxxxxx" autocomplete="tel">
      </div>
      <button type="button" class="cart-auth-submit" id="guest-otp-submit">
        <span class="cart-auth-submit-label">{{ $cartRtl ? 'ابعت كود التأكيد' : 'Send verification code' }}</span>
      </button>
      <p id="guest-otp-message" class="cart-auth-message" role="status" aria-live="polite"></p>
    </div>
    <div id="guest-otp-code-step" class="cart-auth-step" hidden>
      <div class="cart-auth-heading">
        <span class="cart-auth-kicker">{{ $cartRtl ? 'إتمام الطلب' : 'Complete your order' }}</span>
        <h3>{{ $cartRtl ? 'اكتب كود التأكيد' : 'Enter verification code' }}</h3>
      </div>
      <p class="cart-auth-sent-copy">{{ $cartRtl ? 'الكود اتبعت على' : 'The code was sent to' }} <strong id="guest-otp-phone-display"></strong></p>
      <div class="cart-auth-code-boxes" id="guest-otp-boxes" dir="ltr">
        @for($i = 0; $i < 6; $i++)
          <input type="text" inputmode="numeric" maxlength="1" class="guest-otp-code-input" autocomplete="one-time-code" aria-label="{{ $cartRtl ? 'رقم ' . ($i + 1) : 'Digit ' . ($i + 1) }}">
        @endfor
      </div>
      <p id="guest-otp-code-error" class="cart-auth-message cart-auth-error" role="alert" aria-live="polite"></p>
      <p id="guest-otp-code-success" class="cart-auth-message cart-auth-success" role="status" aria-live="polite"></p>
      <button type="button" class="cart-auth-submit" id="guest-otp-verify">
        <span>{{ $cartRtl ? 'تأكيد الكود' : 'Verify code' }}</span>
      </button>
      <div class="cart-auth-resend-row">
        <span id="guest-otp-resend-timer">{{ $cartRtl ? 'إعادة الإرسال خلال' : 'Resend in' }} <strong id="guest-otp-countdown">60</strong> {{ $cartRtl ? 'ثانية' : 's' }}</span>
        <button type="button" id="guest-otp-resend" hidden>{{ $cartRtl ? 'إعادة إرسال الكود' : 'Resend code' }}</button>
      </div>
      <button type="button" class="cart-auth-change-phone" id="guest-otp-change-phone">{{ $cartRtl ? 'تغيير رقم الموبايل' : 'Change phone number' }}</button>
      <div id="guest-otp-dev-box" class="cart-auth-dev-box" hidden aria-live="polite">
        <div>{{ $cartRtl ? 'وضع التجربة — كود التأكيد' : 'Development mode — verification code' }}</div>
        <strong id="guest-otp-dev-value"></strong>
        <small>{{ $cartRtl ? 'وضع تجربة فقط · الكود ما اتبعتش برسالة SMS حقيقية' : 'Development preview only · not sent via real SMS' }}</small>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const CART_RTL = {{ $cartRtl ? 'true' : 'false' }};
const guestOtpModal = document.getElementById('guest-otp-checkout');
const guestOtpPhoneStep = document.getElementById('guest-otp-phone-step');
const guestOtpCodeStep = document.getElementById('guest-otp-code-step');
const guestOtpPhone = document.getElementById('guest-otp-phone');
const guestOtpSubmit = document.getElementById('guest-otp-submit');
const guestOtpClose = document.getElementById('guest-otp-close');
const guestOtpMessage = document.getElementById('guest-otp-message');
const guestOtpPhoneDisplay = document.getElementById('guest-otp-phone-display');
const guestOtpCodeInputs = [...document.querySelectorAll('.guest-otp-code-input')];
const guestOtpCodeError = document.getElementById('guest-otp-code-error');
const guestOtpCodeSuccess = document.getElementById('guest-otp-code-success');
const guestOtpVerify = document.getElementById('guest-otp-verify');
const guestOtpResendTimer = document.getElementById('guest-otp-resend-timer');
const guestOtpCountdown = document.getElementById('guest-otp-countdown');
const guestOtpResend = document.getElementById('guest-otp-resend');
const guestOtpChangePhone = document.getElementById('guest-otp-change-phone');
const guestOtpDevBox = document.getElementById('guest-otp-dev-box');
const guestOtpDevValue = document.getElementById('guest-otp-dev-value');
let guestOtpPhoneValue = '';
let guestOtpCountdownTimer = null;

function setGuestOtpMessage(message, isError = false){
  if(!guestOtpMessage) return;
  guestOtpMessage.textContent = message || '';
  guestOtpMessage.style.color = isError ? '#c02020' : 'var(--c-mid)';
}
function setGuestOtpCodeError(message){
  if(guestOtpCodeError) guestOtpCodeError.textContent = message || '';
  if(guestOtpCodeSuccess) guestOtpCodeSuccess.textContent = '';
}
function setGuestOtpCodeSuccess(message){
  if(guestOtpCodeSuccess) guestOtpCodeSuccess.textContent = message || '';
  if(guestOtpCodeError) guestOtpCodeError.textContent = '';
}
function clearGuestOtpCode(){
  guestOtpCodeInputs.forEach((input) => { input.value = ''; input.classList.remove('filled'); });
}
function getGuestOtpCode(){ return guestOtpCodeInputs.map((input) => input.value).join(''); }
function updateGuestOtpCodeState(){ guestOtpCodeInputs.forEach((input) => input.classList.toggle('filled', input.value !== '')); }
function showGuestOtpDevelopmentCode(devOtp){
  if(!guestOtpDevBox || !guestOtpDevValue) return;
  if(/^\d{6}$/.test(String(devOtp || ''))){
    guestOtpDevValue.textContent = String(devOtp);
    guestOtpDevBox.hidden = false;
  } else {
    guestOtpDevValue.textContent = '';
    guestOtpDevBox.hidden = true;
  }
}
function startGuestOtpCountdown(seconds = 60){
  clearInterval(guestOtpCountdownTimer);
  let remaining = Math.max(0, Number(seconds) || 0);
  if(guestOtpCountdown) guestOtpCountdown.textContent = remaining;
  if(guestOtpResendTimer) guestOtpResendTimer.hidden = remaining <= 0;
  if(guestOtpResend) { guestOtpResend.hidden = remaining > 0; guestOtpResend.disabled = false; }
  if(remaining <= 0) return;
  guestOtpCountdownTimer = setInterval(() => {
    remaining -= 1;
    if(guestOtpCountdown) guestOtpCountdown.textContent = remaining;
    if(remaining <= 0){
      clearInterval(guestOtpCountdownTimer);
      if(guestOtpResendTimer) guestOtpResendTimer.hidden = true;
      if(guestOtpResend) guestOtpResend.hidden = false;
    }
  }, 1000);
}
function showGuestOtpPhoneStep(){
  clearInterval(guestOtpCountdownTimer);
  if(guestOtpPhoneStep) guestOtpPhoneStep.hidden = false;
  if(guestOtpCodeStep) guestOtpCodeStep.hidden = true;
  setGuestOtpMessage(''); setGuestOtpCodeError(''); setGuestOtpCodeSuccess(''); clearGuestOtpCode(); showGuestOtpDevelopmentCode('');
  window.setTimeout(() => guestOtpPhone?.focus(), 30);
}
function showGuestOtpCodeStep(phone, devOtp = ''){
  guestOtpPhoneValue = phone;
  if(guestOtpPhoneDisplay) guestOtpPhoneDisplay.textContent = '+20' + phone.replace(/^0/, '');
  if(guestOtpPhoneStep) guestOtpPhoneStep.hidden = true;
  if(guestOtpCodeStep) guestOtpCodeStep.hidden = false;
  setGuestOtpMessage(''); setGuestOtpCodeError(''); setGuestOtpCodeSuccess(''); clearGuestOtpCode(); showGuestOtpDevelopmentCode(devOtp);
  startGuestOtpCountdown(60);
  window.setTimeout(() => guestOtpCodeInputs[0]?.focus(), 30);
}
function closeGuestOtpModal(){
  if(!guestOtpModal) return;
  guestOtpModal.hidden = true;
  document.body.style.overflow = '';
  showGuestOtpPhoneStep();
}
function openGuestOtpModal(){
  if(!guestOtpModal) return;
  guestOtpModal.hidden = false;
  document.body.style.overflow = 'hidden';
  showGuestOtpPhoneStep();
}
async function sendGuestOtp(phone, isResend = false){
  const status = isResend ? setGuestOtpCodeSuccess : setGuestOtpMessage;
  if(isResend){
    if(guestOtpResend) guestOtpResend.disabled = true;
    setGuestOtpCodeSuccess(CART_RTL ? 'جاري إعادة إرسال الكود…' : 'Resending code…');
  } else {
    if(guestOtpSubmit) guestOtpSubmit.disabled = true;
    setGuestOtpMessage(CART_RTL ? 'بنرسل كود التأكيد…' : 'Sending verification code…');
  }
  try{
    const res = await fetch('/auth/send-otp', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({phone, context:'checkout'})
    });
    const contentType = res.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await res.json() : {success:false,message:await res.text()};
    if(!data.success) throw new Error(data.message || (CART_RTL ? 'مش قادرين نبعت الكود دلوقتي.' : 'We could not send the code right now.'));
    guestOtpPhoneValue = phone;
    sessionStorage.setItem('otp_phone', phone);
    if(data.dev_otp) sessionStorage.setItem('dev_otp', data.dev_otp);
    if(isResend){
      showGuestOtpDevelopmentCode(data.dev_otp);
      setGuestOtpCodeSuccess(CART_RTL ? 'اتبعث كود جديد.' : 'A new code was sent.');
      startGuestOtpCountdown(60);
    } else {
      showGuestOtpCodeStep(phone, data.dev_otp || sessionStorage.getItem('dev_otp') || '');
      sessionStorage.removeItem('dev_otp');
    }
  }catch(error){
    if(isResend){
      setGuestOtpCodeError(error?.message || (CART_RTL ? 'مش قادرين نعيد إرسال الكود دلوقتي.' : 'Could not resend the code.'));
      if(guestOtpResend) guestOtpResend.disabled = false;
    } else {
      setGuestOtpMessage(error?.message || (CART_RTL ? 'حصلت مشكلة. جرّب تاني.' : 'Something went wrong. Please try again.'), true);
      if(guestOtpSubmit) guestOtpSubmit.disabled = false;
    }
  }
}
async function verifyGuestOtp(){
  const otp = getGuestOtpCode();
  if(otp.length !== guestOtpCodeInputs.length){ setGuestOtpCodeError(CART_RTL ? 'اكتب الـ6 أرقام كلها.' : 'Please enter all 6 digits.'); return; }
  if(guestOtpVerify) { guestOtpVerify.disabled = true; guestOtpVerify.setAttribute('aria-busy','true'); }
  setGuestOtpCodeError(''); setGuestOtpCodeSuccess(CART_RTL ? 'جاري التأكيد…' : 'Verifying…');
  try{
    const res = await fetch('/auth/verify-otp', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body:JSON.stringify({phone:guestOtpPhoneValue, otp})
    });
    const contentType = res.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await res.json() : {success:false,message:await res.text()};
    if(!data.success) throw new Error(data.message || (CART_RTL ? 'الكود غير صحيح.' : 'Incorrect code.'));
    sessionStorage.removeItem('otp_phone'); sessionStorage.removeItem('dev_otp');
    setGuestOtpCodeSuccess(CART_RTL ? 'تم التأكيد. جاري التحويل…' : 'Verified. Redirecting…');
    window.setTimeout(() => { window.location.href = data.redirect || '/checkout'; }, 600);
  }catch(error){
    setGuestOtpCodeError(error?.message || (CART_RTL ? 'في مشكلة في النت. جرّب تاني.' : 'Network error. Please try again.'));
    setGuestOtpCodeSuccess(''); clearGuestOtpCode();
    if(guestOtpVerify) { guestOtpVerify.disabled = false; guestOtpVerify.setAttribute('aria-busy','false'); }
    window.setTimeout(() => guestOtpCodeInputs[0]?.focus(), 30);
  }
}
document.querySelectorAll('[data-cart-checkout][data-guest-otp-checkout]').forEach((link) => link.addEventListener('click', (event) => {
  event.preventDefault();
  openGuestOtpModal();
}));
guestOtpClose?.addEventListener('click', closeGuestOtpModal);
guestOtpModal?.addEventListener('click', (event) => { if(event.target === guestOtpModal) closeGuestOtpModal(); });
document.addEventListener('keydown', (event) => { if(event.key === 'Escape' && guestOtpModal && !guestOtpModal.hidden) closeGuestOtpModal(); });
guestOtpPhone?.addEventListener('input', () => { guestOtpPhone.value = guestOtpPhone.value.replace(/[^0-9]/g, ''); });
guestOtpSubmit?.addEventListener('click', () => {
  const phone = (guestOtpPhone?.value || '').trim();
  if(!phone || phone.length < 9){ setGuestOtpMessage(CART_RTL ? 'اكتب رقم موبايل صحيح.' : 'Enter a valid mobile number.', true); return; }
  sendGuestOtp(phone);
});
guestOtpCodeInputs.forEach((input, index) => {
  input.addEventListener('input', (event) => {
    const value = event.target.value.replace(/[^0-9]/g, '');
    event.target.value = value.slice(-1);
    updateGuestOtpCodeState();
    if(value && index < guestOtpCodeInputs.length - 1) guestOtpCodeInputs[index + 1].focus();
    if(getGuestOtpCode().length === guestOtpCodeInputs.length) verifyGuestOtp();
  });
  input.addEventListener('keydown', (event) => {
    if(event.key === 'Backspace' && !input.value && index > 0){ guestOtpCodeInputs[index - 1].value = ''; guestOtpCodeInputs[index - 1].focus(); updateGuestOtpCodeState(); }
  });
  input.addEventListener('paste', (event) => {
    event.preventDefault();
    const text = (event.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, guestOtpCodeInputs.length);
    [...text].forEach((digit, offset) => { if(guestOtpCodeInputs[offset]) guestOtpCodeInputs[offset].value = digit; });
    updateGuestOtpCodeState();
    if(text.length === guestOtpCodeInputs.length) verifyGuestOtp();
  });
});
guestOtpVerify?.addEventListener('click', verifyGuestOtp);
guestOtpResend?.addEventListener('click', () => sendGuestOtp(guestOtpPhoneValue, true));
guestOtpChangePhone?.addEventListener('click', showGuestOtpPhoneStep);
</script>
@endpush

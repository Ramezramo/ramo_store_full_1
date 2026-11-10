@extends('layouts.app')

@section('meta_robots', 'noindex,nofollow')

@php
  $isAr = session('locale') === 'ar';
  $governoratesAr = [
    'Cairo' => 'القاهرة', 'Giza' => 'الجيزة', 'Alexandria' => 'الإسكندرية', 'Aswan' => 'أسوان', 'Asyut' => 'أسيوط',
    'Beheira' => 'البحيرة', 'Beni Suef' => 'بني سويف', 'Dakahlia' => 'الدقهلية', 'Damietta' => 'دمياط', 'Faiyum' => 'الفيوم',
    'Gharbia' => 'الغربية', 'Ismailia' => 'الإسماعيلية', 'Kafr El Sheikh' => 'كفر الشيخ', 'Luxor' => 'الأقصر', 'Matrouh' => 'مطروح',
    'Minya' => 'المنيا', 'Monufia' => 'المنوفية', 'New Valley' => 'الوادي الجديد', 'North Sinai' => 'شمال سينا',
    'Port Said' => 'بورسعيد', 'Qalyubia' => 'القليوبية', 'Qena' => 'قنا', 'Red Sea' => 'البحر الأحمر',
    'Sharqia' => 'الشرقية', 'Sohag' => 'سوهاج', 'South Sinai' => 'جنوب سينا', 'Suez' => 'السويس',
  ];
  $paymentLabelsAr = [
    'manual_wallet' => ['title' => 'الدفع بالمحفظة', 'description' => 'حوّل من أي محفظة موبايل مصرية', 'data_label' => 'حوّل لـ'],
    'manual_instapay' => ['title' => 'الدفع بإنستاباي', 'description' => 'حوّل باستخدام إنستاباي', 'data_label' => 'حوّل لـ'],
    'cod' => ['title' => 'الدفع عند الاستلام', 'description' => 'ادفع لما طلبك يوصل', 'data_label' => 'التفاصيل'],
    'vodafone_cash' => ['title' => 'فودافون كاش', 'description' => 'حوّل من محفظة فودافون كاش', 'data_label' => 'حوّل لـ'],
    'bank_transfer' => ['title' => 'تحويل بنكي', 'description' => 'حوّل على حسابنا البنكي', 'data_label' => 'بيانات الحساب'],
    'fawry' => ['title' => 'فوري', 'description' => 'ادفع من أي منفذ فوري', 'data_label' => 'التفاصيل'],
    'credit_card' => ['title' => 'كارت بنكي', 'description' => 'فيزا أو ماستركارد', 'data_label' => 'التفاصيل'],
  ];
  $initialLatitude = old('latitude', $savedAddress['latitude'] ?? ($user->latitude ?? null));
  $initialLongitude = old('longitude', $savedAddress['longitude'] ?? ($user->longitude ?? null));
  $hasSavedLocation = is_numeric($initialLatitude) && is_numeric($initialLongitude);
  $checkoutText = [
    'isAr' => $isAr,
    'requiredField' => $isAr ? 'اكتب البيانات المطلوبة هنا.' : 'Fill in this field.',
    'requiredOption' => $isAr ? 'اختار من القائمة.' : 'Choose an option.',
    'invalidEmail' => $isAr ? 'اكتب عنوان بريد إلكتروني صحيح.' : 'Enter a valid email address.',
    'invalidPhone' => $isAr ? 'اكتب رقم موبايل صحيح.' : 'Enter a valid phone number.',
    'selected' => $isAr ? 'تم اختيار:' : 'Selected:',
    'locationSelected' => $isAr ? 'تم تحديد المكان.' : 'Location selected.',
    'detailsUnavailable' => $isAr ? 'تم تحديد المكان، بس ماقدرناش نجيب تفاصيل العنوان.' : 'Location selected, but address details could not be loaded.',
    'mapLoading' => $isAr ? 'بنحمّل الخريطة…' : 'Loading map…',
    'mapUnavailable' => $isAr ? 'ماقدرناش نحمّل الخريطة. تقدر تكتب عنوانك بنفسك.' : 'The map could not be loaded. You can still enter your address manually.',
    'mapRetry' => $isAr ? 'جرّب تحميل الخريطة تاني' : 'Retry loading map',
    'locating' => $isAr ? 'بنحدد مكانك…' : 'Locating...',
    'accessDenied' => $isAr ? 'المتصفح رفض إذن الموقع. افتح إعدادات الموقع بجانب اسم الموقع واختار «السماح»، وبعدها اضغط «حاول تحديد موقعي تاني».' : 'Your browser denied location access. Open the site location settings, choose Allow, then press “Try my location again.”',
    'accessBlocked' => $isAr ? 'إذن الموقع متوقف لهذا الموقع من المتصفح. فعّله من إعدادات الموقع، أو استخدم الخريطة يدويًا لتحديد مكانك.' : 'Location access is blocked for this site. Enable it in the site settings or choose your location manually on the map.',
    'detectFailed' => $isAr ? 'المتصفح ماقدرش يحدد مكانك دلوقتي. اتأكد إن الـ GPS والإنترنت شغالين، أو اختار مكانك يدويًا على الخريطة.' : 'Your browser could not detect your location. Check that GPS and internet are available, or choose your location manually on the map.',
    'locationFallback' => $isAr ? 'لو مش عايز تسمح بالموقع، اضغط «تحديد المكان يدويًا» واختار نقطة التوصيل على الخريطة.' : 'If you prefer not to allow location access, choose “Choose location manually” and select your delivery point on the map.',
    'manualLocation' => $isAr ? 'تحديد المكان يدويًا' : 'Choose location manually',
    'retryLocation' => $isAr ? 'حاول تحديد موقعي تاني' : 'Try my location again',
    'autoLocked' => $isAr ? 'تم تثبيت العلامة. فعّل «حرّك العلامة» عشان تغيّرها.' : 'Pin locked. Turn on “Move pin” to adjust it.',
    'placePin' => $isAr ? 'اضغط على الخريطة عشان تحدد مكان التوصيل.' : 'Tap the map to place your delivery pin.',
    'manualReady' => $isAr ? 'اضغط على الخريطة أو اسحب العلامة عشان تغيّر مكان التوصيل.' : 'Tap the map or drag the pin to adjust it.',
    'insecureLocation' => $isAr ? 'الموقع الحالي مش متاح على الاتصال ده. اختار نقطة على الخريطة أو اكتب عنوانك.' : 'Current location is unavailable on this connection. Choose a pin on the map or type your address.',
  ];
@endphp
@section('title', $isAr ? 'إتمام الطلب — Ramo Store' : 'Checkout — Ramo Store')

@push('styles')
<style>
  .ck-auth-widget{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:0 0 22px;padding:16px 18px;border:1px solid #f0d6ca;border-radius:14px;background:#fff9f6}
  .ck-auth-copy{min-width:0}.ck-auth-kicker{display:block;margin-bottom:4px;color:#e85d26;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
  .ck-auth-title{margin:0;color:#181818;font-size:15px;font-weight:800}.ck-auth-desc{margin:4px 0 0;color:#686868;font-size:12px;line-height:1.45}
  .ck-auth-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}.ck-auth-action{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 12px;border:1px solid #1b1b1b;border-radius:9px;background:#1b1b1b;color:#fff;font-size:12px;font-weight:750;line-height:1;text-decoration:none;white-space:nowrap;transition:.15s}.ck-auth-action:hover{background:#343434;border-color:#343434;color:#fff}.ck-auth-action-light{border-color:#d5d5d5;background:#fff;color:#272727}.ck-auth-action-light:hover{border-color:#aaa;background:#f6f6f6;color:#111}
  .ck-save-address{display:flex;align-items:center;gap:13px;min-height:64px;margin-top:4px;padding:12px 15px;border:1px solid #e4e4e4;border-radius:12px;background:#fcfcfc;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s}.ck-save-address:hover{border-color:#cfcfcf;background:#fff}.ck-save-address:has(input:focus-visible){border-color:#e85d26;box-shadow:0 0 0 3px rgba(232,93,38,.14)}.ck-save-address input[type="checkbox"]{width:20px!important;height:20px!important;min-width:20px;margin:0!important;flex:0 0 20px;accent-color:#e85d26;cursor:pointer}.ck-save-address-copy{display:flex;flex-direction:column;gap:3px;min-width:0}.ck-save-address-title{color:#202020;font-size:13px;font-weight:800;line-height:1.25}.ck-save-address-desc{color:#777;font-size:12px;line-height:1.35}
  .checkout-page .ck-page-heading{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-block-end:18px}
  .ck-page-heading h1{margin:0;color:#171717;font-size:clamp(1.375rem,4vw,1.875rem);font-weight:850;line-height:1.2}
  .ck-page-heading a,.ck-address-jump{color:#555;font-size:.8125rem;font-weight:650;text-decoration:underline;text-underline-offset:3px}
  .checkout-page .ck-section{padding:clamp(14px,3vw,24px)}
  .checkout-page .ck-title{margin-block-end:12px}
  .ck-location-map-panel{padding:10px;border:1px solid #ebebe8;border-radius:14px;background:#fafaf9}
  .ck-location-heading{display:flex;align-items:baseline;justify-content:space-between;flex-wrap:wrap;gap:4px 10px;margin-block-end:8px}
  .ck-location-heading strong{font-size:.875rem;font-weight:800}
  .ck-location-actions{display:flex;align-items:stretch;gap:6px;margin-block-end:7px}
  .ck-location-action,.checkout-page .form-group .ck-location-switch{
    flex:1 1 0;min-inline-size:0;min-block-size:44px;margin:0;padding:6px 9px;
    border:1px solid #deded9;border-radius:9px;font:inherit;font-size:.8125rem;
    font-weight:750;line-height:1.25;letter-spacing:normal;text-transform:none;cursor:pointer;
  }
  .ck-location-action{display:flex;align-items:center;justify-content:center;gap:6px;text-align:center;background:#171717;border-color:#171717;color:#fff}
  .ck-location-action svg{flex:none}
  .ck-location-action:hover{background:#303030}
  .ck-location-action:disabled{cursor:wait;opacity:.65}
  .checkout-page .form-group .ck-location-switch{display:flex;align-items:center;justify-content:space-between;gap:6px;background:#fff;color:#252525}
  .ck-location-switch input{position:absolute;width:1px;height:1px;padding:0;margin:0;border:0;opacity:0;pointer-events:none}
  .ck-switch-track{position:relative;display:inline-flex;width:34px;height:20px;flex:0 0 34px;align-items:center;padding:3px;border-radius:999px;background:#bcbcb6;transition:background .15s}
  .ck-switch-track::after{width:14px;height:14px;border-radius:50%;background:#fff;box-shadow:0 1px 3px #0002;content:"";transition:transform .15s}
  .ck-location-switch input:checked + .ck-switch-track{background:#242424}
  .ck-location-switch input:checked + .ck-switch-track::after{transform:translateX(14px)}
  .checkout-page-ar .ck-location-switch input:checked + .ck-switch-track::after{transform:translateX(-14px)}
  .ck-location-mode-note{display:block;margin:0 0 8px;color:#626767;font-size:.75rem;line-height:1.4}
  .ck-location-action:focus-visible,.ck-location-switch:has(input:focus-visible),.ck-map-load-btn:focus-visible,.ck-location-fallback-btn:focus-visible,.ck-address-jump:focus-visible{
    outline:2px solid #b44722;outline-offset:2px
  }
  .ck-location-fallback{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-block-start:8px}
  .ck-location-fallback[hidden]{display:none}
  .ck-location-fallback-copy{color:#6f3e2f;font-size:.75rem}
  .ck-location-fallback-actions{display:flex;flex-wrap:wrap;gap:6px}
  .ck-location-fallback-btn,.ck-map-load-btn{min-block-size:44px;padding:4px 9px;border:1px solid #d9d8d4;border-radius:8px;background:#fff;color:#333;font:inherit;font-size:.75rem;font-weight:700;cursor:pointer}
  .ck-map-shell{position:relative;isolation:isolate;width:100%;height:clamp(190px,36svh,270px);border:1px solid #e1e1dd;border-radius:10px;overflow:hidden;background:#f5f5f3}
  .ck-map-shell.map-location-locked{border-color:#b7d9c6}
  .ck-map-canvas{width:100%;height:100%}
  .ck-map-canvas.map-location-locked{cursor:default}
  .ck-map-placeholder{position:absolute;inset:0;display:grid;place-items:center;padding:12px;background:#f5f5f3;z-index:500;text-align:center}
  .ck-map-placeholder[hidden]{display:none}
  .ck-map-placeholder-inner{display:flex;max-width:240px;align-items:center;flex-direction:column;gap:8px}
  .ck-map-placeholder-copy{color:#555;font-size:.8125rem;line-height:1.4}
  #map-locating-overlay{position:absolute;inset:0;z-index:900;display:grid;place-content:center;background:#fffd;color:#333;font-size:.875rem;font-weight:750;text-align:center}
  #map-locating-overlay[hidden]{display:none}
  .checkout-page #checkout-address{scroll-margin-block-start:80px}
  .checkout-page .ck-address-fields label{font-size:.8125rem;text-transform:none;letter-spacing:normal}
  @media(max-width:600px){
    .checkout-page .ck-page-heading{margin-block-end:12px}
    .ck-map-shell{height:clamp(170px,27svh,220px)}
  }
  .ck-location-status{display:flex;align-items:flex-start;gap:8px;margin:10px 0;padding:11px 13px;border:1px solid #d9e6dc;border-radius:11px;background:#f5fff8;color:#20613b;font-size:12px;line-height:1.55;font-weight:700}.ck-location-status::before{content:'✓';display:inline-flex;flex:0 0 20px;width:20px;height:20px;align-items:center;justify-content:center;border-radius:50%;background:#d9f5e2;color:#167343;font-weight:900}.ck-location-status[hidden]{display:none}.ck-location-status[data-state="error"]{border-color:#f0cfc4;background:#fff7f4;color:#9a3412}.ck-location-status[data-state="error"]::before{content:'!';background:#ffe1d6;color:#b42318}.ck-location-status[data-state="loading"]{border-color:#eadfbd;background:#fffdf3;color:#8a6914}.ck-location-status[data-state="loading"]::before{content:'…';background:#fff0b8;color:#8a6914}

  .checkout-page-ar{font-family:'Cairo','Tahoma',sans-serif;text-align:right}.checkout-page-ar .ck-auth-actions{justify-content:flex-start}.checkout-page-ar .ck-save-address{direction:rtl}.checkout-page-ar .checkout-layout{direction:rtl}.checkout-page-ar .summary-row{direction:rtl}
  .checkout-page .ck-summary{padding:20px;border:1px solid #e8e8e8;border-radius:20px;background:linear-gradient(145deg,#fff,#fcfcfb);box-shadow:0 12px 30px rgba(24,24,24,.07)}
  .checkout-page .ck-summary-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
  .checkout-page .ck-summary-heading{min-width:0}
  .checkout-page .ck-summary>.ck-summary-header .ck-title{margin:0;font-size:18px;letter-spacing:-.02em}
  .checkout-page .ck-summary-caption{margin:4px 0 0;color:#8a8a8a;font-size:10.5px;line-height:1.4}
  .checkout-page .ck-summary-count{flex:0 0 auto;padding:6px 9px;border:1px solid #eeeeec;border-radius:999px;background:#f8f8f7;color:#6d6d6d;font-size:10px;font-weight:800;white-space:nowrap}
  .checkout-page .ck-summary .ck-items{margin-bottom:14px}
  .checkout-page .ck-summary .ck-item{box-shadow:0 3px 10px rgba(24,24,24,.035)}
  .checkout-page .ck-summary .ck-item-price{font-size:12px}
  .checkout-page .ck-items{gap:10px;margin-bottom:16px}
  .checkout-page .ck-item{display:grid;grid-template-columns:64px minmax(0,1fr) auto;align-items:center;gap:11px;padding:10px;border:1px solid #eeeeee;border-radius:14px;background:#fff;box-shadow:0 4px 12px rgba(24,24,24,.035)}
  .checkout-page .ck-item-img{position:relative;width:64px;height:64px;border-radius:13px;background:#f7f7f5;box-shadow:inset 0 0 0 1px rgba(24,24,24,.04);overflow:visible}
  .checkout-page .ck-item-img img{display:block;width:100%;height:100%;border-radius:inherit;object-fit:cover}
  .checkout-page .ck-item-qty{top:-6px;right:-6px;width:22px;height:22px;background:#e85d26;font-size:10px;font-weight:850;box-shadow:0 2px 5px rgba(232,93,38,.24)}
  .checkout-page .ck-item-details{min-width:0}
  .checkout-page .ck-item-name{display:block;min-width:0;font-size:13px;font-weight:800;line-height:1.35;overflow-wrap:anywhere}
  .checkout-page .ck-item-sku{margin-top:3px;font-size:10px;color:#8a8a8a}
  .checkout-page .ck-item-attrs{display:flex;flex-wrap:wrap;gap:4px;margin-top:7px}
  .checkout-page .ck-item-attrs span{padding:4px 7px;border:1px solid #eeeeee;background:#f8f8f7;color:#777;font-size:10px;line-height:1.15;border-radius:7px}
  .checkout-page .ck-item-attrs span strong{color:#292929}
  .checkout-page .ck-item-price-wrap{min-width:92px;text-align:end}
  .checkout-page .ck-item-price-label{display:block;margin-bottom:3px;color:#999;font-size:9px;line-height:1.2}
  .checkout-page .ck-item-price{color:#c94717;font-size:13px;font-weight:850;line-height:1.25;text-align:end;white-space:nowrap}
  .checkout-page .ck-totals{margin-top:4px;padding:14px 13px 5px;border:1px solid #eeeeec;border-radius:15px;background:#fafaf9}
  .checkout-page .ck-totals .summary-row{min-height:22px;margin-bottom:8px;color:#777;font-size:12px;line-height:1.35}
  .checkout-page .ck-totals .summary-row span:first-child{display:inline-flex;align-items:center;gap:5px}
  .checkout-page .ck-totals .summary-row span:last-child{color:#333;font-weight:750;text-align:end}
  .checkout-page .ck-totals .discount-row{padding:7px 8px;border:1px solid #ccebd8;border-radius:9px;background:#f2fff6;color:#197044}
  .checkout-page .ck-totals .discount-row span:last-child{color:#238653;font-weight:850}
  .checkout-page .ck-totals .free-shipping-row span:last-child{color:#238653;font-weight:850}
  .checkout-page .ck-totals .cod-fee-row{padding:7px 8px;border:1px solid #f4d2c4;border-radius:9px;background:#fff8f4;color:#9a3412}
  .checkout-page .ck-totals .cod-fee-row span:last-child{color:#9a3412;font-weight:850}
  .checkout-page .ck-totals .summary-divider{margin:11px 0;border-color:#e3e3e1}
  .checkout-page .ck-totals .total-row{min-height:42px;margin:0;padding:10px 11px;border-radius:11px;background:#fff;color:#222;font-size:16px;font-weight:850}
  .checkout-page .ck-totals .total-row span:last-child{color:#e85d26;font-size:19px;letter-spacing:-.02em}
  .checkout-page .ck-totals .total-note{margin:9px 1px 0;color:#8a8a8a;font-size:10px;line-height:1.4}
  @media(max-width:600px){.ck-auth-widget{align-items:flex-start;flex-direction:column;gap:13px;padding:15px}.ck-auth-actions{justify-content:flex-start;width:100%}.ck-auth-action{flex:1;padding:0 10px}.ck-save-address{min-height:58px;padding:11px 13px}.ck-location-fallback{align-items:stretch;flex-direction:column}.ck-location-fallback-actions{justify-content:stretch}.ck-location-fallback-btn{flex:1}.checkout-page .ck-summary{padding:14px 12px;border-radius:17px}.checkout-page .ck-summary-header{margin-bottom:12px}.checkout-page .ck-summary>.ck-summary-header .ck-title{font-size:16px}.checkout-page .ck-summary-caption{font-size:10px}.checkout-page .ck-summary-count{padding:5px 8px}.checkout-page .ck-item{grid-template-columns:54px minmax(0,1fr) auto;gap:8px;padding:8px;border-radius:12px}.checkout-page .ck-item-img{width:54px;height:54px;border-radius:11px}.checkout-page .ck-item-name{font-size:12px}.checkout-page .ck-item-price-wrap{min-width:78px}.checkout-page .ck-item-price-label{font-size:8px}.checkout-page .ck-item-price{font-size:11px}.checkout-page .ck-totals{padding:11px 9px 4px}
.checkout-page .ck-totals .summary-row{font-size:11.5px}.checkout-page .ck-totals .total-row{font-size:14px}.checkout-page .ck-totals .total-row span:last-child{font-size:17px}}
  .ck-payment-picker{display:grid;gap:8px}
  .ck-payment-picker select{
    inline-size:100%;min-inline-size:0;min-block-size:48px;padding:8px 12px;scroll-margin-block-start:80px;
    border:1px solid #dcdcd8;border-radius:10px;background:#fff;color:#171717;
    font:inherit;font-size:.9375rem;font-weight:750;cursor:pointer;
  }
  .ck-payment-picker select:focus-visible{outline:2px solid #b44722;outline-offset:2px}
  .ck-payment-details{padding:10px 12px;border:1px solid #ecece8;border-radius:10px;background:#fafaf9;color:#555;font-size:.8125rem;line-height:1.45;overflow-wrap:anywhere}
  .ck-payment-details[hidden],#cod-fee-summary-row[hidden]{display:none}
  .ck-payment-details p{margin:0}
  .ck-payment-details p + p,.ck-payment-details .pay-data{margin-block-start:6px}
  .ck-payment-details a{color:#a63f1e;font-weight:700;text-decoration:underline}
  .ck-payment-timing{display:grid;gap:8px;margin:4px 0 0;padding:0;border:0;min-inline-size:0}
  .ck-payment-timing[hidden]{display:none}
  .ck-payment-timing legend{padding:0;margin-block-end:6px;font-size:.875rem;font-weight:700}
  .ck-payment-timing label{display:flex;align-items:center;gap:10px;padding:9px 12px;min-block-size:44px;border:1px solid #ecece8;border-radius:10px;cursor:pointer}
  .ck-payment-timing label:has(input:checked){border-color:#b44722;background:#fff8f4}
  .ck-payment-timing input{margin:0;accent-color:#b44722}
  .ck-payment-timing strong,.ck-payment-timing small{display:block}
  .ck-payment-timing small{color:#555;line-height:1.4}
  .ck-order-actions{display:flex;align-items:center;gap:12px}
  .ck-order-actions-copy{display:none}
  .ck-order-actions .place-order-btn{min-block-size:48px}
  .checkout-page .ck-field-error{display:block;margin-block-start:5px;color:#a52617;font-size:.8125rem}
  .checkout-page :is(input,select,textarea)[aria-invalid="true"]{border-color:#ba3827;box-shadow:0 0 0 2px #ba382726}
  @media(max-width:768px){
    html:has(.checkout-page){scroll-padding-block-end:calc(var(--mobile-nav-height,58px) + 88px)}
    .checkout-page{padding-block-end:96px}
    html:has(body.checkout-keyboard-open){scroll-padding-block-end:16px}
    body.checkout-keyboard-open #mob-nav{display:none!important}
    body.checkout-keyboard-open .ck-order-actions{visibility:hidden;pointer-events:none}
    .checkout-page .form-group :is(input:not([type="checkbox"]),select,textarea),.checkout-page .ck-payment-picker select{font-size:16px}
    .ck-order-actions{
      position:fixed;z-index:9998;inset-inline:0;
      inset-block-end:calc(var(--mobile-nav-height,58px) + var(--mobile-nav-viewport-offset,0px));
      padding:8px max(14px,env(safe-area-inset-right)) 8px max(14px,env(safe-area-inset-left));
      border-block-start:1px solid #e5e5e2;background:#fff;box-shadow:0 -4px 16px #0001;
    }
    .ck-order-actions-copy{display:grid;gap:1px;flex:1 1 auto;min-inline-size:0;line-height:1.25}
    .ck-order-actions-copy strong{font-size:.9375rem;overflow-wrap:anywhere}
    .ck-order-actions-copy a{color:#555;font-size:.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-decoration:underline;text-underline-offset:2px}
    .ck-order-actions .place-order-btn{flex:0 1 48%;inline-size:auto;min-inline-size:0;padding:8px 12px;font-size:.875rem;text-align:center}
  }
  @media(max-width:900px){.checkout-page form{display:flex;flex-direction:column}.checkout-page .checkout-shipping-section{order:-1}}
</style>
@endpush

@section('content')
<div class="page checkout-page {{ $isAr ? 'checkout-page-ar' : '' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <header class="ck-page-heading">
    <h1>{{ $isAr ? 'إتمام الطلب' : 'Checkout' }}</h1>
    <a href="{{ route('cart') }}">{{ $isAr ? 'ارجع للسلة' : 'Back to cart' }}</a>
  </header>

  <div class="checkout-layout">
    {{-- FORM --}}
    <div>
      <form method="POST" action="{{ route('checkout.place') }}" id="checkout-form">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $checkoutIdempotencyKey) }}">

        {{-- CONTACT INFO --}}
        <div class="ck-section">
          <h3 class="ck-title">{{ $isAr ? 'بيانات التواصل' : 'Contact Information' }}</h3>
          @if(!auth()->check())
            @php
              $enabledLoginMethods = [
                'phone'  => (bool) ($authConfig['phone_otp_login'] ?? false),
                'google' => (bool) ($authConfig['google_login'] ?? false),
                'email'  => (bool) ($authConfig['email_login'] ?? true),
              ];
            @endphp
            <aside class="ck-auth-widget" aria-label="{{ $isAr ? 'طرق تسجيل الدخول المتاحة' : 'Available sign-in methods' }}">
              <div class="ck-auth-copy">
                <span class="ck-auth-kicker">{{ $isAr ? 'إتمام طلب كضيف' : 'Guest checkout' }}</span>
                <p class="ck-auth-title">{{ $isAr ? 'سجّل دخولك عشان بياناتك المحفوظة ودفع أسرع' : 'Sign in for saved details and faster checkout' }}</p>
                <p class="ck-auth-desc">{{ $isAr ? 'تقدر تكمّل كضيف وتكتب بيانات التوصيل.' : 'You can also continue as a guest and enter your delivery details below.' }}</p>
              </div>
              <div class="ck-auth-actions">
                @if($enabledLoginMethods['phone'])
                  <a class="ck-auth-action" href="{{ route('login') }}#phone-otp">{{ $isAr ? 'دخول برقم الموبايل' : 'Phone OTP' }}</a>
                @endif
                @if($enabledLoginMethods['google'])
                  <a class="ck-auth-action ck-auth-action-light" href="{{ route('auth.google') }}">{{ $isAr ? 'كمّل بحساب جوجل' : 'Continue with Google' }}</a>
                @endif
                @if($enabledLoginMethods['email'])
                  <a class="ck-auth-action ck-auth-action-light" href="{{ route('login') }}#email-login">{{ $isAr ? 'الإيميل وكلمة السر' : 'Email & password' }}</a>
                @endif
                @if(!in_array(true, $enabledLoginMethods, true))
                  <span class="ck-auth-desc">{{ $isAr ? 'مفيش طريقة دخول مفعّلة دلوقتي. كمّل كضيف.' : 'No sign-in method is currently enabled. Continue as guest.' }}</span>
                @endif
              </div>
            </aside>
          @endif
          <div class="form-grid-2">
            <div class="form-group">
              <label for="checkout-first-name">{{ $isAr ? 'الاسم الأول' : 'First Name' }} *</label>
              <input type="text" id="checkout-first-name" name="first_name" autocomplete="given-name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
              @error('first_name')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label for="checkout-last-name">{{ $isAr ? 'اسم العيلة' : 'Last Name' }} *</label>
              <input type="text" id="checkout-last-name" name="last_name" autocomplete="family-name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
              @error('last_name')<span class="err">{{ $message }}</span>@enderror
            </div>
          </div>
          <div class="form-group">
            <label for="checkout-email">{{ $isAr ? 'الإيميل' : 'Email Address' }} <span style="color:#999;font-weight:400;font-size:12px">({{ $isAr ? 'اختياري' : 'optional' }})</span></label>
            <input type="email" id="checkout-email" name="email" autocomplete="email" value="{{ old('email', ($user && !str_ends_with($user->email ?? '', '@ramostore.local')) ? ($user->email ?? '') : '') }}" placeholder="you@example.com">
            @error('email')<span class="err">{{ $message }}</span>@enderror
          </div>
          <div class="form-group">
              <label for="checkout-phone">{{ $isAr ? 'رقم الموبايل' : 'Phone Number' }} *</label>
              <input type="tel" id="checkout-phone" name="phone" autocomplete="tel" value="{{ old('phone', $user->phone ?? '') }}" placeholder="01xxxxxxxxx" inputmode="tel" pattern="[0-9+\-\s\(\)]{7,20}" required>
            @error('phone')<span class="err">{{ $message }}</span>@enderror
          </div>
        </div>

        {{-- SHIPPING ADDRESS --}}
        <div class="ck-section checkout-shipping-section">
          <h3 class="ck-title">{{ $isAr ? 'عنوان الشحن' : 'Shipping Address' }}</h3>
          <div class="form-group">
            <div id="checkout-location-map-panel" class="ck-location-map-panel">
              <div class="ck-location-heading">
                <strong>{{ $isAr ? 'مكان التوصيل' : 'Delivery location' }}</strong>
                <a href="#checkout-address" class="ck-address-jump">{{ $isAr ? 'اكتب عنوانك' : 'Type your address' }}</a>
              </div>
              <div class="ck-location-actions" aria-label="{{ $isAr ? 'اختيار موقع التوصيل' : 'Delivery location selection' }}">
                <button type="button" class="ck-location-action" id="use-current-location-btn">
                  <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/></svg>
                  {{ $isAr ? 'استخدم موقعي' : 'Use my location' }}
                </button>
                <label class="ck-location-switch" for="manual-location-mode-switch">
                  <span>{{ $isAr ? 'حرّك العلامة' : 'Move pin' }}</span>
                  <input type="checkbox" id="manual-location-mode-switch" checked role="switch" aria-checked="true" aria-describedby="location-mode-note" @disabled(!$hasSavedLocation)>
                  <span class="ck-switch-track" aria-hidden="true"></span>
                </label>
              </div>
              <span id="location-mode-note" class="ck-location-mode-note">{{ $hasSavedLocation ? $checkoutText['manualReady'] : $checkoutText['placePin'] }}</span>
              <div class="ck-map-shell">
                <div id="checkout-map" class="ck-map-canvas" aria-label="{{ $isAr ? 'خريطة مكان التوصيل التفاعلية' : 'Interactive delivery location map' }}"></div>
                <div id="checkout-map-placeholder" class="ck-map-placeholder">
                  <div class="ck-map-placeholder-inner">
                    <span id="checkout-map-message" class="ck-map-placeholder-copy">{{ $checkoutText['mapLoading'] }}</span>
                    <button type="button" id="load-checkout-map-btn" class="ck-map-load-btn" aria-controls="checkout-map" hidden>{{ $checkoutText['mapRetry'] }}</button>
                  </div>
                </div>
                <div id="map-locating-overlay" hidden role="status">{{ $checkoutText['locating'] }}</div>
              </div>
              <div id="location-status" class="ck-location-status" hidden aria-live="polite"></div>
              <div id="location-fallback" class="ck-location-fallback" hidden aria-live="polite">
                <span id="location-fallback-copy" class="ck-location-fallback-copy"></span>
                <div class="ck-location-fallback-actions">
                  <button type="button" id="manual-location-btn" class="ck-location-fallback-btn ck-location-fallback-btn-primary">{{ $checkoutText['manualLocation'] }}</button>
                  <button type="button" id="retry-location-btn" class="ck-location-fallback-btn">{{ $checkoutText['retryLocation'] }}</button>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group ck-address-fields">
            <label for="checkout-address">{{ $isAr ? 'العنوان بالتفصيل' : 'Street Address' }} *</label>
            <input type="text" id="checkout-address" name="address" autocomplete="address-line1" value="{{ old('address', $savedAddress['address'] ?? '') }}" required>
            @error('address')<span class="err">{{ $message }}</span>@enderror
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label for="checkout-city">{{ $isAr ? 'المدينة' : 'City' }} *</label>
              <input type="text" id="checkout-city" name="city" autocomplete="address-level2" value="{{ old('city', $savedAddress['city'] ?? '') }}" required>
              @error('city')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label for="checkout-state">{{ $isAr ? 'المحافظة' : 'Governorate' }} *</label>
              <select id="checkout-state" name="state" autocomplete="address-level1" required>
                <option value="">{{ $isAr ? 'اختار المحافظة' : 'Select governorate' }}</option>
                @foreach(['Cairo','Giza','Alexandria','Aswan','Asyut','Beheira','Beni Suef','Dakahlia','Damietta','Faiyum','Gharbia','Ismailia','Kafr El Sheikh','Luxor','Matrouh','Minya','Monufia','New Valley','North Sinai','Port Said','Qalyubia','Qena','Red Sea','Sharqia','Sohag','South Sinai','Suez'] as $gov)
                  <option value="{{ $gov }}" {{ old('state', $savedAddress['state'] ?? '') === $gov ? 'selected' : '' }}>{{ $isAr ? ($governoratesAr[$gov] ?? $gov) : $gov }}</option>
                @endforeach
              </select>
              @error('state')<span class="err">{{ $message }}</span>@enderror
            </div>
          </div>
          <div class="form-group">
            <label>{{ $isAr ? 'الشقة أو الدور أو تفاصيل إضافية' : 'Apartment / Floor / Additional details' }}</label>
            <textarea name="address_note" rows="2" placeholder="{{ $isAr ? 'الشقة أو الدور أو تفاصيل إضافية' : 'Apartment / Floor / Additional details' }}">{{ old('address_note', $savedAddress['address_note'] ?? '') }}</textarea>
          </div>
          <input type="hidden" name="latitude" id="checkout-latitude" value="{{ old('latitude', $savedAddress['latitude'] ?? ($user->latitude ?? '')) }}">
          <input type="hidden" name="longitude" id="checkout-longitude" value="{{ old('longitude', $savedAddress['longitude'] ?? ($user->longitude ?? '')) }}">
          <label class="ck-save-address" for="save-address">
            <input type="checkbox" name="save_address" value="1" id="save-address" {{ old('save_address', session('checkout_save_address', true)) ? 'checked' : '' }}>
            <span class="ck-save-address-copy">
              <span class="ck-save-address-title">{{ $isAr ? 'احفظ العنوان ده للمرة الجاية' : 'Save this address for future use' }}</span>
              <span class="ck-save-address-desc">{{ $isAr ? 'خلي بيانات التوصيل دي محفوظة في حسابك عشان طلبك الجاي يبقى أسرع.' : 'Keep these delivery details in your account for a faster next checkout.' }}</span>
            </span>
          </label>
        </div>

        {{-- PAYMENT --}}
        <div class="ck-section" id="checkout-payment">
          <h3 class="ck-title" id="checkout-payment-title">{{ $isAr ? 'طريقة الدفع' : 'Payment Method' }}</h3>
          @php
            $selectedPaymentTitle = $isAr
              ? ($paymentLabelsAr[$selectedPaymentMethod]['title'] ?? ($paymentMethods[$selectedPaymentMethod]['title'] ?? ''))
              : ($paymentMethods[$selectedPaymentMethod]['title'] ?? '');
          @endphp
          @php
            $showPaymentTiming = filled($selectedPaymentMethod) && $selectedPaymentMethod !== 'cod';
            $paymentTiming = old('payment_timing', 'now');
            $placeOrderLabel = $isAr ? 'أكّد الطلب' : 'Place order';
            $continuePaymentLabel = $isAr ? 'تابع للدفع' : 'Continue to payment';
          @endphp
          <div class="ck-payment-picker">
            <select id="checkout-payment-method" name="payment_method" required aria-labelledby="checkout-payment-title">
              @forelse($paymentMethods as $val => $method)
                <option value="{{ $val }}" @selected($selectedPaymentMethod === $val)>
                  {{ $isAr ? ($paymentLabelsAr[$val]['title'] ?? $method['title']) : $method['title'] }}
                </option>
              @empty
                <option value="">{{ $isAr ? 'مفيش طرق دفع متاحة' : 'No payment methods available' }}</option>
              @endforelse
            </select>
            @foreach($paymentMethods as $val => $method)
              @php
                $paymentCopy = $isAr ? ($paymentLabelsAr[$val] ?? []) : [];
                $description = $paymentCopy['description'] ?? $method['description'];
                $paymentData = trim((string) ($method['data'] ?? ''));
              @endphp
              <div class="ck-payment-details" data-payment-details="{{ $val }}" @if($selectedPaymentMethod !== $val) hidden @endif>
                <p>{{ $description }}</p>
                @if($val === 'cod' && $codFeeAmount > 0)
                  <div class="pay-data">
                    <span>{{ $isAr ? 'رسوم الدفع عند الاستلام' : 'Cash on Delivery fee' }}:</span>
                    <strong>{{ number_format($codFeeAmount, 2) }} EGP</strong>
                  </div>
                @endif
                @if($paymentData !== '' && strcasecmp($paymentData, trim($method['description'])) !== 0)
                  <div class="pay-data">
                    <span>{{ $paymentCopy['data_label'] ?? ($method['data_label'] ?? ($isAr ? 'التفاصيل' : 'Details')) }}:</span>
                    <strong>{{ $paymentData }}</strong>
                  </div>
                @endif
              </div>
            @endforeach
            <fieldset class="ck-payment-timing" id="checkout-payment-timing" @if(!$showPaymentTiming) hidden disabled @endif>
              <legend>{{ $isAr ? 'هتدفع إمتى؟' : 'When would you like to pay?' }}</legend>
              <label>
                <input type="radio" name="payment_timing" value="now" @checked($paymentTiming !== 'later')>
                <span><strong>{{ $isAr ? 'ادفع دلوقتي' : 'Pay now' }}</strong><small>{{ $isAr ? 'تابع لتعليمات التحويل وارفع الإيصال.' : 'Continue to the transfer instructions and upload your receipt.' }}</small></span>
              </label>
              <label>
                <input type="radio" name="payment_timing" value="later" @checked($paymentTiming === 'later')>
                <span><strong>{{ $isAr ? 'ادفع خلال 24 ساعة' : 'Pay within 24 hours' }}</strong><small>{{ $isAr ? 'ارجع للطلب وادفع وارفع الإيصال قبل ما الطلب يتلغى تلقائيًا.' : 'Return to your order to pay and upload your receipt before it expires.' }}</small></span>
              </label>
            </fieldset>
          </div>
          @error('payment_method')<span class="err">{{ $message }}</span>@enderror
          @error('payment_timing')<span class="err">{{ $message }}</span>@enderror
        </div>

        {{-- ORDER NOTES --}}
        <div class="ck-section">
          <div class="form-group">
            <label>{{ $isAr ? 'ملاحظات على الطلب' : 'Order Notes' }} ({{ $isAr ? 'اختياري' : 'optional' }})</label>
            <textarea name="notes" rows="3" placeholder="{{ $isAr ? 'فيه أي ملاحظات خاصة بطلبك؟' : 'Any special instructions for your order…' }}">{{ old('notes') }}</textarea>
          </div>
        </div>

        <div class="ck-order-actions">
          <div class="ck-order-actions-copy">
            <strong id="checkout-action-total" aria-live="polite">{{ number_format($total, 2) }} EGP</strong>
            <a href="#checkout-payment-method" id="checkout-action-payment" title="{{ $isAr ? 'غيّر طريقة الدفع' : 'Change payment method' }}">{{ $selectedPaymentTitle }}</a>
          </div>
          <button type="submit" class="btn btn-dark place-order-btn" @disabled($selectedPaymentMethod === null)>{{ $showPaymentTiming && $paymentTiming !== 'later' ? $continuePaymentLabel : $placeOrderLabel }}</button>
        </div>
        <p style="margin:12px 2px 0;color:#667085;font-size:12px;line-height:1.7">
          @if($isAr)
            بتأكيد الطلب، إنت بتوافق على <a href="{{ route('policy.terms') }}" style="color:#c94717;text-decoration:underline">الشروط والأحكام</a> و<a href="{{ route('policy.privacy') }}" style="color:#c94717;text-decoration:underline">سياسة الخصوصية</a>. راجع كمان <a href="{{ route('policy.shipping') }}" style="color:#c94717;text-decoration:underline">الشحن</a> و<a href="{{ route('policy.returns') }}" style="color:#c94717;text-decoration:underline">الاسترجاع</a> و<a href="{{ route('policy.payment') }}" style="color:#c94717;text-decoration:underline">الدفع</a>.
          @else
            By placing your order, you agree to the <a href="{{ route('policy.terms') }}" style="color:#c94717;text-decoration:underline">Terms &amp; Conditions</a> and <a href="{{ route('policy.privacy') }}" style="color:#c94717;text-decoration:underline">Privacy Policy</a>. Review <a href="{{ route('policy.shipping') }}" style="color:#c94717;text-decoration:underline">shipping</a>, <a href="{{ route('policy.returns') }}" style="color:#c94717;text-decoration:underline">returns</a>, and <a href="{{ route('policy.payment') }}" style="color:#c94717;text-decoration:underline">payment information</a>.
          @endif
        </p>
      </form>
    </div>

    {{-- ORDER SUMMARY --}}
    <div class="ck-summary" aria-labelledby="checkout-summary-title">
      <div class="ck-summary-header">
        <div class="ck-summary-heading">
          <h3 class="ck-title" id="checkout-summary-title">{{ $isAr ? 'ملخص الطلب' : 'Order Summary' }}</h3>
          <p class="ck-summary-caption">{{ $isAr ? 'راجع التفاصيل قبل تأكيد الطلب' : 'Review the details before placing your order' }}</p>
        </div>
        <span class="ck-summary-count">{{ count($cart) }} {{ $isAr ? (count($cart) === 1 ? 'منتج' : 'منتجات') : (count($cart) === 1 ? 'item' : 'items') }}</span>
      </div>
      <div class="ck-items">
        @foreach($cart as $item)
        <div class="ck-item">
          <div class="ck-item-img">
            @if($item['image'])
              <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
            @else
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">👕</div>
            @endif
            <span class="ck-item-qty">{{ $item['qty'] }}</span>
          </div>
          <div class="ck-item-details">
            <div class="ck-item-name">{{ Str::limit($item['name'], 35) }}</div>
            @if(!empty($item['sku']))
              <div class="ck-item-sku">{{ $isAr ? 'الكود:' : 'SKU:' }} {{ $item['sku'] }}</div>
            @endif
            @if(!empty($item['attrs']))
              <div class="ck-item-attrs">
                @foreach($item['attrs'] as $k => $v)
                  <span>{{ $k }}: <strong>{{ $v }}</strong></span>
                @endforeach
              </div>
            @endif
          </div>
          <div class="ck-item-price-wrap">
            <span class="ck-item-price-label">{{ $isAr ? 'إجمالي المنتج' : 'Item total' }}</span>
            <div class="ck-item-price">{{ number_format($item['price'] * $item['qty'], 2) }} EGP</div>
          </div>
        </div>
        @endforeach
      </div>
      <div class="ck-totals">
        <div class="summary-row"><span>{{ $isAr ? 'الإجمالي الفرعي' : 'Subtotal' }}</span><span>{{ number_format($subtotal, 2) }} EGP</span></div>
        @if($coupon && $discount > 0)
          <div class="summary-row discount-row"><span>{{ $isAr ? 'كود خصم' : 'Coupon' }} ({{ $coupon['code'] }})</span><span>−{{ number_format($discount, 2) }} EGP</span></div>
        @elseif($coupon && !empty($coupon['free_shipping']))
          <div class="summary-row discount-row"><span>{{ $isAr ? 'ميزة الكوبون' : 'Coupon benefit' }} ({{ $coupon['code'] }})</span><span>{{ $isAr ? 'توصيل مجاني' : 'Free shipping' }}</span></div>
        @endif
        <div class="summary-row"><span>{{ $isAr ? 'التوصيل المتوقع' : 'Estimated Delivery' }}</span><span>{{ $isAr ? 'من يومين لـ 4 أيام' : '2–4 days' }}</span></div>
        <div class="summary-row free-shipping-row"><span>{{ $isAr ? 'الشحن' : 'Shipping' }}</span><span>{{ $shippingFee > 0 ? number_format($shippingFee, 2) . ' EGP' : ($isAr ? 'مجاني' : 'Free') }}</span></div>
        <div class="summary-row cod-fee-row" id="cod-fee-summary-row" data-cod-fee-row {{ $codFee > 0 ? '' : 'hidden' }}>
          <span>{{ $isAr ? 'رسوم الدفع عند الاستلام' : 'Cash on Delivery fee' }}</span>
          <span id="cod-fee-summary-value">{{ number_format($codFee, 2) }} EGP</span>
        </div>
        @if($totalTax > 0)
          <div class="summary-row"><span>{{ $isAr ? 'الضريبة' : 'Tax' }}</span><span>{{ number_format($totalTax, 2) }} EGP</span></div>
        @endif
        <div class="summary-divider"></div>
        <div class="summary-row total-row"><span>{{ $isAr ? 'الإجمالي النهائي' : 'Final total' }}</span><span id="checkout-total-value" data-base-total="{{ $baseTotal }}">{{ number_format($total, 2) }} EGP</span></div>
        <p class="total-note">{{ $isAr ? 'الشحن ورسوم الدفع عند الاستلام محسوبين حسب اختيارك.' : 'Shipping and Cash on Delivery fees reflect your selected options.' }}</p>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const checkoutText = @json($checkoutText);
  const paymentSelect = document.getElementById('checkout-payment-method');
  const paymentDetails = document.querySelectorAll('[data-payment-details]');
  const codFeeAmount = Number(@json($codFeeAmount));
  const baseTotal = Number(@json($baseTotal));
  const codFeeRow = document.getElementById('cod-fee-summary-row');
  const codFeeValue = document.getElementById('cod-fee-summary-value');
  const checkoutTotalValue = document.getElementById('checkout-total-value');
  const actionTotal = document.getElementById('checkout-action-total');
  const actionPayment = document.getElementById('checkout-action-payment');
  const paymentTiming = document.getElementById('checkout-payment-timing');
  const placeOrderButton = document.querySelector('.place-order-btn');
  const formatEgp = (value) => Number(value).toFixed(2) + ' EGP';

  const updatePayment = () => {
    const selected = paymentSelect.value;
    paymentDetails.forEach(details => { details.hidden = details.dataset.paymentDetails !== selected; });
    const prepaid = selected !== '' && selected !== 'cod';
    paymentTiming.hidden = paymentTiming.disabled = !prepaid;
    placeOrderButton.textContent = prepaid && paymentTiming.querySelector('input:checked')?.value === 'now'
      ? @json($continuePaymentLabel) : @json($placeOrderLabel);
    const fee = selected === 'cod' ? codFeeAmount : 0;
    codFeeRow.hidden = fee <= 0;
    codFeeValue.textContent = formatEgp(fee);
    checkoutTotalValue.textContent = actionTotal.textContent = formatEgp(baseTotal + fee);
    actionPayment.textContent = paymentSelect.selectedOptions[0]?.textContent.trim() || '';
  };
  paymentSelect.addEventListener('change', updatePayment);
  paymentTiming.addEventListener('change', updatePayment);
  updatePayment();

  const checkoutForm = document.getElementById('checkout-form');
  const mobileCheckout = window.matchMedia('(max-width: 768px)');
  const viewport = window.visualViewport;
  const pendingInvalidFields = new Set();
  let validationFrame = null;

  const errorMessage = (field) => {
    if (field.validity.valueMissing) return field.tagName === 'SELECT' ? checkoutText.requiredOption : checkoutText.requiredField;
    if (field.type === 'email' && field.validity.typeMismatch) return checkoutText.invalidEmail;
    if (field.type === 'tel' && field.validity.patternMismatch) return checkoutText.invalidPhone;
    return field.validationMessage;
  };
  const showFieldError = (field) => {
    const errorId = field.id + '-validation';
    let error = document.getElementById(errorId);
    if (!error) {
      error = document.createElement('span');
      error.id = errorId;
      error.className = 'ck-field-error';
      error.setAttribute('role', 'alert');
      field.insertAdjacentElement('afterend', error);
      const descriptions = [field.getAttribute('aria-describedby'), errorId].filter(Boolean);
      field.setAttribute('aria-describedby', descriptions.join(' '));
    }
    field.setAttribute('aria-invalid', 'true');
    error.textContent = errorMessage(field);
  };
  const clearFieldError = ({ target: field }) => {
    if (!field.validity?.valid) return;
    const errorId = field.id + '-validation';
    const error = document.getElementById(errorId);
    if (!error) return;
    error.remove();
    field.removeAttribute('aria-invalid');
    const remaining = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(id => id && id !== errorId);
    if (remaining.length) field.setAttribute('aria-describedby', remaining.join(' '));
    else field.removeAttribute('aria-describedby');
  };

  checkoutForm.addEventListener('invalid', (event) => {
    event.preventDefault();
    pendingInvalidFields.add(event.target);
    if (validationFrame !== null) return;
    validationFrame = requestAnimationFrame(() => {
      validationFrame = null;
      const fields = [...pendingInvalidFields].filter(field => !field.validity.valid && field.getClientRects().length)
        .sort((a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top);
      pendingInvalidFields.clear();
      const field = fields[0];
      if (!field) return;

      showFieldError(field);
      if (mobileCheckout.matches) {
        const group = field.closest('.form-group, .ck-section');
        if (group) {
          group.tabIndex = -1;
          group.focus({ preventScroll: true });
        }
      } else {
        field.focus({ preventScroll: true });
      }
      field.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' });
    });
  }, true);
  checkoutForm.addEventListener('input', clearFieldError);
  checkoutForm.addEventListener('change', clearFieldError);

  const updateKeyboardLayout = () => {
    const active = document.activeElement;
    const isEditing = checkoutForm.contains(active)
      && active.matches('textarea,input[type="text"],input[type="email"],input[type="tel"]');
    const keyboardOpen = mobileCheckout.matches && isEditing && viewport
      && Math.abs(viewport.scale - 1) < 0.05 && document.documentElement.clientHeight - viewport.height > 120;
    const wasOpen = document.body.classList.contains('checkout-keyboard-open');
    document.body.classList.toggle('checkout-keyboard-open', Boolean(keyboardOpen));
    if (keyboardOpen && !wasOpen) {
      const box = active.getBoundingClientRect();
      if (box.top < viewport.offsetTop || box.bottom > viewport.offsetTop + viewport.height - 12) {
        active.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' });
      }
    }
  };
  viewport?.addEventListener('resize', updateKeyboardLayout);
  mobileCheckout.addEventListener('change', updateKeyboardLayout);
  checkoutForm.addEventListener('focusin', updateKeyboardLayout);
  checkoutForm.addEventListener('focusout', () => requestAnimationFrame(updateKeyboardLayout));

  checkoutForm.addEventListener('submit', (event) => {
    if (!checkoutForm.checkValidity()) {
      event.preventDefault();
      return;
    }
    const submitButton = checkoutForm.querySelector('.place-order-btn');
    if (!submitButton || submitButton.disabled) {
      event.preventDefault();
      return;
    }
    submitButton.disabled = true;
    submitButton.setAttribute('aria-busy', 'true');
    submitButton.textContent = checkoutText.isAr ? 'بنأكد الطلب…' : 'Placing order…';
  });

  const useLocationBtn = document.getElementById('use-current-location-btn');
  const addressInput = document.querySelector('input[name="address"]');
  const cityInput = document.querySelector('input[name="city"]');
  const stateSelect = document.querySelector('select[name="state"]');
  const mapEl = document.getElementById('checkout-map');
  const mapPlaceholder = document.getElementById('checkout-map-placeholder');
  const loadMapBtn = document.getElementById('load-checkout-map-btn');
  const mapMessage = document.getElementById('checkout-map-message');
  const locationStatus = document.getElementById('location-status');
  const locationFallback = document.getElementById('location-fallback');
  const locationFallbackCopy = document.getElementById('location-fallback-copy');
  const manualLocationBtn = document.getElementById('manual-location-btn');
  const manualModeSwitch = document.getElementById('manual-location-mode-switch');
  const locationModeNote = document.getElementById('location-mode-note');
  const mapShell = mapEl?.closest('.ck-map-shell');
  const retryLocationBtn = document.getElementById('retry-location-btn');
  const latitudeInput = document.getElementById('checkout-latitude');
  const longitudeInput = document.getElementById('checkout-longitude');
  let map = null;
  let marker = null;
  let mapLoadPromise = null;
  let manualLocationEnabled = true;
  let pinRequest = 0;
  const hasPin = () => Boolean(latitudeInput?.value && longitudeInput?.value
    && Number.isFinite(Number(latitudeInput.value)) && Number.isFinite(Number(longitudeInput.value)));
  const updateLocationNote = () => {
    if (manualModeSwitch) manualModeSwitch.disabled = !hasPin();
    if (locationModeNote) {
      locationModeNote.textContent = !manualLocationEnabled ? checkoutText.autoLocked
        : hasPin() ? checkoutText.manualReady : checkoutText.placePin;
    }
  };

  const setManualLocationMode = (enabled) => {
    manualLocationEnabled = enabled;
    if (marker?.dragging) {
      enabled ? marker.dragging.enable() : marker.dragging.disable();
    }
    mapShell?.classList.toggle('map-location-locked', !enabled);
    mapEl?.classList.toggle('map-location-locked', !enabled);
    mapEl?.setAttribute('aria-label', enabled
      ? (checkoutText.isAr ? 'خريطة مكان التوصيل — التحديد اليدوي مفعّل' : 'Delivery location map — manual selection enabled')
      : (checkoutText.isAr ? 'خريطة مكان التوصيل — الموقع الحالي مثبت' : 'Delivery location map — current location locked'));
    if (manualModeSwitch) {
      manualModeSwitch.checked = enabled;
      manualModeSwitch.setAttribute('aria-checked', String(enabled));
    }
    updateLocationNote();
  };

  const setStatus = (msg, state = 'info') => {
    if (!locationStatus) return;
    locationStatus.textContent = msg || '';
    locationStatus.dataset.state = state;
    if (msg) locationStatus.removeAttribute('hidden');
    else locationStatus.setAttribute('hidden', '');
  };
  const hideLocationFallback = () => {
    locationFallback?.setAttribute('hidden', '');
  };
  const showLocationFallback = (message = checkoutText.locationFallback) => {
    if (locationFallbackCopy) locationFallbackCopy.textContent = message;
    locationFallback?.removeAttribute('hidden');
  };
  const setCoords = (lat, lng) => {
    if (latitudeInput) latitudeInput.value = lat ?? '';
    if (longitudeInput) longitudeInput.value = lng ?? '';
    updateLocationNote();
  };
  const norm = (v) => (v || '').toString().trim().toLowerCase();
  const matchGovernorate = (value) => {
    const aliases = {
      'al minya': 'Minya',
      'el minya': 'Minya',
      'minya': 'Minya',
      'minia': 'Minya',
      'menya': 'Minya',
      'al minyā': 'Minya',
    };
    const normalized = aliases[norm(value)] || value;
    const found = Array.from(stateSelect?.options || []).find(opt => norm(opt.value) === norm(normalized) || norm(opt.textContent) === norm(normalized));
    if (found && stateSelect) stateSelect.value = found.value;
    return !!found;
  };
  const updateFields = async (lat, lng) => {
    const request = ++pinRequest;
    hideLocationFallback();
    const previous = { address: addressInput?.value, city: cityInput?.value, state: stateSelect?.value };
    setCoords(lat, lng);
    setStatus(checkoutText.locationSelected);
    try {
      const language = checkoutText.isAr ? 'ar,en' : 'en';
      const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1&accept-language=${encodeURIComponent(language)}`);
      if (!res.ok) throw new Error('Address lookup failed.');
      const data = await res.json();
      if (request !== pinRequest) return;
      if (!data.address) throw new Error('Address details unavailable.');
      const addr = data.address;
      const firstNonEmpty = (values) => values.find((value) => value && String(value).trim())?.toString().trim() || '';
      const city = firstNonEmpty([
        addr.city,
        addr.town,
        addr.municipality,
        addr.county,
        addr.city_district,
        addr.village,
        addr.hamlet,
      ]);
      const state = firstNonEmpty([
        addr.state,
        addr.region,
        addr.state_district,
        addr.province,
      ]);
      const detailParts = [
        addr.house_number,
        addr.road,
        addr.pedestrian,
        addr.footway,
        addr.neighbourhood,
        addr.quarter,
        addr.residential,
        addr.suburb,
        addr.locality,
        addr.hamlet !== city ? addr.hamlet : '',
        addr.village !== city ? addr.village : '',
        addr.postcode,
      ].filter((value, index, values) => value && values.indexOf(value) === index);
      const street = detailParts.join(', ') || data.display_name || [city, state].filter(Boolean).join(', ');
      if (street && addressInput?.value === previous.address && addressInput) {
        addressInput.value = street;
        addressInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
      if (city && cityInput?.value === previous.city && cityInput) {
        cityInput.value = city;
        cityInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
      if (stateSelect?.value === previous.state) matchGovernorate(state);
      setStatus(city || state ? `${checkoutText.selected} ${[city, state].filter(Boolean).join(', ')}` : checkoutText.locationSelected);
    } catch (_) {
      if (request === pinRequest) setStatus(checkoutText.detailsUnavailable);
    }
  };
  const showPin = (position) => {
    marker.setLatLng(position);
    if (!map.hasLayer(marker)) marker.addTo(map);
    setManualLocationMode(manualLocationEnabled);
  };
  const initMap = (lat, lng) => {
    if (!window.L || !mapEl) return null;
    if (map) {
      map.setView([lat, lng], 14);
      if (hasPin()) showPin([lat, lng]);
      return map;
    }
    map = L.map(mapEl, { zoomControl: true }).setView([lat, lng], 14);
    L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      maxZoom: 20,
      attribution: 'Google Maps'
    }).addTo(map);
    marker = L.marker([lat, lng], { draggable: manualLocationEnabled });
    if (hasPin()) marker.addTo(map);
    marker.on('dragend', () => {
      if (!manualLocationEnabled) return;
      const p = marker.getLatLng();
      updateFields(p.lat, p.lng);
    });
    map.on('click', (e) => {
      if (!manualLocationEnabled) {
        setStatus(checkoutText.autoLocked);
        return;
      }
      updateFields(e.latlng.lat, e.latlng.lng);
      showPin(e.latlng);
    });
    setManualLocationMode(manualLocationEnabled);
    mapPlaceholder?.setAttribute('hidden', '');
    window.setTimeout(() => map.invalidateSize(), 0);
    return map;
  };
  const ensureMap = (
    lat = hasPin() ? Number(latitudeInput.value) : 30.0444,
    lng = hasPin() ? Number(longitudeInput.value) : 31.2357,
  ) => {
    if (map) return Promise.resolve(initMap(lat, lng));
    if (mapLoadPromise) return mapLoadPromise.then(() => initMap(lat, lng));

    mapLoadPromise = new Promise((resolve, reject) => {
      const complete = () => {
        const initializedMap = initMap(lat, lng);
        initializedMap ? resolve(initializedMap) : reject(new Error('Map could not be initialized.'));
      };
      if (window.L) {
        complete();
        return;
      }
      if (!document.querySelector('link[data-checkout-leaflet]')) {
        const css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        css.dataset.checkoutLeaflet = 'true';
        document.head.appendChild(css);
      }
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      script.dataset.checkoutLeaflet = 'true';
      script.onload = complete;
      script.onerror = () => {
        script.remove();
        reject(new Error('Map resources could not be loaded.'));
      };
      document.body.appendChild(script);
    }).catch((error) => {
      mapLoadPromise = null;
      throw error;
    });
    return mapLoadPromise;
  };
  if (mapEl) {
    const loadMap = async () => {
      if (map) {
        map.invalidateSize();
        return true;
      }
      if (mapMessage) mapMessage.textContent = checkoutText.mapLoading;
      mapPlaceholder?.removeAttribute('hidden');
      loadMapBtn?.setAttribute('hidden', '');
      try {
        await ensureMap();
        return true;
      } catch (_) {
        if (mapMessage) mapMessage.textContent = checkoutText.mapUnavailable;
        mapPlaceholder?.removeAttribute('hidden');
        loadMapBtn?.removeAttribute('hidden');
        return false;
      }
    };

    loadMapBtn?.addEventListener('click', loadMap);
    loadMap();

    const mapOverlay = document.getElementById('map-locating-overlay');
    const setLocating = (active) => {
      if (mapOverlay) mapOverlay.hidden = !active;
      if (useLocationBtn) useLocationBtn.disabled = active;
      if (retryLocationBtn) retryLocationBtn.disabled = active;
    };
    const fetchLocation = () => {
      hideLocationFallback();
      if (!navigator.geolocation) {
        setStatus(checkoutText.detectFailed, 'error');
        showLocationFallback(checkoutText.locationFallback);
        return;
      }
      setStatus('');
      setLocating(true);
      navigator.geolocation.getCurrentPosition(async (pos) => {
        const { latitude, longitude } = pos.coords;
        setCoords(latitude, longitude);
        setManualLocationMode(false);
        const addressLookup = updateFields(latitude, longitude);
        try {
          await ensureMap(latitude, longitude);
        } catch (_) {
          if (mapMessage) mapMessage.textContent = checkoutText.mapUnavailable;
          mapPlaceholder?.removeAttribute('hidden');
          loadMapBtn?.removeAttribute('hidden');
        } finally {
          setLocating(false);
        }
        await addressLookup;
      }, (error) => {
        setLocating(false);
        const message = error?.code === 1 ? checkoutText.accessDenied : checkoutText.detectFailed;
        setStatus(message, 'error');
        showLocationFallback(checkoutText.locationFallback);
      }, { enableHighAccuracy: true, timeout: 60000, maximumAge: 0 });
    };
    const requestCurrentLocation = () => {
      if (!window.isSecureContext) {
        setStatus(checkoutText.insecureLocation, 'error');
        return;
      }
      if (useLocationBtn?.disabled) return;
      if (navigator.permissions) {
        navigator.permissions.query({ name: 'geolocation' }).then((result) => {
          if (result.state === 'denied') {
            setStatus(checkoutText.accessBlocked, 'error');
            showLocationFallback(checkoutText.locationFallback);
          } else {
            fetchLocation();
          }
        }).catch(() => fetchLocation());
      } else {
        fetchLocation();
      }
    };

    manualModeSwitch?.addEventListener('change', (event) => {
      const enabled = event.target.checked;
      setManualLocationMode(enabled);
      if (locationStatus?.dataset.state === 'error') setStatus('');
    });
    manualLocationBtn?.addEventListener('click', async () => {
      hideLocationFallback();
      setStatus('');
      setManualLocationMode(true);
      if (await loadMap()) mapEl.scrollIntoView({ block: 'center' });
    });
    retryLocationBtn?.addEventListener('click', requestCurrentLocation);
    useLocationBtn?.addEventListener('click', requestCurrentLocation);
  }
});
</script>
@endpush

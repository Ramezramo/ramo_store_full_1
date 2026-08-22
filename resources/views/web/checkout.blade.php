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
    'selected' => $isAr ? 'تم اختيار:' : 'Selected:',
    'locationSelected' => $isAr ? 'تم تحديد المكان.' : 'Location selected.',
    'detailsUnavailable' => $isAr ? 'تم تحديد المكان، بس ماقدرناش نجيب تفاصيل العنوان.' : 'Location selected, but address details could not be loaded.',
    'mapLoading' => $isAr ? 'بنحمّل الخريطة…' : 'Loading map…',
    'mapReady' => $isAr ? 'الخريطة جاهزة. اضغط أو اسحب العلامة عشان تعدّل مكان التوصيل.' : 'Map ready. Tap or drag the pin to adjust your delivery location.',
    'mapUnavailable' => $isAr ? 'ماقدرناش نحمّل الخريطة. تقدر تكتب عنوانك بنفسك.' : 'The map could not be loaded. You can still enter your address manually.',
    'mapRetry' => $isAr ? 'جرّب تحميل الخريطة تاني' : 'Retry loading map',
    'locating' => $isAr ? 'بنحدد مكانك…' : 'Locating...',
    'detected' => $isAr ? 'اتحدد مكانك' : 'Location detected',
    'dragPin' => $isAr ? 'تقدر تسحب العلامة عشان تعدّله.' : 'You can drag the pin to adjust it.',
    'manualAddress' => $isAr ? 'تقدر تكتب أو تعدّل عنوانك بنفسك.' : 'You can still enter or edit your address manually.',
    'accessDenied' => $isAr ? 'الوصول لمكانك اترفض. فعّله من إعدادات المتصفح وجرّب تاني.' : 'Location access was denied. Please enable it in your browser settings and try again.',
    'accessBlocked' => $isAr ? 'الوصول لمكانك متوقف. فعّله من إعدادات المتصفح وجرّب تاني.' : 'Location access is blocked. Please enable it in your browser settings and try again.',
    'detectFailed' => $isAr ? 'ماقدرناش نحدد مكانك. اسمح بالوصول للموقع وجرّب تاني.' : 'Could not detect your location. Please allow location access and try again.',
    'locationFallback' => $isAr ? 'تقدر تختار مكانك يدويًا على الخريطة، أو تحاول تحديد موقعك تاني.' : 'You can choose your location manually on the map or try detecting it again.',
    'manualLocation' => $isAr ? 'تحديد المكان يدويًا' : 'Choose location manually',
    'retryLocation' => $isAr ? 'حاول تحديد موقعي تاني' : 'Try my location again',
    'autoLocked' => $isAr ? 'تم تثبيت موقعك الحالي. استخدم «تحديد المكان يدويًا» لو عايز تغيّره.' : 'Your current location is locked. Use “Choose location manually” if you need to change it.',
    'manualReady' => $isAr ? 'التحديد اليدوي شغال. اضغط على الخريطة أو اسحب العلامة لتغيير المكان.' : 'Manual selection is on. Tap the map or drag the pin to change the location.',
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
  .ck-location-empty{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:12px;padding:18px;border:1px dashed #e8c4b3;border-radius:14px;background:linear-gradient(145deg,#fffaf7,#fff);}.ck-location-empty[hidden],.ck-location-map-panel[hidden]{display:none}.ck-location-empty-copy{display:flex;flex-direction:column;gap:5px;min-width:0}.ck-location-empty-title{color:#222;font-size:14px;font-weight:800}.ck-location-empty-copy span{color:#777;font-size:12px;line-height:1.45}.ck-location-choose-btn{min-height:42px;padding:0 15px;border:1px solid #e85d26;border-radius:9px;background:#e85d26;color:#fff;font-size:12px;font-weight:800;cursor:pointer;white-space:nowrap}.ck-location-choose-btn:hover{background:#c94717;border-color:#c94717}.ck-location-choose-btn:focus-visible{outline:3px solid rgba(232,93,38,.24);outline-offset:2px}.ck-location-actions{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:12px}.ck-location-action{flex:1;min-height:42px;padding:0 13px;border:1px solid #e85d26;border-radius:9px;background:#fff;color:#c94717;font-size:12px;font-weight:800;cursor:pointer;white-space:nowrap}.ck-location-action-primary{background:#e85d26;color:#fff}.ck-location-action:hover{background:#fff1e9}.ck-location-action-primary:hover{background:#c94717;color:#fff}.ck-location-action:focus-visible{outline:3px solid rgba(232,93,38,.24);outline-offset:2px}.ck-location-action[aria-pressed="true"]{border-color:#238653;background:#f0fff7;color:#17663d}.ck-location-action:disabled{cursor:wait;opacity:.65}.ck-location-switch{display:flex;align-items:center;justify-content:space-between;gap:10px;flex:1;min-height:42px;padding:0 13px;border:1px solid #e85d26;border-radius:9px;background:#fff;color:#c94717;font-size:12px;font-weight:800;cursor:pointer;transition:border-color .15s,background .15s,box-shadow .15s}.ck-location-switch:hover{background:#fff1e9}.ck-location-switch:has(input:focus-visible){outline:3px solid rgba(232,93,38,.24);outline-offset:2px}.ck-location-switch input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}.ck-switch-track{position:relative;display:inline-flex;width:40px;height:22px;flex:0 0 40px;align-items:center;padding:3px;border-radius:999px;background:#c9c9c9;transition:background .15s}.ck-switch-track::after{width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.22);content:"";transition:transform .15s}.ck-location-switch input:checked + .ck-switch-track{background:#238653}.ck-location-switch input:checked + .ck-switch-track::after{transform:translateX(18px)}.checkout-page-ar .ck-location-switch input:checked + .ck-switch-track::after{transform:translateX(-18px)}.ck-location-mode-note{display:block;margin:-4px 0 10px;color:#697586;font-size:11px;line-height:1.45}.ck-location-fallback{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:10px;padding:12px;border:1px solid #f0d6ca;border-radius:12px;background:#fff9f6}.ck-location-fallback[hidden]{display:none}.ck-location-fallback-copy{color:#8a4c38;font-size:12px;line-height:1.45}.ck-location-fallback-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:7px}.ck-location-fallback-btn{min-height:36px;padding:0 11px;border:1px solid #e85d26;border-radius:8px;background:#fff;color:#c94717;font-size:11px;font-weight:800;cursor:pointer;white-space:nowrap}.ck-location-fallback-btn-primary{background:#e85d26;color:#fff}.ck-location-fallback-btn:hover{background:#fff1e9}.ck-location-fallback-btn-primary:hover{background:#c94717;color:#fff}.ck-location-fallback-btn:focus-visible{outline:3px solid rgba(232,93,38,.24);outline-offset:2px}.ck-map-shell{position:relative;width:100%;height:280px;margin-bottom:12px;border:1px solid rgba(0,0,0,.08);border-radius:14px;overflow:hidden;background:#f8f8f8}.ck-map-shell.map-location-locked{border-color:#b7d9c6}.ck-map-canvas{width:100%;height:100%}.ck-map-canvas.map-location-locked{cursor:default}.ck-map-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:20px;background:linear-gradient(145deg,#fffdfb,#f7f7f6);z-index:2;text-align:center}.ck-map-placeholder[hidden]{display:none}.ck-map-placeholder-inner{display:flex;max-width:240px;align-items:center;flex-direction:column;gap:8px}.ck-map-placeholder-icon{display:flex;width:40px;height:40px;align-items:center;justify-content:center;border-radius:50%;background:#fff1e9;color:#e85d26;font-size:21px}.ck-map-placeholder-title{color:#222;font-size:14px;font-weight:800}.ck-map-placeholder-copy{color:#757575;font-size:12px;line-height:1.4}.ck-map-load-btn{min-height:38px;margin-top:2px;padding:0 13px;border:1px solid #e85d26;border-radius:9px;background:#fff;color:#c94717;font-size:12px;font-weight:800;cursor:pointer}.ck-map-load-btn:hover{background:#fff4ef}.ck-map-load-btn:focus-visible{outline:3px solid rgba(232,93,38,.24);outline-offset:2px}
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
  @media(max-width:900px){.checkout-page form{display:flex;flex-direction:column}.checkout-page .checkout-shipping-section{order:-1}}
</style>
@endpush

@section('content')
<div class="page checkout-page {{ $isAr ? 'checkout-page-ar' : '' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <div class="breadcrumb">
    <a href="{{ route('home') }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a><span>/</span>
    <a href="{{ route('cart') }}">{{ $isAr ? 'السلة' : 'Cart' }}</a><span>/</span>
    <strong>{{ $isAr ? 'إتمام الطلب' : 'Checkout' }}</strong>
  </div>

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
                <p class="ck-auth-desc">{{ $isAr ? 'طرق الدخول المتاحة بيحددها مسؤول المتجر. تقدر كمان تكمل ببيانات طلبك.' : 'The options below are controlled by the store administrator. You can also continue by entering your checkout details.' }}</p>
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
              <label>{{ $isAr ? 'الاسم الأول' : 'First Name' }} *</label>
              <input type="text" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
              @error('first_name')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label>{{ $isAr ? 'اسم العيلة' : 'Last Name' }} *</label>
              <input type="text" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
              @error('last_name')<span class="err">{{ $message }}</span>@enderror
            </div>
          </div>
          <div class="form-group">
            <label>{{ $isAr ? 'الإيميل' : 'Email Address' }} <span style="color:#999;font-weight:400;font-size:12px">({{ $isAr ? 'اختياري' : 'optional' }})</span></label>
            <input type="email" name="email" value="{{ old('email', ($user && !str_ends_with($user->email ?? '', '@ramostore.local')) ? ($user->email ?? '') : '') }}" placeholder="you@example.com">
            @error('email')<span class="err">{{ $message }}</span>@enderror
          </div>
          <div class="form-group">
              <label>{{ $isAr ? 'رقم الموبايل' : 'Phone Number' }} *</label>
              <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="01xxxxxxxxx" inputmode="tel" pattern="[0-9+\-\s()]{7,20}" required>
            @error('phone')<span class="err">{{ $message }}</span>@enderror
          </div>
        </div>

        {{-- SHIPPING ADDRESS --}}
        <div class="ck-section checkout-shipping-section">
          <h3 class="ck-title">{{ $isAr ? 'عنوان الشحن' : 'Shipping Address' }}</h3>
          <div class="form-group">
            <label>{{ $isAr ? 'حدّد مكان التوصيل' : 'Choose your delivery location' }}</label>
            <div id="checkout-location-empty" class="ck-location-empty" hidden>
              <div class="ck-location-empty-copy">
                <strong class="ck-location-empty-title">{{ $isAr ? 'اختار مكان التوصيل الأول' : 'Choose your delivery location first' }}</strong>
                <span>{{ $isAr ? 'اختار مكانك على الخريطة عشان نعرف نوصل طلبك بسهولة.' : 'Pick your location on the map so we can deliver your order easily.' }}</span>
              </div>
              <button type="button" id="choose-location-btn" class="ck-location-choose-btn">{{ $isAr ? 'اختار المكان' : 'Choose location' }}</button>
            </div>
            <div id="checkout-location-map-panel" class="ck-location-map-panel">
              <div class="ck-location-actions" aria-label="{{ $isAr ? 'اختيار موقع التوصيل' : 'Delivery location selection' }}">
                <button type="button" class="ck-location-action ck-location-action-primary" id="use-current-location-btn">📍 {{ $isAr ? 'استخدم موقعي الحالي' : 'Use My Current Location' }}</button>
                <label class="ck-location-switch" for="manual-location-mode-switch">
                  <span>{{ $checkoutText['manualLocation'] }}</span>
                  <input type="checkbox" id="manual-location-mode-switch" checked role="switch" aria-checked="true" aria-describedby="location-mode-note">
                  <span class="ck-switch-track" aria-hidden="true"></span>
                </label>
              </div>
              <span id="location-mode-note" class="ck-location-mode-note">{{ $checkoutText['manualReady'] }}</span>
              <div class="ck-map-shell">
                <div id="checkout-map" class="ck-map-canvas" aria-label="{{ $isAr ? 'خريطة مكان التوصيل التفاعلية' : 'Interactive delivery location map' }}"></div>
                <div id="checkout-map-placeholder" class="ck-map-placeholder">
                  <div class="ck-map-placeholder-inner">
                    <span class="ck-map-placeholder-icon" aria-hidden="true">⌖</span>
                    <span class="ck-map-placeholder-title">{{ $isAr ? 'اختار مكان التوصيل' : 'Choose a delivery pin' }}</span>
                    <span class="ck-map-placeholder-copy">{{ $isAr ? 'بنحمّل الخريطة تلقائيًا. تقدر تحدد المكان يدويًا، وبعد استخدام موقعك الحالي هيثبت المؤشر لتجنب تغييره بالغلط.' : 'The interactive map loads automatically. You can choose a location manually; after using your current location, the pin will lock to prevent accidental changes.' }}</span>
                    <button type="button" id="load-checkout-map-btn" class="ck-map-load-btn" aria-controls="checkout-map" hidden>{{ $checkoutText['mapRetry'] }}</button>
                  </div>
                </div>
                <div id="map-locating-overlay" style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.75);border-radius:14px;z-index:999;flex-direction:column;align-items:center;justify-content:center;gap:10px">
                  <div style="width:38px;height:38px;border:4px solid #e85d26;border-top-color:transparent;border-radius:50%;animation:map-spin .8s linear infinite"></div>
                  <span style="font-size:13px;font-weight:600;color:#e85d26">{{ $isAr ? 'بنحدد مكانك…' : 'Getting your location…' }}</span>
                </div>
              </div>
              <style>@keyframes map-spin{to{transform:rotate(360deg)}}</style>
              <div id="location-status" style="font-size:12px;color:var(--muted)" aria-live="polite"></div>
              <div id="location-fallback" class="ck-location-fallback" hidden aria-live="polite">
                <span id="location-fallback-copy" class="ck-location-fallback-copy"></span>
                <div class="ck-location-fallback-actions">
                  <button type="button" id="manual-location-btn" class="ck-location-fallback-btn ck-location-fallback-btn-primary">{{ $checkoutText['manualLocation'] }}</button>
                  <button type="button" id="retry-location-btn" class="ck-location-fallback-btn">{{ $checkoutText['retryLocation'] }}</button>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>{{ $isAr ? 'العنوان بالتفصيل' : 'Street Address' }} *</label>
            <input type="text" name="address" value="{{ old('address', $savedAddress['address'] ?? '') }}" required>
            @error('address')<span class="err">{{ $message }}</span>@enderror
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>{{ $isAr ? 'المدينة' : 'City' }} *</label>
              <input type="text" name="city" value="{{ old('city', $savedAddress['city'] ?? '') }}" required>
              @error('city')<span class="err">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
              <label>{{ $isAr ? 'المحافظة' : 'State / Governorate' }} *</label>
                <select name="state" required>
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
        <div class="ck-section">
          <h3 class="ck-title">{{ $isAr ? 'طريقة الدفع' : 'Payment Method' }}</h3>
          @php
            $selectedPaymentMethod = old('payment_method', array_key_first($paymentMethods) ?: 'cod');
          @endphp
          <div class="pay-methods">
            @foreach($paymentMethods as $val => $method)
            @php $paymentCopy = $isAr ? ($paymentLabelsAr[$val] ?? []) : []; @endphp
            <label class="pay-option {{ $selectedPaymentMethod === $val ? 'selected' : '' }}" data-val="{{ $val }}">
              <input type="radio" name="payment_method" value="{{ $val }}" {{ $selectedPaymentMethod === $val ? 'checked' : '' }}>
              <span class="pay-icon">{{ $method['icon'] ?? '💳' }}</span>
              <div>
                <div class="pay-title">{{ $paymentCopy['title'] ?? $method['title'] }}</div>
                <div class="pay-desc">{{ $paymentCopy['description'] ?? $method['description'] }}</div>
                @if($val === 'cod' && $codFeeAmount > 0)
                  <div class="pay-data" style="color:#9a3412">
                    <span>{{ $isAr ? 'رسوم الدفع عند الاستلام' : 'Cash on Delivery fee' }}:</span>
                    <strong>{{ number_format($codFeeAmount, 2) }} EGP</strong>
                  </div>
                @endif
                @if($method['data'] ?? '')
                  <div class="pay-data">
                    <span>{{ $paymentCopy['data_label'] ?? ($method['data_label'] ?? ($isAr ? 'التفاصيل' : 'Details')) }}:</span>
                    <strong>{{ $method['data'] }}</strong>
                    @if($method['link'] ?? null)
                      <a href="{{ $method['link'] }}" target="_blank" rel="noopener">{{ $isAr ? 'افتح الرابط' : 'Open link' }}</a>
                    @endif
                  </div>
                @endif
              </div>
            </label>
            @endforeach
          </div>
          @error('payment_method')<span class="err">{{ $message }}</span>@enderror
          <div style="font-size:12px;color:#6b7280;margin-top:10px">{{ $isAr ? 'لو الدفع بمحفظة أو إنستاباي، اعمل الطلب الأول، حوّل المبلغ، وبعدها ارفع الإيصال من صفحة الطلب.' : 'For Wallet or InstaPay, place the order first, transfer the amount, then upload your receipt from the order page.' }}</div>
        </div>

        {{-- ORDER NOTES --}}
        <div class="ck-section">
          <div class="form-group">
            <label>{{ $isAr ? 'ملاحظات على الطلب' : 'Order Notes' }} ({{ $isAr ? 'اختياري' : 'optional' }})</label>
            <textarea name="notes" rows="3" placeholder="{{ $isAr ? 'فيه أي ملاحظات خاصة بطلبك؟' : 'Any special instructions for your order…' }}">{{ old('notes') }}</textarea>
          </div>
        </div>

        <button type="submit" class="btn btn-dark place-order-btn">{{ $isAr ? 'أكّد الطلب ←' : 'Place Order →' }}</button>
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
  const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
  const updateSelectedMethod = () => {
    document.querySelectorAll('.pay-option').forEach((option) => {
      option.classList.toggle('selected', option.querySelector('input')?.checked === true);
    });
  };
  const codFeeAmount = Number(@json($codFeeAmount));
  const baseTotal = Number(@json($baseTotal));
  const codFeeRow = document.getElementById('cod-fee-summary-row');
  const codFeeValue = document.getElementById('cod-fee-summary-value');
  const checkoutTotalValue = document.getElementById('checkout-total-value');
  const formatEgp = (value) => `${Number(value).toFixed(2)} EGP`;
  const updateCheckoutTotal = () => {
    const selected = document.querySelector('input[name="payment_method"]:checked')?.value;
    const fee = selected === 'cod' ? codFeeAmount : 0;
    if (codFeeRow) codFeeRow.hidden = fee <= 0;
    if (codFeeValue) codFeeValue.textContent = formatEgp(fee);
    if (checkoutTotalValue) checkoutTotalValue.textContent = formatEgp(baseTotal + fee);
  };
  paymentOptions.forEach((input) => input.addEventListener('change', () => {
    updateSelectedMethod();
    updateCheckoutTotal();
  }));
  updateSelectedMethod();
  updateCheckoutTotal();

  const checkoutForm = document.getElementById('checkout-form');
  checkoutForm?.addEventListener('submit', (event) => {
    if (!checkoutForm.checkValidity()) return;
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
  const chooseLocationBtn = document.getElementById('choose-location-btn');
  const locationPrompt = document.getElementById('checkout-location-empty');
  const locationMapPanel = document.getElementById('checkout-location-map-panel');
  const hasInitialLocation = @json($hasSavedLocation);
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
    if (locationModeNote) locationModeNote.textContent = enabled ? checkoutText.manualReady : checkoutText.autoLocked;
  };

  const setStatus = (msg) => {
    if (locationStatus) locationStatus.textContent = msg;
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
    setCoords(lat, lng);
    try {
      const language = checkoutText.isAr ? 'ar,en' : 'en';
      const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1&accept-language=${encodeURIComponent(language)}`);
      const data = await res.json();
      const addr = data.address || {};
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
      if (addressInput) addressInput.value = street;
      if (cityInput) cityInput.value = city;
      if (addressInput) addressInput.dispatchEvent(new Event('input', { bubbles: true }));
      if (cityInput) cityInput.dispatchEvent(new Event('input', { bubbles: true }));
      matchGovernorate(state);
      setStatus(city || state ? `${checkoutText.selected} ${[city, state].filter(Boolean).join(', ')}` : checkoutText.locationSelected);
    } catch (_) {
      setStatus(checkoutText.detailsUnavailable);
    }
  };
  const initMap = (lat, lng) => {
    if (!window.L || !mapEl) return null;
    if (map) {
      map.setView([lat, lng], 14);
      marker?.setLatLng([lat, lng]);
      return map;
    }
    map = L.map(mapEl, { zoomControl: true }).setView([lat, lng], 14);
    L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      maxZoom: 20,
      attribution: 'Google Maps'
    }).addTo(map);
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
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
      marker.setLatLng(e.latlng);
      updateFields(e.latlng.lat, e.latlng.lng);
    });
    setManualLocationMode(manualLocationEnabled);
    mapPlaceholder?.setAttribute('hidden', '');
    window.setTimeout(() => map.invalidateSize(), 0);
    return map;
  };
  const savedLat = parseFloat(latitudeInput?.value || '');
  const savedLng = parseFloat(longitudeInput?.value || '');
  const startLat = Number.isFinite(savedLat) ? savedLat : 30.0444;
  const startLng = Number.isFinite(savedLng) ? savedLng : 31.2357;
  const ensureMap = (lat = startLat, lng = startLng) => {
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
      script.onerror = () => reject(new Error('Map resources could not be loaded.'));
      document.body.appendChild(script);
    }).catch((error) => {
      mapLoadPromise = null;
      throw error;
    });
    return mapLoadPromise;
  };
  if (mapEl) {
    const loadMap = () => {
      setStatus(checkoutText.mapLoading);
      // The loading overlay is the single source of loading text; hide the placeholder behind it.
      mapPlaceholder?.setAttribute('hidden', '');
      loadMapBtn?.setAttribute('hidden', '');
      return ensureMap().then(() => {
        setStatus(checkoutText.mapReady);
      }).catch(() => {
        setStatus(checkoutText.mapUnavailable);
        mapPlaceholder?.removeAttribute('hidden');
        loadMapBtn?.removeAttribute('hidden');
        showLocationFallback(checkoutText.mapUnavailable);
      });
    };

    loadMapBtn?.addEventListener('click', loadMap);
    const revealMap = (focusMap = false) => {
      locationPrompt?.setAttribute('hidden', '');
      locationMapPanel?.removeAttribute('hidden');
      return new Promise((resolve) => {
        window.requestAnimationFrame(() => {
          const mapPromise = loadMap() || Promise.resolve();
          mapPromise.then(() => {
            if (focusMap) mapEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            resolve();
          });
        });
      });
    };
    // Always show and initialize the map so a new customer can choose a pin immediately.
    loadMap();

    const mapOverlay = document.getElementById('map-locating-overlay');
    const showMapLoading = () => { if (mapOverlay) mapOverlay.style.display = 'flex'; };
    const hideMapLoading = () => { if (mapOverlay) mapOverlay.style.display = 'none'; };
    const fetchLocation = () => {
      hideLocationFallback();
      if (!navigator.geolocation) {
        setStatus(checkoutText.detectFailed);
        showLocationFallback(checkoutText.locationFallback);
        return;
      }
      setStatus(checkoutText.locating);
      showMapLoading();
      navigator.geolocation.getCurrentPosition(async (pos) => {
        const { latitude, longitude, accuracy } = pos.coords;
        setCoords(latitude, longitude);
        setManualLocationMode(false);
        updateFields(latitude, longitude);
        try {
          await ensureMap(latitude, longitude);
          setStatus(accuracy
            ? (checkoutText.isAr ? `${checkoutText.detected} (دقة ${Math.round(accuracy)} متر). ${checkoutText.autoLocked}` : `Location detected (${Math.round(accuracy)}m accuracy). ${checkoutText.autoLocked}`)
            : `${checkoutText.detected}. ${checkoutText.autoLocked}`);
        } catch (_) {
          setStatus(`${checkoutText.detected}. ${checkoutText.manualAddress}`);
        } finally {
          hideMapLoading();
        }
      }, (error) => {
        hideMapLoading();
        const message = error?.code === 1 ? checkoutText.accessDenied : checkoutText.detectFailed;
        setStatus(message);
        showLocationFallback(checkoutText.locationFallback);
      }, { enableHighAccuracy: true, timeout: 60000, maximumAge: 0 });
    };
    const requestCurrentLocation = () => {
      // Request the browser permission and immediately continue when granted.
      if (navigator.permissions) {
        navigator.permissions.query({ name: 'geolocation' }).then((result) => {
          if (result.state === 'granted') {
            fetchLocation();
          } else if (result.state === 'prompt') {
            fetchLocation();
            result.onchange = () => {
              if (result.state === 'granted') {
                result.onchange = null;
                fetchLocation();
              } else if (result.state === 'denied') {
                result.onchange = null;
                setStatus(checkoutText.accessDenied);
                showLocationFallback(checkoutText.locationFallback);
              }
            };
          } else {
            setStatus(checkoutText.accessBlocked);
            showLocationFallback(checkoutText.locationFallback);
          }
        }).catch(() => fetchLocation());
      } else {
        fetchLocation();
      }
    };

    chooseLocationBtn?.addEventListener('click', () => {
      revealMap();
      requestCurrentLocation();
    });
    manualModeSwitch?.addEventListener('change', (event) => {
      const enabled = event.target.checked;
      setManualLocationMode(enabled);
      setStatus(enabled ? checkoutText.manualReady : checkoutText.autoLocked);
    });
    manualLocationBtn?.addEventListener('click', () => {
      hideLocationFallback();
      if (manualModeSwitch) manualModeSwitch.checked = true;
      setManualLocationMode(true);
      revealMap(true).then(() => setStatus(checkoutText.manualReady));
    });
    retryLocationBtn?.addEventListener('click', requestCurrentLocation);
    useLocationBtn?.addEventListener('click', requestCurrentLocation);
  }
});
</script>
@endpush

@extends('layouts.app')
@php $isAr = session('locale') === 'ar'; @endphp
@section('title', isset($order) && $order ? ($isAr ? 'طلب رقم #'.$order->id.' — التفاصيل' : 'Order #'.$order->id.' — Details') : ($isAr ? 'دور على طلبك' : 'Look Up Your Order'))

@section('content')
<div class="page guest-order-page {{ $isAr ? 'guest-order-page-ar' : '' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

  <div class="breadcrumb">
    <a href="{{ route('home') }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a><span>/</span>
    <strong>{{ $isAr ? 'طلبي' : 'My Order' }}</strong>
  </div>

  {{-- ── LOOKUP FORM ── --}}
  <div class="track-hero">
    <div class="track-form-card">
      <div class="track-icon">🛍️</div>
      <h1 class="track-title">{{ $isAr ? 'دور على طلبك' : 'Find Your Order' }}</h1>
      <p class="track-sub">{{ $isAr ? 'اكتب رقم الطلب والإيميل اللي استخدمته وقت إتمام الطلب.' : 'Enter your order number and the email address you used at checkout.' }}</p>

      @if(session('error'))
        <div class="track-error">
          <span>⚠</span> {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('guest.order.lookup') }}" class="track-form">
        @csrf
        <div class="track-fields">
          <div class="track-field">
            <label>{{ $isAr ? 'رقم الطلب' : 'Order Number' }}</label>
            <input type="number" name="order_id" placeholder="{{ $isAr ? 'مثال: 1042' : 'e.g. 1042' }}"
                   value="{{ old('order_id') }}"
                   min="1" required autofocus>
            @error('order_id')<span class="field-err">{{ $message }}</span>@enderror
          </div>
          <div class="track-field">
            <label>{{ $isAr ? 'الإيميل' : 'Email Address' }}</label>
            <input type="email" name="email" placeholder="you@example.com"
                   value="{{ old('email') }}" required>
            @error('email')<span class="field-err">{{ $message }}</span>@enderror
          </div>
        </div>
        <button type="submit" class="track-submit">{{ $isAr ? 'دور على طلبي ←' : 'Find My Order →' }}</button>
      </form>

      <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--c-light);display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        @auth
          <a href="{{ route('account.orders') }}" class="track-link" style="font-size:13px">📋 {{ $isAr ? 'شوف كل طلباتك في حسابك' : 'View all orders in your account' }}</a>
        @else
          <a href="{{ route('login') }}" class="track-link" style="font-size:13px">🔐 {{ $isAr ? 'سجّل دخولك' : 'Sign in to your account' }}</a>
        @endauth
        <a href="{{ route('order.track') }}" class="track-link" style="font-size:13px;color:var(--c-mid)">{{ $isAr ? 'اتتبع برقم الموبايل بدل كده' : 'Track by phone number instead' }}</a>
      </div>
    </div>
  </div>

  {{-- ── ORDER RESULT ── --}}
  @if(isset($order) && $order)
  @php
    $status = \App\Http\Controllers\Web\OrderTrackingController::statusInfo($order->status ?? 'pending');
    $steps  = ['pending','processing','shipped','completed'];
    $curIdx = array_search(strtolower($order->status ?? 'pending'), $steps);
    if ($curIdx === false) $curIdx = 0;
    $cancelled = in_array(strtolower($order->status ?? ''), ['cancelled','refunded','failed']);
  @endphp

  <div class="order-result">

    {{-- Header --}}
    <div class="or-header">
      <div>
        <h2 class="or-title">{{ $isAr ? 'طلب رقم' : 'Order' }} <span>#{{ $order->id }}</span></h2>
        <div class="or-date">{{ $isAr ? 'اتعمل يوم ' : 'Placed on ' }}{{ $order->date_created ? ($isAr ? \Carbon\Carbon::parse($order->date_created)->locale('ar')->translatedFormat('j F Y، g:i A') : \Carbon\Carbon::parse($order->date_created)->format('d M Y, g:i A')) : ($isAr ? \Carbon\Carbon::parse($order->created_at)->locale('ar')->translatedFormat('j F Y') : \Carbon\Carbon::parse($order->created_at)->format('d M Y')) }}</div>
      </div>
      <div class="or-status-pill" style="background:{{ $status['bg'] }};color:{{ $status['color'] }};border:1.5px solid {{ $status['color'] }}20">
        {{ $status['icon'] }} {{ $status['label'] }}
      </div>
    </div>

    {{-- Progress Tracker --}}
    @if(!$cancelled)
    <div class="or-progress-wrap">
      <div class="or-progress">
        @foreach($steps as $i => $step)
        @php
          $stepStatus = \App\Http\Controllers\Web\OrderTrackingController::statusInfo($step);
          $done   = $i <= $curIdx;
          $active = $i === $curIdx;
        @endphp
        <div class="or-step {{ $done ? 'done' : '' }} {{ $active ? 'active' : '' }}">
          <div class="or-step-circle">{{ $done ? '✓' : ($i+1) }}</div>
          <div class="or-step-label">{{ $stepStatus['icon'] }} {{ $stepStatus['label'] }}</div>
        </div>
        @if($i < count($steps)-1)
          <div class="or-step-line {{ $i < $curIdx ? 'done' : '' }}"></div>
        @endif
        @endforeach
      </div>
    </div>
    @else
    <div class="or-cancelled-banner" style="background:{{ $status['bg'] }};color:{{ $status['color'] }}">
      {{ $status['icon'] }} {{ $isAr ? 'الطلب ده حالته' : 'This order has been' }} <strong>{{ $status['label'] }}</strong>.
      @if(strtolower($order->status) === 'refunded') {{ $isAr ? 'تم رد الفلوس.' : 'A refund has been processed.' }} @endif
    </div>
    @endif

    <div class="or-body">

      {{-- Order Items --}}
      <div class="or-section">
        <div class="or-section-title">{{ $isAr ? 'منتجات الطلب' : 'Order Items' }}</div>
        <div class="or-items">
          @forelse($lineItems ?? [] as $item)
          <div class="or-item">
            <div class="or-item-img">
              @if($item['thumbnail'] ?? null)
                <img src="{{ $item['thumbnail'] }}" alt="{{ $item['name'] }}" loading="lazy">
              @else
                <div class="or-item-placeholder">👕</div>
              @endif
            </div>
            <div class="or-item-info">
              <div class="or-item-name">{{ $item['name'] }}</div>
              @if(!empty($item['attributes']))
                <div class="or-item-attrs">
                  @foreach((array)$item['attributes'] as $attr => $val)
                    <span>{{ $attr }}: <strong>{{ $val }}</strong></span>
                  @endforeach
                </div>
              @endif
              <div class="or-item-meta">
                {{ $isAr ? 'الكمية:' : 'Qty:' }} <strong>{{ $item['quantity'] }}</strong>
                &nbsp;·&nbsp;
                {{ number_format($item['price'] ?? 0, 2) }} EGP {{ $isAr ? 'للقطعة' : 'each' }}
              </div>
            </div>
            <div class="or-item-total">{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }} EGP</div>
          </div>
          @empty
          <div style="color:var(--c-mid);font-size:13px;padding:16px 0">{{ $isAr ? 'مفيش تفاصيل للمنتجات متاحة.' : 'No item details available.' }}</div>
          @endforelse
        </div>
      </div>

      <div class="or-right-col">

        {{-- Order Summary --}}
        <div class="or-section">
          <div class="or-section-title">{{ $isAr ? 'ملخص الطلب' : 'Order Summary' }}</div>
          <div class="or-summary">
            <div class="or-summary-row">
              <span>{{ $isAr ? 'الإجمالي الفرعي' : 'Subtotal' }}</span>
              <span>{{ number_format($order->original_total ?? 0, 2) }} EGP</span>
            </div>
            @if(($order->discount_total ?? 0) > 0)
            <div class="or-summary-row" style="color:#22a35c">
              <span>{{ $isAr ? 'الخصم' : 'Discount' }} @if($order->coupon_code)(<code>{{ $order->coupon_code }}</code>)@endif</span>
              <span>−{{ number_format($order->discount_total, 2) }} EGP</span>
            </div>
            @endif
            @if(($order->shipping_total ?? 0) > 0)
            <div class="or-summary-row">
              <span>{{ $isAr ? 'الشحن' : 'Shipping' }}</span>
              <span>{{ number_format($order->shipping_total, 2) }} EGP</span>
            </div>
            @endif
            <div class="or-summary-row or-summary-total">
              <span>{{ $isAr ? 'الإجمالي' : 'Total' }}</span>
              <span>{{ number_format($order->final_total ?? $order->original_total, 2) }} EGP</span>
            </div>
            <div class="or-summary-row" style="font-size:12px;color:var(--c-mid)">
              <span>{{ $isAr ? 'طريقة الدفع' : 'Payment' }}</span>
              <span>{{ $order->payment_method_title ?? ucfirst($order->payment_method ?? 'N/A') }}</span>
            </div>
            @if(\App\Helpers\PaymentConfig::isManualMethod($order->payment_method ?? null))
              <div class="or-summary-row" style="font-size:12px;color:#9a3412">
                <span>{{ $isAr ? 'حالة الدفع' : 'Payment status' }}</span>
                <strong>{{ $isAr ? match(strtolower($order->payment_status ?? 'pending_payment')) { 'confirmed' => 'تم التأكيد', 'failed' => 'فشل', default => 'في الانتظار' } : ucwords(str_replace('_', ' ', $order->payment_status ?? 'pending_payment')) }}</strong>
              </div>
            @endif
          </div>
        </div>

        {{-- Shipping Address --}}
        <div class="or-section">
          <div class="or-section-title">{{ $isAr ? 'عنوان الشحن' : 'Shipping Address' }}</div>
          <div class="or-address">
            @php $sh = $shipping ?? $billing ?? []; @endphp
            <div class="or-address-name">{{ ($sh['first_name'] ?? '') . ' ' . ($sh['last_name'] ?? '') }}</div>
            @if($sh['address_1'] ?? null)<div>{{ $sh['address_1'] }}</div>@endif
            @if($sh['city'] ?? null)<div>{{ $sh['city'] }}@if($sh['state'] ?? null), {{ $sh['state'] }}@endif</div>@endif
            @if($sh['country'] ?? null)<div>{{ $sh['country'] }}</div>@endif
            @if($sh['phone'] ?? null)<div style="margin-top:6px;font-weight:600">📞 {{ $sh['phone'] }}</div>@endif
            @if($sh['email'] ?? null)<div style="font-size:12px;color:var(--c-mid)">✉ {{ $sh['email'] }}</div>@endif
          </div>
        </div>

        {{-- Customer Note --}}
        @if($order->customer_note ?? null)
        <div class="or-section">
          <div class="or-section-title">{{ $isAr ? 'ملاحظتك' : 'Your Note' }}</div>
          <div class="or-note">{{ $order->customer_note }}</div>
        </div>
        @endif

        @if(\App\Helpers\PaymentConfig::isManualMethod($order->payment_method ?? null) && ($order->payment_status ?? '') !== 'confirmed')
          @php
            $guestPaymentMethod = \App\Helpers\PaymentConfig::detailsFor($order->payment_method);
            $guestHasUploadedReceipt = filled($order->payment_receipt_path);
            $guestReceiptUploadLimit = \App\Http\Controllers\Web\PaymentReceiptController::MAX_UPLOADS_PER_ORDER;
            $guestReceiptUploadCount = $paymentReceiptCount ?? 0;
            $guestReceiptUploadsRemaining = max(0, $guestReceiptUploadLimit - $guestReceiptUploadCount);
            $guestCanUploadReceipt = $guestReceiptUploadCount < $guestReceiptUploadLimit;
          @endphp
          @if($guestPaymentMethod)
          <div class="or-section" style="background:#fffaf5">
            <div class="or-section-title" style="color:#9a3412">{{ $guestHasUploadedReceipt ? ($isAr ? 'مراجعة الدفع' : 'Payment verification') : ($isAr ? 'ارفع إيصال الدفع' : 'Upload payment receipt') }}</div>
            @if($guestHasUploadedReceipt)
              <div style="margin:0 0 12px;padding:12px 14px;border-radius:9px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:13px;line-height:1.6">
                <strong>✓ {{ $isAr ? 'تم رفع الإيصال وهو تحت المراجعة.' : 'Your receipt has been uploaded and is pending review.' }}</strong>
                <div style="margin-top:3px">{{ $isAr ? 'مش محتاج ترفع حاجة تاني، إلا لو عايز تستبدل الإيصال الحالي.' : 'You do not need to upload another file unless you want to replace the current receipt.' }}</div>
                <div style="margin-top:3px">{{ $isAr ? 'متاح لك '.$guestReceiptUploadsRemaining.' من أصل '.$guestReceiptUploadLimit.' محاولات رفع.' : $guestReceiptUploadsRemaining.' of '.$guestReceiptUploadLimit.' upload attempts remain.' }}</div>
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_receipt_path) }}" target="_blank" rel="noopener" style="display:inline-block;margin-top:6px;color:#047857;font-weight:700">{{ $isAr ? 'شوف الإيصال المرفوع ←' : 'View uploaded receipt →' }}</a>
              </div>
            @endif
            <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:10px">
              @if($guestHasUploadedReceipt)
                {{ $isAr ? 'بنراجع تحويل' : 'We are reviewing your transfer of' }} <strong>{{ number_format($order->final_total, 2) }} EGP</strong>{{ $isAr ? ' إلى ' : ' to ' }}<strong>{{ $guestPaymentMethod['destination'] }}</strong>{{ $isAr ? ' حسب الإيصال المرفوع.' : ' using the uploaded receipt.' }}
              @else
                {{ $isAr ? 'حوّل' : 'Transfer' }} <strong>{{ number_format($order->final_total, 2) }} EGP</strong> {{ $isAr ? 'إلى' : 'to' }} <strong>{{ $guestPaymentMethod['destination'] }}</strong>{{ $isAr ? '، وبعدها ارفع الإيصال هنا.' : ', then upload the receipt below.' }}
              @endif
            </p>
            @if(!empty($guestPaymentMethod['link']))
              <a href="{{ $guestPaymentMethod['link'] }}" target="_blank" rel="noopener" style="font-size:12px;color:#e85d26;display:inline-block;margin-bottom:10px">{{ $isAr ? 'افتح رابط إنستاباي ←' : 'Open InstaPay link →' }}</a>
            @endif
            @if($guestCanUploadReceipt)
              <form method="POST" action="{{ route('guest.order.payment-receipt', $order->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="email" value="{{ $billing['email'] ?? '' }}">
                <div style="font-size:12px;font-weight:700;color:#6b7280;margin-bottom:7px">
                  {{ $guestHasUploadedReceipt ? ($isAr ? 'لو عايز تغيّر الإيصال الحالي، اختار صورة بديلة. متبقي '.$guestReceiptUploadsRemaining.' من '.$guestReceiptUploadLimit.'.' : 'Choose a replacement image only if you need to change the current receipt. '.$guestReceiptUploadsRemaining.' of '.$guestReceiptUploadLimit.' attempts remain.') : ($isAr ? 'اختار صورة الإيصال وسيتم رفعها تلقائياً. متبقي '.$guestReceiptUploadsRemaining.' من '.$guestReceiptUploadLimit.'.' : 'Choose the receipt image and it will upload automatically. '.$guestReceiptUploadsRemaining.' of '.$guestReceiptUploadLimit.' attempts remain.') }}
                </div>
                <input id="guest-receipt-{{ $order->id }}" type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0" onchange="if(this.files.length){document.getElementById('guest-receipt-status-{{ $order->id }}').textContent='{{ $isAr ? 'جارٍ رفع الإيصال…' : 'Uploading receipt…' }}';this.form.requestSubmit();}">
                <label for="guest-receipt-{{ $order->id }}" class="track-submit" style="display:inline-flex;width:auto;padding:9px 14px;font-size:12px;cursor:pointer;align-items:center;justify-content:center">
                  {{ $guestHasUploadedReceipt ? ($isAr ? 'ارفع إيصال بديل' : 'Upload replacement receipt') : ($isAr ? 'ارفع الإيصال' : 'Upload receipt') }}
                </label>
                <div id="guest-receipt-status-{{ $order->id }}" aria-live="polite" style="display:inline-block;margin-inline-start:8px;font-size:12px;color:#6b7280"></div>
                <noscript><button class="track-submit" style="width:auto;padding:9px 14px;font-size:12px;margin-top:8px">{{ $isAr ? 'ابعت الإيصال' : 'Submit receipt' }}</button></noscript>
              </form>
            @else
              <div style="margin-top:12px;padding:12px 14px;border-radius:9px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:13px;line-height:1.6">
                <strong>{{ $isAr ? 'وصلت للحد الأقصى لرفع الإيصالات.' : 'You have reached the receipt upload limit.' }}</strong>
                <div>{{ $isAr ? 'تم استخدام 3 من 3 محاولات. من فضلك استنى مراجعة الدفع.' : 'All 3 of 3 upload attempts have been used. Please wait for payment review.' }}</div>
              </div>
            @endif
          </div>
          @endif
        @endif

        {{-- Create Account CTA for guests --}}
        @guest
        <div class="or-section" style="background:linear-gradient(135deg,#f9fafb,#eff6ff)">
          <div class="or-section-title" style="color:#1d4ed8">{{ $isAr ? 'اعمل حساب' : 'Create an Account' }}</div>
          <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:12px">
            {{ $isAr ? 'احفظ بياناتك واتتبع كل طلباتك في مكان واحد.' : 'Save your details and track all your orders in one place.' }}
          </p>
          <a href="{{ route('register') }}"
             style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            {{ $isAr ? 'اعمل حساب مجانًا ←' : 'Sign Up Free →' }}
          </a>
        </div>
        @endguest

      </div>
    </div>

    {{-- Footer actions --}}
    <div class="or-footer">
      <a href="{{ route('guest.order') }}" class="btn btn-outline" style="border-radius:10px;padding:11px 20px;font-size:13.5px">{{ $isAr ? 'دور على طلب تاني' : 'Look Up Another Order' }}</a>
      <a href="{{ route('shop') }}" class="btn btn-dark" style="border-radius:10px;padding:11px 20px;font-size:13.5px">{{ $isAr ? 'كمّل تسوّق' : 'Continue Shopping' }}</a>
    </div>
  </div>
  @endif

</div>
@endsection

@push('scripts')
<style>
.track-hero{display:flex;justify-content:center;padding:20px 0 32px}
.track-form-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:40px 36px;width:100%;max-width:560px;text-align:center}
.track-icon{font-size:48px;margin-bottom:12px;line-height:1}
.track-title{font-size:22px;font-weight:800;margin-bottom:8px}
.track-sub{font-size:14px;color:var(--c-mid);margin-bottom:20px;line-height:1.6}
.track-error{background:#fff0f0;border:1.5px solid #fcc;border-radius:10px;padding:12px 16px;font-size:13.5px;color:#c0392b;margin-bottom:18px;text-align:left;display:flex;gap:8px;align-items:flex-start}
.track-form{text-align:left}
.track-fields{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.track-field label{display:block;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--c-mid);margin-bottom:7px}
.track-field input{width:100%;padding:11px 14px;border:1.5px solid var(--c-light);border-radius:10px;font-size:14px;font-family:inherit;outline:none;background:var(--c-bg);color:var(--c-dark)}
.track-field input:focus{border-color:#aaa;background:var(--c-white)}
.field-err{font-size:12px;color:#e02020;display:block;margin-top:4px}
.track-submit{width:100%;padding:13px;background:var(--c-dark);color:#fff;border:none;border-radius:11px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .15s;letter-spacing:.2px}
.track-submit:hover{background:#333}
.track-link{color:var(--c-orange);font-weight:600;text-decoration:none}

.order-result{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:32px}
.or-header{display:flex;align-items:flex-start;justify-content:space-between;padding:24px 28px;border-bottom:1.5px solid var(--c-light);flex-wrap:wrap;gap:12px}
.or-title{font-size:20px;font-weight:800}
.or-title span{color:var(--c-orange)}
.or-date{font-size:13px;color:var(--c-mid);margin-top:4px}
.or-status-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:50px;font-size:13px;font-weight:700}
.or-progress-wrap{padding:28px 28px 20px;border-bottom:1.5px solid var(--c-light)}
.or-progress{display:flex;align-items:flex-start;justify-content:center;gap:0}
.or-step{display:flex;flex-direction:column;align-items:center;gap:8px;flex:0 0 auto;min-width:100px}
.or-step-circle{width:36px;height:36px;border-radius:50%;background:var(--c-light);color:var(--c-mid);font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;transition:all .25s;border:2px solid var(--c-light);z-index:1}
.or-step.done .or-step-circle{background:var(--c-dark);color:#fff;border-color:var(--c-dark)}
.or-step.active .or-step-circle{background:var(--c-orange);color:#fff;border-color:var(--c-orange);box-shadow:0 0 0 4px rgba(232,93,38,.15)}
.or-step-label{font-size:11.5px;color:var(--c-mid);text-align:center;font-weight:500;line-height:1.3}
.or-step.done .or-step-label,.or-step.active .or-step-label{color:var(--c-dark);font-weight:700}
.or-step-line{flex:1;height:2px;background:var(--c-light);margin-top:17px;min-width:30px;transition:background .25s}
.or-step-line.done{background:var(--c-dark)}
.or-cancelled-banner{margin:20px 28px;border-radius:10px;padding:14px 18px;font-size:14px;display:flex;align-items:center;gap:8px}
.or-body{display:grid;grid-template-columns:1fr 280px;gap:0;align-items:start}
.or-section{padding:24px 28px;border-bottom:1.5px solid var(--c-light)}
.or-section:last-child{border-bottom:none}
.or-right-col{border-left:1.5px solid var(--c-light)}
.or-right-col .or-section{border-bottom:1.5px solid var(--c-light)}
.or-right-col .or-section:last-child{border-bottom:none}
.or-section-title{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--c-mid);margin-bottom:16px}
.or-items{display:flex;flex-direction:column;gap:12px}
.or-item{display:flex;align-items:flex-start;gap:14px;padding:12px;background:var(--c-bg);border-radius:10px}
.or-item-img{width:60px;height:60px;border-radius:8px;overflow:hidden;background:var(--c-light);flex-shrink:0}
.or-item-img img{width:100%;height:100%;object-fit:cover}
.or-item-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:24px;background:var(--c-light)}
.or-item-info{flex:1;min-width:0}
.or-item-name{font-size:13.5px;font-weight:600;margin-bottom:4px;line-height:1.3}
.or-item-attrs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px}
.or-item-attrs span{font-size:11.5px;background:var(--c-white);border:1px solid var(--c-light);padding:2px 7px;border-radius:5px;color:var(--c-mid)}
.or-item-meta{font-size:12.5px;color:var(--c-mid)}
.or-item-total{font-size:14px;font-weight:700;white-space:nowrap;flex-shrink:0}
.or-summary{display:flex;flex-direction:column;gap:9px}
.or-summary-row{display:flex;justify-content:space-between;font-size:13.5px;align-items:baseline}
.or-summary-row code{background:var(--c-bg);padding:1px 6px;border-radius:4px;font-size:12px}
.or-summary-total{border-top:1.5px solid var(--c-light);padding-top:9px;font-weight:800;font-size:15px}
.or-address{font-size:13.5px;color:var(--c-mid);line-height:1.7}
.or-address-name{font-weight:700;color:var(--c-dark);margin-bottom:2px}
.or-note{font-size:13.5px;color:var(--c-mid);line-height:1.6;background:var(--c-bg);padding:12px;border-radius:8px;font-style:italic}
.or-footer{padding:20px 28px;display:flex;gap:12px;flex-wrap:wrap;border-top:1.5px solid var(--c-light)}

/* Arabic tracking layout overrides. */
.guest-order-page-ar{font-family:'Cairo','Tahoma',sans-serif;text-align:right}.guest-order-page-ar .track-form,.guest-order-page-ar .track-error{text-align:right}.guest-order-page-ar .track-field label,.guest-order-page-ar .or-section-title{letter-spacing:0;text-transform:none}.guest-order-page-ar .or-right-col{border-left:none;border-right:1.5px solid var(--c-light)}.guest-order-page-ar .or-item,.guest-order-page-ar .or-header,.guest-order-page-ar .or-footer{direction:rtl}.guest-order-page-ar .or-progress{direction:rtl}.guest-order-page-ar .or-summary-row{direction:rtl}

@media(max-width:700px){
  .track-fields{grid-template-columns:1fr}
  .or-body{grid-template-columns:1fr}
  .or-right-col{border-left:none;border-top:1.5px solid var(--c-light)}.guest-order-page-ar .or-right-col{border-right:none}
  .or-progress{gap:0;overflow-x:auto;padding-bottom:8px;justify-content:flex-start}
  .or-step{min-width:80px}
  .track-form-card{padding:28px 20px}
}
</style>
@endpush

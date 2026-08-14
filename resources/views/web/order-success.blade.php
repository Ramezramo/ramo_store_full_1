@extends('layouts.app')
@php $isAr = session('locale') === 'ar'; @endphp
@section('title', $isAr ? 'تم تأكيد الطلب — Ramo Store' : 'Order Confirmed — Ramo Store')

@push('styles')
<style>
  .order-success-page-ar{font-family:'Cairo','Tahoma',sans-serif;text-align:right;direction:rtl}.order-success-page-ar .od-row,.order-success-page-ar .order-item-row,.order-success-page-ar .success-actions{direction:rtl}.order-success-page-ar .order-item-attr{letter-spacing:0}
</style>
@endpush

@section('content')
<div class="page order-success-page {{ $isAr ? 'order-success-page-ar' : '' }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="max-width:700px;margin:0 auto">

  <div class="success-card">
    <div class="success-icon">✅</div>
    <h1 class="success-title">{{ $isAr ? 'طلبك اتأكد!' : 'Order Placed!' }}</h1>
    <p class="success-sub">{{ $isAr ? 'شكرًا إنك اشتريت من RamoStore. استلمنا طلبك وبنجهزه دلوقتي.' : 'Thank you for shopping with RamoStore. Your order has been received and is being processed.' }}</p>
    <div class="success-badge">{{ $isAr ? 'طلب رقم' : 'Order' }} #{{ $order->id }}</div>
  </div>

  <div class="order-detail-card">
    @php
      $displayOrderStatus = $order->general_order_status ?? $order->status ?? 'pending';
      $displayOrderLabel = $isAr
        ? match(strtolower($displayOrderStatus)) {
            'pending', 'pending_payment' => 'في الانتظار', 'processing' => 'جاري التجهيز', 'shipped' => 'اتشحن', 'completed', 'delivered' => 'اتسلّم',
            'cancelled' => 'اتلغى', 'refunded' => 'اترجع', 'failed', 'payment_failed' => 'فشل الدفع',
            'partially_shipped' => 'اتشحن جزء من الطلب', 'partially_delivered' => 'اتسلّم جزء من الطلب', 'partially_cancelled' => 'اتلغى جزء من الطلب',
            default => $displayOrderStatus,
          }
        : match($displayOrderStatus) {
            'partially_shipped' => 'Partially Shipped',
            'partially_delivered' => 'Partially Delivered',
            'partially_cancelled' => 'Partially Cancelled',
            default => ucfirst($displayOrderStatus),
          };
    @endphp
    <div class="od-row"><span class="od-label">{{ $isAr ? 'حالة الطلب' : 'Status' }}</span><span class="status-badge status-{{ $displayOrderStatus }}">{{ $displayOrderLabel }}</span></div>
    @if(\App\Helpers\PaymentConfig::isManualMethod($order->payment_method ?? null))
      <div class="od-row"><span class="od-label">{{ $isAr ? 'حالة الدفع' : 'Payment status' }}</span><span class="status-badge" style="background:#fff7ed;color:#9a3412">{{ $isAr ? match(strtolower($order->payment_status ?? 'pending_payment')) { 'confirmed' => 'تم التأكيد', 'failed' => 'فشل', default => 'في الانتظار' } : ucwords(str_replace('_', ' ', $order->payment_status ?? 'pending_payment')) }}</span></div>
    @endif
    <div class="od-row"><span class="od-label">{{ $isAr ? 'طريقة الدفع' : 'Payment' }}</span><span>{{ $order->payment_method_title }}</span></div>
    <div class="od-row"><span class="od-label">{{ $isAr ? 'التاريخ' : 'Date' }}</span><span>{{ $isAr ? \Carbon\Carbon::parse($order->date_created)->locale('ar')->translatedFormat('j F Y، g:i A') : \Carbon\Carbon::parse($order->date_created)->format('M d, Y h:i A') }}</span></div>
    @if((float) ($order->total_tax ?? 0) > 0)
      <div class="od-row"><span class="od-label">{{ $isAr ? 'الضريبة' : 'Tax' }}</span><span>{{ number_format($order->total_tax, 2) }} EGP</span></div>
    @endif
    <div class="od-row"><span class="od-label">{{ $isAr ? 'الإجمالي' : 'Total' }}</span><span class="od-total">{{ number_format($order->final_total, 2) }} EGP</span></div>
  </div>

  @if(\App\Helpers\PaymentConfig::isManualMethod($order->payment_method ?? null) && ($order->payment_status ?? '') !== 'confirmed')
    @php
      $paymentMethod = \App\Helpers\PaymentConfig::detailsFor($order->payment_method);
      $successBilling = json_decode($order->billing ?? '{}', true) ?: [];
    @endphp
    @if($paymentMethod)
    <div class="order-detail-card" style="margin-top:16px;background:#fffaf5;border:1.5px solid #fed7aa">
      <h3 style="font-size:15px;font-weight:800;margin-bottom:7px">{{ $isAr ? 'كمّل الدفع' : 'Complete your payment' }}</h3>
      <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:12px">
        {{ $isAr ? 'حوّل' : 'Transfer' }} <strong>{{ number_format($order->final_total, 2) }} EGP</strong> {{ $isAr ? 'باستخدام' : 'using' }} {{ $paymentMethod['title'] }} {{ $isAr ? 'إلى:' : 'to:' }}
      </p>
      <div style="padding:12px;background:#fff;border-radius:9px;font-size:15px;font-weight:800;color:#9a3412;word-break:break-word">
        {{ $paymentMethod['destination'] }}
        @if(!empty($paymentMethod['link']))
          · <a href="{{ $paymentMethod['link'] }}" target="_blank" rel="noopener" style="font-size:12px;color:#e85d26">{{ $isAr ? 'افتح رابط إنستاباي' : 'Open InstaPay link' }}</a>
        @endif
      </div>
      @if($order->payment_receipt_path)
        <div style="margin-top:14px;padding:12px 14px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:9px;color:#065f46">
          <div style="font-size:13px;font-weight:800">✓ {{ $isAr ? 'الإيصال اترفع بنجاح' : 'Receipt uploaded successfully' }}</div>
          <div style="font-size:12px;line-height:1.5;margin-top:4px">
            {{ $isAr ? 'إيصالك' : 'Your receipt is' }} <strong>{{ $isAr ? 'في انتظار المراجعة' : 'pending verification' }}</strong>.
            @if($order->payment_receipt_name)
              {{ $isAr ? 'الملف:' : 'File:' }} <strong>{{ $order->payment_receipt_name }}</strong>
            @endif
          </div>
          <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_receipt_path) }}"
             target="_blank" rel="noopener" style="display:inline-block;margin-top:7px;font-size:12px;font-weight:700;color:#047857">
            {{ $isAr ? 'شوف الإيصال المرفوع ←' : 'View uploaded receipt →' }}
          </a>
        </div>
      @endif
      <p style="font-size:12px;color:#6b7280;margin:12px 0">
        {{ $order->payment_receipt_path ? ($isAr ? 'عاوز تغيّر الإيصال؟ اختار صورة جديدة تحت:' : 'Need to replace the receipt? Choose a new image below:') : ($isAr ? 'بعد التحويل، ارفع سكرين شوت أو صورة للإيصال:' : 'After transferring, upload a screenshot or photo of the receipt:') }}
      </p>
      <form method="POST" action="{{ auth()->check() ? route('account.order.payment-receipt', $order->id) : route('guest.order.payment-receipt', $order->id) }}" enctype="multipart/form-data">
        @csrf
        @guest
          <input type="hidden" name="email" value="{{ $successBilling['email'] ?? '' }}">
        @endguest
      <label for="success-receipt-{{ $order->id }}" style="display:block;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:7px">
        {{ $order->payment_receipt_path ? ($isAr ? 'ارفع إيصال بديل لو محتاج تغيّر الحالي' : 'Upload a replacement receipt only if you need to change the current one') : ($isAr ? 'اختار صورة للإيصال' : 'Choose a receipt image') }}
      </label>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <input id="success-receipt-{{ $order->id }}" type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required style="font-size:12px">
        <button class="btn btn-dark" style="font-size:12px">{{ $order->payment_receipt_path ? ($isAr ? 'ارفع بديل' : 'Upload replacement') : ($isAr ? 'ارفع الإيصال' : 'Upload receipt') }}</button>
        </div>
      </form>
    </div>
    @endif
  @endif

  {{-- VENDOR SUB-ORDERS (if split) --}}
  @if(isset($subOrders) && $subOrders->count() > 1)
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">{{ $isAr ? 'طلبك هيوصلك في' : 'Your order ships in' }} {{ $subOrders->count() }} {{ $isAr ? 'شحنات منفصلة' : 'separate packages' }}</h3>
      <p style="font-size:13px;color:#6b7280;margin-bottom:16px">{{ $isAr ? 'كل متجر هيشحن لوحده، وهتقدر تتابع كل شحنة.' : "Each vendor will ship independently. You'll receive tracking per shipment." }}</p>

      @foreach($subOrders as $sub)
        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;margin-bottom:12px">
          <div style="font-size:13px;font-weight:700;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center">
            <span>{{ $sub->vendor_shop_name ?: ($isAr ? 'المتجر' : 'Store') }}</span>
            <span style="font-size:11px;color:#6b7280;font-weight:400">{{ $isAr ? 'طلب فرعي رقم' : 'Sub-order' }} #{{ $sub->id }}</span>
          </div>
          @foreach($sub->items as $item)
            <div style="display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid #f3f4f6">
              <span>{{ $item['name'] }} <span style="color:#6b7280">× {{ $item['quantity'] }}</span></span>
              <span style="font-weight:600">{{ number_format($item['subtotal'],2) }} EGP</span>
            </div>
          @endforeach
          <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;padding-top:8px;color:#e85d26">
            <span>{{ $isAr ? 'إجمالي الشحنة' : 'Shipment Total' }}</span>
            <span>{{ number_format($sub->total,2) }} EGP</span>
          </div>
        </div>
      @endforeach
    </div>

  @elseif(isset($subOrders) && $subOrders->count() === 1)
    {{-- Single vendor: normal items view --}}
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">{{ $isAr ? 'منتجات الطلب' : 'Items Ordered' }}</h3>
      @foreach($subOrders->first()->items as $item)
        <div class="order-item-row">
          @if(!empty($item['image']))
            <img class="order-item-thumb" src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
          @else
            <div class="order-item-thumb-fallback" aria-hidden="true">🛍️</div>
          @endif
          <div class="order-item-info">
            <span class="order-item-name">{{ $item['name'] }}</span>
            @if(!empty($item['attributes']))<span class="order-item-attr">@foreach($item['attributes'] as $k=>$v) {{ $k }}: {{ $v }} @endforeach</span>@endif
          </div>
          <span class="order-item-qty">× {{ $item['quantity'] }}</span>
          <span class="order-item-price">{{ number_format($item['subtotal'],2) }} EGP</span>
        </div>
      @endforeach
    </div>
  @else
    {{-- Fallback (legacy orders without sub-orders) --}}
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">{{ $isAr ? 'منتجات الطلب' : 'Items Ordered' }}</h3>
      @foreach($lineItems as $item)
        <div class="order-item-row">
          @if(!empty($item['image']))
            <img class="order-item-thumb" src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
          @else
            <div class="order-item-thumb-fallback" aria-hidden="true">🛍️</div>
          @endif
          <div class="order-item-info">
            <span class="order-item-name">{{ $item['name'] }}</span>
            @if(!empty($item['attributes']))<span class="order-item-attr">@foreach($item['attributes'] as $k=>$v) {{ $k }}: {{ $v }} @endforeach</span>@endif
          </div>
          <span class="order-item-qty">× {{ $item['quantity'] }}</span>
          <span class="order-item-price">{{ number_format($item['subtotal'],2) }} EGP</span>
        </div>
      @endforeach
    </div>
  @endif

  @guest
  @php
    $billing = json_decode($order->billing ?? '{}', true) ?? [];
    $guestEmail = $billing['email'] ?? '';
  @endphp
  <div class="order-detail-card" style="margin-top:16px;background:linear-gradient(135deg,#f9fafb,#eff6ff);border-color:#bfdbfe">
    <div style="display:flex;align-items:flex-start;gap:14px">
      <div style="font-size:28px;line-height:1;flex-shrink:0">📧</div>
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e40af;margin-bottom:4px">{{ $isAr ? 'احتفظ بتفاصيل طلبك' : 'Save your order details' }}</div>
        <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:14px">
          {{ $isAr ? 'كملت كضيف. احتفظ برقم طلبك' : 'You checked out as a guest. Note your order number' }} <strong>#{{ $order->id }}</strong> {{ $isAr ? 'عشان تقدر تدور عليه في أي وقت بالإيميل.' : '— you can look it up anytime using your email address.' }}
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a href="{{ route('guest.order') }}?order_id={{ $order->id }}"
             style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            🔍 {{ $isAr ? 'اتتبع الطلب ده' : 'Track This Order' }}
          </a>
          <a href="{{ route('register') }}"
             style="display:inline-block;background:#fff;border:1.5px solid #1a1a1a;color:#1a1a1a;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            {{ $isAr ? 'اعمل حساب ←' : 'Create Account →' }}
          </a>
        </div>
      </div>
    </div>
  </div>
  @endguest

  <div class="success-actions">
    @auth
      <a href="{{ route('account.orders') }}" class="btn btn-dark">{{ $isAr ? 'شوف طلباتي' : 'View My Orders' }}</a>
    @endauth
    <a href="{{ route('shop') }}" class="btn btn-outline">{{ $isAr ? 'كمّل تسوّق' : 'Continue Shopping' }}</a>
  </div>

</div>
@endsection

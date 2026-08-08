@extends('layouts.app')
@section('title', 'Order Confirmed — Ramo Store')

@section('content')
<div class="page" style="max-width:700px;margin:0 auto">

  <div class="success-card">
    <div class="success-icon">✅</div>
    <h1 class="success-title">Order Placed!</h1>
    <p class="success-sub">Thank you for shopping with RamoStore. Your order has been received and is being processed.</p>
    <div class="success-badge">Order #{{ $order->id }}</div>
  </div>

  <div class="order-detail-card">
    <div class="od-row"><span class="od-label">Status</span><span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
    @if(in_array($order->payment_method, ['manual_wallet', 'manual_instapay']))
      <div class="od-row"><span class="od-label">Payment status</span><span class="status-badge" style="background:#fff7ed;color:#9a3412">{{ ucwords(str_replace('_', ' ', $order->payment_status ?? 'pending_payment')) }}</span></div>
    @endif
    <div class="od-row"><span class="od-label">Payment</span><span>{{ $order->payment_method_title }}</span></div>
    <div class="od-row"><span class="od-label">Date</span><span>{{ \Carbon\Carbon::parse($order->date_created)->format('M d, Y h:i A') }}</span></div>
    <div class="od-row"><span class="od-label">Total</span><span class="od-total">{{ number_format($order->final_total, 2) }} EGP</span></div>
  </div>

  @if(in_array($order->payment_method, ['manual_wallet', 'manual_instapay']) && ($order->payment_status ?? '') !== 'confirmed')
    @php
      $paymentMethod = \App\Helpers\PaymentConfig::detailsFor($order->payment_method);
      $successBilling = json_decode($order->billing ?? '{}', true) ?: [];
    @endphp
    @if($paymentMethod)
    <div class="order-detail-card" style="margin-top:16px;background:#fffaf5;border:1.5px solid #fed7aa">
      <h3 style="font-size:15px;font-weight:800;margin-bottom:7px">Complete your payment</h3>
      <p style="font-size:13px;color:#6b7280;line-height:1.6;margin-bottom:12px">
        Transfer <strong>{{ number_format($order->final_total, 2) }} EGP</strong> using {{ $paymentMethod['title'] }} to:
      </p>
      <div style="padding:12px;background:#fff;border-radius:9px;font-size:15px;font-weight:800;color:#9a3412;word-break:break-word">
        {{ $paymentMethod['destination'] }}
        @if(!empty($paymentMethod['link']))
          · <a href="{{ $paymentMethod['link'] }}" target="_blank" rel="noopener" style="font-size:12px;color:#e85d26">Open InstaPay link</a>
        @endif
      </div>
      <p style="font-size:12px;color:#6b7280;margin:12px 0">After transferring, upload a screenshot or photo of the receipt:</p>
      <form method="POST" action="{{ auth()->check() ? route('account.order.payment-receipt', $order->id) : route('guest.order.payment-receipt', $order->id) }}" enctype="multipart/form-data">
        @csrf
        @guest
          <input type="hidden" name="email" value="{{ $successBilling['email'] ?? '' }}">
        @endguest
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <input type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required style="font-size:12px">
          <button class="btn btn-dark" style="font-size:12px">Upload receipt</button>
        </div>
      </form>
    </div>
    @endif
  @endif

  {{-- VENDOR SUB-ORDERS (if split) --}}
  @if(isset($subOrders) && $subOrders->count() > 1)
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">Your order ships in {{ $subOrders->count() }} separate packages</h3>
      <p style="font-size:13px;color:#6b7280;margin-bottom:16px">Each vendor will ship independently. You'll receive tracking per shipment.</p>

      @foreach($subOrders as $sub)
        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;margin-bottom:12px">
          <div style="font-size:13px;font-weight:700;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center">
            <span>{{ $sub->vendor_shop_name ?: 'Store' }}</span>
            <span style="font-size:11px;color:#6b7280;font-weight:400">Sub-order #{{ $sub->id }}</span>
          </div>
          @foreach($sub->items as $item)
            <div style="display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid #f3f4f6">
              <span>{{ $item['name'] }} <span style="color:#6b7280">× {{ $item['quantity'] }}</span></span>
              <span style="font-weight:600">{{ number_format($item['subtotal'],2) }} EGP</span>
            </div>
          @endforeach
          <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;padding-top:8px;color:#e85d26">
            <span>Shipment Total</span>
            <span>{{ number_format($sub->total,2) }} EGP</span>
          </div>
        </div>
      @endforeach
    </div>

  @elseif(isset($subOrders) && $subOrders->count() === 1)
    {{-- Single vendor: normal items view --}}
    <div class="order-detail-card" style="margin-top:16px">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Items Ordered</h3>
      @foreach($subOrders->first()->items as $item)
        <div class="order-item-row">
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
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Items Ordered</h3>
      @foreach($lineItems as $item)
        <div class="order-item-row">
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
        <div style="font-size:15px;font-weight:700;color:#1e40af;margin-bottom:4px">Save your order details</div>
        <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:14px">
          You checked out as a guest. Note your order number <strong>#{{ $order->id }}</strong> — you can look it up anytime using your email address.
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a href="{{ route('guest.order') }}?order_id={{ $order->id }}"
             style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            🔍 Track This Order
          </a>
          <a href="{{ route('register') }}"
             style="display:inline-block;background:#fff;border:1.5px solid #1a1a1a;color:#1a1a1a;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            Create Account →
          </a>
        </div>
      </div>
    </div>
  </div>
  @endguest

  <div class="success-actions">
    @auth
      <a href="{{ route('account.orders') }}" class="btn btn-dark">View My Orders</a>
    @endauth
    <a href="{{ route('shop') }}" class="btn btn-outline">Continue Shopping</a>
  </div>

</div>
@endsection

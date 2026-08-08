@extends('layouts.app')
@section('title', isset($order) && $order ? 'Order #'.$order->id.' — Details' : 'Look Up Your Order')

@section('content')
<div class="page">

  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span>
    <strong>My Order</strong>
  </div>

  {{-- ── LOOKUP FORM ── --}}
  <div class="track-hero">
    <div class="track-form-card">
      <div class="track-icon">🛍️</div>
      <h1 class="track-title">Find Your Order</h1>
      <p class="track-sub">Enter your order number and the email address you used at checkout.</p>

      @if(session('error'))
        <div class="track-error">
          <span>⚠</span> {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('guest.order.lookup') }}" class="track-form">
        @csrf
        <div class="track-fields">
          <div class="track-field">
            <label>Order Number</label>
            <input type="number" name="order_id" placeholder="e.g. 1042"
                   value="{{ old('order_id') }}"
                   min="1" required autofocus>
            @error('order_id')<span class="field-err">{{ $message }}</span>@enderror
          </div>
          <div class="track-field">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="you@example.com"
                   value="{{ old('email') }}" required>
            @error('email')<span class="field-err">{{ $message }}</span>@enderror
          </div>
        </div>
        <button type="submit" class="track-submit">Find My Order →</button>
      </form>

      <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--c-light);display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        @auth
          <a href="{{ route('account.orders') }}" class="track-link" style="font-size:13px">📋 View all orders in your account</a>
        @else
          <a href="{{ route('login') }}" class="track-link" style="font-size:13px">🔐 Sign in to your account</a>
        @endauth
        <a href="{{ route('order.track') }}" class="track-link" style="font-size:13px;color:var(--c-mid)">Track by phone number instead</a>
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
        <h2 class="or-title">Order <span>#{{ $order->id }}</span></h2>
        <div class="or-date">Placed on {{ $order->date_created ? \Carbon\Carbon::parse($order->date_created)->format('d M Y, g:i A') : \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</div>
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
      {{ $status['icon'] }} This order has been <strong>{{ $status['label'] }}</strong>.
      @if(strtolower($order->status) === 'refunded') A refund has been processed. @endif
    </div>
    @endif

    <div class="or-body">

      {{-- Order Items --}}
      <div class="or-section">
        <div class="or-section-title">Order Items</div>
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
                Qty: <strong>{{ $item['quantity'] }}</strong>
                &nbsp;·&nbsp;
                {{ number_format($item['price'] ?? 0, 2) }} EGP each
              </div>
            </div>
            <div class="or-item-total">{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }} EGP</div>
          </div>
          @empty
          <div style="color:var(--c-mid);font-size:13px;padding:16px 0">No item details available.</div>
          @endforelse
        </div>
      </div>

      <div class="or-right-col">

        {{-- Order Summary --}}
        <div class="or-section">
          <div class="or-section-title">Order Summary</div>
          <div class="or-summary">
            <div class="or-summary-row">
              <span>Subtotal</span>
              <span>{{ number_format($order->original_total ?? 0, 2) }} EGP</span>
            </div>
            @if(($order->discount_total ?? 0) > 0)
            <div class="or-summary-row" style="color:#22a35c">
              <span>Discount @if($order->coupon_code)(<code>{{ $order->coupon_code }}</code>)@endif</span>
              <span>−{{ number_format($order->discount_total, 2) }} EGP</span>
            </div>
            @endif
            @if(($order->shipping_total ?? 0) > 0)
            <div class="or-summary-row">
              <span>Shipping</span>
              <span>{{ number_format($order->shipping_total, 2) }} EGP</span>
            </div>
            @endif
            <div class="or-summary-row or-summary-total">
              <span>Total</span>
              <span>{{ number_format($order->final_total ?? $order->original_total, 2) }} EGP</span>
            </div>
            <div class="or-summary-row" style="font-size:12px;color:var(--c-mid)">
              <span>Payment</span>
              <span>{{ $order->payment_method_title ?? ucfirst($order->payment_method ?? 'N/A') }}</span>
            </div>
            @if(in_array($order->payment_method ?? '', ['manual_wallet', 'manual_instapay']))
              <div class="or-summary-row" style="font-size:12px;color:#9a3412">
                <span>Payment status</span>
                <strong>{{ ucwords(str_replace('_', ' ', $order->payment_status ?? 'pending_payment')) }}</strong>
              </div>
            @endif
          </div>
        </div>

        {{-- Shipping Address --}}
        <div class="or-section">
          <div class="or-section-title">Shipping Address</div>
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
          <div class="or-section-title">Your Note</div>
          <div class="or-note">{{ $order->customer_note }}</div>
        </div>
        @endif

        @if(in_array($order->payment_method ?? '', ['manual_wallet', 'manual_instapay']) && ($order->payment_status ?? '') !== 'confirmed')
          @php
            $guestPaymentMethod = \App\Helpers\PaymentConfig::detailsFor($order->payment_method);
          @endphp
          @if($guestPaymentMethod)
          <div class="or-section" style="background:#fffaf5">
            <div class="or-section-title" style="color:#9a3412">Upload payment receipt</div>
            <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:10px">
              Transfer <strong>{{ number_format($order->final_total, 2) }} EGP</strong> to <strong>{{ $guestPaymentMethod['destination'] }}</strong>, then upload the receipt below.
            </p>
            @if(!empty($guestPaymentMethod['link']))
              <a href="{{ $guestPaymentMethod['link'] }}" target="_blank" rel="noopener" style="font-size:12px;color:#e85d26;display:inline-block;margin-bottom:10px">Open InstaPay link →</a>
            @endif
            <form method="POST" action="{{ route('guest.order.payment-receipt', $order->id) }}" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="email" value="{{ $billing['email'] ?? '' }}">
              <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <input type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required style="font-size:12px">
                <button class="track-submit" style="width:auto;padding:9px 14px;font-size:12px">Upload receipt</button>
              </div>
            </form>
          </div>
          @endif
        @endif

        {{-- Create Account CTA for guests --}}
        @guest
        <div class="or-section" style="background:linear-gradient(135deg,#f9fafb,#eff6ff)">
          <div class="or-section-title" style="color:#1d4ed8">Create an Account</div>
          <p style="font-size:13px;color:#555;line-height:1.6;margin-bottom:12px">
            Save your details and track all your orders in one place.
          </p>
          <a href="{{ route('register') }}"
             style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;font-size:13px;font-weight:600;padding:9px 16px;border-radius:8px">
            Sign Up Free →
          </a>
        </div>
        @endguest

      </div>
    </div>

    {{-- Footer actions --}}
    <div class="or-footer">
      <a href="{{ route('guest.order') }}" class="btn btn-outline" style="border-radius:10px;padding:11px 20px;font-size:13.5px">Look Up Another Order</a>
      <a href="{{ route('shop') }}" class="btn btn-dark" style="border-radius:10px;padding:11px 20px;font-size:13.5px">Continue Shopping</a>
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

@media(max-width:700px){
  .track-fields{grid-template-columns:1fr}
  .or-body{grid-template-columns:1fr}
  .or-right-col{border-left:none;border-top:1.5px solid var(--c-light)}
  .or-progress{gap:0;overflow-x:auto;padding-bottom:8px;justify-content:flex-start}
  .or-step{min-width:80px}
  .track-form-card{padding:28px 20px}
}
</style>
@endpush

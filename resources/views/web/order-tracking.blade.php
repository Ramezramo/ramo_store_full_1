@extends('layouts.app')
@section('title', isset($order) && $order ? 'Order #'.$order->id.' — Tracking' : 'Track Your Order')

@section('content')
<div class="page">

  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span>
    <strong>Order Tracking</strong>
  </div>

  {{-- ── LOOKUP FORM ── --}}
  <div class="track-hero">
    <div class="track-form-card">
      <div class="track-icon">📦</div>
      <h1 class="track-title">Track Your Order</h1>
      <p class="track-sub">Enter your order number and the phone number used at checkout.</p>

      @if($error ?? null)
        <div class="track-error">
          <span>⚠</span> {{ $error }}
        </div>
      @endif

      <form method="POST" action="{{ route('order.track.submit') }}" class="track-form">
        @csrf
        <div class="track-fields">
          <div class="track-field">
            <label>Order Number</label>
            <input type="number" name="order_id" placeholder="e.g. 1042"
                   value="{{ old('order_id', request('order_id')) }}"
                   min="1" required autofocus>
            @error('order_id')<span class="field-err">{{ $message }}</span>@enderror
          </div>
          <div class="track-field">
            <label>Phone Number</label>
            <input type="tel" name="phone" placeholder="e.g. 01012345678"
                   value="{{ old('phone') }}" required>
            @error('phone')<span class="field-err">{{ $message }}</span>@enderror
          </div>
        </div>
        <button type="submit" class="track-submit">Track Order →</button>
      </form>

      @auth
        <p class="track-alt">Or <a href="{{ route('account.orders') }}" class="track-link">view all your orders</a> in your account.</p>
      @endauth
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

    {{-- Per-store shipment statuses --}}
    @if(isset($subOrders) && $subOrders->count())
    <div class="or-store-statuses">
      <div class="or-section-title">Store Shipments</div>
      <p class="or-store-intro">This order may arrive in separate packages. Each store has its own delivery status.</p>
      <div class="or-store-list">
        @foreach($subOrders as $sub)
          @php
            $subStatus = \App\Http\Controllers\Web\OrderTrackingController::statusInfo($sub->status ?? 'pending');
            $subSteps = ['pending', 'processing', 'shipped', 'completed'];
            $subIndex = array_search(strtolower($sub->status ?? 'pending'), $subSteps);
            $subIndex = $subIndex === false ? 0 : $subIndex;
            $subCancelled = in_array(strtolower($sub->status ?? ''), ['cancelled', 'refunded', 'failed']);
          @endphp
          <div class="or-store-card">
            <div class="or-store-head">
              <div>
                <strong>{{ $sub->vendor_shop_name ?: 'Store' }}</strong>
                <span>Sub-order #{{ $sub->id }}</span>
              </div>
              <span class="or-store-pill" style="background:{{ $subStatus['bg'] }};color:{{ $subStatus['color'] }}">
                {{ $subStatus['icon'] }} {{ $subStatus['label'] }}
              </span>
            </div>
            @if($subCancelled)
              <div class="or-store-cancelled" style="color:{{ $subStatus['color'] }}">
                {{ $subStatus['icon'] }} This store shipment has been {{ strtolower($subStatus['label']) }}.
              </div>
            @else
              <div class="or-store-progress">
                @foreach($subSteps as $i => $subStep)
                  @php
                    $subStepInfo = \App\Http\Controllers\Web\OrderTrackingController::statusInfo($subStep);
                    $subDone = $i <= $subIndex;
                  @endphp
                  <div class="or-store-step {{ $subDone ? 'done' : '' }}">
                    <span>{{ $subDone ? '✓' : ($i + 1) }}</span>
                    <small>{{ $subStep === 'completed' ? 'Delivered' : $subStepInfo['label'] }}</small>
                  </div>
                  @if($i < count($subSteps) - 1)
                    <div class="or-store-line {{ $i < $subIndex ? 'done' : '' }}"></div>
                  @endif
                @endforeach
              </div>
            @endif
            @if($sub->tracking_number)
              <div class="or-store-tracking">
                Tracking: <strong>{{ $sub->tracking_number }}</strong>
                @if($sub->tracking_carrier) via {{ $sub->tracking_carrier }} @endif
              </div>
            @endif
            @if(!empty($sub->items))
              <div class="or-store-items">
                @foreach($sub->items as $subItem)
                  <div>
                    <span>{{ $subItem['name'] ?? 'Item' }} × {{ $subItem['quantity'] ?? 1 }}</span>
                    <strong>{{ number_format($subItem['subtotal'] ?? 0, 2) }} EGP</strong>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        @endforeach
      </div>
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
          </div>
        </div>

        {{-- Customer Note --}}
        @if($order->customer_note ?? null)
        <div class="or-section">
          <div class="or-section-title">Your Note</div>
          <div class="or-note">{{ $order->customer_note }}</div>
        </div>
        @endif

      </div>
    </div>

    {{-- Footer actions --}}
    <div class="or-footer">
      <a href="{{ route('order.track') }}" class="btn btn-outline" style="border-radius:10px;padding:11px 20px;font-size:13.5px">Track Another Order</a>
      <a href="{{ route('shop') }}" class="btn btn-dark" style="border-radius:10px;padding:11px 20px;font-size:13.5px">Continue Shopping</a>
    </div>
  </div>
  @endif

</div>
@endsection

@push('scripts')
<style>
/* ── Track Hero ── */
.track-hero{display:flex;justify-content:center;padding:20px 0 32px}
.track-form-card{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:40px 36px;width:100%;max-width:540px;text-align:center}
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
.track-alt{margin-top:16px;font-size:13px;color:var(--c-mid)}
.track-link{color:var(--c-orange);font-weight:600}

/* ── Order Result ── */
.order-result{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:32px}
.or-header{display:flex;align-items:flex-start;justify-content:space-between;padding:24px 28px;border-bottom:1.5px solid var(--c-light);flex-wrap:wrap;gap:12px}
.or-title{font-size:20px;font-weight:800}
.or-title span{color:var(--c-orange)}
.or-date{font-size:13px;color:var(--c-mid);margin-top:4px}
.or-status-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:50px;font-size:13px;font-weight:700}

/* Progress tracker */
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

/* Per-store shipments */
.or-store-statuses{padding:24px 28px;border-bottom:1.5px solid var(--c-light)}
.or-store-intro{font-size:13px;color:var(--c-mid);margin:-8px 0 16px}
.or-store-list{display:flex;flex-direction:column;gap:12px}
.or-store-card{border:1.5px solid var(--c-light);border-radius:12px;padding:15px;background:var(--c-bg)}
.or-store-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.or-store-head strong{font-size:14px}
.or-store-head span:not(.or-store-pill){display:block;color:var(--c-mid);font-size:11px;margin-top:3px}
.or-store-pill{display:inline-flex!important;align-items:center;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700}
.or-store-progress{display:flex;align-items:flex-start;margin:18px 0 8px}
.or-store-step{display:flex;flex-direction:column;align-items:center;gap:5px;min-width:68px}
.or-store-step span{width:24px;height:24px;border:2px solid var(--c-light);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--c-mid);font-size:11px;font-weight:700;background:var(--c-white)}
.or-store-step.done span{background:var(--c-dark);border-color:var(--c-dark);color:#fff}
.or-store-step small{font-size:10px;color:var(--c-mid);text-align:center;line-height:1.2}
.or-store-step.done small{color:var(--c-dark);font-weight:700}
.or-store-line{height:2px;flex:1;background:var(--c-light);margin-top:11px}
.or-store-line.done{background:var(--c-dark)}
.or-store-cancelled{font-size:13px;font-weight:600;padding-top:14px}
.or-store-tracking{font-size:12px;color:var(--c-mid);padding-top:10px;border-top:1px solid var(--c-light)}
.or-store-tracking strong{color:var(--c-dark);font-family:monospace}
.or-store-items{border-top:1px solid var(--c-light);margin-top:10px;padding-top:8px;display:flex;flex-direction:column;gap:5px}
.or-store-items div{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:var(--c-mid)}
.or-store-items strong{color:var(--c-dark);white-space:nowrap}

/* Body layout */
.or-body{display:grid;grid-template-columns:1fr 280px;gap:0;align-items:start}
.or-section{padding:24px 28px;border-bottom:1.5px solid var(--c-light)}
.or-section:last-child{border-bottom:none}
.or-right-col{border-left:1.5px solid var(--c-light)}
.or-right-col .or-section{border-bottom:1.5px solid var(--c-light)}
.or-right-col .or-section:last-child{border-bottom:none}
.or-section-title{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--c-mid);margin-bottom:16px}

/* Items */
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

/* Summary */
.or-summary{display:flex;flex-direction:column;gap:9px}
.or-summary-row{display:flex;justify-content:space-between;font-size:13.5px;align-items:baseline}
.or-summary-row code{background:var(--c-bg);padding:1px 6px;border-radius:4px;font-size:12px}
.or-summary-total{border-top:1.5px solid var(--c-light);padding-top:9px;font-weight:800;font-size:15px}

/* Address */
.or-address{font-size:13.5px;color:var(--c-mid);line-height:1.7}
.or-address-name{font-weight:700;color:var(--c-dark);margin-bottom:2px}
.or-note{font-size:13.5px;color:var(--c-mid);line-height:1.6;background:var(--c-bg);padding:12px;border-radius:8px;font-style:italic}

/* Footer */
.or-footer{padding:20px 28px;display:flex;gap:12px;flex-wrap:wrap;border-top:1.5px solid var(--c-light)}

@media(max-width:700px){
  .track-fields{grid-template-columns:1fr}
  .or-body{grid-template-columns:1fr}
  .or-right-col{border-left:none;border-top:1.5px solid var(--c-light)}
  .or-progress{gap:0;overflow-x:auto;padding-bottom:8px;justify-content:flex-start}
  .or-step{min-width:80px}
  .or-store-progress{overflow-x:auto;padding-bottom:4px}
  .or-store-step{min-width:64px}
  .track-form-card{padding:28px 20px}
}
</style>
@endpush

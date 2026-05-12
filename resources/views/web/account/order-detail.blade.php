@extends('web.account.layout')
@php
  $pageTitle = 'Order #'.$order->id;
  $cancelled = in_array($order->status, ['cancelled','refunded','failed']);
  $hasSubOrders = isset($subOrders) && $subOrders->count() > 0;
  $messageCount = 0;
  if ($hasSubOrders) {
    foreach ($subOrders as $s) {
      $messageCount += isset($s->messages) ? count($s->messages) : 0;
    }
  }

  // ── Savings calculation ──
  $saleSavings = 0;
  foreach ($lineItems as $li) {
    $regPrice  = floatval($li['variation']['regular_price'] ?? $li['regular_price'] ?? 0);
    $paidPrice = floatval($li['variation']['price_used']    ?? $li['price']['final'] ?? (is_numeric($li['price'] ?? null) ? $li['price'] : 0));
    $lineQty   = intval($li['quantity'] ?? 1);
    if ($regPrice > $paidPrice && $paidPrice > 0) {
      $saleSavings += ($regPrice - $paidPrice) * $lineQty;
    }
  }
  $couponSavings = floatval($order->discount_total ?? 0);
  $totalSavings  = $saleSavings + $couponSavings;
  $couponCode    = $order->coupon_code ?? null;
@endphp

@section('account-content')
<div class="acc-section-title" style="margin-bottom:20px">Order #{{ $order->id }}</div>

@if($cancelled)
  <div class="acc-alert acc-alert-error" style="margin-bottom:24px">This order has been <strong>{{ ucfirst($order->status) }}</strong>.</div>
@endif

@if(session('success'))
  <div class="acc-alert acc-alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="acc-alert acc-alert-error" style="margin-bottom:16px">{{ session('error') }}</div>
@endif

{{-- ── ORDER META ────────────────────────────────────────────────── --}}
<div class="order-detail-card">
  <div class="od-row"><span class="od-label">Order #</span><strong>#{{ $order->id }}</strong></div>
  <div class="od-row"><span class="od-label">Status</span><span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
  <div class="od-row"><span class="od-label">Date</span><span>{{ \Carbon\Carbon::parse($order->date_created)->format('M d, Y h:i A') }}</span></div>
  <div class="od-row"><span class="od-label">Payment</span><span>{{ $order->payment_method_title }}</span></div>
  <div class="od-row"><span class="od-label">Total Paid</span><strong style="color:#e85d26">{{ number_format($order->final_total, 2) }} EGP</strong></div>
  @if($order->customer_note)
    <div class="od-row"><span class="od-label">Notes</span><span>{{ $order->customer_note }}</span></div>
  @endif
</div>

{{-- ── YOU SAVED BANNER ──────────────────────────────────────────── --}}
@if(!$cancelled && $totalSavings > 0)
<div style="margin-top:14px;background:linear-gradient(135deg,#dcfce7 0%,#f0fdf4 100%);border:1.5px solid #86efac;border-radius:14px;padding:16px 20px">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
    <div style="font-size:22px">🎉</div>
    <div>
      <div style="font-size:15px;font-weight:800;color:#15803d">You saved {{ number_format($totalSavings, 2) }} EGP on this order!</div>
      <div style="font-size:12px;color:#16a34a;margin-top:2px">Great deal — here's the breakdown:</div>
    </div>
  </div>
  <div style="display:flex;flex-direction:column;gap:6px">
    @if($saleSavings > 0)
      <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border-radius:8px;padding:8px 12px">
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:14px">🏷️</span>
          <span style="font-size:13px;font-weight:600;color:#15803d">Sale price discount</span>
        </div>
        <span style="font-size:13px;font-weight:800;color:#15803d">−{{ number_format($saleSavings, 2) }} EGP</span>
      </div>
    @endif
    @if($couponSavings > 0)
      <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border-radius:8px;padding:8px 12px">
        <div style="display:flex;align-items:center;gap:6px">
          <span style="font-size:14px">🎟️</span>
          <div>
            <span style="font-size:13px;font-weight:600;color:#15803d">Coupon</span>
            @if($couponCode)
              <span style="margin-left:6px;background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;padding:2px 7px;border-radius:999px;font-family:monospace;border:1px solid #86efac">{{ strtoupper($couponCode) }}</span>
            @endif
          </div>
        </div>
        <span style="font-size:13px;font-weight:800;color:#15803d">−{{ number_format($couponSavings, 2) }} EGP</span>
      </div>
    @endif
  </div>
</div>
@endif

{{-- ── SHIPPING ADDRESS ─────────────────────────────────────────── --}}
@if(!empty($billing))
<div class="order-detail-card" style="margin-top:16px">
  <h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Shipping Address</h3>
  <p style="font-size:14px;line-height:1.8;color:var(--c-dark)">
    {{ $billing['first_name'] ?? '' }} {{ $billing['last_name'] ?? '' }}<br>
    {{ $billing['address_1'] ?? '' }}<br>
    {{ $billing['city'] ?? '' }}@if(!empty($billing['state'])), {{ $billing['state'] }}@endif<br>
    {{ $billing['phone'] ?? '' }}
    @if(!empty($billing['latitude']) && !empty($billing['longitude']))
      <br>
      <iframe
        width="100%"
        height="220"
        style="border:0;border-radius:12px;margin-top:10px"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        src="https://www.google.com/maps?q={{ $billing['latitude'] }},{{ $billing['longitude'] }}&z=15&output=embed">
      </iframe>
    @endif
  </p>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- SUB-ORDERS (one per vendor)                                        --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@if($hasSubOrders)

  @foreach($subOrders as $sub)
    @php
      $subStepIndex = match($sub->status) {
        'pending'    => 0,
        'processing' => 1,
        'shipped'    => 2,
        'completed'  => 3,
        default      => 0,
      };
      $subCancelled = in_array($sub->status, ['cancelled']);
      $subFillPct   = $subCancelled ? 0 : match($subStepIndex) { 0=>0,1=>33,2=>66,3=>100,default=>0 };
      $subSteps     = ['pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','completed'=>'Delivered'];
    @endphp

    <div style="margin-top:20px;border:2px solid #e5e7eb;border-radius:16px;overflow:hidden">

      {{-- Sub-order header --}}
      <div style="background:#f9fafb;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e7eb;flex-wrap:wrap;gap:8px">
        <div>
          <div style="font-size:13px;font-weight:800;color:#111827">
            {{ $sub->vendor_shop_name ?: 'Store' }}
            <span style="font-weight:400;color:#6b7280;font-size:12px">— Sub-order #{{ $sub->id }}</span>
          </div>
          @if($sub->tracking_number)
            <div style="font-size:12px;color:#6b7280;margin-top:3px">
              Tracking: <strong style="font-family:monospace">{{ $sub->tracking_number }}</strong>
              @if($sub->tracking_carrier) via {{ $sub->tracking_carrier }} @endif
            </div>
          @endif
        </div>
        <span class="status-badge status-{{ $sub->status }}" style="font-size:12px">{{ ucfirst($sub->status) }}</span>
      </div>

      {{-- Sub-order progress bar --}}
      @if(!$subCancelled)
      <div style="padding:16px 18px 4px;background:#fff">
        <div style="position:relative;display:flex;justify-content:space-between;margin-bottom:20px">
          <div style="position:absolute;top:14px;left:0;right:0;height:3px;background:#e5e7eb;z-index:0"></div>
          <div style="position:absolute;top:14px;left:0;height:3px;background:#e85d26;z-index:1;width:{{ $subFillPct }}%;transition:width .4s"></div>
          @foreach($subSteps as $sKey => $sLabel)
            @php
              $si = array_search($sKey, array_keys($subSteps));
              $sCls = $si < $subStepIndex ? 'done' : ($si === $subStepIndex ? 'current' : '');
            @endphp
            <div style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:4px;flex:1">
              <div style="width:28px;height:28px;border-radius:50%;border:3px solid {{ $sCls==='done'?'#e85d26':($sCls==='current'?'#e85d26':'#e5e7eb') }};background:{{ $sCls==='done'?'#e85d26':($sCls==='current'?'#fff7ed':'#fff') }};display:flex;align-items:center;justify-content:center">
                @if($sCls === 'done')
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                @elseif($sCls === 'current')
                  <div style="width:8px;height:8px;border-radius:50%;background:#e85d26"></div>
                @endif
              </div>
              <div style="font-size:10px;font-weight:600;color:{{ $sCls?'#111827':'#6b7280' }};text-align:center;max-width:60px">{{ $sLabel }}</div>
            </div>
          @endforeach
        </div>
      </div>
      @else
      <div style="padding:10px 18px;background:#fff;font-size:13px;color:#ef4444;font-weight:600">This shipment was cancelled.</div>
      @endif

      {{-- Sub-order items --}}
      <div style="padding:0 18px 16px;background:#fff">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:10px">Items</div>
        @foreach($sub->items as $item)
          <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f3f4f6">
            <div style="flex:1">
              <a href="{{ route('product', $item['product_id']) }}" style="font-weight:600;font-size:13px;color:#111827;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px">{{ $item['name'] }}</a>
              @if(!empty($item['attributes']))
                <div style="font-size:11px;color:#6b7280;margin-top:2px">
                  @foreach($item['attributes'] as $k => $v){{ $k }}: {{ $v }} @endforeach
                </div>
              @endif
            </div>
            <div style="font-size:12px;color:#6b7280;white-space:nowrap">× {{ $item['quantity'] }}</div>
            <div style="font-size:13px;font-weight:700;white-space:nowrap;color:#111827">{{ number_format($item['subtotal'], 2) }} EGP</div>
          </div>
        @endforeach
        <div style="display:flex;justify-content:space-between;padding-top:12px;font-size:13px">
          <span style="color:#6b7280">Sub-total</span>
          <strong>{{ number_format($sub->subtotal, 2) }} EGP</strong>
        </div>
        @if($sub->discount_total > 0)
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:4px">
            <span style="color:#22c55e">Discount</span>
            <strong style="color:#22c55e">−{{ number_format($sub->discount_total, 2) }} EGP</strong>
          </div>
        @endif
        <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:800;margin-top:8px;padding-top:8px;border-top:2px solid #e5e7eb">
          <span>Vendor Total</span>
          <span style="color:#e85d26">{{ number_format($sub->total, 2) }} EGP</span>
        </div>
      </div>

      {{-- Messages & reply for this sub-order --}}
      <div style="padding:16px 18px;background:#fafafa;border-top:1px solid #e5e7eb">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:12px">
          Messages with {{ $sub->vendor_shop_name ?: 'Vendor' }}
        </div>

        @if(isset($sub->messages) && count($sub->messages) > 0)
          <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px">
            @foreach($sub->messages as $msg)
              <div style="border:1px solid {{ $msg->is_vendor_response ? '#fdba74' : '#e5e7eb' }};background:{{ $msg->is_vendor_response ? '#fff7ed' : '#fff' }};border-radius:10px;padding:10px 13px">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px">
                  <strong style="color:{{ $msg->is_vendor_response ? '#e85d26' : '#111827' }}">
                    {{ $msg->is_vendor_response ? ($msg->vendor_shop_name ?: 'Vendor') : 'You' }}
                  </strong>
                  <span style="color:#6b7280">{{ \Carbon\Carbon::parse($msg->created_at)->format('d M Y, g:i A') }}</span>
                </div>
                <div style="font-size:13px;line-height:1.7;color:#111827">{{ $msg->message }}</div>
                @if($msg->is_vendor_response)
                  <div style="margin-top:6px;display:inline-block;background:rgba(232,93,38,.12);color:#e85d26;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px">Vendor</div>
                @endif
              </div>
            @endforeach
          </div>
        @else
          <div style="color:#6b7280;font-size:13px;margin-bottom:12px">No messages yet. Ask a question below.</div>
        @endif

        <form method="POST" action="{{ route('account.order.messages.store', $order->id) }}">
          @csrf
          <input type="hidden" name="sub_order_id" value="{{ $sub->id }}">
          <textarea name="message" rows="3" placeholder="Message {{ $sub->vendor_shop_name ?: 'Vendor' }}..." style="width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box"></textarea>
          <div style="margin-top:8px">
            <button type="submit" class="btn btn-dark" style="font-size:12px;padding:8px 16px">Send Message</button>
          </div>
        </form>
      </div>

      {{-- Refund / Return CTA per sub-order --}}
      @if(in_array($sub->status, ['completed','shipped','processing']))
        <div style="padding:12px 18px;background:#fff;border-top:1px solid #e5e7eb">
          <a href="{{ route('account.refunds.create', ['order_id' => $order->id]) }}" style="font-size:12px;color:#e85d26;text-decoration:none;font-weight:600">⚠ Request Refund / Return for this shipment →</a>
        </div>
      @endif

    </div>{{-- end sub-order card --}}
  @endforeach

@else
  {{-- LEGACY: no sub-orders, show all items as before --}}
  <div class="order-detail-card" style="margin-top:16px">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Items</h3>
    @foreach($lineItems as $item)
    <div class="order-item-row">
      <div class="order-item-info">
        <a href="{{ route('product', $item['product_id']) }}" class="order-item-name">{{ $item['name'] }}</a>
        @if(!empty($item['attributes']))
          <span class="order-item-attr">@foreach($item['attributes'] as $k => $v){{ $k }}: {{ $v }} @endforeach</span>
        @endif
      </div>
      <span class="order-item-qty">× {{ $item['quantity'] }}</span>
      <span class="order-item-price">{{ number_format($item['subtotal'], 2) }} EGP</span>
    </div>
    @endforeach
    <div class="ck-totals" style="margin-top:16px">
      <div class="summary-row"><span>Subtotal</span><span>{{ number_format($order->original_total, 2) }} EGP</span></div>
      @if($order->discount_total > 0)
        <div class="summary-row discount-row"><span>Discount</span><span>−{{ number_format($order->discount_total, 2) }} EGP</span></div>
      @endif
      <div class="summary-divider"></div>
      <div class="summary-row total-row"><span>Total</span><span>{{ number_format($order->final_total, 2) }} EGP</span></div>
    </div>
  </div>
@endif

<div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
  <a href="{{ route('account.orders') }}" class="btn btn-outline">← Back to Orders</a>
</div>
@endsection

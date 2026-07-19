@extends('admin.layout')
@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)

@section('topbar-actions')
  <a href="{{ route('admin.orders') }}" class="btn btn-ghost btn-sm">← Back to Orders</a>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

  {{-- Left column --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Status & quick update --}}
    <div class="card">
      <div class="card-title">Order Status</div>
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        @php
          $sc = match($order->status) {
            'completed'                       => 'badge-green',
            'pending'                         => 'badge-yellow',
            'processing'                      => 'badge-blue',
            'shipped'                         => 'badge-purple',
            'cancelled', 'failed'             => 'badge-red',
            'refunded', 'on-hold'             => 'badge-gray',
            default                           => 'badge-gray',
          };
        @endphp
        <span class="badge {{ $sc }}" style="font-size:14px;padding:6px 14px">{{ ucfirst($order->status) }}</span>

        <form method="POST" action="{{ route('admin.orders.status', $order->id) }}" style="display:flex;gap:8px;align-items:center">
          @csrf @method('PATCH')
          <select name="status">
            @foreach(['pending','processing','shipped','completed','cancelled','refunded','failed'] as $s)
              <option value="{{ $s }}" {{ $order->status==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
          <button class="btn btn-primary">Update Status</button>
        </form>
      </div>
    </div>

    {{-- Line items --}}
    <div class="card">
      <div class="card-title">Items Ordered</div>
      @if(!empty($lineItems))
        <div class="table-wrap" style="border:none">
          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th>Variation</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
            @foreach($lineItems as $item)
              @php
                $attrs = $item['attributes'] ?? $item['attrs'] ?? [];
                $attrStr = is_array($attrs)
                  ? collect($attrs)->map(fn($v,$k) => "$k: $v")->implode(' · ')
                  : '';
                $qty = $item['quantity'] ?? $item['qty'] ?? 1;
                $itemTotal = $item['subtotal'] ?? (($item['price'] ?? 0) * $qty);
              @endphp
              <tr>
                <td>
                  <div style="font-weight:600">{{ $item['name'] ?? 'Unknown' }}</div>
                  @if(!empty($item['sku']))
                    <div style="font-size:11px;color:var(--muted)">SKU: {{ $item['sku'] }}</div>
                  @endif
                  @if($item['variation_id'] ?? null)
                    <div style="font-size:11px;color:var(--muted)">Var #{{ $item['variation_id'] }}</div>
                  @endif
                </td>
                <td>
                  @if($attrStr)
                    <div style="font-size:12px;color:var(--muted);line-height:1.6">
                      @foreach(is_array($attrs) ? $attrs : [] as $k => $v)
                        <span style="display:inline-block;background:rgba(255,255,255,.07);border-radius:4px;padding:1px 6px;margin:1px;font-size:11px">
                          <strong>{{ $k }}</strong>: {{ $v }}
                        </span>
                      @endforeach
                    </div>
                  @else
                    <span style="color:var(--muted);font-size:12px">—</span>
                  @endif
                </td>
                <td>{{ $qty }}</td>
                <td>{{ $order->currency_symbol }}{{ number_format($item['price'] ?? 0, 2) }}</td>
                <td style="font-weight:600">{{ $order->currency_symbol }}{{ number_format($itemTotal, 2) }}</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p style="color:var(--muted);font-size:13px">No line items recorded.</p>
      @endif
    </div>

    {{-- Sub-orders --}}
    @if(isset($subOrders) && $subOrders->count() > 0)
    <div class="card">
      <div class="card-title">Vendor Sub-Orders ({{ $subOrders->count() }})</div>
      @foreach($subOrders as $sub)
        @php
          $subItems = json_decode($sub->line_items ?? '[]', true) ?: [];
          $subSc = match($sub->status) {
            'completed'           => 'badge-green',
            'pending'             => 'badge-yellow',
            'processing'          => 'badge-blue',
            'shipped'             => 'badge-purple',
            'cancelled', 'failed' => 'badge-red',
            default               => 'badge-gray',
          };
        @endphp
        <div style="border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:14px;margin-bottom:12px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px">
            <div>
              <span style="font-weight:700;font-size:14px">{{ $sub->vendor_shop_name ?: 'No Store Name' }}</span>
              <span style="font-size:11px;color:var(--muted);margin-left:8px">Sub-order #{{ $sub->id }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
              <span class="badge {{ $subSc }}" style="font-size:12px">{{ ucfirst($sub->status) }}</span>
            </div>
          </div>
          @foreach($subItems as $si)
            <div style="display:flex;justify-content:space-between;font-size:12px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.05)">
              <span style="color:var(--muted)">{{ $si['name'] }} × {{ $si['quantity'] }}</span>
              <span style="font-weight:600">{{ number_format($si['subtotal'],2) }} EGP</span>
            </div>
          @endforeach
          <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;margin-top:8px;color:var(--accent)">
            <span>Vendor Total</span><span>{{ number_format($sub->total, 2) }} EGP</span>
          </div>
          @if($sub->tracking_number)
            <div style="margin-top:6px;font-size:12px;color:var(--muted)">
              Tracking: <span style="font-family:monospace;color:var(--accent)">{{ $sub->tracking_number }}</span>
              @if($sub->tracking_carrier) via {{ $sub->tracking_carrier }} @endif
            </div>
          @endif
        </div>
      @endforeach
    </div>
    @endif

    {{-- Timeline --}}
    @if(!empty($timeline))
    <div class="card">
      <div class="card-title">Order Timeline</div>
      @foreach($timeline as $event)
        <div style="display:flex;gap:12px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
          <div style="width:8px;height:8px;border-radius:50%;background:var(--accent);margin-top:5px;flex-shrink:0"></div>
          <div>
            <div style="font-size:13px;font-weight:500">{{ $event['message'] ?? $event['note'] ?? json_encode($event) }}</div>
            @if(isset($event['date_created']))
              <div style="font-size:11px;color:var(--muted)">{{ $event['date_created'] }}</div>
            @endif
          </div>
        </div>
      @endforeach
    </div>
    @endif

  </div>

  {{-- Right column --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Order summary --}}
    <div class="card">
      <div class="card-title">Summary</div>
      <div class="detail-row"><div class="detail-label">Order ID</div><div class="detail-value">#{{ $order->id }}</div></div>
      <div class="detail-row"><div class="detail-label">Date</div><div class="detail-value">{{ $order->date_created ? \Carbon\Carbon::parse($order->date_created)->format('d M Y, H:i') : '—' }}</div></div>
      <div class="detail-row"><div class="detail-label">Payment</div><div class="detail-value">{{ $order->payment_method_title ?? '—' }}</div></div>
      <div class="detail-row"><div class="detail-label">Discount</div><div class="detail-value">{{ $order->currency_symbol }}{{ number_format($order->discount_total, 2) }}</div></div>
      <div class="detail-row"><div class="detail-label">Shipping</div><div class="detail-value">{{ $order->currency_symbol }}{{ number_format($order->shipping_total, 2) }}</div></div>
      <div class="detail-row" style="border-bottom:none">
        <div class="detail-label">Total</div>
        <div class="detail-value" style="font-size:20px;font-weight:800;color:var(--accent)">{{ $order->currency_symbol }}{{ number_format($order->final_total, 2) }}</div>
      </div>
    </div>

    {{-- Customer --}}
    <div class="card">
      <div class="card-title">Customer</div>
      @if($customer)
        <div class="detail-row"><div class="detail-label">Name</div><div class="detail-value">{{ $customer->name }}</div></div>
        <div class="detail-row"><div class="detail-label">Email</div><div class="detail-value">{{ $customer->email }}</div></div>
        <div class="detail-row" style="border-bottom:none"><div class="detail-label">Phone</div><div class="detail-value">{{ $customer->phone ?? '—' }}</div></div>
      @else
        <p style="color:var(--muted);font-size:13px">Customer #{{ $order->customer_id }} (not found)</p>
      @endif
    </div>

    {{-- Billing --}}
    @if(!empty($billing))
    <div class="card">
      <div class="card-title">Billing Address</div>
      <div style="font-size:13px;line-height:1.8;color:var(--muted)">
        {{ $billing['first_name'] ?? '' }} {{ $billing['last_name'] ?? '' }}<br>
        @if(!empty($billing['address_1'])) {{ $billing['address_1'] }}<br>@endif
        @if(!empty($billing['city'])) {{ $billing['city'] }}@endif
        @if(!empty($billing['postcode'])) , {{ $billing['postcode'] }}@endif
        @if(!empty($billing['country'])) &nbsp;{{ $billing['country'] }}@endif
        @if(!empty($billing['phone'])) <br>{{ $billing['phone'] }}@endif
      </div>
    </div>
    @endif

    {{-- Shipping --}}
    @if(!empty($shipping) && array_filter($shipping))
    <div class="card">
      <div class="card-title">Shipping Address</div>
      <div style="font-size:13px;line-height:1.8;color:var(--muted)">
        {{ $shipping['first_name'] ?? '' }} {{ $shipping['last_name'] ?? '' }}<br>
        @if(!empty($shipping['address_1'])) {{ $shipping['address_1'] }}<br>@endif
        @if(!empty($shipping['city'])) {{ $shipping['city'] }}@endif
        @if(!empty($shipping['country'])) &nbsp;{{ $shipping['country'] }}@endif
      </div>
      @if(!empty($shipping['latitude']) && !empty($shipping['longitude']))
        <div style="margin-top:8px">
          <a href="https://www.google.com/maps?q={{ $shipping['latitude'] }},{{ $shipping['longitude'] }}"
             target="_blank" rel="noopener"
             style="font-size:12px;color:var(--accent);text-decoration:none;display:inline-block;margin-bottom:6px">
            📍 {{ $shipping['latitude'] }}, {{ $shipping['longitude'] }} — Open in Google Maps
          </a>
        </div>
        <iframe
          width="100%"
          height="200"
          style="border:0;border-radius:10px;display:block"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q={{ $shipping['latitude'] }},{{ $shipping['longitude'] }}&z=15&output=embed">
        </iframe>
      @endif
    </div>
    @endif

  </div>
</div>

@endsection

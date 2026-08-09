@extends('admin.layout')
@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)

@section('content')

<div style="display:flex;gap:12px;margin-bottom:20px">
  <a href="{{ route('admin.orders') }}" class="btn btn-secondary">← Back to Orders</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Order Details --}}
    <div class="section">
      <div class="section-header">
        <div class="section-title">Order Details</div>
        @php
          $sc = match($order->status) {
            'completed' => 'badge-green', 'processing' => 'badge-blue',
            'shipped' => 'badge-purple', 'partially_shipped' => 'badge-orange',
            'partially_delivered' => 'badge-blue', 'partially_cancelled' => 'badge-red',
            'cancelled','refunded' => 'badge-red', default => 'badge-yellow'
          };
        @endphp
        <span class="badge {{ $sc }}" style="font-size:13px;padding:5px 14px">{{ app(\App\Services\OrderStatusService::class)->label($order->status) }}</span>
      </div>
      <div style="padding:20px">
        <div class="detail-grid">
          <div class="detail-item">
            <div class="label">Order ID</div>
            <div class="val">#{{ $order->id }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Order Key</div>
            <div class="val" style="font-size:12px;color:var(--muted)">{{ $order->order_key ?: '—' }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Payment Method</div>
            <div class="val">{{ $order->payment_method_title ?: '—' }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Currency</div>
            <div class="val">{{ $order->currency }} {{ $order->currency_symbol }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Subtotal</div>
            <div class="val">{{ number_format($order->original_total, 2) }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Discount</div>
            <div class="val">{{ number_format($order->discount_total, 2) }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Shipping</div>
            <div class="val">{{ number_format($order->shipping_total, 2) }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Final Total</div>
            <div class="val" style="font-size:18px;font-weight:800;color:var(--accent)">{{ number_format($order->final_total, 2) }} {{ $order->currency_symbol }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Date Created</div>
            <div class="val">{{ $order->date_created ? date('M d, Y H:i', strtotime($order->date_created)) : '—' }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Date Paid</div>
            <div class="val">{{ $order->date_paid ? date('M d, Y H:i', strtotime($order->date_paid)) : 'Not paid' }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Customer Note</div>
            <div class="val">{{ $order->customer_note ?: '—' }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Location</div>
            <div class="val">
              @php $bill = json_decode($order->billing, true) ?? []; @endphp
              @if(!empty($bill['latitude']) && !empty($bill['longitude']))
                <a href="https://www.google.com/maps?q={{ $bill['latitude'] }},{{ $bill['longitude'] }}" target="_blank" rel="noopener">{{ $bill['latitude'] }}, {{ $bill['longitude'] }}</a>
              @else
                —
              @endif
            </div>
          </div>
          <div class="detail-item">
            <div class="label">Coupon</div>
            <div class="val">{{ $order->coupon_code ?: '—' }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Line Items --}}
    @if($order->line_items)
    <div class="section">
      <div class="section-header"><div class="section-title">Line Items</div></div>
      <div style="padding:16px 20px">
        @php $items = json_decode($order->line_items, true) ?? []; @endphp
        @if(count($items))
          <div class="table-wrap">
            <table>
              <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
              <tbody>
                @foreach($items as $item)
                <tr>
                  <td style="font-weight:600">{{ $item['name'] ?? '—' }}</td>
                  <td>{{ $item['quantity'] ?? $item['qty'] ?? '—' }}</td>
                  <td>{{ isset($item['price']) ? number_format($item['price'], 2) : '—' }}</td>
                  <td>{{ isset($item['total']) ? number_format($item['total'], 2) : '—' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <p style="color:var(--muted)">No line items data.</p>
        @endif
      </div>
    </div>
    @endif

  </div>

  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Update Status --}}
    <div class="section">
      <div class="section-header"><div class="section-title">Update Status</div></div>
      <div style="padding:20px">
        <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
          @csrf @method('PUT')
          <div class="form-group">
            <label class="form-label">New Status</label>
            <select name="status" class="form-control">
              <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
              <option value="on-hold" {{ $order->status === 'on-hold' ? 'selected' : '' }}>On Hold</option>
              <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
              <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
              <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
              <option value="failed" {{ $order->status === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%">Update Status</button>
        </form>
      </div>
    </div>

    {{-- Customer Info --}}
    <div class="section">
      <div class="section-header"><div class="section-title">Customer</div></div>
      <div style="padding:20px">
        @if($customer)
          <div class="detail-item" style="margin-bottom:12px">
            <div class="label">Name</div>
            <div class="val">{{ $customer->name }}</div>
          </div>
          <div class="detail-item" style="margin-bottom:12px">
            <div class="label">Email</div>
            <div class="val" style="font-size:13px">{{ $customer->email }}</div>
          </div>
          <div class="detail-item">
            <div class="label">Phone</div>
            <div class="val">{{ $customer->phone ?: '—' }}</div>
          </div>
          @if($customer->is_blocked)
            <div style="margin-top:12px">
              <span class="badge badge-red">User is blocked</span>
            </div>
          @endif
        @else
          <p style="color:var(--muted)">Customer ID: {{ $order->customer_id ?: 'Guest' }}</p>
        @endif
      </div>
    </div>

    {{-- Billing Address --}}
    @if($order->billing)
    <div class="section">
      <div class="section-header"><div class="section-title">Billing Address</div></div>
      <div style="padding:20px">
        @php $billing = json_decode($order->billing, true) ?? []; @endphp
        @foreach(['first_name','last_name','address_1','address_2','city','state','postcode','country','phone','email'] as $field)
          @if(!empty($billing[$field]))
            <div style="font-size:13px;color:var(--text);margin-bottom:4px">{{ $billing[$field] }}</div>
          @endif
        @endforeach
        @if(!empty($billing['latitude']) && !empty($billing['longitude']))
          <div style="margin-top:10px">
            <iframe
              width="100%"
              height="220"
              style="border:0;border-radius:12px"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              src="https://www.google.com/maps?q={{ $billing['latitude'] }},{{ $billing['longitude'] }}&z=15&output=embed">
            </iframe>
          </div>
        @endif
      </div>
    </div>
    @endif

    {{-- Shipping Address --}}
    @if($order->shipping)
    @php $shipping = json_decode($order->shipping, true) ?? []; @endphp
    @if(array_filter(array_intersect_key($shipping, array_flip(['first_name','last_name','address_1','city','state','country','latitude','longitude']))))
    <div class="section">
      <div class="section-header"><div class="section-title">Shipping Address</div></div>
      <div style="padding:20px">
        @foreach(['first_name','last_name','address_1','address_2','city','state','postcode','country','phone','email'] as $field)
          @if(!empty($shipping[$field]))
            <div style="font-size:13px;color:var(--text);margin-bottom:4px">{{ $shipping[$field] }}</div>
          @endif
        @endforeach
        @if(!empty($shipping['latitude']) && !empty($shipping['longitude']))
          <div style="margin-top:6px;margin-bottom:8px">
            <a href="https://www.google.com/maps?q={{ $shipping['latitude'] }},{{ $shipping['longitude'] }}"
               target="_blank" rel="noopener"
               style="font-size:12px;color:var(--accent);text-decoration:none">
              📍 {{ $shipping['latitude'] }}, {{ $shipping['longitude'] }} — Open in Google Maps
            </a>
          </div>
          <div style="margin-top:4px">
            <iframe
              width="100%"
              height="220"
              style="border:0;border-radius:12px"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              src="https://www.google.com/maps?q={{ $shipping['latitude'] }},{{ $shipping['longitude'] }}&z=15&output=embed">
            </iframe>
          </div>
        @endif
      </div>
    </div>
    @endif
    @endif

  </div>

</div>

@endsection

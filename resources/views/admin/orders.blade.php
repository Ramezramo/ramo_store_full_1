@extends('admin.layout')
@section('title', 'Orders')
@section('page-title', 'Orders Management')

@section('content')

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="{{ $search }}" placeholder="Order ID, customer ID…" style="width:200px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All Statuses</option>
      @foreach($statuses as $s)
        <option value="{{ $s }}" {{ $status==$s?'selected':'' }}>{{ app(\App\Services\OrderStatusService::class)->label($s) }}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  @if($search || $status)
    <div class="form-group" style="justify-content:flex-end">
      <a href="{{ route('admin.orders') }}" class="btn btn-ghost">Clear</a>
    </div>
  @endif
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px">{{ $orders->total() }} order(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Order</th>
        <th>Customer ID</th>
        <th>Status</th>
        <th>Payment</th>
        <th>Total</th>
        <th>Date</th>
        <th>Update Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    @forelse($orders as $order)
          @php
            $displayStatus = $order->general_order_status ?? $order->status ?? 'pending';
            $displayLabel = match($displayStatus) {
              'partially_shipped' => 'Partially Shipped',
              'partially_delivered' => 'Partially Delivered',
              'partially_cancelled' => 'Partially Cancelled',
              default => ucfirst($displayStatus),
            };
            $sc = match($displayStatus) {
              'completed'           => 'badge-green',
          'pending'             => 'badge-yellow',
          'processing'          => 'badge-blue',
              'shipped'             => 'badge-purple',
              'partially_shipped'   => 'badge-orange',
              'partially_delivered'  => 'badge-blue',
              'cancelled', 'partially_cancelled' => 'badge-red',
          default               => 'badge-gray',
        };
      @endphp
      <tr>
        <td style="font-weight:700">#{{ $order->id }}</td>
        <td style="color:var(--muted)">{{ $order->customer_id ?? '—' }}</td>
        <td><span class="badge {{ $sc }}">{{ $displayLabel }}</span></td>
        <td style="color:var(--muted);font-size:12px">{{ $order->payment_method_title ?? '—' }}</td>
        <td style="font-weight:600">{{ $order->currency_symbol }}{{ number_format($order->final_total, 2) }}</td>
        <td style="color:var(--muted);font-size:12px;white-space:nowrap">{{ $order->date_created ? \Carbon\Carbon::parse($order->date_created)->format('d M Y') : '—' }}</td>
        <td style="color:var(--muted);font-size:12px">Computed from payment &amp; shipments</td>
        <td><a href="{{ route('admin.orders.detail', $order->id) }}" class="btn btn-ghost btn-sm">View</a></td>
      </tr>
    @empty
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No orders found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="pagination">{{ $orders->links('admin.pagination') }}</div>

@endsection

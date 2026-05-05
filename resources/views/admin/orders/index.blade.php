@extends('admin.layout')
@section('title', 'Orders')
@section('page-title', 'Orders Management')

@section('content')

<div class="section">
  <div class="section-header">
    <div class="section-title">All Orders</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      @foreach($statuses as $s)
        @php
          $sc = match($s->status) {
            'completed' => 'badge-green', 'processing' => 'badge-blue',
            'shipped' => 'badge-purple',
            'cancelled','refunded' => 'badge-red', default => 'badge-yellow'
          };
        @endphp
        <span class="badge {{ $sc }}">{{ $s->status }}: {{ $s->cnt }}</span>
      @endforeach
    </div>
  </div>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search order ID, customer…" class="search-input">
    <select name="status" class="filter-select">
      <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
      <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
      <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>Processing</option>
      <option value="on-hold" {{ $status === 'on-hold' ? 'selected' : '' }}>On Hold</option>
      <option value="shipped" {{ $status === 'shipped' ? 'selected' : '' }}>Shipped</option>
      <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
      <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
      <option value="refunded" {{ $status === 'refunded' ? 'selected' : '' }}>Refunded</option>
    </select>
    <button type="submit" class="btn-filter">Filter</button>
    @if($search || $status !== 'all')
      <a href="{{ route('admin.orders') }}" class="btn btn-secondary btn-sm">Clear</a>
    @endif
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Status</th>
          <th>Payment</th>
          <th>Total</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
        @php
          $sc = match($order->status) {
            'completed' => 'badge-green', 'processing' => 'badge-blue',
            'shipped' => 'badge-purple',
            'cancelled','refunded' => 'badge-red', default => 'badge-yellow'
          };
        @endphp
        <tr>
          <td><a href="{{ route('admin.orders.show', $order->id) }}" style="color:var(--accent);font-weight:700">#{{ $order->id }}</a></td>
          <td style="color:var(--muted)">{{ $order->customer_id ? 'User #'.$order->customer_id : 'Guest' }}</td>
          <td><span class="badge {{ $sc }}">{{ $order->status }}</span></td>
          <td style="color:var(--muted)">{{ $order->payment_method_title ?: '—' }}</td>
          <td style="font-weight:600">{{ number_format($order->final_total, 2) }} {{ $order->currency_symbol }}</td>
          <td style="color:var(--muted)">{{ $order->date_created ? date('M d, Y', strtotime($order->date_created)) : '—' }}</td>
          <td>
            <div style="display:flex;gap:6px;align-items:center">
              <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary btn-sm">View</a>
              <form method="POST" action="{{ route('admin.orders.status', $order->id) }}" style="display:flex;gap:4px">
                @csrf @method('PUT')
                <select name="status" class="filter-select" style="padding:4px 8px;font-size:11px">
                  <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                  <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                  <option value="on-hold" {{ $order->status === 'on-hold' ? 'selected' : '' }}>On Hold</option>
                  <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                  <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                  <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                  <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:32px">No orders found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">{{ $orders->links() }}</div>
</div>

@endsection

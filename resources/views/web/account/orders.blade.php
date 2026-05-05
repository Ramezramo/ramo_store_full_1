@extends('web.account.layout')
@php $pageTitle = 'My Orders'; @endphp

@section('account-content')
<div class="acc-section-title">Order History</div>

@if($orders->count())
<div class="orders-table-wrap">
  <table class="orders-table">
    <thead>
      <tr>
        <th>Order #</th><th>Date</th><th>Status</th><th>Payment</th><th>Total</th><th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($orders as $order)
      <tr>
        <td><strong>#{{ $order->id }}</strong></td>
        <td>{{ \Carbon\Carbon::parse($order->date_created)->format('M d, Y') }}</td>
        <td><span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
        <td>{{ $order->payment_method_title }}</td>
        <td><strong>{{ number_format($order->final_total, 2) }} EGP</strong></td>
        <td><a href="{{ route('account.order', $order->id) }}" class="btn btn-outline" style="font-size:12px;padding:6px 14px">View</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @if($orders->hasPages())
  <div class="pagination-wrap" style="margin:20px 18px">
    @if($orders->onFirstPage())<span>‹</span>@else<a href="{{ $orders->previousPageUrl() }}">‹</a>@endif
    @foreach($orders->getUrlRange(max(1,$orders->currentPage()-2), min($orders->lastPage(),$orders->currentPage()+2)) as $page => $url)
      @if($page == $orders->currentPage())<span class="active-page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
    @endforeach
    @if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}">›</a>@else<span>›</span>@endif
  </div>
  @endif
</div>
@else
  <div class="empty">
    <div class="empty-icon">📦</div>
    <h3>No orders yet</h3>
    <p>When you place an order it will appear here.</p>
    <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:20px">Shop Now</a>
  </div>
@endif
@endsection

@extends('web.account.layout')
@php $pageTitle = 'My Orders'; @endphp

@section('account-content')
<style>
  .orders-mobile-list { display: none; }

  @media (max-width: 600px) {
    .orders-table-wrap { display: none; }
    .orders-mobile-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .order-mobile-card {
      background: var(--c-white);
      border: 1.5px solid var(--c-light);
      border-radius: 14px;
      padding: 15px;
    }
    .order-mobile-card-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 10px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--c-light);
    }
    .order-mobile-card-header .status-badge {
      flex-shrink: 0;
      max-width: 48%;
      text-align: center;
    }
    .order-mobile-number {
      font-size: 15px;
      font-weight: 800;
      line-height: 1.3;
    }
    .order-mobile-date {
      color: var(--c-mid);
      font-size: 11.5px;
      margin-top: 3px;
    }
    .order-mobile-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 13px 12px;
      padding: 14px 0;
    }
    .order-mobile-label {
      display: block;
      color: var(--c-mid);
      font-size: 10.5px;
      font-weight: 700;
      letter-spacing: .35px;
      margin-bottom: 2px;
      text-transform: uppercase;
    }
    .order-mobile-value {
      display: block;
      font-size: 13px;
      line-height: 1.4;
      overflow-wrap: anywhere;
    }
    .order-mobile-total {
      font-weight: 800;
    }
    .order-mobile-action {
      border-top: 1px solid var(--c-light);
      padding-top: 12px;
    }
    .order-mobile-action .btn {
      justify-content: center;
      width: 100%;
      padding: 10px 14px;
      font-size: 12.5px;
    }
    .orders-mobile-pagination {
      display: flex !important;
      margin: 4px 0 0;
    }
  }
</style>

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
        <td><span class="status-badge status-{{ $order->status }}">{{ app(\App\Services\OrderStatusService::class)->label($order->status) }}</span></td>
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

<div class="orders-mobile-list">
  @foreach($orders as $order)
    <article class="order-mobile-card">
      <div class="order-mobile-card-header">
        <div>
          <div class="order-mobile-number">Order #{{ $order->id }}</div>
          <div class="order-mobile-date">{{ \Carbon\Carbon::parse($order->date_created)->format('M d, Y') }}</div>
        </div>
        <span class="status-badge status-{{ $order->status }}">{{ app(\App\Services\OrderStatusService::class)->label($order->status) }}</span>
      </div>

      <div class="order-mobile-details">
        <div>
          <span class="order-mobile-label">Payment</span>
          <span class="order-mobile-value">{{ $order->payment_method_title }}</span>
        </div>
        <div>
          <span class="order-mobile-label">Total</span>
          <span class="order-mobile-value order-mobile-total">{{ number_format($order->final_total, 2) }} EGP</span>
        </div>
      </div>

      <div class="order-mobile-action">
        <a href="{{ route('account.order', $order->id) }}" class="btn btn-outline">View order</a>
      </div>
    </article>
  @endforeach
</div>

@if($orders->hasPages())
  <div class="pagination-wrap orders-mobile-pagination" style="display:none">
    @if($orders->onFirstPage())<span>‹</span>@else<a href="{{ $orders->previousPageUrl() }}">‹</a>@endif
    @foreach($orders->getUrlRange(max(1,$orders->currentPage()-2), min($orders->lastPage(),$orders->currentPage()+2)) as $page => $url)
      @if($page == $orders->currentPage())<span class="active-page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
    @endforeach
    @if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}">›</a>@else<span>›</span>@endif
  </div>
@endif
@else
  <div class="empty">
    <div class="empty-icon">📦</div>
    <h3>No orders yet</h3>
    <p>When you place an order it will appear here.</p>
    <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:20px">Shop Now</a>
  </div>
@endif
@endsection

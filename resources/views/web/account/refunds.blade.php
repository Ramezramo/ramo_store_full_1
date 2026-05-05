@extends('web.account.layout')
@php $pageTitle = 'My Refund Requests'; @endphp

@section('account-content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div class="acc-section-title" style="margin-bottom:0">Refund & Return Requests</div>
  <a href="{{ route('account.refunds.create') }}" class="btn btn-dark" style="font-size:13px;padding:9px 18px">+ New Request</a>
</div>

@if($refunds->count())
<div class="orders-table-wrap">
  <table class="orders-table">
    <thead>
      <tr>
        <th>Req #</th>
        <th>Order #</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Date</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($refunds as $r)
      @php
        $sc = match($r->status) {
          'approved'  => 'status-completed',
          'rejected'  => 'status-cancelled',
          'completed' => 'status-completed',
          'cancelled' => 'status-cancelled',
          default     => 'status-processing',
        };
        $reasonLabel = match($r->reason) {
          'damaged'          => 'Item Damaged',
          'wrong_item'       => 'Wrong Item',
          'changed_mind'     => 'Changed Mind',
          'not_as_described' => 'Not as Described',
          default            => 'Other',
        };
      @endphp
      <tr>
        <td><strong>#{{ $r->id }}</strong></td>
        <td><a href="{{ route('account.order', $r->order_id) }}" style="color:inherit">#{{ $r->order_id }}</a></td>
        <td><span style="font-weight:600;text-transform:capitalize">{{ $r->type }}</span></td>
        <td style="color:#666;font-size:13px">{{ $reasonLabel }}</td>
        <td><span class="status-badge {{ $sc }}">{{ ucfirst($r->status) }}</span></td>
        <td style="font-size:12px;color:#888">{{ \Carbon\Carbon::parse($r->created_at)->format('M d, Y') }}</td>
        <td>
          <a href="{{ route('account.refunds.show', $r->id) }}" class="btn btn-outline" style="font-size:12px;padding:5px 12px">View</a>
          @if($r->status === 'pending')
            <form method="POST" action="{{ route('account.refunds.cancel', $r->id) }}" style="display:inline" onsubmit="return confirm('Cancel this request?')">
              @csrf @method('PATCH')
              <button class="btn btn-outline" style="font-size:12px;padding:5px 12px;margin-left:4px;color:#dc2626;border-color:#dc2626">Cancel</button>
            </form>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @if($refunds->hasPages())
  <div class="pagination-wrap" style="margin:20px 18px">
    @if($refunds->onFirstPage())<span>‹</span>@else<a href="{{ $refunds->previousPageUrl() }}">‹</a>@endif
    @foreach($refunds->getUrlRange(max(1,$refunds->currentPage()-2), min($refunds->lastPage(),$refunds->currentPage()+2)) as $page => $url)
      @if($page == $refunds->currentPage())<span class="active-page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
    @endforeach
    @if($refunds->hasMorePages())<a href="{{ $refunds->nextPageUrl() }}">›</a>@else<span>›</span>@endif
  </div>
  @endif
</div>
@else
  <div class="empty">
    <div class="empty-icon">🔄</div>
    <h3>No requests yet</h3>
    <p>Submit a refund or return request for any eligible order.</p>
    <a href="{{ route('account.refunds.create') }}" class="btn btn-dark" style="margin-top:20px">Request Refund / Return</a>
  </div>
@endif
@endsection

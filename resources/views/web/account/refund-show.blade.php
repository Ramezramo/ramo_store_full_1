@extends('web.account.layout')
@php $pageTitle = 'Request #'.$refund->id; @endphp

@section('account-content')
<div style="margin-bottom:20px">
  <a href="{{ route('account.refunds') }}" style="font-size:13px;color:#888;text-decoration:none">← Back to Requests</a>
</div>
<div class="acc-section-title" style="margin-bottom:20px">Request #{{ $refund->id }}</div>

@php
  $sc = match($refund->status) {
    'approved'  => 'status-completed',
    'rejected'  => 'status-cancelled',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled',
    default     => 'status-processing',
  };
  $reasonLabel = match($refund->reason) {
    'damaged'          => 'Item Damaged',
    'wrong_item'       => 'Wrong Item',
    'changed_mind'     => 'Changed Mind',
    'not_as_described' => 'Not as Described',
    default            => 'Other',
  };
@endphp

<div class="order-detail-card">
  <div class="od-row"><span class="od-label">Request #</span><strong>#{{ $refund->id }}</strong></div>
  <div class="od-row"><span class="od-label">Order</span><a href="{{ route('account.order', $refund->order_id) }}" style="color:inherit;font-weight:600">#{{ $refund->order_id }}</a></div>
  <div class="od-row"><span class="od-label">Order Total</span><span>{{ number_format($refund->order_total, 2) }} EGP</span></div>
  <div class="od-row"><span class="od-label">Type</span><span style="font-weight:600;text-transform:capitalize">{{ $refund->type }}</span></div>
  <div class="od-row"><span class="od-label">Reason</span><span>{{ $reasonLabel }}</span></div>
  <div class="od-row"><span class="od-label">Status</span><span class="status-badge {{ $sc }}">{{ ucfirst($refund->status) }}</span></div>
  <div class="od-row"><span class="od-label">Submitted</span><span>{{ \Carbon\Carbon::parse($refund->created_at)->format('M d, Y h:i A') }}</span></div>
</div>

@if($refund->description)
<div class="order-detail-card" style="margin-top:16px">
  <h3 style="font-size:14px;font-weight:700;margin-bottom:10px">Your Description</h3>
  <p style="font-size:14px;line-height:1.7;color:#444">{{ $refund->description }}</p>
</div>
@endif

@if($refund->admin_note)
<div class="order-detail-card" style="margin-top:16px;border-left:3px solid #e85d26">
  <h3 style="font-size:14px;font-weight:700;margin-bottom:10px">Response from Support</h3>
  <p style="font-size:14px;line-height:1.7;color:#444">{{ $refund->admin_note }}</p>
</div>
@endif

@if($refund->status === 'pending')
<div style="margin-top:20px">
  <form method="POST" action="{{ route('account.refunds.cancel', $refund->id) }}" onsubmit="return confirm('Cancel this request?')">
    @csrf @method('PATCH')
    <button class="btn btn-outline" style="color:#dc2626;border-color:#dc2626">Cancel Request</button>
  </form>
</div>
@endif
@endsection

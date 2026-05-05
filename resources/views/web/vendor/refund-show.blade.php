@extends('web.vendor.layout')
@section('title','Refund Request #'.$refund->id)
@section('page-title','Refund Request #'.$refund->id)

@section('content')

<div style="margin-bottom:16px">
  <a href="{{ route('vendor.refunds') }}" style="font-size:13px;color:var(--mid)">← Back to Requests</a>
</div>

@php
  $badgeClass = match($refund->status) {
    'approved','completed' => 'badge-approved',
    'rejected','cancelled' => 'badge-blocked',
    default => 'badge-pending',
  };
  $reasonLabel = match($refund->reason) {
    'damaged'          => 'Item Arrived Damaged',
    'wrong_item'       => 'Wrong Item Received',
    'changed_mind'     => 'Changed Mind',
    'not_as_described' => 'Not as Described',
    default            => 'Other',
  };
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
  <div style="background:#fff;border:1px solid var(--light);border-radius:10px;padding:20px">
    <div style="font-size:12px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:14px">Request Details</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--mid);padding:5px 0;width:40%">Request #</td><td style="font-weight:600">#{{ $refund->id }}</td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Order</td><td><a href="{{ route('vendor.orders.show', $refund->order_id) }}" style="color:var(--orange);font-weight:600">#{{ $refund->order_id }}</a></td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Order Total</td><td style="font-weight:700">{{ number_format($refund->order_total, 2) }} EGP</td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Type</td><td style="font-weight:600;text-transform:capitalize">{{ $refund->type }}</td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Reason</td><td>{{ $reasonLabel }}</td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Status</td><td><span class="{{ $badgeClass }}">{{ ucfirst($refund->status) }}</span></td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Submitted</td><td style="font-size:12px">{{ \Carbon\Carbon::parse($refund->created_at)->format('M d, Y H:i') }}</td></tr>
    </table>
  </div>
  <div style="background:#fff;border:1px solid var(--light);border-radius:10px;padding:20px">
    <div style="font-size:12px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:14px">Customer</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--mid);padding:5px 0;width:40%">Name</td><td style="font-weight:600">{{ $refund->customer_name }}</td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Email</td><td>{{ $refund->customer_email }}</td></tr>
      <tr><td style="color:var(--mid);padding:5px 0">Phone</td><td>{{ $refund->customer_phone ?: '—' }}</td></tr>
    </table>
  </div>
</div>

@if($refund->description)
<div style="background:#fff;border:1px solid var(--light);border-radius:10px;padding:20px;margin-bottom:20px">
  <div style="font-size:12px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Customer Description</div>
  <p style="font-size:13px;line-height:1.7;color:#444">{{ $refund->description }}</p>
</div>
@endif

@if($refund->admin_note)
<div style="background:#fff;border:1px solid var(--light);border-left:3px solid var(--orange);border-radius:10px;padding:20px">
  <div style="font-size:12px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Admin Note</div>
  <p style="font-size:13px;line-height:1.7;color:#444">{{ $refund->admin_note }}</p>
</div>
@endif

@endsection

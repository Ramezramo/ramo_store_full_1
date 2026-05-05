@extends('admin.layout')
@section('title','Refund Request #'.$refund->id)
@section('page-title','Refund Request #'.$refund->id)

@section('content')

<div style="margin-bottom:16px">
  <a href="{{ route('admin.refunds') }}" style="color:var(--muted);font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Requests
  </a>
</div>

@php
  $sc = match($refund->status) {
    'approved','completed' => 'badge-green',
    'rejected','cancelled' => 'badge-red',
    default => 'badge-yellow',
  };
  $reasonLabel = match($refund->reason) {
    'damaged'          => 'Item Arrived Damaged',
    'wrong_item'       => 'Wrong Item Received',
    'changed_mind'     => 'Changed Mind',
    'not_as_described' => 'Not as Described',
    default            => 'Other',
  };
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  {{-- Request Info --}}
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Request Details</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Request #</td><td style="font-weight:600">#{{ $refund->id }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Order</td><td><a href="{{ route('admin.orders.detail', $refund->order_id) }}" style="color:var(--accent);font-weight:600">#{{ $refund->order_id }}</a></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Order Total</td><td style="font-weight:700">{{ number_format($refund->order_total, 2) }} EGP</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Type</td><td style="font-weight:600;text-transform:capitalize">{{ $refund->type }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Reason</td><td>{{ $reasonLabel }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Status</td><td><span class="badge {{ $sc }}">{{ ucfirst($refund->status) }}</span></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Vendor</td><td>{{ $refund->vendor_shop_name ?? '—' }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Submitted</td><td style="font-size:12px">{{ \Carbon\Carbon::parse($refund->created_at)->format('d M Y, H:i') }}</td></tr>
    </table>
  </div>

  {{-- Customer Info --}}
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Customer</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Name</td><td style="font-weight:600">{{ $refund->customer_name }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Email</td><td>{{ $refund->customer_email }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Phone</td><td>{{ $refund->customer_phone ?: '—' }}</td></tr>
    </table>
  </div>
</div>

@if($refund->description)
<div class="card" style="padding:20px;margin-bottom:24px">
  <div style="font-weight:600;font-size:14px;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--border)">Customer Description</div>
  <p style="font-size:13px;line-height:1.7;color:var(--text)">{{ $refund->description }}</p>
</div>
@endif

{{-- Admin Action --}}
@if(!in_array($refund->status, ['cancelled']))
<div class="card" style="padding:20px">
  <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Update Status</div>
  <form method="POST" action="{{ route('admin.refunds.update', $refund->id) }}">
    @csrf @method('PATCH')
    <div class="form-row" style="flex-wrap:wrap;align-items:flex-end">
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="pending"   {{ $refund->status=='pending'?'selected':'' }}>Pending</option>
          <option value="approved"  {{ $refund->status=='approved'?'selected':'' }}>Approved</option>
          <option value="rejected"  {{ $refund->status=='rejected'?'selected':'' }}>Rejected</option>
          <option value="completed" {{ $refund->status=='completed'?'selected':'' }}>Completed</option>
        </select>
      </div>
      <div class="form-group" style="flex:1;min-width:260px">
        <label>Note to Customer (optional)</label>
        <input type="text" name="admin_note" value="{{ $refund->admin_note ?? '' }}" placeholder="e.g. Refund processed to original payment method" style="width:100%">
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div>
  </form>
</div>
@endif

@endsection

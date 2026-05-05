@extends('web.vendor.layout')
@section('title','Refund Requests')
@section('page-title','Refund & Return Requests')

@section('content')

<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:flex-end">
  <div>
    <label style="display:block;font-size:11px;font-weight:700;color:var(--mid);margin-bottom:4px;text-transform:uppercase">Status</label>
    <select name="status" style="padding:8px 12px;border:1px solid var(--light);border-radius:8px;font-size:13px;background:#fff">
      <option value="">All</option>
      <option value="pending"   {{ $status=='pending'?'selected':'' }}>Pending</option>
      <option value="approved"  {{ $status=='approved'?'selected':'' }}>Approved</option>
      <option value="rejected"  {{ $status=='rejected'?'selected':'' }}>Rejected</option>
      <option value="completed" {{ $status=='completed'?'selected':'' }}>Completed</option>
    </select>
  </div>
  <div>
    <label style="display:block;font-size:11px;font-weight:700;color:var(--mid);margin-bottom:4px;text-transform:uppercase">Type</label>
    <select name="type" style="padding:8px 12px;border:1px solid var(--light);border-radius:8px;font-size:13px;background:#fff">
      <option value="">All</option>
      <option value="refund" {{ $type=='refund'?'selected':'' }}>Refund</option>
      <option value="return" {{ $type=='return'?'selected':'' }}>Return</option>
    </select>
  </div>
  <button type="submit" class="vs-btn vs-btn-primary vs-btn-sm">Filter</button>
  @if($status || $type)<a href="{{ route('vendor.refunds') }}" class="vs-btn vs-btn-ghost vs-btn-sm">Clear</a>@endif
</form>

<div class="vs-table-wrap">
  <table class="vs-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Order</th>
        <th>Customer</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Date</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    @forelse($refunds as $r)
      @php
        $badgeClass = match($r->status) {
          'approved','completed' => 'badge-approved',
          'rejected','cancelled' => 'badge-blocked',
          default => 'badge-pending',
        };
        $reasonLabel = match($r->reason) {
          'damaged'          => 'Damaged',
          'wrong_item'       => 'Wrong Item',
          'changed_mind'     => 'Changed Mind',
          'not_as_described' => 'Not as Described',
          default            => 'Other',
        };
      @endphp
      <tr>
        <td style="font-weight:600">#{{ $r->id }}</td>
        <td><a href="{{ route('vendor.orders.show', $r->order_id) }}" style="color:var(--orange)">#{{ $r->order_id }}</a></td>
        <td>
          <div style="font-weight:500">{{ $r->customer_name }}</div>
          <div style="font-size:12px;color:var(--mid)">{{ $r->customer_email }}</div>
        </td>
        <td style="text-transform:capitalize;font-weight:600">{{ $r->type }}</td>
        <td style="color:var(--mid);font-size:12px">{{ $reasonLabel }}</td>
        <td><span class="{{ $badgeClass }}">{{ ucfirst($r->status) }}</span></td>
        <td style="font-size:12px;color:var(--mid)">{{ \Carbon\Carbon::parse($r->created_at)->format('M d, Y') }}</td>
        <td><a href="{{ route('vendor.refunds.show', $r->id) }}" class="vs-btn vs-btn-ghost vs-btn-sm">View</a></td>
      </tr>
    @empty
      <tr><td colspan="8" style="text-align:center;color:var(--mid);padding:32px">No requests found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

@if($refunds->hasPages())
<div style="margin-top:16px">{{ $refunds->links('admin.pagination') }}</div>
@endif
@endsection

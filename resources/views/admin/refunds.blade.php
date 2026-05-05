@extends('admin.layout')
@section('title','Refund Requests')
@section('page-title','Refund & Return Requests')

@section('content')

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="{{ $search }}" placeholder="Order # or customer…" style="width:200px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      <option value="pending"   {{ $status=='pending'?'selected':'' }}>Pending</option>
      <option value="approved"  {{ $status=='approved'?'selected':'' }}>Approved</option>
      <option value="rejected"  {{ $status=='rejected'?'selected':'' }}>Rejected</option>
      <option value="completed" {{ $status=='completed'?'selected':'' }}>Completed</option>
      <option value="cancelled" {{ $status=='cancelled'?'selected':'' }}>Cancelled</option>
    </select>
  </div>
  <div class="form-group">
    <label>Type</label>
    <select name="type">
      <option value="">All</option>
      <option value="refund" {{ $type=='refund'?'selected':'' }}>Refund</option>
      <option value="return" {{ $type=='return'?'selected':'' }}>Return</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  @if($search || $status || $type)
    <div class="form-group">
      <a href="{{ route('admin.refunds') }}" class="btn btn-ghost">Clear</a>
    </div>
  @endif
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px">{{ $refunds->total() }} request(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Order</th>
        <th>Customer</th>
        <th>Vendor</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    @forelse($refunds as $r)
      @php
        $sc = match($r->status) {
          'approved','completed' => 'badge-green',
          'rejected','cancelled' => 'badge-red',
          default => 'badge-yellow',
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
        <td><a href="{{ route('admin.orders.detail', $r->order_id) }}" style="color:var(--accent)">#{{ $r->order_id }}</a></td>
        <td>
          <div style="font-weight:500">{{ $r->customer_name }}</div>
          <div style="font-size:11px;color:var(--muted)">{{ $r->customer_email }}</div>
        </td>
        <td style="font-size:12px;color:var(--muted)">{{ $r->vendor_shop_name ?? '—' }}</td>
        <td><span class="badge badge-blue" style="text-transform:capitalize">{{ $r->type }}</span></td>
        <td style="font-size:12px;color:var(--muted)">{{ $reasonLabel }}</td>
        <td><span class="badge {{ $sc }}">{{ ucfirst($r->status) }}</span></td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}</td>
        <td>
          <a href="{{ route('admin.refunds.show', $r->id) }}" class="btn btn-secondary btn-sm">Review</a>
        </td>
      </tr>
    @empty
      <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No requests found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="pagination">{{ $refunds->links('admin.pagination') }}</div>

@endsection

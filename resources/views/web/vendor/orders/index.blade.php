@extends('web.vendor.layout')
@section('title', 'My Orders')
@section('page-title', 'Orders')

@push('styles')
<style>
:root{--orange:#f97316;--red:#ef4444;--green:#22c55e;--mid:#6b7280;--light:#e5e7eb;--dark:#111827;--yellow:#f59e0b}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:24px}
.stat-card{background:#fff;border:1px solid var(--light);border-radius:12px;padding:14px 16px}
.stat-card .sc-num{font-size:26px;font-weight:800;color:var(--dark);line-height:1}
.stat-card .sc-label{font-size:11px;color:var(--mid);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px}
.filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px}
.filter-bar select,.filter-bar input{padding:7px 12px;border:1px solid var(--light);border-radius:8px;font-size:13px;outline:none}
.filter-bar select:focus,.filter-bar input:focus{border-color:var(--orange)}
.ot{width:100%;border-collapse:collapse}
.ot th{background:#f9fafb;padding:10px 14px;text-align:left;font-size:11px;font-weight:700;color:var(--mid);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--light)}
.ot td{padding:12px 14px;border-bottom:1px solid #f3f4f6;font-size:13px;color:var(--dark);vertical-align:middle}
.ot tr:last-child td{border-bottom:none}
.ot tr:hover td{background:#fafafa}
.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.s-pending{background:#fef3c7;color:#92400e}.s-processing{background:#dbeafe;color:#1e40af}.s-shipped{background:#f3e8ff;color:#6b21a8}.s-delivered{background:#dcfce7;color:#166534}.s-cancelled,.s-returned{background:#fee2e2;color:#991b1b}.s-on-hold,.s-refunded,.s-failed{background:#f3f4f6;color:var(--mid)}
.action-link{font-size:12px;font-weight:600;color:var(--orange);text-decoration:none}
.action-link:hover{text-decoration:underline}
.empty-state{text-align:center;padding:60px 20px;color:var(--mid)}
.vs-alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:16px}
</style>
@endpush

@section('content')

@if(session('success'))
  <div class="vs-alert-ok">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
  </div>
@endif

{{-- STAT CARDS --}}
<div class="stat-grid">
  <div class="stat-card"><div class="sc-num">{{ $stats['total'] }}</div><div class="sc-label">Total</div></div>
  <div class="stat-card" style="border-top:3px solid var(--yellow)"><div class="sc-num" style="color:var(--yellow)">{{ $stats['pending'] }}</div><div class="sc-label">Pending</div></div>
  <div class="stat-card" style="border-top:3px solid #3b82f6"><div class="sc-num" style="color:#3b82f6">{{ $stats['processing'] }}</div><div class="sc-label">Processing</div></div>
  <div class="stat-card" style="border-top:3px solid #8b5cf6"><div class="sc-num" style="color:#8b5cf6">{{ $stats['shipped'] }}</div><div class="sc-label">Shipped</div></div>
  <div class="stat-card" style="border-top:3px solid var(--green)"><div class="sc-num" style="color:var(--green)">{{ $stats['delivered'] }}</div><div class="sc-label">Delivered</div></div>
</div>

{{-- FILTERS --}}
<form method="GET" action="{{ route('vendor.orders') }}" class="filter-bar">
  <select name="status" onchange="this.form.submit()">
    <option value="">All Statuses</option>
    @foreach(['pending','processing','shipped','delivered','cancelled','returned'] as $s)
      <option value="{{ $s }}" {{ $statusFilter===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  <input type="text" name="search" placeholder="Search sub-order # or customer…" value="{{ $search }}" style="min-width:200px">
  <button type="submit" class="vs-btn vs-btn-primary" style="padding:7px 16px;font-size:13px">Search</button>
  @if($statusFilter || $search)
    <a href="{{ route('vendor.orders') }}" style="font-size:12px;color:var(--mid);text-decoration:none">Clear</a>
  @endif
</form>

{{-- ORDER TABLE --}}
@if($paginator && $paginator->count() > 0)
  <div class="vs-table-wrap">
    <table class="ot">
      <thead>
        <tr>
          <th>Sub-Order</th>
          <th>Customer</th>
          <th>Items</th>
          <th>Your Total</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $sub)
          @php
            $b    = $sub->billing_data;
            $name = trim(($b['first_name']??'').(' '.($b['last_name']??''))) ?: 'Guest';
          @endphp
          <tr>
            <td>
              <div style="font-weight:700">#{{ $sub->id }}</div>
              <div style="font-size:11px;color:var(--mid)">Order #{{ $sub->parent_order_id }}</div>
              @if($sub->tracking_number)
                <div style="font-size:11px;color:#3b82f6;margin-top:2px;font-family:monospace">{{ $sub->tracking_number }}</div>
              @endif
            </td>
            <td>
              <div style="font-weight:600">{{ $name }}</div>
              @if(!empty($b['phone']))<div style="font-size:11px;color:var(--mid)">{{ $b['phone'] }}</div>@endif
            </td>
            <td>
              @foreach(array_slice((array)$sub->vendor_items, 0, 2) as $item)
                <div style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px">
                  {{ $item['name'] }} <span style="color:var(--mid)">×{{ $item['quantity'] }}</span>
                </div>
              @endforeach
              @if(count((array)$sub->vendor_items) > 2)
                <div style="font-size:11px;color:var(--mid)">+{{ count((array)$sub->vendor_items) - 2 }} more</div>
              @endif
            </td>
            <td style="font-weight:700;white-space:nowrap">{{ number_format($sub->vendor_total,2) }} EGP</td>
            <td><span class="badge s-{{ $sub->status }}">{{ $sub->status === 'delivered' ? 'Delivered' : ucfirst($sub->status) }}</span></td>
            <td style="color:var(--mid);font-size:12px;white-space:nowrap">{{ \Carbon\Carbon::parse($sub->created_at)->format('d M Y') }}</td>
            <td><a href="{{ route('vendor.orders.show', $sub->id) }}" class="action-link">View →</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if($paginator->hasPages())
    <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;font-size:13px">
      @if($paginator->onFirstPage())<span style="padding:5px 12px;border:1px solid var(--light);border-radius:7px;color:var(--mid)">← Prev</span>
      @else<a href="{{ $paginator->previousPageUrl() }}" style="padding:5px 12px;border:1px solid var(--light);border-radius:7px;color:var(--dark);text-decoration:none">← Prev</a>@endif
      <span style="padding:5px 12px;background:#fff7ed;border:1px solid var(--orange);border-radius:7px;color:var(--orange);font-weight:600">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
      @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" style="padding:5px 12px;border:1px solid var(--light);border-radius:7px;color:var(--dark);text-decoration:none">Next →</a>
      @else<span style="padding:5px 12px;border:1px solid var(--light);border-radius:7px;color:var(--mid)">Next →</span>@endif
    </div>
  @endif

@else
  <div class="empty-state">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
    <h3 style="font-size:16px;font-weight:700;color:var(--dark);margin-bottom:6px">No orders yet</h3>
    <p style="font-size:13px;margin-bottom:20px">Orders containing your products will appear here automatically.</p>
    <a href="{{ route('vendor.products') }}" class="vs-btn vs-btn-primary">View Your Products</a>
  </div>
@endif

@endsection

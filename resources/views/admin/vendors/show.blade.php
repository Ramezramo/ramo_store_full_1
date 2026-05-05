@extends('admin.layout')
@section('title', $vendor->shop_name.' — Vendor')
@section('page-title', 'Vendor Detail')

@section('content')

@php
  $sc = match($vendor->status) {
    'approved'=>'badge-green','pending'=>'badge-yellow',
    'blocked'=>'badge-red','rejected'=>'badge-gray',default=>'badge-gray'
  };
@endphp

@if(session('success'))
  <div class="alert alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif

{{-- Back link --}}
<div style="margin-bottom:20px">
  <a href="{{ route('admin.vendors') }}" style="color:var(--muted);font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Vendors
  </a>
</div>

{{-- Header card --}}
<div class="card" style="margin-bottom:24px;padding:24px">
  <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
    <div style="width:64px;height:64px;border-radius:50%;overflow:hidden;background:var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center">
      @if($vendor->shop_logo)
        <img src="{{ $vendor->shop_logo }}" style="width:100%;height:100%;object-fit:cover" alt="logo">
      @else
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      @endif
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:20px;font-weight:700;margin-bottom:4px">{{ $vendor->shop_name }}</div>
      <div style="color:var(--muted);font-size:13px">{{ $vendor->first_name }} {{ $vendor->last_name }} &nbsp;·&nbsp; {{ $vendor->email }}</div>
      @if($vendor->shop_address)
        <div style="color:var(--muted);font-size:12px;margin-top:2px">{{ $vendor->shop_address }}</div>
      @endif
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <span class="badge {{ $sc }}" style="font-size:13px;padding:6px 14px">{{ ucfirst($vendor->status) }}</span>
      @if($vendor->status !== 'approved')
        <form method="POST" action="{{ route('admin.vendors.approve', $vendor->id) }}">
          @csrf @method('PATCH')
          <button class="btn btn-success btn-sm">Approve</button>
        </form>
      @endif
      @if($vendor->status !== 'blocked')
        <form method="POST" action="{{ route('admin.vendors.block', $vendor->id) }}" onsubmit="return confirm('Block this vendor?')">
          @csrf @method('PATCH')
          <button class="btn btn-warning btn-sm">Block</button>
        </form>
      @endif
      @if($vendor->status !== 'rejected')
        <form method="POST" action="{{ route('admin.vendors.reject', $vendor->id) }}" onsubmit="return confirm('Reject this vendor?')">
          @csrf @method('PATCH')
          <button class="btn btn-danger btn-sm">Reject</button>
        </form>
      @endif
      <form method="POST" action="{{ route('admin.vendors.delete', $vendor->id) }}" onsubmit="return confirm('Permanently delete this vendor? This cannot be undone.')">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm">Delete</button>
      </form>
    </div>
  </div>
</div>

{{-- Two-column info grid --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  {{-- Contact & Account --}}
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Contact & Account</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Full Name</td><td style="font-weight:500">{{ $vendor->first_name }} {{ $vendor->last_name }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Email</td><td>{{ $vendor->email }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Phone</td><td>{{ $vendor->phone ?: '—' }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Joined</td><td>{{ $vendor->created_at ? \Carbon\Carbon::parse($vendor->created_at)->format('d M Y, H:i') : '—' }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Rating</td><td>{{ $vendor->rating ?? '0' }} / 5 ({{ $vendor->rating_count ?? 0 }} reviews)</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Commission</td>
        <td>
          @if($vendor->sales_commission_percentage !== null)
            <span style="font-weight:600;color:var(--primary)">{{ $vendor->sales_commission_percentage }}%</span>
          @else
            <span style="color:var(--muted)">Not set</span>
          @endif
        </td>
      </tr>
    </table>
  </div>

  {{-- Shop Info --}}
  <div class="card" style="padding:20px">
    <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Shop Info</div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr><td style="color:var(--muted);padding:6px 0;width:45%">Shop Name</td><td style="font-weight:500">{{ $vendor->shop_name }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Address</td><td>{{ $vendor->shop_address ?: '—' }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Products</td><td><strong>{{ $productCount }}</strong></td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Min. Order</td><td>{{ $vendor->minimum_order_amount ? number_format($vendor->minimum_order_amount).' EGP' : '—' }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Free Delivery Over</td><td>{{ $vendor->free_delivery_over_amount ? number_format($vendor->free_delivery_over_amount).' EGP' : '—' }}</td></tr>
      <tr><td style="color:var(--muted);padding:6px 0">Vacation</td>
        <td>
          @if($vendor->vacation_status)
            <span class="badge badge-yellow">On Vacation</span>
            @if($vendor->vacation_start_date !== 'empty')
              <span style="font-size:11px;color:var(--muted);margin-left:6px">{{ $vendor->vacation_start_date }} – {{ $vendor->vacation_end_date }}</span>
            @endif
          @else
            <span style="color:var(--muted)">No</span>
          @endif
        </td>
      </tr>
    </table>
  </div>

</div>

{{-- Banking info --}}
<div class="card" style="padding:20px;margin-bottom:24px">
  <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Banking / Payout Info</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;font-size:13px">
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Account Holder</div>
      <div style="font-weight:500">{{ $vendor->holder_name ?: '—' }}</div>
    </div>
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Bank Name</div>
      <div style="font-weight:500">{{ $vendor->bank_name ?: '—' }}</div>
    </div>
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Branch</div>
      <div>{{ $vendor->branch ?: '—' }}</div>
    </div>
    <div>
      <div style="color:var(--muted);margin-bottom:4px">Account No.</div>
      <div>{{ $vendor->account_no ?: '—' }}</div>
    </div>
  </div>
</div>

{{-- Recent sub-orders --}}
<div class="card" style="padding:20px;margin-bottom:24px">
  <div style="font-weight:600;font-size:14px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Recent Sub-Orders</div>
  @if(isset($subOrders) && $subOrders->isNotEmpty())
    <div class="table-wrap" style="margin:0">
      <table>
        <thead>
          <tr>
            <th>Sub-Order</th>
            <th>Parent Order</th>
            <th>Status</th>
            <th>Total</th>
            <th>Tracking</th>
          </tr>
        </thead>
        <tbody>
          @foreach($subOrders as $sub)
            @php
              $sc = match($sub->status) {
                'completed'=>'badge-green','pending'=>'badge-yellow',
                'processing'=>'badge-blue','shipped'=>'badge-orange',
                'cancelled'=>'badge-red', default=>'badge-gray'
              };
            @endphp
            <tr>
              <td style="font-weight:600">#{{ $sub->id }}</td>
              <td>#{{ $sub->parent_order_id }}</td>
              <td><span class="badge {{ $sc }}">{{ ucfirst($sub->status) }}</span></td>
              <td>{{ number_format($sub->total, 2) }}</td>
              <td style="font-size:12px;color:var(--muted)">
                {{ $sub->tracking_number ?: '—' }}
                @if($sub->tracking_carrier) / {{ $sub->tracking_carrier }} @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div style="color:var(--muted);font-size:13px">No sub-orders yet.</div>
  @endif
</div>

{{-- Recent products --}}
<div class="card" style="padding:20px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">
    <div style="font-weight:600;font-size:14px">Products <span style="color:var(--muted);font-weight:400">({{ $productCount }} total)</span></div>
    @if($productCount > 10)
      <a href="{{ route('admin.products') }}?vendor={{ $vendor->id }}" style="font-size:12px;color:var(--primary)">View all</a>
    @endif
  </div>
  @if($products->isEmpty())
    <div style="color:var(--muted);font-size:13px;text-align:center;padding:24px 0">No products yet.</div>
  @else
    <div class="table-wrap" style="margin:0">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Publish Status</th>
            <th>Acceptance</th>
            <th>Added</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $p)
            @php
              $ac = match($p->acceptance_status ?? 'pending') {
                'approved'=>'badge-green','pending'=>'badge-yellow',
                'rejected'=>'badge-red',default=>'badge-gray'
              };
            @endphp
            <tr>
              <td style="color:var(--muted);font-size:12px">#{{ $p->id }}</td>
              <td style="font-weight:500;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->name }}</td>
              <td><span class="badge {{ $p->status === 'publish' ? 'badge-green' : 'badge-gray' }}">{{ $p->status ?: '—' }}</span></td>
              <td><span class="badge {{ $ac }}">{{ ucfirst($p->acceptance_status ?? 'pending') }}</span></td>
              <td style="color:var(--muted);font-size:12px">{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d M Y') : '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

@endsection

@extends('admin.layout')
@section('title', 'Vendors')
@section('page-title', 'Vendor Management')

@section('content')

@if(session('success'))
  <div class="alert alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif

<form method="GET" class="form-row" style="margin-bottom:20px">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="{{ $search }}" placeholder="Shop name, email, owner…" style="width:240px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      <option value="pending"  {{ $status=='pending'  ?'selected':'' }}>Pending</option>
      <option value="approved" {{ $status=='approved' ?'selected':'' }}>Approved</option>
      <option value="blocked"  {{ $status=='blocked'  ?'selected':'' }}>Blocked</option>
      <option value="rejected" {{ $status=='rejected' ?'selected':'' }}>Rejected</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  @if($search || $status)
    <div class="form-group">
      <a href="{{ route('admin.vendors') }}" class="btn btn-ghost">Clear</a>
    </div>
  @endif
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px">{{ $vendors->total() }} vendor(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Shop Name</th>
        <th>Owner</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Status</th>
        <th>Commission</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    @forelse($vendors as $v)
      @php
        $sc = match($v->status) {
          'approved'=>'badge-green','pending'=>'badge-yellow',
          'blocked'=>'badge-red','rejected'=>'badge-gray',default=>'badge-gray'
        };
      @endphp
      <tr>
        <td style="color:var(--muted);font-size:12px">#{{ $v->id }}</td>
        <td>
          <a href="{{ route('admin.vendors.show', $v->id) }}" style="font-weight:600;color:var(--primary);text-decoration:none">
            {{ $v->shop_name }}
          </a>
        </td>
        <td>{{ $v->first_name }} {{ $v->last_name }}</td>
        <td style="color:var(--muted);font-size:12px">{{ $v->email }}</td>
        <td style="color:var(--muted);font-size:12px">{{ $v->phone ?: '—' }}</td>
        <td><span class="badge {{ $sc }}">{{ ucfirst($v->status) }}</span></td>
        <td style="font-size:13px;text-align:center">
          {{ $v->sales_commission_percentage !== null ? $v->sales_commission_percentage.'%' : '—' }}
        </td>
        <td style="color:var(--muted);font-size:12px;white-space:nowrap">
          {{ $v->created_at ? \Carbon\Carbon::parse($v->created_at)->format('d M Y') : '—' }}
        </td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
            <a href="{{ route('admin.vendors.show', $v->id) }}" class="btn btn-ghost btn-sm">View</a>
            @if($v->status !== 'approved')
              <form method="POST" action="{{ route('admin.vendors.approve', $v->id) }}">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm">Approve</button>
              </form>
            @endif
            @if($v->status !== 'blocked')
              <form method="POST" action="{{ route('admin.vendors.block', $v->id) }}" onsubmit="return confirm('Block this vendor?')">
                @csrf @method('PATCH')
                <button class="btn btn-warning btn-sm">Block</button>
              </form>
            @endif
            @if($v->status !== 'rejected')
              <form method="POST" action="{{ route('admin.vendors.reject', $v->id) }}" onsubmit="return confirm('Reject this vendor?')">
                @csrf @method('PATCH')
                <button class="btn btn-danger btn-sm">Reject</button>
              </form>
            @endif
            <form method="POST" action="{{ route('admin.vendors.delete', $v->id) }}" onsubmit="return confirm('Permanently delete this vendor?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No vendors found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="pagination">{{ $vendors->links('admin.pagination') }}</div>

@endsection

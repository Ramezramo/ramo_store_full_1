@extends('admin.layout')
@section('title', 'Vendors')
@section('page-title', 'Vendors Management')

@section('content')

<div class="section">
  <div class="section-header">
    <div class="section-title">All Vendor Shops</div>
  </div>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search shop name, owner…" class="search-input">
    <select name="status" class="filter-select">
      <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
      <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
      <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
      <option value="blocked" {{ $status === 'blocked' ? 'selected' : '' }}>Blocked</option>
      <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
    </select>
    <button type="submit" class="btn-filter">Filter</button>
    @if($search || $status !== 'all')
      <a href="{{ route('admin.vendors') }}" class="btn btn-secondary btn-sm">Clear</a>
    @endif
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Shop Name</th>
          <th>Owner</th>
          <th>Email</th>
          <th>Status</th>
          <th>Owner Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($vendors as $vendor)
        @php
          $sc = match($vendor->status) {
            'active' => 'badge-green',
            'pending' => 'badge-yellow',
            'blocked' => 'badge-red',
            'rejected' => 'badge-gray',
            default => 'badge-gray'
          };
        @endphp
        <tr>
          <td style="color:var(--muted)">{{ $vendor->id }}</td>
          <td style="font-weight:700">{{ $vendor->shop_name }}</td>
          <td>{{ $vendor->owner_name ?: '—' }}</td>
          <td style="color:var(--muted)">{{ $vendor->owner_email ?: '—' }}</td>
          <td><span class="badge {{ $sc }}">{{ $vendor->status }}</span></td>
          <td>
            @if($vendor->owner_blocked)
              <span class="badge badge-red">User Blocked</span>
            @else
              <span class="badge badge-green">Active</span>
            @endif
          </td>
          <td style="color:var(--muted)">{{ $vendor->created_at ? date('M d, Y', strtotime($vendor->created_at)) : '—' }}</td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              @if($vendor->status !== 'active')
                <form method="POST" action="{{ route('admin.vendors.approve', $vendor->id) }}">
                  @csrf
                  <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </form>
              @endif
              @if($vendor->status !== 'blocked')
                <form method="POST" action="{{ route('admin.vendors.block', $vendor->id) }}">
                  @csrf
                  <button type="submit" class="btn btn-warning btn-sm">Block</button>
                </form>
              @endif
              @if($vendor->status !== 'rejected')
                <form method="POST" action="{{ route('admin.vendors.reject', $vendor->id) }}">
                  @csrf
                  <button type="submit" class="btn btn-secondary btn-sm">Reject</button>
                </form>
              @endif
              <form method="POST" action="{{ route('admin.vendors.delete', $vendor->id) }}" id="del-vendor-{{ $vendor->id }}">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-danger btn-sm" onclick="confirm_action('del-vendor-{{ $vendor->id }}', 'Delete this vendor shop?')">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No vendors found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">{{ $vendors->links() }}</div>
</div>

@endsection

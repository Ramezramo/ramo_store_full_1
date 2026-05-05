@extends('admin.layout')
@section('title', 'Users')
@section('page-title', 'Users Management')

@section('content')

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="{{ $search }}" placeholder="Name, email, phone…" style="width:220px">
  </div>
  <div class="form-group">
    <label>Role</label>
    <select name="role">
      <option value="">All Roles</option>
      <option value="customer" {{ $role=='customer'?'selected':'' }}>Customer</option>
      <option value="admin" {{ $role=='admin'?'selected':'' }}>Admin</option>
      <option value="vendor" {{ $role=='vendor'?'selected':'' }}>Vendor</option>
    </select>
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="status">
      <option value="">All</option>
      <option value="active" {{ $status=='active'?'selected':'' }}>Active</option>
      <option value="blocked" {{ $status=='blocked'?'selected':'' }}>Blocked</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  @if($search || $role || $status)
    <div class="form-group" style="justify-content:flex-end">
      <a href="{{ route('admin.users') }}" class="btn btn-ghost">Clear</a>
    </div>
  @endif
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px">
  {{ $users->total() }} user(s) found
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Status</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    @forelse($users as $user)
      <tr>
        <td style="color:var(--muted)">#{{ $user->id }}</td>
        <td style="font-weight:600;max-width:150px">{{ $user->name }}</td>
        <td style="color:var(--muted);font-size:12px">{{ $user->email }}</td>
        <td style="color:var(--muted);font-size:12px">{{ $user->phone }}</td>
        <td>
          <span class="badge badge-gray">{{ trim(strip_tags(str_replace(['"','[',']','\\'], '', $user->role))) }}</span>
        </td>
        <td>
          @if($user->is_blocked)
            <span class="badge badge-red">Blocked</span>
          @else
            <span class="badge badge-green">Active</span>
          @endif
        </td>
        <td style="color:var(--muted);font-size:12px;white-space:nowrap">{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}</td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap">
            @if($user->is_blocked)
              <form method="POST" action="{{ route('admin.users.unblock', $user->id) }}">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm">Unblock</button>
              </form>
            @else
              <form method="POST" action="{{ route('admin.users.block', $user->id) }}" onsubmit="return confirm('Block this user?')">
                @csrf @method('PATCH')
                <button class="btn btn-warning btn-sm">Block</button>
              </form>
            @endif

            <form method="POST" action="{{ route('admin.users.role', $user->id) }}" style="display:flex;gap:4px">
              @csrf @method('PATCH')
              <select name="role" style="padding:4px 6px;font-size:12px;height:28px">
                <option value="customer" {{ str_contains($user->role,'customer')?'selected':'' }}>Customer</option>
                <option value="admin" {{ str_contains($user->role,'admin')?'selected':'' }}>Admin</option>
                <option value="vendor" {{ str_contains($user->role,'vendor')?'selected':'' }}>Vendor</option>
              </select>
              <button class="btn btn-ghost btn-sm">Set</button>
            </form>

            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" onsubmit="return confirm('Permanently delete this user and all their data?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No users found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="pagination">
  {{ $users->links('admin.pagination') }}
</div>

@endsection

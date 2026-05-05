@extends('admin.layout')
@section('title', 'Users')
@section('page-title', 'Users Management')

@section('content')

<div class="section">
  <div class="section-header">
    <div class="section-title">All Users</div>
  </div>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, phone…" class="search-input">
    <select name="filter" class="filter-select">
      <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Users</option>
      <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Active</option>
      <option value="blocked" {{ $filter === 'blocked' ? 'selected' : '' }}>Blocked</option>
    </select>
    <button type="submit" class="btn-filter">Filter</button>
    @if($search || $filter !== 'all')
      <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">Clear</a>
    @endif
  </form>

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
          <td style="color:var(--muted)">{{ $user->id }}</td>
          <td style="font-weight:600">{{ $user->name ?: '—' }}</td>
          <td style="color:var(--muted)">{{ $user->email }}</td>
          <td style="color:var(--muted)">{{ $user->phone ?: '—' }}</td>
          <td>
            @php $role = is_string($user->role) ? trim($user->role, '[]"') : $user->role; @endphp
            <span class="badge {{ $role === 'admin' || str_contains($role,'administrator') ? 'badge-purple' : 'badge-gray' }}">
              {{ $role ?: 'user' }}
            </span>
          </td>
          <td>
            @if($user->is_blocked)
              <span class="badge badge-red">Blocked</span>
            @else
              <span class="badge badge-green">Active</span>
            @endif
          </td>
          <td style="color:var(--muted)">{{ $user->created_at ? date('M d, Y', strtotime($user->created_at)) : '—' }}</td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              @if($user->is_blocked)
                <form method="POST" action="{{ route('admin.users.unblock', $user->id) }}">
                  @csrf
                  <button type="submit" class="btn btn-success btn-sm">Unblock</button>
                </form>
              @else
                <form method="POST" action="{{ route('admin.users.block', $user->id) }}" id="block-user-{{ $user->id }}">
                  @csrf
                  <button type="button" class="btn btn-warning btn-sm" onclick="confirm_action('block-user-{{ $user->id }}', 'Block this user?')">Block</button>
                </form>
              @endif

              {{-- Role update --}}
              <form method="POST" action="{{ route('admin.users.role', $user->id) }}" style="display:flex;gap:4px">
                @csrf @method('PUT')
                <select name="role" class="filter-select" style="padding:4px 8px;font-size:11px">
                  <option value="normal_user" {{ $role === 'normal_user' || $role === 'customer' ? 'selected' : '' }}>User</option>
                  <option value="vendor" {{ str_contains($role,'vendor') ? 'selected' : '' }}>Vendor</option>
                  <option value="admin" {{ str_contains($role,'admin') ? 'selected' : '' }}>Admin</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Set</button>
              </form>

              <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" id="del-user-{{ $user->id }}">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-danger btn-sm" onclick="confirm_action('del-user-{{ $user->id }}', 'Permanently delete this user and all their data?')">Delete</button>
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
    {{ $users->links() }}
  </div>
</div>

@endsection

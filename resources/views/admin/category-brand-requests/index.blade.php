@extends('admin.layout')
@section('title', 'Category & Brand Requests')
@section('page-title', 'Category & Brand Requests')

@section('topbar-actions')
  <a href="{{ route('admin.cbr') }}?status=pending" class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-ghost' }}">
    Pending @if($counts['pending'] > 0)<span style="background:rgba(255,255,255,.25);border-radius:10px;padding:0 5px;margin-left:2px;font-size:10px">{{ $counts['pending'] }}</span>@endif
  </a>
  <a href="{{ route('admin.cbr') }}?status=approved" class="btn btn-sm {{ $status === 'approved' ? 'btn-primary' : 'btn-ghost' }}">Approved</a>
  <a href="{{ route('admin.cbr') }}?status=rejected" class="btn btn-sm {{ $status === 'rejected' ? 'btn-primary' : 'btn-ghost' }}">Rejected</a>
  <a href="{{ route('admin.cbr') }}?status=" class="btn btn-sm {{ $status === '' ? 'btn-primary' : 'btn-ghost' }}">All</a>
@endsection

@push('styles')
<style>
.req-note-form{display:none;margin-top:8px}
.req-note-form.open{display:flex;flex-direction:column;gap:6px}
.req-row-actions{display:flex;gap:6px;align-items:flex-start;flex-direction:column}
.req-note-form input,.req-note-form select{
  padding:5px 8px;border-radius:5px;border:1px solid var(--border);
  background:var(--card);color:var(--text);font-size:12px;width:220px;
}
</style>
@endpush

@section('content')

<div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap">
  @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $s=>$label)
  <div class="stat-card" style="min-width:140px;flex:1">
    <div class="stat-value" style="font-size:22px">{{ $counts[$s] }}</div>
    <div class="stat-label">{{ $label }}</div>
  </div>
  @endforeach
</div>

<form method="GET" style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
  <input type="hidden" name="status" value="{{ $status }}">
  <div class="form-group">
    <label>Filter by Type</label>
    <select name="type" onchange="this.form.submit()" style="min-width:140px">
      <option value="" {{ $type === '' ? 'selected' : '' }}>All Types</option>
      <option value="category" {{ $type === 'category' ? 'selected' : '' }}>Category</option>
      <option value="brand" {{ $type === 'brand' ? 'selected' : '' }}>Brand</option>
    </select>
  </div>
</form>

@if($requests->isEmpty())
  <div class="card" style="text-align:center;padding:48px">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" style="color:var(--muted);margin-bottom:12px"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p style="color:var(--muted)">No {{ $status }} requests found.</p>
  </div>
@else
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Type</th>
          <th>Name</th>
          <th>Parent Category</th>
          <th>Description</th>
          <th>Vendor</th>
          <th>Status</th>
          <th>Date</th>
          @if($status === 'pending')<th>Actions</th>@else<th>Admin Note</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach($requests as $req)
        <tr>
          <td style="color:var(--muted)">{{ $req->id }}</td>
          <td>
            @if($req->type === 'category')
              <span class="badge badge-blue">Category</span>
            @else
              <span class="badge badge-purple">Brand</span>
            @endif
          </td>
          <td style="font-weight:600">{{ $req->name }}</td>
          <td style="font-size:12px">
            @if($req->type === 'category')
              @if($req->parent_category_name)
                <span class="badge badge-green">↳ {{ $req->parent_category_name }}</span>
              @else
                <span style="color:var(--muted)">Top-level</span>
              @endif
            @else
              <span style="color:var(--muted)">—</span>
            @endif
          </td>
          <td style="color:var(--muted);max-width:160px;font-size:12px">{{ $req->description ? Str::limit($req->description, 50) : '—' }}</td>
          <td style="font-size:12px">{{ $req->vendor_name ?? '—' }}</td>
          <td>
            @if($req->status === 'pending')
              <span class="badge badge-yellow">Pending</span>
            @elseif($req->status === 'approved')
              <span class="badge badge-green">Approved</span>
            @else
              <span class="badge badge-red">Rejected</span>
            @endif
          </td>
          <td style="color:var(--muted);font-size:12px;white-space:nowrap">{{ \Carbon\Carbon::parse($req->created_at)->format('M d, Y') }}</td>

          @if($status === 'pending')
          <td>
            <div class="req-row-actions">
              {{-- Approve --}}
              <button onclick="toggleNote('approve-{{ $req->id }}')" class="btn btn-sm btn-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
              </button>
              <div id="approve-{{ $req->id }}" class="req-note-form">
                <form method="POST" action="{{ route('admin.cbr.approve', $req->id) }}">
                  @csrf @method('PATCH')
                  @if($req->type === 'category')
                  <div>
                    <label style="font-size:11px;color:var(--muted);display:block;margin-bottom:2px">Parent category (override)</label>
                    <select name="parent_category_id">
                      <option value="">— No parent (top-level) —</option>
                      @php
                        $topLevel = $allCategories->filter(fn($c) => $c->parent == 0 || $c->parent === null);
                      @endphp
                      @foreach($topLevel as $cat)
                        <option value="{{ $cat->id }}" {{ $req->parent_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @foreach($allCategories->where('parent', $cat->id) as $child)
                          <option value="{{ $child->id }}" {{ $req->parent_category_id == $child->id ? 'selected' : '' }}>&nbsp;&nbsp;↳ {{ $child->name }}</option>
                        @endforeach
                      @endforeach
                    </select>
                  </div>
                  @endif
                  <input type="text" name="admin_note" placeholder="Note (optional)">
                  <button type="submit" class="btn btn-sm btn-success">Confirm Approve</button>
                </form>
              </div>

              {{-- Reject --}}
              <button onclick="toggleNote('reject-{{ $req->id }}')" class="btn btn-sm btn-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
              </button>
              <div id="reject-{{ $req->id }}" class="req-note-form">
                <form method="POST" action="{{ route('admin.cbr.reject', $req->id) }}">
                  @csrf @method('PATCH')
                  <input type="text" name="admin_note" placeholder="Reason (optional)">
                  <button type="submit" class="btn btn-sm btn-danger">Confirm Reject</button>
                </form>
              </div>
            </div>
          </td>
          @else
          <td style="color:var(--muted);font-size:12px">{{ $req->admin_note ?: '—' }}</td>
          @endif
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($requests->hasPages())
    <div class="pagination" style="margin-top:16px">{{ $requests->links() }}</div>
  @endif
@endif

<script>
function toggleNote(id) {
  document.getElementById(id).classList.toggle('open');
}
</script>
@endsection

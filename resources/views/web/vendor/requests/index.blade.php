@extends('web.vendor.layout')
@section('title', 'My Requests')
@section('page-title', 'Category & Brand Requests')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <p style="color:var(--mid);font-size:13px">Track the status of your submitted category and brand requests.</p>
  <a href="{{ route('vendor.requests.create') }}" class="vs-btn vs-btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Request
  </a>
</div>

@if($requests->isEmpty())
  <div style="background:var(--white);border:1px solid var(--light);border-radius:12px;padding:48px;text-align:center">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="color:var(--mid);margin-bottom:12px"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p style="color:var(--mid);font-size:14px">No requests yet. Submit one to get started.</p>
    <a href="{{ route('vendor.requests.create') }}" class="vs-btn vs-btn-primary" style="margin-top:16px;display:inline-flex">Submit a Request</a>
  </div>
@else
  <div class="vs-table-wrap">
    <table class="vs-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Type</th>
          <th>Name</th>
          <th>Parent Category</th>
          <th>Description</th>
          <th>Status</th>
          <th>Admin Note</th>
          <th>Submitted</th>
        </tr>
      </thead>
      <tbody>
        @foreach($requests as $req)
        <tr>
          <td style="color:var(--mid)">{{ $req->id }}</td>
          <td>
            @if($req->type === 'category')
              <span style="background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Category</span>
            @else
              <span style="background:#faf5ff;color:#6d28d9;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Brand</span>
            @endif
          </td>
          <td style="font-weight:600">{{ $req->name }}</td>
          <td style="font-size:12px;color:var(--mid)">
            @if($req->type === 'category' && $req->parent_category_name)
              <span style="background:#f0fdf4;color:#166534;padding:2px 7px;border-radius:20px;font-size:11px">↳ {{ $req->parent_category_name }}</span>
            @elseif($req->type === 'category')
              <span style="color:var(--mid)">Top-level</span>
            @else
              —
            @endif
          </td>
          <td style="color:var(--mid);max-width:160px;font-size:12px">{{ $req->description ? Str::limit($req->description, 50) : '—' }}</td>
          <td>
            @if($req->status === 'pending')
              <span class="badge-pending">Pending</span>
            @elseif($req->status === 'approved')
              <span class="badge-approved">Approved</span>
            @else
              <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">Rejected</span>
            @endif
          </td>
          <td style="color:var(--mid);font-size:12px;max-width:160px">{{ $req->admin_note ?: '—' }}</td>
          <td style="color:var(--mid);font-size:12px;white-space:nowrap">{{ \Carbon\Carbon::parse($req->created_at)->format('M d, Y') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($requests->hasPages())
    <div style="margin-top:16px">{{ $requests->links() }}</div>
  @endif
@endif
@endsection

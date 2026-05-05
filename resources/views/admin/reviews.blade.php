@extends('admin.layout')
@section('title', 'Reviews')
@section('page-title', 'Product Reviews')

@section('content')

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="{{ $search }}" placeholder="Review text, title…" style="width:220px">
  </div>
  <div class="form-group">
    <label>Rating</label>
    <select name="rating">
      <option value="">All Ratings</option>
      @for($i=5;$i>=1;$i--)
        <option value="{{ $i }}" {{ $rating==$i?'selected':'' }}>{{ $i }} ★</option>
      @endfor
    </select>
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="approved">
      <option value="">All</option>
      <option value="1" {{ $approved==='1'?'selected':'' }}>Approved</option>
      <option value="0" {{ $approved==='0'?'selected':'' }}>Pending</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  @if($search || $rating || $approved !== '')
    <div class="form-group" style="justify-content:flex-end">
      <a href="{{ route('admin.reviews') }}" class="btn btn-ghost">Clear</a>
    </div>
  @endif
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px">{{ $reviews->total() }} review(s)</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Product</th>
        <th>User</th>
        <th>Rating</th>
        <th>Title</th>
        <th>Review</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    @forelse($reviews as $review)
      <tr>
        <td style="color:var(--muted)">#{{ $review->id }}</td>
        <td style="font-size:12px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          {{ $review->product_name ?? 'Product #'.$review->product_id }}
        </td>
        <td style="font-size:12px;color:var(--muted)">
          <div>{{ $review->user_name ?? '—' }}</div>
          @if($review->is_verified_purchase)
            <span class="badge badge-green" style="font-size:10px;padding:2px 5px">Verified</span>
          @endif
        </td>
        <td>
          <span style="color:{{ $review->rating >= 4 ? 'var(--green)' : ($review->rating >= 3 ? 'var(--yellow)' : 'var(--red)') }};font-weight:700;font-size:14px">
            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
          </span>
        </td>
        <td style="font-weight:600;font-size:13px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          {{ $review->title ?: '—' }}
        </td>
        <td style="font-size:12px;color:var(--muted);max-width:200px">
          <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $review->body }}">
            {{ $review->body }}
          </div>
          <div style="font-size:11px;margin-top:2px">👍 {{ $review->helpful_count }} helpful</div>
        </td>
        <td>
          @if($review->approved)
            <span class="badge badge-green">Approved</span>
          @else
            <span class="badge badge-yellow">Pending</span>
          @endif
        </td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">
          {{ $review->created_at ? \Carbon\Carbon::parse($review->created_at)->format('d M Y') : '—' }}
        </td>
        <td>
          <div style="display:flex;gap:6px">
            <form method="POST" action="{{ route('admin.reviews.toggle', $review->id) }}">
              @csrf @method('PATCH')
              @if($review->approved)
                <button class="btn btn-warning btn-sm">Unapprove</button>
              @else
                <button class="btn btn-success btn-sm">Approve</button>
              @endif
            </form>
            <form method="POST" action="{{ route('admin.reviews.delete', $review->id) }}" onsubmit="return confirm('Delete this review?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No reviews found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="pagination">{{ $reviews->links('admin.pagination') }}</div>

@endsection

@extends('admin.layout')
@section('title', 'Vendor Applications')
@section('page-title', 'Vendor Applications')

@section('content')
<div style="max-width:1100px">
  <div style="margin-bottom:18px"><a class="btn btn-ghost" href="{{ route('admin.vendors') }}">← Back to vendors</a></div>
  @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
  <div class="card" style="margin-bottom:20px">
    <div class="card-title">Application settings</div>
    <form method="POST" action="{{ route('admin.vendor-applications.settings') }}" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
      @csrf
      <input type="hidden" name="require_identity" value="0">
      <label style="display:flex;align-items:center;gap:9px">
        <input type="checkbox" name="require_identity" value="1" @checked($requireIdentity) style="width:18px;height:18px;accent-color:var(--accent)">
        Require an ID photo on new applications
      </label>
      <button class="btn btn-primary" type="submit">Save setting</button>
    </form>
    <p style="font-size:12px;color:var(--muted);line-height:1.6;margin-top:10px">The choice applies when an application is submitted. ID photos are encrypted and only accessible to admins.</p>
  </div>
  <div class="card" style="margin-bottom:20px">
    <div class="card-title">Rejection reasons</div>
    <p style="color:var(--muted);font-size:12px;margin-bottom:12px">Applicants see the selected reason. Disable a reason to remove it from future reviews.</p>
    <form method="POST" action="{{ route('admin.vendor-applications.reasons.add') }}" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
      @csrf
      <input type="text" name="label" required maxlength="200" placeholder="Add a rejection reason" style="flex:1;min-width:200px">
      <button class="btn btn-primary">Add reason</button>
    </form>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      @foreach($reasons as $reason)
        <form method="POST" action="{{ route('admin.vendor-applications.reasons.toggle', $reason->id) }}" style="display:flex;align-items:center;gap:6px;border:1px solid var(--border);border-radius:8px;padding:6px 8px">
          @csrf @method('PATCH')
          <span style="{{ $reason->active ? '' : 'opacity:.5' }}">{{ $reason->label }}</span>
          <button class="btn btn-ghost btn-sm">{{ $reason->active ? 'Disable' : 'Enable' }}</button>
        </form>
      @endforeach
    </div>
  </div>
  <div class="card">
    <div class="card-title">Seller applications</div>
    @forelse($applications as $application)
      @php $personal = $application->personal_data ?? []; @endphp
      <article style="border:1px solid var(--border);border-radius:10px;padding:18px;margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;flex-wrap:wrap">
          <div>
            <strong style="font-size:15px">{{ trim(($personal['first_name'] ?? '').' '.($personal['last_name'] ?? '')) ?: 'Draft application' }}</strong>
            <div style="color:var(--muted);font-size:12px;margin-top:4px">Application #{{ $application->id }} · {{ $application->status === 'on_hold' ? 'Consider later' : ucfirst($application->status) }} · Submission {{ $application->submission_count }} / 2 @if($application->submitted_at) · {{ $application->submitted_at->format('d M Y H:i') }} UTC @endif</div>
          </div>
          @if($application->identity_document)
            <a class="btn btn-ghost" href="{{ route('admin.vendor-applications.identity', $application->id) }}" target="_blank" rel="noopener">View encrypted ID</a>
          @else
            <span class="badge badge-gray">No ID uploaded</span>
          @endif
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:15px;font-size:13px">
          @foreach(['email' => 'Email', 'phone' => 'Phone', 'address' => 'Residential address'] as $key => $label)
            <div><span style="display:block;color:var(--muted);font-size:11px">{{ $label }}</span><span style="overflow-wrap:anywhere">{{ $personal[$key] ?? '—' }}</span></div>
          @endforeach
        </div>
        @if($application->rejection_reason)<p style="color:#fca5a5;margin-top:12px;font-size:13px">Reason: {{ $application->rejection_reason }}</p>@endif
        <div style="margin-top:12px">
          <a class="btn btn-ghost btn-sm" href="{{ route('admin.users.flags', $application->user_id) }}">User flags ({{ ($flags[$application->user_id] ?? collect())->count() }})</a>
          @foreach(($flags[$application->user_id] ?? collect()) as $flag)
            <div style="color:#fcd34d;font-size:12px;margin-top:6px">⚑ {{ $flag->reason }}</div>
          @endforeach
        </div>
        @if(($reviews[$application->id] ?? collect())->isNotEmpty())
          <details style="margin-top:12px;font-size:12px;color:var(--muted)">
            <summary style="cursor:pointer">Review history</summary>
            @foreach($reviews[$application->id] as $review)
              <p style="margin-top:6px">Submission {{ $review->submission_number }} · {{ $review->decision === 'on_hold' ? 'Consider later' : ucfirst($review->decision) }}@if($review->reason) · {{ $review->reason }}@endif · Admin #{{ $review->reviewed_by ?? '—' }} · {{ $review->created_at->format('d M Y H:i') }} UTC</p>
            @endforeach
          </details>
        @endif
        @if(in_array($application->status, ['pending', 'on_hold'], true))
          <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-top:16px">
            <form method="POST" action="{{ route('admin.vendor-applications.review', $application->id) }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
              @csrf @method('PATCH')<input type="hidden" name="decision" value="approve">
              @if($application->identity_document)
                <label style="display:flex;align-items:center;gap:6px;font-size:12px"><input type="checkbox" name="identity_verified" value="1" required> I reviewed this ID</label>
              @endif
              <button class="btn btn-success">Approve</button>
            </form>
            <form method="POST" action="{{ route('admin.vendor-applications.review', $application->id) }}" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">
              @csrf @method('PATCH')<input type="hidden" name="decision" value="reject">
              <label style="display:grid;gap:4px;font-size:12px;color:var(--muted)">Reason for rejection
                <select name="reason_id" required><option value="">Choose a reason</option>@foreach($reasons->where('active', true) as $reason)<option value="{{ $reason->id }}">{{ $reason->label }}</option>@endforeach</select>
              </label>
              <label style="display:flex;align-items:center;gap:5px;font-size:12px"><input type="checkbox" name="add_flag" value="1"> Add a user flag</label>
              <button class="btn btn-danger">Reject</button>
            </form>
            @if($application->status === 'pending')
              <form method="POST" action="{{ route('admin.vendor-applications.review', $application->id) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="hold"><button class="btn btn-warning">Consider later</button></form>
            @endif
          </div>
        @endif
      </article>
    @empty
      <p style="color:var(--muted)">No applications yet.</p>
    @endforelse
    <div class="pagination">{{ $applications->links('admin.pagination') }}</div>
  </div>
</div>
@endsection

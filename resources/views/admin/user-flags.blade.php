@extends('admin.layout')
@section('title', 'User Flags')
@section('page-title', 'User Flags')
@section('content')
<div style="max-width:800px">
  <a class="btn btn-ghost" href="{{ route('admin.users') }}" style="margin-bottom:18px">← Back to Users</a>
  @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
  <div class="card" style="margin-bottom:18px">
    <h2 style="font-size:17px;margin-bottom:5px">{{ $customer->name }} · #{{ $customer->id }}</h2>
    <p style="color:var(--muted);font-size:13px;line-height:1.6;margin-bottom:14px">Flags are private notes for administrators. They do not block the customer automatically.</p>
    <form method="POST" action="{{ route('admin.users.flags.store', $customer->id) }}">
      @csrf
      <label for="flag-reason" style="display:block;font-size:12px;color:var(--muted);margin-bottom:6px">Reason for flag</label>
      <textarea id="flag-reason" name="reason" required maxlength="1000" rows="3" style="width:100%;margin-bottom:10px"></textarea>
      <button class="btn btn-primary">Add flag</button>
    </form>
  </div>
  <div class="card">
    <div class="card-title">Flag history</div>
    @forelse($flags as $flag)
      <div style="padding:12px 0;border-bottom:1px solid var(--border)">
        <span class="badge {{ $flag->resolved_at ? 'badge-gray' : 'badge-yellow' }}">{{ $flag->resolved_at ? 'Resolved' : 'Active' }}</span>
        <span style="font-size:12px;color:var(--muted);margin-inline-start:8px">{{ $flag->created_at->format('d M Y H:i') }} UTC · Admin #{{ $flag->created_by ?? '—' }}</span>
        <p style="margin:8px 0;white-space:pre-wrap;overflow-wrap:anywhere">{{ $flag->reason }}</p>
        @if(!$flag->resolved_at)
          <form method="POST" action="{{ route('admin.users.flags.resolve', [$customer->id, $flag->id]) }}">@csrf @method('PATCH')<button class="btn btn-ghost btn-sm">Resolve flag</button></form>
        @else
          <small style="color:var(--muted)">Resolved {{ $flag->resolved_at->format('d M Y H:i') }} UTC · Admin #{{ $flag->resolved_by ?? '—' }}</small>
        @endif
      </div>
    @empty
      <p style="color:var(--muted)">No flags for this user.</p>
    @endforelse
  </div>
</div>
@endsection

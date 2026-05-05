@extends('admin.layout')
@section('title', 'Devices')
@section('page-title', 'Device Management')

@section('content')

{{-- Block by device ID --}}
<div class="card" style="margin-bottom:20px">
  <div class="card-title">Block All Tokens by Device ID</div>
  <form method="POST" action="{{ route('admin.devices.block-by-id') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
    @csrf
    <div class="form-group">
      <label>Device ID</label>
      <input type="text" name="device_id" placeholder="e.g. NRD90M or unique-device-id-12345" style="width:320px">
    </div>
    <button type="submit" class="btn btn-danger" onclick="return confirm('Block ALL tokens for this device ID?')">Block All Tokens</button>
  </form>
</div>

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="{{ $search }}" placeholder="Device ID, identifier, name…" style="width:260px">
  </div>
  <div class="form-group">
    <label>Status</label>
    <select name="blocked">
      <option value="">All Devices</option>
      <option value="0" {{ $blocked==='0'?'selected':'' }}>Active</option>
      <option value="1" {{ $blocked==='1'?'selected':'' }}>Blocked</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  @if($search || $blocked !== '')
    <div class="form-group" style="justify-content:flex-end">
      <a href="{{ route('admin.devices') }}" class="btn btn-ghost">Clear</a>
    </div>
  @endif
</form>

<div style="margin-bottom:12px;color:var(--muted);font-size:13px">{{ $devices->total() }} device token(s) found</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Device ID</th>
        <th>Identifier</th>
        <th>User ID</th>
        <th>Device Info</th>
        <th>Last Used</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    @forelse($devices as $device)
      @php
        $info = @json_decode($device->about_device, true);
        $model = $info['model'] ?? ($info['model'] ?? null);
        $brand = $info['brand'] ?? null;
        $os    = $info['version']['release'] ?? $info['os'] ?? null;
      @endphp
      <tr>
        <td style="color:var(--muted)">#{{ $device->id }}</td>
        <td style="font-size:12px;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace" title="{{ $device->device_id }}">{{ $device->device_id }}</td>
        <td style="font-size:12px;color:var(--muted)">{{ $device->identifier ?: '—' }}</td>
        <td style="color:var(--muted)">{{ $device->tokenable_id ?: '—' }}</td>
        <td style="font-size:12px;color:var(--muted)">
          @if($model) <span>{{ $brand ? $brand.' ' : '' }}{{ $model }}</span>@endif
          @if($os) <span style="display:block;font-size:11px">Android {{ $os }}</span>@endif
          @if(!$model && !$os) — @endif
        </td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">{{ $device->last_used_at ? \Carbon\Carbon::parse($device->last_used_at)->format('d M Y') : '—' }}</td>
        <td>
          @if($device->blocked)
            <span class="badge badge-red">Blocked</span>
          @else
            <span class="badge badge-green">Active</span>
          @endif
        </td>
        <td>
          <div style="display:flex;gap:6px">
            @if($device->blocked)
              <form method="POST" action="{{ route('admin.devices.unblock', $device->id) }}">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm">Unblock</button>
              </form>
            @else
              <form method="POST" action="{{ route('admin.devices.block', $device->id) }}" onsubmit="return confirm('Block this device token?')">
                @csrf @method('PATCH')
                <button class="btn btn-warning btn-sm">Block</button>
              </form>
            @endif
            <form method="POST" action="{{ route('admin.devices.delete', $device->id) }}" onsubmit="return confirm('Delete this device token?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No devices found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="pagination">{{ $devices->links('admin.pagination') }}</div>

@endsection

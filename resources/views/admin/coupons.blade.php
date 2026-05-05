@extends('admin.layout')
@section('title', 'Coupons')
@section('page-title', 'Coupons Management')

@section('content')

{{-- Create Coupon --}}
<div class="card" style="margin-bottom:24px">
  <div class="card-title">Create New Coupon</div>
  <form method="POST" action="{{ route('admin.coupons.create') }}">
    @csrf
    <div class="form-row" style="align-items:flex-end;flex-wrap:wrap">
      <div class="form-group">
        <label>Coupon Code *</label>
        <input type="text" name="code" required placeholder="e.g. SAVE20" style="width:160px;text-transform:uppercase" oninput="this.value=this.value.toUpperCase()">
      </div>
      <div class="form-group">
        <label>Discount Type</label>
        <select name="discount_type">
          <option value="percent">Percentage (%)</option>
          <option value="fixed_cart">Fixed Amount</option>
        </select>
      </div>
      <div class="form-group">
        <label>Amount *</label>
        <input type="number" name="amount" required min="0" step="0.01" placeholder="e.g. 20" style="width:110px">
      </div>
      <div class="form-group">
        <label>Usage Limit</label>
        <input type="number" name="usage_limit" min="1" placeholder="Unlimited" style="width:120px">
      </div>
      <div class="form-group">
        <label>Expires</label>
        <input type="date" name="date_expires" style="width:150px">
      </div>
      <div class="form-group">
        <label>Min Order</label>
        <input type="number" name="minimum_amount" min="0" step="0.01" placeholder="0" style="width:110px">
      </div>
      <div class="form-group">
        <label>Vendor Scope</label>
        <select name="vendor_id" style="width:180px">
          <option value="">Global / All Vendors</option>
          @foreach($vendors as $v)
            <option value="{{ $v->id }}">{{ $v->shop_name }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Create Coupon</button>
    </div>
    @if($errors->any())
      <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif
  </form>
</div>

{{-- Coupons Table --}}
<div style="margin-bottom:12px;color:var(--muted);font-size:13px">{{ $coupons->total() }} coupon(s)</div>
<div style="margin-bottom:12px;display:flex;gap:8px;flex-wrap:wrap">
  <a href="{{ route('admin.coupons') }}" class="btn btn-ghost btn-sm">All</a>
  <a href="{{ route('admin.coupons', ['vendor' => 'global']) }}" class="btn btn-ghost btn-sm">Global</a>
  @foreach($vendors as $v)
    <a href="{{ route('admin.coupons', ['vendor' => $v->id]) }}" class="btn btn-ghost btn-sm">{{ $v->shop_name }}</a>
  @endforeach
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Vendor</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Usage</th>
        <th>Min Order</th>
        <th>Expires</th>
        <th>Status</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    @forelse($coupons as $coupon)
      <tr>
        <td>
          <span style="font-family:monospace;font-weight:700;font-size:13px;color:var(--accent)">{{ $coupon->code }}</span>
        </td>
        <td style="font-size:12px;color:var(--muted)">{{ $coupon->vendor_shop_name ?? 'Global' }}</td>
        <td>
          <span class="badge {{ $coupon->discount_type === 'percent' ? 'badge-blue' : 'badge-orange' }}">
            {{ $coupon->discount_type === 'percent' ? 'Percentage' : 'Fixed' }}
          </span>
        </td>
        <td style="font-weight:700">
          {{ $coupon->discount_type === 'percent' ? $coupon->amount.'%' : number_format($coupon->amount, 2) }}
        </td>
        <td style="color:var(--muted)">
          {{ $coupon->usage_count }}
          @if($coupon->usage_limit) / {{ $coupon->usage_limit }} @else / ∞ @endif
          @if($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit)
            <span class="badge badge-red" style="margin-left:4px">Maxed</span>
          @endif
        </td>
        <td style="color:var(--muted);font-size:12px">
          {{ $coupon->minimum_amount > 0 ? number_format($coupon->minimum_amount, 2) : '—' }}
        </td>
        <td style="font-size:12px;color:var(--muted)">
          @if($coupon->date_expires)
            @php $exp = \Carbon\Carbon::parse($coupon->date_expires); @endphp
            <span style="{{ $exp->isPast() ? 'color:var(--red)' : '' }}">
              {{ $exp->format('d M Y') }}
              @if($exp->isPast()) <span class="badge badge-red" style="margin-left:2px">Expired</span>@endif
            </span>
          @else
            Never
          @endif
        </td>
        <td>
          <span class="badge {{ $coupon->status === 'publish' ? 'badge-green' : 'badge-gray' }}">
            {{ $coupon->status === 'publish' ? 'Active' : 'Disabled' }}
          </span>
        </td>
        <td style="font-size:12px;color:var(--muted);white-space:nowrap">
          {{ \Carbon\Carbon::parse($coupon->date_created)->format('d M Y') }}
        </td>
        <td>
          <div style="display:flex;gap:6px">
            <form method="POST" action="{{ route('admin.coupons.toggle', $coupon->id) }}">
              @csrf @method('PATCH')
              @if($coupon->status === 'publish')
                <button class="btn btn-warning btn-sm">Disable</button>
              @else
                <button class="btn btn-success btn-sm">Enable</button>
              @endif
            </form>
            <form method="POST" action="{{ route('admin.coupons.delete', $coupon->id) }}" onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:32px">No coupons yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="pagination">{{ $coupons->links('admin.pagination') }}</div>

@endsection

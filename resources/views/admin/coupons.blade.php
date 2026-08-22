@extends('admin.layout')
@section('title', 'Coupons')
@section('page-title', 'Coupons Management')

@section('content')

<style>
  .coupon-advanced-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:14px;padding-top:14px;border-top:1px solid var(--border)}
  .coupon-advanced-grid .form-group{min-width:0}
  .coupon-advanced-grid input,.coupon-advanced-grid textarea,.coupon-advanced-grid select{width:100%}
  .coupon-checks{display:flex;gap:16px;align-items:center;flex-wrap:wrap;margin-top:12px;color:var(--muted);font-size:12px}
  .coupon-checks label{display:inline-flex;align-items:center;gap:7px}
  .coupon-checks input{accent-color:var(--accent)}
  .coupon-edit-row>td{padding:0!important;border-top:0!important}
  .coupon-edit-panel{padding:18px;background:var(--panel);border:1px solid var(--border);border-radius:10px;margin:4px 0 12px}
  .coupon-edit-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:end}
  .coupon-edit-form .form-group{min-width:0}
  .coupon-edit-form input,.coupon-edit-form textarea,.coupon-edit-form select{width:100%}
  .coupon-edit-form textarea{min-height:68px;resize:vertical}
  .coupon-edit-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:14px}
  @media(max-width:1000px){.coupon-advanced-grid,.coupon-edit-form{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(max-width:650px){.coupon-advanced-grid,.coupon-edit-form{grid-template-columns:1fr}.coupon-edit-panel{min-width:680px}.table-wrap{overflow-x:auto}}
</style>

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
      <div class="coupon-advanced-grid" style="flex-basis:100%">
        <div class="form-group"><label>Per-user Limit</label><input type="number" name="usage_limit_per_user" min="1" placeholder="Unlimited"></div>
        <div class="form-group"><label>Items Limit</label><input type="number" name="limit_usage_to_x_items" min="1" placeholder="Unlimited"></div>
        <div class="form-group"><label>Max Discount</label><input type="number" name="maximum_amount" min="0" step="0.01" placeholder="0 = unlimited"></div>
        <div class="form-group"><label>Product IDs</label><input type="text" name="product_ids" placeholder="1, 2, 3"></div>
        <div class="form-group"><label>Excluded Product IDs</label><input type="text" name="excluded_product_ids" placeholder="1, 2, 3"></div>
        <div class="form-group"><label>Categories</label><input type="text" name="product_categories" placeholder="category-slug, another-slug"></div>
        <div class="form-group"><label>Excluded Categories</label><input type="text" name="excluded_product_categories" placeholder="category-slug"></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="2" placeholder="Customer-facing offer details"></textarea></div>
      </div>
      <div class="coupon-checks" style="flex-basis:100%">
        <label><input type="checkbox" name="free_shipping" value="1"> Free shipping</label>
        <label><input type="checkbox" name="exclude_sale_items" value="1"> Exclude sale items</label>
        <label><input type="checkbox" name="individual_use" value="1"> Individual use only</label>
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
            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('coupon-edit-{{ $coupon->id }}').open = true">Edit</button>
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
      @php
        $editProductIds = json_decode($coupon->product_ids ?? '[]', true) ?: [];
        $editExcludedProductIds = json_decode($coupon->excluded_product_ids ?? '[]', true) ?: [];
        $editCategories = json_decode($coupon->product_categories ?? '[]', true) ?: [];
        $editExcludedCategories = json_decode($coupon->excluded_product_categories ?? '[]', true) ?: [];
      @endphp
      <tr class="coupon-edit-row">
        <td colspan="10">
          <details id="coupon-edit-{{ $coupon->id }}">
            <summary style="cursor:pointer;color:var(--accent);font-weight:700;padding:12px 0">Edit full coupon settings</summary>
            <div class="coupon-edit-panel">
              <form method="POST" action="{{ route('admin.coupons.update', $coupon->id) }}">
                @csrf @method('PATCH')
                <div class="coupon-edit-form">
                  <div class="form-group"><label>Coupon Code *</label><input type="text" name="code" required maxlength="50" value="{{ $coupon->code }}" oninput="this.value=this.value.toUpperCase()"></div>
                  <div class="form-group"><label>Discount Type</label><select name="discount_type"><option value="percent" @selected($coupon->discount_type === 'percent')>Percentage (%)</option><option value="fixed_cart" @selected($coupon->discount_type === 'fixed_cart')>Fixed Amount</option></select></div>
                  <div class="form-group"><label>Amount *</label><input type="number" name="amount" required min="0" step="0.01" value="{{ $coupon->amount }}"></div>
                  <div class="form-group"><label>Vendor Scope</label><select name="vendor_id"><option value="">Global / All Vendors</option>@foreach($vendors as $v)<option value="{{ $v->id }}" @selected((int) $coupon->vendor_id === (int) $v->id)>{{ $v->shop_name }}</option>@endforeach</select></div>
                  <div class="form-group"><label>Usage Limit</label><input type="number" name="usage_limit" min="1" value="{{ $coupon->usage_limit }}" placeholder="Unlimited"></div>
                  <div class="form-group"><label>Per-user Limit</label><input type="number" name="usage_limit_per_user" min="1" value="{{ $coupon->usage_limit_per_user }}" placeholder="Unlimited"></div>
                  <div class="form-group"><label>Items Limit</label><input type="number" name="limit_usage_to_x_items" min="1" value="{{ $coupon->limit_usage_to_x_items }}" placeholder="Unlimited"></div>
                  <div class="form-group"><label>Expires</label><input type="date" name="date_expires" value="{{ $coupon->date_expires ? \Carbon\Carbon::parse($coupon->date_expires)->format('Y-m-d') : '' }}"></div>
                  <div class="form-group"><label>Min Order</label><input type="number" name="minimum_amount" min="0" step="0.01" value="{{ $coupon->minimum_amount }}"></div>
                  <div class="form-group"><label>Max Discount</label><input type="number" name="maximum_amount" min="0" step="0.01" value="{{ $coupon->maximum_amount }}" placeholder="0 = unlimited"></div>
                  <div class="form-group"><label>Product IDs</label><input type="text" name="product_ids" value="{{ implode(', ', $editProductIds) }}" placeholder="1, 2, 3"></div>
                  <div class="form-group"><label>Excluded Product IDs</label><input type="text" name="excluded_product_ids" value="{{ implode(', ', $editExcludedProductIds) }}" placeholder="1, 2, 3"></div>
                  <div class="form-group"><label>Categories</label><input type="text" name="product_categories" value="{{ implode(', ', $editCategories) }}" placeholder="category-slug"></div>
                  <div class="form-group"><label>Excluded Categories</label><input type="text" name="excluded_product_categories" value="{{ implode(', ', $editExcludedCategories) }}" placeholder="category-slug"></div>
                  <div class="form-group" style="grid-column:span 2"><label>Description</label><textarea name="description" rows="2" placeholder="Customer-facing offer details">{{ $coupon->description }}</textarea></div>
                </div>
                <div class="coupon-checks">
                  <label><input type="checkbox" name="free_shipping" value="1" @checked($coupon->free_shipping)> Free shipping</label>
                  <label><input type="checkbox" name="exclude_sale_items" value="1" @checked($coupon->exclude_sale_items)> Exclude sale items</label>
                  <label><input type="checkbox" name="individual_use" value="1" @checked($coupon->individual_use)> Individual use only</label>
                </div>
                <div class="coupon-edit-actions">
                  <button type="submit" class="btn btn-primary btn-sm">Save Coupon</button>
                  <span style="font-size:12px;color:var(--muted)">Usage count is system-managed: {{ $coupon->usage_count }}</span>
                </div>
              </form>
            </div>
          </details>
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

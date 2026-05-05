@extends('admin.layout')
@section('title', 'Products')
@section('page-title', 'Products Management')

@section('content')

<form method="GET" class="form-row">
  <div class="form-group">
    <label>Search</label>
    <input type="search" name="search" value="{{ $search }}" placeholder="Product name…" style="width:220px">
  </div>
  <div class="form-group">
    <label>Approval</label>
    <select name="acceptance">
      <option value="">All</option>
      <option value="pending" {{ $acceptance=='pending'?'selected':'' }}>Pending</option>
      <option value="approved" {{ $acceptance=='approved'?'selected':'' }}>Approved</option>
      <option value="rejected" {{ $acceptance=='rejected'?'selected':'' }}>Rejected</option>
    </select>
  </div>
  <div class="form-group">
    <label>Visibility</label>
    <select name="status">
      <option value="">All</option>
      <option value="publish" {{ $status=='publish'?'selected':'' }}>Published</option>
      <option value="draft" {{ $status=='draft'?'selected':'' }}>Draft</option>
      <option value="private" {{ $status=='private'?'selected':'' }}>Private</option>
    </select>
  </div>
  <div class="form-group" style="justify-content:flex-end">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
  @if($search || $acceptance || $status)
    <div class="form-group" style="justify-content:flex-end">
      <a href="{{ route('admin.products') }}" class="btn btn-ghost">Clear</a>
    </div>
  @endif
</form>

<form method="POST" action="{{ route('admin.products.bulk') }}" id="bulk-products-form">
  @csrf
  <div style="margin:10px 0 12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select name="bulk_action" class="filter-select" style="max-width:180px">
      <option value="">Bulk actions</option>
      <option value="approve">Approve</option>
      <option value="reject">Reject</option>
      <option value="delete">Delete</option>
    </select>
    <button type="submit" class="btn btn-primary" onclick="return confirmBulkProducts()">Apply</button>
    <span style="color:var(--muted);font-size:13px">{{ $products->total() }} product(s) found</span>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:34px"><input type="checkbox" id="select-all-products" onchange="document.querySelectorAll('.product-check').forEach(cb => cb.checked = this.checked)"></th>
          <th>ID</th>
          <th>Name</th>
          <th>Shop</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Approval</th>
          <th>Visibility</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($products as $product)
        @php
          $ac = match($product->acceptance_status) {
            'approved'=>'badge-green','pending'=>'badge-yellow','rejected'=>'badge-red',default=>'badge-gray'
          };
          $sc = match($product->status) {
            'publish'=>'badge-blue','draft'=>'badge-gray','private'=>'badge-orange',default=>'badge-gray'
          };
        @endphp
        <tr>
          <td><input type="checkbox" class="product-check" name="ids[]" value="{{ $product->id }}"></td>
          <td style="color:var(--muted)">#{{ $product->id }}</td>
          <td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $product->name }}</td>
          <td style="color:var(--muted);font-size:12px">{{ $product->shop_name ?? '—' }}</td>
          <td style="font-weight:600">
            @if($product->min_price == $product->max_price)
              {{ number_format($product->min_price ?? 0, 2) }}
            @else
              {{ number_format($product->min_price ?? 0, 2) }}–{{ number_format($product->max_price ?? 0, 2) }}
            @endif
          </td>
          <td>
            <span class="badge {{ $product->stock_status === 'instock' ? 'badge-green' : 'badge-red' }}">
              {{ $product->stock_status === 'instock' ? 'In Stock' : 'Out' }}
            </span>
          </td>
          <td><span class="badge {{ $ac }}">{{ ucfirst($product->acceptance_status) }}</span></td>
          <td>
            <form method="POST" action="{{ route('admin.products.toggle', $product->id) }}" style="display:flex;gap:4px">
              @csrf @method('PATCH')
              <select name="status" style="padding:4px 6px;font-size:12px;height:28px">
                <option value="publish" {{ $product->status=='publish'?'selected':'' }}>Publish</option>
                <option value="draft" {{ $product->status=='draft'?'selected':'' }}>Draft</option>
                <option value="private" {{ $product->status=='private'?'selected':'' }}>Private</option>
              </select>
              <button class="btn btn-ghost btn-sm">Set</button>
            </form>
          </td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-secondary btn-sm">View</a>
              @if($product->acceptance_status !== 'approved')
                <form method="POST" action="{{ route('admin.products.approve', $product->id) }}">
                  @csrf @method('PATCH')
                  <button class="btn btn-success btn-sm">Approve</button>
                </form>
              @endif
              @if($product->acceptance_status !== 'rejected')
                <form method="POST" action="{{ route('admin.products.reject', $product->id) }}" onsubmit="return confirm('Reject this product?')">
                  @csrf @method('PATCH')
                  <button class="btn btn-warning btn-sm">Reject</button>
                </form>
              @endif
              <form method="POST" action="{{ route('admin.products.delete', $product->id) }}" onsubmit="return confirm('Delete this product?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No products found.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</form>

<div class="pagination">{{ $products->links('admin.pagination') }}</div>

<script>
function confirmBulkProducts() {
  const action = document.querySelector('select[name="bulk_action"]').value;
  const checked = document.querySelectorAll('.product-check:checked').length;
  if (!action) { alert('Choose a bulk action.'); return false; }
  if (!checked) { alert('Select at least one product.'); return false; }
  if (action === 'delete') return confirm('Delete selected products? This cannot be undone.');
  return confirm('Apply this bulk action to selected products?');
}
</script>

@endsection
@extends('web.vendor.layout')
@section('title', 'Seller Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- STATUS BANNER --}}
@if(in_array($vendor->status, ['approved', 'active']))
  <div class="vs-alert vs-alert-success" style="margin-bottom:20px">
    ✓ Your store is <strong>live</strong>. Customers can browse your products.
    <a href="{{ route('vendor.store', $vendor->id) }}" target="_blank" style="margin-left:10px;font-weight:700;text-decoration:underline">View My Store →</a>
  </div>
@elseif($vendor->status === 'pending')
  <div class="vs-alert vs-alert-warning" style="margin-bottom:20px">
    ⏳ Your application is <strong>under review</strong>. Our team will approve your store shortly.
  </div>
@elseif($vendor->status === 'rejected')
  <div class="vs-alert vs-alert-error" style="margin-bottom:20px">
    ✕ Your application was <strong>not approved</strong>. Please contact support for more information.
  </div>
@else
  <div class="vs-alert vs-alert-warning" style="margin-bottom:20px">
    ⚠ Your store status is <strong>{{ ucfirst($vendor->status) }}</strong>. Please contact support if you think this is a mistake.
  </div>
@endif

{{-- STATS --}}
<div class="vs-stat-grid">
  <div class="vs-stat-card">
    <div class="vs-stat-icon">📦</div>
    <div class="vs-stat-value">{{ $stats['products'] }}</div>
    <div class="vs-stat-label">Products Listed</div>
  </div>
  <div class="vs-stat-card">
    <div class="vs-stat-icon">🛍️</div>
    <div class="vs-stat-value">{{ $stats['orders'] }}</div>
    <div class="vs-stat-label">Total Orders</div>
  </div>
  <div class="vs-stat-card">
    <div class="vs-stat-icon">⭐</div>
    <div class="vs-stat-value">{{ number_format((float)$stats['rating'], 1) }}</div>
    <div class="vs-stat-label">Avg Rating</div>
  </div>
</div>

{{-- SHOP INFO --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
  <div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin-bottom:14px">Shop Details</div>
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
      @if($vendor->shop_logo_url)
        <img src="{{ $vendor->shop_logo_url }}" alt="" style="width:60px;height:60px;border-radius:12px;object-fit:cover;border:1px solid var(--light)">
      @else
        <div style="width:60px;height:60px;border-radius:12px;background:#f0f0ec;display:flex;align-items:center;justify-content:center;font-size:24px">🏪</div>
      @endif
      <div>
        <div style="font-size:16px;font-weight:700">{{ $vendor->shop_name }}</div>
        <span class="badge-{{ $vendor->status }}">{{ ucfirst($vendor->status) }}</span>
      </div>
    </div>
    <div style="font-size:13px;color:var(--mid);display:flex;flex-direction:column;gap:6px">
      <div>📧 {{ $vendor->email }}</div>
      <div>📞 {{ $vendor->phone }}</div>
      @if($vendor->shop_address)
        <div>📍 {{ $vendor->shop_address }}</div>
      @endif
    </div>
  </div>

  <div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin-bottom:14px">Quick Actions</div>
    <div style="display:flex;flex-direction:column;gap:10px">
      <a href="{{ route('vendor.store', $vendor->id) }}" target="_blank"
         style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fafaf8;border:1px solid var(--light);border-radius:9px;font-size:13px;font-weight:600;transition:.15s"
         onmouseover="this.style.borderColor='#e85d26'" onmouseout="this.style.borderColor='var(--light)'">
        🌐 <span>View My Public Store</span>
      </a>
      <a href="{{ route('shop') }}" target="_blank"
         style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fafaf8;border:1px solid var(--light);border-radius:9px;font-size:13px;font-weight:600;transition:.15s"
         onmouseover="this.style.borderColor='#e85d26'" onmouseout="this.style.borderColor='var(--light)'">
        🛒 <span>Browse the Marketplace</span>
      </a>
      <div style="padding:12px 14px;background:#fff9f5;border:1px solid #fde8d8;border-radius:9px;font-size:12px;color:#92400e">
        💡 To add products, ask your account manager or use the seller API.
      </div>
    </div>
  </div>
</div>

{{-- RECENT PRODUCTS --}}
@if($recentProducts->count())
<div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:20px">
  <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--mid);margin-bottom:16px">Recent Products</div>
  <div class="vs-table-wrap">
    <table class="vs-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentProducts as $p)
        <tr>
          <td style="font-weight:600">{{ $p->name }}</td>
          <td>
            @if($p->on_sale && $p->min_sale > 0)
              <span style="font-weight:700;color:var(--orange)">{{ number_format($p->min_sale,2) }} EGP</span>
              <span style="text-decoration:line-through;color:var(--mid);font-size:11px;margin-left:4px">{{ number_format($p->min_regular,2) }}</span>
            @else
              {{ number_format($p->min_regular ?? $p->min_sale ?? 0, 2) }} EGP
            @endif
          </td>
          <td>
            @php $st = $p->status ?? 'active'; @endphp
            <span class="badge-{{ $st === 'active' ? 'approved' : ($st === 'pending' ? 'pending' : 'blocked') }}">
              {{ ucfirst($st) }}
            </span>
          </td>
          <td><a href="{{ route('product', $p->id) }}" target="_blank" style="font-size:12px;color:var(--orange);font-weight:600">View →</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@else
  <div style="background:#fff;border:1px solid var(--light);border-radius:12px;padding:32px;text-align:center;color:var(--mid)">
    <div style="font-size:36px;margin-bottom:10px">📦</div>
    <div style="font-weight:600;margin-bottom:4px">No products yet</div>
    <div style="font-size:13px">Once your store is approved, your products will appear here.</div>
  </div>
@endif

@endsection

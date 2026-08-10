@extends('admin.layout')
@section('title', 'Analytics')
@section('page-title', 'Analytics & Reports')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- KPI Cards --}}
<div class="stat-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
  <div class="stat-card">
    <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
    <div class="stat-value">{{ number_format($kpis['total_revenue'], 0) }}</div>
    <div class="stat-label">Total Revenue</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div>
    <div class="stat-value">{{ number_format($kpis['total_orders']) }}</div>
    <div class="stat-label">Total Orders</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
    <div class="stat-value">{{ number_format($kpis['total_users']) }}</div>
    <div class="stat-label">Total Users</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
    <div class="stat-value">{{ number_format($kpis['avg_order_value'], 2) }}</div>
    <div class="stat-label">Avg Order Value</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
    <div class="stat-value">{{ $kpis['completed_orders'] }}</div>
    <div class="stat-label">Completed Orders</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
    <div class="stat-value">{{ $kpis['cancelled_orders'] }}</div>
    <div class="stat-label">Cancelled Orders</div>
  </div>
</div>

{{-- Charts Row 1 --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">

  {{-- User Growth --}}
  <div class="card">
    <div class="card-title">User Registrations by Month</div>
    <canvas id="userGrowthChart" height="100"></canvas>
  </div>

  {{-- Orders by Status --}}
  <div class="card">
    <div class="card-title">Orders by Status</div>
    <canvas id="orderStatusChart" height="180"></canvas>
    <div id="statusLegend" style="margin-top:12px;display:flex;flex-direction:column;gap:6px"></div>
  </div>

</div>

{{-- Charts Row 2 --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

  {{-- Revenue over Time --}}
  <div class="card">
    <div class="card-title">Revenue by Month</div>
    <canvas id="revenueChart" height="130"></canvas>
  </div>

  {{-- Payment Methods --}}
  <div class="card">
    <div class="card-title">Payment Methods</div>
    <canvas id="paymentChart" height="130"></canvas>
    <div id="paymentLegend" style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px"></div>
  </div>

</div>

{{-- Tables Row --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  {{-- Top Products --}}
  <div class="card">
    <div class="card-title">Products Overview</div>
    <div class="table-wrap" style="border:none">
      <table>
        <thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($top_products as $p)
          <tr>
            <td style="font-weight:600;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->name }}</td>
            <td>@if($p->max_price !== null && $p->max_price > $p->min_price){{ number_format($p->min_price) }} – {{ number_format($p->max_price) }}@else{{ number_format($p->min_price) }}@endif</td>
            <td><span class="badge {{ $p->stock_status === 'instock' ? 'badge-green' : 'badge-red' }}">{{ $p->stock_status === 'instock' ? 'In Stock' : 'Out' }}</span></td>
            <td><span class="badge {{ $p->acceptance_status === 'approved' ? 'badge-green' : ($p->acceptance_status === 'pending' ? 'badge-yellow' : 'badge-red') }}">{{ ucfirst($p->acceptance_status) }}</span></td>
          </tr>
        @empty
          <tr><td colspan="4" style="color:var(--muted);text-align:center">No products yet.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- User Roles Breakdown --}}
  <div class="card">
    <div class="card-title">User Roles Breakdown</div>
    <canvas id="userRolesChart" height="160"></canvas>
    <div style="margin-top:16px">
      @foreach($user_roles as $r)
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)">
        <span style="font-size:13px;color:var(--muted)">{{ $r->label }}</span>
        <span style="font-weight:700">{{ number_format($r->cnt) }}</span>
      </div>
      @endforeach
    </div>
  </div>

</div>

<script>
const palette = ['#e85d26','#3b82f6','#22c55e','#eab308','#a855f7','#06b6d4','#f43f5e','#84cc16'];

// ── User Growth Chart ──
const ugLabels = @json($user_growth->pluck('month'));
const ugData   = @json($user_growth->pluck('cnt'));

new Chart(document.getElementById('userGrowthChart'), {
  type: 'bar',
  data: {
    labels: ugLabels,
    datasets: [{
      label: 'New Users',
      data: ugData,
      backgroundColor: 'rgba(232,93,38,.7)',
      borderColor: '#e85d26',
      borderWidth: 1,
      borderRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8892a4' } },
      y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8892a4', precision: 0 } }
    }
  }
});

// ── Order Status Doughnut ──
const osLabels = @json($order_statuses->pluck('status'));
const osData   = @json($order_statuses->pluck('cnt'));
const osColors = osLabels.map((l,i) => ({
  'completed':'#22c55e','pending':'#eab308','processing':'#3b82f6',
  'cancelled':'#ef4444','refunded':'#f97316','failed':'#ef4444','on-hold':'#a855f7'
}[l] ?? palette[i % palette.length]));

if (osData.length > 0) {
  new Chart(document.getElementById('orderStatusChart'), {
    type: 'doughnut',
    data: { labels: osLabels, datasets: [{ data: osData, backgroundColor: osColors, borderWidth: 2, borderColor: '#1e2435' }] },
    options: {
      responsive: true, cutout: '65%',
      plugins: { legend: { display: false } }
    }
  });
  const leg = document.getElementById('statusLegend');
  osLabels.forEach((l,i) => {
    leg.innerHTML += `<div style="display:flex;align-items:center;gap:6px;font-size:12px">
      <span style="width:10px;height:10px;border-radius:50%;background:${osColors[i]};flex-shrink:0"></span>
      <span style="color:#8892a4">${l}</span>
      <span style="margin-left:auto;font-weight:700">${osData[i]}</span>
    </div>`;
  });
} else {
  document.getElementById('orderStatusChart').parentElement.innerHTML += '<p style="color:var(--muted);font-size:13px;margin-top:8px">No order data yet.</p>';
}

// ── Revenue Chart ──
const revLabels = @json($revenue_by_month->pluck('month'));
const revData   = @json($revenue_by_month->pluck('revenue'));

new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: revLabels.length ? revLabels : ['No data'],
    datasets: [{
      label: 'Revenue',
      data: revData.length ? revData : [0],
      borderColor: '#22c55e',
      backgroundColor: 'rgba(34,197,94,.1)',
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#22c55e',
      pointRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8892a4' } },
      y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8892a4' } }
    }
  }
});

// ── Payment Methods ──
const pmLabels = @json($payment_methods->pluck('method'));
const pmData   = @json($payment_methods->pluck('cnt'));

if (pmData.length > 0) {
  new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: { labels: pmLabels, datasets: [{ data: pmData, backgroundColor: palette, borderWidth: 2, borderColor: '#1e2435' }] },
    options: {
      responsive: true, cutout: '60%',
      plugins: { legend: { display: false } }
    }
  });
  const pl = document.getElementById('paymentLegend');
  pmLabels.forEach((l,i) => {
    pl.innerHTML += `<div style="display:flex;align-items:center;gap:5px;font-size:12px">
      <span style="width:10px;height:10px;border-radius:2px;background:${palette[i % palette.length]};flex-shrink:0"></span>
      <span style="color:#8892a4">${l || 'Unknown'}</span>
      <span style="font-weight:700;margin-left:4px">${pmData[i]}</span>
    </div>`;
  });
}

// ── User Roles ──
const urLabels = @json($user_roles->pluck('label'));
const urData   = @json($user_roles->pluck('cnt'));

new Chart(document.getElementById('userRolesChart'), {
  type: 'doughnut',
  data: { labels: urLabels, datasets: [{ data: urData, backgroundColor: ['#3b82f6','#e85d26','#22c55e','#eab308'], borderWidth: 2, borderColor: '#1e2435' }] },
  options: {
    responsive: true, cutout: '60%',
    plugins: { legend: { labels: { color: '#8892a4', font: { size: 12 } } } }
  }
});
</script>

@endsection

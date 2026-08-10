<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin') — Ramo Store</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0f1117;--sidebar:#161b27;--card:#1e2435;--border:#2a3347;
  --accent:#e85d26;--accent2:#f97316;--text:#e2e8f0;--muted:#8892a4;
  --green:#22c55e;--red:#ef4444;--yellow:#eab308;--blue:#3b82f6;
  --radius:10px;
}
html{scroll-behavior:smooth}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;font-size:14px}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:inherit}
.sidebar{width:240px;height:100vh;min-height:100vh;background:var(--sidebar);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;z-index:50;overflow:hidden}
.sidebar-logo{padding:20px 20px 16px;border-bottom:1px solid var(--border)}
.sidebar-logo a{display:flex;align-items:center;gap:10px;font-size:17px;font-weight:800;color:var(--text)}
.sidebar-logo span{color:var(--accent)}
.sidebar-nav{flex:1;min-height:0;padding:12px 0;overflow-y:auto;overflow-x:hidden}
.nav-section{padding:16px 16px 6px;font-size:10px;font-weight:700;letter-spacing:.08em;color:var(--muted);text-transform:uppercase}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 20px;color:var(--muted);font-size:13.5px;font-weight:500;transition:.15s;position:relative}
.nav-item:hover{color:var(--text);background:rgba(255,255,255,.04)}
.nav-item.active{color:var(--accent);background:rgba(232,93,38,.08)}
.nav-item.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--accent);border-radius:0 3px 3px 0}
.nav-item svg{width:16px;height:16px;flex-shrink:0}
.nav-badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;padding:1px 6px;border-radius:20px;font-weight:700}
.nav-badge.yellow{background:var(--yellow);color:#000}
.nav-counts{margin-left:auto;display:flex;align-items:center;gap:5px;flex-shrink:0}
.nav-count{background:rgba(255,255,255,.07);color:var(--muted);font-size:10.5px;line-height:1;padding:3px 7px;border-radius:20px;font-weight:700;font-variant-numeric:tabular-nums}
.nav-item:hover .nav-count{background:rgba(255,255,255,.12);color:var(--text)}
.nav-item.active .nav-count{background:rgba(232,93,38,.22);color:var(--accent)}
.nav-count.pending{background:rgba(239,68,68,.18);color:#fca5a5}
.nav-item.active .nav-count.pending,.nav-item:hover .nav-count.pending{background:rgba(239,68,68,.28);color:#fecaca}
.sidebar-bottom{padding:16px;border-top:1px solid var(--border)}
.sidebar-user{display:flex;align-items:center;gap:10px}
.sidebar-user-avatar{width:32px;height:32px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
.sidebar-user-info{min-width:0}
.sidebar-user-name{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sidebar-user-role{font-size:11px;color:var(--muted)}
.main{margin-left:240px;flex:1;min-height:100vh;display:flex;flex-direction:column}
.topbar{padding:16px 28px;border-bottom:1px solid var(--border);background:var(--sidebar);display:flex;align-items:center;justify-content:space-between;gap:16px;position:sticky;top:0;z-index:40}
.topbar-title{font-size:18px;font-weight:700}
.topbar-actions{display:flex;align-items:center;gap:10px}
.content{padding:28px;flex:1}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px}
.card-title{font-size:13px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:16px}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:16px;margin-bottom:24px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px;display:flex;flex-direction:column;gap:6px}
.stat-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:4px}
.stat-icon svg{width:18px;height:18px}
.stat-icon.orange{background:rgba(232,93,38,.15);color:var(--accent)}
.stat-icon.green{background:rgba(34,197,94,.15);color:var(--green)}
.stat-icon.red{background:rgba(239,68,68,.15);color:var(--red)}
.stat-icon.blue{background:rgba(59,130,246,.15);color:var(--blue)}
.stat-icon.yellow{background:rgba(234,179,8,.15);color:var(--yellow)}
.stat-value{font-size:26px;font-weight:800;line-height:1}
.stat-label{font-size:12px;color:var(--muted)}
.stat-sub{font-size:11px;color:var(--muted);margin-top:2px}
.table-wrap{overflow-x:auto;border-radius:var(--radius);border:1px solid var(--border)}
table{width:100%;border-collapse:collapse;background:var(--card)}
thead{background:rgba(255,255,255,.03)}
th{padding:11px 14px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted);white-space:nowrap;border-bottom:1px solid var(--border)}
td{padding:11px 14px;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,.02)}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap}
.badge-green{background:rgba(34,197,94,.15);color:var(--green)}
.badge-red{background:rgba(239,68,68,.15);color:var(--red)}
.badge-yellow{background:rgba(234,179,8,.15);color:var(--yellow)}
.badge-blue{background:rgba(59,130,246,.15);color:var(--blue)}
.badge-purple{background:rgba(139,92,246,.15);color:#7c3aed}
.badge-gray{background:rgba(255,255,255,.08);color:var(--muted)}
.badge-orange{background:rgba(232,93,38,.15);color:var(--accent)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:7px;font-size:13px;font-weight:600;border:none;transition:.15s;cursor:pointer;white-space:nowrap}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover{background:var(--accent2)}
.btn-sm{padding:4px 10px;font-size:12px;border-radius:5px}
.btn-danger{background:rgba(239,68,68,.15);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:var(--red);color:#fff}
.btn-success{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.2)}
.btn-success:hover{background:var(--green);color:#fff}
.btn-warning{background:rgba(234,179,8,.15);color:var(--yellow);border:1px solid rgba(234,179,8,.2)}
.btn-warning:hover{background:var(--yellow);color:#000}
.btn-ghost{background:rgba(255,255,255,.06);color:var(--text);border:1px solid var(--border)}
.btn-ghost:hover{background:rgba(255,255,255,.1)}
.form-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:flex-end}
.form-group{display:flex;flex-direction:column;gap:4px}
.form-group label{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
input[type=text],input[type=email],input[type=search],select,textarea{
  background:var(--card);border:1px solid var(--border);color:var(--text);padding:8px 12px;border-radius:7px;font-size:13px;outline:none;transition:.15s;font-family:inherit}
input:focus,select:focus,textarea:focus{border-color:var(--accent)}
select option{background:var(--card)}
.alert{padding:12px 16px;border-radius:8px;font-size:13px;font-weight:500;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:var(--green)}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:var(--red)}
.pagination{display:flex;gap:6px;align-items:center;justify-content:center;margin-top:20px;flex-wrap:wrap}
.pagination a,.pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 10px;border-radius:6px;font-size:13px;font-weight:500;border:1px solid var(--border);background:var(--card);color:var(--muted);transition:.15s}
.pagination a:hover{border-color:var(--accent);color:var(--accent)}
.pagination span.current{background:var(--accent);color:#fff;border-color:var(--accent)}
.pagination span.dots{background:transparent;border:none}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.detail-row{display:flex;flex-direction:column;gap:2px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05)}
.detail-row:last-child{border-bottom:none}
.detail-label{font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase}
.detail-value{font-size:14px;font-weight:500}
@media(max-width:900px){.sidebar{transform:translateX(-100%)}.main{margin-left:0}}
</style>
@stack('styles')
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="{{ route('admin.dashboard') }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Ramo<span>Admin</span>
    </a>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Overview</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Dashboard
    </a>
    <div class="nav-section">Management</div>
    <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      Users
      @include('admin.partials.nav-count', ['key' => 'users'])
    </a>
    <a href="{{ route('admin.orders') }}" class="nav-item {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Orders
      @include('admin.partials.nav-count', ['key' => 'orders'])
    </a>
    <a href="{{ route('admin.vendors') }}" class="nav-item {{ request()->routeIs('admin.vendors*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Vendors
      @include('admin.partials.nav-count', ['key' => 'vendors'])
    </a>
    <a href="{{ route('admin.products') }}" class="nav-item {{ request()->routeIs('admin.products') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
      Products
      @include('admin.partials.nav-count', ['key' => 'products'])
    </a>
    <a href="{{ route('admin.devices') }}" class="nav-item {{ request()->routeIs('admin.devices') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
      Devices
      @include('admin.partials.nav-count', ['key' => 'devices'])
    </a>
    <a href="{{ route('admin.coupons') }}" class="nav-item {{ request()->routeIs('admin.coupons') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
      Coupons
      @include('admin.partials.nav-count', ['key' => 'coupons'])
    </a>
    <a href="{{ route('admin.refunds') }}" class="nav-item {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
      Refunds
      @include('admin.partials.nav-count', ['key' => 'refunds'])
    </a>
    <a href="{{ route('admin.reviews') }}" class="nav-item {{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      Reviews
      @include('admin.partials.nav-count', ['key' => 'reviews'])
    </a>
    <a href="{{ route('admin.cbr') }}" class="nav-item {{ request()->routeIs('admin.cbr') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2z"/><path d="M7 7h.01"/></svg>
      Cat & Brand Requests
      @include('admin.partials.nav-count', ['key' => 'requests'])
    </a>
    <div class="nav-section">Content & Analytics</div>
    <a href="{{ route('admin.timeline') }}" class="nav-item {{ request()->routeIs('admin.timeline') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="4" rx="1"/><rect x="3" y="10" width="11" height="4" rx="1"/><rect x="3" y="17" width="14" height="4" rx="1"/></svg>
      Homepage Builder
    </a>
    <a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      Analytics
    </a>
    <a href="{{ route('admin.configs') }}" class="nav-item {{ request()->routeIs('admin.configs') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 115.64 19.07M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
      App Configs
    </a>
    <a href="{{ route('admin.auth-settings') }}" class="nav-item {{ request()->routeIs('admin.auth-settings') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      Auth Settings
    </a>
    <a href="{{ route('admin.shipping-settings') }}" class="nav-item {{ request()->routeIs('admin.shipping-settings') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      Shipping Settings
    </a>
    <a href="{{ route('admin.payment-methods') }}" class="nav-item {{ request()->routeIs('admin.payment-methods') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg>
      Payment Methods
    </a>
  </nav>
  <div class="sidebar-bottom">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
        <div class="sidebar-user-role">Administrator</div>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:12px">
      @csrf
      <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
      </button>
    </form>
  </div>
</aside>
<div class="main">
  <div class="topbar">
    <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
    <div class="topbar-actions">@yield('topbar-actions')</div>
  </div>
  <div class="content">
    @if(session('success'))
      <div class="alert alert-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
      </div>
    @endif
    @yield('content')
  </div>
</div>
</body>
</html>
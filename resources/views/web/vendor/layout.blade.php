<!DOCTYPE html>
@php
  $sellerArabic = request()->routeIs('vendor.dashboard') && session('locale') === 'ar';
  $sellerText = static fn ($en, $ar) => $sellerArabic ? $ar : $en;
@endphp
<html lang="{{ $sellerText('en', 'ar') }}" dir="{{ $sellerText('ltr', 'rtl') }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Vendor Portal') — RamoStore</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#f7f7f5;--white:#fff;--dark:#1a1a1a;--mid:#6b7280;
  --light:#e5e7eb;--orange:#e85d26;--orange2:#d44f1a;
  --green:#16a34a;--red:#dc2626;--yellow:#d97706;
  --sidebar:#1e2435;--sidebar-text:#e2e8f0;--sidebar-muted:#8892a4;
  --border:#2a3347;--radius:10px;
}
html{scroll-behavior:smooth}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--dark);min-height:100vh;display:flex;font-size:14px}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:inherit}
input,select,textarea{font-family:inherit}

/* SIDEBAR */
.vs-sidebar{width:230px;background:var(--sidebar);display:flex;flex-direction:column;position:fixed;inset-block:0;inset-inline-start:0;z-index:50;border-inline-end:1px solid var(--border)}
.vs-logo{padding:20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.vs-logo-text{font-size:16px;font-weight:800;color:var(--sidebar-text)}
.vs-logo-text span{color:var(--orange)}
.vs-nav{flex:1;padding:12px 0;overflow-y:auto}
.vs-nav-label{padding:14px 18px 5px;font-size:10px;font-weight:700;letter-spacing:.08em;color:var(--sidebar-muted);text-transform:uppercase}
.vs-nav-item{display:flex;align-items:center;gap:10px;padding:9px 18px;color:var(--sidebar-muted);font-size:13px;font-weight:500;transition:.15s;position:relative}
.vs-nav-item:hover{color:var(--sidebar-text);background:rgba(255,255,255,.04)}
.vs-nav-item.active{color:var(--orange);background:rgba(232,93,38,.08)}
.vs-nav-item.active::before{content:'';position:absolute;inset-inline-start:0;inset-block:0;width:3px;background:var(--orange);border-radius:3px}
.vs-bottom{padding:16px;border-top:1px solid var(--border)}
.vs-vendor-info{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.vs-vendor-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0;overflow:hidden}
.vs-vendor-name{font-size:12px;font-weight:700;color:var(--sidebar-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vs-vendor-status{font-size:11px;color:var(--sidebar-muted)}

/* MAIN */
.vs-main{margin-inline-start:230px;flex:1;min-inline-size:0;min-height:100vh;display:flex;flex-direction:column}
.vs-topbar{padding:14px 24px;border-bottom:1px solid var(--light);background:var(--white);display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:40}
.vs-topbar-title{font-size:17px;font-weight:700;flex:1;min-inline-size:0}
.vs-topbar-action{font-size:13px;color:var(--mid)}
.vs-workspace-exit{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.vs-workspace-label{font-size:12px;font-weight:700;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:999px;padding:6px 10px;overflow-wrap:anywhere}
.vs-content{padding:24px;flex:1}
.vs-mobile-menu{display:none}

/* CARDS */
.vs-stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-bottom:24px}
.vs-stat-card{background:var(--white);border:1px solid var(--light);border-radius:12px;padding:18px;display:flex;flex-direction:column;gap:4px}
.vs-stat-icon{font-size:22px;margin-bottom:4px}
.vs-stat-value{font-size:26px;font-weight:800;color:var(--dark)}
.vs-stat-label{font-size:12px;color:var(--mid)}

/* ALERTS */
.vs-alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px}
.vs-alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:var(--green)}
.vs-alert-error{background:#fef2f2;border:1px solid #fecaca;color:var(--red)}
.vs-alert-warning{background:#fffbeb;border:1px solid #fde68a;color:var(--yellow)}

/* TABLE */
.vs-table-wrap{overflow-x:auto;border-radius:10px;border:1px solid var(--light);background:var(--white)}
table.vs-table{width:100%;border-collapse:collapse}
table.vs-table thead{background:#fafaf8}
table.vs-table th{padding:10px 14px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--mid);border-bottom:1px solid var(--light)}
table.vs-table td{padding:10px 14px;border-bottom:1px solid #f3f4f6;font-size:13px;vertical-align:middle}
table.vs-table tr:last-child td{border-bottom:none}

/* FORM */
.vs-form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:16px}
.vs-label{font-size:12px;font-weight:600;color:var(--mid);text-transform:uppercase;letter-spacing:.04em}
.vs-input{padding:10px 13px;border:1px solid var(--light);border-radius:8px;font-size:14px;background:var(--white);color:var(--dark);outline:none;transition:.15s;width:100%}
.vs-input:focus{border-color:var(--orange);box-shadow:0 0 0 3px rgba(232,93,38,.08)}
.vs-input.err{border-color:var(--red)}
.vs-err{font-size:12px;color:var(--red);margin-top:2px}
.vs-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:700;border:none;cursor:pointer;transition:.15s;white-space:nowrap}
.vs-btn-primary{background:var(--orange);color:#fff}
.vs-btn-primary:hover{background:var(--orange2)}
.vs-btn-ghost{background:#f5f5f2;color:var(--dark);border:1px solid var(--light)}
.vs-btn-ghost:hover{background:#ebebeb}
.vs-btn-sm{padding:6px 12px;font-size:12px;border-radius:6px}
.badge-pending{background:#fef9c3;color:#92400e;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
.badge-approved{background:#dcfce7;color:#166534;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
.badge-active{background:#dcfce7;color:#166534;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
.badge-blocked{background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}

@media(max-width:800px){
  .vs-sidebar{display:none}
  .vs-main{margin-inline-start:0}
  .vs-topbar{padding:8px 12px;flex-wrap:wrap;gap:4px 10px}
  .vs-topbar-title{font-size:16px}
  .vs-topbar-action{display:inline-flex;align-items:center;min-block-size:44px}
  .vs-content{padding:14px 12px 24px}
  .vs-mobile-menu{display:block;position:relative;flex:none}
  .vs-mobile-menu summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:6px;min-block-size:44px;padding-inline:10px;border:1px solid var(--light);border-radius:8px;font-weight:700}
  .vs-mobile-menu summary::-webkit-details-marker{display:none}
  .vs-mobile-menu[open] summary{background:#fff1e9;color:#963615;border-color:#f0ccbb}
  .vs-mobile-panel{position:absolute;inset-block-start:calc(100% + 6px);inset-inline-end:0;width:min(310px,calc(100vw - 24px));max-height:calc(100dvh - 85px);overflow:auto;overscroll-behavior:contain;background:var(--sidebar);border:1px solid var(--border);border-radius:12px;box-shadow:0 10px 28px rgba(0,0,0,.2)}
  .vs-mobile-panel .vs-nav-item{min-block-size:44px}
}
:is(.vs-nav-item,.vs-topbar-action,.vs-mobile-menu summary,.vs-btn):focus-visible{outline:2px solid var(--orange);outline-offset:2px}
</style>
@stack('styles')
</head>
<body>

@php
  $vendorWorkspace = app(\App\Services\VendorWorkspace::class);
  $vendor = $vendorWorkspace->vendor();
  $adminManagingVendor = $vendorWorkspace->isAdminManaging();
@endphp

@section('seller-navigation')
  <nav class="vs-nav" aria-label="{{ $sellerText('Seller navigation', 'قائمة البائع') }}">
    <div class="vs-nav-label">{{ $sellerText('Seller Hub', 'لوحة البائع') }}</div>
    <a href="{{ route('vendor.dashboard') }}" class="vs-nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      {{ $sellerText('Dashboard', 'لوحة التحكم') }}
    </a>
    <a href="{{ route('vendor.products') }}" class="vs-nav-item {{ request()->routeIs('vendor.products*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      {{ $sellerText('Products', 'المنتجات') }}
    </a>
    <a href="{{ route('vendor.orders') }}" class="vs-nav-item {{ request()->routeIs('vendor.orders*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
      {{ $sellerText('Orders', 'الطلبات') }}
    </a>
    <a href="{{ route('vendor.refunds') }}" class="vs-nav-item {{ request()->routeIs('vendor.refunds*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
      {{ $sellerText('Refund Requests', 'طلبات الاسترداد') }}
    </a>
    <a href="{{ route('vendor.requests') }}" class="vs-nav-item {{ request()->routeIs('vendor.requests*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2z"/><path d="M7 7h.01"/></svg>
      {{ $sellerText('Category & Brand', 'الفئات والعلامات التجارية') }}
    </a>
    <a href="{{ route('vendor.store.profile') }}" class="vs-nav-item {{ request()->routeIs('vendor.store.profile') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      {{ $sellerText('Store Profile', 'بيانات المتجر') }}
    </a>
    <a href="{{ route('vendor.store', $vendor?->id) }}" target="_blank" rel="noopener" class="vs-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
      {{ $sellerText('My Store', 'افتح متجري') }}
    </a>
  </nav>

  <div class="vs-bottom">
    <div class="vs-vendor-info">
      @if($vendor?->shop_logo_url)
        <img src="{{ $vendor->shop_logo_url }}" alt="" class="vs-vendor-avatar">
      @else
        <div class="vs-vendor-avatar">{{ strtoupper(substr($vendor?->shop_name ?? 'V', 0, 1)) }}</div>
      @endif
      <div style="min-width:0">
        <div class="vs-vendor-name">{{ $vendor?->shop_name }}</div>
        <div class="vs-vendor-status">{{ ucfirst($vendor?->status ?? '') }}</div>
      </div>
    </div>
    @unless($adminManagingVendor)
    <form method="POST" action="{{ route('vendor.logout') }}">
      @csrf
      <button type="submit" class="vs-btn vs-btn-ghost" style="width:100%;font-size:13px;padding:8px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        {{ $sellerText('Exit seller dashboard', 'الخروج من لوحة البائع') }}
      </button>
    </form>
    @endunless
  </div>
@endsection

<aside class="vs-sidebar">
  <div class="vs-logo">
    <svg viewBox="0 0 24 24" fill="none" stroke="#e85d26" stroke-width="2" width="22" height="22"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    <div class="vs-logo-text">Ramo<span>Seller</span></div>
  </div>
  @yield('seller-navigation')
</aside>

<div class="vs-main">
  <div class="vs-topbar">
    <div class="vs-topbar-title">@yield('page-title','Dashboard')</div>
    @unless($adminManagingVendor)
      @if(auth('web')->check())
        <a href="{{ route('account.hub') }}" class="vs-topbar-action">{{ $sellerText('My account', 'حسابي') }}</a>
      @else
        <a href="{{ route('home') }}" class="vs-topbar-action">{{ $sellerText('Back to store', 'العودة للتسوق') }}</a>
      @endif
    @endunless
    <details class="vs-mobile-menu">
      <summary>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        {{ $sellerText('Menu', 'القائمة') }}
      </summary>
      <div class="vs-mobile-panel">@yield('seller-navigation')</div>
    </details>
    @if($adminManagingVendor)
      <form method="POST" action="{{ route('vendor.workspace.exit') }}" class="vs-workspace-exit">
        @csrf
        <span class="vs-workspace-label">{{ $sellerText('Admin managing', 'إدارة متجر') }} {{ $vendor?->shop_name }}</span>
        <button type="submit" class="vs-btn vs-btn-ghost vs-btn-sm">{{ $sellerText('Return to Admin', 'العودة للإدارة') }}</button>
      </form>
    @endif
  </div>
  <div class="vs-content">
    @if(session('success'))
      <div class="vs-alert vs-alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="vs-alert vs-alert-error">✕ {{ session('error') }}</div>
    @endif
    @yield('content')
  </div>
</div>

</body>
</html>

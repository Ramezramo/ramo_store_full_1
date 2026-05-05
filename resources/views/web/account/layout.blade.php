@extends('layouts.app')
@section('title', $pageTitle ?? 'My Account — Ramo Store')

@section('content')
<div class="page">
  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span><strong>{{ $pageTitle ?? 'My Account' }}</strong>
  </div>

  <div class="acc-layout">

    {{-- Sidebar --}}
    <aside class="acc-sidebar">
      @php $u = Auth::user(); @endphp
      <div class="acc-avatar-block">
        <div class="acc-avatar">{{ strtoupper(substr($u->first_name ?: $u->name, 0, 1)) }}</div>
        <div>
          <div class="acc-avatar-name">{{ $u->first_name ? $u->first_name.' '.($u->last_name ?? '') : $u->name }}</div>
          <div class="acc-avatar-email">{{ $u->email }}</div>
        </div>
      </div>
      <nav class="acc-nav">
        <a href="{{ route('account.profile') }}" class="acc-nav-item {{ request()->routeIs('account.profile') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Profile
        </a>
        <a href="{{ route('account.orders') }}" class="acc-nav-item {{ request()->routeIs('account.orders','account.order') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          My Orders
        </a>
        <a href="{{ route('wishlist') }}" class="acc-nav-item {{ request()->routeIs('wishlist') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          Wishlist
        </a>
        <a href="{{ route('account.reviews') }}" class="acc-nav-item {{ request()->routeIs('account.reviews') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          My Reviews
        </a>
        <a href="{{ route('account.refunds') }}" class="acc-nav-item {{ request()->routeIs('account.refunds*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
          Refund & Returns
        </a>
      </nav>
      <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;padding-top:16px">
        @csrf
        <button type="submit" class="acc-signout-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Sign Out
        </button>
      </form>
    </aside>

    {{-- Main content --}}
    <main class="acc-main">
      @if(session('success'))
        <div class="acc-alert acc-alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="acc-alert acc-alert-error">{{ session('error') }}</div>
      @endif
      @yield('account-content')
    </main>

  </div>
</div>
@endsection

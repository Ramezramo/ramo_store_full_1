@extends('layouts.app')
@section('title', ($pageTitle ?? (session('locale') === 'ar' ? 'حسابي' : 'My Account')) . ' — Ramo Store')

@section('content')
@php
  $u = Auth::user();
  $isAr = session('locale') === 'ar';
  $accountTitle = $pageTitle ?? ($isAr ? 'حسابي' : 'My Account');
@endphp

{{-- Mobile back bar (hidden on desktop) --}}
<div class="acc-mobile-back">
  <a href="{{ route('account.hub') }}" class="acc-mobile-back-btn">
    <svg fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
  </a>
  <span class="acc-mobile-back-title">{{ $accountTitle }}</span>
</div>

<style>
.acc-mobile-back {
  display: none;
  align-items: center;
  gap: 8px;
  padding: 12px 14px 10px;
  border-bottom: 1px solid #f0f0f0;
  background: #fff;
  position: sticky; top: 0; z-index: 10;
}
.acc-mobile-back-btn {
  width: 34px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 50%; background: #f5f5f5; color: #333;
  flex-shrink: 0;
}
.acc-mobile-back-title { font-size: 16px; font-weight: 700; color: #1a1a1a; }
[dir="rtl"] .acc-mobile-back-btn svg { transform: scaleX(-1); }
[dir="rtl"] .acc-nav-item, [dir="rtl"] .acc-signout-btn { text-align: right; }
@media(max-width: 768px) {
  .acc-mobile-back { display: flex; }
  .acc-sidebar { display: none !important; }
  .acc-layout { display: block !important; }
  .acc-main { padding: 16px !important; }
  /* breadcrumb hidden on mobile to save space */
  .breadcrumb { display: none; }
}
</style>

<div class="page" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="{{ $isAr ? 'font-family:Tahoma,Arial,sans-serif' : '' }}">
  <div class="breadcrumb">
    <a href="{{ route('home') }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a><span>/</span>
    <a href="{{ route('account.hub') }}">{{ $isAr ? 'حسابي' : 'My Account' }}</a><span>/</span>
    <strong>{{ $accountTitle }}</strong>
  </div>

  <div class="acc-layout">

    {{-- Sidebar (desktop only) --}}
    <aside class="acc-sidebar">
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
          {{ $isAr ? 'بياناتي' : 'Profile' }}
        </a>
        <a href="{{ route('account.orders') }}" class="acc-nav-item {{ request()->routeIs('account.orders','account.order') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          {{ $isAr ? 'طلباتي' : 'My Orders' }}
        </a>
        <a href="{{ route('wishlist') }}" class="acc-nav-item {{ request()->routeIs('wishlist') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          {{ $isAr ? 'المفضلة' : 'Wishlist' }}
        </a>
        <a href="{{ route('account.reviews') }}" class="acc-nav-item {{ request()->routeIs('account.reviews') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          {{ $isAr ? 'تقييماتي' : 'My Reviews' }}
        </a>
        <a href="{{ route('account.refunds') }}" class="acc-nav-item {{ request()->routeIs('account.refunds*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
          {{ $isAr ? 'الاسترجاع والمرتجعات' : 'Refund & Returns' }}
        </a>
      </nav>
      <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;padding-top:16px">
        @csrf
        <button type="submit" class="acc-signout-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          {{ $isAr ? 'تسجيل الخروج' : 'Sign Out' }}
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

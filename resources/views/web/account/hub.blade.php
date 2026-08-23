@extends('layouts.app')
@section('title', (session('locale') === 'ar' ? 'حسابي' : 'My Account') . ' — Ramo Store')

@section('content')
@php
  $u = Auth::user();
  $displayName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->name;
  $hasPlaceholderEmail = str_ends_with($u->email ?? '', '@ramostore.local');
  $displayEmail = $hasPlaceholderEmail ? null : $u->email;
  $isAr = session('locale') === 'ar';
@endphp

<style>
/* ── Account Hub ─────────────────────────────────────── */
.acc-hub-wrap {
  max-width: 560px;
  margin: 0 auto;
  padding: 0 0 80px;
}

/* Hero greeting */
.acc-hub-hero {
  background: linear-gradient(135deg, #ff6b00 0%, #ff8c00 100%);
  padding: 28px 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  color: #fff;
}
.acc-hub-avatar {
  width: 60px; height: 60px;
  border-radius: 50%;
  background: rgba(255,255,255,.22);
  display: flex; align-items: center; justify-content: center;
  font-size: 26px; font-weight: 700; color: #fff;
  flex-shrink: 0;
  border: 2.5px solid rgba(255,255,255,.45);
}
.acc-hub-name { font-size: 19px; font-weight: 700; line-height: 1.2; }
.acc-hub-email { font-size: 13px; opacity: .85; margin-top: 3px; word-break: break-all; }
.acc-hub-edit-link {
  margin-left: auto; flex-shrink: 0;
  background: rgba(255,255,255,.18);
  border: 1.5px solid rgba(255,255,255,.4);
  color: #fff; border-radius: 8px;
  padding: 7px 14px; font-size: 13px; font-weight: 600;
  text-decoration: none; white-space: nowrap;
  transition: background .15s;
}
.acc-hub-edit-link:hover { background: rgba(255,255,255,.3); color: #fff; }

/* Section group */
.acc-hub-section {
  margin: 0 0 6px;
}
.acc-hub-section-label {
  font-size: 11.5px; font-weight: 700; color: #999;
  letter-spacing: .06em; text-transform: uppercase;
  padding: 18px 16px 8px;
}

/* Menu rows */
.acc-hub-item {
  display: flex; align-items: center; gap: 14px;
  padding: 15px 16px;
  background: #fff;
  border-bottom: 1px solid #f0f0f0;
  text-decoration: none; color: #1a1a1a;
  transition: background .12s;
}
.acc-hub-item:last-child { border-bottom: none; }
.acc-hub-item:active,
.acc-hub-item:hover { background: #fef6f0; }
.acc-hub-item-icon {
  width: 40px; height: 40px; border-radius: 10px;
  background: #fff7f0; border: 1.5px solid #ffe4cc;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #ff6b00;
}
.acc-hub-item-icon svg { width: 20px; height: 20px; }
.acc-hub-item-label { flex: 1; font-size: 15px; font-weight: 500; }
.acc-hub-item-sub { font-size: 12px; color: #888; margin-top: 2px; }
.acc-hub-chevron { color: #ccc; flex-shrink: 0; }
.acc-hub-chevron svg { width: 16px; height: 16px; }

/* Settings group */
.acc-hub-settings {
  margin-top: 10px;
  padding: 0 12px;
}
.acc-hub-settings .acc-hub-section-label {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 4px 12px;
  color: #b85d25;
  font-size: 13px;
  letter-spacing: 0;
  text-transform: none;
}
.acc-hub-settings-mark {
  width: 28px;
  height: 28px;
  border-radius: 9px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #f06a22;
  background: #fff5ed;
  border: 1px solid #ffe0cc;
}
.acc-hub-settings-mark svg { width: 16px; height: 16px; }
.acc-hub-settings .acc-hub-section {
  overflow: hidden;
  margin: 0;
  background: linear-gradient(135deg, #fffaf6 0%, #fff 100%);
  border: 1px solid #ffe6d6;
  border-radius: 20px;
  box-shadow: 0 8px 22px rgba(203, 106, 43, .08);
}
.acc-hub-settings .acc-hub-item,
.acc-hub-settings .acc-hub-signout {
  min-height: 70px;
  padding: 14px 16px;
  background: transparent;
  border-bottom-color: #f5e4d8;
}
.acc-hub-settings .acc-hub-item-label,
.acc-hub-settings .acc-hub-signout span { font-size: 16px; font-weight: 600; }
.acc-hub-settings .acc-hub-item-sub {
  margin-top: 4px;
  line-height: 1.45;
  color: #8d817a;
}
.acc-hub-settings .acc-hub-item-icon,
.acc-hub-settings .acc-hub-signout-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 3px 10px rgba(205, 111, 52, .08);
}
.acc-hub-settings .acc-hub-item:hover,
.acc-hub-settings .acc-hub-item:active { background: #fff3e9; }
.acc-hub-settings .acc-hub-signout:hover,
.acc-hub-settings .acc-hub-signout:active { background: #fff4f4; }

/* Sign out button row */
.acc-hub-signout {
  display: flex; align-items: center; gap: 14px;
  width: 100%; padding: 15px 16px;
  background: #fff; border: none; border-bottom: 1px solid #f0f0f0;
  text-align: left; cursor: pointer; color: #e53935;
  transition: background .12s;
}
.acc-hub-signout:hover { background: #fff5f5; }
.acc-hub-signout-icon {
  width: 40px; height: 40px; border-radius: 10px;
  background: #fff5f5; border: 1.5px solid #ffc8c8;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #e53935;
}
.acc-hub-signout span { flex: 1; font-size: 15px; font-weight: 500; }
[dir="rtl"] .acc-hub-edit-link { margin-left: 0; margin-right: auto; }
[dir="rtl"] .acc-hub-chevron svg { transform: scaleX(-1); }
[dir="rtl"] .acc-hub-signout { text-align: right; }

/* Desktop: wider card layout */
@media(max-width: 768px) {
  .acc-hub-wrap { padding-top: 68px; }
}

@media(min-width: 769px) {
  .acc-hub-wrap { padding: 32px 16px 60px; }
  .acc-hub-hero { border-radius: 14px; margin-bottom: 4px; }
  .acc-hub-section { border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.07); margin: 0 0 12px; }
  .acc-hub-section-label { background: #fafafa; }
  .acc-hub-item, .acc-hub-signout { border-radius: 0; }
}
</style>

<div class="acc-hub-wrap" dir="{{ $isAr ? 'rtl' : 'ltr' }}" style="{{ $isAr ? 'font-family:Tahoma,Arial,sans-serif' : '' }}">

  {{-- Hero --}}
  <div class="acc-hub-hero">
    <div class="acc-hub-avatar">{{ strtoupper(substr($displayName, 0, 1)) }}</div>
    <div style="flex:1;min-width:0">
      <div class="acc-hub-name">{{ $displayName }}</div>
      @if($displayEmail)
        <div class="acc-hub-email">{{ $displayEmail }}</div>
      @else
        <div class="acc-hub-email" style="opacity:.7">{{ $isAr ? 'مفيش إيميل مضاف' : 'No email set' }}</div>
      @endif
    </div>
    <a href="{{ route('account.profile') }}" class="acc-hub-edit-link">{{ $isAr ? 'تعديل' : 'Edit' }}</a>
  </div>

  {{-- My Account section --}}
  <div class="acc-hub-section">
    <div class="acc-hub-section-label">{{ $isAr ? 'حسابي' : 'My Account' }}</div>

    <a href="{{ route('account.orders') }}" class="acc-hub-item">
      <div class="acc-hub-item-icon">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      </div>
      <div class="acc-hub-item-label">{{ $isAr ? 'طلباتي' : 'My Orders' }}</div>
      <div class="acc-hub-chevron"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
    </a>

    <a href="{{ route('account.referral') }}" class="acc-hub-item">
      <div class="acc-hub-item-icon">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"/><circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M9.5 10.5L7.5 14M14.5 10.5l2 3"/></svg>
      </div>
      <div class="acc-hub-item-label"><div>{{ $isAr ? 'شارك واربح' : 'Refer & Earn' }}</div><div class="acc-hub-item-sub">{{ $isAr ? 'شارك رابطك مع أصحابك' : 'Share your referral link' }}</div></div>
      <div class="acc-hub-chevron"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
    </a>

    <a href="{{ route('wishlist') }}" class="acc-hub-item">
      <div class="acc-hub-item-icon">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
      </div>
      <div class="acc-hub-item-label">{{ $isAr ? 'المفضلة' : 'Wishlist' }}</div>
      <div class="acc-hub-chevron"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
    </a>

    <a href="{{ route('account.reviews') }}" class="acc-hub-item">
      <div class="acc-hub-item-icon">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
      <div class="acc-hub-item-label">{{ $isAr ? 'تقييماتي' : 'My Reviews' }}</div>
      <div class="acc-hub-chevron"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
    </a>

    <a href="{{ route('account.refunds') }}" class="acc-hub-item">
      <div class="acc-hub-item-icon">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
      </div>
      <div class="acc-hub-item-label">{{ $isAr ? 'الاسترجاع والمرتجعات' : 'Refund & Returns' }}</div>
      <div class="acc-hub-chevron"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
    </a>
  </div>

  {{-- Account Settings --}}
  <div class="acc-hub-settings">
    <div class="acc-hub-section-label">
      <span class="acc-hub-settings-mark" aria-hidden="true">
        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06-1.7 1.7-.06-.06a1.7 1.7 0 00-1.88-.34 1.7 1.7 0 00-1.03 1.56V20h-2.4v-.2a1.7 1.7 0 00-1.03-1.56 1.7 1.7 0 00-1.88.34l-.06.06-1.7-1.7.06-.06A1.7 1.7 0 008.4 15a1.7 1.7 0 00-1.56-1.03H6v-2.4h.2A1.7 1.7 0 007.76 10a1.7 1.7 0 00-.34-1.88l-.06-.06 1.7-1.7.06.06A1.7 1.7 0 0011 6.76 1.7 1.7 0 0012.03 5.2V5h2.4v.2A1.7 1.7 0 0015.46 6.76a1.7 1.7 0 001.88-.34l.06-.06 1.7 1.7-.06.06A1.7 1.7 0 0018.7 10a1.7 1.7 0 001.56 1.03h.2v2.4h-.2A1.7 1.7 0 0019.4 15z"/></svg>
      </span>
      <span>{{ $isAr ? 'الإعدادات' : 'Settings' }}</span>
    </div>
    <div class="acc-hub-section">
    <a href="{{ route('account.profile') }}" class="acc-hub-item">
      <div class="acc-hub-item-icon">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div>
        <div class="acc-hub-item-label">{{ $isAr ? 'عدّل بياناتي' : 'Edit Profile' }}</div>
        <div class="acc-hub-item-sub">{{ $isAr ? 'الاسم، الإيميل، الموبايل وكلمة السر' : 'Name, email, phone & password' }}</div>
      </div>
      <div class="acc-hub-chevron"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
    </a>

    <form action="{{ route('logout') }}" method="POST" style="margin:0">
      @csrf
      <button type="submit" class="acc-hub-signout">
        <div class="acc-hub-signout-icon">
          <svg fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </div>
        <span>{{ $isAr ? 'تسجيل الخروج' : 'Sign Out' }}</span>
      </button>
    </form>
    </div>
  </div>

</div>
@endsection

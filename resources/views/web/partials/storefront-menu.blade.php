<div class="nav-mobile-menu storefront-drawer{{ $headerIsArabic ? ' nav-mobile-menu--rtl' : '' }}" id="nav-mobile-menu" dir="{{ $headerIsArabic ? 'rtl' : 'ltr' }}" aria-hidden="true" inert>
  <div class="nav-mobile-backdrop" onclick="closeMobileMenu()" aria-hidden="true"></div>
  <div class="nav-mobile-panel" role="dialog" aria-modal="true" aria-label="{{ $mobileMenuLabel }}" tabindex="-1">
    <div class="header-drawer-heading">
      <strong>{{ $headerIsArabic ? 'اكتشف رامو' : 'Explore Ramo' }}</strong>
      <button type="button" class="header-drawer-close" onclick="closeMobileMenu()" aria-label="{{ $headerIsArabic ? 'اقفل القائمة' : 'Close menu' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
      </button>
    </div>
    <div class="nav-mobile-links">
      <div class="header-menu-label">{{ $headerIsArabic ? 'تسوّق واكتشف' : 'Shop & discover' }}</div>
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">@include('web.partials.header-icon', ['icon' => 'home']) {{ $mobileHomeLabel }}</a>
      <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">@include('web.partials.header-icon', ['icon' => 'shop']) {{ $mobileShopLabel }}</a>
      <a href="{{ route('offers') }}" class="{{ request()->routeIs('offers') ? 'active' : '' }}">@include('web.partials.header-icon', ['icon' => 'offers']) {{ $mobileOffersLabel }}</a>
      <a href="{{ route('wishlist') }}" class="{{ request()->routeIs('wishlist') ? 'active' : '' }}">@include('web.partials.header-icon', ['icon' => 'heart']) {{ $mobileWishlistLabel }}</a>
      <a href="{{ route('order.track') }}" class="{{ request()->routeIs('order.track*') ? 'active' : '' }}">@include('web.partials.header-icon', ['icon' => 'package']) {{ $mobileTrackOrderLabel }}</a>
      <hr class="nav-mobile-divider">
      <div class="header-menu-label">{{ $headerIsArabic ? 'حسابك' : 'Your account' }}</div>
      @auth
        @php $__mu = Auth::user(); @endphp
        <a href="{{ route('account.hub') }}">@include('web.partials.header-icon', ['icon' => 'user']) {{ $__mu->first_name ?: $__mu->name }}</a>
        <a href="{{ route('account.orders') }}">@include('web.partials.header-icon', ['icon' => 'orders']) {{ $mobileMyOrdersLabel }}</a>
      @endauth
      <a href="{{ route('cart') }}" class="{{ request()->routeIs('cart') ? 'active' : '' }}">@include('web.partials.header-icon', ['icon' => 'cart']) {{ $mobileCartLabel }}</a>
      <a href="{{ route('language.switch', ['lang' => $timelineNextLocale]) }}?redirect={{ urlencode(request()->fullUrl()) }}" class="nav-mobile-language-link" target="_top" lang="{{ $timelineNextLocale }}" title="{{ $timelineSwitchTitle }}" aria-label="{{ $timelineSwitchTitle }}">
        @include('web.partials.header-icon', ['icon' => 'globe']) <span dir="{{ $timelineNextLocale === 'ar' ? 'rtl' : 'ltr' }}">{{ $headerIsArabic ? 'English' : 'العربية' }}</span>
      </a>
      @auth
        @php $__isAdm = $__mu->email === 'adminramoui@gmail.com' || str_contains((string)$__mu->role, 'admin'); @endphp
        @if($__isAdm)
          <hr class="nav-mobile-divider">
          <div class="header-menu-label">{{ $headerIsArabic ? 'الإدارة' : 'Administration' }}</div>
          <a href="{{ route('admin.dashboard') }}">@include('web.partials.header-icon', ['icon' => 'dashboard']) {{ $mobileAdminPanelLabel }}</a>
          <a href="/api-guide.html" target="_blank" rel="noopener">@include('web.partials.header-icon', ['icon' => 'code']) {{ $headerIsArabic ? 'دليل الـ API' : 'API documentation' }}</a>
        @endif
      @endauth
      @if(auth()->guard('vendor_web')->check())
        @php $__vmu = auth()->guard('vendor_web')->user(); @endphp
        <hr class="nav-mobile-divider">
        <div class="header-menu-label">{{ $headerIsArabic ? 'إدارة متجرك' : 'Your store' }}</div>
        <a href="{{ route('vendor.dashboard') }}">@include('web.partials.header-icon', ['icon' => 'dashboard']) {{ Str::limit($__vmu->shop_name, 32) }}</a>
        <a href="{{ route('vendor.store', $__vmu->id) }}" target="_blank" rel="noopener">@include('web.partials.header-icon', ['icon' => 'shop']) {{ $headerIsArabic ? 'افتح متجري' : 'View my store' }}</a>
        <form method="POST" action="{{ route('vendor.logout') }}">@csrf
          <button type="submit" class="nav-mobile-signout">@include('web.partials.header-icon', ['icon' => 'logout']) {{ $headerIsArabic ? 'خروج من حساب البائع' : 'Sign out of seller account' }}</button>
        </form>
      @endif
      @auth
        <hr class="nav-mobile-divider">
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button type="submit" class="nav-mobile-signout">@include('web.partials.header-icon', ['icon' => 'logout']) {{ $mobileSignOutLabel }}</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="header-signin">{{ $mobileSignInLabel }}</a>
        <a href="{{ route('register') }}" style="justify-content:center">{{ $mobileRegisterLabel }}</a>
      @endauth
    </div>
  </div>
</div>

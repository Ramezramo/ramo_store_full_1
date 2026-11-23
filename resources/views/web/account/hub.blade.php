@extends('layouts.app')
@section('title', (session('locale') === 'ar' ? 'حسابي' : 'Account') . ' — Ramo Store')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/account-hub.css') }}?v={{ filemtime(public_path('css/account-hub.css')) }}">
@endpush

@section('content')
@php
  $isAr = session('locale') === 'ar';
  $accountTitle = $isAr ? 'حسابي' : 'Account';
  $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?: $accountTitle);
  $displayEmail = str_ends_with($user->email ?? '', '@ramostore.local') ? null : $user->email;
  $hasMarketplace = (bool) $vendorApplication?->vendor_id;
  $vendorTitle = $hasMarketplace
    ? ($isAr ? 'متجري' : 'My marketplace')
    : ($isAr ? 'البيع على رامو' : 'Become a vendor');
  $vendorDescription = match ($vendorApplication?->status) {
    'pending' => $isAr ? 'طلبك تحت المراجعة' : 'Application under review',
    'on_hold' => $isAr ? 'طلبك مؤجل للمراجعة لاحقًا' : 'Application on hold',
    'approved' => match (true) {
      $marketplaceReady => $isAr ? 'افتح لوحة البائع' : 'Open your seller dashboard',
      $hasMarketplace => $isAr ? 'راجع حالة متجرك' : 'View your store status',
      default => $isAr ? 'أنشئ متجرك' : 'Create your marketplace',
    },
    'rejected' => $isAr ? 'راجع حالة طلبك' : 'View your application',
    default => $isAr ? 'ابدأ طلبك' : 'Start your application',
  };
  $groups = [
    'shopping' => [
      'heading' => $isAr ? 'التسوّق' : 'Shopping',
      'links' => [
        ['route' => 'account.orders', 'icon' => 'orders', 'label' => $isAr ? 'طلباتي' : 'My orders'],
        ['route' => 'account.refunds', 'icon' => 'return', 'label' => $isAr ? 'المرتجعات والاسترداد' : 'Returns & refunds'],
        ['route' => 'wishlist', 'icon' => 'heart', 'label' => $isAr ? 'المفضلة' : 'Wishlist'],
        ['route' => 'account.reviews', 'icon' => 'star', 'label' => $isAr ? 'تقييماتي' : 'My reviews'],
      ],
    ],
    'selling' => [
      'heading' => $isAr ? 'البيع والمكافآت' : 'Selling & rewards',
      'links' => [
        ['route' => $marketplaceReady ? 'vendor.marketplace.switch' : 'vendor.register',
          'post' => $marketplaceReady, 'icon' => 'shop', 'label' => $vendorTitle, 'detail' => $vendorDescription],
        ['route' => 'account.referral', 'icon' => 'referral', 'label' => $isAr ? 'شارك واربح' : 'Refer & earn',
          'detail' => $isAr ? 'شارك رابطك مع أصحابك' : 'Share your referral link'],
      ],
    ],
    'settings' => [
      'heading' => $isAr ? 'الإعدادات' : 'Settings',
      'links' => [
        ['route' => 'account.notifications', 'icon' => 'bell', 'label' => $isAr ? 'الإشعارات' : 'Notifications'],
      ],
    ],
  ];
@endphp

<main class="acc-hub-wrap" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
  <div class="acc-hub-profile-area">
    <div class="acc-hub-heading">
      <h1>{{ $accountTitle }}</h1>
      <a href="{{ route('account.profile') }}" class="acc-hub-edit-link">
        @include('web.partials.header-icon', ['icon' => 'edit'])
        {{ $isAr ? 'تعديل البيانات' : 'Edit profile' }}
      </a>
    </div>

    <section class="acc-hub-profile" aria-labelledby="acc-hub-name">
      <span class="acc-hub-avatar" aria-hidden="true">{{ Str::upper(Str::substr($displayName, 0, 1)) }}</span>
      <div class="acc-hub-identity">
        <h2 id="acc-hub-name">{{ $displayName }}</h2>
        <p class="acc-hub-email">
          @if($displayEmail)
            <bdi dir="ltr">{{ $displayEmail }}</bdi>
          @else
            {{ $isAr ? 'مفيش إيميل مضاف' : 'No email added' }}
          @endif
        </p>
      </div>
    </section>
  </div>

  <div class="acc-hub-groups">
    @foreach($groups as $groupKey => $group)
      <section class="acc-hub-group" aria-labelledby="acc-hub-{{ $groupKey }}">
        <h2 id="acc-hub-{{ $groupKey }}">{{ $group['heading'] }}</h2>
        <div class="acc-hub-links">
          @foreach($group['links'] as $link)
            @if($link['post'] ?? false)
              <form method="POST" action="{{ route($link['route']) }}" target="_top">
                @csrf
                <button type="submit" class="acc-hub-item">
            @else
              <a href="{{ route($link['route']) }}" class="acc-hub-item">
            @endif
              <span class="acc-hub-item-icon">@include('web.partials.header-icon', ['icon' => $link['icon']])</span>
              <span class="acc-hub-item-copy">
                <span class="acc-hub-item-label">{{ $link['label'] }}</span>
                @if(isset($link['detail']))
                  <span class="acc-hub-item-sub">{{ $link['detail'] }}</span>
                @endif
              </span>
              <svg class="acc-hub-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
            @if($link['post'] ?? false)
                </button>
              </form>
            @else
              </a>
            @endif
          @endforeach

          @if($groupKey === 'settings')
            <form method="POST" action="{{ route('logout') }}" class="acc-hub-signout-form">
              @csrf
              <button type="submit" class="acc-hub-item acc-hub-signout">
                <span class="acc-hub-item-icon">@include('web.partials.header-icon', ['icon' => 'logout'])</span>
                <span class="acc-hub-item-label">{{ $isAr ? 'تسجيل الخروج' : 'Sign out' }}</span>
              </button>
            </form>
          @endif
        </div>
      </section>
    @endforeach
  </div>
</main>
@endsection

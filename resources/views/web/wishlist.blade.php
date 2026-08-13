@extends('layouts.app')

@php
  $wishlistRtl = session('locale', 'en') === 'ar';
  $wishlistCopy = $wishlistRtl ? [
    'title' => 'المفضلة — Ramo Store',
    'home' => 'الرئيسية',
    'wishlist' => 'المفضلة',
    'heading' => 'المفضلة',
    'emptyHeading' => 'المفضلة فاضية',
    'emptyText' => 'احفظ المنتجات اللي عجبتك عشان تلاقيها بسهولة بعدين.',
    'browse' => 'تصفّح المنتجات',
  ] : [
    'title' => 'Wishlist — Ramo Store',
    'home' => 'Home',
    'wishlist' => 'Wishlist',
    'heading' => 'My Wishlist',
    'emptyHeading' => 'Your wishlist is empty',
    'emptyText' => 'Save products you love to find them easily later.',
    'browse' => 'Browse Products',
  ];
@endphp

@section('title', $wishlistCopy['title'])

@push('styles')
<style>
  /* The global 360px fallback is intentionally single-column for general grids.
     Wishlist cards stay compact so one saved product does not fill the phone viewport. */
  @media (max-width: 600px) {
    .wishlist-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      gap: 12px;
    }
    .wishlist-grid .product-card-img {
      aspect-ratio: 3 / 4;
    }
    .wishlist-grid .product-card-body {
      padding: 10px;
    }
    .wishlist-grid .product-card-name {
      min-height: 38px;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
      overflow: hidden;
    }
  }

  .wishlist-page[dir="rtl"] .breadcrumb,
  .wishlist-page[dir="rtl"] .sec-head,
  .wishlist-page[dir="rtl"] .product-card,
  .wishlist-page[dir="rtl"] .product-card-body {
    direction: rtl;
    text-align: right;
  }
  .wishlist-page[dir="rtl"] .wishlist-grid {
    direction: rtl;
  }
  .wishlist-page[dir="rtl"] .empty {
    direction: rtl;
  }
</style>
@endpush

@section('content')
<div class="page wishlist-page" @if($wishlistRtl) lang="ar" dir="rtl" @endif>
  <div class="breadcrumb">
    <a href="{{ route('home') }}">{{ $wishlistCopy['home'] }}</a><span>/</span><strong>{{ $wishlistCopy['wishlist'] }}</strong>
  </div>

  @if($products->isEmpty())
    <div class="empty" style="padding:100px 20px">
      <div class="empty-icon">♡</div>
      <h3>{{ $wishlistCopy['emptyHeading'] }}</h3>
      <p>{{ $wishlistCopy['emptyText'] }}</p>
      <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:24px">{{ $wishlistCopy['browse'] }}</a>
    </div>
  @else
    <div class="sec-head"><h2 class="sec-title">{{ $wishlistCopy['heading'] }} ({{ $products->count() }})</h2></div>
    <div class="product-grid wishlist-grid">
      @foreach($products as $p)
        @include('web.partials.product-card', [
          'p'            => $p,
          'cardVariations' => [],
          'cardOptions'  => ['showWishlist' => false, 'showDetails' => false, 'removeWishlist' => true, 'idPrefix' => 'wish'],
        ])
      @endforeach
    </div>
  @endif
</div>
@endsection

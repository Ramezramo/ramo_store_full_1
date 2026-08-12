@extends('layouts.app')
@section('title', 'Wishlist — Ramo Store')

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
</style>
@endpush

@section('content')
<div class="page">
  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span><strong>Wishlist</strong>
  </div>

  @if($products->isEmpty())
    <div class="empty" style="padding:100px 20px">
      <div class="empty-icon">♡</div>
      <h3>Your wishlist is empty</h3>
      <p>Save products you love to find them easily later.</p>
      <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:24px">Browse Products</a>
    </div>
  @else
    <div class="sec-head"><h2 class="sec-title">My Wishlist ({{ $products->count() }})</h2></div>
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

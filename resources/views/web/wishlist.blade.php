@extends('layouts.app')
@section('title', 'Wishlist — Ramo Store')

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
    <div class="product-grid">
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

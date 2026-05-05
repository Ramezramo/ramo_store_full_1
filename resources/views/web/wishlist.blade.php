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
      <div class="product-card" id="wish-{{ $p->id }}">
        <a href="{{ route('product', $p->id) }}" class="product-card-img" style="display:block">
          @if($p->thumbnail_url)
            <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" loading="lazy">
          @else
            <div class="placeholder">👕</div>
          @endif
        </a>
        <div class="product-card-body">
          <a href="{{ route('product', $p->id) }}" class="product-card-name">{{ $p->name }}</a>
          <div class="product-card-price">
            @if($p->on_sale)
              <span class="price-main sale">{{ number_format($p->sale_price, 2) }} EGP</span>
              <span class="price-old">{{ number_format($p->price, 2) }}</span>
            @else
              <span class="price-main">{{ number_format($p->price, 2) }} EGP</span>
            @endif
          </div>
          <div style="display:flex;gap:8px;margin-top:10px">
            <button onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->on_sale ? $p->sale_price : $p->price }}, '{{ $p->thumbnail_url }}')" class="btn btn-dark" style="flex:1;padding:9px 12px;font-size:13px;border-radius:8px">Add to Cart</button>
            <form action="{{ route('wishlist.remove', $p->id) }}" method="POST">
              @csrf @method('DELETE')
              <button class="btn btn-outline" style="padding:9px 12px;font-size:13px;border-radius:8px;color:#e02020;border-color:#e02020" title="Remove">✕</button>
            </form>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  @endif
</div>
@endsection

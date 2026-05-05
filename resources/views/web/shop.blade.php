@extends('layouts.app')
@section('title', 'Shop — Ramo Store')

@section('content')
<div class="page">

  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span><strong>Shop</strong>
    @if(request('search'))<span>/</span><span>"{{ request('search') }}"</span>@endif
  </div>

  <button class="shop-filter-toggle" id="shop-filter-btn" onclick="toggleShopFilter()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
    Filters & Categories
  </button>

  <div class="shop-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
      <h3>Categories</h3>
      <ul class="cat-list">
        <li>
          <a href="{{ route('shop', array_filter(request()->except('category','page'))) }}"
             class="{{ !request('category') ? 'active' : '' }}">All Products</a>
        </li>
        @foreach($categories as $cat)
        <li>
          <a href="{{ route('shop', array_merge(request()->except('category','page'), ['category'=>$cat->id])) }}"
             class="{{ request('category') == $cat->id ? 'active' : '' }}">{{ $cat->name }}</a>
        </li>
        @endforeach
      </ul>

      <hr class="sidebar-divider">
      <h3>Sort By</h3>
      <form method="GET" action="{{ route('shop') }}" id="sort-form">
        @foreach(request()->except('sort','page') as $k => $v)
          <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <select class="sort-select" name="sort" onchange="document.getElementById('sort-form').submit()">
          <option value=""          {{ !request('sort') ? 'selected' : '' }}>Latest</option>
          <option value="price_asc" {{ request('sort')==='price_asc'  ? 'selected' : '' }}>Price: Low → High</option>
          <option value="price_desc"{{ request('sort')==='price_desc' ? 'selected' : '' }}>Price: High → Low</option>
        </select>
      </form>
    </aside>

    {{-- MAIN --}}
    <div>
      <div class="shop-toolbar">
        <span class="result-count">{{ $products->total() }} product{{ $products->total()!=1?'s':'' }}</span>
        <div class="search-bar">
          <form method="GET" action="{{ route('shop') }}" style="display:contents">
            @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            <input type="text" name="search" placeholder="Search…" value="{{ request('search') }}">
            <button type="submit">🔍</button>
          </form>
        </div>
      </div>

      @if($products->count())
        <div class="product-grid">
          @foreach($products as $p)
          <div class="product-card">
            <a href="{{ route('product', $p->id) }}" class="product-card-img">
              @if($p->thumbnail_url)
                <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" loading="lazy">
              @else
                <div class="placeholder">👕</div>
              @endif
              @if($p->on_sale)<span class="badge-sale">@if($p->discount_percentage > 0)-{{ round($p->discount_percentage) }}%@else SALE @endif</span>@endif
              <button class="wish-btn" onclick="event.preventDefault();toggleWishlist(this,{{ $p->id }})" title="Add to Wishlist">♡</button>
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
              <button class="card-add-btn" onclick="addToCart({{ $p->id }},'{{ addslashes($p->name) }}',{{ $p->display_price }},'{{ $p->thumbnail_url }}')">Add to Cart</button>
              @if(!empty($p->coupon))
              @php $__c=$p->coupon;$__b=$p->on_sale?$p->sale_price:$p->price;$__cp=$__c->discount_type==='percent'?$__b*(1-(float)$__c->amount/100):max(0,$__b-(float)$__c->amount); @endphp
              <a href="{{ route('cart') }}" class="pc-coupon-bar" onclick="event.preventDefault();saveCouponAndGo('{{ strtoupper($__c->code) }}','{{ route('cart') }}')" title="Click to apply this coupon at checkout">
                <span class="pc-coupon-left">🏷️ WITH CODE <strong class="pc-coupon-code">{{ strtoupper($__c->code) }}</strong></span>
                <span class="pc-coupon-right">↓ {{ number_format($__cp,0) }} EGP</span>
              </a>
              @endif
            </div>
          </div>
          @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($products->hasPages())
        <div class="pagination-wrap">
          @if($products->onFirstPage())<span>‹</span>@else<a href="{{ $products->previousPageUrl() }}">‹</a>@endif
          @foreach($products->getUrlRange(max(1,$products->currentPage()-2), min($products->lastPage(),$products->currentPage()+2)) as $page => $url)
            @if($page == $products->currentPage())<span class="active-page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
          @endforeach
          @if($products->hasMorePages())<a href="{{ $products->nextPageUrl() }}">›</a>@else<span>›</span>@endif
        </div>
        @endif

      @else
        <div class="empty">
          <div class="empty-icon">🔍</div>
          <h3>No products found</h3>
          <p>Try a different search term or browse all categories.</p>
          <a href="{{ route('shop') }}" class="btn btn-dark" style="margin-top:20px">Clear filters</a>
        </div>
      @endif
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
function toggleShopFilter() {
  const sidebar = document.querySelector('.sidebar');
  const btn = document.getElementById('shop-filter-btn');
  const open = sidebar.classList.toggle('mobile-open');
  btn.innerHTML = open
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Close Filters'
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg> Filters & Categories';
}
</script>
@endpush

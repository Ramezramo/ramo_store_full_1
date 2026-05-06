@extends('layouts.app')
@section('title', 'Shop — Ramo Store')

@push('styles')
<style>
/* ── Category sidebar hierarchy ─────────────────────────── */
.cat-list { list-style:none; margin:0; padding:0; }
.cat-list > li { border-bottom:1px solid #f0f0ee; }
.cat-list > li:last-child { border-bottom:none; }

/* Parent row */
.cat-parent-row {
  display:flex; align-items:center; justify-content:space-between;
  gap:6px;
}
.cat-parent-link {
  flex:1; display:block; padding:9px 4px 9px 0;
  font-size:14px; font-weight:600; color:#333;
  transition:.15s;
}
.cat-parent-link:hover { color:#e85d26; }
.cat-parent-link.active { color:#e85d26; }

/* Toggle chevron */
.cat-toggle {
  background:none; border:none; cursor:pointer;
  color:#aaa; padding:6px; border-radius:4px;
  transition:.15s; flex-shrink:0; line-height:1;
}
.cat-toggle:hover { color:#e85d26; background:#fff5f2; }
.cat-toggle svg { display:block; transition:transform .2s; }
.cat-toggle.open svg { transform:rotate(180deg); }

/* Children list */
.cat-children {
  list-style:none; margin:0; padding:0 0 6px 14px;
  display:none;
  border-left:2px solid #f0ede8;
}
.cat-children.open { display:block; }
.cat-children li { }
.cat-children a {
  display:flex; align-items:center; gap:5px;
  padding:5px 4px; font-size:13px; color:#666;
  transition:.15s; border-radius:4px;
}
.cat-children a:hover { color:#e85d26; }
.cat-children a.active { color:#e85d26; font-weight:600; }
.cat-children a::before {
  content:''; width:5px; height:5px;
  border-radius:50%; background:#d5cfc9; flex-shrink:0;
}
.cat-children a.active::before { background:#e85d26; }

/* All Products link */
.cat-all-link {
  display:block; padding:10px 4px; font-size:14px; font-weight:600;
  color:#333; border-bottom:1px solid #f0f0ee; margin-bottom:4px;
  transition:.15s;
}
.cat-all-link:hover,.cat-all-link.active { color:#e85d26; }

/* Product count badge next to category */
.cat-count {
  font-size:11px; color:#bbb; font-weight:400; margin-left:3px;
}
</style>
@endpush

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span><strong>Shop</strong>
    @if(request('search'))<span>/</span><span>"{{ request('search') }}"</span>@endif
    @if($activeCategoryId)
      @php
        $activeCat = $parentCats->firstWhere('id', $activeCategoryId)
          ?? $childCats->flatten()->firstWhere('id', $activeCategoryId);
      @endphp
      @if($activeCat)<span>/</span><span>{{ $activeCat->name }}</span>@endif
    @endif
  </div>

  <button class="shop-filter-toggle" id="shop-filter-btn" onclick="toggleShopFilter()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
    Filters & Categories
  </button>

  <div class="shop-layout">

    {{-- ── SIDEBAR ─────────────────────────────────────── --}}
    <aside class="sidebar">
      <h3>Categories</h3>

      <ul class="cat-list">
        {{-- All Products --}}
        <li>
          <a href="{{ route('shop', array_filter(request()->except('category','page'))) }}"
             class="cat-all-link {{ !$activeCategoryId ? 'active' : '' }}">
            All Products
          </a>
        </li>

        {{-- Parent categories --}}
        @foreach($parentCats as $parent)
          @php
            $hasChildren = isset($childCats[$parent->id]) && $childCats[$parent->id]->count() > 0;
            $isParentActive = $activeCategoryId == $parent->id;
            $isOpen = $activeParentId == $parent->id;
            $parentUrl = route('shop', array_merge(request()->except('category','page'), ['category' => $parent->id]));
          @endphp
          <li>
            <div class="cat-parent-row">
              <a href="{{ $parentUrl }}"
                 class="cat-parent-link {{ $isParentActive ? 'active' : '' }}">
                {{ $parent->name }}
              </a>
              @if($hasChildren)
                <button class="cat-toggle {{ $isOpen ? 'open' : '' }}"
                        onclick="toggleChildren('children-{{ $parent->id }}', this)"
                        aria-label="Toggle sub-categories">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13">
                    <polyline points="6 9 12 15 18 9"/>
                  </svg>
                </button>
              @endif
            </div>

            @if($hasChildren)
              <ul class="cat-children {{ $isOpen ? 'open' : '' }}" id="children-{{ $parent->id }}">
                @foreach($childCats[$parent->id] as $child)
                  <li>
                    <a href="{{ route('shop', array_merge(request()->except('category','page'), ['category' => $child->id])) }}"
                       class="{{ $activeCategoryId == $child->id ? 'active' : '' }}">
                      {{ $child->name }}
                    </a>
                  </li>
                @endforeach
              </ul>
            @endif
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

    {{-- ── MAIN ─────────────────────────────────────────── --}}
    <div>
      <div class="shop-toolbar">
        <span class="result-count">
          {{ $products->total() }} product{{ $products->total()!=1?'s':'' }}
          @if($activeCategoryId && isset($activeCat))
            in <strong>{{ $activeCat->name }}</strong>
            @php $isParentFilter = $parentCats->firstWhere('id', $activeCategoryId) && isset($childCats[$activeCategoryId]); @endphp
            @if($isParentFilter)
              <span style="font-size:12px;color:#aaa">(incl. sub-categories)</span>
            @endif
          @endif
        </span>
        <div class="search-bar">
          <form method="GET" action="{{ route('shop') }}" style="display:contents">
            @if($activeCategoryId)<input type="hidden" name="category" value="{{ $activeCategoryId }}">@endif
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

function toggleChildren(listId, btn) {
  const list = document.getElementById(listId);
  const open = list.classList.toggle('open');
  btn.classList.toggle('open', open);
}
</script>
@endpush

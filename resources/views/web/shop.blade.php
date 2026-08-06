@extends('layouts.app')
@section('title', 'Shop — Ramo Store')

@push('styles')
<style>
/* ══════════════════════════════════════════════
   SHOP PAGE — PRODUCT GRID OVERRIDE
   Scoped here so it always loads with this page,
   unaffected by any browser or service-worker cache
   that may hold an older version of the global CSS.
══════════════════════════════════════════════ */
#infinite-product-grid {
  display: grid !important;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)) !important;
  gap: 14px !important;
}
@media (max-width: 600px) {
  #infinite-product-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
    gap: 10px !important;
  }
}
/* ── Narrow phones: Amazon-style horizontal list ── */
@media (max-width: 480px) {
  /* Stack cards vertically, one per row, no side-by-side */
  #infinite-product-grid {
    display: flex !important;
    flex-direction: column !important;
    gap: 0 !important;
  }

  /* Each card: horizontal row — image left, info right */
  #infinite-product-grid .product-card {
    display: flex !important;
    flex-direction: row !important;
    align-items: stretch !important;
    width: 100% !important;
    min-width: 0 !important;
    border-radius: 0 !important;
    border-left: none !important;
    border-right: none !important;
    border-top: none !important;
    border-bottom: 1px solid var(--c-light) !important;
    box-shadow: none !important;
    transform: none !important;
    background: #fff;
    padding: 14px 0;
    gap: 0;
  }
  #infinite-product-grid .product-card:hover {
    transform: none !important;
    box-shadow: none !important;
  }

  /* Left: fixed-width square image */
  #infinite-product-grid .product-card-img {
    width: 120px !important;
    min-width: 120px !important;
    height: 120px !important;
    aspect-ratio: 1 / 1 !important;
    flex-shrink: 0 !important;
    border-radius: 8px !important;
    margin: 0 12px 0 0 !important;
    overflow: hidden !important;
    background: var(--c-bg);
    align-self: flex-start !important;
  }

  /* Right: info column fills remaining space */
  #infinite-product-grid .product-card-body {
    flex: 1 !important;
    min-width: 0 !important;
    padding: 0 12px 0 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 5px !important;
  }

  /* Title: show up to 3 lines */
  #infinite-product-grid .product-card-name {
    font-size: 13.5px !important;
    font-weight: 500 !important;
    line-height: 1.45 !important;
    -webkit-line-clamp: 3 !important;
    color: var(--c-dark);
  }

  /* Price row */
  #infinite-product-grid .product-card-price {
    margin-top: 2px;
  }
  #infinite-product-grid .price-main {
    font-size: 16px !important;
    font-weight: 700 !important;
  }
  #infinite-product-grid .price-old {
    font-size: 12px !important;
  }

  /* Sale badge: small pill, doesn't take up layout space */
  #infinite-product-grid .badge-sale {
    font-size: 9px !important;
    padding: 2px 6px !important;
  }

  /* Wish button stays in image corner */
  #infinite-product-grid .wish-btn {
    top: 6px !important;
    right: 6px !important;
    width: 26px !important;
    height: 26px !important;
    font-size: 13px !important;
  }

  /* Buttons: compact side-by-side row */
  #infinite-product-grid .card-add-btn {
    padding: 8px 12px !important;
    font-size: 12px !important;
    border-radius: 6px !important;
    margin-top: 4px !important;
  }
  #infinite-product-grid .card-details-btn {
    padding: 7px 12px !important;
    font-size: 12px !important;
    border-radius: 6px !important;
    margin-top: 4px !important;
  }

  /* Coupon bar: collapse a bit */
  #infinite-product-grid .pc-coupon-bar {
    margin: 6px 0 0 !important;
    border-radius: 6px !important;
    font-size: 10px !important;
  }

  /* Swatches: smaller */
  #infinite-product-grid .pc-swatch {
    width: 16px !important;
    height: 16px !important;
  }
  #infinite-product-grid .pc-size {
    padding: 2px 7px !important;
    font-size: 10px !important;
  }
}

/* ══════════════════════════════════════════════
   FOLDABLE WIDGET SECTIONS
══════════════════════════════════════════════ */
.widget {
  margin-bottom: 4px;
}

/* Widget header — the clickable fold toggle */
.widget-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 4px; cursor: pointer; user-select: none;
  border-radius: 8px; transition: background .13s;
  gap: 8px;
}
.widget-header:hover { background: var(--c-tag); padding-left: 8px; padding-right: 8px; }

.widget-header-left {
  display: flex; align-items: center; gap: 7px;
  font-size: 10px; font-weight: 800; letter-spacing: .1em;
  text-transform: uppercase; color: var(--c-mid);
}
.widget-header-left svg { flex-shrink: 0; }

.widget-fold-icon {
  display: flex; align-items: center; justify-content: center;
  width: 18px; height: 18px; border-radius: 5px;
  background: var(--c-bg); border: 1px solid var(--c-light);
  color: var(--c-mid); flex-shrink: 0; transition: background .13s;
}
.widget-header:hover .widget-fold-icon { background: var(--c-light); }
.widget-fold-icon svg { transition: transform .25s cubic-bezier(.4,0,.2,1); display:block; }
.widget.collapsed .widget-fold-icon svg { transform: rotate(-90deg); }

/* Widget body — the foldable content */
.widget-body {
  overflow: hidden;
  max-height: 1200px;
  transition: max-height .35s cubic-bezier(.4,0,.2,1), opacity .25s ease;
  opacity: 1;
}
.widget.collapsed .widget-body {
  max-height: 0;
  opacity: 0;
}

/* ── Category items ──────────────────────────────────────── */
.cat-all-pill {
  display: flex; align-items: center; justify-content: space-between;
  padding: 9px 13px; border-radius: 9px; font-size: 13.5px;
  font-weight: 600; color: var(--c-dark); margin-bottom: 4px;
  transition: all .15s; text-decoration: none; background: transparent;
}
.cat-all-pill:hover { background: var(--c-tag); }
.cat-all-pill.active { background: var(--c-dark); color: #fff; }
.cat-all-pill.active .cat-count-badge { background: rgba(255,255,255,.18); color: #fff; }

.cat-count-badge {
  font-size: 10px; font-weight: 700;
  background: var(--c-bg); color: var(--c-mid);
  padding: 2px 7px; border-radius: 20px;
  border: 1px solid var(--c-light);
  min-width: 24px; text-align: center; flex-shrink: 0;
}

.cat-parent-item { margin-bottom: 2px; }

.cat-parent-btn {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  gap: 6px; padding: 9px 13px; border-radius: 9px;
  background: transparent; border: none; cursor: pointer;
  font-family: inherit; font-size: 13.5px; font-weight: 600;
  color: var(--c-dark); text-align: left; transition: all .15s; text-decoration: none;
}
.cat-parent-btn:hover { background: var(--c-tag); }
.cat-parent-btn.active { background: var(--c-orange); color: #fff; }
.cat-parent-btn.active .cat-count-badge { background: rgba(255,255,255,.25); color: #fff; border-color: transparent; }
.cat-parent-btn.parent-open:not(.active) { background: var(--c-tag); }

.cat-parent-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.cat-chevron {
  flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  width: 20px; height: 20px; border-radius: 5px; transition: all .2s; color: var(--c-mid);
}
.cat-chevron svg { transition: transform .25s cubic-bezier(.4,0,.2,1); display: block; }

.cat-children-wrap {
  overflow: hidden; max-height: 0;
  transition: max-height .3s cubic-bezier(.4,0,.2,1);
}
.cat-children-wrap.open { max-height: 600px; }

.cat-children-inner {
  padding: 3px 0 6px 12px; margin-left: 13px;
  border-left: 2px solid var(--c-light);
}

.cat-child-link {
  display: flex; align-items: center; justify-content: space-between;
  gap: 6px; padding: 7px 11px; border-radius: 8px;
  font-size: 13px; font-weight: 500; color: var(--c-mid);
  text-decoration: none; transition: all .13s; margin-bottom: 1px;
}
.cat-child-link::before {
  content: ''; width: 5px; height: 5px; border-radius: 50%;
  background: var(--c-light); flex-shrink: 0; transition: background .13s;
}
.cat-child-link:hover { background: var(--c-tag); color: var(--c-dark); }
.cat-child-link:hover::before { background: #ccc; }
.cat-child-link.active {
  background: #fff5f2; color: var(--c-orange); font-weight: 700;
  border: 1px solid #fde0d5;
}
.cat-child-link.active::before { background: var(--c-orange); }
.cat-child-link.active .cat-count-badge { background: #fde0d5; color: var(--c-orange); border-color: transparent; }

/* ── Divider ──────────────────────────────────────────────── */
.widget-divider { border: none; border-top: 1.5px solid var(--c-light); margin: 8px 0; }

/* ── Toolbar active strip ─────────────────────────────────── */
.active-cat-strip {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fff5f2; border: 1px solid #fde0d5;
  color: var(--c-orange); font-size: 12px; font-weight: 700;
  padding: 4px 10px 4px 8px; border-radius: 20px;
}
.active-cat-strip a {
  display: flex; align-items: center; color: var(--c-orange);
  opacity: .7; transition: opacity .13s; margin-left: 4px;
}
.active-cat-strip a:hover { opacity: 1; }

/* ── Mobile toggle ────────────────────────────────────────── */
.shop-filter-toggle {
  display: none; align-items: center; gap: 7px;
  padding: 10px 16px; border-radius: 50px;
  background: var(--c-white); border: 1.5px solid var(--c-light);
  font-size: 13.5px; font-weight: 600; color: var(--c-dark);
  margin-bottom: 16px; cursor: pointer; transition: all .15s;
}
.shop-filter-toggle:hover { background: var(--c-tag); }

@media (max-width: 860px) {
  .shop-layout { grid-template-columns: 1fr; }
  .shop-filter-toggle { display: inline-flex; }
  .sidebar { display: none; position: static; }
  .sidebar.mobile-open { display: block; }
}
</style>
@endpush

@section('content')
<div class="page">

  {{-- Breadcrumb --}}
  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span>
    <a href="{{ route('shop') }}">Shop</a>
    @if($activeCategoryId)
      @php
        $allCatsFlat  = $parentCats->merge($childCats->flatten());
        $activeCatObj = $allCatsFlat->firstWhere('id', $activeCategoryId);
        $activeCatName = $activeCatObj->name ?? '';
        $isChildActive = $activeCatObj && $activeCatObj->parent > 0;
        if ($isChildActive) { $parentCatObj = $parentCats->firstWhere('id', $activeCatObj->parent); }
      @endphp
      @if($isChildActive && isset($parentCatObj))
        <span>/</span>
        <a href="{{ route('shop', ['category' => $parentCatObj->id]) }}">{{ $parentCatObj->name }}</a>
      @endif
      <span>/</span><strong>{{ $activeCatName }}</strong>
    @endif
    @if($activeBrandName ?? false)<span>/</span><strong>{{ $activeBrandName }}</strong>@endif
    @if(request('search'))<span>/</span><span>"{{ request('search') }}"</span>@endif
  </div>

  <button class="shop-filter-toggle" id="shop-filter-btn" onclick="toggleShopFilter()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
    Filters & Categories
  </button>

  <div class="shop-layout">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════ --}}
    <aside class="sidebar" id="shop-sidebar">

      {{-- ── Widget: Categories ───────────────────────────────── --}}
      <div class="widget" id="widget-categories">

        <div class="widget-header" onclick="toggleWidget('widget-categories')" title="Click to collapse">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Categories
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          {{-- All Products --}}
          <a href="{{ route('shop', array_filter(request()->except('category','page'))) }}"
             class="cat-all-pill {{ !$activeCategoryId ? 'active' : '' }}">
            <span>All Products</span>
            <span class="cat-count-badge">{{ $products->total() }}</span>
          </a>

          <hr class="widget-divider" style="margin:8px 0">

          {{-- Parent → Children --}}
          @foreach($parentCats as $parent)
            @php
              $hasChildren   = isset($childCats[$parent->id]) && $childCats[$parent->id]->count() > 0;
              $isActive      = $activeCategoryId == $parent->id;
              $isOpen        = $activeParentId == $parent->id;
              $parentUrl     = route('shop', array_merge(request()->except('category','page'), ['category' => $parent->id]));
              $ownCount      = $catCounts[$parent->id] ?? 0;
              $childrenCount = 0;
              if ($hasChildren) foreach ($childCats[$parent->id] as $ch) $childrenCount += ($catCounts[$ch->id] ?? 0);
              $totalCount    = $ownCount + $childrenCount;
            @endphp

            <div class="cat-parent-item">
              @if($hasChildren)
                <div style="display:flex;align-items:center;border-radius:9px;{{ $isActive ? 'background:var(--c-orange);' : ($isOpen ? 'background:var(--c-tag);' : '') }}">
                  <a href="{{ $parentUrl }}"
                     style="flex:1;display:flex;align-items:center;gap:8px;padding:9px 0 9px 13px;color:{{ $isActive ? '#fff' : 'var(--c-dark)' }};text-decoration:none;min-width:0;font-size:13.5px;font-weight:600;">
                    <span class="cat-parent-name">{{ $parent->name }}</span>
                    @if($totalCount > 0)
                      <span class="cat-count-badge" style="{{ $isActive ? 'background:rgba(255,255,255,.25);color:#fff;border-color:transparent;' : '' }}">{{ $totalCount }}</span>
                    @endif
                  </a>
                  <button onclick="toggleCatChildren('cc-{{ $parent->id }}', this)"
                          style="background:none;border:none;cursor:pointer;padding:9px 13px;flex-shrink:0;display:flex;align-items:center;justify-content:center;color:{{ $isActive ? 'rgba(255,255,255,.7)' : 'var(--c-mid)' }};"
                          aria-label="Toggle sub-categories">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"
                         class="cat-chevron-svg"
                         style="transition:transform .25s;{{ $isOpen ? 'transform:rotate(180deg)' : '' }}">
                      <polyline points="6 9 12 15 18 9"/>
                    </svg>
                  </button>
                </div>
              @else
                <a href="{{ $parentUrl }}" class="cat-parent-btn {{ $isActive ? 'active' : '' }}">
                  <span class="cat-parent-name">{{ $parent->name }}</span>
                  @if($totalCount > 0)<span class="cat-count-badge">{{ $totalCount }}</span>@endif
                </a>
              @endif

              @if($hasChildren)
                <div class="cat-children-wrap {{ $isOpen ? 'open' : '' }}" id="cc-{{ $parent->id }}">
                  <div class="cat-children-inner">
                    @foreach($childCats[$parent->id] as $child)
                      @php
                        $childActive = $activeCategoryId == $child->id;
                        $childCount  = $catCounts[$child->id] ?? 0;
                        $childUrl    = route('shop', array_merge(request()->except('category','page'), ['category' => $child->id]));
                      @endphp
                      <a href="{{ $childUrl }}" class="cat-child-link {{ $childActive ? 'active' : '' }}">
                        <span style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $child->name }}</span>
                        @if($childCount > 0)<span class="cat-count-badge">{{ $childCount }}</span>@endif
                      </a>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>
          @endforeach
        </div>{{-- /widget-body --}}
      </div>{{-- /widget-categories --}}

      @if($allBrands->count())
      <hr class="widget-divider">

      {{-- ── Widget: Brands ───────────────────────────────────── --}}
      <div class="widget" id="widget-brands">

        <div class="widget-header" onclick="toggleWidget('widget-brands')" title="Click to collapse">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Brands
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          @foreach($allBrands as $brand)
            @php $isBrandActive = $activeBrandName === $brand->name; @endphp
            <a href="{{ $isBrandActive ? route('shop', array_filter(request()->except('brand','page'))) : route('shop', array_merge(request()->except('brand','page'), ['brand' => $brand->name])) }}"
               class="cat-parent-btn {{ $isBrandActive ? 'active' : '' }}"
               style="margin-bottom:2px">
              {{ $brand->name }}
              @if($isBrandActive)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="10" height="10" style="margin-left:auto;opacity:.7"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              @endif
            </a>
          @endforeach
        </div>

      </div>{{-- /widget-brands --}}
      @endif

      <hr class="widget-divider">

      {{-- ── Widget: Sort By ──────────────────────────────────── --}}
      <div class="widget" id="widget-sort">

        <div class="widget-header" onclick="toggleWidget('widget-sort')" title="Click to collapse">
          <span class="widget-header-left">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="9" y2="18"/></svg>
            Sort By
          </span>
          <span class="widget-fold-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>

        <div class="widget-body">
          <form method="GET" action="{{ route('shop') }}" id="sort-form" style="padding-bottom:4px">
            @foreach(request()->except('sort','page') as $k => $v)
              <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <select class="sort-select" name="sort" onchange="document.getElementById('sort-form').submit()">
              <option value=""          {{ !request('sort') ? 'selected' : '' }}>Latest</option>
              <option value="price_asc" {{ request('sort')==='price_asc'  ? 'selected' : '' }}>Price: Low → High</option>
              <option value="price_desc"{{ request('sort')==='price_desc' ? 'selected' : '' }}>Price: High → Low</option>
            </select>
          </form>
        </div>

      </div>{{-- /widget-sort --}}

    </aside>

    {{-- ══ MAIN ════════════════════════════════════════════════ --}}
    <div>
      <div class="shop-toolbar">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <span class="result-count">{{ $products->total() }} product{{ $products->total()!=1?'s':'' }}</span>
          @if($activeCategoryId && isset($activeCatName) && $activeCatName)
            <span class="active-cat-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              {{ $activeCatName }}
              @php $isParentFilter = $parentCats->firstWhere('id', $activeCategoryId) && isset($childCats[$activeCategoryId]) && $childCats[$activeCategoryId]->count() > 0; @endphp
              @if($isParentFilter)<span style="opacity:.6;font-weight:500">+ sub-categories</span>@endif
              <a href="{{ route('shop', array_filter(request()->except('category','page'))) }}" title="Clear category">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            </span>
          @endif
          @if($activeBrandName ?? false)
            <span class="active-cat-strip">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Brand: {{ $activeBrandName }}
              <a href="{{ route('shop', array_filter(request()->except('brand','page'))) }}" title="Clear brand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </a>
            </span>
          @endif
        </div>
        <div class="search-bar">
          <form method="GET" action="{{ route('shop') }}" style="display:contents">
            @if($activeCategoryId)<input type="hidden" name="category" value="{{ $activeCategoryId }}">@endif
            <input type="text" name="search" placeholder="Search products…" value="{{ request('search') }}">
            <button type="submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
          </form>
        </div>
      </div>

      @if($products->count())
        <div class="product-grid" id="infinite-product-grid">
          @foreach($products as $p)
            @include('web.partials.product-card', ['p' => $p, 'cardVariations' => []])
          @endforeach
        </div>

        {{-- Infinite scroll sentinel & loader --}}
        <div id="scroll-sentinel" style="height:1px;margin-top:8px"></div>
        <div id="scroll-loader" style="display:none;text-align:center;padding:24px 0">
          <span style="display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--c-mid)">
            <svg style="animation:spin .8s linear infinite" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
            Loading more products…
          </span>
        </div>
        <div id="scroll-end" style="display:none;text-align:center;padding:20px 0;font-size:13px;color:var(--c-mid)">
          You've seen all {{ $products->total() }} products
        </div>

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
/* ── Widget fold/unfold ────────────────────────────────────── */
const STORAGE_KEY = 'ramo_shop_widgets';

function getWidgetState() {
  try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch { return {}; }
}
function saveWidgetState(state) {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch {}
}

function toggleWidget(id) {
  const widget = document.getElementById(id);
  widget.classList.toggle('collapsed');
  const state = getWidgetState();
  state[id] = widget.classList.contains('collapsed');
  saveWidgetState(state);
}

/* Restore collapsed state on page load */
document.addEventListener('DOMContentLoaded', function () {
  const state = getWidgetState();
  Object.keys(state).forEach(function (id) {
    if (state[id]) {
      const el = document.getElementById(id);
      if (el) el.classList.add('collapsed');
    }
  });
});

/* ── Category sub-list toggle ──────────────────────────────── */
function toggleCatChildren(id, btn) {
  const wrap = document.getElementById(id);
  const svg  = btn.querySelector('.cat-chevron-svg');
  const open = wrap.classList.toggle('open');
  if (svg) svg.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}

/* ── Mobile sidebar toggle ─────────────────────────────────── */
function toggleShopFilter() {
  const sidebar = document.getElementById('shop-sidebar');
  const btn = document.getElementById('shop-filter-btn');
  const open = sidebar.classList.toggle('mobile-open');
  btn.innerHTML = open
    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Close Filters'
    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg> Filters & Categories';
}

/* ── Infinite scroll ────────────────────────────────────────── */
(function () {
  const grid      = document.getElementById('infinite-product-grid');
  const sentinel  = document.getElementById('scroll-sentinel');
  const loader    = document.getElementById('scroll-loader');
  const endMsg    = document.getElementById('scroll-end');

  if (!grid || !sentinel) return;

  let nextPage    = {{ $products->hasMorePages() ? $products->currentPage() + 1 : 'null' }};
  let loading     = false;

  /* Build base URL from current page URL (strip ?page=…) */
  function buildUrl(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    return url.toString();
  }

  function loadMore() {
    if (loading || nextPage === null) return;
    loading = true;
    loader.style.display = 'block';

    fetch(buildUrl(nextPage), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(r => r.json())
      .then(data => {
        /* Append new cards */
        grid.insertAdjacentHTML('beforeend', data.html);

        if (data.hasMore) {
          nextPage = data.nextPage;
          loader.style.display = 'none';
        } else {
          nextPage = null;
          loader.style.display = 'none';
          endMsg.style.display = 'block';
          observer.disconnect();
        }
        loading = false;
      })
      .catch(() => {
        loader.style.display = 'none';
        loading = false;
      });
  }

  const observer = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '200px' });

  if (nextPage !== null) {
    observer.observe(sentinel);
  } else {
    /* Already showing all products on first load */
    endMsg.style.display = 'block';
  }
})();
</script>

@push('styles')
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

<script>
/* Tell any active service worker to clear its page cache immediately,
   so stale shop HTML doesn't block CSS updates from reaching the user. */
if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
  navigator.serviceWorker.controller.postMessage('clear');
}
</script>
@endpush

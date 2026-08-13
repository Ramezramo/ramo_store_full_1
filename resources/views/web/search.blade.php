@extends('layouts.app')
@section('title', ($q ? "\"$q\" — Search Results" : 'Search') . ' — Ramo Store')

@section('content')
<div class="page">

  <div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a><span>/</span>
    <strong>{{ $q ? "Search: \"$q\"" : 'Search' }}</strong>
  </div>

  {{-- SEARCH HERO --}}
  <div class="search-hero">
    <form method="GET" action="{{ route('search') }}" id="search-form">
      <div class="search-hero-bar">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search products…" id="search-q" autocomplete="off">
        <button type="submit">Search</button>
      </div>
      {{-- Preserve other filters on search --}}
      @if($sort && $sort !== 'relevance') <input type="hidden" name="sort" value="{{ $sort }}"> @endif
      @if($categoryId) <input type="hidden" name="category" value="{{ $categoryId }}"> @endif
    </form>
    @if($q)
      <p class="search-result-meta">{{ $products->total() }} result{{ $products->total() != 1 ? 's' : '' }} for <strong>"{{ $q }}"</strong></p>
    @endif
  </div>

  {{-- ACTIVE FILTER CHIPS --}}
  @if(count($activeFilters) > 0)
  <div class="filter-chips">
    @foreach($activeFilters as $f)
      @php
        $removeParams = array_merge(request()->except(is_array($f['remove']) ? $f['remove'] : [$f['remove']]));
        if($f['type'] === 'in_stock') unset($removeParams['in_stock']);
      @endphp
      <a href="{{ route('search', $removeParams) }}" class="filter-chip">
        {{ $f['label'] }} <span>×</span>
      </a>
    @endforeach
    <a href="{{ route('search', $q ? ['q' => $q] : []) }}" class="filter-chip-clear">Clear all</a>
  </div>
  @endif

  <div class="search-layout" id="search-layout">

    <button type="button" class="mobile-filter-toggle" id="mobile-filter-toggle" aria-expanded="false" aria-controls="search-sidebar">
      <span>Filters</span><span class="mobile-filter-chevron" aria-hidden="true">⌄</span>
    </button>

    {{-- ── SIDEBAR FILTERS ── --}}
    <aside class="search-sidebar" id="search-sidebar">
      <form method="GET" action="{{ route('search') }}" id="filter-form">
        @if($q) <input type="hidden" name="q" value="{{ $q }}"> @endif

        {{-- Sort --}}
        <div class="filter-section">
          <div class="filter-label">Sort By</div>
          <select name="sort" class="sort-select" onchange="document.getElementById('filter-form').submit()">
            <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Relevance</option>
            <option value="newest"    {{ $sort === 'newest'    ? 'selected' : '' }}>Newest First</option>
            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
            <option value="price_desc"{{ $sort === 'price_desc'? 'selected' : '' }}>Price: High to Low</option>
            <option value="name_asc"  {{ $sort === 'name_asc'  ? 'selected' : '' }}>Name: A–Z</option>
            <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name: Z–A</option>
          </select>
        </div>

        {{-- Price Range --}}
        <div class="filter-section">
          <div class="filter-label">Price Range <span id="price-label" class="filter-val"></span></div>
          <div class="price-range-wrap">
            <div class="price-range-track" id="range-track">
              <div class="price-range-fill" id="range-fill"></div>
              <input type="range" id="range-min" class="range-input range-min"
                     min="{{ floor($priceRange->min_price) }}" max="{{ ceil($priceRange->max_price) }}"
                     value="{{ $minPrice ?? floor($priceRange->min_price) }}" step="5">
              <input type="range" id="range-max" class="range-input range-max"
                     min="{{ floor($priceRange->min_price) }}" max="{{ ceil($priceRange->max_price) }}"
                     value="{{ $maxPrice ?? ceil($priceRange->max_price) }}" step="5">
            </div>
            <div class="price-inputs-row">
              <div class="price-input-box">
                <span>Min</span>
                <input type="number" name="min_price" id="min-price-input"
                       value="{{ $minPrice !== null ? (int)$minPrice : '' }}"
                       placeholder="{{ floor($priceRange->min_price) }}"
                       min="{{ floor($priceRange->min_price) }}" max="{{ ceil($priceRange->max_price) }}">
              </div>
              <div class="price-input-sep">–</div>
              <div class="price-input-box">
                <span>Max</span>
                <input type="number" name="max_price" id="max-price-input"
                       value="{{ $maxPrice !== null ? (int)$maxPrice : '' }}"
                       placeholder="{{ ceil($priceRange->max_price) }}"
                       min="{{ floor($priceRange->min_price) }}" max="{{ ceil($priceRange->max_price) }}">
              </div>
            </div>
          </div>
        </div>

        {{-- In Stock --}}
        <div class="filter-section">
          <label class="toggle-row">
            <span class="filter-label" style="margin:0">In Stock Only</span>
            <label class="toggle-switch">
              <input type="checkbox" name="in_stock" value="1" id="in-stock-toggle"
                     {{ $inStock ? 'checked' : '' }} onchange="document.getElementById('filter-form').submit()">
              <span class="toggle-slider"></span>
            </label>
          </label>
        </div>

        {{-- Category --}}
        <div class="filter-section">
          <div class="filter-label">Category</div>
          <div class="cat-filter-list">
            <a href="{{ route('search', array_merge(request()->except('category'), ['category' => ''])) }}"
               class="cat-filter-item {{ !$categoryId ? 'active' : '' }}">All Categories</a>
            @foreach($categories as $cat)
              <a href="{{ route('search', array_merge(request()->except('category'), ['category' => $cat->id])) }}"
                 class="cat-filter-item {{ $categoryId == $cat->id ? 'active' : '' }}">
                {{ $cat->name }}
              </a>
            @endforeach
          </div>
        </div>

        {{-- Apply button (for price range) --}}
        <button type="submit" class="apply-filters-btn">Apply Filters</button>
      </form>
    </aside>

    {{-- ── RESULTS ── --}}
    <div class="search-results">

      @if($products->isEmpty())
        <div class="search-empty">
          <div style="font-size:64px;margin-bottom:20px">🔍</div>
          <h3>No products found</h3>
          @if($q)
            <p>We couldn't find anything for <strong>"{{ $q }}"</strong>. Try a different search or remove some filters.</p>
          @else
            <p>Try adjusting the filters or browsing all products.</p>
          @endif
          <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap">
            <a href="{{ route('search') }}" class="btn btn-outline" style="border-radius:10px;padding:11px 22px">Clear all filters</a>
            <a href="{{ route('shop') }}" class="btn btn-dark" style="border-radius:10px;padding:11px 22px">Browse shop</a>
          </div>
        </div>
      @else
        <div class="search-toolbar">
          <span class="result-count">{{ $products->total() }} product{{ $products->total()!=1?'s':'' }}</span>
          <span style="font-size:13px;color:var(--c-mid)">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
        </div>

        <div class="product-grid" style="margin-bottom:32px">
          @foreach($products as $p)
            @php
              $cardNameHtml = $q
                ? preg_replace('/(' . preg_quote(e($q), '/') . ')/i', '<mark class="search-hl">$1</mark>', e($p->name))
                : null;
            @endphp
            @include('web.partials.product-card', [
              'p'            => $p,
              'cardVariations' => [],
              'cardNameHtml' => $cardNameHtml,
            ])
          @endforeach
        </div>

        {{-- Pagination --}}
        @if($products->lastPage() > 1)
        <div class="pagination-wrap">
          @if($products->onFirstPage())
            <span>←</span>
          @else
            <a href="{{ $products->previousPageUrl() }}">←</a>
          @endif

          @for($pg = max(1, $products->currentPage()-2); $pg <= min($products->lastPage(), $products->currentPage()+2); $pg++)
            @if($pg === $products->currentPage())
              <span class="active-page">{{ $pg }}</span>
            @else
              <a href="{{ $products->url($pg) }}">{{ $pg }}</a>
            @endif
          @endfor

          @if($products->hasMorePages())
            <a href="{{ $products->nextPageUrl() }}">→</a>
          @else
            <span>→</span>
          @endif
        </div>
        @endif
      @endif

    </div>
  </div>

</div>
@endsection

@push('scripts')
<style>
/* ── Search Page Styles ── */
.search-hero{margin-bottom:28px}
.search-hero-bar{display:flex;background:var(--c-white);border:2px solid var(--c-dark);border-radius:50px;overflow:hidden;max-width:680px}
.search-hero-bar input{flex:1;padding:14px 20px;border:none;outline:none;font-size:15px;font-family:inherit;background:none}
.search-hero-bar button{padding:12px 24px;background:var(--c-dark);color:#fff;border:none;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .15s}
.search-hero-bar button:hover{background:var(--c-accent-h)}
.search-result-meta{margin-top:10px;font-size:14px;color:var(--c-mid)}
.search-result-meta strong{color:var(--c-dark)}

.filter-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px}
.filter-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--c-dark);color:#fff;border-radius:50px;font-size:12.5px;font-weight:600;transition:opacity .15s}
.filter-chip:hover{opacity:.8}
.filter-chip span{font-size:15px;line-height:1}
.filter-chip-clear{display:inline-flex;align-items:center;padding:5px 12px;background:var(--c-tag);color:var(--c-mid);border-radius:50px;font-size:12.5px;font-weight:600;transition:all .15s}
.filter-chip-clear:hover{background:var(--c-light);color:var(--c-dark)}

.search-layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:28px;align-items:start}
.mobile-filter-toggle{display:none}
.search-sidebar,.search-results{min-width:0}
.search-sidebar{background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg);padding:20px;position:sticky;top:84px}
.filter-section{border-bottom:1px solid var(--c-light);padding-bottom:18px;margin-bottom:18px}
.filter-section:last-of-type{border-bottom:none;margin-bottom:0;padding-bottom:0}
.filter-label{font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--c-mid);margin-bottom:12px;display:flex;justify-content:space-between;align-items:center}
.filter-val{font-size:11px;color:var(--c-orange);font-weight:600;text-transform:none;letter-spacing:0}

/* Price range slider */
.price-range-wrap{user-select:none}
.price-range-track{position:relative;height:6px;background:var(--c-light);border-radius:3px;margin:14px 4px 18px}
.price-range-fill{position:absolute;height:100%;background:var(--c-dark);border-radius:3px;pointer-events:none}
.range-input{position:absolute;width:100%;height:6px;-webkit-appearance:none;appearance:none;background:none;border:none;pointer-events:none;top:0;left:0;margin:0;padding:0}
.range-input::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:18px;height:18px;border-radius:50%;background:var(--c-dark);cursor:pointer;pointer-events:all;border:2.5px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.range-input::-moz-range-thumb{width:18px;height:18px;border-radius:50%;background:var(--c-dark);cursor:pointer;pointer-events:all;border:2.5px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.price-inputs-row{display:flex;align-items:center;gap:6px}
.price-input-box{flex:1;display:flex;flex-direction:column;gap:3px}
.price-input-box span{font-size:10px;color:var(--c-mid);font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.price-input-box input{width:100%;padding:7px 10px;border:1.5px solid var(--c-light);border-radius:8px;font-size:13px;font-family:inherit;outline:none;background:var(--c-bg)}
.price-input-box input:focus{border-color:#999}
.price-input-sep{color:var(--c-mid);flex-shrink:0;padding-top:16px}

/* Toggle switch */
.toggle-row{display:flex;align-items:center;justify-content:space-between;cursor:pointer}
.toggle-switch{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:#ddd;border-radius:24px;transition:.2s}
.toggle-slider::before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-switch input:checked + .toggle-slider{background:var(--c-dark)}
.toggle-switch input:checked + .toggle-slider::before{transform:translateX(18px)}

/* Category filter list */
.cat-filter-list{display:flex;flex-direction:column;gap:2px}
.cat-filter-item{display:block;padding:7px 10px;border-radius:8px;font-size:13.5px;color:var(--c-mid);transition:all .12s}
.cat-filter-item:hover,.cat-filter-item.active{background:var(--c-tag);color:var(--c-dark);font-weight:600}

.apply-filters-btn{width:100%;margin-top:18px;padding:11px;background:var(--c-dark);color:#fff;border:none;border-radius:10px;font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .15s}
.apply-filters-btn:hover{background:var(--c-accent-h)}

.search-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.search-empty{text-align:center;padding:64px 20px;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:var(--radius-lg)}
.search-empty h3{font-size:20px;font-weight:800;margin-bottom:12px}
.search-empty p{color:var(--c-mid);font-size:14px}
mark.search-hl{background:#fff3cd;color:inherit;border-radius:2px;padding:0 1px}

@media(max-width:768px){
  .search-hero{margin-bottom:20px}
  .search-hero-bar{width:100%;max-width:none}
  .search-hero-bar input{min-width:0;padding:13px 16px;font-size:16px}
  .search-hero-bar button{padding:12px 18px;flex-shrink:0}
  .filter-chips{margin-bottom:16px}
  .search-layout{grid-template-columns:minmax(0,1fr);gap:16px}
  .mobile-filter-toggle{display:flex;align-items:center;justify-content:space-between;width:100%;padding:14px 16px;background:var(--c-white);border:1.5px solid var(--c-light);border-radius:14px;color:var(--c-dark);font:700 14px/1 inherit;cursor:pointer}
  .mobile-filter-chevron{font-size:18px;line-height:1;transition:transform .18s}
  .search-layout.filters-open .mobile-filter-chevron{transform:rotate(180deg)}
  .search-sidebar{display:none;position:static;padding:16px;border-radius:16px}
  .search-layout.filters-open .search-sidebar{display:block}
  .search-results{width:100%;min-width:0;overflow:hidden}
  .search-toolbar{gap:10px;flex-wrap:wrap;margin-bottom:14px}
  .search-toolbar>span:last-child{font-size:12px!important}
  .search-empty{padding:48px 16px}
}
</style>

<script>
const PRICE_MIN_ABS = {{ floor($priceRange->min_price) }};
const PRICE_MAX_ABS = {{ ceil($priceRange->max_price)  }};

const rangeMin   = document.getElementById('range-min');
const rangeMax   = document.getElementById('range-max');
const fill       = document.getElementById('range-fill');
const priceLabel = document.getElementById('price-label');
const minInput   = document.getElementById('min-price-input');
const maxInput   = document.getElementById('max-price-input');

function updateRange() {
  const mn = parseInt(rangeMin.value);
  const mx = parseInt(rangeMax.value);
  const total = PRICE_MAX_ABS - PRICE_MIN_ABS;
  const leftPct  = ((mn - PRICE_MIN_ABS) / total) * 100;
  const rightPct = ((mx - PRICE_MIN_ABS) / total) * 100;

  fill.style.left  = leftPct  + '%';
  fill.style.width = (rightPct - leftPct) + '%';

  priceLabel.textContent = mn + ' – ' + mx + ' EGP';
  minInput.value = mn === PRICE_MIN_ABS ? '' : mn;
  maxInput.value = mx === PRICE_MAX_ABS ? '' : mx;
}

rangeMin.addEventListener('input', () => {
  if (parseInt(rangeMin.value) > parseInt(rangeMax.value) - 5) {
    rangeMin.value = parseInt(rangeMax.value) - 5;
  }
  updateRange();
});

rangeMax.addEventListener('input', () => {
  if (parseInt(rangeMax.value) < parseInt(rangeMin.value) + 5) {
    rangeMax.value = parseInt(rangeMin.value) + 5;
  }
  updateRange();
});

// Sync typed inputs back to sliders
minInput?.addEventListener('input', () => {
  const v = parseInt(minInput.value) || PRICE_MIN_ABS;
  rangeMin.value = Math.min(v, parseInt(rangeMax.value) - 5);
  updateRange();
});
maxInput?.addEventListener('input', () => {
  const v = parseInt(maxInput.value) || PRICE_MAX_ABS;
  rangeMax.value = Math.max(v, parseInt(rangeMin.value) + 5);
  updateRange();
});

// Auto-focus search
document.getElementById('search-q')?.focus();

// Mobile filters are collapsed by default and expand on demand.
const searchLayout = document.getElementById('search-layout');
const mobileFilterToggle = document.getElementById('mobile-filter-toggle');
mobileFilterToggle?.addEventListener('click', () => {
  const open = searchLayout.classList.toggle('filters-open');
  mobileFilterToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
});

// Init
updateRange();
</script>
@endpush

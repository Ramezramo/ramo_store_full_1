@extends('layouts.app')

@php
  $searchRtl = session('locale', 'en') === 'ar';
  $hasQuery = $q !== '';
  $resultCount = $products->total();
  $resultLabel = $searchRtl
    ? ($resultCount === 1 ? 'نتيجة' : 'نتائج')
    : ($resultCount === 1 ? 'result' : 'results');
  $sortOptions = $searchRtl
    ? ['relevance' => 'الأكثر صلة', 'newest' => 'الأحدث', 'price_asc' => 'الأقل سعرًا', 'price_desc' => 'الأعلى سعرًا', 'name_asc' => 'الاسم تصاعديًا', 'name_desc' => 'الاسم تنازليًا']
    : ['relevance' => 'Relevance', 'newest' => 'Newest first', 'price_asc' => 'Price: Low to high', 'price_desc' => 'Price: High to low', 'name_asc' => 'Name: A–Z', 'name_desc' => 'Name: Z–A'];
  $filters = array_filter(request()->only('category', 'sort', 'min_price', 'max_price', 'in_stock'), fn ($value) => is_scalar($value) && $value !== '');
  $clearFiltersUrl = route('search', $hasQuery ? ['q' => $q] : []);
@endphp

@section('title', ($hasQuery ? $q . ' — ' : '') . ($searchRtl ? 'البحث' : 'Search') . ' — Ramo Store')

@section('content')
<div class="page search-page" dir="{{ $searchRtl ? 'rtl' : 'ltr' }}">
  @include('web.partials.search-form')

  <section class="search-results" aria-labelledby="search-results-title">
    <div class="search-results-heading">
      <h2 id="search-results-title">
        @if($hasQuery)
          {{ $searchRtl ? 'نتائج البحث عن' : 'Results for' }} <bdi>{{ $q }}</bdi>
        @else
          {{ $searchRtl ? 'تصفّح المنتجات' : 'Explore products' }}
        @endif
      </h2>
      <p>{{ $resultCount }} {{ $resultLabel }}</p>
    </div>

    <details class="search-filters">
      <summary>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M7 12h10m-7 5h4"/></svg>
        {{ $searchRtl ? 'الفلاتر والترتيب' : 'Filter & sort' }}
      </summary>
      <form action="{{ route('search') }}" method="GET" class="search-filter-fields">
        @if($hasQuery) <input type="hidden" name="q" value="{{ $q }}"> @endif
        <label>
          {{ $searchRtl ? 'الترتيب' : 'Sort by' }}
          <select name="sort">
            @foreach($sortOptions as $value => $label)
              <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <label>
          {{ $searchRtl ? 'القسم' : 'Category' }}
          <select name="category">
            <option value="">{{ $searchRtl ? 'كل الأقسام' : 'All categories' }}</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" @selected($categoryId == $category->id)>
                {{ \App\Support\StorefrontLabels::category($category->name, $searchRtl) }}
              </option>
            @endforeach
          </select>
        </label>
        <label>
          {{ $searchRtl ? 'أقل سعر' : 'Min. price' }}
          <input type="number" name="min_price" min="0" step="0.01" inputmode="decimal"
                 value="{{ $minPrice }}" placeholder="{{ floor($priceRange->min_price ?? 0) }}">
        </label>
        <label>
          {{ $searchRtl ? 'أعلى سعر' : 'Max. price' }}
          <input type="number" name="max_price" min="0" step="0.01" inputmode="decimal"
                 value="{{ $maxPrice }}" placeholder="{{ ceil($priceRange->max_price ?? 0) }}">
        </label>
        <label class="search-stock">
          <input type="checkbox" name="in_stock" value="1" @checked($inStock)>
          {{ $searchRtl ? 'المتاح بس' : 'In stock only' }}
        </label>
        <button type="submit">{{ $searchRtl ? 'تطبيق' : 'Apply filters' }}</button>
      </form>
    </details>

    @if($activeFilters || ($sort && $sort !== 'relevance'))
      <div class="search-active-filters">
        @foreach($activeFilters as $filter)
          <a href="{{ route('search', request()->except(['page', ...((array) $filter['remove'])])) }}">
            {{ $searchRtl && $filter['type'] === 'in_stock' ? 'المتاح بس' : $filter['label'] }}
            <span aria-hidden="true">×</span>
          </a>
        @endforeach
        <a class="search-clear-filters" href="{{ $clearFiltersUrl }}">{{ $searchRtl ? 'مسح الفلاتر' : 'Clear filters' }}</a>
      </div>
    @endif

    @if($products->isEmpty())
      <div class="search-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg>
        <h3>{{ $searchRtl ? 'ملقيناش منتجات' : 'No products found' }}</h3>
        <p>{{ $searchRtl ? 'جرّب اسم منتج تاني أو غيّر الفلاتر.' : 'Try a different product name or change the filters.' }}</p>
        <a href="{{ route('shop') }}">{{ $searchRtl ? 'تصفّح المتجر' : 'Browse the shop' }}</a>
      </div>
    @else
      <div class="product-grid product-grid--showcase search-product-grid">
        @foreach($products as $p)
          @include('web.partials.product-card', [
            'p' => $p,
            'cardVariations' => $cardVariations[$p->id] ?? [],
            'cardOptions' => ['idPrefix' => 'search'],
          ])
        @endforeach
      </div>

      @if($products->hasPages())
        <nav class="search-pagination" aria-label="{{ $searchRtl ? 'صفحات النتائج' : 'Search result pages' }}">
          @if(!$products->onFirstPage())
            <a href="{{ $products->previousPageUrl() }}" rel="prev">{{ $searchRtl ? 'السابق' : 'Previous' }}</a>
          @endif
          <span aria-current="page">
            {{ $searchRtl ? 'صفحة' : 'Page' }} {{ $products->currentPage() }} {{ $searchRtl ? 'من' : 'of' }} {{ $products->lastPage() }}
          </span>
          @if($products->hasMorePages())
            <a href="{{ $products->nextPageUrl() }}" rel="next">{{ $searchRtl ? 'التالي' : 'Next' }}</a>
          @endif
        </nav>
      @endif
    @endif
  </section>
</div>
@endsection

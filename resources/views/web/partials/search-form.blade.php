@php($searchFormId = $searchFormId ?? 'search')
<section class="search-intro" aria-labelledby="{{ $searchFormId }}-title">
  <h1 id="{{ $searchFormId }}-title">{{ $searchRtl ? 'ابحث في المتجر' : 'Search the store' }}</h1>

  <form action="{{ route('search') }}" method="GET" class="search-form" role="search" data-storefront-search-form>
    <div class="search-field">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg>
      <input type="search" name="q" id="{{ $searchFormId }}-q" value="{{ $q }}" dir="auto"
             placeholder="{{ $searchRtl ? 'اكتب اسم المنتج…' : 'Enter a product name…' }}"
             aria-label="{{ $searchRtl ? 'اسم المنتج' : 'Product name' }}"
             autocomplete="off" enterkeyhint="search" required pattern=".*\S.*">
      <button type="submit">{{ $searchRtl ? 'بحث' : 'Search' }}</button>
    </div>
    <ul class="search-suggestions" data-suggestions-url="{{ route('search.suggestions', [], false) }}"
        aria-label="{{ $searchRtl ? 'منتجات مقترحة' : 'Suggested products' }}" hidden></ul>
    @foreach($filters as $name => $value)
      <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
  </form>
</section>

@if($paginator->hasPages())
  @php
    $paginationRtl = session('locale', 'en') === 'ar';
    $previousLabel = $paginationRtl ? 'الصفحة السابقة' : 'Previous page';
    $nextLabel = $paginationRtl ? 'الصفحة التالية' : 'Next page';
  @endphp
  <nav class="pagination-wrap storefront-pagination" aria-label="{{ $paginationRtl ? 'صفحات المنتجات' : 'Product pages' }}" dir="{{ $paginationRtl ? 'rtl' : 'ltr' }}">
    @if($paginator->onFirstPage())
      <span aria-disabled="true" aria-label="{{ $previousLabel }}">{{ $paginationRtl ? '›' : '‹' }}</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ $previousLabel }}">{{ $paginationRtl ? '›' : '‹' }}</a>
    @endif

    @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 1), min($paginator->lastPage(), $paginator->currentPage() + 1)) as $page => $url)
      @if($page === $paginator->currentPage())
        <span class="active-page" aria-current="page" aria-label="{{ $paginationRtl ? 'صفحة '.$page : 'Page '.$page }}">{{ $page }}</span>
      @else
        <a href="{{ $url }}" aria-label="{{ $paginationRtl ? 'روح لصفحة '.$page : 'Go to page '.$page }}">{{ $page }}</a>
      @endif
    @endforeach

    @if($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ $nextLabel }}">{{ $paginationRtl ? '‹' : '›' }}</a>
    @else
      <span aria-disabled="true" aria-label="{{ $nextLabel }}">{{ $paginationRtl ? '‹' : '›' }}</span>
    @endif
  </nav>
@endif

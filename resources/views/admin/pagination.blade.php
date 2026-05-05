@if ($paginator->hasPages())
  @foreach ($elements as $element)
    @if (is_string($element))
      <span class="dots">{{ $element }}</span>
    @endif
    @if (is_array($element))
      @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
          <span class="current">{{ $page }}</span>
        @else
          <a href="{{ $url }}">{{ $page }}</a>
        @endif
      @endforeach
    @endif
  @endforeach
@endif

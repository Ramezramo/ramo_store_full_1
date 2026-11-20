@php $photoFallback = asset('images/product-placeholder.svg'); @endphp
<img src="{{ $photoSrc ?: $photoFallback }}" alt="{{ $photoAlt }}"
     @if(isset($photoId)) id="{{ $photoId }}" @endif
     @if(isset($photoClass)) class="{{ $photoClass }}" @endif
     loading="lazy" decoding="async" data-photo-pending data-fallback="{{ $photoFallback }}"
     onload="this.removeAttribute('data-photo-pending')"
     onerror="if (this.getAttribute('src') === this.dataset.fallback) this.removeAttribute('data-photo-pending'); else this.src = this.dataset.fallback;">

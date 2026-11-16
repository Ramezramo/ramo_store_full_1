@if($hasMoreSections && !$preview)
  @push('styles')
  <style>
    .timeline-placeholder{display:grid;gap:14px;text-align:start;padding-block:8px 20px}
    .timeline-placeholder[hidden]{display:none}
    .timeline-placeholder-title{inline-size:42%;block-size:18px;border-radius:5px}
    .timeline-placeholder-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .timeline-placeholder-card{aspect-ratio:1;border-radius:8px}
    .timeline-placeholder :is(.timeline-placeholder-title,.timeline-placeholder-card){background:linear-gradient(100deg,#f2f2f2 30%,#fafafa 50%,#f2f2f2 70%);background-size:220% 100%;animation:timeline-placeholder-pulse 1.4s ease-in-out infinite}
    @keyframes timeline-placeholder-pulse{to{background-position:-220% 0}}
    @media(min-width:768px){.timeline-placeholder-cards{grid-template-columns:repeat(3,minmax(0,1fr))}.timeline-placeholder-card:last-child{display:block}}
    @media(max-width:767px){.timeline-placeholder-card:last-child{display:none}}
    @media(prefers-reduced-motion:reduce){.timeline-placeholder :is(.timeline-placeholder-title,.timeline-placeholder-card){animation:none}}
  </style>
  @endpush
  <div id="timeline-loader" data-url="{{ route('home.sections', [], false) }}" data-page="{{ $page + 1 }}" aria-busy="false" style="text-align:center;padding:18px 0 36px">
    <div class="timeline-placeholder" data-timeline-placeholder hidden aria-hidden="true">
      <div class="timeline-placeholder-title"></div>
      <div class="timeline-placeholder-cards">
        <div class="timeline-placeholder-card"></div>
        <div class="timeline-placeholder-card"></div>
        <div class="timeline-placeholder-card"></div>
      </div>
    </div>
    <p data-timeline-status role="status" aria-live="polite" style="color:#777;font-size:13px">{{ $timelineRtl ? 'تابع التمرير للمزيد' : 'Keep scrolling to discover more' }}</p>
    <a data-timeline-button href="{{ route('home', ['timeline_page' => $page + 1]) }}" style="padding:10px 20px;border:1px solid #ddd;border-radius:999px;color:inherit">{{ $timelineRtl ? 'عرض المزيد' : 'Load more sections' }}</a>
    <div data-timeline-sentinel aria-hidden="true" style="height:1px"></div>
  </div>
  @push('scripts')
  <script>
  (function () {
    const loader = document.getElementById('timeline-loader');
    if (!loader) return;
    const button = loader.querySelector('[data-timeline-button]');
    const status = loader.querySelector('[data-timeline-status]');
    const sentinel = loader.querySelector('[data-timeline-sentinel]');
    const placeholder = loader.querySelector('[data-timeline-placeholder]');
    const catalogView = loader.closest('[data-catalog-view]');
    const isActive = () => !catalogView || catalogView.dataset.catalogActive === '1';
    const supportsObserver = 'IntersectionObserver' in window;
    let page = Number(loader.dataset.page);
    let loading = false;
    let observer = null;
    if (supportsObserver) button.hidden = true;

    async function loadSections(event) {
      if (event) event.preventDefault();
      if (loading || !isActive()) return;
      loading = true;
      observer?.unobserve(sentinel);
      loader.setAttribute('aria-busy', 'true');
      if (placeholder) placeholder.hidden = false;
      status.textContent = @json($timelineRtl ? 'جارٍ تحميل المزيد...' : 'Loading more sections…');
      const url = new URL(loader.dataset.url, window.location.href);
      url.searchParams.set('page', String(page));
      try {
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Timeline request failed');
        const data = await response.json();
        if (typeof data.html !== 'string' || typeof data.hasMore !== 'boolean' || (data.hasMore && (!Number.isInteger(data.nextPage) || data.nextPage <= page))) throw new Error('Invalid timeline response');
        const template = document.createElement('template');
        template.innerHTML = data.html;
        const scripts = Array.from(template.content.querySelectorAll('script'));
        scripts.forEach(script => script.remove());
        const sections = Array.from(template.content.children).filter(element => element.id.startsWith('tl-sec-'));
        loader.before(template.content);
        scripts.forEach(original => {
          const script = document.createElement('script');
          Array.from(original.attributes).forEach(attribute => script.setAttribute(attribute.name, attribute.value));
          script.textContent = original.textContent;
          loader.before(script);
        });
        sections.forEach(section => initializeTimelineWidgets(section));
        if (typeof window.refreshWishlistState === 'function') window.refreshWishlistState();
        if (!data.hasMore) {
          observer?.disconnect();
          loader.remove();
          return;
        }
        page = data.nextPage;
        const nextUrl = new URL(window.location.href);
        nextUrl.searchParams.set('timeline_page', String(page));
        button.href = nextUrl.toString();
        button.hidden = supportsObserver;
        status.textContent = @json($timelineRtl ? 'تابع التمرير للمزيد' : 'Keep scrolling to discover more');
        observer?.observe(sentinel);
      } catch (error) {
        observer?.unobserve(sentinel);
        status.textContent = @json($timelineRtl ? 'تعذر تحميل المزيد الآن.' : 'Couldn’t load more sections right now.');
        button.textContent = @json($timelineRtl ? 'إعادة المحاولة' : 'Try again');
        button.hidden = false;
      } finally {
        loading = false;
        loader.setAttribute('aria-busy', 'false');
        if (placeholder) placeholder.hidden = true;
      }
    }
    button.addEventListener('click', loadSections);
    if (supportsObserver) {
      observer = new IntersectionObserver(entries => {
        if (entries.some(entry => entry.isIntersecting)) loadSections();
      }, {
        // Cached pages scroll inside this container. The viewport margin is
        // clipped by it, so observe the scrolling page itself to load ahead.
        root: catalogView,
        rootMargin: `0px 0px ${Math.min(2400, Math.max(1200, (catalogView?.clientHeight || window.innerHeight) * 2))}px 0px`
      });
      observer.observe(sentinel);
      document.addEventListener('catalog:shown', () => {
        if (isActive() && document.contains(loader)) { observer.unobserve(sentinel); observer.observe(sentinel); }
      });
    }
  })();
  </script>
  @endpush
@endif

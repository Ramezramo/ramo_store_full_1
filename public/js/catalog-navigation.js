/* Home and Shop stay mounted in separate scrolling views for twelve minutes. */
(() => {
  'use strict';
  if (window !== window.top) return;
  const host = document.getElementById('storefront-content');
  const first = host?.querySelector('[data-catalog-view]');
  const scope = document.querySelector('meta[name="catalog-session"]')?.content;
  if (!host || !first || !scope) return;
  const searchEntry = document.getElementById('storefront-search-entry');
  const catalogLifetime = 12 * 60 * 1000;
  const productPreviewLifetime = 5 * 60 * 1000;
  const locale = document.documentElement.lang;
  const initialPath = location.pathname + location.search;
  const views = new Map([[initialPath, { element: first, title: document.title, loadedAt: Date.now(), initialized: true }]]);
  const initialStyles = [...document.querySelectorAll('head > style:not(#storefront-layout-css)')];
  let activePath = initialPath;
  let frame = null;
  let transientLoading = null;
  let previewImageLease = null;
  let loadingTimer;
  const connectedDocuments = new WeakSet();
  const productPreviews = new Map();
  const decodedHeroes = new WeakMap();
  const pendingHeroes = new WeakMap();
  const isProductPage = url => /^\/product\/[^/]+\/?$/.test(url.pathname);
  const eligible = url => url.origin === location.origin && !url.search && !url.hash && ['/', '/shop'].includes(url.pathname);
  const isLocalSearchEntry = url => searchEntry && url.origin === location.origin && url.pathname === '/search' && !url.search && !url.hash;

  function readProductPreview(link, url) {
    if (!isProductPage(url)) return null;
    const card = link.closest?.('.product-card, .cart-item-card, .pcw-card, .tl-arrival-card');
    const photo = link.querySelector?.('img') || card?.querySelector('img');
    const title = card?.querySelector('.product-card-name, .cart-item-name, .pcw-title, .tl-arrival-name');
    const name = (title?.getAttribute('title') || photo?.alt || title?.textContent || link.getAttribute('title') || '').trim();
    let image = null;
    try {
      const source = photo?.currentSrc || photo?.src;
      const parsed = source && new URL(source, url.href);
      if (parsed && ['http:', 'https:'].includes(parsed.protocol)) image = parsed.href;
    } catch (_) { /* Missing or invalid photos use the standard placeholder. */ }
    return name || image ? { name, image, photo, width: photo?.naturalWidth, height: photo?.naturalHeight, savedAt: Date.now() } : null;
  }

  function currentProductPreview(url) {
    const preview = productPreviews.get(url.pathname);
    if (preview && Date.now() - preview.savedAt < productPreviewLifetime) return preview;
    productPreviews.delete(url.pathname);
    return null;
  }

  function renderProductPreview(loading, url, labels, sourcePhoto = null) {
    loading.classList.add('catalog-transient-loading--product');
    const preview = currentProductPreview(url);
    const name = preview?.name || labels.product;
    const content = document.createElement('div');
    content.className = 'catalog-product-preview product-page page';
    const breadcrumb = document.createElement('nav');
    breadcrumb.className = 'catalog-product-breadcrumb breadcrumb';
    breadcrumb.setAttribute('aria-label', labels.breadcrumb);
    for (const [path, label] of [['/', labels.home], ['/shop', labels.shop]]) {
      const link = document.createElement('a');
      link.href = new URL(path, location.origin).href;
      link.textContent = label;
      const divider = document.createElement('span');
      divider.textContent = '/';
      divider.setAttribute('aria-hidden', 'true');
      breadcrumb.append(link, divider);
    }
    const current = document.createElement('strong');
    current.textContent = name;
    breadcrumb.appendChild(current);
    const grid = document.createElement('div');
    grid.className = 'catalog-product-layout product-layout';
    const gallery = document.createElement('div');
    gallery.className = 'gallery-wrap';
    const stage = document.createElement('div');
    stage.className = 'gallery-stage';
    const media = document.createElement('div');
    media.className = 'gallery-main';
    const open = document.createElement('div');
    open.className = 'gallery-open';
    const expand = document.createElement('span');
    expand.className = 'gallery-expand';
    expand.textContent = '⤢';
    expand.setAttribute('aria-hidden', 'true');
    let photo;
    const fallback = new URL('/images/product-placeholder.svg', location.origin).href;
    if (preview?.image && sourcePhoto?.complete && sourcePhoto.naturalWidth > 0 && sourcePhoto.parentNode) {
      // Reuse the decoded image itself: a new img can fetch again on no-store
      // responses and wait behind the product request, leaving a white frame.
      const marker = document.createComment('Product preview image');
      sourcePhoto.replaceWith(marker);
      previewImageLease = { photo: sourcePhoto, marker };
      photo = sourcePhoto;
    } else {
      photo = document.createElement('img');
      photo.alt = name;
      photo.loading = 'eager';
      photo.fetchPriority = 'high';
      photo.src = preview?.image || fallback;
      photo.addEventListener('error', () => { if (photo.src !== fallback) photo.src = fallback; }, { once: true });
    }
    function sizePreview() {
      const width = photo.naturalWidth || preview?.width;
      const height = photo.naturalHeight || preview?.height;
      if (width && height) gallery.style.setProperty('--gallery-ratio', `${width} / ${height}`);
    }
    sizePreview();
    photo.addEventListener('load', sizePreview, { once: true });
    open.append(photo, expand);
    media.appendChild(open);
    const strip = document.createElement('div');
    strip.className = 'gallery-thumbs';
    const thumb = document.createElement('div');
    thumb.className = 'gallery-thumb active';
    thumb.setAttribute('aria-hidden', 'true');
    const thumbPhoto = photo.cloneNode(false);
    thumbPhoto.removeAttribute('id');
    thumbPhoto.alt = '';
    thumbPhoto.addEventListener('error', () => { if (thumbPhoto.src !== fallback) thumbPhoto.src = fallback; }, { once: true });
    thumb.appendChild(thumbPhoto);
    strip.appendChild(thumb);
    const controls = document.createElement('div');
    controls.className = 'gallery-controls';
    controls.setAttribute('aria-hidden', 'true');
    for (const symbol of ['‹', '•', '›']) {
      const control = document.createElement('span');
      control.className = 'gallery-arrow';
      control.textContent = symbol;
      controls.appendChild(control);
    }
    stage.append(media, controls);
    gallery.append(strip, stage);
    const info = document.createElement('div');
    info.className = 'catalog-product-info';
    const heading = document.createElement('h1');
    heading.textContent = name;
    info.appendChild(heading);
    const shapes = document.createElement('div');
    shapes.className = 'catalog-product-details';
    shapes.setAttribute('aria-hidden', 'true');
    for (let i = 0; i < 3; i++) {
      const block = document.createElement('div');
      block.className = 'nav-shell-block';
      shapes.appendChild(block);
    }
    const status = document.createElement('p');
    status.className = 'nav-shell-status';
    status.textContent = labels.productLoading;
    info.append(shapes, status);
    grid.append(gallery, info);
    content.append(breadcrumb, grid);
    loading.appendChild(content);
    document.title = name + ' — Ramo Store';
    return status;
  }

  function updateNavigation(path) {
    document.querySelectorAll('#mob-nav a[href]').forEach(link => {
      link.classList.remove('is-opening');
      const linkPath = new URL(link.href).pathname;
      const active = linkPath === path || (linkPath === '/account' && path.startsWith('/account/'));
      link.classList.toggle('on', active);
      if (active) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });
    document.querySelectorAll('.nav-links a[href], .storefront-drawer .nav-mobile-links a[href]').forEach(link => link.classList.toggle('active', new URL(link.href).pathname === path));
    document.querySelectorAll('.nav-notifications-top').forEach(link => {
      if (path === '/account/notifications') link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });
    document.querySelectorAll('.header-search-link').forEach(link => {
      if (path === '/search') link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });
    document.querySelectorAll('.nav-language-btn, .nav-mobile-language-link').forEach(link => {
      const url = new URL(link.href);
      url.searchParams.set('redirect', new URL(path, location.origin).href);
      link.href = url.href;
    });
  }

  function clearTransientLoading() {
    clearTimeout(loadingTimer);
    if (previewImageLease) {
      const { photo, marker } = previewImageLease;
      if (marker.parentNode) marker.replaceWith(photo);
      previewImageLease = null;
    }
    transientLoading?.remove();
    transientLoading = null;
    host.removeAttribute('aria-busy');
  }

  function discardFrame() {
    const previous = frame;
    frame = null;
    if (previous?.dataset.ready === '1') {
      previous.contentDocument?.dispatchEvent(new Event('catalog:leaving'));
    }
    previous?.remove();
    clearTransientLoading();
  }

  function beginTransientLoading(url, sourcePhoto = null) {
    clearTransientLoading();
    const kind = url.pathname === '/cart' ? 'cart' : url.pathname === '/account/notifications' ? 'notifications' : url.pathname.startsWith('/account') ? 'account' : 'page';
    const labels = locale === 'ar'
      ? { cart: 'السلة', notifications: 'الإشعارات', account: 'حسابي', page: 'جارٍ فتح الصفحة', product: 'المنتج', productLoading: 'جارٍ تحميل تفاصيل المنتج…', home: 'الرئيسية', shop: 'المتجر', breadcrumb: 'مسار التصفح', loading: 'جارٍ التحميل…', slow: 'التحميل يستغرق وقتًا أطول من المعتاد.', retry: 'حاول مرة أخرى' }
      : { cart: 'Your Cart', notifications: 'Notifications', account: 'My Account', page: 'Opening page', product: 'Product', productLoading: 'Loading product details…', home: 'Home', shop: 'Shop', breadcrumb: 'Breadcrumb', loading: 'Loading…', slow: 'This is taking longer than expected.', retry: 'Try again' };
    const loading = document.createElement('section');
    loading.className = 'catalog-transient-loading';
    loading.setAttribute('role', 'status');
    loading.setAttribute('aria-live', 'polite');
    const heading = document.createElement('h1');
    heading.textContent = labels[kind];
    const shapes = document.createElement('div');
    shapes.className = 'nav-shell-shapes nav-shell-shapes--' + kind;
    shapes.setAttribute('aria-hidden', 'true');
    for (let i = 0; i < 3; i++) {
      const block = document.createElement('div');
      block.className = 'nav-shell-block';
      shapes.appendChild(block);
    }
    const status = document.createElement('p');
    status.className = 'nav-shell-status';
    status.textContent = labels.loading;
    loading.append(heading, shapes, status);
    let progress = status;
    if (isProductPage(url)) {
      loading.replaceChildren();
      progress = renderProductPreview(loading, url, labels, sourcePhoto);
    }
    transientLoading = loading;
    host.inert = false;
    host.setAttribute('aria-busy', 'true');
    host.appendChild(loading);
    document.body.classList.remove('account-page', 'shop-filter-open');
    updateNavigation(url.pathname);
    loadingTimer = setTimeout(() => {
      progress.textContent = labels.slow + ' ';
      const retry = document.createElement('a');
      retry.href = url.href;
      retry.textContent = labels.retry;
      progress.appendChild(retry);
    }, 15000);
  }

  function activate(path, view) {
    discardFrame();
    host.inert = false;
    document.body.classList.remove('account-page', 'mobile-auth-screen', 'mobile-auth-login', 'shop-filter-open');
    views.forEach(item => {
      const active = item === view;
      item.element.dataset.catalogActive = active ? '1' : '0';
      item.element.inert = !active;
      item.element.setAttribute('aria-hidden', String(!active));
    });
    activePath = path;
    document.title = view.title;
    document.querySelector('link[rel="canonical"]')?.setAttribute('href', new URL(path, location.origin).href);
    updateNavigation(path);
    if (view.scripts && !view.initialized) {
      view.initialized = true;
      for (const original of view.scripts) {
        const script = document.createElement('script');
        for (const attribute of original.attributes) script.setAttribute(attribute.name, attribute.value);
        script.textContent = original.textContent;
        view.element.appendChild(script);
      }
      view.scripts = null;
      window._pcInitializeVariations?.(view.element);
    }
    if (view.initialized) {
      document.dispatchEvent(new CustomEvent('catalog:shown', { detail: { path, element: view.element } }));
      if (path !== '/search') window.refreshWishlistState?.();
    }
  }

  function showSearchEntry(url, push) {
    const path = url.pathname;
    if (push && location.href !== url.href) history.pushState(null, '', path);
    let view = views.get(path);
    if (!view) {
      const element = document.createElement('div');
      element.className = 'catalog-view';
      element.dataset.catalogView = path;
      element.appendChild(searchEntry.content.cloneNode(true));
      view = { element, title: searchEntry.dataset.title, initialized: true };
      views.set(path, view);
      host.appendChild(element);
    }
    activate(path, view);
    if (push) view.element.querySelector('input[name="q"]')?.focus({ preventScroll: true });
  }

  async function show(path, push = true) {
    if (!['/', '/shop', '/search'].includes(initialPath) && views.has(initialPath)) {
      first.remove();
      initialStyles.forEach(style => style.remove());
      views.delete(initialPath);
    }
    const existing = views.get(path);
    if (existing && Date.now() - existing.loadedAt >= catalogLifetime) {
      location.assign(path);
      return;
    }
    if (push && location.pathname !== path) history.pushState(null, '', path);
    if (existing) {
      activate(path, existing);
      return;
    }
    const element = document.createElement('div');
    element.id = path === '/' ? 'catalog-view-home' : 'catalog-view-shop';
    element.className = 'catalog-view';
    element.dataset.catalogView = path;
    const heading = document.createElement('h1');
    heading.textContent = locale === 'ar' ? (path === '/' ? 'الرئيسية' : 'المتجر') : (path === '/' ? 'Home' : 'Shop');
    const loading = document.createElement('div');
    loading.className = 'catalog-view-loading';
    loading.setAttribute('role', 'status');
    loading.setAttribute('aria-label', locale === 'ar' ? 'جارٍ التحميل' : 'Loading');
    loading.appendChild(heading);
    const shapes = document.createElement('div');
    shapes.className = 'nav-shell-shapes nav-shell-shapes--shop';
    shapes.setAttribute('aria-hidden', 'true');
    for (let i = 0; i < 5; i++) {
      const shape = document.createElement('div');
      shape.className = 'nav-shell-block';
      shapes.appendChild(shape);
    }
    loading.appendChild(shapes);
    element.appendChild(loading);
    const view = { element, title: heading.textContent + ' — Ramo Store', loadedAt: Date.now(), initialized: false };
    views.set(path, view);
    host.appendChild(element);
    activate(path, view);
    try {
      const response = await fetch(path, { cache: 'no-store', credentials: 'same-origin', headers: { Accept: 'text/html' } });
      if (!response.ok || response.redirected) throw new Error('Catalog navigation failed');
      const page = new DOMParser().parseFromString(await response.text(), 'text/html');
      const source = page.querySelector('[data-catalog-view]');
      if (!source || page.querySelector('meta[name="catalog-session"]')?.content !== scope || page.documentElement.lang !== locale) throw new Error('Session or language changed');
      const scripts = [...source.querySelectorAll('script'), ...page.querySelectorAll('#storefront-page-scripts script')];
      source.querySelectorAll('script').forEach(script => script.remove());
      for (const style of page.querySelectorAll('head > style:not(#storefront-layout-css)')) document.head.appendChild(style.cloneNode(true));
      element.replaceChildren(...source.childNodes);
      view.title = page.title;
      view.scripts = scripts;
      view.loadedAt = Date.now();
      if (activePath === path) activate(path, view);
    } catch (_) {
      // A real navigation handles authentication changes and server errors normally.
      if (activePath === path) location.assign(path);
      else { views.delete(path); element.remove(); }
    }
  }

  const supportedPage = url => url.origin === location.origin
    && !/^\/(?:admin|seller|api|auth|language|login|register|logout|vendor-logout|checkout|order-success|email|forgot-password|reset-password|vendor-register)(?:\/|$)/.test(url.pathname)
    && !/\.[a-z0-9]{2,5}$/i.test(url.pathname);

  function presentFrame(requestedFrame) {
    if (frame !== requestedFrame) return;
    try {
      const current = new URL(requestedFrame.contentWindow.location.href);
      if (current.href === 'about:blank') return;
      if (!supportedPage(current)) { location.assign(current.href); return; }
      if (eligible(current)) { history.replaceState(null, '', current.pathname); show(current.pathname, false); return; }
      const doc = requestedFrame.contentDocument;
      if (!doc?.body?.childElementCount) return;
      // The outer navigation and cached catalog belong to one language. A
      // redirected page in another language must rebuild that outer document.
      if (doc.documentElement.lang !== locale) { location.assign(current.href); return; }
      // Reveal the real gallery only after its hero is decoded. Other page
      // images keep loading normally without delaying this handoff.
      const hero = isProductPage(current) ? doc.querySelector?.('#main-img') : null;
      const heroUrl = hero?.currentSrc || hero?.src;
      if (heroUrl && hero.style.display !== 'none' && decodedHeroes.get(hero) !== heroUrl) {
        if (pendingHeroes.get(hero) === heroUrl) return;
        pendingHeroes.set(hero, heroUrl);
        const reveal = async () => {
          try { await hero.decode?.(); } catch (_) { /* Broken photos use the page's fallback. */ }
          if ((hero.currentSrc || hero.src) === heroUrl) decodedHeroes.set(hero, heroUrl);
          if (pendingHeroes.get(hero) === heroUrl) pendingHeroes.delete(hero);
          presentFrame(requestedFrame);
        };
        if (hero.complete) reveal();
        else {
          hero.addEventListener('load', reveal, { once: true });
          hero.addEventListener('error', reveal, { once: true });
        }
        return;
      }
      document.title = doc.title;
      history.replaceState(null, '', current.pathname + current.search + current.hash);
      if (!connectedDocuments.has(doc)) {
        connectedDocuments.add(doc);
        doc.addEventListener('click', event => handleClick(event, current), true);
        doc.addEventListener('submit', event => {
          const form = event.target;
          const action = new URL(form.action, current.href);
          if (!supportedPage(action)) form.target = '_top';
          else handleSubmit(event, current);
        }, true);
        requestedFrame.contentWindow.addEventListener('beforeunload', () => {
          if (frame !== requestedFrame) return;
          requestedFrame.dataset.ready = '0';
          requestedFrame.inert = true;
          requestedFrame.setAttribute('aria-hidden', 'true');
          beginTransientLoading(current);
        });
      }
      clearTransientLoading();
      host.inert = true;
      requestedFrame.inert = false;
      requestedFrame.removeAttribute('aria-hidden');
      requestedFrame.dataset.ready = '1';
    } catch (_) { location.assign(requestedFrame.src); }
  }

  function visit(url, push = true, preview = null) {
    if (isLocalSearchEntry(url)) { showSearchEntry(url, push); return; }
    if (eligible(url)) { show(url.pathname, push); return; }
    if (!supportedPage(url)) { location.assign(url.href); return; }
    if (preview && isProductPage(url)) {
      // Keep only recent public thumbnail metadata for Back/Forward and retries.
      productPreviews.delete(url.pathname);
      productPreviews.set(url.pathname, { name: preview.name, image: preview.image, width: preview.width, height: preview.height, savedAt: preview.savedAt });
      if (productPreviews.size > 20) productPreviews.delete(productPreviews.keys().next().value);
    }
    if (push && location.href !== url.href) history.pushState(null, '', url.pathname + url.search + url.hash);
    views.forEach(view => {
      view.element.dataset.catalogActive = '0';
      view.element.inert = true;
      view.element.setAttribute('aria-hidden', 'true');
    });
    activePath = url.pathname + url.search;
    discardFrame();
    beginTransientLoading(url, preview?.photo);
    frame = document.createElement('iframe');
    frame.className = 'catalog-transient-frame';
    frame.title = locale === 'ar' ? 'صفحة المتجر' : 'Store page';
    frame.dataset.ready = '0';
    frame.inert = true;
    frame.setAttribute('aria-hidden', 'true');
    frame.src = url.href;
    const requestedFrame = frame;
    frame.addEventListener('load', () => presentFrame(requestedFrame));
    document.body.appendChild(frame);
  }

  // The destination announces its rendered markup without waiting for images or fonts.
  window.addEventListener('message', event => {
    if (event.origin !== location.origin || !frame || event.source !== frame.contentWindow || event.data?.type !== 'storefront:ready') return;
    presentFrame(frame);
  });

  function handleClick(event, base = new URL(location.href)) {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const link = event.target.closest('a[href]');
    if (!link || link.target || link.hasAttribute('download') || link.getAttribute('href')?.startsWith('#')) return;
    const url = new URL(link.href, base.href);
    if (url.origin !== location.origin) return;
    if (!eligible(url) && !supportedPage(url)) {
      if (frame && event.view !== window) { event.preventDefault(); location.assign(url.href); }
      return;
    }
    event.preventDefault();
    if (eligible(url)) {
      if (url.pathname !== activePath || !views.has(activePath) || Date.now() - views.get(activePath).loadedAt >= catalogLifetime) show(url.pathname);
    } else visit(url, true, readProductPreview(link, url));
  }
  function handleSubmit(event, base = new URL(location.href)) {
    const form = event.target;
    if (event.defaultPrevented || form.method.toLowerCase() !== 'get' || form.target) return;
    const url = new URL(form.action, base.href);
    if (!supportedPage(url)) return;
    url.search = '';
    const data = new FormData(form);
    if (event.submitter?.name) data.append(event.submitter.name, event.submitter.value);
    for (const [name, value] of data) if (typeof value === 'string') url.searchParams.append(name, value);
    event.preventDefault();
    visit(url);
  }
  document.addEventListener('click', event => handleClick(event), true);
  document.addEventListener('submit', event => handleSubmit(event), true);
  window.addEventListener('popstate', () => {
    const url = new URL(location.href);
    visit(url, false);
  });
})();

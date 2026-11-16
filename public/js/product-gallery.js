/* Responsive product gallery. No third-party scripts or image services. */
(() => {
  'use strict';
  function imageLoader({ createImage, ready, failed, busy }) {
    let version = 0;
    return url => {
      const request = ++version;
      busy(true);
      const image = createImage();
      image.onload = async () => {
        try { await image.decode?.(); } catch (_) { /* load already succeeded */ }
        if (request !== version) return;
        ready(url);
        busy(false);
      };
      image.onerror = () => {
        if (request !== version) return;
        busy(false);
        failed(url);
      };
      image.src = url;
    };
  }
  if (typeof module !== 'undefined' && module.exports) module.exports = { imageLoader };
  if (typeof document === 'undefined') return;
  const root = document.querySelector('[data-product-gallery]');
  if (!root) return;
  const strip = root.querySelector('.gallery-thumbs');
  const main = root.querySelector('#main-img');
  // Size the narrow-screen frame from the first photo, then keep it stable
  // while browsing so the thumbnails and product details do not jump around.
  let aspectSet = false;
  function setInitialAspect() {
    if (aspectSet || !main.naturalWidth || !main.naturalHeight) return;
    root.style.setProperty('--gallery-ratio', `${main.naturalWidth} / ${main.naturalHeight}`);
    aspectSet = true;
  }
  main.addEventListener('load', setInitialAspect);
  if (main.complete) setInitialAspect();
  const status = root.querySelector('.gallery-image-status');
  const controls = root.querySelector('.gallery-controls');
  const dots = root.querySelector('.gallery-dots');
  const dialog = document.getElementById('product-gallery-dialog');
  const large = dialog.querySelector('img');
  const counter = dialog.querySelector('.gallery-counter');
  const ar = document.documentElement.lang === 'ar';
  let urls = [], selected = main.getAttribute('src') || '', opener;
  const absolute = url => new URL(url, document.baseURI).href;
  const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
  const load = imageLoader({
    createImage: () => new Image(),
    busy: value => {
      root.setAttribute('aria-busy', String(value));
      status.textContent = value ? (ar ? 'جارٍ تحميل الصورة…' : 'Loading image…') : '';
    },
    ready: url => {
      main.src = url;
      main.style.display = '';
      const placeholder = document.getElementById('main-img-placeholder');
      if (placeholder) placeholder.style.display = 'none';
      large.src = url;
      main.animate?.([{ opacity: .65 }, { opacity: 1 }], { duration: reduced() ? 0 : 180 });
      const index = urls.indexOf(url);
      // Warm only adjacent photos rather than downloading the whole gallery.
      for (const next of [urls[index - 1], urls[index + 1]]) if (next) { const img = new Image(); img.src = next; }
    },
    failed: () => {
      if (main.getAttribute('src')) { selected = absolute(main.src); sync(); }
      status.textContent = ar ? 'تعذّر تحميل الصورة. اختار صورة تانية أو حاول مرة تانية.' : 'Image could not load. Choose another photo or try again.';
    },
  });
  function sync() {
    const index = urls.indexOf(selected);
    strip.querySelectorAll('.gallery-thumb').forEach(button => {
      const active = absolute(button.querySelector('img').src) === selected;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
      if (active && strip.clientHeight) {
        const item = button.getBoundingClientRect(), rail = strip.getBoundingClientRect();
        if (item.top < rail.top) strip.scrollTop -= rail.top - item.top;
        else if (item.bottom > rail.bottom) strip.scrollTop += item.bottom - rail.bottom;
        if (item.left < rail.left) strip.scrollLeft -= rail.left - item.left;
        else if (item.right > rail.right) strip.scrollLeft += item.right - rail.right;
      }
    });
    [...dots.children].forEach((button, i) => button.setAttribute('aria-current', String(i === index)));
    controls.hidden = urls.length < 2;
    dialog.querySelector('.gallery-controls').hidden = urls.length < 2;
    counter.textContent = `${index + 1} / ${urls.length}`;
    const active = dots.children[index];
    if (active) dots.scrollLeft = active.offsetLeft - dots.offsetLeft - dots.clientWidth / 2 + active.offsetWidth / 2;
  }
  function refresh() {
    urls = [...new Set([...strip.querySelectorAll('img')].map(image => absolute(image.src)))];
    dots.replaceChildren(...urls.map((url, index) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'gallery-dot';
      button.setAttribute('aria-label', ar ? `عرض الصورة ${index + 1}` : `View image ${index + 1}`);
      button.addEventListener('click', () => select(url, true));
      return button;
    }));
    sync();
  }
  function select(url, manual = false) {
    if (!url) return;
    url = absolute(url);
    if (!urls.includes(url)) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'gallery-thumb';
      button.setAttribute('aria-label', ar ? 'عرض صورة المنتج' : 'View product image');
      const image = document.createElement('img');
      image.src = url;
      image.alt = main.alt;
      image.loading = 'lazy';
      button.append(image);
      strip.append(button);
      refresh();
    }
    selected = url;
    large.classList.remove('is-zoomed');
    sync();
    load(url);
    if (manual) root.dispatchEvent(new CustomEvent('gallery:select', { detail: { url } }));
  }
  function step(delta) {
    if (urls.length < 2) return;
    const index = urls.indexOf(selected);
    select(urls[(index + delta + urls.length) % urls.length], true);
  }
  strip.addEventListener('click', event => {
    const button = event.target.closest('.gallery-thumb');
    if (button) select(button.querySelector('img').src, true);
  });
  for (const scope of [root, dialog]) {
    scope.querySelectorAll('[data-gallery-step]').forEach(button => button.addEventListener('click', () => step(Number(button.dataset.galleryStep))));
    scope.addEventListener('keydown', event => {
      if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
      event.preventDefault();
      step(event.key === 'ArrowRight' ? 1 : -1);
    });
  }
  // A horizontal gesture changes images; vertical gestures remain page scrolls.
  for (const surface of [root.querySelector('.gallery-open'), large]) {
    let gesture;
    surface.addEventListener('pointerdown', event => {
      gesture = { x: event.clientX, y: event.clientY, id: event.pointerId };
      surface.setPointerCapture(event.pointerId);
    });
    surface.addEventListener('pointercancel', () => { gesture = null; });
    surface.addEventListener('pointerup', event => {
      if (!gesture || gesture.id !== event.pointerId) return;
      const dx = event.clientX - gesture.x, dy = event.clientY - gesture.y;
      gesture = null;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.5) {
        surface.dataset.swiped = '1';
        step(dx < 0 ? 1 : -1);
      }
    });
    surface.addEventListener('click', event => {
      if (surface.dataset.swiped) { delete surface.dataset.swiped; event.preventDefault(); event.stopImmediatePropagation(); }
    }, true);
  }
  root.querySelector('.gallery-open').addEventListener('click', () => {
    if (!selected || main.style.display === 'none') return;
    opener = document.activeElement;
    large.src = main.src;
    large.alt = main.alt;
    dialog.showModal();
  });
  dialog.querySelector('.gallery-lightbox-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  dialog.addEventListener('close', () => { large.classList.remove('is-zoomed'); opener?.focus({ preventScroll: true }); });
  large.addEventListener('click', () => large.classList.toggle('is-zoomed'));
  new MutationObserver(refresh).observe(strip, { childList: true });
  new ResizeObserver(([entry]) => { strip.style.maxHeight = `${entry.contentRect.height}px`; }).observe(root.querySelector('.gallery-main'));
  refresh();
  if (selected) selected = absolute(selected);
  sync();
  window.productGallery = { select, refresh };
})();

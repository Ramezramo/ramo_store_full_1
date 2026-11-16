(() => {
  const header = document.querySelector('.storefront-header');
  const menu = document.getElementById('nav-mobile-menu');
  const trigger = document.getElementById('nav-hamburger');
  const bottomTrigger = document.getElementById('mobile-menu-trigger');
  const triggers = [trigger, bottomTrigger].filter(Boolean);
  if (!header || !menu || !triggers.length) return;
  const panel = menu.querySelector('.nav-mobile-panel');
  const searchToggle = header.querySelector('.header-search-toggle');
  const searchInput = header.querySelector('input[name="q"]');
  let previousOverflow = '';
  const locked = new Map();
  let openerTrigger = trigger || bottomTrigger;

  function visibleTrigger(preferred) {
    return triggers.find(button => button === preferred && button.getClientRects().length)
      || triggers.find(button => button.getClientRects().length);
  }

  function updateScrollState() {
    const page = document.documentElement.classList.contains('catalog-keep-alive')
      ? document.querySelector('.catalog-view[data-catalog-active="1"]')
      : null;
    const top = page ? page.scrollTop : (window.scrollY || document.documentElement.scrollTop || 0);
    header.classList.toggle('is-scrolled', top > 24);
  }
  function measure() {
    document.documentElement.style.setProperty('--storefront-header-height', Math.ceil(header.getBoundingClientRect().height) + 'px');
    updateScrollState();
  }
  function setSearchOpen(open, restoreFocus = true) {
    if (!searchToggle || !searchInput) return;
    header.classList.toggle('is-search-open', open);
    searchToggle.setAttribute('aria-expanded', String(open));
    if (open) searchInput.focus({ preventScroll: true });
    else if (restoreFocus) searchToggle.focus({ preventScroll: true });
  }
  function setOpen(open, restoreFocus = true, opener = null) {
    if (opener && triggers.includes(opener)) openerTrigger = opener;
    if (open === menu.classList.contains('open')) return;
    if (open) setSearchOpen(false, false);
    measure();
    menu.classList.toggle('open', open);
    header.classList.toggle('is-menu-open', open);
    triggers.forEach(button => {
      button.classList.toggle('open', open);
      button.setAttribute('aria-expanded', String(open));
    });
    menu.setAttribute('aria-hidden', String(!open));
    menu.inert = !open;
    if (open) {
      openerTrigger = visibleTrigger(openerTrigger) || openerTrigger;
      previousOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      const background = [document.getElementById('storefront-content')];
      const bottomNav = document.getElementById('mob-nav');
      if (bottomNav && bottomTrigger && bottomNav.contains(bottomTrigger)) {
        // Keep Menu available as the sheet's close control, while other tabs
        // and the current page cannot be activated behind the modal.
        background.push(...Array.from(bottomNav.children).filter(element => element !== bottomTrigger));
      } else if (bottomNav) background.push(bottomNav);
      background.forEach(element => {
        if (element) { locked.set(element, element.inert); element.inert = true; }
      });
      panel.querySelector('button,a[href]')?.focus({ preventScroll: true });
      // The drawer may still be hidden until its opening styles are applied.
      requestAnimationFrame(() => {
        if (menu.classList.contains('open') && !panel.contains(document.activeElement)) {
          panel.querySelector('button,a[href]')?.focus({ preventScroll: true });
        }
      });
    } else {
      document.body.style.overflow = previousOverflow;
      locked.forEach((inert, element) => { element.inert = inert; });
      locked.clear();
      if (restoreFocus) visibleTrigger(openerTrigger)?.focus({ preventScroll: true });
    }
  }
  window.RamoHeader = { setOpen, setSearchOpen, toggle: opener => setOpen(!menu.classList.contains('open'), true, opener) };
  searchToggle?.addEventListener('click', () => setSearchOpen(true));
  menu.inert = true;
  menu.addEventListener('keydown', event => {
    if (event.key !== 'Tab' || !menu.classList.contains('open')) return;
    const controls = [...panel.querySelectorAll('a[href],button:not([disabled])')].filter(element => element.getClientRects().length);
    const first = controls[0], last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
  });
  document.addEventListener('focusin', event => {
    if (menu.classList.contains('open') && !panel.contains(event.target) && event.target !== openerTrigger) {
      panel.querySelector('button,a[href]')?.focus({ preventScroll: true });
    }
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && menu.classList.contains('open')) { event.preventDefault(); setOpen(false); }
    else if (event.key === 'Escape' && header.classList.contains('is-search-open')) { event.preventDefault(); setSearchOpen(false); }
  });
  function reset() { setOpen(false, false); setSearchOpen(false, false); updateScrollState(); }
  document.addEventListener('catalog:shown', reset);
  window.addEventListener('popstate', reset);
  document.addEventListener('scroll', updateScrollState, { capture: true, passive: true });
  window.addEventListener('scroll', updateScrollState, { passive: true });
  document.addEventListener('click', event => {
    if (!header.contains(event.target)) setSearchOpen(false, false);
    // A newly mounted catalog placeholder is already at its own scroll position,
    // before its content is ready to announce catalog:shown.
    if (event.target.closest('a[href]')) updateScrollState();
  });
  header.addEventListener('click', event => {
    if (event.target.closest('a[href]')) setOpen(false, false);
  });
  header.addEventListener('submit', () => { setOpen(false, false); updateScrollState(); });
  if (typeof ResizeObserver === 'function') new ResizeObserver(measure).observe(header);
  window.addEventListener('resize', measure, { passive: true });
  window.addEventListener('pageshow', () => { reset(); measure(); });
  measure();
})();

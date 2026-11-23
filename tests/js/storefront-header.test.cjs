const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const { runInNewContext } = require('node:vm');

const source = readFileSync(resolve(__dirname, '../../public/js/storefront-header.js'), 'utf8');

function headerFixture({ hostInert = false, navInert = false, overflow = '', height = 56.25,
  keepAlive = false, windowScroll = 0, documentScroll = 0, cachedScroll = 0, bottomMenu = false } = {}) {
  let document;
  class Element {
    constructor(tag = 'div') {
      this.tagName = tag;
      this.children = [];
      this.listeners = new Map();
      this.listenerOptions = new Map();
      this.attributes = new Map();
      this.inert = false;
      this.visible = true;
      this.isConnected = true;
      this.focusCalls = [];
      this.style = { setProperty(name, value) { this[name] = value; } };
      const classes = new Set();
      this.classList = {
        contains: name => classes.has(name),
        toggle(name, force = !classes.has(name)) {
          if (force) classes.add(name); else classes.delete(name);
          return force;
        },
      };
    }
    append(...children) { children.forEach(child => { child.parent = this; this.children.push(child); }); }
    contains(element) { return element === this || this.children.some(child => child.contains(element)); }
    setAttribute(name, value) { this.attributes.set(name, String(value)); }
    getAttribute(name) { return this.attributes.get(name); }
    addEventListener(type, listener, options) {
      if (!this.listeners.has(type)) this.listeners.set(type, []);
      this.listeners.get(type).push(listener);
      if (!this.listenerOptions.has(type)) this.listenerOptions.set(type, []);
      this.listenerOptions.get(type).push(options);
    }
    emit(type, options = {}) {
      const event = { target: this, currentTarget: this, preventDefault() { this.defaultPrevented = true; }, ...options };
      this.listeners.get(type)?.forEach(listener => listener(event));
      return event;
    }
    getClientRects() { return this.visible ? [{}] : []; }
    focus(options) {
      this.focusCalls.push(options);
      if (this.rejectFocus) return;
      document.activeElement = this;
      document.emit('focusin', { target: this });
    }
    closest(selector) {
      if (selector === 'a[href]' && this.tagName === 'a' && this.href) return this;
      if (selector === '#' + this.id) return this;
      return this.parent?.closest(selector) || null;
    }
  }
  document = new Element('document');
  document.body = new Element('body');
  document.documentElement = new Element('html');
  document.documentElement.scrollTop = documentScroll;
  document.documentElement.classList.toggle('catalog-keep-alive', keepAlive);
  document.scrollingElement = document.documentElement;
  document.activeElement = document.body;
  document.body.style.overflow = overflow;
  const window = new Element('window');
  window.scrollY = windowScroll;
  const header = new Element('header');
  header.height = height;
  header.getBoundingClientRect = () => ({ height: header.height });
  const trigger = new Element('button');
  trigger.id = 'nav-hamburger';
  trigger.setAttribute('aria-expanded', 'false');
  const searchLink = new Element('a');
  searchLink.href = '/search';
  const logo = new Element('a');
  logo.href = '/';
  header.append(trigger, searchLink, logo);
  const menu = new Element();
  menu.setAttribute('aria-hidden', 'true');
  const panel = new Element();
  const close = new Element('button');
  const shop = new Element('a');
  shop.href = '/shop';
  const last = new Element('a');
  last.href = '/language/ar';
  const hidden = new Element('a');
  hidden.href = '/admin';
  hidden.visible = false;
  const disabled = new Element('button');
  disabled.disabled = true;
  panel.append(close, shop, last, hidden, disabled);
  panel.querySelector = () => close;
  panel.querySelectorAll = () => panel.children.filter(element => element.tagName === 'a' || !element.disabled);
  menu.append(panel);
  menu.querySelector = selector => selector === '.nav-mobile-panel' ? panel : null;
  const host = new Element();
  host.inert = hostInert;
  const cachedView = new Element();
  cachedView.scrollTop = cachedScroll;
  const inactiveView = new Element();
  inactiveView.scrollTop = 0;
  const horizontalWidget = new Element();
  horizontalWidget.scrollTop = 0;
  horizontalWidget.scrollLeft = 0;
  host.activeCatalogView = cachedView;
  host.append(cachedView, inactiveView, horizontalWidget);
  const nav = new Element();
  nav.id = 'mob-nav';
  nav.inert = navInert;
  const navLinks = ['/', '/shop', '/cart', '/account'].map(href => {
    const link = new Element('a');
    link.href = href;
    return link;
  });
  const bottomTrigger = bottomMenu ? new Element('button') : null;
  if (bottomTrigger) {
    bottomTrigger.id = 'mobile-menu-trigger';
    bottomTrigger.setAttribute('aria-expanded', 'false');
    bottomTrigger.setAttribute('aria-controls', 'nav-mobile-menu');
    nav.append(...navLinks.slice(0, 3), bottomTrigger, navLinks[3]);
  } else nav.append(...navLinks);
  nav.querySelectorAll = selector => selector === 'a[href]' ? navLinks : nav.children;
  document.querySelector = selector => selector === '.storefront-header' ? header
    : selector === '.catalog-view[data-catalog-active="1"]' ? host.activeCatalogView : null;
  const ids = { 'nav-mobile-menu': menu, 'nav-hamburger': trigger, 'mobile-menu-trigger': bottomTrigger,
    'storefront-content': host, 'mob-nav': nav };
  document.getElementById = id => ids[id] || null;
  document.querySelectorAll = selector => selector.includes('nav-mobile-menu') || selector.includes('menu-trigger')
    ? [trigger, bottomTrigger].filter(Boolean) : [];
  const animationFrames = [];
  const observers = [];
  class ResizeObserver {
    constructor(callback) { this.callback = callback; observers.push(this); }
    observe(element) { this.element = element; }
  }
  runInNewContext(source, { window, document, ResizeObserver, requestAnimationFrame: callback => animationFrames.push(callback) });
  return {
    document, window, header, menu, panel, trigger, searchLink, logo, close, shop, last, host, nav,
    cachedView, inactiveView, horizontalWidget, bottomTrigger, navLinks,
    api: window.RamoHeader, observers,
    flushAnimationFrames() { animationFrames.splice(0).forEach(callback => callback()); },
  };
}

test('opening settings exposes the drawer, locks the page, and focuses its first control', () => {
  const app = headerFixture();
  assert.equal(app.menu.inert, true);
  app.api.toggle();
  assert.equal(app.menu.classList.contains('open'), true);
  assert.equal(app.header.classList.contains('is-menu-open'), true);
  assert.equal(app.trigger.classList.contains('open'), true);
  assert.equal(app.trigger.getAttribute('aria-expanded'), 'true');
  assert.equal(app.menu.getAttribute('aria-hidden'), 'false');
  assert.equal(app.menu.inert, false);
  assert.equal(app.host.inert, true);
  assert.equal(app.nav.inert, true);
  assert.equal(app.document.body.style.overflow, 'hidden');
  assert.equal(app.document.activeElement, app.close);
  assert.equal(app.close.focusCalls[0].preventScroll, true);
  app.api.toggle();
  assert.equal(app.trigger.getAttribute('aria-expanded'), 'false');
  assert.equal(app.menu.getAttribute('aria-hidden'), 'true');
  assert.equal(app.menu.inert, true);
  assert.equal(app.menu.classList.contains('open'), false);
  assert.equal(app.header.classList.contains('is-menu-open'), false);
  assert.equal(app.trigger.classList.contains('open'), false);
});

test('closing restores each prior inert and overflow state even after repeated opens', () => {
  for (const hostInert of [false, true]) {
    for (const navInert of [false, true]) {
      for (const overflow of ['', 'auto', 'hidden']) {
        const app = headerFixture({ hostInert, navInert, overflow });
        app.api.setOpen(true);
        app.api.setOpen(true);
        app.api.setOpen(false);
        app.api.setOpen(false);
        assert.equal(app.host.inert, hostInert);
        assert.equal(app.nav.inert, navInert);
        assert.equal(app.document.body.style.overflow, overflow);
      }
    }
  }
});

test('the header height follows initial layout, its observer, and viewport changes', () => {
  const app = headerFixture();
  const height = () => app.document.documentElement.style['--storefront-header-height'];
  assert.equal(height(), '57px');
  assert.equal(app.observers[0].element, app.header);
  app.header.height = 104.1;
  app.observers[0].callback();
  assert.equal(height(), '105px');
  app.header.height = 72;
  app.window.emit('resize');
  assert.equal(height(), '72px');
  app.header.height = 56;
  app.window.emit('pageshow');
  assert.equal(height(), '56px');
});

test('Escape closes settings and restores focus to its trigger without scrolling', () => {
  const app = headerFixture();
  app.api.setOpen(true);
  const event = app.document.emit('keydown', { key: 'Escape' });
  assert.equal(event.defaultPrevented, true);
  assert.equal(app.menu.inert, true);
  assert.equal(app.document.activeElement, app.trigger);
  assert.equal(app.trigger.focusCalls.at(-1).preventScroll, true);
});

test('Tab wraps around visible enabled drawer controls in both directions', () => {
  const app = headerFixture();
  app.api.setOpen(true);
  const reverse = app.menu.emit('keydown', { key: 'Tab', shiftKey: true });
  assert.equal(reverse.defaultPrevented, true);
  assert.equal(app.document.activeElement, app.last);
  const forward = app.menu.emit('keydown', { key: 'Tab' });
  assert.equal(forward.defaultPrevented, true);
  assert.equal(app.document.activeElement, app.close);
  app.shop.focus();
  assert.equal(app.menu.emit('keydown', { key: 'Tab' }).defaultPrevented, undefined);
  app.api.setOpen(false);
  assert.equal(app.menu.emit('keydown', { key: 'Tab' }).defaultPrevented, undefined);
});

test('focus that escapes an open drawer returns to its first control', () => {
  const app = headerFixture();
  app.api.setOpen(true);
  app.searchLink.focus();
  assert.equal(app.document.activeElement, app.close);
  app.shop.focus();
  assert.equal(app.document.activeElement, app.shop);
});

test('opening retries focus after the drawer becomes visible', () => {
  const app = headerFixture();
  app.close.rejectFocus = true;
  app.api.setOpen(true);
  assert.equal(app.document.activeElement, app.document.body);
  app.close.rejectFocus = false;
  app.flushAnimationFrames();
  assert.equal(app.document.activeElement, app.close);
});

test('a late opening frame cannot steal focus after the drawer closes', () => {
  const app = headerFixture();
  app.close.rejectFocus = true;
  app.api.setOpen(true);
  app.api.setOpen(false);
  app.searchLink.focus();
  app.close.rejectFocus = false;
  app.flushAnimationFrames();
  assert.equal(app.document.activeElement, app.searchLink);
});

test('cached navigation and browser history close settings without moving focus', () => {
  for (const [target, event] of [['document', 'catalog:shown'], ['window', 'popstate'], ['window', 'pageshow']]) {
    const app = headerFixture({ overflow: 'auto', hostInert: true });
    app.api.setOpen(true);
    const focused = app.document.activeElement;
    app[target].emit(event);
    app.flushAnimationFrames();
    assert.equal(app.menu.inert, true);
    assert.equal(app.host.inert, true);
    assert.equal(app.nav.inert, false);
    assert.equal(app.document.body.style.overflow, 'auto');
    assert.equal(app.document.activeElement, focused);
    assert.equal(app.trigger.focusCalls.length, 0);
  }
});

test('the search link closes the drawer without moving focus', () => {
  const app = headerFixture();
  app.api.setOpen(true);
  const focused = app.document.activeElement;
  app.header.emit('click', { target: app.searchLink });
  assert.equal(app.menu.inert, true);
  assert.equal(app.document.activeElement, focused);
  assert.equal(app.trigger.focusCalls.length, 0);
});
test('glass starts only after the window has scrolled more than 24 pixels', () => {
  for (const [windowScroll, expected] of [[0, false], [24, false], [25, true], [500, true]]) {
    const app = headerFixture({ windowScroll });
    assert.equal(app.header.classList.contains('is-scrolled'), expected);
  }
  const app = headerFixture({ documentScroll: 80 });
  assert.equal(app.header.classList.contains('is-scrolled'), true);
  app.document.documentElement.scrollTop = 0;
  app.window.emit('scroll');
  assert.equal(app.header.classList.contains('is-scrolled'), false);
});

test('cached pages use the active view scroll position instead of window scrolling', () => {
  const app = headerFixture({ keepAlive: true, windowScroll: 300, cachedScroll: 0 });
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  app.cachedView.scrollTop = 25;
  app.document.emit('scroll', { target: app.cachedView });
  assert.equal(app.header.classList.contains('is-scrolled'), true);
  app.cachedView.scrollTop = 24;
  app.document.emit('scroll', { target: app.cachedView });
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  const capturedScroll = app.document.listenerOptions.get('scroll')?.some(options => options === true || options?.capture === true);
  assert.equal(capturedScroll, true, 'inner view scroll events must be captured because they do not bubble');
});

test('inactive views and horizontal widgets cannot replace the active page scroll state', () => {
  const app = headerFixture({ keepAlive: true });
  app.inactiveView.scrollTop = 900;
  app.document.emit('scroll', { target: app.inactiveView });
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  app.horizontalWidget.scrollLeft = 500;
  app.document.emit('scroll', { target: app.horizontalWidget });
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  app.cachedView.scrollTop = 200;
  app.document.emit('scroll', { target: app.cachedView });
  assert.equal(app.header.classList.contains('is-scrolled'), true);
  app.document.emit('scroll', { target: app.horizontalWidget });
  assert.equal(app.header.classList.contains('is-scrolled'), true);
});

test('switching cached pages immediately reflects their independently preserved scroll positions', () => {
  const app = headerFixture({ keepAlive: true, cachedScroll: 350 });
  assert.equal(app.header.classList.contains('is-scrolled'), true);
  app.host.activeCatalogView = app.inactiveView;
  app.document.emit('catalog:shown', { detail: { element: app.inactiveView } });
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  assert.equal(app.cachedView.scrollTop, 350);
  app.host.activeCatalogView = app.cachedView;
  app.window.emit('popstate');
  assert.equal(app.header.classList.contains('is-scrolled'), true);
  assert.equal(app.inactiveView.scrollTop, 0);
});

test('restored pages recheck scrolling on pageshow, resize, and header measurement', () => {
  const app = headerFixture();
  app.window.scrollY = 400;
  app.window.emit('pageshow', { persisted: true });
  assert.equal(app.header.classList.contains('is-scrolled'), true);
  app.window.scrollY = 0;
  app.window.emit('resize');
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  app.window.scrollY = 50;
  app.observers[0].callback();
  assert.equal(app.header.classList.contains('is-scrolled'), true);
  const cached = headerFixture({ keepAlive: true });
  cached.cachedView.scrollTop = 80;
  cached.window.emit('pageshow', { persisted: true });
  assert.equal(cached.header.classList.contains('is-scrolled'), true);
});

test('a navigation click updates glass before a new catalog page finishes loading', () => {
  const app = headerFixture({ keepAlive: true, cachedScroll: 350 });
  app.host.activeCatalogView = app.inactiveView;
  app.document.emit('click', { target: app.logo });
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  app.host.activeCatalogView = app.cachedView;
  app.document.emit('click', { target: app.logo });
  assert.equal(app.header.classList.contains('is-scrolled'), true);
});

test('negative overscroll returns the header to its opaque top state', () => {
  const app = headerFixture({ windowScroll: 100 });
  app.window.scrollY = -20;
  app.window.emit('scroll');
  assert.equal(app.header.classList.contains('is-scrolled'), false);
  const cached = headerFixture({ keepAlive: true, cachedScroll: 100 });
  cached.cachedView.scrollTop = -20;
  cached.document.emit('scroll', { target: cached.cachedView });
  assert.equal(cached.header.classList.contains('is-scrolled'), false);
});

test('bottom Menu opens the shared drawer and synchronizes both menu triggers', () => {
  const app = headerFixture({ bottomMenu: true });
  app.api.toggle(app.bottomTrigger);
  assert.equal(app.menu.inert, false);
  assert.equal(app.header.classList.contains('is-menu-open'), true);
  assert.equal(app.host.inert, true);
  assert.equal(app.nav.inert, false);
  assert.equal(app.bottomTrigger.inert, false);
  assert.equal(app.navLinks.every(link => link.inert), true);
  for (const trigger of [app.trigger, app.bottomTrigger]) {
    assert.equal(trigger.getAttribute('aria-expanded'), 'true');
    assert.equal(trigger.classList.contains('open'), true);
  }
  app.api.toggle(app.bottomTrigger);
  assert.equal(app.menu.inert, true);
  assert.equal(app.document.activeElement, app.bottomTrigger);
  assert.equal(app.navLinks.every(link => !link.inert), true);
  for (const trigger of [app.trigger, app.bottomTrigger]) {
    assert.equal(trigger.getAttribute('aria-expanded'), 'false');
    assert.equal(trigger.classList.contains('open'), false);
  }
});

test('Escape returns focus to the actual bottom opener', () => {
  const app = headerFixture({ bottomMenu: true });
  app.api.setOpen(true, true, app.bottomTrigger);
  app.document.emit('keydown', { key: 'Escape' });
  assert.equal(app.menu.inert, true);
  assert.equal(app.document.activeElement, app.bottomTrigger);
  assert.equal(app.bottomTrigger.focusCalls.at(-1).preventScroll, true);
  assert.equal(app.trigger.focusCalls.length, 0);
});

test('bottom Menu stays focusable while other navigation children preserve their inert states', () => {
  const app = headerFixture({ bottomMenu: true, overflow: 'auto' });
  app.navLinks[1].inert = true;
  app.api.toggle(app.bottomTrigger);
  app.bottomTrigger.focus();
  assert.equal(app.document.activeElement, app.bottomTrigger);
  assert.equal(app.bottomTrigger.inert, false);
  assert.equal(app.navLinks.every(link => link.inert), true);
  app.api.setOpen(false);
  assert.deepEqual(app.navLinks.map(link => link.inert), [false, true, false, false]);
  assert.equal(app.document.body.style.overflow, 'auto');
  assert.equal(app.document.activeElement, app.bottomTrigger);
});

test('closing after a breakpoint change restores focus to a visible menu trigger', () => {
  for (const fromBottom of [false, true]) {
    const app = headerFixture({ bottomMenu: true });
    const opener = fromBottom ? app.bottomTrigger : app.trigger;
    const fallback = fromBottom ? app.trigger : app.bottomTrigger;
    app.api.toggle(opener);
    opener.visible = false;
    app.window.emit('resize');
    app.document.emit('keydown', { key: 'Escape' });
    assert.equal(app.document.activeElement, fallback);
    assert.equal(opener.focusCalls.length, 0);
    assert.equal(app.menu.inert, true);
  }
});

test('cached navigation closes a bottom-opened menu without focusing either trigger', () => {
  const app = headerFixture({ bottomMenu: true });
  app.api.toggle(app.bottomTrigger);
  const focused = app.document.activeElement;
  app.document.emit('catalog:shown');
  app.flushAnimationFrames();
  assert.equal(app.menu.inert, true);
  assert.equal(app.document.activeElement, focused);
  assert.equal(app.trigger.focusCalls.length, 0);
  assert.equal(app.bottomTrigger.focusCalls.length, 0);
  assert.equal(app.navLinks.every(link => !link.inert), true);
});

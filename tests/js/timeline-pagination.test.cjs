const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const { runInNewContext } = require('node:vm');

function scriptFor(kind) {
  const filename = kind === 'widgets' ? 'timeline-pagination.blade.php' : 'timeline-sections.blade.php';
  const source = readFileSync(resolve(__dirname, '../../resources/views/web/partials', filename), 'utf8');
  const marker = kind === 'widgets' ? "const loader = document.getElementById('timeline-loader')" : "const loader = document.getElementById('all-products-infinite-";
  const position = source.indexOf(marker);
  assert.notEqual(position, -1, 'The production loader must exist');
  return source.slice(source.lastIndexOf('(function () {', position), source.indexOf('})();', position) + 5)
    .replace(/@json\([^\n]+\)/g, '"Loading label"');
}

function fixture(kind, { cached = true, active = true, height = 720 } = {}) {
  class Element {
    constructor() {
      this.dataset = {};
      this.attributes = new Map();
      this.events = new Map();
      this.hidden = false;
      this.disabled = false;
      this.isConnected = true;
      this.children = [];
      this.classList = { add() {}, remove() {}, toggle() {} };
    }
    setAttribute(name, value) { this.attributes.set(name, value); }
    removeAttribute(name) { this.attributes.delete(name); }
    getAttribute(name) { return this.attributes.get(name); }
    addEventListener(name, callback) { this.events.set(name, callback); }
    remove() { this.isConnected = false; }
    before(fragment) { calls.inserted.push(fragment); }
    append(fragment) { calls.inserted.push(fragment); }
    querySelectorAll() { return []; }
  }
  const calls = { requests: [], inserted: [], initialized: [], observers: [], documentEvents: new Map() };
  const view = cached ? Object.assign(new Element(), { clientHeight: height }) : null;
  if (view) view.dataset.catalogActive = active ? '1' : '0';
  const loader = new Element();
  Object.assign(loader.dataset, {
    url: kind === 'widgets' ? '/home/sections' : '/home/products',
    page: '2', nextPage: '2', loadingLabel: 'Loading', scrollLabel: 'More',
    errorLabel: 'Failed', retryLabel: 'Retry', endLabel: 'Finished', loadLabel: 'Load more',
  });
  const button = new Element(), status = new Element(), sentinel = new Element(), grid = new Element();
  const extraElements = new Map();
  loader.closest = () => view;
  loader.querySelector = selector => {
    if (selector.endsWith('-button]')) return button;
    if (selector.endsWith('-status]')) return status;
    if (selector.endsWith('-sentinel]')) return sentinel;
    if (!extraElements.has(selector)) extraElements.set(selector, new Element());
    return extraElements.get(selector);
  };
  const document = {
    documentElement: { classList: { contains: name => cached && name === 'catalog-keep-alive' } },
    getElementById: id => id.startsWith('all-products-grid-') ? grid : loader,
    contains: element => element.isConnected,
    addEventListener: (name, callback) => calls.documentEvents.set(name, callback),
    createElement: tag => {
      const element = new Element();
      if (tag === 'template') {
        element.content = new Element();
        element.content.children = [Object.assign(new Element(), { id: 'tl-sec-next' })];
        element.content.querySelectorAll = selector => selector === '.product-card' ? [new Element(), new Element()] : [];
      }
      return element;
    },
  };
  class Observer {
    constructor(callback, options) {
      this.callback = callback;
      this.options = options;
      this.observed = new Set();
      this.observeCalls = 0;
      calls.observers.push(this);
    }
    observe(element) { this.observed.add(element); this.observeCalls++; }
    unobserve(element) { this.observed.delete(element); }
    disconnect() { this.observed.clear(); }
    intersect(isIntersecting = true) {
      this.callback([{ target: sentinel, isIntersecting,
        // This sentinel is still below the visible scrolling area.
        boundingClientRect: { top: height + 600 }, intersectionRatio: isIntersecting ? .01 : 0 }]);
    }
  }
  const app = {
    document, URL, innerHeight: 900, location: { href: 'http://store.test/' },
    IntersectionObserver: Observer,
    fetch: (url, options) => new Promise((resolve, reject) => {
      calls.requests.push({ url: String(url), options, resolve, reject });
    }),
    initializeTimelineWidgets: section => calls.initialized.push(section),
    _pcInitializeVariations: section => calls.initialized.push(section),
  };
  app.window = app;
  runInNewContext(scriptFor(kind), app);
  return { calls, view, loader, sentinel, button, observer: calls.observers[0] };
}

for (const kind of ['widgets', 'products']) {
  test(`${kind}: prefetch uses the cached page scroll container and its height`, () => {
    const f = fixture(kind);
    assert.equal(f.observer.options.root, f.view);
    assert.equal(f.observer.options.rootMargin, '0px 0px 1440px 0px');
    assert.equal(f.calls.requests.length, 0);
    f.observer.intersect();
    assert.equal(f.calls.requests.length, 1, 'Approaching the loader starts loading before it is visible');
    assert.equal(new URL(f.calls.requests[0].url).searchParams.get(kind === 'widgets' ? 'page' : 'all_products_page'), '2');
    assert.equal(f.loader.attributes.get('aria-busy'), 'true');
  });

  test(`${kind}: repeated intersection notifications do not duplicate a pending request`, async () => {
    const f = fixture(kind);
    f.observer.intersect(false);
    assert.equal(f.calls.requests.length, 0);
    f.observer.intersect();
    f.observer.intersect();
    f.observer.intersect();
    assert.equal(f.calls.requests.length, 1);
    f.calls.requests[0].resolve({ ok: true, json: async () => ({ html: '<div id="tl-sec-next">Next page</div>', hasMore: true, nextPage: 3 }) });
    await new Promise(setImmediate);
    assert.equal(f.loader.attributes.get('aria-busy'), 'false');
    assert.ok(f.calls.inserted.length > 0, 'The prefetched batch is inserted');
    f.observer.intersect();
    assert.equal(f.calls.requests.length, 2);
    assert.equal(new URL(f.calls.requests[1].url).searchParams.get(kind === 'widgets' ? 'page' : 'all_products_page'), '3');
  });

  test(`${kind}: inactive cached pages wait and resume when Home is shown`, () => {
    const f = fixture(kind, { active: false });
    f.observer.intersect();
    assert.equal(f.calls.requests.length, 0);
    f.view.dataset.catalogActive = '1';
    const observations = f.observer.observeCalls;
    f.calls.documentEvents.get('catalog:shown')({ detail: { element: f.view } });
    assert.ok(f.observer.observeCalls > observations, 'Returning to cached Home checks the sentinel again');
    f.observer.intersect();
    assert.equal(f.calls.requests.length, 1);
  });

  test(`${kind}: normal document scrolling falls back to the viewport`, () => {
    const f = fixture(kind, { cached: false });
    assert.equal(f.observer.options.root, null);
    assert.equal(f.observer.options.rootMargin, '0px 0px 1800px 0px');
  });

  test(`${kind}: lookahead stays bounded on very short and tall screens`, () => {
    assert.equal(fixture(kind, { height: 300 }).observer.options.rootMargin, '0px 0px 1200px 0px');
    assert.equal(fixture(kind, { height: 1800 }).observer.options.rootMargin, '0px 0px 2400px 0px');
  });
}

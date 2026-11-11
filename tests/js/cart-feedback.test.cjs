const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const { runInNewContext } = require('node:vm');

function fixture() {
  class Element {
    constructor(text = '') {
      this.textContent = text;
      this.childNodes = [];
      this.dataset = {};
      this.attributes = new Map();
      this.disabled = false;
      this.classList = { add() {}, remove() {} };
    }
    append(...nodes) { this.childNodes.push(...nodes); }
    replaceChildren(...nodes) { this.childNodes = nodes; }
    getAttribute(name) { return this.attributes.get(name) ?? null; }
    setAttribute(name, value) { this.attributes.set(name, value); }
    removeAttribute(name) { this.attributes.delete(name); }
  }
  const button = new Element();
  const original = new Element('Add to Cart');
  button.append(original);
  button.setAttribute('aria-label', 'Add to Cart: Graphics card');
  const sticky = new Element();
  sticky.append(new Element('Add to Cart'));
  const status = new Element();
  const timers = new Map();
  const calls = { fetch: [], drawer: [], errors: [], finished: [], badge: [], events: [] };
  let respond, reject;
  const response = new Promise((resolve, fail) => { respond = resolve; reject = fail; });
  const app = {
    document: {
      getElementById: id => id === 'cart-request-status' ? status : id === 'add-to-cart-btn' ? button : null,
      querySelector: () => sticky,
      createElement: () => new Element(),
    },
    AbortController,
    setTimeout: (callback, duration) => { timers.set(callback, duration); return callback; },
    clearTimeout: callback => timers.delete(callback),
    fetch: (url, options) => {
      calls.fetch.push({ url, options });
      options.signal.addEventListener('abort', () => reject(Object.assign(new Error('Timed out'), { name: 'AbortError' })));
      return response;
    },
    CSRF_TOKEN: 'test-csrf',
    STOREFRONT_COPY: { adding: 'Adding…', added: 'Added to cart.', networkError: 'Connection failed.', sessionExpired: 'Session expired.', slowCart: 'Check your cart before trying again.', addError: 'Could not add.' },
    updateCartBadge: count => calls.badge.push(count),
    openAtcDrawer: data => calls.drawer.push(data),
    showCartErrors: messages => calls.errors.push(messages),
    notifyProductCartAddFinished: success => calls.finished.push(success),
    dispatchEvent: event => calls.events.push(event),
    CustomEvent: class { constructor(type, options) { this.type = type; this.detail = options.detail; } },
  };
  app.window = app;
  runInNewContext(readFileSync(resolve(__dirname, '../../public/js/cart-feedback.js'), 'utf8'), app);
  const layout = readFileSync(resolve(__dirname, '../../resources/views/layouts/app.blade.php'), 'utf8');
  runInNewContext(layout.slice(layout.indexOf('function cartRequestError('), layout.indexOf('// ── Added-to-cart drawer')), app);
  const add = () => app.addToCart(10, 'Graphics card', 100, '/card.jpg', 20, 1, null, null, button);
  const reply = data => respond({ status: 200, json: async () => data });
  return { app, button, original, sticky, status, timers, calls, add, reply, respond, reject };
}

test('a delayed add shows a spinner and progress text, blocks duplicate clicks, and confirms success', async () => {
  const f = fixture();
  const request = f.add();
  assert.equal(f.button.disabled, true);
  assert.equal(f.button.getAttribute('aria-busy'), 'true');
  assert.equal(f.button.childNodes[0].childNodes[0].className, 'cart-button-spinner');
  assert.equal(f.button.childNodes[0].childNodes[1].textContent, 'Adding…');
  assert.equal(f.status.textContent, 'Adding…');
  assert.match(f.button.getAttribute('aria-label'), /Graphics card.*Adding/);
  await f.add();
  assert.equal(f.calls.fetch.length, 1);
  assert.equal(f.calls.fetch[0].options.headers.Accept, 'application/json');
  f.reply({ success: true, count: 1, items: [], cart_total: 100 });
  await request;
  assert.equal(f.button.disabled, false);
  assert.equal(f.button.childNodes[0], f.original);
  assert.equal(f.button.getAttribute('aria-busy'), null);
  assert.equal(f.button.getAttribute('aria-label'), 'Add to Cart: Graphics card');
  assert.equal(f.button.dataset.adding, undefined);
  assert.equal(f.calls.drawer.length, 1);
  assert.equal(f.calls.events[0].type, 'cart:updated');
  assert.equal(f.status.textContent, 'Added to cart.');
  assert.equal(f.timers.size, 0);
});

for (const failure of ['network', 'invalid-json', 'stock', 'session']) {
  test(`the button recovers and reports a ${failure} failure without a false success`, async () => {
    const f = fixture();
    const request = f.add();
    if (failure === 'network') f.reject(new Error('Offline'));
    if (failure === 'invalid-json') f.respond({ status: 500, json: async () => { throw new SyntaxError('HTML response'); } });
    if (failure === 'stock') f.reply({ success: false, message: 'Out of stock' });
    if (failure === 'session') f.respond({ status: 419 });
    await request;
    assert.equal(f.button.disabled, false);
    assert.equal(f.button.childNodes[0], f.original);
    assert.equal(f.calls.drawer.length, 0);
    assert.equal(f.calls.errors.length, 1);
    assert.equal(f.calls.finished[0], false);
    assert.equal(f.timers.size, 0);
  });
}

test('a stalled request times out and releases its button without automatically retrying', async () => {
  const f = fixture();
  const request = f.add();
  const [[timeout, duration]] = f.timers;
  assert.equal(duration, 30000);
  timeout();
  await request;
  assert.equal(f.button.disabled, false);
  assert.equal(f.calls.errors[0][0], 'Check your cart before trying again.');
  assert.equal(f.calls.fetch.length, 1);
  assert.equal(f.timers.size, 0);
});

test('product primary and sticky buttons keep their spinners while selections refresh', () => {
  const f = fixture();
  Object.assign(f.app, {
    multiVariantMode: false, productCartRequestPending: true,
    singleQuantityBounds: () => ({ inCart: 0, minimum: 1, maximum: 5 }),
    requiredSelectionControl: () => null,
    updateCartSelectionSummary() {},
    PRODUCT_TEXT: { addToCart: 'Add to Cart', adding: 'Adding…', unavailable: 'Unavailable' },
  });
  const source = readFileSync(resolve(__dirname, '../../resources/views/web/product.blade.php'), 'utf8');
  runInNewContext(source.slice(source.indexOf('function updateAddButtonState()'), source.indexOf('function slugify(')), f.app);
  f.app.updateAddButtonState();
  const spinner = f.button.childNodes[0];
  f.app.updateAddButtonState();
  assert.equal(f.button.childNodes[0], spinner);
  assert.equal(f.button.disabled, true);
  assert.equal(f.sticky.disabled, true);
  assert.equal(f.sticky.childNodes[0].childNodes[1].textContent, 'Adding…');
  f.app.productCartRequestPending = false;
  f.app.updateAddButtonState();
  assert.equal(f.button.disabled, false);
  assert.equal(f.sticky.disabled, false);
  assert.equal(f.button.textContent, 'Add to Cart');
});

test('bulk add uses the same bounded request and keeps partial-stock feedback', async () => {
  const f = fixture();
  const request = f.app.addMultipleToCart(10, [{ variation_id: 20, qty: 2 }]);
  assert.equal(f.calls.fetch[0].url, '/cart/add-multiple');
  f.reply({ success: true, count: 2, items: [], failed_items: [{ attributes: { Color: 'Blue' }, message: 'Only one available' }] });
  await request;
  assert.equal(f.calls.drawer.length, 1);
  assert.match(f.calls.errors[0][0], /Blue:?.*Only one available/);
  assert.equal(f.timers.size, 0);
});

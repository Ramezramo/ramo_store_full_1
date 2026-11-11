const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const { runInNewContext } = require('node:vm');

// Keep requests pending so the transition can be checked before any server response.
function navigation() {
  class Element {
    constructor(tag = 'div') {
      this.tagName = tag;
      this.dataset = {};
      this.attributes = new Map();
      this.children = [];
      this.listeners = new Map();
      const classes = new Set();
      this.classList = {
        add: (...values) => values.forEach(value => classes.add(value)),
        remove: (...values) => values.forEach(value => classes.delete(value)),
        toggle: (value, active) => active ? classes.add(value) : classes.delete(value),
        contains: value => classes.has(value),
      };
    }
    appendChild(child) { child.parent = this; this.children.push(child); return child; }
    append(...children) { children.forEach(child => this.appendChild(child)); }
    remove() { if (this.parent) this.parent.children = this.parent.children.filter(child => child !== this); }
    setAttribute(name, value) { this.attributes.set(name, value); }
    removeAttribute(name) { this.attributes.delete(name); }
    hasAttribute(name) { return this.attributes.has(name); }
    getAttribute(name) { return this.attributes.get(name); }
    addEventListener(type, callback) {
      if (!this.listeners.has(type)) this.listeners.set(type, []);
      this.listeners.get(type).push(callback);
    }
    emit(type, event = {}) { this.listeners.get(type)?.forEach(callback => callback(event)); }
    dispatchEvent(event) { this.emit(event.type, event); }
  }
  const document = new Element('document');
  const window = new Element('window');
  window.top = window;
  const location = new URL('http://store.test/');
  location.assign = href => { throw new Error('Unexpected full navigation: ' + href); };
  const history = {
    pushState: (_, __, href) => { location.href = new URL(href, location).href; },
    replaceState: (_, __, href) => { location.href = new URL(href, location).href; },
  };
  const host = new Element();
  const home = host.appendChild(new Element());
  home.dataset.catalogView = '/';
  home.dataset.catalogActive = '1';
  home.scrollTop = 456;
  host.querySelector = () => home;
  document.body = new Element('body');
  document.documentElement = { lang: 'en' };
  document.title = 'Home';
  const links = ['/', '/shop', '/cart', '/account/notifications', '/account'].map(path => {
    const link = new Element('a');
    link.href = new URL(path, location).href;
    return link;
  });
  document.getElementById = id => id === 'storefront-content' ? host : null;
  document.querySelector = selector => selector.includes('catalog-session') ? { content: 'session-scope' } : null;
  document.querySelectorAll = selector => selector === '#mob-nav a[href]' ? links : [];
  document.createElement = tag => {
    const element = new Element(tag);
    if (tag === 'iframe') element.contentWindow = new Element('window');
    return element;
  };
  const timers = new Set();
  runInNewContext(readFileSync(resolve(__dirname, '../../public/js/catalog-navigation.js'), 'utf8'), {
    window, document, location, history, URL,
    setTimeout: callback => { timers.add(callback); return callback; },
    clearTimeout: callback => timers.delete(callback),
    CustomEvent: class { constructor(type, options) { this.type = type; this.detail = options.detail; } },
  });
  const click = (path, source = document) => {
    const link = new Element('a');
    link.href = new URL(path, location).href;
    source.emit('click', {
      button: 0, target: { closest: () => link },
      preventDefault() { this.defaultPrevented = true; },
    });
  };
  const frame = () => document.body.children.find(child => child.tagName === 'iframe');
  const loading = () => host.children.find(child => child.className === 'catalog-transient-loading');
  const ready = (element = frame(), path = new URL(element.src).pathname) => {
    const doc = new Element('document');
    doc.body = { childElementCount: 3 };
    doc.title = path;
    element.contentDocument = doc;
    element.contentWindow.location = new URL(path, location);
    window.emit('message', { origin: location.origin, source: element.contentWindow, data: { type: 'storefront:ready' } });
    return doc;
  };
  return { document, window, host, home, links, click, frame, loading, ready, timers, location };
}

test('account and notifications keep navigation available until their documents are ready', () => {
  const app = navigation();
  app.click('/account');
  const account = app.frame();
  assert.equal(account.dataset.ready, '0');
  assert.equal(account.inert, true);
  assert.equal(app.loading().children[0].textContent, 'My Account');
  assert.equal(app.host.inert, false);
  assert.equal(app.links[4].classList.contains('on'), true);

  const accountDoc = app.ready();
  assert.equal(account.dataset.ready, '1');
  assert.equal(app.loading(), undefined);
  assert.equal(app.timers.size, 0);
  // A later load event must not register duplicate navigation listeners.
  account.emit('load');
  assert.equal(accountDoc.listeners.get('click').length, 1);

  app.click('/account/notifications', accountDoc);
  assert.notEqual(app.frame(), account);
  assert.equal(app.frame().dataset.ready, '0');
  assert.equal(app.loading().children[0].textContent, 'Notifications');
  assert.equal(app.links[3].classList.contains('on'), true);
  assert.equal(app.links[4].classList.contains('on'), false);
  app.ready(account); // An old page finishing late cannot cover the new loading view.
  assert.equal(app.frame().dataset.ready, '0');
  app.ready();
  assert.equal(app.frame().dataset.ready, '1');
});

test('returning Home cancels a pending page and preserves its actual view and position', () => {
  const app = navigation();
  app.click('/account/notifications');
  const pending = app.frame();
  app.click('/');
  assert.equal(app.frame(), undefined);
  assert.equal(app.loading(), undefined);
  assert.equal(app.host.children[0], app.home);
  assert.equal(app.home.dataset.catalogActive, '1');
  assert.equal(app.home.scrollTop, 456);
  app.ready(pending);
  assert.equal(app.home.dataset.catalogActive, '1');
  assert.equal(app.location.pathname, '/');
});

test('in-page POST reloads hide the old frame until fresh markup is ready', () => {
  const app = navigation();
  app.click('/account/notifications');
  app.ready();
  const frame = app.frame();
  frame.contentWindow.emit('beforeunload');
  assert.equal(frame.dataset.ready, '0');
  assert.ok(app.loading());
  app.ready();
  assert.equal(frame.dataset.ready, '1');
  assert.equal(app.loading(), undefined);
});

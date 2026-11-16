const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const { runInNewContext } = require('node:vm');

// Keep requests pending so the transition can be checked before any server response.
function navigation(locale = 'en') {
  class Element {
    constructor(tag = 'div') {
      this.tagName = tag;
      this.dataset = {};
      this.attributes = new Map();
      this.children = [];
      this.listeners = new Map();
      this.style = { setProperty(name, value) { this[name] = value; } };
      const classes = new Set();
      this.classList = {
        add: (...values) => values.forEach(value => classes.add(value)),
        remove: (...values) => values.forEach(value => classes.delete(value)),
        toggle: (value, active) => active ? classes.add(value) : classes.delete(value),
        contains: value => classes.has(value),
      };
    }
    appendChild(child) { child.parent = this; this.children.push(child); return child; }
    get parentNode() { return this.parent; }
    replaceWith(node) { const index = this.parent.children.indexOf(this); this.parent.children[index] = node; node.parent = this.parent; this.parent = null; }
    append(...children) { children.forEach(child => this.appendChild(child)); }
    replaceChildren(...children) { this.children = []; this.append(...children); }
    cloneNode() { const clone = new Element(this.tagName); clone.src = this.src; clone.alt = this.alt; return clone; }
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
  document.documentElement = { lang: locale, dir: locale === 'ar' ? 'rtl' : 'ltr' };
  document.title = 'Home';
  const links = ['/', '/shop', '/cart', '/account'].map(path => {
    const link = new Element('a');
    link.href = new URL(path, location).href;
    return link;
  });
  const menuButton = new Element('button');
  menuButton.setAttribute('id', 'mobile-menu-trigger');
  menuButton.setAttribute('aria-expanded', 'false');
  menuButton.closest = () => null;
  const notificationLink = new Element('a');
  notificationLink.href = new URL('/account/notifications', location).href;
  notificationLink.classList.add('nav-notifications-top');
  notificationLink.setAttribute('data-notifications-link', '');
  document.getElementById = id => id === 'storefront-content' ? host : null;
  document.querySelector = selector => selector.includes('catalog-session') ? { content: 'session-scope' } : null;
  document.querySelectorAll = selector => selector === '#mob-nav a[href]' ? links
    : selector === '.nav-notifications-top' || selector === '[data-notifications-link]' ? [notificationLink] : [];
  document.createElement = tag => {
    const element = new Element(tag);
    if (tag === 'iframe') element.contentWindow = new Element('window');
    return element;
  };
  document.createComment = () => new Element('comment');
  const timers = new Set();
  runInNewContext(readFileSync(resolve(__dirname, '../../public/js/catalog-navigation.js'), 'utf8'), {
    window, document, location, history, URL,
    setTimeout: callback => { timers.add(callback); return callback; },
    clearTimeout: callback => timers.delete(callback),
    CustomEvent: class { constructor(type, options) { this.type = type; this.detail = options.detail; } },
    fetch: () => new Promise(() => {}),
  });
  const click = (path, source = document, card = null) => {
    const link = new Element('a');
    link.href = new URL(path, location).href;
    link.closest = () => card;
    const event = {
      button: 0, target: { closest: () => link },
      preventDefault() { this.defaultPrevented = true; },
    };
    source.emit('click', event);
    return event;
  };
  const frame = () => document.body.children.find(child => child.tagName === 'iframe');
  const loading = () => host.children.find(child => child.className === 'catalog-transient-loading');
  const ready = (element = frame(), path = new URL(element.src).pathname, pageLocale = locale, hero = null) => {
    const doc = new Element('document');
    doc.documentElement = { lang: pageLocale };
    doc.querySelector = selector => selector === '#main-img' ? hero : null;
    doc.body = { childElementCount: 3 };
    doc.title = path;
    element.contentDocument = doc;
    element.contentWindow.location = new URL(path, location);
    window.emit('message', { origin: location.origin, source: element.contentWindow, data: { type: 'storefront:ready' } });
    return doc;
  };
  const productCard = ({ name = 'Graphics card', image = 'http://store.test/card.jpg', selectedImage = '', loaded = false } = {}) => {
    const photo = new Element('img');
    photo.src = image;
    photo.currentSrc = selectedImage;
    photo.alt = name;
    photo.complete = loaded;
    photo.naturalWidth = loaded ? 800 : 0;
    photo.naturalHeight = loaded ? 600 : 0;
    const imageParent = new Element();
    imageParent.appendChild(photo);
    const title = new Element('a');
    title.textContent = name;
    title.setAttribute('title', name);
    return { photo, imageParent, querySelector: selector => selector === 'img' ? photo : title };
  };
  return { document, window, host, home, links, menuButton, notificationLink,
    click, frame, loading, ready, timers, location, productCard };
}

function find(element, predicate) {
  if (predicate(element)) return element;
  for (const child of element?.children || []) {
    const match = find(child, predicate);
    if (match) return match;
  }
}

function previewPhoto(app) {
  const media = find(app.loading(), node => node.className === 'gallery-main');
  return find(media, node => node.tagName === 'img');
}

test('language links bypass cached navigation from both the outer page and a child page', () => {
  const app = navigation();
  const languageUrl = '/language/ar?redirect=http%3A%2F%2Fstore.test%2Fcart';
  const event = app.click(languageUrl);
  assert.equal(event.defaultPrevented, undefined);
  assert.equal(app.frame(), undefined);
  assert.equal(app.loading(), undefined);

  app.click('/cart');
  const cartDoc = app.ready();
  const navigations = [];
  app.location.assign = href => navigations.push(href);
  const childEvent = app.click(languageUrl, cartDoc);
  assert.equal(childEvent.defaultPrevented, true);
  assert.deepEqual(navigations, ['http://store.test' + languageUrl]);
  assert.equal(app.loading(), undefined);
});

test('a child response in a different language rebuilds the outer page instead of mixing languages', () => {
  for (const [outer, child] of [['en', 'ar'], ['ar', 'en']]) {
    const app = navigation(outer);
    app.click('/cart');
    const frame = app.frame();
    const navigations = [];
    app.location.assign = href => navigations.push(href);
    app.ready(frame, '/cart', child);
    assert.deepEqual(navigations, ['http://store.test/cart']);
    assert.equal(frame.dataset.ready, '0');
    assert.ok(app.loading());
  }
});

test('Arabic stays selected through pending pages, product previews, reloads and cached catalog navigation', () => {
  const app = navigation('ar');
  app.click('/cart');
  assert.equal(app.loading().children[0].textContent, 'السلة');
  assert.equal(app.loading().children[2].textContent, 'جارٍ التحميل…');
  assert.equal(app.frame().title, 'صفحة المتجر');
  const cartDoc = app.ready();
  app.frame().contentWindow.emit('beforeunload');
  assert.equal(app.loading().children[0].textContent, 'السلة');
  app.ready();
  app.click('/product/42', cartDoc);
  assert.ok(find(app.loading(), node => node.textContent === 'جارٍ تحميل تفاصيل المنتج…'));
  assert.ok(find(app.loading(), node => node.textContent === 'مسار التصفح' || node.getAttribute('aria-label') === 'مسار التصفح'));
  app.click('/');
  assert.equal(app.home.dataset.catalogActive, '1');
  app.click('/shop');
  assert.ok(find(app.host, node => node.tagName === 'h1' && node.textContent === 'المتجر'));
  assert.equal(app.document.documentElement.lang, 'ar');
  assert.equal(app.document.documentElement.dir, 'rtl');
});

test('both language switch controls use a top-level navigation even without JavaScript', () => {
  const layout = readFileSync(resolve(__dirname, '../../resources/views/layouts/app.blade.php'), 'utf8')
    + readFileSync(resolve(__dirname, '../../resources/views/web/partials/storefront-menu.blade.php'), 'utf8');
  for (const className of ['nav-language-btn', 'nav-mobile-language-link']) {
    assert.match(layout, new RegExp('class="' + className + '"\\s+target="_top"'));
  }
});

test('account and notifications keep navigation available until their documents are ready', () => {
  const app = navigation();
  app.click('/account');
  const account = app.frame();
  assert.equal(account.dataset.ready, '0');
  assert.equal(account.inert, true);
  assert.equal(app.loading().children[0].textContent, 'My Account');
  assert.equal(app.host.inert, false);
  assert.equal(app.links[3].classList.contains('on'), true);

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
  assert.equal(app.links[3].getAttribute('aria-current'), 'page');
  assert.equal(app.notificationLink.getAttribute('aria-current'), 'page');
  app.ready(account); // An old page finishing late cannot cover the new loading view.
  assert.equal(app.frame().dataset.ready, '0');
  app.ready();
  assert.equal(app.frame().dataset.ready, '1');
});

test('bottom Menu buttons bypass page navigation and loading shells', () => {
  const app = navigation();
  const event = { button: 0, target: app.menuButton,
    preventDefault() { this.defaultPrevented = true; } };
  app.document.emit('click', event);
  assert.equal(event.defaultPrevented, undefined);
  assert.equal(app.frame(), undefined);
  assert.equal(app.loading(), undefined);
  assert.equal(app.location.pathname, '/');
});

test('the top notification bell current-page state clears when leaving notifications', () => {
  const app = navigation();
  app.click('/account/notifications');
  assert.equal(app.notificationLink.getAttribute('aria-current'), 'page');
  assert.equal(app.links[3].classList.contains('on'), true);
  app.ready();
  app.click('/');
  assert.equal(app.notificationLink.getAttribute('aria-current'), undefined);
  assert.equal(app.links[3].classList.contains('on'), false);
  assert.equal(app.links[0].classList.contains('on'), true);
  assert.equal(app.menuButton.getAttribute('aria-expanded'), 'false');
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

test('product details open immediately with the clicked card name and current variation thumbnail', () => {
  const app = navigation();
  const card = app.productCard({ name: 'Classic White Oxford Shirt', selectedImage: 'http://store.test/selected-white.jpg' });
  app.click('/product/42', app.document, card);
  assert.equal(app.frame().dataset.ready, '0');
  assert.equal(app.frame().src, 'http://store.test/product/42');
  const heading = find(app.loading(), node => node.tagName === 'h1');
  const image = previewPhoto(app);
  assert.equal(heading.textContent, 'Classic White Oxford Shirt');
  assert.equal(image.src, 'http://store.test/selected-white.jpg');
  assert.equal(image.loading, 'eager');
  assert.ok(find(app.loading(), node => node.textContent === 'Loading product details…'));
  assert.equal(app.home.scrollTop, 456);
  app.ready();
  assert.equal(app.frame().dataset.ready, '1');
  assert.equal(app.loading(), undefined);
});

test('product text stays text and invalid or broken thumbnail URLs use the placeholder', () => {
  const app = navigation();
  app.click('/product/42', app.document, app.productCard({ name: '<img onerror=alert(1)>', image: 'javascript:alert(1)' }));
  assert.equal(find(app.loading(), node => node.tagName === 'h1').textContent, '<img onerror=alert(1)>');
  assert.equal(previewPhoto(app).src, 'http://store.test/images/product-placeholder.svg');
  app.click('/product/43', app.document, app.productCard({ image: 'http://store.test/broken.jpg' }));
  const image = previewPhoto(app);
  image.emit('error');
  assert.equal(image.src, 'http://store.test/images/product-placeholder.svg');
});

test('a product link from the cart frame gets its own preview and late pages cannot replace it', () => {
  const app = navigation();
  app.click('/cart');
  const cartFrame = app.frame();
  const cart = app.ready();
  app.click('/product/42', cart, app.productCard({ name: 'From the cart' }));
  const firstProduct = app.frame();
  assert.equal(find(app.loading(), node => node.tagName === 'h1').textContent, 'From the cart');
  app.click('/product/43', app.document, app.productCard({ name: 'A different product' }));
  app.ready(firstProduct);
  app.ready(cartFrame);
  assert.equal(find(app.loading(), node => node.tagName === 'h1').textContent, 'A different product');
  app.ready();
  assert.equal(app.location.pathname, '/product/43');
});

test('Back to a product reuses only its thumbnail metadata and fetches fresh details', () => {
  const app = navigation();
  app.click('/product/42', app.document, app.productCard());
  const originalFrame = app.frame();
  app.click('/');
  assert.equal(app.loading(), undefined);
  assert.equal(app.home.scrollTop, 456);
  app.location.href = 'http://store.test/product/42';
  app.window.emit('popstate');
  assert.notEqual(app.frame(), originalFrame);
  assert.equal(previewPhoto(app).src, 'http://store.test/card.jpg');
});

test('a product without a card image still has a product-shaped loading state', () => {
  const app = navigation();
  app.click('/product/42');
  assert.equal(find(app.loading(), node => node.tagName === 'h1').textContent, 'Product');
  assert.equal(previewPhoto(app).src, 'http://store.test/images/product-placeholder.svg');
});

test('a loaded thumbnail is reused without another image request and restored to the cached card', () => {
  const app = navigation();
  const card = app.productCard({ loaded: true });
  app.click('/product/42', app.document, card);
  assert.equal(previewPhoto(app), card.photo);
  assert.equal(card.imageParent.children[0].tagName, 'comment');
  app.ready();
  assert.equal(card.imageParent.children[0], card.photo);
  app.click('/product/42', app.document, card);
  app.click('/');
  assert.equal(card.imageParent.children[0], card.photo);
  assert.equal(app.home.scrollTop, 456);
});

test('product preview uses the live gallery structure and carries photo proportions through history', () => {
  const app = navigation();
  const card = app.productCard({ loaded: true });
  app.click('/product/42', app.document, card);
  const gallery = find(app.loading(), node => node.className === 'gallery-wrap');
  assert.equal(gallery.style['--gallery-ratio'], '800 / 600');
  assert.ok(find(gallery, node => node.className === 'gallery-main'));
  assert.ok(find(gallery, node => node.className === 'gallery-thumbs'));
  assert.ok(find(gallery, node => node.className === 'gallery-controls'));
  app.ready();
  app.click('/product/42');
  assert.equal(find(app.loading(), node => node.className === 'gallery-wrap').style['--gallery-ratio'], '800 / 600');
});

test('the preview remains visible until the destination hero is loaded and decoded', async () => {
  const app = navigation();
  app.click('/product/42');
  const frame = app.frame();
  const hero = app.document.createElement('img');
  hero.src = 'http://store.test/product.jpg';
  hero.complete = false;
  let finishDecode;
  hero.decode = () => new Promise(resolve => { finishDecode = resolve; });
  app.ready(frame, '/product/42', 'en', hero);
  assert.equal(frame.dataset.ready, '0');
  assert.ok(app.loading());
  hero.emit('load');
  assert.equal(frame.dataset.ready, '0');
  finishDecode();
  await new Promise(setImmediate);
  assert.equal(frame.dataset.ready, '1');
  assert.equal(app.loading(), undefined);
});

test('late hero decoding cannot reveal a product after the user has gone back', async () => {
  const app = navigation();
  app.click('/product/42');
  const frame = app.frame();
  const hero = app.document.createElement('img');
  hero.src = 'http://store.test/product.jpg';
  hero.complete = true;
  let finishDecode;
  hero.decode = () => new Promise(resolve => { finishDecode = resolve; });
  app.ready(frame, '/product/42', 'en', hero);
  app.click('/');
  finishDecode();
  await new Promise(setImmediate);
  assert.equal(app.frame(), undefined);
  assert.equal(app.home.dataset.catalogActive, '1');
});

test('a broken destination hero reveals its fallback without getting stuck loading', async () => {
  const app = navigation();
  app.click('/product/42');
  const frame = app.frame();
  const hero = app.document.createElement('img');
  hero.src = 'http://store.test/broken.jpg';
  hero.complete = false;
  hero.decode = () => Promise.reject(new Error('Broken image'));
  app.ready(frame, '/product/42', 'en', hero);
  hero.emit('error');
  await new Promise(setImmediate);
  assert.equal(frame.dataset.ready, '1');
  assert.equal(app.loading(), undefined);
});

/**
 * Ramo Store — client-side page cache for the Home ("/") and Shop ("/shop")
 * pages, stored entirely on the visitor's device via the Cache Storage API
 * (never on the backend). Repeat visits to these two pages are served
 * instantly from cache while a background request silently refreshes the
 * cache for next time (stale-while-revalidate).
 *
 * Safety: authenticated visitors are always routed to the network — never
 * cached, never served from cache — so no personalized/account content can
 * ever leak into (or be read back from) the shared on-device cache. See
 * app/Http/Middleware/SetAuthFlagCookie.php for how the "ramo_auth_flag"
 * cookie this relies on is kept accurate.
 */

// Bumped to v2: forces old caches to be purged on activate. The v1 cache could
// contain personalized/authenticated HTML that was wrongly cached due to the
// Cookie-header bug described below, so it must not be reused.
const CACHE_NAME = 'ramo-page-cache-v3';
const CACHEABLE_PATHS = ['/', '/shop'];

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// NOTE: FetchEvent.request.headers never exposes the "Cookie" header — browsers
// strip it before handing the request to the service worker, for privacy reasons.
// So we can't read `ramo_auth_flag` off `request` at all; we must use the
// Cookie Store API (available in the SW global scope in Chromium browsers) to
// read the cookie directly. Where that API isn't available, fail safe by
// treating the visitor as authenticated (i.e. always hit the network) rather
// than risk serving a stale/incorrect cached page.
async function isAuthenticated() {
  if (!('cookieStore' in self)) return true;
  try {
    const cookie = await self.cookieStore.get('ramo_auth_flag');
    return !cookie || cookie.value !== '0';
  } catch (e) {
    return true;
  }
}

function isCacheableRequest(request, url) {
  if (request.method !== 'GET') return false;
  if (url.origin !== self.location.origin) return false;
  if (!CACHEABLE_PATHS.includes(url.pathname)) return false;
  const accept = request.headers.get('accept') || '';
  if (request.mode !== 'navigate' && !accept.includes('text/html')) return false;
  return true;
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(CACHE_NAME);
  const cached = await cache.match(request, { ignoreVary: true });

  const networkFetch = fetch(request)
    .then((response) => {
      if (response && response.ok) cache.put(request, response.clone());
      return response;
    })
    .catch(() => null);

  if (cached) {
    // Serve instantly from the device cache; refresh it silently in the background.
    return cached;
  }

  const fresh = await networkFetch;
  return fresh || Response.error();
}

self.addEventListener('fetch', (event) => {
  const request = event.request;
  let url;
  try {
    url = new URL(request.url);
  } catch (e) {
    return;
  }

  if (!isCacheableRequest(request, url)) return;

  event.respondWith(
    isAuthenticated().then((authed) => {
      if (authed) return fetch(request); // always hit the network for logged-in visitors, never touch the cache
      return staleWhileRevalidate(request);
    })
  );
});

self.addEventListener('message', (event) => {
  if (event.data === 'clear') {
    event.waitUntil(caches.delete(CACHE_NAME));
  }
});

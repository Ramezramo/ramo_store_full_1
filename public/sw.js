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

const CACHE_NAME = 'ramo-page-cache-v1';
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

function isAuthenticated(request) {
  const cookie = request.headers.get('cookie') || '';
  return /(?:^|;\s*)ramo_auth_flag=1(?:;|$)/.test(cookie);
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
  if (isAuthenticated(request)) return; // always hit the network for logged-in visitors

  event.respondWith(staleWhileRevalidate(request));
});

self.addEventListener('message', (event) => {
  if (event.data === 'clear') {
    event.waitUntil(caches.delete(CACHE_NAME));
  }
});

const CACHE_NAME = 'dlatif-pos-v1';
const PRECACHE = ['/'];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (e) => {
  // Network-first: always try network, fallback to cache for assets
  if (e.request.method !== 'GET') return;

  const url = new URL(e.request.url);

  // Cache static assets (JS, CSS, fonts, images)
  if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
    e.respondWith(
      caches.open(CACHE_NAME).then((cache) =>
        cache.match(e.request).then((cached) => {
          const fetched = fetch(e.request).then((response) => {
            if (response.ok) cache.put(e.request, response.clone());
            return response;
          });
          return cached || fetched;
        })
      )
    );
    return;
  }

  // All other requests: network only (no offline)
  e.respondWith(fetch(e.request));
});

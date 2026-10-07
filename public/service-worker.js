const CACHE_NAME = 'rd-expense-v11';
const OFFLINE_URL = '/offline';
const STATIC_ASSETS = [
  OFFLINE_URL,
  '/manifest.json',
  '/css/glass.css',
  '/js/theme.js',
  '/css/shell.css',
  '/js/shell.js',
  '/css/motion.css',
  '/js/login.js',
  '/js/sfx.js',
  '/js/expense-form.js',
  '/icons/icon.svg',
  'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css',
  'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => Promise.allSettled(
      STATIC_ASSETS.map(asset => fetch(asset).then(response => cache.put(asset, response)))
    ))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);
  const isStatic = requestUrl.pathname.startsWith('/css/')
    || requestUrl.pathname.startsWith('/js/')
    || requestUrl.pathname.startsWith('/icons/')
    || requestUrl.pathname === '/manifest.json'
    || requestUrl.hostname === 'cdn.jsdelivr.net';

  if (isStatic) {
    event.respondWith(
      caches.match(event.request).then(cached => cached || fetch(event.request).then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
        return response;
      }))
    );
    return;
  }

  event.respondWith(
    fetch(event.request)
      .catch(() => caches.match(OFFLINE_URL))
  );
});

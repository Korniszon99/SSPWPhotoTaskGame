// Cache only explicitly enumerated static assets. Never cache event photos or responses with user data.
const CACHE_NAME = 'photo-challenge-v3';
const STATIC_CACHE = [
    'style.css',
    'js/ratings.js',
    'graphics/logo-fut.png',
    'lang/en.arb',
    'lang/pl.arb',
    'graphics/en.svg',
    'graphics/pl.svg',
];
const STATIC_PATHS = new Set(STATIC_CACHE.map(path => new URL(path, self.registration.scope).pathname));

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_CACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin || !STATIC_PATHS.has(url.pathname)) return;

    event.respondWith(
        caches.match(request).then(cached => cached || fetch(request).then(response => {
            if (response.ok && response.type === 'basic') {
                const copy = response.clone();
                event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.put(request, copy)));
            }
            return response;
        }))
    );
});

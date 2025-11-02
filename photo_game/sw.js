const CACHE_NAME = 'photo-challenge-v2';
const STATIC_CACHE = [
    'style.css',
    'js/ratings.js',
    'graphics/logo-fut.png',
    'lang/en.arb',
    'lang/pl.arb',
    'graphics/en.svg',
    'graphics/pl.svg',
];

// Instalacja
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_CACHE))
            .then(() => self.skipWaiting())
    );
});

// Aktywacja
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            ))
            .then(() => self.clients.claim())
    );
});

// Fetch - różne strategie dla różnych typów plików
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Ignoruj wszystkie nie-HTTP(S) żądania (np. chrome-extension://)
    if (!url.protocol.startsWith('http')) return;

    // Strategia dla plików PHP - NETWORK FIRST
    if (url.pathname.endsWith('.php') || url.pathname === '/' || event.request.method !== 'GET') {
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Strategia dla plików statycznych (CSS, JS, obrazy) - CACHE FIRST
    if (url.pathname.match(/\.(css|js|jpg|jpeg|png|gif|svg|woff|woff2|arb)$/)) {
        event.respondWith(
            caches.match(event.request)
                .then(cached => cached || fetch(event.request)
                    .then(response => {
                        if (response.status === 200) {
                            const clone = response.clone();
                            caches.open(CACHE_NAME)
                                .then(cache => cache.put(event.request, clone));
                        }
                        return response;
                    })
                )
        );
        return;
    }

    // Domyślnie - próbuj sieć, potem cache
    event.respondWith(
        fetch(event.request)
            .catch(() => caches.match(event.request))
    );
});
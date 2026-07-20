const CACHE_NAME = 'failerry-v1';
const OFFLINE_URL = '/offline.html';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/favicon.ico',
    '/favicon.jpg',
    OFFLINE_URL
];
    
self.addEventListener('install', event => {
    self.skipWaiting(); // Force new SW to install immediately
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim(); // Take control immediately
});

self.addEventListener('fetch', event => {
    // Skip non-GET requests (like POST uploads)
    if (event.request.method !== 'GET') {
        return;
    }

    // Only handle navigation requests (HTML pages) for the offline fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                // If network fails, serve the offline page
                return caches.open(CACHE_NAME).then(cache => {
                    return cache.match(OFFLINE_URL);
                });
            })
        );
    } else {
        // For other assets, try cache first, then network
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    return response || fetch(event.request);
                })
        );
    }
});

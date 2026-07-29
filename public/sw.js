const CACHE_NAME = 'failerry-v2';
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

    // Skip non-HTTP(S) scheme requests (e.g. chrome-extension://)
    if (!event.request.url.startsWith('http://') && !event.request.url.startsWith('https://')) {
        return;
    }

    // Handle navigation requests (HTML page transitions)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.open(CACHE_NAME).then(cache => {
                    return cache.match(OFFLINE_URL);
                });
            })
        );
        return;
    }

    // For static assets, try cache first, then fetch network safely with catch fallback
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                if (response) {
                    return response;
                }
                return fetch(event.request).catch(err => {
                    // Prevent uncaught promise rejections on network errors or cancelled requests
                    return new Response('', { status: 408, statusText: 'Network Error' });
                });
            })
    );
});


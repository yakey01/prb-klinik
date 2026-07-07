/**
 * Service Worker — Klinik Dokterku PRB
 * Strategy: Network-first for HTML, Cache-first for assets
 */

const CACHE_NAME = 'prb-klinik-v2';
const STATIC_CACHE = 'prb-static-v2';

// Assets to pre-cache for offline support
const PRECACHE_ASSETS = [
    '/dashboard',
    '/offline',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// Routes that work offline (show cached version)
const CACHEABLE_ROUTES = [
    '/dashboard',
    '/pasien',
    '/stok',
    '/katalog',
    '/laporan',
    '/stok-keluar',
    '/distributor',
];

// ── Install: pre-cache critical assets ──────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch(() => {
                // Silently ignore if offline during install
            });
        }).then(() => self.skipWaiting())
    );
});

// ── Activate: clean old caches ───────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME && name !== STATIC_CACHE)
                    .map(name => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// ── Fetch: Network-first strategy ────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET, cross-origin, Livewire polling, auth endpoints
    if (request.method !== 'GET') return;
    if (url.origin !== location.origin) return;
    if (url.pathname.startsWith('/livewire')) return;
    if (url.pathname.startsWith('/login') || url.pathname.startsWith('/logout')) return;
    if (url.searchParams.has('_token')) return;

    // Static assets: cache-first (build/, fonts, icons)
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(STATIC_CACHE).then(cache => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    // HTML pages: network-first, fall back to cache, then offline page
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then((cached) => {
                        if (cached) return cached;
                        // Show offline fallback for known routes
                        const isCacheable = CACHEABLE_ROUTES.some(r => url.pathname.startsWith(r));
                        if (isCacheable) {
                            return caches.match('/dashboard') || offlineResponse();
                        }
                        return offlineResponse();
                    });
                })
        );
        return;
    }
});

function offlineResponse() {
    return new Response(
        `<!DOCTYPE html>
        <html lang="id">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Offline — Klinik Dokterku</title>
        <style>
            body { background:#0a1410; color:#eaf3ee; font-family:'Plus Jakarta Sans',system-ui,sans-serif;
                   display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
            .box { text-align:center; padding:2rem; }
            .icon { font-size:3rem; margin-bottom:1rem; }
            h1 { font-size:1.2rem; color:#f2c668; margin:0 0 .5rem; }
            p { color:#8fae9f; font-size:.85rem; margin:0 0 1.5rem; }
            button { background:linear-gradient(135deg,#d9a441,#c4892e); color:#1a0e00;
                     border:none; padding:.75rem 1.5rem; border-radius:.5rem; font-weight:700;
                     font-size:.9rem; cursor:pointer; }
        </style>
        </head>
        <body>
        <div class="box">
            <div class="icon">📡</div>
            <h1>Tidak Ada Koneksi</h1>
            <p>Koneksi internet terputus.<br>Periksa jaringan dan coba lagi.</p>
            <button onclick="location.reload()">Coba Lagi</button>
        </div>
        </body>
        </html>`,
        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
}

// ── Push Notifications ────────────────────────────────────────────
self.addEventListener('push', (event) => {
    if (!event.data) return;
    const data = event.data.json().catch(() => ({
        title: 'Klinik Dokterku',
        body: event.data.text(),
    }));

    event.waitUntil(
        data.then(({ title, body, url = '/dashboard', icon = '/icons/icon-192.png' }) => {
            return self.registration.showNotification(title, {
                body,
                icon,
                badge: '/icons/icon-96.png',
                tag: 'prb-notification',
                renotify: true,
                data: { url },
                actions: [
                    { action: 'open', title: 'Buka Aplikasi' },
                    { action: 'dismiss', title: 'Tutup' },
                ],
            });
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    if (event.action === 'dismiss') return;

    const url = event.notification.data?.url || '/dashboard';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});

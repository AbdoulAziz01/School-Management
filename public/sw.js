/**
 * EduManager — Service Worker v6
 * Cache-First  : assets statiques + pédagogiques
 * Network-First: pages HTML (avec double-cache redirect + ignoreVary)
 * Offline élève: /offline-student.html (lit IndexedDB)
 * Offline admin: /offline.html
 */

'use strict';

const SW_VERSION     = 'v6';
const STATIC_CACHE   = `edumanager-static-${SW_VERSION}`;
const DYNAMIC_CACHE  = `edumanager-dynamic-${SW_VERSION}`;
const OFFLINE_URL         = '/offline.html';
const OFFLINE_STUDENT_URL = '/offline-student.html';

// ── Pré-cache à l'installation ───────────────────────────────────────────────
const PRECACHE_URLS = [
    OFFLINE_URL,
    OFFLINE_STUDENT_URL,
    '/login',
    '/',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-regular-400.woff2',
];

// ── Cache-First : assets immuables ───────────────────────────────────────────
const CACHE_FIRST_PATTERNS = [
    /\/build\/assets\/.+\.(js|css)$/,
    /cdn\.jsdelivr\.net/,
    /cdnjs\.cloudflare\.com/,
    /fonts\.googleapis\.com/,
    /fonts\.gstatic\.com/,
    /\.(png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|eot)(\?.*)?$/i,
    /\/storage\/lms\//,
    /\/storage\/lessons\//,
    /\.pdf$/i,
    /\/storage\/audio\//,
    /\.(mp3|ogg|wav)$/i,
];

// ── Network-First : pages dynamiques ─────────────────────────────────────────
const NETWORK_FIRST_PATTERNS = [
    /\/api\//,
    /\/student\//,
    /\/teacher\//,
    /\/admin\//,
];

// ── Installation ──────────────────────────────────────────────────────────────
self.addEventListener('install', function (event) {
    event.waitUntil(
        Promise.allSettled(
            PRECACHE_URLS.map(function (url) {
                return caches.open(STATIC_CACHE).then(function (cache) {
                    return cache.add(new Request(url, { credentials: 'omit' }));
                }).catch(function (err) {
                    console.warn('[SW] Précache ignoré :', url, err.message);
                });
            })
        ).then(function () { return self.skipWaiting(); })
    );
});

// ── Activation : purge des anciens caches ─────────────────────────────────────
self.addEventListener('activate', function (event) {
    const valid = [STATIC_CACHE, DYNAMIC_CACHE];
    event.waitUntil(
        caches.keys()
            .then(function (keys) {
                return Promise.all(
                    keys.filter(function (k) { return !valid.includes(k); })
                        .map(function (k)   { return caches.delete(k); })
                );
            })
            .then(function () { return self.clients.claim(); })
    );
});

// ── Interception des requêtes ─────────────────────────────────────────────────
self.addEventListener('fetch', function (event) {
    const request = event.request;
    const url     = request.url;

    if (request.method !== 'GET') return;
    if (!url.startsWith('http'))  return;

    if (matchesPatterns(url, CACHE_FIRST_PATTERNS)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    if (request.mode === 'navigate' || matchesPatterns(url, NETWORK_FIRST_PATTERNS)) {
        event.respondWith(networkFirstWithOfflineFallback(request));
        return;
    }

    event.respondWith(staleWhileRevalidate(request, DYNAMIC_CACHE));
});

// ════════════════════════════════════════════════════════════════════════════════
// Stratégies
// ════════════════════════════════════════════════════════════════════════════════

async function cacheFirst(request, cacheName) {
    const cached = await matchCache(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if ((response.ok || response.type === 'opaque') && response.status !== 206) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (_) {
        if (request.destination === 'image') return placeholderImage();
        return new Response('', { status: 503, statusText: 'Offline' });
    }
}

/**
 * Network-First avec :
 * - Double-cache (URL originale + URL finale après redirect)
 * - Fallback élève : /offline-student.html (lit IndexedDB)
 * - Fallback générique : /offline.html
 */
async function networkFirstWithOfflineFallback(request) {
    try {
        const response = await fetch(request);

        if (response.ok) {
            const cache  = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());

            // Double-cache après redirect (/admin → /admin/dashboard)
            if (response.redirected && response.url && response.url !== request.url) {
                cache.put(new Request(response.url), response.clone());
            }
        }

        return response;
    } catch (_) {
        // 1. Page exacte dans le cache ?
        const cached = await matchCache(request);
        if (cached) return cached;

        // 2. Page offline adaptée au profil de l'URL
        const urlPath = new URL(request.url).pathname;

        if (urlPath.startsWith('/student/')) {
            const studentOffline = await matchCache(new Request(OFFLINE_STUDENT_URL));
            if (studentOffline) return studentOffline;
        }

        // 3. Fallback générique
        const genericOffline = await matchCache(new Request(OFFLINE_URL));
        return genericOffline || new Response(
            '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">'
            + '<meta name="viewport" content="width=device-width,initial-scale=1">'
            + '<title>Hors ligne</title>'
            + '<style>body{min-height:100vh;display:flex;align-items:center;justify-content:center;'
            + 'background:#1c1917;font-family:sans-serif;color:#fef3c7;padding:20px}'
            + 'div{text-align:center}h1{margin-bottom:10px}p{color:#a8a29e;margin-bottom:20px}'
            + 'button{padding:10px 24px;background:#f59e0b;border:none;border-radius:8px;'
            + 'font-weight:700;cursor:pointer}</style></head>'
            + '<body><div><h1>Hors ligne</h1>'
            + '<p>V&eacute;rifiez votre connexion.</p>'
            + '<button onclick="location.reload()">R&eacute;essayer</button></div></body></html>',
            { headers: { 'Content-Type': 'text/html; charset=utf-8' }, status: 503 }
        );
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache        = await caches.open(cacheName);
    const cached       = await matchCache(request);
    const fetchPromise = fetch(request).then(function (response) {
        if (response.ok || response.type === 'opaque') {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(function () { return null; });

    return cached || fetchPromise;
}

// ════════════════════════════════════════════════════════════════════════════════
// Utilitaires
// ════════════════════════════════════════════════════════════════════════════════

// ignoreVary=true : évite les faux-négatifs dus à Vary: Cookie / Accept-Encoding
async function matchCache(request) {
    return caches.match(request, { ignoreVary: true });
}

function matchesPatterns(url, patterns) {
    return patterns.some(function (p) { return p.test(url); });
}

function placeholderImage() {
    return new Response('<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"/>', {
        headers: { 'Content-Type': 'image/svg+xml', 'Cache-Control': 'no-store' },
    });
}

// ── Background Sync ───────────────────────────────────────────────────────────
self.addEventListener('sync', function (event) {
    if (event.tag === 'sync-attendance') {
        event.waitUntil(
            self.clients.matchAll().then(function (clients) {
                clients.forEach(function (c) {
                    c.postMessage({ type: 'SYNC_COMPLETE', tag: 'sync-attendance' });
                });
            })
        );
    }
});

/**
 * AzelieEdu — Service Worker v6
 * Stratégie principale : Cache-First pour les assets pédagogiques et statiques.
 * Stratégie secondaire : Network-First pour les pages HTML (données fraîches).
 * Fallback offline pour les navigations impossibles.
 *
 * v6 : purge du cache /login précédent (refonte visuelle de la page de connexion)
 * v5 : fix ignoreVary + double-cache sur redirect + pré-cache login
 */

'use strict';

const SW_VERSION     = 'v6';
const STATIC_CACHE   = `edumanager-static-${SW_VERSION}`;
const DYNAMIC_CACHE  = `edumanager-dynamic-${SW_VERSION}`;
const OFFLINE_URL    = '/offline.html';

// ── Assets pré-chargés à l'installation ──────────────────────────────────────
const PRECACHE_URLS = [
    OFFLINE_URL,
    '/login',
    '/',
    // CDN Bootstrap + FontAwesome (UI)
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-regular-400.woff2',
];

// ── Patterns → Cache-First (ne changent pas ou rarement) ─────────────────────
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

// ── Patterns → Network-First (données fraîches souhaitées) ───────────────────
const NETWORK_FIRST_PATTERNS = [
    /\/api\//,
    /\/student\//,
    /\/teacher\//,
    /\/admin\//,
];

// ── Installation : pré-cache des assets critiques ────────────────────────────
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

// ── Activation : purge des anciens caches ────────────────────────────────────
self.addEventListener('activate', function (event) {
    const validCaches = [STATIC_CACHE, DYNAMIC_CACHE];

    event.waitUntil(
        caches.keys()
            .then(function (keys) {
                return Promise.all(
                    keys
                        .filter(function (k) { return !validCaches.includes(k); })
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

    // ── 1. Cache-First pour les assets statiques et pédagogiques ─────────────
    if (matchesPatterns(url, CACHE_FIRST_PATTERNS)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // ── 2. Network-First pour les pages applicatives (données fraîches) ───────
    if (request.mode === 'navigate' || matchesPatterns(url, NETWORK_FIRST_PATTERNS)) {
        event.respondWith(networkFirstWithOfflineFallback(request));
        return;
    }

    // ── 3. Stale-While-Revalidate pour tout le reste ──────────────────────────
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

        if (response.ok || response.type === 'opaque') {
            const cache = await caches.open(cacheName);
            if (response.status !== 206) {
                cache.put(request, response.clone());
            }
        }

        return response;
    } catch (_) {
        if (request.destination === 'image') {
            return placeholderImage();
        }
        return new Response('', { status: 503, statusText: 'Offline' });
    }
}

/**
 * Network-First avec fallback cache puis page offline.
 * Double-cache : stocke sous l'URL d'origine ET sous l'URL finale après redirect.
 */
async function networkFirstWithOfflineFallback(request) {
    try {
        const response = await fetch(request);

        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);

            // Toujours cacher sous l'URL de la requête originale
            cache.put(request, response.clone());

            // Si la réponse a suivi un redirect, cacher aussi sous l'URL finale
            // (ex: /admin → /admin/dashboard : on cache les deux clés)
            if (response.redirected && response.url && response.url !== request.url) {
                cache.put(new Request(response.url), response.clone());
            }
        }

        return response;
    } catch (_) {
        // Réseau indisponible → chercher dans le cache (ignoreVary pour fiabilité)
        const cached = await matchCache(request);
        if (cached) return cached;

        // Dernière chance : page offline générique
        const offlinePage = await matchCache(new Request(OFFLINE_URL));
        return offlinePage || new Response(
            '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hors ligne</title><style>body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#1c1917;font-family:sans-serif;color:#fef3c7;padding:20px}div{text-align:center}h1{margin-bottom:10px}p{color:#a8a29e}</style></head><body><div><h1>Hors ligne</h1><p>V&eacute;rifiez votre connexion.</p><button onclick="location.reload()" style="margin-top:20px;padding:10px 24px;background:#f59e0b;border:none;border-radius:8px;font-weight:700;cursor:pointer">R&eacute;essayer</button></div></body></html>',
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

/**
 * Recherche dans tous les caches avec ignoreVary=true pour éviter les faux négatifs
 * dus aux headers Vary: Cookie / Vary: Accept-Encoding envoyés par Laravel.
 */
async function matchCache(request) {
    return caches.match(request, { ignoreVary: true });
}

function matchesPatterns(url, patterns) {
    return patterns.some(function (pattern) { return pattern.test(url); });
}

function placeholderImage() {
    const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"/>';
    return new Response(svg, {
        headers: { 'Content-Type': 'image/svg+xml', 'Cache-Control': 'no-store' },
    });
}

// ── Background Sync : retry des formulaires soumis hors ligne ─────────────────
self.addEventListener('sync', function (event) {
    if (event.tag === 'sync-attendance') {
        event.waitUntil(syncPendingAttendances());
    }
});

async function syncPendingAttendances() {
    const clients = await self.clients.matchAll();
    clients.forEach(function (client) {
        client.postMessage({ type: 'SYNC_COMPLETE', tag: 'sync-attendance' });
    });
}

/**
 * EduManager — Service Worker v4
 * Stratégie principale : Cache-First pour les assets pédagogiques et statiques.
 * Stratégie secondaire : Network-First pour les pages HTML (données fraîches).
 * Fallback offline pour les navigations impossibles.
 */

'use strict';

const SW_VERSION     = 'v4';
const STATIC_CACHE   = `edumanager-static-${SW_VERSION}`;   // Assets immuables
const DYNAMIC_CACHE  = `edumanager-dynamic-${SW_VERSION}`;  // Pages et ressources dynamiques
const OFFLINE_URL    = '/offline.html';

// ── Assets pré-chargés à l'installation ──────────────────────────────────────
const PRECACHE_URLS = [
    OFFLINE_URL,
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
    // Assets compilés (Vite hash dans le nom)
    /\/build\/assets\/.+\.(js|css)$/,
    // CDN externes
    /cdn\.jsdelivr\.net/,
    /cdnjs\.cloudflare\.com/,
    /fonts\.googleapis\.com/,
    /fonts\.gstatic\.com/,
    // Images et polices locales
    /\.(png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|eot)(\?.*)?$/i,
    // Ressources pédagogiques (PDFs cours, stockés dans /storage/)
    /\/storage\/lms\//,
    /\/storage\/lessons\//,
    /\.pdf$/i,
    // Audio (messages vocaux parents)
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
        caches.open(STATIC_CACHE)
            .then(function (cache) {
                return cache.addAll(
                    PRECACHE_URLS.map(function (url) {
                        return new Request(url, { credentials: 'omit' });
                    })
                );
            })
            .then(function () { return self.skipWaiting(); })
            .catch(function (err) {
                // Ne pas bloquer l'install si un CDN est inaccessible
                console.warn('[SW] Précache partiel :', err.message);
                return self.skipWaiting();
            })
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

    // Ignorer : non-GET, extensions navigateur, chrome-extension, etc.
    if (request.method !== 'GET') return;
    if (!url.startsWith('http'))  return;

    const urlObj = new URL(url);

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

/**
 * Cache-First : sert depuis le cache, met en cache si absent.
 * Idéal pour : CSS/JS hashés, fonts, images, PDFs cours.
 */
async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request, { ignoreSearch: false });

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.ok || response.type === 'opaque') {
            const cache = await caches.open(cacheName);
            // Ne pas mettre en cache les réponses partielles (range requests)
            if (response.status !== 206) {
                cache.put(request, response.clone());
            }
        }

        return response;
    } catch (_) {
        // Ressource absente du cache et réseau indisponible
        if (request.destination === 'image') {
            return placeholderImage();
        }
        return new Response('', { status: 503, statusText: 'Offline' });
    }
}

/**
 * Network-First avec fallback cache puis page offline.
 * Idéal pour : pages HTML de l'application.
 */
async function networkFirstWithOfflineFallback(request) {
    try {
        const response = await fetch(request);

        // Mettre en cache la page réussie pour usage offline futur
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }

        return response;
    } catch (_) {
        // Réseau indisponible → chercher dans le cache
        const cached = await caches.match(request);
        if (cached) return cached;

        // Dernière chance : page offline générique
        const offlinePage = await caches.match(OFFLINE_URL);
        return offlinePage || new Response(
            '<h1>Hors ligne</h1><p>Vérifiez votre connexion.</p>',
            { headers: { 'Content-Type': 'text/html; charset=utf-8' }, status: 503 }
        );
    }
}

/**
 * Stale-While-Revalidate : sert le cache immédiatement puis met à jour en arrière-plan.
 */
async function staleWhileRevalidate(request, cacheName) {
    const cache      = await caches.open(cacheName);
    const cached     = await cache.match(request);
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

function matchesPatterns(url, patterns) {
    return patterns.some(function (pattern) { return pattern.test(url); });
}

/** SVG placeholder 1×1 transparent pour les images manquantes */
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
    // Lire les appels sauvegardés dans IndexedDB et les envoyer quand le réseau revient
    // (Implémentation complète dans js/offline-queue.js)
    const clients = await self.clients.matchAll();
    clients.forEach(function (client) {
        client.postMessage({ type: 'SYNC_COMPLETE', tag: 'sync-attendance' });
    });
}

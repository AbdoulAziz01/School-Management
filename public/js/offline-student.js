/**
 * EduManager — Offline Student Sync
 * Sauvegarde les données de l'élève dans IndexedDB lors de chaque visite en ligne.
 * Le SW sert ensuite /offline-student.html quand le réseau est absent.
 */

(function () {
    'use strict';

    const DB_NAME      = 'edumanager-offline';
    const DB_VERSION   = 1;
    const SYNC_KEY     = 'em_last_sync';
    const SYNC_TTL     = 30 * 60 * 1000; // 30 minutes
    const STORES       = ['profile', 'stats', 'grades', 'lessons', 'schedule'];

    // ── Ouvrir (ou créer) la base IndexedDB ──────────────────────────────────
    function openDB() {
        return new Promise(function (resolve, reject) {
            var req = indexedDB.open(DB_NAME, DB_VERSION);

            req.onupgradeneeded = function (e) {
                var db = e.target.result;
                STORES.forEach(function (name) {
                    if (!db.objectStoreNames.contains(name)) {
                        db.createObjectStore(name, { keyPath: 'key' });
                    }
                });
            };

            req.onsuccess = function () { resolve(req.result); };
            req.onerror   = function () { reject(req.error); };
        });
    }

    // ── Écrire une valeur dans un store ──────────────────────────────────────
    function saveStore(db, storeName, value) {
        return new Promise(function (resolve, reject) {
            var tx    = db.transaction(storeName, 'readwrite');
            var store = tx.objectStore(storeName);
            store.put({ key: 'data', value: value, ts: Date.now() });
            tx.oncomplete = resolve;
            tx.onerror    = function () { reject(tx.error); };
        });
    }

    // ── Synchronisation principale ────────────────────────────────────────────
    async function syncOfflineData() {
        if (!navigator.onLine) return;

        // Throttle : une fois par 30 minutes max
        var last = parseInt(localStorage.getItem(SYNC_KEY) || '0', 10);
        if (Date.now() - last < SYNC_TTL) return;

        try {
            var res = await fetch('/student/offline-data', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!res.ok) return;

            var data = await res.json();
            var db   = await openDB();

            await Promise.all([
                saveStore(db, 'profile',  data.profile),
                saveStore(db, 'stats',    data.stats),
                saveStore(db, 'grades',   data.grades),
                saveStore(db, 'lessons',  data.lessons),
                saveStore(db, 'schedule', data.schedule),
            ]);

            localStorage.setItem(SYNC_KEY, Date.now().toString());

            // Afficher un toast discret pour confirmer
            showSyncBadge();
        } catch (err) {
            // Silencieux — ne jamais casser l'UI pour une erreur de sync offline
            console.warn('[EduManager Offline] Sync échoué :', err.message);
        }
    }

    // ── Badge discret de confirmation ─────────────────────────────────────────
    function showSyncBadge() {
        var el = document.createElement('div');
        el.style.cssText = [
            'position:fixed;bottom:16px;right:16px;z-index:9999',
            'background:#1c1917;color:#fbbf24;border:1px solid rgba(251,191,36,.3)',
            'border-radius:10px;padding:8px 14px;font-size:.78rem;font-weight:600',
            'display:flex;align-items:center;gap:8px',
            'box-shadow:0 4px 20px rgba(0,0,0,.4)',
            'opacity:0;transition:opacity .3s ease',
            'pointer-events:none',
        ].join(';');
        el.innerHTML = '<span style="font-size:1rem">✓</span> Données hors ligne sauvegardées';
        document.body.appendChild(el);

        requestAnimationFrame(function () {
            el.style.opacity = '1';
            setTimeout(function () {
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 400);
            }, 2500);
        });
    }

    // ── Lancer après le chargement du DOM ────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncOfflineData);
    } else {
        // Petit délai pour ne pas bloquer le rendu de la page
        setTimeout(syncOfflineData, 1500);
    }
})();

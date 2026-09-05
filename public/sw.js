// BengkelOS Service Worker — Modul 7: Hybrid Offline Sync
const CACHE_NAME = 'bengkelos-v2';
const OFFLINE_URL = '/mobile/offline';
const STATIC_ASSETS = [
    '/mobile',
    '/mobile/scanner',
    '/mobile/wo',
    '/mobile/offline',
    '/manifest.json',
    '/css/app.css',
    '/js/app.js',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(STATIC_ASSETS).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                })
                .catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL)))
        );
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                return response;
            })
            .catch(() => caches.match(request))
    );
});

// Background Sync: kirim antrian offline begitu koneksi kembali.
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-offline-queue') {
        event.waitUntil(syncOfflineQueue());
    }
});

// Fallback untuk browser yang belum dukung Background Sync (misal Safari/iOS):
// halaman memanggil ini langsung lewat postMessage saat event 'online'.
self.addEventListener('message', (event) => {
    if (event.data === 'flush-offline-queue') {
        event.waitUntil(syncOfflineQueue());
    }
});

async function syncOfflineQueue() {
    const db = await openOfflineDB();
    const items = await new Promise((resolve) => {
        const tx = db.transaction('queue', 'readonly');
        const req = tx.objectStore('queue').getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => resolve([]);
    });

    for (const item of items) {
        try {
            const response = await fetch('/api/sync/push', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Sync-Key': item.key },
                credentials: 'same-origin',
                body: JSON.stringify(item.payload),
            });

            if (response.ok) {
                await deleteQueueItem(db, item.id);
                notifyClients({ type: 'sync-success', key: item.key });
            } else if (response.status >= 400 && response.status < 500) {
                // Data tidak valid (misal WO sudah dihapus) — buang agar tidak nyangkut selamanya.
                await deleteQueueItem(db, item.id);
                notifyClients({ type: 'sync-rejected', key: item.key });
            }
            // status 5xx / network error lain: biarkan tetap di antrian untuk dicoba lagi.
        } catch (e) {
            // Masih offline / server tidak terjangkau — coba lagi nanti.
        }
    }
}

function deleteQueueItem(db, id) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('queue', 'readwrite');
        tx.objectStore('queue').delete(id);
        tx.oncomplete = resolve;
        tx.onerror = () => reject(tx.error);
    });
}

function openOfflineDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('bengkelos-offline', 1);
        req.onupgradeneeded = (e) => {
            e.target.result.createObjectStore('queue', { keyPath: 'id', autoIncrement: true });
        };
        req.onsuccess = (e) => resolve(e.target.result);
        req.onerror = (e) => reject(e);
    });
}

async function notifyClients(message) {
    const clients = await self.clients.matchAll();
    clients.forEach((client) => client.postMessage(message));
}

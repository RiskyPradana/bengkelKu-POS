// Modul 7: Hybrid Offline Sync — antrian aksi mekanik saat perangkat offline.
// Aksi ditulis ke IndexedDB di sini, lalu direplay oleh public/sw.js
// (Background Sync) atau oleh flush manual di file ini saat browser
// mendeteksi koneksi kembali (untuk browser yang belum mendukung Background
// Sync API, misalnya Safari/iOS & Firefox desktop).

const DB_NAME = 'bengkelos-offline';
const STORE_NAME = 'queue';

function openDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = (e) => {
            e.target.result.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
        };
        req.onsuccess = (e) => resolve(e.target.result);
        req.onerror = (e) => reject(e.target.error);
    });
}

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

async function queueAction(key, payload) {
    const db = await openDb();
    await new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readwrite');
        tx.objectStore(STORE_NAME).add({ key, payload, queuedAt: Date.now() });
        tx.oncomplete = resolve;
        tx.onerror = () => reject(tx.error);
    });

    window.dispatchEvent(new CustomEvent('bengkelos-queued', { detail: { key } }));

    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        try {
            const reg = await navigator.serviceWorker.ready;
            await reg.sync.register('sync-offline-queue');
        } catch (e) {
            // abaikan — akan dicoba lagi lewat flush manual saat online
        }
    } else if (navigator.onLine) {
        flushQueue();
    }

    return pendingCount();
}

async function pendingCount() {
    const db = await openDb();
    return new Promise((resolve) => {
        const tx = db.transaction(STORE_NAME, 'readonly');
        const req = tx.objectStore(STORE_NAME).count();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => resolve(0);
    });
}

async function flushQueue() {
    if ('serviceWorker' in navigator) {
        const reg = await navigator.serviceWorker.ready.catch(() => null);
        if (reg && reg.active) {
            reg.active.postMessage('flush-offline-queue');
            return;
        }
    }

    // Fallback tanpa Service Worker aktif: proses langsung dari halaman.
    const db = await openDb();
    const items = await new Promise((resolve) => {
        const tx = db.transaction(STORE_NAME, 'readonly');
        const req = tx.objectStore(STORE_NAME).getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => resolve([]);
    });

    for (const item of items) {
        try {
            const response = await fetch('/api/sync/push', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Sync-Key': item.key,
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(item.payload),
            });
            if (response.ok || (response.status >= 400 && response.status < 500)) {
                const tx = db.transaction(STORE_NAME, 'readwrite');
                tx.objectStore(STORE_NAME).delete(item.id);
            }
        } catch (e) {
            // masih offline, coba lagi nanti
        }
    }

    window.dispatchEvent(new CustomEvent('bengkelos-synced', { detail: {} }));
}

function updateOnlineState() {
    const online = navigator.onLine;
    window.dispatchEvent(new CustomEvent('bengkelos-online-status', { detail: { online } }));
    if (online) flushQueue();
}

if (typeof window !== 'undefined') {
    window.BengkelOffline = { queue: queueAction, flush: flushQueue, pendingCount };

    window.addEventListener('online', updateOnlineState);
    window.addEventListener('offline', updateOnlineState);

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && (event.data.type === 'sync-success' || event.data.type === 'sync-rejected')) {
                window.dispatchEvent(new CustomEvent('bengkelos-synced', { detail: event.data }));
            }
        });
    }

    document.addEventListener('DOMContentLoaded', updateOnlineState);
}

export { queueAction, flushQueue, pendingCount };

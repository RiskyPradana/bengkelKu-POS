<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline &mdash; BengkelOS Mekanik</title>
    <link rel="manifest" href="/manifest.json">
    <style>
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#0f172a; color:#f1f5f9; font-family:system-ui,-apple-system,sans-serif; text-align:center; padding:24px; }
        .card { max-width:340px; }
        .icon { font-size:48px; margin-bottom:12px; }
        h1 { font-size:18px; margin:0 0 8px; }
        p { color:#94a3b8; font-size:14px; margin:0 0 20px; line-height:1.5; }
        .pending { background:#1e293b; border-radius:16px; padding:12px 16px; font-size:13px; color:#fbbf24; margin-bottom:20px; }
        button { background:#f59e0b; color:#0f172a; border:none; border-radius:12px; padding:12px 24px; font-weight:600; font-size:14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#x1F4E1;</div>
        <h1>Sedang offline</h1>
        <p>Koneksi internet terputus. Aksi seperti mulai/selesaikan WO dan tambah sparepart tetap bisa disimpan &mdash; nanti otomatis terkirim saat koneksi kembali.</p>
        <div class="pending" id="pending-count">Memuat antrian...</div>
        <button onclick="window.location.reload()">Coba Lagi</button>
    </div>
    <script>
        (function () {
            if (!('indexedDB' in window)) return;
            var req = indexedDB.open('bengkelos-offline', 1);
            req.onsuccess = function (e) {
                var db = e.target.result;
                if (!db.objectStoreNames.contains('queue')) return;
                var tx = db.transaction('queue', 'readonly');
                var store = tx.objectStore('queue');
                var countReq = store.count();
                countReq.onsuccess = function () {
                    var el = document.getElementById('pending-count');
                    var n = countReq.result;
                    el.textContent = n > 0 ? (n + ' aksi menunggu disinkronkan') : 'Tidak ada aksi tertunda';
                };
            };
        })();
        window.addEventListener('online', function () { window.location.reload(); });
    </script>
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak &mdash; BengkelOS</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #0f172a;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            padding: 24px;
        }
        .card {
            max-width: 420px;
            width: 100%;
            background: #fff;
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.15);
        }
        .badge {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: #fef3c7;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }
        h1 { font-size: 20px; margin: 0 0 8px; }
        p { color: #64748b; font-size: 14px; line-height: 1.6; margin: 0 0 24px; }
        a.button {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 12px;
        }
        a.button:hover { background: #1e293b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">&#x1F512;</div>
        <h1>Akses Ditolak</h1>
        <p>{{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk membuka halaman ini. Hubungi Owner/Admin kalau menurut Anda ini keliru.' }}</p>
        <a class="button" href="{{ \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/') }}">Kembali ke Dashboard</a>
    </div>
</body>
</html>

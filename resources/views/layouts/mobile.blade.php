<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>BengkelOS Mobile</title>
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-900 text-white">

{{-- Mobile Top Bar --}}
<header class="fixed top-0 inset-x-0 z-50 bg-slate-900/95 backdrop-blur border-b border-slate-800 safe-area-top">
    <div class="flex items-center px-4 h-14 gap-3">
        <span class="text-amber-400 text-lg font-bold tracking-tight">&#x1F527; BengkelOS</span>
        <div class="flex-1"></div>
        <span class="text-slate-400 text-xs">{{ auth()->user()?->name }}</span>
    </div>

    {{-- Modul 7: Hybrid Offline Sync — indikator status koneksi & antrian tertunda --}}
    <div x-data="{ online: navigator.onLine, pending: 0 }"
         x-init="
            window.addEventListener('bengkelos-online-status', (e) => online = e.detail.online);
            window.addEventListener('bengkelos-queued', async () => { if (window.BengkelOffline) pending = await window.BengkelOffline.pendingCount(); });
            window.addEventListener('bengkelos-synced', async () => { if (window.BengkelOffline) pending = await window.BengkelOffline.pendingCount(); });
            (async () => { if (window.BengkelOffline) pending = await window.BengkelOffline.pendingCount(); })();
         "
         x-show="!online || pending > 0"
         style="display:none"
         class="px-4 py-1.5 text-xs font-medium text-center"
         :class="online ? 'bg-amber-500/20 text-amber-300' : 'bg-red-500/20 text-red-300'">
        <span x-show="!online">&#x1F4E1; Sedang offline &mdash; aksi tetap tersimpan</span>
        <span x-show="online &amp;&amp; pending > 0" x-text="'\u2601\uFE0F Menyinkronkan ' + pending + ' aksi tertunda...'"></span>
    </div>
</header>

{{-- Content --}}
<main class="pt-14 pb-20 min-h-full">
    {{ $slot }}
</main>

{{-- Bottom Navigation --}}
<nav class="fixed bottom-0 inset-x-0 z-50 bg-slate-900/95 backdrop-blur border-t border-slate-800 safe-area-bottom">
    <div class="grid grid-cols-3 h-16">
        <a href="/mobile" class="flex flex-col items-center justify-center gap-1 text-xs {{ request()->is('mobile') ? 'text-amber-400' : 'text-slate-400' }}">
            <span class="text-xl">&#x1F3E0;</span>
            <span>Beranda</span>
        </a>
        <a href="/mobile/scanner" class="flex flex-col items-center justify-center gap-1 text-xs {{ request()->is('mobile/scanner') ? 'text-amber-400' : 'text-slate-400' }}">
            <span class="text-xl">&#x1F4F7;</span>
            <span>Scan</span>
        </a>
        <a href="/mobile/wo" class="flex flex-col items-center justify-center gap-1 text-xs {{ request()->is('mobile/wo') ? 'text-amber-400' : 'text-slate-400' }}">
            <span class="text-xl">&#x1F4CB;</span>
            <span>Work Order</span>
        </a>
    </div>
</nav>

@livewireScripts
@stack('scripts')
</body>
</html>

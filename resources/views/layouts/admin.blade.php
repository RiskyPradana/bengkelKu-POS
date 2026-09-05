{{--
    Berkas ini sengaja hanya meneruskan ke layout utama di components/layouts/app.blade.php,
    sama seperti layouts/app.blade.php.

    Dulu berkas ini punya tampilan sendiri (sidebar gelap, font Barlow Condensed, tanpa mode
    gelap), itu sebabnya halaman yang memakainya (Dashboard, Work Order, Pelanggan, Katalog,
    Inventaris, CRM & Reminder, Komisi, Laporan) terlihat BEDA dari Kasir & Manajemen User yang
    sudah memakai tema baru. Sekarang cukup rawat SATU berkas: components/layouts/app.blade.php
--}}
@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Dipertahankan agar halaman lama yang masih memakai class ini tidak kehilangan gaya font-nya */
        .font-display { font-family: 'Barlow Condensed', sans-serif; }
        .font-mono-jet { font-family: 'JetBrains Mono', monospace; }
    </style>
@endpush

<x-layouts.app :title="$pageTitle ?? ($title ?? null)" :subtitle="$pageSub ?? null">
    <div class="p-4 space-y-6 md:p-6">
        {{ $slot ?? '' }}
    </div>
</x-layouts.app>

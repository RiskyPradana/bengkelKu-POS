{{-- Berkas ini sengaja hanya meneruskan ke layout utama di components/layouts/app.blade.php. Dulu dua berkas ini berisi tampilan berbeda, itu sebabnya Dashboard gelap tapi Kasir putih. Sekarang cukup rawat SATU berkas: components/layouts/app.blade.php --}}

<x-layouts.app :title="$title ?? null" :subtitle="$subtitle ?? null">
    {{ $slot ?? '' }}
</x-layouts.app>

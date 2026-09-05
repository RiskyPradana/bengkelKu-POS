@props([
    'title'          => null,
    'subtitle'       => null,
    'startCollapsed' => false,
])

<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'BengkelOS' }}</title>

    {{--
        Terapkan tema SEBELUM halaman digambar.
        Tanpa ini, layar akan berkedip putih dulu saat mode gelap dipakai.
    --}}
    <script>
        (function () {
            try {
                var tema = localStorage.getItem('bengkelos-tema');
                if (tema === 'gelap') {
                    document.documentElement.classList.add('dark');
                } else if (tema === null && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Chart.js untuk Dashboard Analitik --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    {{-- Samakan warna teks & garis grafik dengan tema aktif --}}
    <script>
        (function () {
            if (! window.Chart) { return; }

            function terapkanWarnaChart() {
                var gelap = document.documentElement.classList.contains('dark');
                window.Chart.defaults.color = gelap ? '#94a3b8' : '#475569';
                window.Chart.defaults.borderColor = gelap
                    ? 'rgba(148, 163, 184, 0.15)'
                    : 'rgba(100, 116, 139, 0.15)';
            }

            terapkanWarnaChart();

            // Saat tombol tema diklik, gambar ulang semua grafik
            window.addEventListener('tema-berubah', function () {
                terapkanWarnaChart();
                Object.values(window.Chart.instances || {}).forEach(function (c) {
                    try { c.update(); } catch (e) {}
                });
            });
        })();
    </script>

    @stack('head')
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased">

@php
    use Illuminate\Support\Facades\Route as RouteFacade;
    use App\Domains\MasterData\Services\RoleRegistry;

    /**
     * Menu inti, dikelompokkan seperti sidebar asli.
     * Route yang belum terdaftar otomatis disembunyikan, jadi tidak error.
     */
    $coreGroups = [
        [
            'label' => 'Ringkasan',
            'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'label' => 'Operasional',
            'items' => [
                ['route' => 'work-orders', 'label' => 'Work Order (SPK)', 'icon' => 'clipboard'],
                ['route' => 'pos.cashier', 'label' => 'Kasir & POS',      'icon' => 'cart'],
            ],
        ],
        [
            'label' => 'Data Master',
            'items' => [
                ['route' => 'customers', 'label' => 'Pelanggan',            'icon' => 'people'],
                ['route' => 'catalog',   'label' => 'Katalog Produk & Jasa', 'icon' => 'box'],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                ['route' => 'reports', 'label' => 'Laporan Keuangan', 'icon' => 'report'],
            ],
        ],
    ];

    $coreIcons = [
        'home'      => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'clipboard' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'cart'      => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'people'    => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'box'       => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'report'    => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    ];

    // Buang menu yang route-nya belum ada, lalu buang grup yang jadi kosong
    $coreGroups = collect($coreGroups)
        ->map(function (array $group) {
            $group['items'] = collect($group['items'])
                ->filter(fn (array $item) => RouteFacade::has($item['route']))
                ->values()
                ->all();

            return $group;
        })
        ->filter(fn (array $group) => count($group['items']) > 0)
        ->values()
        ->all();

    $currentUser = auth()->user();

    $inisial = $currentUser
        ? collect(explode(' ', trim($currentUser->name)))
            ->filter()
            ->take(2)
            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
            ->implode('')
        : '?';

    $roleLabel = '';
    if ($currentUser && ! empty($currentUser->role)) {
        $roleMeta  = RoleRegistry::list()[$currentUser->role] ?? null;
        $roleLabel = $roleMeta['label'] ?? $currentUser->role;
    }
@endphp

<div x-data="{
        sidebarOpen: false,
        sidebarCollapsed: {{ ($startCollapsed ?? false) ? 'true' : 'false' }},
        gelap: document.documentElement.classList.contains('dark'),
        toggleTema() {
            this.gelap = ! this.gelap;
            document.documentElement.classList.toggle('dark', this.gelap);
            try {
                localStorage.setItem('bengkelos-tema', this.gelap ? 'gelap' : 'terang');
            } catch (e) {}
            window.dispatchEvent(new CustomEvent('tema-berubah', { detail: { gelap: this.gelap } }));
        }
     }"
     class="min-h-screen">

    {{-- Latar gelap saat sidebar dibuka di HP --}}
    <div x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"
         style="display: none;"></div>

    {{-- ============================ SIDEBAR ============================ --}}
    {{--
        sidebarOpen  -> kontrol drawer overlay di layar kecil (HP/tablet).
        sidebarCollapsed -> kontrol sembunyikan sidebar secara permanen di desktop
                            (dipakai misalnya oleh halaman Kasir supaya tampil
                            full-screen), bisa ditampilkan lagi lewat tombol di topbar.
    --}}
    <aside :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarCollapsed ? 'lg:-translate-x-full' : 'lg:translate-x-0',
            ]"
           class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 transition-transform duration-200 border-r bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 h-[72px] shrink-0 border-b border-slate-200 dark:border-slate-800">
            <a href="{{ RouteFacade::has('dashboard') ? route('dashboard') : url('/') }}"
               class="flex items-center gap-3 min-w-0">
                <div class="flex items-center justify-center rounded-xl shadow-sm w-11 h-11 shrink-0 bg-amber-400 dark:bg-amber-400">
                    <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-bold leading-tight truncate text-slate-900 dark:text-white">BengkelOS</p>
                    <p class="text-[10px] font-semibold tracking-wider uppercase truncate text-slate-400 dark:text-slate-500">Manajemen Bengkel</p>
                </div>
            </a>

            <button @click="sidebarOpen = false"
                    class="p-1.5 ml-auto rounded-lg lg:hidden text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Menu --}}
        {{--
            Sidebar mengingat posisi scroll terakhir lewat sessionStorage, jadi
            begitu pindah halaman (full page reload, bukan SPA), sidebar tidak
            balik ke posisi paling atas lagi — tetap di posisi menu yang tadi diklik.
        --}}
        <nav
            x-init="
                $nextTick(() => {
                    try {
                        var saved = sessionStorage.getItem('bengkelos-sidebar-scroll');
                        if (saved !== null) { $el.scrollTop = parseInt(saved, 10) || 0; }
                    } catch (e) {}
                });
            "
            @scroll.debounce.150ms="
                try { sessionStorage.setItem('bengkelos-sidebar-scroll', String($el.scrollTop)); } catch (e) {}
            "
            class="flex-1 px-3 pb-4 overflow-y-auto">

            @foreach ($coreGroups as $group)
                <div class="mt-5">
                    <p class="px-3 mb-2 text-xs font-semibold tracking-wider uppercase text-slate-400 dark:text-slate-500">
                        {{ $group['label'] }}
                    </p>

                    @foreach ($group['items'] as $item)
                        @php $isActive = request()->routeIs($item['route']); @endphp

                        <a href="{{ route($item['route']) }}"
                           @class([
                               'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                               'bg-amber-50 text-amber-700 dark:bg-slate-800 dark:text-amber-400' => $isActive,
                               'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! $isActive,
                           ])>
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $coreIcons[$item['icon']] ?? '' }}" />
                            </svg>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach

            {{-- Menu add-on: Analisa, Operasional, Mobile, Pengaturan --}}
            <x-sidebar-addons />

        </nav>

        {{-- Info user --}}
        @if ($currentUser)
            <div class="flex items-center gap-3 px-4 py-3 border-t shrink-0 border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-center w-10 h-10 text-sm font-bold rounded-full shrink-0 bg-amber-400 text-slate-900">
                    {{ $inisial }}
                </div>

                <div class="min-w-0">
                    <p class="text-sm font-semibold truncate text-slate-900 dark:text-white">{{ $currentUser->name }}</p>
                    <p class="text-xs truncate text-slate-500 dark:text-slate-400">
                        {{ $roleLabel ?: $currentUser->email }}
                    </p>
                </div>

                @if (RouteFacade::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                        @csrf
                        <button type="submit" title="Keluar"
                                class="p-2 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </aside>

    {{-- ============================ KONTEN ============================ --}}
    <div :class="sidebarCollapsed ? 'lg:pl-0' : 'lg:pl-64'" class="transition-[padding] duration-200">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex items-center gap-3 px-4 border-b h-[72px] bg-white/90 dark:bg-slate-900/90 backdrop-blur border-slate-200 dark:border-slate-800 lg:px-6">

            {{-- Hamburger: khusus HP/tablet, buka drawer sidebar --}}
            <button @click="sidebarOpen = true"
                    class="p-2 -ml-2 rounded-lg lg:hidden text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            {{-- Tombol sembunyikan/tampilkan sidebar: khusus desktop, berlaku di semua halaman --}}
            <button @click="sidebarCollapsed = ! sidebarCollapsed"
                    :title="sidebarCollapsed ? 'Tampilkan sidebar' : 'Sembunyikan sidebar'"
                    class="hidden lg:inline-flex p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h10M3 17h18" />
                </svg>
            </button>

            <div class="min-w-0">
                <h1 class="text-xl font-bold leading-tight truncate text-slate-900 dark:text-white">
                    {{ $title ?? 'Dashboard' }}
                </h1>
                @if ($subtitle)
                    <p class="text-sm truncate text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>

            <div class="flex items-center gap-3 ml-auto">

                {{-- Tanggal --}}
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        {{ now()->translatedFormat('D, d M Y') }}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        {{ now()->format('H:i') }} WITA
                    </p>
                </div>

                {{-- Tombol tema --}}
                <button @click="toggleTema()"
                        :title="gelap ? 'Ganti ke tema terang' : 'Ganti ke tema gelap'"
                        class="p-2 border rounded-lg border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">

                    {{-- Bulan: tampil saat tema terang --}}
                    <svg x-show="! gelap" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>

                    {{-- Matahari: tampil saat tema gelap --}}
                    <svg x-show="gelap" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>

                {{-- Avatar --}}
                @if ($currentUser)
                    <div class="flex items-center justify-center w-10 h-10 text-sm font-bold rounded-full shrink-0 bg-amber-400 text-slate-900">
                        {{ $inisial }}
                    </div>
                @endif
            </div>
        </header>

        {{-- Pesan flash --}}
        @if (session('sukses') || session('gagal') || session('status'))
            <div class="px-4 pt-4 space-y-2 lg:px-6">
                @if (session('sukses'))
                    <div class="p-3 text-sm border rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300">
                        {{ session('sukses') }}
                    </div>
                @endif
                @if (session('gagal'))
                    <div class="p-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300">
                        {{ session('gagal') }}
                    </div>
                @endif
                @if (session('status'))
                    <div class="p-3 text-sm text-blue-800 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-500/10 dark:border-blue-500/30 dark:text-blue-300">
                        {{ session('status') }}
                    </div>
                @endif
            </div>
        @endif

        <main>
            {{ $slot ?? '' }}
        </main>
    </div>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>

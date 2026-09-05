{{--
    Menu Sidebar Add-On (Modul 3–7 + Fase 3)

    Cara pakai: sisipkan di dalam <nav> sidebar pada layouts/app.blade.php:
        <x-sidebar-addons />

    Hak akses diatur di config/roles.php — bukan di file ini.
--}}

@php
    use Illuminate\Support\Facades\Route as RouteFacade;
    use Illuminate\Support\Facades\Schema;

    $user = auth()->user();

    // Deteksi apakah kolom role sudah tersedia
    $hasRoleColumn = false;
    try {
        $hasRoleColumn = Schema::hasColumn('users', 'role');
    } catch (\Throwable $e) {
        $hasRoleColumn = false;
    }

    $userRole  = $hasRoleColumn ? strtolower((string) ($user->role ?? '')) : '';
    $permissive = ! $hasRoleColumn && config('roles.permissive_when_no_role_column', true);

    $access = config('roles.access', []);

    $navGroups = [
        [
            'label' => 'Analisa',
            'items' => [
                ['route' => 'analytics', 'icon' => 'chart', 'label' => 'Dashboard Analitik'],
            ],
        ],
        [
            'label' => 'Operasional',
            'items' => [
                ['route' => 'inventory',     'icon' => 'cube', 'label' => 'Stok Multi-Cabang'],
                ['route' => 'purchasing',    'icon' => 'cart', 'label' => 'Pembelian & Supplier'],
                ['route' => 'crm.reminders', 'icon' => 'chat', 'label' => 'CRM & Pengingat WA'],
                ['route' => 'commission',    'icon' => 'cash', 'label' => 'Komisi Mekanik'],
            ],
        ],
        [
            'label' => 'Mobile',
            'items' => [
                ['route' => 'mobile.home',    'icon' => 'device', 'label' => 'Mode Mekanik'],
                ['route' => 'mobile.scanner', 'icon' => 'qrcode', 'label' => 'Scan Sparepart'],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'items' => [
                ['route' => 'settings.users', 'icon' => 'users', 'label' => 'Manajemen User'],
            ],
        ],
    ];

    $icons = [
        'cube'   => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'cart'   => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'chat'   => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'cash'   => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'device' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        'qrcode' => 'M12 4v1m6 11h2m-6 0h-1v4m-6-4h1v4m5-16v1m-5 4H4m5 0H8m0 0V4m0 5h1m6 0h1m-1 0V4m5 5h-1m-4 0h-1m5 5h-1m-4 0h-1m0 0v5m5-5v5m-5 0h5',
        'chart'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'users'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    ];

    // Filter: route harus terdaftar DAN role harus diizinkan
    $navGroups = collect($navGroups)
        ->map(function (array $group) use ($access, $userRole, $permissive) {
            $group['items'] = collect($group['items'])
                ->filter(fn (array $item) => RouteFacade::has($item['route']))
                ->filter(function (array $item) use ($access, $userRole, $permissive) {
                    if ($permissive) {
                        return true;
                    }

                    $allowed = $access[$item['route']] ?? null;

                    // Route tanpa aturan dianggap terbuka untuk semua user login
                    return $allowed === null || in_array($userRole, $allowed, true);
                })
                ->values()
                ->all();

            return $group;
        })
        ->filter(fn (array $group) => count($group['items']) > 0)
        ->values()
        ->all();
@endphp

{{-- Peringatan kalau kolom role belum ada (hanya tampil di mode debug) --}}
@if (! $hasRoleColumn && config('app.debug'))
    <div class="mx-3 mt-4 p-2.5 text-xs rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300">
        <b>Kolom `role` belum ada.</b><br>
        Jalankan <code class="px-1 bg-amber-100 dark:bg-amber-500/20 rounded">php artisan migrate</code> lalu buka Manajemen User.
    </div>
@endif

@if (count($navGroups) === 0 && config('app.debug'))
    <div class="mx-3 mt-4 p-2.5 text-xs rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-300">
        <b>Tidak ada menu yang bisa ditampilkan.</b><br>
        Role kamu: <code class="px-1 bg-red-100 dark:bg-red-500/20 rounded">{{ $userRole ?: 'kosong' }}</code><br>
        Jalankan <code class="px-1 bg-red-100 dark:bg-red-500/20 rounded">php artisan bengkel:doctor</code> untuk diagnosa.
    </div>
@endif

@foreach ($navGroups as $group)
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] ?? '' }}" />
                </svg>
                <span>{{ $item['label'] }}</span>

                {{-- Badge stok rendah --}}
                @if ($item['route'] === 'inventory')
                    @php
                        $lowStock = 0;
                        try {
                            $lowStock = \App\Domains\Inventory\Models\BranchStock::query()
                                ->whereColumn('quantity', '<=', 'min_stock')
                                ->count();
                        } catch (\Throwable $e) {
                            $lowStock = 0;
                        }
                    @endphp
                    @if ($lowStock > 0)
                        <span class="ml-auto px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $lowStock }}</span>
                    @endif
                @endif

                {{-- Badge hutang supplier belum lunas --}}
                @if ($item['route'] === 'purchasing')
                    @php
                        $unpaidPo = 0;
                        try {
                            $unpaidPo = \App\Domains\Purchasing\Models\PurchaseOrder::query()
                                ->whereIn('status', ['ordered', 'partially_received', 'received'])
                                ->where('payment_status', '!=', 'lunas')
                                ->count();
                        } catch (\Throwable $e) {
                            $unpaidPo = 0;
                        }
                    @endphp
                    @if ($unpaidPo > 0)
                        <span class="ml-auto px-2 py-0.5 text-xs font-bold text-white bg-amber-500 rounded-full">{{ $unpaidPo }}</span>
                    @endif
                @endif

                {{-- Badge pengingat jatuh tempo --}}
                @if ($item['route'] === 'crm.reminders')
                    @php
                        $dueToday = 0;
                        try {
                            $dueToday = \App\Domains\CRM\Models\ServiceReminder::query()
                                ->where('status', 'pending')
                                ->whereDate('due_date', '<=', now())
                                ->count();
                        } catch (\Throwable $e) {
                            $dueToday = 0;
                        }
                    @endphp
                    @if ($dueToday > 0)
                        <span class="ml-auto px-2 py-0.5 text-xs font-bold text-white bg-amber-500 rounded-full">{{ $dueToday }}</span>
                    @endif
                @endif
            </a>
        @endforeach
    </div>
@endforeach

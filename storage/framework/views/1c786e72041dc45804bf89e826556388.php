<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title'    => null,
    'subtitle' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title'    => null,
    'subtitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'BengkelOS'); ?></title>

    
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

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>


    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    
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

    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased">

<?php
    use Illuminate\Support\Facades\Route as RouteFacade;

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
        $roleLabel = config('roles.list.' . $currentUser->role . '.label', $currentUser->role);
    }
?>

<div x-data="{
        sidebarOpen: false,
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

    
    <div x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"
         style="display: none;"></div>

    
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 transition-transform duration-200 border-r bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 lg:translate-x-0">

        
        <div class="flex items-center gap-3 px-4 h-[72px] shrink-0 border-b border-slate-200 dark:border-slate-800">
            <a href="<?php echo e(RouteFacade::has('dashboard') ? route('dashboard') : url('/')); ?>"
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

        
        <nav class="flex-1 px-3 pb-4 overflow-y-auto">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coreGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mt-5">
                    <p class="px-3 mb-2 text-xs font-semibold tracking-wider uppercase text-slate-400 dark:text-slate-500">
                        <?php echo e($group['label']); ?>

                    </p>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $isActive = request()->routeIs($item['route']); ?>

                        <a href="<?php echo e(route($item['route'])); ?>"
                           class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                               'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                               'bg-amber-50 text-amber-700 dark:bg-slate-800 dark:text-amber-400' => $isActive,
                               'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' => ! $isActive,
                           ]); ?>">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($coreIcons[$item['icon']] ?? ''); ?>" />
                            </svg>
                            <span class="truncate"><?php echo e($item['label']); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if (isset($component)) { $__componentOriginal98d08e464582ad923bf653905b0943c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98d08e464582ad923bf653905b0943c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-addons','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-addons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98d08e464582ad923bf653905b0943c3)): ?>
<?php $attributes = $__attributesOriginal98d08e464582ad923bf653905b0943c3; ?>
<?php unset($__attributesOriginal98d08e464582ad923bf653905b0943c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98d08e464582ad923bf653905b0943c3)): ?>
<?php $component = $__componentOriginal98d08e464582ad923bf653905b0943c3; ?>
<?php unset($__componentOriginal98d08e464582ad923bf653905b0943c3); ?>
<?php endif; ?>

        </nav>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser): ?>
            <div class="flex items-center gap-3 px-4 py-3 border-t shrink-0 border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-center w-10 h-10 text-sm font-bold rounded-full shrink-0 bg-amber-400 text-slate-900">
                    <?php echo e($inisial); ?>

                </div>

                <div class="min-w-0">
                    <p class="text-sm font-semibold truncate text-slate-900 dark:text-white"><?php echo e($currentUser->name); ?></p>
                    <p class="text-xs truncate text-slate-500 dark:text-slate-400">
                        <?php echo e($roleLabel ?: $currentUser->email); ?>

                    </p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(RouteFacade::has('logout')): ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="ml-auto">
                        <?php echo csrf_field(); ?>
                        <button type="submit" title="Keluar"
                                class="p-2 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </aside>

    
    <div class="lg:pl-64">

        
        <header class="sticky top-0 z-20 flex items-center gap-3 px-4 border-b h-[72px] bg-white/90 dark:bg-slate-900/90 backdrop-blur border-slate-200 dark:border-slate-800 lg:px-6">

            <button @click="sidebarOpen = true"
                    class="p-2 -ml-2 rounded-lg lg:hidden text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="min-w-0">
                <h1 class="text-xl font-bold leading-tight truncate text-slate-900 dark:text-white">
                    <?php echo e($title ?? 'Dashboard'); ?>

                </h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle): ?>
                    <p class="text-sm truncate text-slate-500 dark:text-slate-400"><?php echo e($subtitle); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex items-center gap-3 ml-auto">

                
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <?php echo e(now()->translatedFormat('D, d M Y')); ?>

                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        <?php echo e(now()->format('H:i')); ?> WITA
                    </p>
                </div>

                
                <button @click="toggleTema()"
                        :title="gelap ? 'Ganti ke tema terang' : 'Ganti ke tema gelap'"
                        class="p-2 border rounded-lg border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">

                    
                    <svg x-show="! gelap" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>

                    
                    <svg x-show="gelap" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentUser): ?>
                    <div class="flex items-center justify-center w-10 h-10 text-sm font-bold rounded-full shrink-0 bg-amber-400 text-slate-900">
                        <?php echo e($inisial); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </header>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('sukses') || session('gagal') || session('status')): ?>
            <div class="px-4 pt-4 space-y-2 lg:px-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('sukses')): ?>
                    <div class="p-3 text-sm border rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300">
                        <?php echo e(session('sukses')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('gagal')): ?>
                    <div class="p-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300">
                        <?php echo e(session('gagal')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                    <div class="p-3 text-sm text-blue-800 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-500/10 dark:border-blue-500/30 dark:text-blue-300">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <main>
            <?php echo e($slot ?? ''); ?>

        </main>
    </div>
</div>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>
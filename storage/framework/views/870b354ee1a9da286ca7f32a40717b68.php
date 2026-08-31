<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($pageTitle ?? 'Dashboard'); ?> &mdash; BengkelOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8f7f5; }
        .font-display { font-family: 'Barlow Condensed', sans-serif; }
        .font-mono-jet { font-family: 'JetBrains Mono', monospace; }

        /* Sidebar nav links */
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 10px;
            font-size: 13.5px; font-weight: 500; color: #94a3b8;
            text-decoration: none; transition: all 0.15s ease;
        }
        .nav-link:hover { background: rgba(255,255,255,.07); color: #e2e8f0; }
        .nav-link.active {
            background: rgba(251,191,36,.15);
            color: #fbbf24;
        }
        .nav-link .nav-icon { font-size: 15px; width: 20px; text-align: center; flex-shrink: 0; }

        /* Scrollbar slim */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
    </style>
</head>
<body>
<div class="flex min-h-screen">

    
    <aside class="w-60 bg-slate-900 flex flex-col fixed inset-y-0 left-0 z-30 shadow-2xl">

        
        <div class="px-4 pt-5 pb-4 border-b border-slate-800/70">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-400 flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-slate-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085"/>
                    </svg>
                </div>
                <div>
                    <div class="font-display text-[20px] font-bold text-white leading-none tracking-tight">BengkelOS</div>
                    <div class="text-[9px] font-medium tracking-widest uppercase text-slate-500 mt-0.5">Manajemen Bengkel</div>
                </div>
            </div>
        </div>

        
        <nav class="flex-1 px-3 py-5 space-y-5 overflow-y-auto">

            <div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-600 px-3 mb-1.5">Ringkasan</p>
                <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <span class="nav-icon">&#x1F3E0;</span>
                    <span>Dashboard</span>
                </a>
            </div>

            <div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-600 px-3 mb-1.5">Operasional</p>
                <div class="space-y-0.5">
                    <a href="<?php echo e(route('work-orders')); ?>" class="nav-link <?php echo e(request()->routeIs('work-orders') ? 'active' : ''); ?>">
                        <span class="nav-icon">&#x1F4CB;</span>
                        <span>Work Order (SPK)</span>
                    </a>
                    <a href="<?php echo e(route('pos.cashier')); ?>" class="nav-link <?php echo e(request()->routeIs('pos.cashier') ? 'active' : ''); ?>">
                        <span class="nav-icon">&#x1F9EE;</span>
                        <span>Kasir &amp; POS</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-600 px-3 mb-1.5">Data Master</p>
                <div class="space-y-0.5">
                    <a href="<?php echo e(route('customers')); ?>" class="nav-link <?php echo e(request()->routeIs('customers') ? 'active' : ''); ?>">
                        <span class="nav-icon">&#x1F465;</span>
                        <span>Pelanggan</span>
                    </a>
                    <a href="<?php echo e(route('catalog')); ?>" class="nav-link <?php echo e(request()->routeIs('catalog') ? 'active' : ''); ?>">
                        <span class="nav-icon">&#x1F4E6;</span>
                        <span>Katalog Produk &amp; Jasa</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-600 px-3 mb-1.5">Laporan</p>
                <a href="<?php echo e(route('reports')); ?>" class="nav-link <?php echo e(request()->routeIs('reports') ? 'active' : ''); ?>">
                    <span class="nav-icon">&#x1F4CA;</span>
                    <span>Laporan Keuangan</span>
                </a>
            </div>

            
            <div class="pt-2 border-t border-slate-800/70">
                <p class="text-[9px] font-bold uppercase tracking-widest text-slate-600 px-3 mb-1.5">Add-on (Coming Soon)</p>
                <div class="space-y-0.5 opacity-40 pointer-events-none">
                    <div class="nav-link"><span class="nav-icon">&#x1F4F1;</span><span>Mobile Mekanik (PWA)</span></div>
                    <div class="nav-link"><span class="nav-icon">&#x1F3EA;</span><span>Multi-Cabang</span></div>
                    <div class="nav-link"><span class="nav-icon">&#x1F4AC;</span><span>CRM &amp; WhatsApp</span></div>
                </div>
            </div>

        </nav>

        
        <div class="px-3 py-3 border-t border-slate-800/70">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-900 font-bold text-sm flex-shrink-0">
                    <?php echo e(strtoupper(substr(auth()->user()->name ?? 'A', 0, 1))); ?>

                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-semibold text-slate-200 truncate"><?php echo e(auth()->user()->name ?? 'Admin'); ?></div>
                    <div class="text-[10px] text-slate-500 truncate"><?php echo e(auth()->user()->email ?? ''); ?></div>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" title="Logout"
                            class="p-1.5 rounded-lg text-slate-600 hover:text-red-400 hover:bg-red-400/10 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    
    <div class="flex-1 ml-60 flex flex-col min-h-screen">

        
        <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-6 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="font-display text-[22px] font-bold text-slate-800 leading-none">
                        <?php echo e($pageTitle ?? 'Dashboard'); ?>

                    </h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($pageSub)): ?>
                    <p class="text-xs text-slate-400 mt-0.5"><?php echo e($pageSub); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden md:block">
                        <div class="text-xs font-semibold text-slate-700">
                            <?php echo e(now()->locale('id')->isoFormat('ddd, D MMM YYYY')); ?>

                        </div>
                        <div class="text-xs text-slate-400"><?php echo e(now()->format('H:i')); ?> WIB</div>
                    </div>
                    <div class="w-px h-7 bg-slate-200 hidden md:block"></div>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-900 font-bold text-sm">
                        <?php echo e(strtoupper(substr(auth()->user()->name ?? 'A', 0, 1))); ?>

                    </div>
                </div>
            </div>
        </header>

        
        <main class="flex-1 p-6 overflow-x-hidden">
            <?php echo e($slot); ?>

        </main>

        <footer class="px-6 py-3 border-t border-slate-200/60 text-center text-[11px] text-slate-400">
            &copy; <?php echo e(date('Y')); ?> BengkelOS &mdash; Laravel <?php echo e(app()->version()); ?>

        </footer>
    </div>
</div>


<div x-data="{ toasts: [] }"
     x-on:notify.window="toasts.push($event.detail); setTimeout(() => toasts.splice(toasts.indexOf($event.detail), 1), 3500)"
     class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none">
    <template x-for="(t, i) in toasts" :key="i">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800': t.type === 'success',
                'bg-red-50 border-red-200 text-red-800': t.type === 'error',
                'bg-blue-50 border-blue-200 text-blue-800': t.type === 'info',
             }"
             class="flex items-center gap-3 px-4 py-3 rounded-xl border shadow-xl text-sm font-medium min-w-[280px] pointer-events-auto">
            <span x-text="t.type==='success' ? '&#x2705;' : t.type==='error' ? '&#x274C;' : '&#x2139;&#xFE0F;'"></span>
            <span x-text="t.message"></span>
        </div>
    </template>
</div>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/layouts/admin.blade.php ENDPATH**/ ?>
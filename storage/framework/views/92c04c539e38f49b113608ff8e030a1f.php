<?php
    $stats  = $this->workOrderStats;
    $kanban = $this->kanban;
    $recent = $this->recentWorkOrders;
?>

<div class="space-y-6">


<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">

    
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total WO</span>
            <span class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-base">&#x1F4CB;</span>
        </div>
        <div class="font-display text-4xl font-bold text-slate-800"><?php echo e($stats['total']); ?></div>
        <div class="h-1 w-full rounded-full bg-slate-200"></div>
    </div>

    
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Pending</span>
            <span class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center text-base">&#x23F3;</span>
        </div>
        <div class="font-display text-4xl font-bold text-amber-500"><?php echo e($stats['pending']); ?></div>
        <div class="h-1 w-full rounded-full bg-amber-200"></div>
    </div>

    
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">In Progress</span>
            <span class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-base">&#x1F527;</span>
        </div>
        <div class="font-display text-4xl font-bold text-blue-500"><?php echo e($stats['in_progress']); ?></div>
        <div class="h-1 w-full rounded-full bg-blue-200"></div>
    </div>

    
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Selesai</span>
            <span class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-base">&#x2705;</span>
        </div>
        <div class="font-display text-4xl font-bold text-emerald-500"><?php echo e($stats['completed']); ?></div>
        <div class="h-1 w-full rounded-full bg-emerald-200"></div>
    </div>

    
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Lunas</span>
            <span class="w-8 h-8 rounded-xl bg-violet-50 flex items-center justify-center text-base">&#x1F4B0;</span>
        </div>
        <div class="font-display text-4xl font-bold text-violet-500"><?php echo e($stats['paid']); ?></div>
        <div class="h-1 w-full rounded-full bg-violet-200"></div>
    </div>

    
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Pelanggan</span>
            <span class="w-8 h-8 rounded-xl bg-rose-50 flex items-center justify-center text-base">&#x1F464;</span>
        </div>
        <div class="font-display text-4xl font-bold text-rose-500"><?php echo e($stats['total_customers']); ?></div>
        <div class="h-1 w-full rounded-full bg-rose-200"></div>
    </div>

</div>


<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 p-6 text-white">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full bg-amber-400"></div>
            <div class="absolute -bottom-12 -left-8 w-40 h-40 rounded-full bg-blue-500"></div>
        </div>
        <div class="relative">
            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Omzet <?php echo e(now()->locale('id')->isoFormat('MMMM YYYY')); ?></div>
            <div class="font-display text-5xl font-bold text-white mt-2">
                Rp <?php echo e(number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.')); ?>

            </div>
            <div class="text-xs text-slate-400 mt-2">Total invoice lunas bulan ini</div>
        </div>
        <div class="relative mt-5 flex items-center gap-2">
            <span class="inline-flex items-center gap-1 bg-white/10 rounded-lg px-3 py-1.5 text-xs font-medium">
                &#x1F4C5; <?php echo e(now()->locale('id')->isoFormat('MMMM')); ?>

            </span>
        </div>
    </div>

    
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 p-6 text-white">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full bg-white"></div>
        </div>
        <div class="relative">
            <div class="text-[10px] font-bold uppercase tracking-widest text-blue-200">Omzet Hari Ini</div>
            <div class="font-display text-5xl font-bold text-white mt-2">
                Rp <?php echo e(number_format($stats['today_revenue'] ?? 0, 0, ',', '.')); ?>

            </div>
            <div class="text-xs text-blue-200 mt-2"><?php echo e(now()->locale('id')->isoFormat('dddd, D MMMM YYYY')); ?></div>
        </div>
        <div class="relative mt-5 flex items-center gap-2">
            <span class="inline-flex items-center gap-1 bg-white/15 rounded-lg px-3 py-1.5 text-xs font-medium">
                &#x1F551; <?php echo e(now()->format('H:i')); ?> WIB
            </span>
        </div>
    </div>

</div>


<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-slate-800">Work Order Board</h2>
            <p class="text-xs text-slate-400 mt-0.5">Status real-time semua pekerjaan bengkel</p>
        </div>
        <a href="<?php echo e(route('work-orders')); ?>"
           class="text-xs font-semibold text-amber-500 hover:text-amber-600 transition-colors inline-flex items-center gap-1">
            Kelola semua
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-slate-100">

        
        <div class="p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Pending</span>
                <span class="ml-auto font-display text-xl font-bold text-amber-500">
                    <?php echo e(isset($kanban['Pending']) ? count($kanban['Pending']) : 0); ?>

                </span>
            </div>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($kanban['Pending'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl bg-amber-50 border border-amber-100 p-3">
                    <div class="font-semibold text-[12px] text-slate-700 truncate"><?php echo e($wo->customer?->name ?? '—'); ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5"><?php echo e($wo->vehicle?->plate_number ?? '—'); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4 text-slate-300 text-xs">Kosong</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Dikerjakan</span>
                <span class="ml-auto font-display text-xl font-bold text-blue-500">
                    <?php echo e(isset($kanban['In Progress']) ? count($kanban['In Progress']) : 0); ?>

                </span>
            </div>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($kanban['In Progress'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl bg-blue-50 border border-blue-100 p-3">
                    <div class="font-semibold text-[12px] text-slate-700 truncate"><?php echo e($wo->customer?->name ?? '—'); ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5"><?php echo e($wo->vehicle?->plate_number ?? '—'); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4 text-slate-300 text-xs">Kosong</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Selesai</span>
                <span class="ml-auto font-display text-xl font-bold text-emerald-500">
                    <?php echo e(isset($kanban['Completed']) ? count($kanban['Completed']) : 0); ?>

                </span>
            </div>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($kanban['Completed'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3">
                    <div class="font-semibold text-[12px] text-slate-700 truncate"><?php echo e($wo->customer?->name ?? '—'); ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5"><?php echo e($wo->vehicle?->plate_number ?? '—'); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4 text-slate-300 text-xs">Kosong</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Lunas</span>
                <span class="ml-auto font-display text-xl font-bold text-violet-500">
                    <?php echo e(isset($kanban['Paid']) ? count($kanban['Paid']) : 0); ?>

                </span>
            </div>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($kanban['Paid'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl bg-violet-50 border border-violet-100 p-3">
                    <div class="font-semibold text-[12px] text-slate-700 truncate"><?php echo e($wo->customer?->name ?? '—'); ?></div>
                    <div class="text-[11px] text-slate-400 mt-0.5"><?php echo e($wo->vehicle?->plate_number ?? '—'); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4 text-slate-300 text-xs">Kosong</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-slate-800">Work Order Terbaru</h2>
            <p class="text-xs text-slate-400 mt-0.5">10 WO terakhir yang masuk</p>
        </div>
        <a href="<?php echo e(route('work-orders')); ?>"
           class="text-xs font-semibold text-amber-500 hover:text-amber-600 transition-colors">
            Lihat semua &rarr;
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left px-6 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">#</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Pelanggan</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 hidden md:table-cell">Kendaraan</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Status</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 hidden lg:table-cell">Mekanik</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 hidden lg:table-cell">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-6 py-3.5">
                        <span class="font-mono-jet text-[11px] text-slate-400">#<?php echo e(str_pad($i + 1, 3, '0', STR_PAD_LEFT)); ?></span>
                    </td>
                    <td class="px-6 py-3.5">
                        <div class="font-medium text-slate-700"><?php echo e($wo->customer?->name ?? '—'); ?></div>
                    </td>
                    <td class="px-6 py-3.5 hidden md:table-cell">
                        <div class="font-mono-jet text-xs bg-slate-100 text-slate-600 rounded-lg px-2 py-1 inline-block">
                            <?php echo e($wo->vehicle?->plate_number ?? '—'); ?>

                        </div>
                    </td>
                    <td class="px-6 py-3.5">
                        <?php
                            $statusMap = [
                                'Pending'     => 'bg-amber-100 text-amber-700',
                                'In Progress' => 'bg-blue-100 text-blue-700',
                                'Completed'   => 'bg-emerald-100 text-emerald-700',
                                'Paid'        => 'bg-violet-100 text-violet-700',
                            ];
                            $statusLabel = $wo->status instanceof \BackedEnum ? $wo->status->value : (string)$wo->status;
                            $badge = $statusMap[$statusLabel] ?? 'bg-slate-100 text-slate-600';
                        ?>
                        <span class="inline-block text-[11px] font-semibold px-2.5 py-1 rounded-full <?php echo e($badge); ?>">
                            <?php echo e($statusLabel); ?>

                        </span>
                    </td>
                    <td class="px-6 py-3.5 hidden lg:table-cell text-sm text-slate-500">
                        <?php echo e($wo->mechanic?->name ?? '—'); ?>

                    </td>
                    <td class="px-6 py-3.5 hidden lg:table-cell text-xs text-slate-400 font-mono-jet">
                        <?php echo e($wo->created_at?->format('d/m/Y H:i')); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="text-4xl mb-3 opacity-30">&#x1F4CB;</div>
                        <p class="font-medium text-slate-400">Belum ada Work Order</p>
                        <a href="<?php echo e(route('work-orders')); ?>"
                           class="inline-block mt-3 text-xs font-semibold text-amber-500 hover:underline">
                            Buat WO pertama &rarr;
                        </a>
                    </td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>
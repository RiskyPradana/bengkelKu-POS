<?php
$revenue = $this->revenueStats;
$woStats = $this->workOrderStatsByStatus;
$topSvc  = $this->topServices;
$topProd = $this->topProducts;

$months = $this->months;
$years  = $this->years;

$statusColors = [
    'Pending'     => 'bg-amber-100 text-amber-700',
    'In Progress' => 'bg-blue-100 text-blue-700',
    'Completed'   => 'bg-emerald-100 text-emerald-700',
    'Paid'        => 'bg-violet-100 text-violet-700',
];
?>

<div>

<div class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex items-center gap-2 bg-white rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm">
        <span class="text-slate-400 text-sm">📅</span>
        <select wire:model.live="month" class="text-sm font-semibold text-slate-700 bg-transparent border-none focus:outline-none pr-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($num); ?>"><?php echo e($name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <select wire:model.live="year" class="text-sm font-semibold text-slate-700 bg-transparent border-none focus:outline-none">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($y); ?>"><?php echo e($y); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
    </div>
    <div class="flex items-center gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button wire:click="$set('month', <?php echo e($m); ?>)"
                class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors
                       <?php echo e($month == $m ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50'); ?>">
            <?php echo e($lbl); ?>

        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>


<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 text-white rounded-2xl shadow-lg p-6 xl:col-span-2">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-3">💵 Total Omzet Periode Ini</p>
        <p class="font-display text-[42px] font-bold text-amber-400 leading-none">
            Rp <?php echo e(number_format($revenue['revenue'], 0, ',', '.')); ?>

        </p>
        <p class="text-xs text-slate-400 mt-2">
            <?php echo e($months[$month]); ?> <?php echo e($year); ?> &middot; <?php echo e($revenue['invoice_count']); ?> invoice lunas
        </p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-t-4 border-emerald-500">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Rata-rata per Invoice</p>
        <p class="font-display text-3xl font-bold text-emerald-600">
            Rp <?php echo e(number_format($revenue['avg_per_inv'], 0, ',', '.')); ?>

        </p>
        <p class="text-xs text-slate-400 mt-1"><?php echo e($revenue['invoice_count']); ?> transaksi</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-t-4 border-rose-400">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Total Diskon</p>
        <p class="font-display text-3xl font-bold text-rose-500">
            Rp <?php echo e(number_format($revenue['discount'], 0, ',', '.')); ?>

        </p>
        <p class="text-xs text-slate-400 mt-1">Pajak: Rp <?php echo e(number_format($revenue['tax'], 0, ',', '.')); ?></p>
    </div>
</div>


<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">

    
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-display text-[19px] font-bold text-slate-900">Work Order per Status</h3>
            <p class="text-[11px] text-slate-400"><?php echo e($months[$month]); ?> <?php echo e($year); ?></p>
        </div>
        <div class="p-5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($woStats->isEmpty()): ?>
            <div class="text-center py-8 text-slate-300">
                <div class="text-3xl mb-2">📋</div>
                <p class="text-sm">Belum ada data</p>
            </div>
            <?php else: ?>
            <?php $woTotal = $woStats->sum('total'); ?>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $woStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                $st  = $row->status instanceof \App\Domains\WorkOrder\Enums\WorkOrderStatus ? $row->status->value : (string)$row->status;
                $pct = $woTotal > 0 ? round(($row->total / $woTotal) * 100) : 0;
                $barColor = ['Pending'=>'bg-amber-400','In Progress'=>'bg-blue-500','Completed'=>'bg-emerald-500','Paid'=>'bg-violet-500'][$st] ?? 'bg-slate-400';
                ?>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full <?php echo e($statusColors[$st] ?? 'bg-gray-100 text-gray-600'); ?>"><?php echo e($st); ?></span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono-jet text-sm font-bold text-slate-800"><?php echo e($row->total); ?></span>
                            <span class="text-[11px] text-slate-400 ml-1.5"><?php echo e($pct); ?>%</span>
                        </div>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full <?php echo e($barColor); ?> rounded-full transition-all" style="width: <?php echo e($pct); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Total WO bulan ini</span>
                    <span class="font-mono-jet text-sm font-bold text-slate-900"><?php echo e($woTotal); ?></span>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-display text-[19px] font-bold text-slate-900">🔧 Top Jasa Servis</h3>
            <p class="text-[11px] text-slate-400"><?php echo e($months[$month]); ?> <?php echo e($year); ?></p>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topSvc; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="px-5 py-3.5 flex items-center gap-4">
                <span class="font-display text-2xl font-bold text-slate-200 w-7 shrink-0 text-center">#<?php echo e($i + 1); ?></span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-slate-900 truncate"><?php echo e($item->name); ?></p>
                    <p class="text-[11px] text-slate-400"><?php echo e($item->total_qty); ?>x dikerjakan</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-mono-jet text-sm font-bold text-emerald-600">Rp <?php echo e(number_format($item->total_revenue, 0, ',', '.')); ?></p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-10 text-slate-300 px-5">
                <div class="text-3xl mb-2">🔧</div>
                <p class="text-sm">Belum ada data jasa</p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-display text-[19px] font-bold text-slate-900">📦 Top Sparepart</h3>
            <p class="text-[11px] text-slate-400"><?php echo e($months[$month]); ?> <?php echo e($year); ?></p>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topProd; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="px-5 py-3.5 flex items-center gap-4">
                <span class="font-display text-2xl font-bold text-slate-200 w-7 shrink-0 text-center">#<?php echo e($i + 1); ?></span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-slate-900 truncate"><?php echo e($item->name); ?></p>
                    <p class="text-[11px] text-slate-400"><?php echo e($item->total_qty); ?> unit terjual</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-mono-jet text-sm font-bold text-blue-600">Rp <?php echo e(number_format($item->total_revenue, 0, ',', '.')); ?></p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-10 text-slate-300 px-5">
                <div class="text-3xl mb-2">📦</div>
                <p class="text-sm">Belum ada data sparepart</p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>


<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-display text-[20px] font-bold text-slate-900">Invoice Lunas — <?php echo e($months[$month]); ?> <?php echo e($year); ?></h3>
        <span class="text-xs text-slate-400"><?php echo e($revenue['invoice_count']); ?> invoice</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                <th class="text-left px-5 py-3">No Invoice</th>
                <th class="text-left px-5 py-3">Pelanggan</th>
                <th class="text-left px-5 py-3">Kendaraan</th>
                <th class="text-right px-5 py-3">Diskon</th>
                <th class="text-right px-5 py-3">Pajak</th>
                <th class="text-right px-5 py-3">Grand Total</th>
                <th class="text-left px-5 py-3">Tgl Bayar</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = \App\Domains\POS\Models\Invoice::with(['workOrder.customer','workOrder.vehicle'])
                ->where('status', \App\Domains\POS\Enums\InvoiceStatus::Paid)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->limit(30)
                ->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-5 py-3.5">
                    <span class="font-mono-jet text-[11px] font-bold text-slate-500 uppercase">№<?php echo e(strtoupper(substr($inv->id, -8))); ?></span>
                </td>
                <td class="px-5 py-3.5 font-medium text-slate-900">
                    <?php echo e($inv->workOrder?->customer?->name ?? '—'); ?>

                </td>
                <td class="px-5 py-3.5">
                    <span class="font-mono-jet text-xs font-bold text-slate-700">
                        <?php echo e($inv->workOrder?->vehicle?->plate_number ?? '—'); ?>

                    </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inv->discount > 0): ?>
                    <span class="font-mono-jet text-xs text-rose-500">-Rp <?php echo e(number_format($inv->discount, 0, ',', '.')); ?></span>
                    <?php else: ?>
                    <span class="text-slate-300">—</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inv->tax > 0): ?>
                    <span class="font-mono-jet text-xs text-slate-500">Rp <?php echo e(number_format($inv->tax, 0, ',', '.')); ?></span>
                    <?php else: ?>
                    <span class="text-slate-300">—</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono-jet text-sm font-bold text-emerald-600">Rp <?php echo e(number_format($inv->grand_total, 0, ',', '.')); ?></span>
                </td>
                <td class="px-5 py-3.5 text-slate-400 text-xs whitespace-nowrap">
                    <?php echo e($inv->created_at->format('d/m/Y H:i')); ?>

                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" class="text-center py-14 text-slate-400">
                    <div class="text-4xl mb-3 opacity-40">🧾</div>
                    <p class="font-medium">Belum ada invoice lunas</p>
                    <p class="text-xs mt-1">Periode <?php echo e($months[$month]); ?> <?php echo e($year); ?></p>
                </td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($revenue['invoice_count'] > 0): ?>
            <tfoot class="bg-slate-50 border-t border-slate-200">
            <tr>
                <td colspan="5" class="px-5 py-3.5 text-right font-semibold text-sm text-slate-700">Total Omzet:</td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono-jet text-base font-bold text-emerald-600">
                        Rp <?php echo e(number_format($revenue['revenue'], 0, ',', '.')); ?>

                    </span>
                </td>
                <td class="px-5 py-3.5"></td>
            </tr>
            </tfoot>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
    </div>
</div>
</div>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/livewire/reports/index.blade.php ENDPATH**/ ?>
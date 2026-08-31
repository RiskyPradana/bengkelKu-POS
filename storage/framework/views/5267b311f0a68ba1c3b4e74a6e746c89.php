<div class="p-4 space-y-4">

    <div class="bg-slate-800 rounded-2xl p-4">
        <p class="text-slate-400 text-xs">Selamat datang,</p>
        <p class="text-white font-bold text-xl"><?php echo e(auth()->user()?->name); ?></p>
        <p class="text-slate-400 text-xs mt-0.5"><?php echo e(now()->translatedFormat('l, d F Y')); ?></p>
    </div>

    
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-slate-800 rounded-2xl p-4">
            <p class="text-amber-400 text-2xl font-bold"><?php echo e($activeWoCount); ?></p>
            <p class="text-slate-400 text-xs mt-1">WO Aktif Hari Ini</p>
        </div>
        <div class="bg-slate-800 rounded-2xl p-4">
            <p class="text-green-400 text-2xl font-bold"><?php echo e($completedWoCount); ?></p>
            <p class="text-slate-400 text-xs mt-1">WO Selesai Hari Ini</p>
        </div>
    </div>

    
    <div class="bg-slate-800 rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-700">
            <p class="text-white font-semibold text-sm">&#x1F527; WO Ditugaskan ke Saya</p>
        </div>
        <div class="divide-y divide-slate-700">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $myWorkOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="/mobile/wo/<?php echo e($wo->id); ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-amber-400/10 flex items-center justify-center">
                    <span class="text-amber-400 text-lg">&#x1F697;</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate"><?php echo e($wo->vehicle?->plate_number); ?> &mdash; <?php echo e($wo->vehicle?->model_name); ?></p>
                    <p class="text-slate-400 text-xs"><?php echo e($wo->wo_number); ?></p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                    <?php echo e($wo->status === 'in_progress' ? 'bg-blue-500/20 text-blue-400' : 'bg-amber-500/20 text-amber-400'); ?>">
                    <?php echo e($wo->status === 'in_progress' ? 'On Progress' : ucfirst($wo->status)); ?>

                </span>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="px-4 py-8 text-center text-slate-500 text-sm">Tidak ada WO aktif</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-2 gap-3">
        <a href="/mobile/scanner" class="bg-amber-500 rounded-2xl p-4 flex flex-col items-center gap-2 text-white">
            <span class="text-3xl">&#x1F4F7;</span>
            <span class="text-sm font-medium">Scan Sparepart</span>
        </a>
        <a href="/mobile/wo" class="bg-slate-700 rounded-2xl p-4 flex flex-col items-center gap-2 text-white">
            <span class="text-3xl">&#x1F4CB;</span>
            <span class="text-sm font-medium">Semua WO</span>
        </a>
    </div>

</div>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/livewire/mobile/home.blade.php ENDPATH**/ ?>
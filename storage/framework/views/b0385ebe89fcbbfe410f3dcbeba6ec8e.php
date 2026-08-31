<div class="p-4 space-y-4">

    <h2 class="text-white font-bold text-lg">&#x1F4CB; Work Order Aktif</h2>

    
    <div class="flex gap-2">
        <input wire:model.live="search" type="text" placeholder="Cari WO / plat nomor..."
            class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
    </div>

    
    <div class="space-y-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-white font-semibold"><?php echo e($wo->vehicle?->plate_number); ?> &mdash; <?php echo e($wo->vehicle?->model_name); ?></p>
                    <p class="text-slate-400 text-xs mt-0.5"><?php echo e($wo->wo_number); ?> &bull; <?php echo e($wo->customer?->name); ?></p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium flex-shrink-0
                    <?php echo e($wo->status === 'in_progress' ? 'bg-blue-500/20 text-blue-400' :
                       ($wo->status === 'done' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400')); ?>">
                    <?php echo e(ucfirst(str_replace('_', ' ', $wo->status))); ?>

                </span>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wo->complaint): ?>
            <p class="text-slate-400 text-xs mt-2 line-clamp-2"><?php echo e($wo->complaint); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex items-center gap-2 mt-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wo->status === 'pending'): ?>
                <button type="button" wire:click="startWork(<?php echo e($wo->id); ?>)"
                    class="flex-1 py-2 bg-blue-500 text-white rounded-xl text-xs font-medium hover:bg-blue-600 transition-colors">
                    &#x25B6; Mulai Kerjakan
                </button>
                <?php elseif($wo->status === 'in_progress'): ?>
                <button type="button" wire:click="finishWork(<?php echo e($wo->id); ?>)"
                    class="flex-1 py-2 bg-green-500 text-white rounded-xl text-xs font-medium hover:bg-green-600 transition-colors">
                    &#x2705; Selesai
                </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="/mobile/scanner?wo=<?php echo e($wo->id); ?>" class="px-3 py-2 bg-slate-700 text-slate-300 rounded-xl text-xs font-medium">
                    &#x1F4F7; Scan
                </a>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="py-16 text-center text-slate-500 text-sm">Tidak ada WO aktif</div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="pb-2"><?php echo e($workOrders->links()); ?></div>

    
    <div x-data="{ show:false, msg:'', type:'success' }"
         x-on:notify.window="show=true; msg=$event.detail.message; type=$event.detail.type; setTimeout(()=>show=false,3000)"
         x-show="show" x-transition
         :class="type==='success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed bottom-24 right-4 left-4 text-white px-5 py-3 rounded-2xl shadow-lg text-sm font-medium z-50 text-center">
        <span x-text="msg"></span>
    </div>

</div>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/livewire/mobile/wo.blade.php ENDPATH**/ ?>
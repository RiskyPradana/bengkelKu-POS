<?php
$inputCls   = 'w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition-colors';
$selectCls  = $inputCls;
$labelCls   = 'block text-xs font-semibold text-slate-600 mb-1.5';
$errorCls   = 'text-xs text-red-500 mt-1';
$btnPrimary = 'bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2';
$btnGhost   = 'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors';

$statuses = [
    'Pending'     => ['pill' => 'bg-amber-100 text-amber-700 border-amber-200',   'dot' => 'bg-amber-400'],
    'In Progress' => ['pill' => 'bg-blue-100 text-blue-700 border-blue-200',      'dot' => 'bg-blue-500'],
    'Completed'   => ['pill' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
    'Paid'        => ['pill' => 'bg-violet-100 text-violet-700 border-violet-200', 'dot' => 'bg-violet-500'],
];

$filterTabs = [
    '' => 'Semua',
    'Pending'     => 'Pending',
    'In Progress' => 'In Progress',
    'Completed'   => 'Selesai',
    'Paid'        => 'Lunas',
];
?>

<div>

<div class="flex flex-wrap items-center gap-3 mb-5">
    
    <div class="relative flex-1 min-w-[200px] max-w-sm">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">🔍</span>
        <input wire:model.live.debounce.350ms="search"
               type="text"
               placeholder="Cari pelanggan, plat, keluhan..."
               class="<?php echo e($inputCls); ?> pl-10">
    </div>

    
    <div class="flex items-center gap-1 bg-white rounded-xl border border-slate-200 p-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filterTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button wire:click="$set('statusFilter', '<?php echo e($val); ?>')"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all
                       <?php echo e($statusFilter === $val
                            ? 'bg-slate-900 text-white shadow-sm'
                            : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'); ?>">
            <?php echo e($label); ?>

        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="flex-1"></div>

    
    <button wire:click="openCreateModal" class="<?php echo e($btnPrimary); ?>">
        <span class="text-base">+</span> Buat WO Baru
    </button>
</div>


<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                <th class="text-left px-5 py-3">ID</th>
                <th class="text-left px-5 py-3">Pelanggan</th>
                <th class="text-left px-5 py-3">Kendaraan</th>
                <th class="text-left px-5 py-3">Mekanik</th>
                <th class="text-left px-5 py-3">Keluhan</th>
                <th class="text-left px-5 py-3">Status</th>
                <th class="text-left px-5 py-3">Total</th>
                <th class="text-left px-5 py-3">Tgl Buat</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $st = $wo->status?->value ?? (string)$wo->status; ?>
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-5 py-3.5">
                    <span class="font-mono-jet text-[11px] font-bold text-slate-400 uppercase">
                        №<?php echo e(strtoupper(substr($wo->id, -6))); ?>

                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <p class="font-semibold text-slate-900 text-sm"><?php echo e($wo->customer?->name ?? '—'); ?></p>
                    <p class="text-[11px] text-slate-400"><?php echo e($wo->customer?->phone); ?></p>
                </td>
                <td class="px-5 py-3.5">
                    <p class="font-mono-jet text-xs font-bold text-slate-800"><?php echo e($wo->vehicle?->plate_number ?? '—'); ?></p>
                    <p class="text-[11px] text-slate-400"><?php echo e($wo->vehicle?->brand); ?> <?php echo e($wo->vehicle?->type); ?></p>
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-sm">
                    <?php echo e($wo->mechanic?->name ?? '—'); ?>

                </td>
                <td class="px-5 py-3.5 max-w-[160px]">
                    <p class="text-slate-600 text-xs line-clamp-2"><?php echo e($wo->complaint ?? '—'); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wo->odometer): ?>
                    <p class="text-[10.5px] text-slate-400 mt-0.5">KM: <?php echo e(number_format($wo->odometer, 0, ',', '.')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="px-5 py-3.5">
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full border
                                <?php echo e(($statuses[$st] ?? [])['pill'] ?? 'bg-gray-100 text-gray-500 border-gray-200'); ?>">
                        <?php echo e($st); ?>

                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wo->invoice?->grand_total): ?>
                    <span class="font-mono-jet text-xs font-bold text-emerald-600">
                        Rp <?php echo e(number_format($wo->invoice->grand_total, 0, ',', '.')); ?>

                    </span>
                    <?php else: ?>
                    <span class="text-slate-300 text-xs">—</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="px-5 py-3.5 text-slate-400 text-xs whitespace-nowrap">
                    <?php echo e($wo->created_at->format('d/m/Y')); ?>

                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-2">
                        <button wire:click="openEditModal('<?php echo e($wo->id); ?>')"
                                class="text-slate-500 hover:text-amber-600 transition-colors p-1 rounded-lg hover:bg-amber-50"
                                title="Edit">
                            ✏️
                        </button>
                        <button wire:click="deleteWorkOrder('<?php echo e($wo->id); ?>')"
                                wire:confirm="Yakin ingin menghapus Work Order ini? Tindakan ini tidak bisa dibatalkan."
                                class="text-slate-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50"
                                title="Hapus">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="9" class="text-center py-16 text-slate-400">
                    <div class="text-4xl mb-3 opacity-40">📋</div>
                    <p class="font-medium text-slate-500 mb-1">Belum ada Work Order</p>
                    <p class="text-xs text-slate-400 mb-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $statusFilter): ?>
                            Tidak ada WO yang cocok dengan filter ini
                        <?php else: ?>
                            Klik "Buat WO Baru" untuk memulai
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $statusFilter): ?>
                    <button wire:click="$set('search',''); $set('statusFilter','')"
                            class="text-amber-600 hover:underline text-sm">
                        Reset filter
                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$this->workOrders->isEmpty()): ?>
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100">
        <p class="text-xs text-slate-400">Menampilkan <span class="font-semibold text-slate-600"><?php echo e($this->workOrders->count()); ?></span> Work Order</p>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
<div class="fixed inset-0 z-50 flex items-center justify-center" wire:key="wo-modal">
    
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
         wire:click="$set('showModal', false)"></div>

    
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-4 z-10 flex flex-col max-h-[90vh]"
         x-data x-trap.noscroll="true">

        
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-display text-[22px] font-bold text-slate-900">
                    <?php echo e($isEditing ? 'Edit Work Order' : 'Buat Work Order Baru'); ?>

                </h3>
                <p class="text-xs text-slate-400"><?php echo e($isEditing ? 'Perbarui data SPK servis' : 'Isi data untuk membuat SPK servis baru'); ?></p>
            </div>
            <button wire:click="$set('showModal', false)"
                    class="text-slate-400 hover:text-slate-700 transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-xl">
                ×
            </button>
        </div>

        
        <div class="px-6 py-5 overflow-y-auto space-y-4">

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="<?php echo e($labelCls); ?>">Pelanggan <span class="text-red-500">*</span></label>
                    <select wire:model.live="customerId" class="<?php echo e($selectCls); ?>">
                        <option value="">— Pilih Pelanggan —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->name); ?> (<?php echo e($customer->phone); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['customerId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="<?php echo e($errorCls); ?>"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label class="<?php echo e($labelCls); ?>">Kendaraan <span class="text-red-500">*</span></label>
                    <select wire:model="vehicleId" class="<?php echo e($selectCls); ?>" <?php if(!$customerId): ?> disabled <?php endif; ?>>
                        <option value="">— Pilih Kendaraan —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vehicle->id); ?>"><?php echo e($vehicle->plate_number); ?> — <?php echo e($vehicle->brand); ?> <?php echo e($vehicle->type); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$customerId): ?>
                    <p class="text-[11px] text-slate-400 mt-1">Pilih pelanggan terlebih dahulu</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['vehicleId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="<?php echo e($errorCls); ?>"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="<?php echo e($labelCls); ?>">Status</label>
                    <select wire:model="status" class="<?php echo e($selectCls); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>"><?php echo e($s); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="<?php echo e($labelCls); ?>">Odometer (km)</label>
                    <input wire:model="odometer" type="number" min="0"
                           placeholder="Contoh: 45000" class="<?php echo e($inputCls); ?>">
                </div>
            </div>

            
            <div>
                <label class="<?php echo e($labelCls); ?>">Deskripsi Keluhan <span class="text-red-500">*</span></label>
                <textarea wire:model="complaint" rows="3"
                          placeholder="Jelaskan keluhan / kerusakan kendaraan pelanggan..."
                          class="<?php echo e($inputCls); ?> resize-none"></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['complaint'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="<?php echo e($errorCls); ?>"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div>
                <label class="<?php echo e($labelCls); ?>">Mekanik yang Ditugaskan</label>
                <select wire:model="mechanicId" class="<?php echo e($selectCls); ?>">
                    <option value="">— Belum ditugaskan —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->mechanics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mechanic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($mechanic->id); ?>"><?php echo e($mechanic->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        </div>

        
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between shrink-0">
            <button wire:click="$set('showModal', false)" class="<?php echo e($btnGhost); ?>">Batal</button>
            <button wire:click="saveWorkOrder" wire:loading.attr="disabled"
                    class="<?php echo e($btnPrimary); ?>">
                <span wire:loading.remove wire:target="saveWorkOrder">💾 Simpan</span>
                <span wire:loading wire:target="saveWorkOrder">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/livewire/work-order/index.blade.php ENDPATH**/ ?>
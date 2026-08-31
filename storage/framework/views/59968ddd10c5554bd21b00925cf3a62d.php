<div class="p-6 space-y-6">

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">&#x1F4E6; Stok & Inventori</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola stok multi-cabang & transfer barang</p>
        </div>
    </div>

    
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['stock'=>'&#x1F4E6; Stok Cabang','movements'=>'&#x1F4CA; Riwayat','transfers'=>'&#x21C4; Transfer','alerts'=>'&#x26A0; Peringatan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button type="button" wire:click="$set('tab','<?php echo e($key); ?>')"
            class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors
                <?php echo e($tab === $key ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'); ?>">
            <?php echo $label; ?>

        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'stock'): ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-3">
            <input wire:model.live="search" type="text" placeholder="Cari produk..."
                class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            <select wire:model.live="filterBranch"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <option value="">Semua Cabang</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left">SKU</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3 text-center">Min. Stok</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-800"><?php echo e($stock->product->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs"><?php echo e($stock->product->sku ?? '-'); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?php echo e($stock->branch->name ?? 'Pusat'); ?></td>
                        <td class="px-4 py-3 text-center font-bold <?php echo e($stock->quantity <= $stock->min_stock ? 'text-red-600' : 'text-slate-800'); ?>">
                            <?php echo e($stock->quantity); ?>

                        </td>
                        <td class="px-4 py-3 text-center text-slate-500"><?php echo e($stock->min_stock); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stock->quantity <= $stock->min_stock): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-600 rounded-full text-xs font-medium">Stok Rendah</span>
                            <?php elseif($stock->quantity <= $stock->min_stock * 1.5): ?>
                            <span class="px-2 py-1 bg-amber-100 text-amber-600 rounded-full text-xs font-medium">Hampir Habis</span>
                            <?php else: ?>
                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-medium">Normal</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" wire:click="openAdjust('<?php echo e($stock->product_id); ?>', '<?php echo e($stock->branch_id); ?>')"
                                class="px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-200 transition-colors">
                                Adjust
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Tidak ada data stok</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'movements'): ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <select wire:model.live="filterMovementType"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <option value="">Semua Tipe</option>
                <option value="in">Masuk</option>
                <option value="out">Keluar</option>
                <option value="transfer_in">Transfer Masuk</option>
                <option value="transfer_out">Transfer Keluar</option>
                <option value="adjustment">Penyesuaian</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500 text-xs"><?php echo e($m->created_at->format('d M Y H:i')); ?></td>
                        <td class="px-4 py-3 font-medium text-slate-800"><?php echo e($m->product->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-slate-600"><?php echo e($m->branch->name ?? 'Pusat'); ?></td>
                        <td class="px-4 py-3">
                            <?php $badges=['in'=>'bg-green-100 text-green-700','out'=>'bg-red-100 text-red-700','transfer_in'=>'bg-blue-100 text-blue-700','transfer_out'=>'bg-orange-100 text-orange-700','adjustment'=>'bg-purple-100 text-purple-700']; ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo e($badges[$m->type] ?? 'bg-slate-100 text-slate-600'); ?>"><?php echo e(ucfirst(str_replace('_',' ',$m->type))); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold <?php echo e(in_array($m->type,['in','transfer_in']) ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e(in_array($m->type,['in','transfer_in']) ? '+' : '-'); ?><?php echo e($m->quantity); ?></td>
                        <td class="px-4 py-3 text-slate-500 text-xs"><?php echo e($m->reference_id ?? '-'); ?></td>
                        <td class="px-4 py-3 text-slate-500 text-xs"><?php echo e(Str::limit($m->notes,40)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4"><?php echo e($movements->links()); ?></div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'transfers'): ?>
    <div class="space-y-4">
        <button type="button" wire:click="openTransfer"
            class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors shadow-sm">
            &#x2795; Buat Transfer Baru
        </button>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">No. Transfer</th>
                            <th class="px-4 py-3 text-left">Dari</th>
                            <th class="px-4 py-3 text-left">Ke</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-amber-600"><?php echo e($t->transfer_number); ?></td>
                            <td class="px-4 py-3 text-slate-700"><?php echo e($t->fromBranch->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-slate-700"><?php echo e($t->toBranch->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-slate-500 text-xs"><?php echo e($t->transferred_at?->format('d M Y') ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php $sc=['pending'=>'bg-slate-100 text-slate-600','in_transit'=>'bg-blue-100 text-blue-700','received'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-600']; ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo e($sc[$t->status] ?? 'bg-slate-100 text-slate-500'); ?>"><?php echo e(ucfirst($t->status)); ?></span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs"><?php echo e(Str::limit($t->notes,40)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada transfer</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'alerts'): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lowStockItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-2xl">&#x26A0;</div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800 truncate"><?php echo e($item->product->name); ?></p>
                <p class="text-xs text-slate-500"><?php echo e($item->branch->name ?? 'Pusat'); ?></p>
                <p class="text-sm mt-1">
                    <span class="text-red-600 font-bold"><?php echo e($item->quantity); ?></span>
                    <span class="text-slate-400"> / min <?php echo e($item->min_stock); ?></span>
                </p>
            </div>
            <button type="button" wire:click="openAdjust(<?php echo e($item->id); ?>)"
                class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-medium hover:bg-amber-600 transition-colors">
                Isi
            </button>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-3 py-16 text-center">
            <p class="text-5xl mb-3">&#x2705;</p>
            <p class="text-slate-500">Semua stok dalam kondisi normal!</p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAdjustModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showAdjustModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">&#x1F4CB; Penyesuaian Stok</h2>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($adjustStock): ?>
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="font-medium text-slate-700"><?php echo e($adjustStock->product->name); ?></p>
                <p class="text-sm text-slate-500"><?php echo e($adjustStock->branch->name ?? 'Pusat'); ?> &bull; Stok saat ini: <strong><?php echo e($adjustStock->quantity); ?></strong></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Tipe Penyesuaian</label>
                    <select wire:model="adjType" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="in">Stok Masuk (+)</option>
                        <option value="out">Stok Keluar (-)</option>
                        <option value="adjustment">Koreksi (set langsung)</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Jumlah</label>
                    <input wire:model="adjQty" type="number" min="1"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['adjQty'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Catatan</label>
                    <textarea wire:model="adjNotes" rows="2" placeholder="Alasan penyesuaian..."
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showAdjustModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="saveAdjust" class="flex-1 py-2 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Simpan</button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTransferModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showTransferModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-slate-800">&#x21C4; Transfer Stok Antar Cabang</h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Dari Cabang</label>
                    <select wire:model="trfFromBranch" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['trfFromBranch'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Ke Cabang</label>
                    <select wire:model="trfToBranch" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['trfToBranch'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Catatan</label>
                <input wire:model="trfNotes" type="text" placeholder="Opsional..."
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            </div>
            
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700">Item Transfer</p>
                    <button type="button" wire:click="addTransferItem" class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Tambah Item</button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $trfItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex gap-2 items-center">
                    <select wire:model="trfItems.<?php echo e($idx); ?>.product_id" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih Produk --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <input wire:model="trfItems.<?php echo e($idx); ?>.qty" type="number" min="1" placeholder="Qty" class="w-20 border border-slate-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none text-center">
                    <button type="button" wire:click="removeTransferItem(<?php echo e($idx); ?>)" class="text-red-400 hover:text-red-600">&#x2715;</button>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showTransferModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="saveTransfer" class="flex-1 py-2 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Buat Transfer</button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-data="{ show:false, msg:'', type:'success' }"
         x-on:notify.window="show=true; msg=$event.detail.message; type=$event.detail.type; setTimeout(()=>show=false,3000)"
         x-show="show" x-transition
         :class="type==='success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed bottom-6 right-6 text-white px-5 py-3 rounded-2xl shadow-lg text-sm font-medium z-50">
        <span x-text="msg"></span>
    </div>

</div>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/livewire/inventory/index.blade.php ENDPATH**/ ?>
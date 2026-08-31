<div class="w-full">

    
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-amber-400 text-3xl shadow-lg mb-4">
            🔧
        </div>
        <h1 class="font-display text-[36px] font-bold text-white leading-none">BengkelOS</h1>
        <p class="text-slate-400 text-sm mt-1">Sistem Manajemen Bengkel</p>
    </div>

    
    <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 p-8">

        <h2 class="font-display text-[24px] font-bold text-white mb-1">Masuk ke Akun</h2>
        <p class="text-slate-400 text-sm mb-7">Masukkan email dan password Anda</p>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
        <div class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 rounded-xl px-4 py-3 text-sm mb-5">
            <?php echo e(session('status')); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="space-y-5">

            
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">
                    Email
                </label>
                <input wire:model="email"
                       type="email"
                       placeholder="admin@bengkel.com"
                       autocomplete="email"
                       wire:keydown.enter="login"
                       class="w-full bg-slate-700 border <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-slate-600 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                              rounded-xl px-4 py-3 text-white text-sm placeholder-slate-500
                              focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400
                              transition-colors">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                    <span>⚠️</span> <?php echo e($message); ?>

                </p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div>
                <label class="block text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">
                    Password
                </label>
                <input wire:model="password"
                       type="password"
                       placeholder="••••••••"
                       autocomplete="current-password"
                       wire:keydown.enter="login"
                       class="w-full bg-slate-700 border <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php else: ?> border-slate-600 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                              rounded-xl px-4 py-3 text-white text-sm placeholder-slate-500
                              focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400
                              transition-colors">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
                    <span>⚠️</span> <?php echo e($message); ?>

                </p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="flex items-center gap-2.5">
                <button type="button" wire:click="$toggle('remember')"
                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors
                               <?php echo e($remember ? 'bg-amber-400' : 'bg-slate-600'); ?>">
                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform
                                 <?php echo e($remember ? 'translate-x-[18px]' : 'translate-x-0.5'); ?>"></span>
                </button>
                <span class="text-sm text-slate-400">Ingat saya</span>
            </div>

            
            <button wire:click="login"
                    wire:loading.attr="disabled"
                    class="w-full bg-amber-400 hover:bg-amber-500 disabled:opacity-60
                           text-slate-900 font-bold py-3.5 rounded-xl text-sm
                           transition-all shadow-lg shadow-amber-400/20
                           flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="login">
                    🔓 Masuk
                </span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </div>

    
    <p class="text-center text-slate-600 text-xs mt-6">
        &copy; <?php echo e(date('Y')); ?> BengkelOS &mdash; Sistem Manajemen Bengkel
    </p>
</div>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/livewire/auth/login.blade.php ENDPATH**/ ?>
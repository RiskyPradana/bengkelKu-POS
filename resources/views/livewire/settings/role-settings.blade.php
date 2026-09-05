<div class="p-4 space-y-5 md:p-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Role & Hak Akses</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Atur role karyawan dan menu apa saja yang bisa mereka akses — tanpa perlu edit kode.</p>
        </div>

        <button wire:click="openCreate"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Role
        </button>
    </div>

    @if (session('sukses'))
        <div class="flex items-start gap-3 p-4 text-sm border rounded-lg bg-emerald-50 border-emerald-200 text-emerald-800">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if (session('gagal'))
        <div class="flex items-start gap-3 p-4 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('gagal') }}</span>
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($roles as $role)
            <div class="p-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-{{ $role->color }}-50 text-{{ $role->color }}-700 shrink-0">
                            {{ $role->label }}
                        </span>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-300">{{ $role->description ?: 'Tidak ada deskripsi.' }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-gray-400 dark:text-slate-500">
                                <span>Level {{ $role->level }}</span>
                                @if ($role->is_default)
                                    <span class="px-1.5 py-0.5 font-medium text-blue-700 bg-blue-50 rounded">Default user baru</span>
                                @endif
                                @if ($role->is_system)
                                    <span class="px-1.5 py-0.5 font-medium text-gray-600 bg-gray-100 rounded">Role sistem</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @forelse (($role->access ?? []) as $routeName)
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 rounded">
                                        {{ $manageableRoutes[$routeName] ?? $routeName }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 dark:text-slate-500">Belum ada menu yang diizinkan.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        @unless ($role->is_default)
                            <button wire:click="makeDefault('{{ $role->key }}')"
                                    class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">
                                Jadikan Default
                            </button>
                        @endunless

                        <button wire:click="openEdit('{{ $role->key }}')"
                                class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">
                            Ubah
                        </button>

                        @unless ($role->is_system)
                            <button wire:click="deleteRole('{{ $role->key }}')"
                                    wire:confirm="Hapus role {{ $role->label }}?"
                                    class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">
                                Hapus
                            </button>
                        @endunless
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 bg-black/50 sm:items-center sm:p-4" wire:key="modal-role">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-t-2xl sm:rounded-2xl max-h-[92vh] overflow-y-auto">

                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $editingKey ? 'Ubah Role' : 'Tambah Role Baru' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="p-1 text-gray-400 dark:text-slate-500 rounded hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">
                            Kode Role <span class="text-gray-400 dark:text-slate-500">(huruf kecil, tanpa spasi)</span>
                        </label>
                        <input type="text" wire:model="key" placeholder="contoh: supervisor" @if($editingKey) disabled @endif
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 dark:disabled:bg-slate-800">
                        @error('key') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nama Role</label>
                        <input type="text" wire:model="label" placeholder="contoh: Supervisor Cabang"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Deskripsi <span class="text-gray-400 dark:text-slate-500">(opsional)</span></label>
                        <textarea wire:model="description" rows="2"
                                  class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Warna Badge</label>
                            <select wire:model="color" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                @foreach (['purple','blue','emerald','amber','orange','pink','red','gray'] as $c)
                                    <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Level</label>
                            <input type="number" min="0" max="100" wire:model="level"
                                   class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-slate-200">Menu yang bisa diakses</label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($manageableRoutes as $routeName => $menuLabel)
                                <label class="flex items-center gap-2 p-2 text-sm border rounded-lg cursor-pointer border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                    <input type="checkbox" wire:model="access" value="{{ $routeName }}"
                                           class="text-blue-600 border-gray-300 dark:border-slate-700 rounded focus:ring-blue-500">
                                    <span class="text-gray-700 dark:text-slate-200">{{ $menuLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">{{ $editingKey ? 'Simpan Perubahan' : 'Tambah Role' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="p-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <p class="text-xs text-gray-400 dark:text-slate-500">
            Perubahan di halaman ini langsung berlaku ke menu sidebar semua user, tanpa perlu deploy ulang kode.
        </p>
    </div>
</div>

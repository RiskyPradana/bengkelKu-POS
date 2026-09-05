<div class="p-4 space-y-5 md:p-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Cabang</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Kelola daftar cabang bengkel beserta alamat dan kontaknya.</p>
        </div>

        <button wire:click="openCreate"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Cabang
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

    <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                    <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                        <th class="px-4 py-3 font-semibold">Nama Cabang</th>
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Kontak</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse ($branches as $b)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 {{ $b->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $b->name }}</p>
                                @if ($b->address)
                                    <p class="text-xs text-gray-500 dark:text-slate-400">{{ $b->address }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-mono font-semibold bg-gray-100 dark:bg-slate-800 rounded">{{ $b->code }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">
                                {{ $b->phone ?: '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($b->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEdit('{{ $b->id }}')"
                                            class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>

                                    <button wire:click="toggleActive('{{ $b->id }}')"
                                            class="px-2.5 py-1.5 text-xs font-medium rounded-md {{ $b->is_active ? 'text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                        {{ $b->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>

                                    <button wire:click="deleteBranch('{{ $b->id }}')"
                                            wire:confirm="Hapus cabang {{ $b->name }}? Kalau cabang pernah dipakai transaksi/stok, cabang hanya akan dinonaktifkan."
                                            class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada cabang. Klik "Tambah Cabang" untuk mulai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($branches->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-800">{{ $branches->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 bg-black/50 sm:items-center sm:p-4" wire:key="modal-branch">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-t-2xl sm:rounded-2xl max-h-[92vh] overflow-y-auto">

                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $editingId ? 'Ubah Cabang' : 'Tambah Cabang Baru' }}
                    </h2>
                    <button wire:click="$set('showModal', false)" class="p-1 text-gray-400 dark:text-slate-500 rounded hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nama Cabang</label>
                        <input type="text" wire:model="name" placeholder="contoh: Cabang Kuta"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Kode Cabang</label>
                        <input type="text" wire:model="code" placeholder="contoh: KTA"
                               class="w-full text-sm uppercase border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Alamat <span class="text-gray-400 dark:text-slate-500">(opsional)</span></label>
                        <textarea wire:model="address" rows="2"
                                  class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Telepon <span class="text-gray-400 dark:text-slate-500">(opsional)</span></label>
                        <input type="text" wire:model="phone" placeholder="0361xxxxxxx"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2.5 pt-1">
                        <input type="checkbox" wire:model="isActive"
                               class="text-blue-600 border-gray-300 dark:border-slate-700 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-slate-200">Cabang aktif</span>
                    </label>

                    <div class="flex gap-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Simpan Perubahan' : 'Tambah Cabang' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

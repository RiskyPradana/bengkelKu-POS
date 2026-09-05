<div class="p-4 space-y-5 md:p-6" x-data="{ toast: null }" x-on:notify.window="toast = $event.detail; setTimeout(() => toast = null, 3500)">

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Voucher &amp; Diskon</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Kode voucher di sini bisa langsung diterapkan kasir di halaman Kasir.</p>
    </div>

    <div x-show="toast" x-transition style="display:none"
         class="p-3 text-sm border rounded-lg bg-emerald-50 border-emerald-200 text-emerald-800">
        <span x-text="toast?.message"></span>
    </div>

    <div class="grid gap-4 lg:grid-cols-[360px_1fr]">
        <form wire:submit="saveVoucher" class="p-4 space-y-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl h-fit">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $voucherId ? 'Ubah Voucher' : 'Tambah Voucher' }}</h3>

            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Kode Voucher</label>
                <input type="text" wire:model="code" placeholder="contoh: HEMAT10" style="text-transform:uppercase"
                       class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Tipe</label>
                    <select wire:model="type" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        <option value="percent">Persen (%)</option>
                        <option value="fixed">Nominal (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Nilai</label>
                    <input type="number" step="0.01" min="0" wire:model="value" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    @error('value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Min. Belanja (Rp)</label>
                    <input type="number" step="1" min="0" wire:model="minPurchase" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Maks. Diskon (Rp) <span class="text-gray-400">opsional</span></label>
                    <input type="number" step="1" min="0" wire:model="maxDiscount" placeholder="tanpa batas" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Kuota Pemakaian <span class="text-gray-400">opsional</span></label>
                <input type="number" step="1" min="0" wire:model="usageLimit" placeholder="tanpa batas" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Berlaku Dari</label>
                    <input type="date" wire:model="validFrom" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Berlaku Sampai</label>
                    <input type="date" wire:model="validUntil" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="isActive" class="text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm text-gray-700 dark:text-slate-200">Aktif</span>
            </label>

            <div class="flex gap-2 pt-2">
                @if ($voucherId)
                    <button type="button" wire:click="openCreate" class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                @endif
                <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>

        <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                        <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                            <th class="px-4 py-3 font-semibold">Kode</th>
                            <th class="px-4 py-3 font-semibold">Nilai</th>
                            <th class="px-4 py-3 font-semibold">Min. Belanja</th>
                            <th class="px-4 py-3 font-semibold">Terpakai</th>
                            <th class="px-4 py-3 font-semibold">Berlaku</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @forelse ($this->vouchers as $v)
                            <tr class="{{ $v->is_active ? '' : 'opacity-60' }}">
                                <td class="px-4 py-3 font-mono font-bold text-gray-900 dark:text-white">{{ $v->code }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-slate-200">
                                    {{ $v->type === 'percent' ? number_format($v->value,1).'%' : 'Rp '.number_format($v->value,0,',','.') }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-slate-400">Rp {{ number_format($v->min_purchase,0,',','.') }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-slate-400">{{ $v->used_count }}{{ $v->usage_limit ? ' / '.$v->usage_limit : '' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                                    @if ($v->valid_from || $v->valid_until)
                                        {{ $v->valid_from?->format('d/m/y') ?? '...' }} &ndash; {{ $v->valid_until?->format('d/m/y') ?? '...' }}
                                    @else
                                        Selalu
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $v->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button wire:click="editVoucher('{{ $v->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>
                                    <button wire:click="toggleVoucher('{{ $v->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-slate-300 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">{{ $v->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    <button wire:click="deleteVoucher('{{ $v->id }}')" wire:confirm="Hapus voucher {{ $v->code }}?" class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-slate-400">Belum ada voucher. Tambahkan lewat form di kiri.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

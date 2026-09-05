<div class="p-4 space-y-5 md:p-6" x-data="{ toast: null }" x-on:notify.window="toast = $event.detail; setTimeout(() => toast = null, 3500)">

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Master Data</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Merek, Satuan, dan Rak kini berdiri sendiri &mdash; dinamis dan bisa dikelola langsung dari sini.</p>
    </div>

    {{-- Toast --}}
    <div x-show="toast" x-transition style="display:none"
         class="p-3 text-sm border rounded-lg"
         :class="toast?.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-sky-50 border-sky-200 text-sky-800'">
        <span x-text="toast?.message"></span>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-slate-800 rounded-xl w-fit">
        <button wire:click="switchTab('brand')"
                @class(['px-4 py-2 text-sm font-semibold rounded-lg transition-colors', 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm' => $tab === 'brand', 'text-gray-500 dark:text-slate-400' => $tab !== 'brand'])>
            Merek ({{ $this->brands->count() }})
        </button>
        <button wire:click="switchTab('unit')"
                @class(['px-4 py-2 text-sm font-semibold rounded-lg transition-colors', 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm' => $tab === 'unit', 'text-gray-500 dark:text-slate-400' => $tab !== 'unit'])>
            Satuan ({{ $this->units->count() }})
        </button>
        <button wire:click="switchTab('rack')"
                @class(['px-4 py-2 text-sm font-semibold rounded-lg transition-colors', 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm' => $tab === 'rack', 'text-gray-500 dark:text-slate-400' => $tab !== 'rack'])>
            Rak ({{ $this->racks->count() }})
        </button>
    </div>

    {{-- ═══════════════ MEREK ═══════════════ --}}
    @if ($tab === 'brand')
    <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
        <form wire:submit="saveBrand" class="p-4 space-y-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl h-fit">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $brandId ? 'Ubah Merek' : 'Tambah Merek' }}</h3>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Nama Merek</label>
                <input type="text" wire:model="brandName" placeholder="contoh: Wuling, Yamaha, HELIX"
                       class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                @error('brandName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="brandActive" class="text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm text-gray-700 dark:text-slate-200">Aktif</span>
            </label>
            <div class="flex gap-2 pt-2">
                @if ($brandId)
                    <button type="button" wire:click="resetBrandForm" class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                @endif
                <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>

        <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                    <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                        <th class="px-4 py-3 font-semibold">Nama Merek</th>
                        <th class="px-4 py-3 font-semibold">Jumlah Produk</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse ($this->brands as $b)
                        <tr class="{{ $b->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $b->name }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400">{{ $b->products_count }}</td>
                            <td class="px-4 py-3">{{ $b->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="editBrand('{{ $b->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>
                                <button wire:click="toggleBrand('{{ $b->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-slate-300 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">{{ $b->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                <button wire:click="deleteBrand('{{ $b->id }}')" wire:confirm="Hapus merek {{ $b->name }}?" class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-slate-400">Belum ada merek. Tambahkan lewat form di kiri, atau lewat menu Import dari iPos 5.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══════════════ SATUAN ═══════════════ --}}
    @if ($tab === 'unit')
    <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
        <form wire:submit="saveUnit" class="p-4 space-y-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl h-fit">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $unitId ? 'Ubah Satuan' : 'Tambah Satuan' }}</h3>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Nama Satuan</label>
                <input type="text" wire:model="unitName" placeholder="contoh: PCS, UNIT, BOX, LITER"
                       class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                @error('unitName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Singkatan <span class="text-gray-400">(opsional)</span></label>
                <input type="text" wire:model="unitAbbr" placeholder="contoh: pcs, unit"
                       class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="unitActive" class="text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm text-gray-700 dark:text-slate-200">Aktif</span>
            </label>
            <div class="flex gap-2 pt-2">
                @if ($unitId)
                    <button type="button" wire:click="resetUnitForm" class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                @endif
                <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>

        <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                    <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                        <th class="px-4 py-3 font-semibold">Nama Satuan</th>
                        <th class="px-4 py-3 font-semibold">Singkatan</th>
                        <th class="px-4 py-3 font-semibold">Jumlah Produk</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse ($this->units as $u)
                        <tr class="{{ $u->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400">{{ $u->abbreviation ?: '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400">{{ $u->products_count }}</td>
                            <td class="px-4 py-3">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="editUnit('{{ $u->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>
                                <button wire:click="toggleUnit('{{ $u->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-slate-300 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                <button wire:click="deleteUnit('{{ $u->id }}')" wire:confirm="Hapus satuan {{ $u->name }}?" class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-slate-400">Belum ada satuan. Tambahkan lewat form di kiri, atau lewat menu Import dari iPos 5.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══════════════ RAK ═══════════════ --}}
    @if ($tab === 'rack')
    <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
        <form wire:submit="saveRack" class="p-4 space-y-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl h-fit">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $rackId ? 'Ubah Rak' : 'Tambah Rak' }}</h3>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Nama Rak / Lokasi</label>
                <input type="text" wire:model="rackName" placeholder="contoh: RAK BESI 1, ETALASE 1"
                       class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                @error('rackName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-slate-300">Cabang</label>
                <select wire:model="rackBranchId" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua cabang</option>
                    @foreach ($this->branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="rackActive" class="text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm text-gray-700 dark:text-slate-200">Aktif</span>
            </label>
            <div class="flex gap-2 pt-2">
                @if ($rackId)
                    <button type="button" wire:click="resetRackForm" class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                @endif
                <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>

        <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                    <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                        <th class="px-4 py-3 font-semibold">Nama Rak</th>
                        <th class="px-4 py-3 font-semibold">Cabang</th>
                        <th class="px-4 py-3 font-semibold">Jumlah Stok Tersimpan</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse ($this->racks as $r)
                        <tr class="{{ $r->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $r->name }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400">{{ $r->branch?->name ?? 'Semua cabang' }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400">{{ $r->branch_stocks_count }}</td>
                            <td class="px-4 py-3">{{ $r->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="editRack('{{ $r->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>
                                <button wire:click="toggleRack('{{ $r->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-slate-300 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">{{ $r->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                <button wire:click="deleteRack('{{ $r->id }}')" wire:confirm="Hapus rak {{ $r->name }}?" class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-slate-400">Belum ada rak. Tambahkan lewat form di kiri, atau lewat menu Import dari iPos 5.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

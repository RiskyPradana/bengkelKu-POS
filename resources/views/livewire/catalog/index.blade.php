<div class="p-4 space-y-5 md:p-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Katalog</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Master data sparepart dan jasa servis bengkel.</p>
        </div>
        <div class="flex gap-2">
            @if ($this->isOwner)
                <button wire:click="openMarginSettings"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100">
                    &#x2696;&#xFE0F; Margin per Kategori
                </button>
            @endif
            @if ($tab === 'product')
                <button wire:click="openCreateProduct"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
                    + Tambah Sparepart
                </button>
            @else
                <button wire:click="openCreateService"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
                    + Tambah Jasa
                </button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-3 text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg">{{ session('message') }}</div>
    @endif

    @unless ($this->isOwner)
        <div class="flex items-start gap-3 p-4 text-sm border rounded-lg bg-slate-50 border-slate-200 text-slate-600">
            <span class="text-lg">&#x1F512;</span>
            <span>Harga pokok (harga beli) dan pengaturan margin hanya bisa dilihat &amp; diubah oleh akun <b>Owner</b>.</span>
        </div>
    @endunless

    {{-- Tabs --}}
    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-slate-800 rounded-xl w-fit">
        <button wire:click="switchTab('product')"
                @class(['px-4 py-2 text-sm font-semibold rounded-lg transition-colors', 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm' => $tab === 'product', 'text-gray-500 dark:text-slate-400' => $tab !== 'product'])>
            Sparepart ({{ $this->productCount }})
        </button>
        <button wire:click="switchTab('service')"
                @class(['px-4 py-2 text-sm font-semibold rounded-lg transition-colors', 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm' => $tab === 'service', 'text-gray-500 dark:text-slate-400' => $tab !== 'service'])>
            Jasa Servis ({{ $this->serviceCount }})
        </button>
    </div>

    <div class="relative max-w-md">
        <svg class="absolute w-5 h-5 text-gray-400 left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" wire:model.live.debounce.400ms="search"
               placeholder="Cari {{ $tab === 'product' ? 'sparepart' : 'jasa' }}..."
               class="w-full py-2 pl-10 pr-3 text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
    </div>

    {{-- Tabel Sparepart --}}
    @if ($tab === 'product')
    <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                    <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">Kategori</th>
                        <th class="px-4 py-3 font-semibold">SKU / Barcode</th>
                        @if ($this->isOwner)
                            <th class="px-4 py-3 font-semibold text-right">Harga Beli</th>
                            <th class="px-4 py-3 font-semibold text-right">Margin</th>
                        @endif
                        <th class="px-4 py-3 font-semibold text-right">Harga Jual</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse ($this->products as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 {{ $p->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $p->name }}</td>
                            <td class="px-4 py-3">
                                @if ($p->category)
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $p->category }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400">
                                {{ $p->sku ?: '-' }} @if($p->barcode) &bull; {{ $p->barcode }} @endif
                            </td>
                            @if ($this->isOwner)
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-300">Rp {{ number_format($p->cost_price ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-300">{{ $p->margin_percent !== null ? number_format($p->margin_percent, 1) . '%' : '-' }}</td>
                            @endif
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($p->sell_price ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if ($p->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-slate-400"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openHistory('{{ $p->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800" title="Riwayat harga">Riwayat</button>
                                    <button wire:click="openEditProduct('{{ $p->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>
                                    <button wire:click="toggleProduct('{{ $p->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-slate-300 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">{{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    <button wire:click="deleteProduct('{{ $p->id }}')" wire:confirm="Hapus {{ $p->name }}?" class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-slate-400">Belum ada sparepart. Klik "Tambah Sparepart" untuk mulai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    {{-- Tabel Jasa --}}
    <div class="overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-800">
                    <tr class="text-xs tracking-wider text-left text-gray-500 dark:text-slate-400 uppercase">
                        <th class="px-4 py-3 font-semibold">Kode</th>
                        <th class="px-4 py-3 font-semibold">Nama Jasa</th>
                        <th class="px-4 py-3 font-semibold text-right">Harga</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse ($this->serviceItems as $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 {{ $s->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400">{{ $s->code ?: '-' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($s->price ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if ($s->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-slate-400"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEditService('{{ $s->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-slate-200 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>
                                    <button wire:click="toggleService('{{ $s->id }}')" class="px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-slate-300 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800">{{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    <button wire:click="deleteService('{{ $s->id }}')" wire:confirm="Hapus {{ $s->name }}?" class="px-2.5 py-1.5 text-xs font-medium text-red-600 rounded-md hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-slate-400">Belum ada jasa servis. Klik "Tambah Jasa" untuk mulai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Modal Sparepart --}}
    @if ($showProductModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 bg-black/50 sm:items-center sm:p-4" wire:key="modal-product">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-t-2xl sm:rounded-2xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $editingProduct ? 'Ubah Sparepart' : 'Tambah Sparepart' }}</h2>
                    <button wire:click="$set('showProductModal', false)" class="p-1 text-gray-400 dark:text-slate-500 rounded hover:bg-gray-100 dark:hover:bg-slate-800">&#x2715;</button>
                </div>
                <form wire:submit="saveProduct" class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nama Sparepart</label>
                        <input type="text" wire:model="pName" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('pName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">SKU</label>
                            <input type="text" wire:model="pSku" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Barcode</label>
                            <input type="text" wire:model="pBarcode" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Kategori</label>
                        <input type="text" list="kategori-list" wire:model="pCategory" placeholder="contoh: Oli, Ban, Kelistrikan"
                               class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        <datalist id="kategori-list">
                            @foreach ($this->categories as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    @if ($this->isOwner)
                        <div class="grid grid-cols-2 gap-3 p-3 border border-amber-100 rounded-lg bg-amber-50/50">
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Harga Beli (Pokok)</label>
                                <input type="number" step="1" min="0" wire:model="pCostPrice" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                @error('pCostPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Margin (%)</label>
                                <input type="number" step="0.5" min="0" wire:model="pMarginPercent" placeholder="kosongkan = pakai margin kategori" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                @error('pMarginPercent') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Harga Jual</label>
                        <input type="number" step="1" min="0" wire:model="pSellPrice" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('pSellPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if ($editingProduct)
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Keterangan Perubahan Harga <span class="text-gray-400">(wajib jika harga berubah)</span></label>
                            <textarea wire:model="priceChangeNote" rows="2" placeholder="contoh: kenaikan harga dari supplier" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500"></textarea>
                            @error('priceChangeNote') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <label class="flex items-center gap-2.5">
                        <input type="checkbox" wire:model="pActive" class="text-blue-600 border-gray-300 dark:border-slate-700 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-slate-200">Aktif dijual</span>
                    </label>

                    <div class="flex gap-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showProductModal', false)" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Riwayat Harga --}}
    @if ($showHistoryModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 bg-black/50 sm:items-center sm:p-4" wire:key="modal-history">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-t-2xl sm:rounded-2xl max-h-[85vh] overflow-y-auto">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Riwayat Perubahan Harga</h2>
                    <button wire:click="closeHistory" class="p-1 text-gray-400 dark:text-slate-500 rounded hover:bg-gray-100 dark:hover:bg-slate-800">&#x2715;</button>
                </div>
                <div class="px-5 py-4 space-y-3">
                    @forelse ($this->productHistory as $h)
                        <div class="p-3 border border-gray-100 dark:border-slate-800 rounded-lg">
                            <div class="flex items-center justify-between text-xs text-gray-400 dark:text-slate-500">
                                <span>{{ $h->created_at?->format('d M Y, H:i') }}</span>
                                <span>{{ $h->changedBy?->name ?? 'Sistem' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                                @if ($this->isOwner)
                                    <div>
                                        <p class="text-xs text-gray-400">Harga Beli</p>
                                        <p class="text-gray-700 dark:text-slate-200">Rp {{ number_format($h->old_cost_price,0,',','.') }} &rarr; Rp {{ number_format($h->new_cost_price,0,',','.') }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs text-gray-400">Harga Jual</p>
                                    <p class="text-gray-700 dark:text-slate-200">Rp {{ number_format($h->old_sell_price,0,',','.') }} &rarr; Rp {{ number_format($h->new_sell_price,0,',','.') }}</p>
                                </div>
                            </div>
                            @if ($h->note)
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 italic">"{{ $h->note }}"</p>
                            @endif
                        </div>
                    @empty
                        <p class="py-8 text-sm text-center text-gray-400 dark:text-slate-500">Belum ada riwayat perubahan harga untuk sparepart ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Margin per Kategori (Owner) --}}
    @if ($showMarginModal && $this->isOwner)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 bg-black/50 sm:items-center sm:p-4" wire:key="modal-margin">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-t-2xl sm:rounded-2xl max-h-[85vh] overflow-y-auto">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Margin per Kategori</h2>
                    <button wire:click="$set('showMarginModal', false)" class="p-1 text-gray-400 dark:text-slate-500 rounded hover:bg-gray-100 dark:hover:bg-slate-800">&#x2715;</button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <form wire:submit="saveCategoryMargin" class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-slate-200">Kategori</label>
                            <input type="text" list="kategori-list" wire:model="marginCategory" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="w-28">
                            <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-slate-200">Margin (%)</label>
                            <input type="number" step="0.5" min="0" wire:model="marginPercentInput" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">{{ $editingMarginId ? 'Simpan' : 'Tambah' }}</button>
                    </form>
                    @error('marginCategory') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('marginPercentInput') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="divide-y divide-gray-100 dark:divide-slate-800 border border-gray-100 dark:border-slate-800 rounded-lg">
                        @forelse ($this->categoryMargins as $m)
                            <div class="flex items-center justify-between px-3 py-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-slate-200">{{ $m->category }}</p>
                                    <p class="text-xs text-gray-400">Margin default: {{ number_format($m->margin_percent,1) }}%</p>
                                </div>
                                <div class="flex gap-1">
                                    <button wire:click="editCategoryMargin('{{ $m->id }}')" class="px-2 py-1 text-xs text-gray-600 dark:text-slate-300 rounded hover:bg-gray-100 dark:hover:bg-slate-800">Ubah</button>
                                    <button wire:click="deleteCategoryMargin('{{ $m->id }}')" wire:confirm="Hapus margin kategori {{ $m->category }}?" class="px-2 py-1 text-xs text-red-600 rounded hover:bg-red-50">Hapus</button>
                                </div>
                            </div>
                        @empty
                            <p class="px-3 py-6 text-sm text-center text-gray-400">Belum ada margin per kategori. Item tanpa margin sendiri akan pakai margin kategori ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Jasa --}}
    @if ($showServiceModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-0 bg-black/50 sm:items-center sm:p-4" wire:key="modal-service">
            <div class="w-full max-w-lg bg-white dark:bg-slate-900 shadow-xl rounded-t-2xl sm:rounded-2xl max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $editingService ? 'Ubah Jasa' : 'Tambah Jasa' }}</h2>
                    <button wire:click="$set('showServiceModal', false)" class="p-1 text-gray-400 dark:text-slate-500 rounded hover:bg-gray-100 dark:hover:bg-slate-800">&#x2715;</button>
                </div>
                <form wire:submit="saveService" class="px-5 py-4 space-y-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Kode Jasa</label>
                        <input type="text" wire:model="sCode" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nama Jasa</label>
                        <input type="text" wire:model="sName" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('sName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Harga</label>
                        <input type="number" step="1" min="0" wire:model="sPrice" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        @error('sPrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex items-center gap-2.5">
                        <input type="checkbox" wire:model="sActive" class="text-blue-600 border-gray-300 dark:border-slate-700 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-slate-200">Aktif ditawarkan</span>
                    </label>
                    <div class="flex gap-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showServiceModal', false)" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

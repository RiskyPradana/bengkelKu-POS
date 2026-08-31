@php
$inputCls   = 'w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition-colors';
$labelCls   = 'block text-xs font-semibold text-slate-600 mb-1.5';
$errorCls   = 'text-xs text-red-500 mt-1';
$btnPrimary = 'bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2';
$btnGhost   = 'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors';
@endphp

<div>
{{-- ===== TABS + TOOLBAR ===== --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    {{-- Tab Switcher --}}
    <div class="flex items-center bg-white rounded-xl border border-slate-200 p-1 gap-1">
        <button type="button" wire:click="switchTab('product')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-2
                       {{ $tab === 'product' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
            📦 Sparepart
            <span class="text-[10px] font-bold {{ $tab === 'product' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-full">
                {{ $this->productCount }}
            </span>
        </button>
        <button type="button" wire:click="switchTab('service')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-2
                       {{ $tab === 'service' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
            🔧 Jasa Servis
            <span class="text-[10px] font-bold {{ $tab === 'service' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-full">
                {{ $this->serviceCount }}
            </span>
        </button>
    </div>

    {{-- Search --}}
    <div class="relative flex-1 min-w-[200px] max-w-sm">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
        <input wire:model.live.debounce.350ms="search" type="text"
               placeholder="{{ $tab === 'product' ? 'Cari nama, SKU, barcode...' : 'Cari nama atau kode jasa...' }}"
               class="{{ $inputCls }} pl-10">
    </div>

    <div class="flex-1"></div>

    @if($tab === 'product')
    <button type="button" wire:click="openCreateProduct" class="{{ $btnPrimary }}">
        <span class="text-base">+</span> Tambah Sparepart
    </button>
    @else
    <button type="button" wire:click="openCreateService" class="{{ $btnPrimary }}">
        <span class="text-base">+</span> Tambah Jasa
    </button>
    @endif
</div>

{{-- ===== PRODUCT TABLE ===== --}}
@if($tab === 'product')
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                <th class="text-left px-5 py-3">Nama Sparepart</th>
                <th class="text-left px-5 py-3">SKU / Barcode</th>
                <th class="text-right px-5 py-3">Harga Beli</th>
                <th class="text-right px-5 py-3">Harga Jual</th>
                <th class="text-right px-5 py-3">Margin</th>
                <th class="text-center px-5 py-3">Status</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($this->products as $product)
            @php
            $margin = $product->cost_price > 0
                ? round((($product->sell_price - $product->cost_price) / $product->cost_price) * 100, 1)
                : null;
            @endphp
            <tr class="hover:bg-stone-50 transition-colors group">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-sm shrink-0">📦</div>
                        <span class="font-semibold text-slate-900">{{ $product->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <div class="space-y-0.5">
                        @if($product->sku)
                        <p class="font-mono-jet text-[11px] text-slate-500 font-medium">SKU: {{ $product->sku }}</p>
                        @endif
                        @if($product->barcode)
                        <p class="font-mono-jet text-[11px] text-slate-400">📈 {{ $product->barcode }}</p>
                        @endif
                        @if(!$product->sku && !$product->barcode)
                        <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </div>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono-jet text-xs text-slate-600">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono-jet text-sm font-bold text-slate-900">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    @if($margin !== null)
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                {{ $margin >= 20 ? 'bg-emerald-100 text-emerald-700' : ($margin >= 10 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600') }}">
                        {{ $margin }}%
                    </span>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-center">
                    <button type="button" wire:click="toggleProduct('{{ $product->id }}')"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                                   {{ $product->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                     {{ $product->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                        <button type="button" wire:click="openEditProduct('{{ $product->id }}')"
                                class="text-slate-500 hover:text-amber-600 p-1.5 rounded-lg hover:bg-amber-50 transition-colors">
                            ✏️
                        </button>
                        <button type="button" wire:click="deleteProduct('{{ $product->id }}')"
                                wire:confirm="Hapus sparepart {{ $product->name }}?"
                                class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-14 text-slate-400">
                    <div class="text-4xl mb-3 opacity-40">📦</div>
                    <p class="font-medium text-slate-500">Belum ada data sparepart</p>
                    @if($search)<p class="text-xs mt-1">Tidak ditemukan "{{ $search }}"</p>@endif
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($this->products->isNotEmpty())
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100">
        <p class="text-xs text-slate-400"><span class="font-semibold text-slate-600">{{ $this->products->count() }}</span> sparepart ditampilkan</p>
    </div>
    @endif
</div>
@endif

{{-- ===== SERVICE TABLE ===== --}}
@if($tab === 'service')
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                <th class="text-left px-5 py-3">Nama Jasa</th>
                <th class="text-left px-5 py-3">Kode</th>
                <th class="text-right px-5 py-3">Harga</th>
                <th class="text-center px-5 py-3">Status</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($this->serviceItems as $service)
            <tr class="hover:bg-stone-50 transition-colors group">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm shrink-0">🔧</div>
                        <span class="font-semibold text-slate-900">{{ $service->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    @if($service->code)
                    <span class="font-mono-jet text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-lg">{{ $service->code }}</span>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono-jet text-sm font-bold text-slate-900">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3.5 text-center">
                    <button type="button" wire:click="toggleService('{{ $service->id }}')"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                                   {{ $service->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                     {{ $service->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                        <button type="button" wire:click="openEditService('{{ $service->id }}')"
                                class="text-slate-500 hover:text-amber-600 p-1.5 rounded-lg hover:bg-amber-50 transition-colors">
                            ✏️
                        </button>
                        <button type="button" wire:click="deleteService('{{ $service->id }}')"
                                wire:confirm="Hapus jasa {{ $service->name }}?"
                                class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-14 text-slate-400">
                    <div class="text-4xl mb-3 opacity-40">🔧</div>
                    <p class="font-medium text-slate-500">Belum ada data jasa servis</p>
                    @if($search)<p class="text-xs mt-1">Tidak ditemukan "{{ $search }}"</p>@endif
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($this->serviceItems->isNotEmpty())
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100">
        <p class="text-xs text-slate-400"><span class="font-semibold text-slate-600">{{ $this->serviceItems->count() }}</span> jasa servis ditampilkan</p>
    </div>
    @endif
</div>
@endif

{{-- ===== PRODUCT MODAL ===== --}}
@if($showProductModal)
<div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showProductModal', false)"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 z-10">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-display text-[22px] font-bold text-slate-900">{{ $editingProduct ? 'Edit Sparepart' : 'Tambah Sparepart' }}</h3>
                <p class="text-xs text-slate-400">Data produk / spare part bengkel</p>
            </div>
            <button type="button" wire:click="$set('showProductModal', false)" class="text-slate-400 hover:text-slate-700 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-xl">×</button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="{{ $labelCls }}">Nama Sparepart <span class="text-red-500">*</span></label>
                <input wire:model="pName" type="text" placeholder="Nama produk / sparepart" class="{{ $inputCls }}">
                @error('pName') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelCls }}">SKU</label>
                    <input wire:model="pSku" type="text" placeholder="Kode SKU" class="{{ $inputCls }} font-mono-jet">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Barcode</label>
                    <input wire:model="pBarcode" type="text" placeholder="Kode barcode" class="{{ $inputCls }} font-mono-jet">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Harga Beli (Rp)</label>
                    <input wire:model="pCostPrice" type="number" min="0" step="100" placeholder="0" class="{{ $inputCls }} font-mono-jet">
                    @error('pCostPrice') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                    <input wire:model="pSellPrice" type="number" min="0" step="100" placeholder="0" class="{{ $inputCls }} font-mono-jet">
                    @error('pSellPrice') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center gap-3 py-1">
                <button type="button" wire:click="$toggle('pActive')"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $pActive ? 'bg-emerald-500' : 'bg-slate-300' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $pActive ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
                <span class="text-sm font-medium text-slate-700">{{ $pActive ? 'Aktif (tampil di POS)' : 'Nonaktif' }}</span>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <button type="button" wire:click="$set('showProductModal', false)" class="{{ $btnGhost }}">Batal</button>
            <button type="button" wire:click="saveProduct" wire:loading.attr="disabled" class="{{ $btnPrimary }}">
                <span wire:loading.remove wire:target="saveProduct">💾 Simpan</span>
                <span wire:loading wire:target="saveProduct">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ===== SERVICE MODAL ===== --}}
@if($showServiceModal)
<div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showServiceModal', false)"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 z-10">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-display text-[22px] font-bold text-slate-900">{{ $editingService ? 'Edit Jasa' : 'Tambah Jasa Servis' }}</h3>
                <p class="text-xs text-slate-400">Item pekerjaan / jasa bengkel</p>
            </div>
            <button type="button" wire:click="$set('showServiceModal', false)" class="text-slate-400 hover:text-slate-700 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-xl">×</button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="{{ $labelCls }}">Nama Jasa <span class="text-red-500">*</span></label>
                <input wire:model="sName" type="text" placeholder="Nama pekerjaan / jasa" class="{{ $inputCls }}">
                @error('sName') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelCls }}">Kode Jasa</label>
                    <input wire:model="sCode" type="text" placeholder="Kode unik" class="{{ $inputCls }} font-mono-jet">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input wire:model="sPrice" type="number" min="0" step="500" placeholder="0" class="{{ $inputCls }} font-mono-jet">
                    @error('sPrice') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-center gap-3 py-1">
                <button type="button" wire:click="$toggle('sActive')"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $sActive ? 'bg-emerald-500' : 'bg-slate-300' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $sActive ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
                <span class="text-sm font-medium text-slate-700">{{ $sActive ? 'Aktif (tersedia di POS)' : 'Nonaktif' }}</span>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <button type="button" wire:click="$set('showServiceModal', false)" class="{{ $btnGhost }}">Batal</button>
            <button type="button" wire:click="saveService" wire:loading.attr="disabled" class="{{ $btnPrimary }}">
                <span wire:loading.remove wire:target="saveService">💾 Simpan</span>
                <span wire:loading wire:target="saveService">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif
</div>{{-- root wrapper --}}

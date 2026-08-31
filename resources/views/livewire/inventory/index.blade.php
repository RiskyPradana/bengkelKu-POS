<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">&#x1F4E6; Stok & Inventori</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola stok multi-cabang & transfer barang</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1">
        @foreach(['stock'=>'&#x1F4E6; Stok Cabang','movements'=>'&#x1F4CA; Riwayat','transfers'=>'&#x21C4; Transfer','alerts'=>'&#x26A0; Peringatan'] as $key=>$label)
        <button type="button" wire:click="$set('tab','{{ $key }}')"
            class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors
                {{ $tab === $key ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            {!! $label !!}
        </button>
        @endforeach
    </div>

    {{-- TAB: STOK CABANG --}}
    @if($tab === 'stock')
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-3">
            <input wire:model.live="search" type="text" placeholder="Cari produk..."
                class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            <select wire:model.live="filterBranch"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
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
                    @forelse($stocks as $stock)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $stock->product->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $stock->product->sku ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $stock->branch->name ?? 'Pusat' }}</td>
                        <td class="px-4 py-3 text-center font-bold {{ $stock->quantity <= $stock->min_stock ? 'text-red-600' : 'text-slate-800' }}">
                            {{ $stock->quantity }}
                        </td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $stock->min_stock }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($stock->quantity <= $stock->min_stock)
                            <span class="px-2 py-1 bg-red-100 text-red-600 rounded-full text-xs font-medium">Stok Rendah</span>
                            @elseif($stock->quantity <= $stock->min_stock * 1.5)
                            <span class="px-2 py-1 bg-amber-100 text-amber-600 rounded-full text-xs font-medium">Hampir Habis</span>
                            @else
                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-medium">Normal</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" wire:click="openAdjust('{{ $stock->product_id }}', '{{ $stock->branch_id }}')"
                                class="px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-200 transition-colors">
                                Adjust
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Tidak ada data stok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB: RIWAYAT GERAKAN STOK --}}
    @if($tab === 'movements')
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
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $m->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $m->product->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m->branch->name ?? 'Pusat' }}</td>
                        <td class="px-4 py-3">
                            @php $badges=['in'=>'bg-green-100 text-green-700','out'=>'bg-red-100 text-red-700','transfer_in'=>'bg-blue-100 text-blue-700','transfer_out'=>'bg-orange-100 text-orange-700','adjustment'=>'bg-purple-100 text-purple-700']; @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badges[$m->type] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst(str_replace('_',' ',$m->type)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold {{ in_array($m->type,['in','transfer_in']) ? 'text-green-600' : 'text-red-600' }}">{{ in_array($m->type,['in','transfer_in']) ? '+' : '-' }}{{ $m->quantity }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $m->reference_id ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ Str::limit($m->notes,40) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $movements->links() }}</div>
    </div>
    @endif

    {{-- TAB: TRANSFER CABANG --}}
    @if($tab === 'transfers')
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
                        @forelse($transfers as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-amber-600">{{ $t->transfer_number }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $t->fromBranch->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $t->toBranch->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $t->transferred_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $sc=['pending'=>'bg-slate-100 text-slate-600','in_transit'=>'bg-blue-100 text-blue-700','received'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-600']; @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sc[$t->status] ?? 'bg-slate-100 text-slate-500' }}">{{ ucfirst($t->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ Str::limit($t->notes,40) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada transfer</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB: PERINGATAN STOK --}}
    @if($tab === 'alerts')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($lowStockItems as $item)
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-2xl">&#x26A0;</div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800 truncate">{{ $item->product->name }}</p>
                <p class="text-xs text-slate-500">{{ $item->branch->name ?? 'Pusat' }}</p>
                <p class="text-sm mt-1">
                    <span class="text-red-600 font-bold">{{ $item->quantity }}</span>
                    <span class="text-slate-400"> / min {{ $item->min_stock }}</span>
                </p>
            </div>
            <button type="button" wire:click="openAdjust({{ $item->id }})"
                class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-medium hover:bg-amber-600 transition-colors">
                Isi
            </button>
        </div>
        @empty
        <div class="col-span-3 py-16 text-center">
            <p class="text-5xl mb-3">&#x2705;</p>
            <p class="text-slate-500">Semua stok dalam kondisi normal!</p>
        </div>
        @endforelse
    </div>
    @endif

    {{-- MODAL: ADJUST STOK --}}
    @if($showAdjustModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showAdjustModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">&#x1F4CB; Penyesuaian Stok</h2>
            @if($adjustStock)
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="font-medium text-slate-700">{{ $adjustStock->product->name }}</p>
                <p class="text-sm text-slate-500">{{ $adjustStock->branch->name ?? 'Pusat' }} &bull; Stok saat ini: <strong>{{ $adjustStock->quantity }}</strong></p>
            </div>
            @endif
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
                    @error('adjQty') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
    @endif

    {{-- MODAL: BUAT TRANSFER --}}
    @if($showTransferModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showTransferModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-slate-800">&#x21C4; Transfer Stok Antar Cabang</h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Dari Cabang</label>
                    <select wire:model="trfFromBranch" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                    </select>
                    @error('trfFromBranch') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Ke Cabang</label>
                    <select wire:model="trfToBranch" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                    </select>
                    @error('trfToBranch') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Catatan</label>
                <input wire:model="trfNotes" type="text" placeholder="Opsional..."
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            </div>
            {{-- Item List --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700">Item Transfer</p>
                    <button type="button" wire:click="addTransferItem" class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Tambah Item</button>
                </div>
                @foreach($trfItems as $idx => $item)
                <div class="flex gap-2 items-center">
                    <select wire:model="trfItems.{{ $idx }}.product_id" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($allProducts as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                    <input wire:model="trfItems.{{ $idx }}.qty" type="number" min="1" placeholder="Qty" class="w-20 border border-slate-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none text-center">
                    <button type="button" wire:click="removeTransferItem({{ $idx }})" class="text-red-400 hover:text-red-600">&#x2715;</button>
                </div>
                @endforeach
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showTransferModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="saveTransfer" class="flex-1 py-2 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Buat Transfer</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Toast Notification --}}
    <div x-data="{ show:false, msg:'', type:'success' }"
         x-on:notify.window="show=true; msg=$event.detail.message; type=$event.detail.type; setTimeout(()=>show=false,3000)"
         x-show="show" x-transition
         :class="type==='success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed bottom-6 right-6 text-white px-5 py-3 rounded-2xl shadow-lg text-sm font-medium z-50">
        <span x-text="msg"></span>
    </div>

</div>

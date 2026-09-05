<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">&#x1F69A; Pembelian & Supplier</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola supplier, pesanan pembelian, penerimaan barang & hutang</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-xl px-4 py-2 shadow-sm">
            <p class="text-xs text-slate-400">Total Hutang Belum Lunas</p>
            <p class="text-lg font-bold text-red-600">Rp {{ number_format($this->outstandingTotal, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1">
        @foreach(['suppliers'=>'&#x1F3EA; Supplier','orders'=>'&#x1F4E6; Pesanan Pembelian','payments'=>'&#x1F4B0; Pembayaran'] as $key=>$label)
        <button type="button" wire:click="switchTab('{{ $key }}')"
            class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors
                {{ $tab === $key ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            {!! $label !!}
        </button>
        @endforeach
    </div>

    {{-- TAB: SUPPLIER --}}
    @if($tab === 'suppliers')
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <input wire:model.live="search" type="text" placeholder="Cari supplier..."
                class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            <button type="button" wire:click="openCreateSupplier"
                class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors shadow-sm whitespace-nowrap">
                &#x2795; Tambah Supplier
            </button>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Kontak</th>
                            <th class="px-4 py-3 text-left">Telepon</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($this->suppliers as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s->contact_person ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s->phone ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s->email ?: '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleSupplier('{{ $s->id }}')"
                                    class="px-2 py-1 rounded-full text-xs font-medium {{ $s->is_active ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" wire:click="openEditSupplier('{{ $s->id }}')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-200">Edit</button>
                                    <button type="button" wire:click="deleteSupplier('{{ $s->id }}')" wire:confirm="Hapus supplier ini?" class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-medium hover:bg-red-200">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada supplier</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB: PESANAN PEMBELIAN --}}
    @if($tab === 'orders')
    <div class="space-y-4">
        <div class="flex items-center gap-3 flex-wrap">
            <input wire:model.live="search" type="text" placeholder="Cari no. PO / supplier..."
                class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            <select wire:model.live="filterStatus"
                class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="ordered">Dipesan</option>
                <option value="partially_received">Sebagian Diterima</option>
                <option value="received">Selesai Diterima</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
            <button type="button" wire:click="openCreateOrder"
                class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors shadow-sm whitespace-nowrap">
                &#x2795; Buat Pesanan Pembelian
            </button>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">No. PO</th>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-left">Cabang</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Bayar</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $statusBadge = [
                                'draft' => 'bg-slate-100 text-slate-600',
                                'ordered' => 'bg-blue-100 text-blue-700',
                                'partially_received' => 'bg-amber-100 text-amber-700',
                                'received' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-600',
                            ];
                            $statusLabel = [
                                'draft' => 'Draft',
                                'ordered' => 'Dipesan',
                                'partially_received' => 'Sebagian Diterima',
                                'received' => 'Selesai Diterima',
                                'cancelled' => 'Dibatalkan',
                            ];
                            $payBadge = [
                                'belum_lunas' => 'bg-red-100 text-red-600',
                                'sebagian' => 'bg-amber-100 text-amber-700',
                                'lunas' => 'bg-green-100 text-green-700',
                            ];
                            $payLabel = [
                                'belum_lunas' => 'Belum Lunas',
                                'sebagian' => 'Sebagian',
                                'lunas' => 'Lunas',
                            ];
                        @endphp
                        @forelse($this->purchaseOrders as $po)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-amber-600">{{ $po->po_number }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $po->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $po->branch->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $po->order_date?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">Rp {{ number_format((float) $po->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusBadge[$po->status] ?? 'bg-slate-100 text-slate-500' }}">{{ $statusLabel[$po->status] ?? $po->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $payBadge[$po->payment_status] ?? 'bg-slate-100 text-slate-500' }}">{{ $payLabel[$po->payment_status] ?? $po->payment_status }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    @if($po->status === 'draft')
                                    <button type="button" wire:click="markOrdered('{{ $po->id }}')" class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-200">Pesan</button>
                                    <button type="button" wire:click="cancelOrder('{{ $po->id }}')" wire:confirm="Batalkan pesanan ini?" class="px-2.5 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-medium hover:bg-red-200">Batal</button>
                                    @endif
                                    @if(in_array($po->status, ['ordered', 'partially_received']))
                                    <button type="button" wire:click="openReceive('{{ $po->id }}')" class="px-2.5 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium hover:bg-green-200">Terima Barang</button>
                                    @endif
                                    @if(!in_array($po->status, ['cancelled', 'draft']) && $po->payment_status !== 'lunas')
                                    <button type="button" wire:click="openPayment('{{ $po->id }}')" class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-200">Bayar</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">Belum ada pesanan pembelian</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB: PEMBAYARAN --}}
    @if($tab === 'payments')
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">No. PO</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Metode</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($this->paymentHistory as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $p->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-xs font-medium text-amber-600">{{ $p->purchaseOrder->po_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $p->purchaseOrder->supplier->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ ucfirst($p->method) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600">Rp {{ number_format((float) $p->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $p->reference_number ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat pembayaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- MODAL: SUPPLIER --}}
    @if($showSupplierModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showSupplierModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">{{ $editingSupplier ? 'Edit Supplier' : 'Tambah Supplier' }}</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Nama Supplier</label>
                    <input wire:model="supName" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                    @error('supName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Kontak Person</label>
                    <input wire:model="supContact" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700 mb-1 block">Telepon</label>
                        <input wire:model="supPhone" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 mb-1 block">Email</label>
                        <input wire:model="supEmail" type="text" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        @error('supEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Alamat</label>
                    <textarea wire:model="supAddress" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none resize-none"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input wire:model="supActive" type="checkbox" class="rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                    Supplier aktif
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showSupplierModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="saveSupplier" class="flex-1 py-2 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Simpan</button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: BUAT PESANAN PEMBELIAN --}}
    @if($showOrderModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showOrderModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-slate-800">&#x1F4E6; Pesanan Pembelian Baru</h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Supplier</label>
                    <select wire:model="orderSupplierId" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($this->activeSuppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                    @error('orderSupplierId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Cabang Tujuan</label>
                    <select wire:model="orderBranchId" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($this->branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                    </select>
                    @error('orderBranchId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Tanggal Pesan</label>
                    <input wire:model="orderDate" type="date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Estimasi Datang</label>
                    <input wire:model="orderExpected" type="date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Diskon (Rp)</label>
                    <input wire:model="orderDiscount" type="number" min="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Catatan</label>
                <input wire:model="orderNotes" type="text" placeholder="Opsional..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            </div>
            {{-- Item List --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700">Item Pembelian</p>
                    <button type="button" wire:click="addOrderItem" class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Tambah Item</button>
                </div>
                @error('orderItems') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                @foreach($orderItems as $idx => $item)
                <div class="flex gap-2 items-center">
                    <select wire:model="orderItems.{{ $idx }}.product_id" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih Sparepart --</option>
                        @foreach($this->allProducts as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                    <input wire:model="orderItems.{{ $idx }}.quantity_ordered" type="number" min="1" placeholder="Qty" class="w-20 border border-slate-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none text-center">
                    <input wire:model="orderItems.{{ $idx }}.unit_cost" type="number" min="0" placeholder="Harga Beli" class="w-32 border border-slate-200 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none text-right">
                    <button type="button" wire:click="removeOrderItem({{ $idx }})" class="text-red-400 hover:text-red-600">&#x2715;</button>
                </div>
                @endforeach
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showOrderModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="saveOrder" class="flex-1 py-2 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Buat Pesanan</button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: TERIMA BARANG --}}
    @if($showReceiveModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showReceiveModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-slate-800">&#x1F4E5; Terima Barang</h2>
            <div>
                <label class="text-sm font-medium text-slate-700 mb-1 block">Cabang Penerima</label>
                <select wire:model="receiveBranchId" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($this->branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </select>
                @error('receiveBranchId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-700">Jumlah Diterima</p>
                @if($receivingPoId)
                @php $po = \App\Domains\Purchasing\Models\PurchaseOrder::with('items.product')->find($receivingPoId); @endphp
                @foreach($po?->items ?? [] as $item)
                @php $remaining = max(0, $item->quantity_ordered - $item->quantity_received); @endphp
                <div class="flex items-center justify-between gap-3 bg-slate-50 rounded-lg px-3 py-2">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $item->product->name ?? '-' }}</p>
                        <p class="text-xs text-slate-400">Sisa: {{ $remaining }} dari {{ $item->quantity_ordered }}</p>
                    </div>
                    <input wire:model="receiveQty.{{ $item->id }}" type="number" min="0" max="{{ $remaining }}" class="w-24 border border-slate-200 rounded-lg px-2 py-2 text-sm text-center focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                @endforeach
                @endif
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showReceiveModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="saveReceive" class="flex-1 py-2 bg-green-500 text-white rounded-xl text-sm font-medium hover:bg-green-600">Simpan Penerimaan</button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: PEMBAYARAN --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showPaymentModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">&#x1F4B0; Bayar Hutang Supplier</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Jumlah Bayar (Rp)</label>
                    <input wire:model="paymentAmount" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                    @error('paymentAmount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Metode Pembayaran</label>
                    <select wire:model="paymentMethod" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="giro">Giro / BG</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">No. Referensi</label>
                    <input wire:model="paymentReference" type="text" placeholder="Opsional..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Catatan</label>
                    <textarea wire:model="paymentNotes" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showPaymentModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="savePayment" class="flex-1 py-2 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Simpan Pembayaran</button>
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

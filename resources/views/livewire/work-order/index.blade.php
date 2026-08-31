@php
$inputCls   = 'w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition-colors';
$selectCls  = $inputCls;
$labelCls   = 'block text-xs font-semibold text-slate-600 mb-1.5';
$errorCls   = 'text-xs text-red-500 mt-1';
$btnPrimary = 'bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2';
$btnGhost   = 'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors';

$statuses = [
    'Pending'     => ['pill' => 'bg-amber-100 text-amber-700 border-amber-200',   'dot' => 'bg-amber-400'],
    'In Progress' => ['pill' => 'bg-blue-100 text-blue-700 border-blue-200',      'dot' => 'bg-blue-500'],
    'Completed'   => ['pill' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
    'Paid'        => ['pill' => 'bg-violet-100 text-violet-700 border-violet-200', 'dot' => 'bg-violet-500'],
];

$filterTabs = [
    '' => 'Semua',
    'Pending'     => 'Pending',
    'In Progress' => 'In Progress',
    'Completed'   => 'Selesai',
    'Paid'        => 'Lunas',
];
@endphp

<div>
{{-- ===== TOOLBAR ===== --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    {{-- Search --}}
    <div class="relative flex-1 min-w-[200px] max-w-sm">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base">🔍</span>
        <input wire:model.live.debounce.350ms="search"
               type="text"
               placeholder="Cari pelanggan, plat, keluhan..."
               class="{{ $inputCls }} pl-10">
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex items-center gap-1 bg-white rounded-xl border border-slate-200 p-1">
        @foreach($filterTabs as $val => $label)
        <button wire:click="$set('statusFilter', '{{ $val }}')"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all
                       {{ $statusFilter === $val
                            ? 'bg-slate-900 text-white shadow-sm'
                            : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Create button --}}
    <button wire:click="openCreateModal" class="{{ $btnPrimary }}">
        <span class="text-base">+</span> Buat WO Baru
    </button>
</div>

{{-- ===== TABLE ===== --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                <th class="text-left px-5 py-3">ID</th>
                <th class="text-left px-5 py-3">Pelanggan</th>
                <th class="text-left px-5 py-3">Kendaraan</th>
                <th class="text-left px-5 py-3">Mekanik</th>
                <th class="text-left px-5 py-3">Keluhan</th>
                <th class="text-left px-5 py-3">Status</th>
                <th class="text-left px-5 py-3">Total</th>
                <th class="text-left px-5 py-3">Tgl Buat</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($this->workOrders as $wo)
            @php $st = $wo->status?->value ?? (string)$wo->status; @endphp
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-5 py-3.5">
                    <span class="font-mono-jet text-[11px] font-bold text-slate-400 uppercase">
                        №{{ strtoupper(substr($wo->id, -6)) }}
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <p class="font-semibold text-slate-900 text-sm">{{ $wo->customer?->name ?? '—' }}</p>
                    <p class="text-[11px] text-slate-400">{{ $wo->customer?->phone }}</p>
                </td>
                <td class="px-5 py-3.5">
                    <p class="font-mono-jet text-xs font-bold text-slate-800">{{ $wo->vehicle?->plate_number ?? '—' }}</p>
                    <p class="text-[11px] text-slate-400">{{ $wo->vehicle?->brand }} {{ $wo->vehicle?->type }}</p>
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-sm">
                    {{ $wo->mechanic?->name ?? '—' }}
                </td>
                <td class="px-5 py-3.5 max-w-[160px]">
                    <p class="text-slate-600 text-xs line-clamp-2">{{ $wo->complaint ?? '—' }}</p>
                    @if($wo->odometer)
                    <p class="text-[10.5px] text-slate-400 mt-0.5">KM: {{ number_format($wo->odometer, 0, ',', '.') }}</p>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full border
                                {{ ($statuses[$st] ?? [])['pill'] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                        {{ $st }}
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    @if($wo->invoice?->grand_total)
                    <span class="font-mono-jet text-xs font-bold text-emerald-600">
                        Rp {{ number_format($wo->invoice->grand_total, 0, ',', '.') }}
                    </span>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-400 text-xs whitespace-nowrap">
                    {{ $wo->created_at->format('d/m/Y') }}
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-2">
                        <button wire:click="openEditModal('{{ $wo->id }}')"
                                class="text-slate-500 hover:text-amber-600 transition-colors p-1 rounded-lg hover:bg-amber-50"
                                title="Edit">
                            ✏️
                        </button>
                        <button wire:click="deleteWorkOrder('{{ $wo->id }}')"
                                wire:confirm="Yakin ingin menghapus Work Order ini? Tindakan ini tidak bisa dibatalkan."
                                class="text-slate-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50"
                                title="Hapus">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-16 text-slate-400">
                    <div class="text-4xl mb-3 opacity-40">📋</div>
                    <p class="font-medium text-slate-500 mb-1">Belum ada Work Order</p>
                    <p class="text-xs text-slate-400 mb-4">
                        @if($search || $statusFilter)
                            Tidak ada WO yang cocok dengan filter ini
                        @else
                            Klik "Buat WO Baru" untuk memulai
                        @endif
                    </p>
                    @if($search || $statusFilter)
                    <button wire:click="$set('search',''); $set('statusFilter','')"
                            class="text-amber-600 hover:underline text-sm">
                        Reset filter
                    </button>
                    @endif
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if(!$this->workOrders->isEmpty())
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100">
        <p class="text-xs text-slate-400">Menampilkan <span class="font-semibold text-slate-600">{{ $this->workOrders->count() }}</span> Work Order</p>
    </div>
    @endif
</div>

{{-- ===== CREATE / EDIT MODAL ===== --}}
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center" wire:key="wo-modal">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
         wire:click="$set('showModal', false)"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl mx-4 z-10 flex flex-col max-h-[90vh]"
         x-data x-trap.noscroll="true">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-display text-[22px] font-bold text-slate-900">
                    {{ $isEditing ? 'Edit Work Order' : 'Buat Work Order Baru' }}
                </h3>
                <p class="text-xs text-slate-400">{{ $isEditing ? 'Perbarui data SPK servis' : 'Isi data untuk membuat SPK servis baru' }}</p>
            </div>
            <button wire:click="$set('showModal', false)"
                    class="text-slate-400 hover:text-slate-700 transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-xl">
                ×
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 overflow-y-auto space-y-4">

            {{-- Pelanggan --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelCls }}">Pelanggan <span class="text-red-500">*</span></label>
                    <select wire:model.live="customerId" class="{{ $selectCls }}">
                        <option value="">— Pilih Pelanggan —</option>
                        @foreach($this->customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                    @error('customerId') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="{{ $labelCls }}">Kendaraan <span class="text-red-500">*</span></label>
                    <select wire:model="vehicleId" class="{{ $selectCls }}" @if(!$customerId) disabled @endif>
                        <option value="">— Pilih Kendaraan —</option>
                        @foreach($this->vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }} — {{ $vehicle->brand }} {{ $vehicle->type }}</option>
                        @endforeach
                    </select>
                    @if(!$customerId)
                    <p class="text-[11px] text-slate-400 mt-1">Pilih pelanggan terlebih dahulu</p>
                    @endif
                    @error('vehicleId') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Status & Odometer --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelCls }}">Status</label>
                    <select wire:model="status" class="{{ $selectCls }}">
                        @foreach($this->statuses as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Odometer (km)</label>
                    <input wire:model="odometer" type="number" min="0"
                           placeholder="Contoh: 45000" class="{{ $inputCls }}">
                </div>
            </div>

            {{-- Keluhan --}}
            <div>
                <label class="{{ $labelCls }}">Deskripsi Keluhan <span class="text-red-500">*</span></label>
                <textarea wire:model="complaint" rows="3"
                          placeholder="Jelaskan keluhan / kerusakan kendaraan pelanggan..."
                          class="{{ $inputCls }} resize-none"></textarea>
                @error('complaint') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
            </div>

            {{-- Mekanik --}}
            <div>
                <label class="{{ $labelCls }}">Mekanik yang Ditugaskan</label>
                <select wire:model="mechanicId" class="{{ $selectCls }}">
                    <option value="">— Belum ditugaskan —</option>
                    @foreach($this->mechanics as $mechanic)
                    <option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between shrink-0">
            <button wire:click="$set('showModal', false)" class="{{ $btnGhost }}">Batal</button>
            <button wire:click="saveWorkOrder" wire:loading.attr="disabled"
                    class="{{ $btnPrimary }}">
                <span wire:loading.remove wire:target="saveWorkOrder">💾 Simpan</span>
                <span wire:loading wire:target="saveWorkOrder">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif
</div>{{-- root wrapper --}}

@php
$inputCls   = 'w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400 transition-colors';
$labelCls   = 'block text-xs font-semibold text-slate-600 mb-1.5';
$errorCls   = 'text-xs text-red-500 mt-1';
$btnPrimary = 'bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2';
$btnGhost   = 'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors';
@endphp

<div>
{{-- ===== TOOLBAR ===== --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    <div class="relative flex-1 min-w-[200px] max-w-sm">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
        <input wire:model.live.debounce.350ms="search" type="text"
               placeholder="Cari nama, telepon, plat nomor..."
               class="{{ $inputCls }} pl-10">
    </div>
    <div class="flex-1"></div>
    <button wire:click="openCreateModal" class="{{ $btnPrimary }}">
        <span class="text-base">+</span> Tambah Pelanggan
    </button>
</div>

{{-- ===== SUMMARY STATS ===== --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    @php
    $allCust    = $this->customers;
    $custCount  = $allCust->count();
    $veCount    = \App\Domains\CustomerVehicle\Models\Vehicle::count();
    $woCount    = \App\Domains\WorkOrder\Models\WorkOrder::count();
    @endphp
    <div class="bg-white rounded-2xl shadow-sm p-5 border-t-4 border-amber-400">
        <p class="text-[10.5px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Total Pelanggan</p>
        <p class="font-display text-4xl font-bold text-slate-900">{{ $custCount }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-t-4 border-blue-400">
        <p class="text-[10.5px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Total Kendaraan</p>
        <p class="font-display text-4xl font-bold text-slate-900">{{ $veCount }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-t-4 border-emerald-400">
        <p class="text-[10.5px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Total Work Order</p>
        <p class="font-display text-4xl font-bold text-slate-900">{{ $woCount }}</p>
    </div>
</div>

{{-- ===== CUSTOMER TABLE ===== --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                <th class="text-left px-5 py-3">Nama Pelanggan</th>
                <th class="text-left px-5 py-3">Telepon</th>
                <th class="text-left px-5 py-3">Email</th>
                <th class="text-left px-5 py-3">Alamat</th>
                <th class="text-center px-5 py-3">Kendaraan</th>
                <th class="text-center px-5 py-3">WO</th>
                <th class="text-right px-5 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($this->customers as $customer)
            <tr class="hover:bg-stone-50 transition-colors group">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                        <span class="font-semibold text-slate-900">{{ $customer->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    @if($customer->phone)
                    <span class="font-mono-jet text-xs text-slate-700">{{ $customer->phone }}</span>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $customer->email ?? '—' }}</td>
                <td class="px-5 py-3.5 text-slate-500 text-xs max-w-[160px] truncate">{{ $customer->address ?? '—' }}</td>
                <td class="px-5 py-3.5 text-center">
                    <button wire:click="openVehicleList('{{ $customer->id }}', '{{ addslashes($customer->name) }}')"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                                   {{ ($customer->vehicles_count ?? 0) > 0 ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}
                                   transition-colors">
                        🚗 {{ $customer->vehicles_count ?? 0 }}
                    </button>
                </td>
                <td class="px-5 py-3.5 text-center">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ ($customer->work_orders_count ?? 0) > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                        {{ $customer->work_orders_count ?? 0 }} WO
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                        <button wire:click="openAddVehicle('{{ $customer->id }}', '{{ addslashes($customer->name) }}')"
                                class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-50 transition-colors text-xs" title="Tambah Kendaraan">
                            + 🚗
                        </button>
                        <button wire:click="openEditModal('{{ $customer->id }}')"
                                class="text-slate-500 hover:text-amber-600 p-1.5 rounded-lg hover:bg-amber-50 transition-colors" title="Edit">
                            ✏️
                        </button>
                        <button wire:click="deleteCustomer('{{ $customer->id }}')"
                                wire:confirm="Yakin hapus pelanggan {{ $customer->name }}? Semua data terkait akan terhapus."
                                class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-16 text-slate-400">
                    <div class="text-4xl mb-3 opacity-40">👤</div>
                    <p class="font-medium text-slate-500">Belum ada pelanggan</p>
                    @if($search)
                    <p class="text-xs mt-1">Tidak ditemukan dengan kata kunci "{{ $search }}"</p>
                    @endif
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if(!$this->customers->isEmpty())
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100">
        <p class="text-xs text-slate-400">Total <span class="font-semibold text-slate-600">{{ $this->customers->count() }}</span> pelanggan ditampilkan</p>
    </div>
    @endif
</div>

{{-- ===== VEHICLE LIST MODAL ===== --}}
@if($showVehicleList)
<div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showVehicleList', false)"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 z-10 flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-display text-[22px] font-bold text-slate-900">🚗 Daftar Kendaraan</h3>
                <p class="text-xs text-slate-400">{{ $vehicleListCustomerName }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="openAddVehicle('{{ $vehicleListCustomerId }}', '{{ addslashes($vehicleListCustomerName) }}')"
                        class="text-xs font-semibold bg-amber-400 hover:bg-amber-500 text-slate-900 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                    + Tambah
                </button>
                <button wire:click="$set('showVehicleList', false)"
                        class="text-slate-400 hover:text-slate-700 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-xl">
                    ×
                </button>
            </div>
        </div>
        <div class="overflow-y-auto p-6">
            @if($this->vehicleList->isEmpty())
            <div class="text-center py-10 text-slate-400">
                <div class="text-4xl mb-3 opacity-40">🚗</div>
                <p class="font-medium">Belum ada kendaraan terdaftar</p>
                <button wire:click="openAddVehicle('{{ $vehicleListCustomerId }}', '{{ addslashes($vehicleListCustomerName) }}')"
                        class="text-amber-600 hover:underline text-sm mt-2 inline-block">+ Tambah kendaraan</button>
            </div>
            @else
            <div class="space-y-3">
                @foreach($this->vehicleList as $vehicle)
                <div class="flex items-center gap-4 p-4 bg-stone-50 rounded-xl border border-slate-100 hover:border-slate-200 transition-colors">
                    <div class="h-10 w-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0">🚗</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-mono-jet text-sm font-bold text-slate-900">{{ $vehicle->plate_number }}</p>
                        <p class="text-xs text-slate-600 mt-0.5">{{ $vehicle->brand }} {{ $vehicle->type }} {{ $vehicle->year ? '(' . $vehicle->year . ')' : '' }}</p>
                        @if($vehicle->last_mileage)
                        <p class="text-[11px] text-slate-400">KM terakhir: {{ number_format($vehicle->last_mileage, 0, ',', '.') }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button wire:click="openEditVehicle('{{ $vehicle->id }}')"
                                class="text-slate-500 hover:text-amber-600 p-1.5 rounded-lg hover:bg-amber-50 transition-colors">
                            ✏️
                        </button>
                        <button wire:click="deleteVehicle('{{ $vehicle->id }}')"
                                wire:confirm="Hapus kendaraan {{ $vehicle->plate_number }}?"
                                class="text-slate-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                            🗑️
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ===== CUSTOMER MODAL ===== --}}
@if($showModal)
<div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 z-10 flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-display text-[22px] font-bold text-slate-900">{{ $isEditing ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}</h3>
                <p class="text-xs text-slate-400">{{ $isEditing ? 'Perbarui informasi pelanggan' : 'Daftarkan pelanggan baru' }}</p>
            </div>
            <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-700 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-xl">×</button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="{{ $labelCls }}">Nama Lengkap <span class="text-red-500">*</span></label>
                <input wire:model="custName" type="text" placeholder="Nama pelanggan" class="{{ $inputCls }}">
                @error('custName') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelCls }}">No. Telepon</label>
                    <input wire:model="custPhone" type="text" placeholder="08xx-xxxx-xxxx" class="{{ $inputCls }}">
                    @error('custPhone') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Email</label>
                    <input wire:model="custEmail" type="email" placeholder="email@contoh.com" class="{{ $inputCls }}">
                    @error('custEmail') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="{{ $labelCls }}">Alamat</label>
                <textarea wire:model="custAddress" rows="2" placeholder="Alamat pelanggan (opsional)" class="{{ $inputCls }} resize-none"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <button wire:click="$set('showModal', false)" class="{{ $btnGhost }}">Batal</button>
            <button wire:click="saveCustomer" wire:loading.attr="disabled" class="{{ $btnPrimary }}">
                <span wire:loading.remove wire:target="saveCustomer">💾 Simpan</span>
                <span wire:loading wire:target="saveCustomer">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ===== VEHICLE MODAL ===== --}}
@if($showVehicleModal)
<div class="fixed inset-0 z-[60] flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showVehicleModal', false)"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 z-10 flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-display text-[22px] font-bold text-slate-900">{{ $editingVehicle ? 'Edit Kendaraan' : 'Tambah Kendaraan' }}</h3>
                <p class="text-xs text-slate-400">Pelanggan: {{ $vehicleCustomerName }}</p>
            </div>
            <button wire:click="$set('showVehicleModal', false)" class="text-slate-400 hover:text-slate-700 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-xl">×</button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="{{ $labelCls }}">Plat Nomor <span class="text-red-500">*</span></label>
                    <input wire:model="vPlate" type="text" placeholder="Contoh: B 1234 XYZ" class="{{ $inputCls }} uppercase font-mono-jet tracking-wider">
                    @error('vPlate') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Merek <span class="text-red-500">*</span></label>
                    <input wire:model="vBrand" type="text" placeholder="Honda, Toyota, dll" class="{{ $inputCls }}">
                    @error('vBrand') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Tipe / Model</label>
                    <input wire:model="vType" type="text" placeholder="Beat, Avanza, dll" class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Tahun</label>
                    <input wire:model="vYear" type="number" min="1990" max="{{ date('Y') }}" placeholder="Contoh: 2020" class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="{{ $labelCls }}">KM Terakhir</label>
                    <input wire:model="vMileage" type="number" min="0" placeholder="Contoh: 35000" class="{{ $inputCls }}">
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <button wire:click="$set('showVehicleModal', false)" class="{{ $btnGhost }}">Batal</button>
            <button wire:click="saveVehicle" wire:loading.attr="disabled" class="{{ $btnPrimary }}">
                <span wire:loading.remove wire:target="saveVehicle">💾 Simpan</span>
                <span wire:loading wire:target="saveVehicle">Menyimpan...</span>
            </button>
        </div>
    </div>
</div>
@endif
</div>{{-- root wrapper --}}

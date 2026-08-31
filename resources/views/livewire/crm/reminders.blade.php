<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">&#x1F514; CRM & Pengingat Servis</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kirim pengingat servis via WhatsApp otomatis</p>
        </div>
        <button type="button" wire:click="openCreate"
            class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors shadow-sm">
            &#x2795; Buat Pengingat
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $stats = [
                ['icon'=>'&#x1F514;','label'=>'Total Pengingat','value'=>$reminders->total(),'color'=>'blue'],
                ['icon'=>'&#x23F3;','label'=>'Pending','value'=>$reminders->where('status','pending')->count(),'color'=>'amber'],
                ['icon'=>'&#x2705;','label'=>'Terkirim','value'=>$reminders->where('status','sent')->count(),'color'=>'green'],
                ['icon'=>'&#x274C;','label'=>'Gagal','value'=>$reminders->where('status','failed')->count(),'color'=>'red'],
            ];
        @endphp
        @foreach($stats as $s)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-2xl mb-1">{!! $s['icon'] !!}</p>
            <p class="text-2xl font-bold text-slate-800">{{ $s['value'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1">
        <button type="button" wire:click="$set('tab','reminders')"
            class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors {{ $tab==='reminders' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            &#x1F4CB; Daftar Pengingat
        </button>
        <button type="button" wire:click="$set('tab','logs')"
            class="flex-1 py-2 text-sm font-medium rounded-lg transition-colors {{ $tab==='logs' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            &#x1F4F1; Log WhatsApp
        </button>
    </div>

    {{-- TAB: PENGINGAT --}}
    @if($tab === 'reminders')
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap gap-3">
            <input wire:model.live="search" type="text" placeholder="Cari pelanggan / plat nomor..."
                class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            <select wire:model.live="filterStatus" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="sent">Terkirim</option>
                <option value="failed">Gagal</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Jadwal Kirim</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($reminders as $r)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $r->customer->name ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $r->customer->phone ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-slate-700">{{ $r->vehicle->plate_number ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $r->vehicle->model_name ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-medium">{{ ucfirst($r->reminder_type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $r->scheduled_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $sc=['pending'=>'bg-amber-100 text-amber-700','sent'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-600','cancelled'=>'bg-slate-100 text-slate-500']; @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sc[$r->status] ?? 'bg-slate-100 text-slate-500' }}">{{ ucfirst($r->status) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                @if($r->status === 'pending')
                                <button type="button" wire:click="sendReminder({{ $r->id }})" wire:loading.attr="disabled"
                                    class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium hover:bg-green-200 transition-colors">
                                    &#x1F4F2; Kirim
                                </button>
                                @endif
                                <button type="button" wire:click="deleteReminder({{ $r->id }})" wire:confirm="Hapus pengingat ini?"
                                    class="px-3 py-1 bg-red-50 text-red-500 rounded-lg text-xs font-medium hover:bg-red-100 transition-colors">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada pengingat</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $reminders->links() }}</div>
    </div>
    @endif

    {{-- TAB: LOG WA --}}
    @if($tab === 'logs')
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">No. Tujuan</th>
                        <th class="px-4 py-3 text-left">Pesan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($waLogs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $log->phone }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs max-w-xs">{{ Str::limit($log->message, 80) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $log->status==='sent' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">{{ ucfirst($log->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $log->reference_type ? Str::afterLast($log->reference_type, '\\').':'.$log->reference_id : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada log</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $waLogs->links() }}</div>
    </div>
    @endif

    {{-- MODAL: BUAT PENGINGAT --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:click.self="$set('showModal',false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-800">&#x1F4F1; Buat Pengingat WhatsApp</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Kendaraan</label>
                    <select wire:model="fVehicleId" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="">-- Pilih Kendaraan --</option>
                        @foreach($allVehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->plate_number }} - {{ $v->customer->name ?? '' }}</option>
                        @endforeach
                    </select>
                    @error('fVehicleId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Tipe Pengingat</label>
                    <select wire:model.live="fTrigger" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                        <option value="interval_days">Berdasarkan Interval Hari</option>
                        <option value="mileage">Berdasarkan Kilometer</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Interval Hari</label>
                    <input wire:model="fIntervalDay" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Interval Kilometer</label>
                    <input wire:model="fMileage" type="number" min="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Tanggal Servis Terakhir</label>
                    <input wire:model="fLastDate" type="date" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-700 mb-1 block">KM Servis Terakhir</label>
                    <input wire:model="fLastMileage" type="number" min="0" placeholder="Opsional" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showModal',false)" class="flex-1 py-2 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" wire:click="saveReminder" class="flex-1 py-2 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600">Simpan</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Toast --}}
    <div x-data="{ show:false, msg:'', type:'success' }"
         x-on:notify.window="show=true; msg=$event.detail.message; type=$event.detail.type; setTimeout(()=>show=false,3000)"
         x-show="show" x-transition
         :class="type==='success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed bottom-6 right-6 text-white px-5 py-3 rounded-2xl shadow-lg text-sm font-medium z-50">
        <span x-text="msg"></span>
    </div>

</div>

<div class="p-4 space-y-4">

    <h2 class="text-white font-bold text-lg">&#x1F4CB; Work Order Aktif</h2>

    {{-- Filter --}}
    <div class="flex gap-2">
        <input wire:model.live="search" type="text" placeholder="Cari WO / plat nomor..."
            class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
    </div>

    {{-- WO List --}}
    <div class="space-y-3">
        @forelse($workOrders as $wo)
        <div class="bg-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-white font-semibold">{{ $wo->vehicle?->plate_number }} &mdash; {{ $wo->vehicle?->model_name }}</p>
                    <p class="text-slate-400 text-xs mt-0.5">{{ $wo->wo_number }} &bull; {{ $wo->customer?->name }}</p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium flex-shrink-0
                    {{ $wo->status->value === 'In Progress' ? 'bg-blue-500/20 text-blue-400' :
                       ($wo->status->value === 'Completed' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400') }}">
                    {{ $wo->status->label() }}
                </span>
            </div>

            @if($wo->complaint)
            <p class="text-slate-400 text-xs mt-2 line-clamp-2">{{ $wo->complaint }}</p>
            @endif

            <div class="flex items-center gap-2 mt-3" x-data>
                @if($wo->status->value === 'Pending')
                <button type="button"
                    @click="navigator.onLine ? $wire.startWork('{{ $wo->id }}') : window.BengkelOffline.queue('wo.start', { id: '{{ $wo->id }}' }).then(() => $dispatch('notify', { type: 'success', message: 'Tersimpan offline, akan disinkronkan otomatis.' }))"
                    class="flex-1 py-2 bg-blue-500 text-white rounded-xl text-xs font-medium hover:bg-blue-600 transition-colors">
                    &#x25B6; Mulai Kerjakan
                </button>
                @elseif($wo->status->value === 'In Progress')
                <button type="button"
                    @click="navigator.onLine ? $wire.finishWork('{{ $wo->id }}') : window.BengkelOffline.queue('wo.finish', { id: '{{ $wo->id }}' }).then(() => $dispatch('notify', { type: 'success', message: 'Tersimpan offline, akan disinkronkan otomatis.' }))"
                    class="flex-1 py-2 bg-green-500 text-white rounded-xl text-xs font-medium hover:bg-green-600 transition-colors">
                    &#x2705; Selesai
                </button>
                @endif
                <a href="/mobile/scanner?wo={{ $wo->id }}" class="px-3 py-2 bg-slate-700 text-slate-300 rounded-xl text-xs font-medium">
                    &#x1F4F7; Scan
                </a>
            </div>
        </div>
        @empty
        <div class="py-16 text-center text-slate-500 text-sm">Tidak ada WO aktif</div>
        @endforelse
    </div>

    <div class="pb-2">{{ $workOrders->links() }}</div>

    {{-- Toast --}}
    <div x-data="{ show:false, msg:'', type:'success' }"
         x-on:notify.window="show=true; msg=$event.detail.message; type=$event.detail.type; setTimeout(()=>show=false,3000)"
         x-show="show" x-transition
         :class="type==='success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed bottom-24 right-4 left-4 text-white px-5 py-3 rounded-2xl shadow-lg text-sm font-medium z-50 text-center">
        <span x-text="msg"></span>
    </div>

</div>

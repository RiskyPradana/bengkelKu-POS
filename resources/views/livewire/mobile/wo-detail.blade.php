<div class="p-4 space-y-4">

    <a href="/mobile/wo" class="inline-flex items-center gap-1 text-slate-400 text-sm">&#x2190; Kembali ke daftar WO</a>

    @if (! $this->workOrder)
        <div class="py-16 text-center text-slate-500 text-sm">SPK tidak ditemukan atau sudah dihapus.</div>
    @else
        @php($wo = $this->workOrder)

        <div class="bg-slate-800 rounded-2xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-white font-bold text-lg">{{ $wo->vehicle?->plate_number ?? '-' }}</p>
                    <p class="text-slate-400 text-sm mt-0.5">{{ trim(($wo->vehicle?->brand ?? '').' '.($wo->vehicle?->type ?? '')) ?: '-' }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0
                    {{ $wo->status->value === 'In Progress' ? 'bg-blue-500/20 text-blue-400' :
                       ($wo->status->value === 'Completed' ? 'bg-green-500/20 text-green-400' :
                       ($wo->status->value === 'Paid' ? 'bg-slate-500/20 text-slate-300' : 'bg-amber-500/20 text-amber-400')) }}">
                    {{ $wo->status->label() }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                <div>
                    <p class="text-slate-500 text-xs">No. SPK</p>
                    <p class="text-white font-medium">{{ $wo->wo_number ?? ('WO-'.str($wo->id)->substr(0,8)) }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Pelanggan</p>
                    <p class="text-white font-medium">{{ $wo->customer?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Odometer</p>
                    <p class="text-white font-medium">{{ $wo->odometer ? number_format($wo->odometer, 0, ',', '.').' km' : '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Mekanik</p>
                    <p class="text-white font-medium">{{ $wo->mechanic?->name ?? '-' }}</p>
                </div>
            </div>
            @if ($wo->complaint)
            <div class="mt-4">
                <p class="text-slate-500 text-xs">Keluhan / Catatan</p>
                <p class="text-slate-200 text-sm mt-1">{{ $wo->complaint }}</p>
            </div>
            @endif
        </div>

        {{-- Item Jasa & Sparepart --}}
        <div class="bg-slate-800 rounded-2xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700">
                <p class="text-white font-semibold text-sm">&#x1F6E0; Jasa &amp; Sparepart</p>
            </div>
            <div class="divide-y divide-slate-700">
                @forelse ($wo->items as $item)
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $item->name }}</p>
                        <p class="text-slate-400 text-xs">{{ $item->qty }} x Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</p>
                    </div>
                    <p class="text-slate-200 text-sm font-semibold">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-slate-500 text-sm">Belum ada item</div>
                @endforelse
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2" x-data>
            @if($wo->status->value === 'Pending')
            <button type="button" wire:click="startWork"
                class="flex-1 py-3 bg-blue-500 text-white rounded-xl text-sm font-medium hover:bg-blue-600 transition-colors">
                &#x25B6; Mulai Kerjakan
            </button>
            @elseif($wo->status->value === 'In Progress')
            <button type="button" wire:click="finishWork"
                class="flex-1 py-3 bg-green-500 text-white rounded-xl text-sm font-medium hover:bg-green-600 transition-colors">
                &#x2705; Tandai Selesai
            </button>
            @endif
            <a href="/mobile/scanner?wo={{ $wo->id }}" class="px-4 py-3 bg-slate-700 text-slate-300 rounded-xl text-sm font-medium">
                &#x1F4F7; Scan
            </a>
        </div>
    @endif

    {{-- Toast --}}
    <div x-data="{ show:false, msg:'', type:'success' }"
         x-on:notify.window="show=true; msg=$event.detail.message; type=$event.detail.type; setTimeout(()=>show=false,3000)"
         x-show="show" x-transition
         :class="type==='success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed bottom-24 right-4 left-4 text-white px-5 py-3 rounded-2xl shadow-lg text-sm font-medium z-50 text-center">
        <span x-text="msg"></span>
    </div>

</div>

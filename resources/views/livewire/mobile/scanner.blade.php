<div class="p-4 space-y-4">

    @if(!$showResult)
    {{-- SCANNER VIEW --}}
    <div class="text-center space-y-4">
        <h2 class="text-lg font-bold text-white">&#x1F4F7; Scan Barcode / QR</h2>
        <p class="text-slate-400 text-sm">Arahkan kamera ke barcode atau QR code sparepart</p>

        {{-- Camera Preview --}}
        <div class="relative bg-slate-800 rounded-2xl overflow-hidden" style="height:300px"
             x-data="barcodeScanner()"
             x-init="init()"
             x-on:scanned="$wire.onScanned($event.detail)">

            <video id="preview" class="w-full h-full object-cover" autoplay muted playsinline></video>

            {{-- Scan overlay --}}
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-56 h-56 border-2 border-amber-400 rounded-2xl relative">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-amber-400 rounded-tl-xl"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-amber-400 rounded-tr-xl"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-amber-400 rounded-bl-xl"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-amber-400 rounded-br-xl"></div>
                    <div class="absolute top-1/2 -translate-y-0.5 inset-x-0 h-0.5 bg-amber-400 opacity-70 animate-pulse"></div>
                </div>
            </div>

            <div x-show="error" class="absolute bottom-4 inset-x-4 bg-red-500/80 text-white text-xs text-center py-2 rounded-xl" x-text="error"></div>
        </div>

        {{-- Manual Input --}}
        <div class="flex gap-2">
            <input wire:model="scannedCode" type="text" placeholder="Atau ketik kode manual..."
                class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
            <button type="button" wire:click="onScanned('{{ $scannedCode }}')"
                class="px-4 py-3 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600">
                &#x1F50D;
            </button>
        </div>
    </div>
    @else
    {{-- RESULT VIEW --}}
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <button type="button" wire:click="resetScan" class="text-slate-400 hover:text-white">
                &#x2190; Scan Lagi
            </button>
        </div>

        {{-- Produk Ditemukan --}}
        @if($foundProduct)
        <div class="bg-slate-800 rounded-2xl p-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-amber-400/20 rounded-xl flex items-center justify-center text-2xl">&#x1F4E6;</div>
                <div>
                    <p class="font-bold text-white">{{ $foundProduct->name }}</p>
                    <p class="text-xs text-slate-400">SKU: {{ $foundProduct->sku }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div class="bg-slate-700 rounded-xl p-3 text-center">
                    <p class="text-xs text-slate-400">Harga Jual</p>
                    <p class="font-bold text-amber-400">Rp {{ number_format($foundProduct->sell_price,0,',','.') }}</p>
                </div>
                <div class="bg-slate-700 rounded-xl p-3 text-center">
                    <p class="text-xs text-slate-400">Stok Cabang</p>
                    <p class="font-bold {{ ($stock?->quantity ?? 0) > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $stock?->quantity ?? 0 }} unit
                    </p>
                </div>
            </div>
        </div>

        @if($addedToWo)
        <div class="bg-green-500/20 border border-green-500/40 rounded-2xl p-4 text-center">
            <p class="text-green-400 font-semibold">&#x2705; Berhasil ditambahkan ke Work Order!</p>
        </div>
        @else
        {{-- Tambah ke WO --}}
        <div class="bg-slate-800 rounded-2xl p-4 space-y-3">
            <p class="text-sm font-semibold text-white">Tambahkan ke Work Order</p>
            <select wire:model="selectedWoId"
                class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-3 text-white text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
                <option value="">-- Pilih Work Order Aktif --</option>
                @foreach($activeWorkOrders as $wo)
                <option value="{{ $wo->id }}">{{ $wo->wo_number }} - {{ $wo->vehicle?->plate_number }}</option>
                @endforeach
            </select>
            @error('selectedWoId') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            <div class="flex gap-3">
                <div class="flex-1">
                    <label class="text-xs text-slate-400 block mb-1">Qty</label>
                    <input wire:model="qty" type="number" min="1"
                        class="w-full bg-slate-700 border border-slate-600 rounded-xl px-4 py-3 text-white text-center focus:ring-2 focus:ring-amber-400 focus:outline-none">
                </div>
                <div class="flex-1 flex flex-col justify-end">
                    <button type="button" wire:click="addToWorkOrder"
                        class="w-full py-3 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 transition-colors">
                        &#x2795; Tambah ke WO
                    </button>
                </div>
            </div>
        </div>
        @endif
        @else
        {{-- Produk Tidak Ditemukan --}}
        <div class="bg-red-500/20 border border-red-500/40 rounded-2xl p-6 text-center">
            <p class="text-4xl mb-3">&#x274C;</p>
            <p class="text-red-400 font-semibold">Produk tidak ditemukan</p>
            <p class="text-slate-400 text-sm mt-1">Kode: <span class="font-mono text-amber-400">{{ $scannedCode }}</span></p>
        </div>
        @endif
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

@push('scripts')
<script>
function barcodeScanner() {
    return {
        error: '',
        init() {
            // Gunakan jsQR library jika tersedia (tambahkan via CDN di layout)
            if (!navigator.mediaDevices) {
                this.error = 'Kamera tidak didukung di browser ini';
                return;
            }
            const video = document.getElementById('preview');
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(stream => {
                    video.srcObject = stream;
                    video.play();
                    this.scanLoop(video);
                })
                .catch(() => { this.error = 'Izin kamera ditolak'; });
        },
        scanLoop(video) {
            if (typeof jsQR === 'undefined') return;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const scan = () => {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    if (code) {
                        this.$dispatch('scanned', code.data);
                        return; // Stop scanning after finding a code
                    }
                }
                requestAnimationFrame(scan);
            };
            requestAnimationFrame(scan);
        }
    }
}
</script>
@endpush

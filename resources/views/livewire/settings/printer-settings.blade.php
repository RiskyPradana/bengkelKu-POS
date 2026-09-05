<div class="p-4 space-y-5 md:p-6 max-w-3xl">

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Printer</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
            Atur printer untuk cetak laporan (kertas A4, printer inkjet seperti Epson/Canon) dan printer struk kasir (printer thermal 58mm/80mm).
        </p>
    </div>

    @if (session('sukses'))
        <div class="flex items-start gap-3 p-4 text-sm border rounded-lg bg-emerald-50 border-emerald-200 text-emerald-800">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">

        {{-- Printer Laporan (A4) --}}
        <div class="p-5 space-y-4 bg-white border rounded-xl dark:bg-slate-900 border-gray-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Printer Laporan (A4)</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Untuk cetak Laporan Keuangan — printer inkjet biasa (Epson, Canon, HP, dll).</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-gray-100 dark:border-slate-800">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nama Printer <span class="text-gray-400 dark:text-slate-500">(opsional, sesuai driver di komputer)</span></label>
                    <input type="text" wire:model="reportPrinterName" placeholder="contoh: EPSON L3110 Series"
                           class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    @error('reportPrinterName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Ukuran Kertas</label>
                    <select wire:model="reportPaperSize"
                            class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        <option value="A4">A4</option>
                        <option value="Letter">Letter</option>
                        <option value="F4">F4 / Folio</option>
                    </select>
                    @error('reportPaperSize') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Orientasi</label>
                    <select wire:model="reportOrientation"
                            class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        <option value="portrait">Potret (Portrait)</option>
                        <option value="landscape">Lanskap (Landscape)</option>
                    </select>
                    @error('reportOrientation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-slate-500">
                Nama printer di sini hanya sebagai catatan/label — pemilihan printer fisik &amp; driver tetap lewat kotak dialog cetak bawaan browser saat tombol "Cetak Laporan" ditekan.
            </p>
        </div>

        {{-- Printer Struk (Thermal) --}}
        <div class="p-5 space-y-4 bg-white border rounded-xl dark:bg-slate-900 border-gray-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17H7a2 2 0 01-2-2v-4a2 2 0 012-2h10a2 2 0 012 2v4a2 2 0 01-2 2h-2m-6 0v3a1 1 0 001 1h4a1 1 0 001-1v-3m-6 0h6M8 9V5a1 1 0 011-1h6a1 1 0 011 1v4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Printer Struk (Thermal)</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Untuk cetak struk kasir — printer thermal kertas 58mm (umum disebut "58x40").</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-gray-100 dark:border-slate-800">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Nama Printer <span class="text-gray-400 dark:text-slate-500">(opsional)</span></label>
                    <input type="text" wire:model="receiptPrinterName" placeholder="contoh: EPPOS Thermal 58mm"
                           class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    @error('receiptPrinterName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Lebar Kertas</label>
                    <select wire:model="receiptPaperWidth"
                            class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                        <option value="58">58mm (umum disebut 58x40)</option>
                        <option value="80">80mm</option>
                    </select>
                    @error('receiptPaperWidth') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Ukuran Font Struk (px)</label>
                    <input type="number" min="8" max="20" wire:model="receiptFontSize"
                           class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    @error('receiptFontSize') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2.5 pt-6">
                    <input type="checkbox" wire:model="receiptAutoCut"
                           class="text-blue-600 border-gray-300 dark:border-slate-700 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-slate-200">Potong otomatis (auto-cut) setelah cetak, jika printer mendukung</span>
                </label>
            </div>
            <p class="text-xs text-gray-400 dark:text-slate-500">
                58mm ("58x40") adalah ukuran thermal paling umum untuk struk kasir kecil; kertas 80mm biasa dipakai untuk struk yang lebih lebar/detail.
            </p>
        </div>

        <div>
            <button type="submit" wire:loading.attr="disabled"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>

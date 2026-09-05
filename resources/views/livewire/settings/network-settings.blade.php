<div class="p-4 space-y-5 md:p-6 max-w-3xl">

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Jaringan Lokal (LAN)</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
            Atur akses BengkelOS lewat WiFi/jaringan lokal saat internet mati, supaya mekanik &amp; kasir tetap bisa memakai aplikasi dari HP/laptop yang terhubung ke WiFi yang sama dengan server.
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

    {{-- Kartu URL akses LAN --}}
    <div class="p-5 border rounded-xl bg-slate-900 text-white shadow-sm">
        <p class="text-xs font-semibold tracking-wider uppercase text-slate-400">Alamat Akses LAN Saat Ini</p>
        <p class="mt-2 text-2xl font-bold font-mono break-all">{{ $this->lanUrl }}</p>
        <p class="mt-2 text-sm text-slate-300">
            Bagikan alamat ini ke HP/laptop mekanik &amp; kasir — pastikan perangkat itu terhubung ke WiFi yang sama dengan server BengkelOS. Kalau internet dari ISP mati tapi WiFi/router lokal masih menyala, aplikasi tetap bisa diakses lewat alamat ini.
        </p>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="p-5 space-y-4 bg-white border rounded-xl dark:bg-slate-900 border-gray-200 dark:border-slate-800">
            <label class="flex items-center gap-2.5">
                <input type="checkbox" wire:model="lanEnabled"
                       class="text-blue-600 border-gray-300 rounded dark:border-slate-700 focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700 dark:text-slate-200">Aktifkan mode akses jaringan lokal (LAN)</span>
            </label>
            <p class="text-xs text-gray-500 dark:text-slate-400 pl-6">
                Kalau dinonaktifkan, alamat LAN di atas masih bisa dicoba diakses (tergantung cara server dijalankan), tapi sistem tidak akan menyarankannya sebagai mode offline resmi ke pengguna.
            </p>

            <div class="grid gap-4 sm:grid-cols-3 pt-2 border-t border-gray-100 dark:border-slate-800">
                <div class="sm:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Alamat IP Server</label>
                    <input type="text" wire:model="lanIp" placeholder="contoh: 192.168.1.5"
                           class="w-full font-mono text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    @error('lanIp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">
                        Terdeteksi otomatis: <span class="font-mono">{{ $detectedIp }}</span>
                        <button type="button" wire:click="refreshDetectedIp" class="ml-1 font-medium text-blue-600 hover:underline">deteksi ulang</button>
                    </p>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Port</label>
                    <input type="text" wire:model="lanPort" placeholder="8000"
                           class="w-full font-mono text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    @error('lanPort') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>

    <div class="p-5 space-y-3 text-sm bg-amber-50 border border-amber-200 rounded-xl dark:bg-amber-500/10 dark:border-amber-500/30 text-amber-900 dark:text-amber-200">
        <p class="font-semibold">Cara memakai mode akses LAN saat internet mati:</p>
        <ol class="pl-5 space-y-1.5 list-decimal">
            <li>Pastikan server (komputer/laptop tempat BengkelOS berjalan) dan HP/laptop mekanik terhubung ke WiFi/router yang sama (WiFi lokal biasanya tetap berfungsi meski internet dari ISP putus).</li>
            <li>Di server, jalankan aplikasi dengan alamat yang bisa diakses perangkat lain, contoh: <code class="px-1 bg-amber-100 dark:bg-amber-500/20 rounded font-mono">php artisan serve --host=0.0.0.0 --port={{ $lanPort ?: 8000 }}</code> (jangan pakai <code class="px-1 bg-amber-100 dark:bg-amber-500/20 rounded font-mono">127.0.0.1</code>, karena itu hanya bisa diakses dari server itu sendiri).</li>
            <li>Di HP/laptop mekanik, buka browser dan ketik alamat: <span class="font-mono font-semibold">{{ $this->lanUrl }}</span></li>
            <li>Kalau server berjalan lewat Nginx/Apache (produksi), pastikan servernya juga mendengarkan di semua alamat (<code class="px-1 bg-amber-100 dark:bg-amber-500/20 rounded font-mono">0.0.0.0</code>) dan port di atas dibuka di firewall.</li>
        </ol>
    </div>
</div>

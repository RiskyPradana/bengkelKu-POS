<div class="p-4 space-y-6" x-data="dashboardCharts()" x-init="init()"
     wire:key="dash-{{ $kpi['periode'] }}-{{ $branchId ?? 'all' }}">

    {{-- ══════════════ FILTER ══════════════ --}}
    <div class="flex flex-wrap items-end gap-3 p-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
        <div>
            <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-slate-400">Periode</label>
            <select wire:model.live="preset"
                    class="px-3 py-2 text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="hari_ini">Hari Ini</option>
                <option value="minggu_ini">Minggu Ini</option>
                <option value="bulan_ini">Bulan Ini</option>
                <option value="bulan_lalu">Bulan Lalu</option>
                <option value="akhir_30hari">30 Hari Terakhir</option>
                <option value="tahun_ini">Tahun Ini</option>
                <option value="kustom">Kustom</option>
            </select>
        </div>

        <div>
            <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-slate-400">Dari</label>
            <input type="date" wire:model.live="startDate"
                   class="px-3 py-2 text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-slate-400">Sampai</label>
            <input type="date" wire:model.live="endDate"
                   class="px-3 py-2 text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        @if ($branches->count() > 1)
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500 dark:text-slate-400">Cabang</label>
                <select wire:model.live="branchId"
                        class="px-3 py-2 text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="ml-auto text-sm text-gray-500 dark:text-slate-400">
            <span wire:loading.remove>Periode: <b class="text-gray-700 dark:text-slate-200">{{ $kpi['periode'] }}</b></span>
            <span wire:loading class="text-blue-600">Memuat data...</span>
        </div>
    </div>

    {{-- ══════════════ KARTU KPI ══════════════ --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

        @php
            $cards = [
                [
                    'label'  => 'Total Omzet',
                    'value'  => 'Rp ' . number_format($kpi['omzet'], 0, ',', '.'),
                    'growth' => $kpi['omzet_growth'],
                    'color'  => 'blue',
                    'icon'   => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'label'  => 'Jumlah Transaksi',
                    'value'  => number_format($kpi['transaksi'], 0, ',', '.') . ' nota',
                    'growth' => $kpi['transaksi_growth'],
                    'color'  => 'emerald',
                    'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                ],
                [
                    'label'  => 'Rata-rata / Transaksi',
                    'value'  => 'Rp ' . number_format($kpi['rata_transaksi'], 0, ',', '.'),
                    'growth' => null,
                    'color'  => 'violet',
                    'icon'   => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                ],
                [
                    'label'  => 'Work Order',
                    'value'  => number_format($kpi['work_order'], 0, ',', '.') . ' WO',
                    'growth' => null,
                    'color'  => 'amber',
                    'icon'   => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                ],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="p-5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 dark:text-slate-400 uppercase">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</p>

                        @if (! is_null($card['growth']))
                            @php $up = $card['growth'] >= 0; @endphp
                            <p @class([
                                'mt-2 text-xs font-semibold flex items-center gap-1',
                                'text-emerald-600' => $up,
                                'text-red-600' => ! $up,
                            ])>
                                <span>{{ $up ? '▲' : '▼' }}</span>
                                {{ number_format(abs($card['growth']), 1) }}%
                                <span class="font-normal text-gray-400 dark:text-slate-500">vs periode lalu</span>
                            </p>
                        @endif
                    </div>

                    <div class="p-2.5 rounded-lg bg-{{ $card['color'] }}-50">
                        <svg class="w-5 h-5 text-{{ $card['color'] }}-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════ BARIS CHART 1 ══════════════ --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Tren Omzet --}}
        <div class="p-5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tren Omzet Harian</h3>
                <span class="text-xs text-gray-400 dark:text-slate-500">{{ $kpi['periode'] }}</span>
            </div>
            <div class="h-72">
                <canvas id="chartTrend"></canvas>
            </div>
        </div>

        {{-- Komposisi Omzet --}}
        <div class="p-5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
            <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Jasa vs Sparepart</h3>
            <div class="h-56">
                <canvas id="chartComposition"></canvas>
            </div>
            <div class="pt-4 mt-4 space-y-2 border-t border-gray-100 dark:border-slate-800">
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600 dark:text-slate-300">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span> Jasa Servis
                    </span>
                    <b class="text-gray-900 dark:text-white">Rp {{ number_format($kpi['omzet_jasa'], 0, ',', '.') }}</b>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600 dark:text-slate-300">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span> Sparepart
                    </span>
                    <b class="text-gray-900 dark:text-white">Rp {{ number_format($kpi['omzet_sparepart'], 0, ',', '.') }}</b>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ BARIS CHART 2 ══════════════ --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Jam Sibuk --}}
        <div class="p-5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
            <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Jam Sibuk Bengkel</h3>
            <p class="mb-4 text-xs text-gray-500 dark:text-slate-400">Berguna untuk mengatur jadwal shift mekanik</p>
            <div class="h-64">
                <canvas id="chartBusyHours"></canvas>
            </div>
        </div>

        {{-- Perbandingan Cabang --}}
        <div class="p-5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
            <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Perbandingan Cabang</h3>
            <p class="mb-4 text-xs text-gray-500 dark:text-slate-400">Omzet per cabang pada periode ini</p>
            <div class="h-64">
                <canvas id="chartBranch"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════ TABEL: SPAREPART & JASA ══════════════ --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Sparepart Terlaris --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                <h3 class="font-semibold text-gray-900 dark:text-white">Sparepart Terlaris</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-slate-400 uppercase bg-gray-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-5 py-2.5 text-left font-medium">Nama</th>
                            <th class="px-5 py-2.5 text-right font-medium">Qty</th>
                            <th class="px-5 py-2.5 text-right font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @forelse ($topParts as $i => $part)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-blue-700 bg-blue-50 rounded">
                                            {{ $i + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $part['nama'] }}</p>
                                            <p class="text-xs text-gray-400 dark:text-slate-500">{{ $part['sku'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($part['qty'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-gray-600 dark:text-slate-300">Rp {{ number_format($part['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 dark:text-slate-500">Belum ada penjualan sparepart pada periode ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Jasa Terpopuler --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                <h3 class="font-semibold text-gray-900 dark:text-white">Jasa Paling Sering</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-slate-400 uppercase bg-gray-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-5 py-2.5 text-left font-medium">Jenis Jasa</th>
                            <th class="px-5 py-2.5 text-right font-medium">Frekuensi</th>
                            <th class="px-5 py-2.5 text-right font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @forelse ($topServices as $i => $service)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-emerald-700 bg-emerald-50 rounded">
                                            {{ $i + 1 }}
                                        </span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $service['nama'] }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ $service['jumlah'] }}x</td>
                                <td class="px-5 py-3 text-right text-gray-600 dark:text-slate-300">Rp {{ number_format($service['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 dark:text-slate-500">Belum ada jasa tercatat pada periode ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════ PERFORMA MEKANIK ══════════════ --}}
    <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800">
            <h3 class="font-semibold text-gray-900 dark:text-white">Performa Mekanik</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-slate-400">Diurutkan berdasarkan total nilai jasa yang dikerjakan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 dark:text-slate-400 uppercase bg-gray-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-2.5 text-left font-medium">Mekanik</th>
                        <th class="px-5 py-2.5 text-right font-medium">WO Selesai</th>
                        <th class="px-5 py-2.5 text-right font-medium">Total Jasa</th>
                        <th class="px-5 py-2.5 text-right font-medium">Rata / WO</th>
                        <th class="px-5 py-2.5 text-right font-medium">Komisi</th>
                        <th class="px-5 py-2.5 text-left font-medium w-40">Kontribusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @php $maxJasa = collect($mechanics)->max('total_jasa') ?: 1; @endphp

                    @forelse ($mechanics as $i => $m)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex items-center justify-center w-7 h-7 text-xs font-bold text-white bg-gradient-to-br from-blue-500 to-blue-600 rounded-full">
                                        {{ strtoupper(substr($m['nama'], 0, 2)) }}
                                    </span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $m['nama'] }}</span>
                                    @if ($i === 0)
                                        <span class="px-1.5 py-0.5 text-xs font-semibold text-amber-700 bg-amber-100 rounded">Terbaik</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ $m['total_wo'] }}</td>
                            <td class="px-5 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($m['total_jasa'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-gray-500 dark:text-slate-400">Rp {{ number_format($m['rata_jasa'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($m['total_komisi'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <div class="w-full h-2 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full"
                                         style="width: {{ round(($m['total_jasa'] / $maxJasa) * 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400 dark:text-slate-500">Belum ada data komisi mekanik pada periode ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════ PELANGGAN & STOK ══════════════ --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Pelanggan Loyal --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                <h3 class="font-semibold text-gray-900 dark:text-white">Pelanggan Teratas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-slate-400 uppercase bg-gray-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-5 py-2.5 text-left font-medium">Nama</th>
                            <th class="px-5 py-2.5 text-right font-medium">Kunjungan</th>
                            <th class="px-5 py-2.5 text-right font-medium">Total Belanja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @forelse ($topCustomers as $c)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $c['nama'] }}</p>
                                    <p class="text-xs text-gray-400 dark:text-slate-500">{{ $c['telepon'] }}</p>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ $c['kunjungan'] }}x</td>
                                <td class="px-5 py-3 text-right text-gray-600 dark:text-slate-300">Rp {{ number_format($c['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400 dark:text-slate-500">Belum ada data pelanggan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Peringatan Stok --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                <h3 class="font-semibold text-gray-900 dark:text-white">Perlu Restock Segera</h3>
                @if (count($lowStock) > 0)
                    <span class="px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">{{ count($lowStock) }}</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-slate-400 uppercase bg-gray-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-5 py-2.5 text-left font-medium">Sparepart</th>
                            <th class="px-5 py-2.5 text-right font-medium">Sisa</th>
                            <th class="px-5 py-2.5 text-right font-medium">Min</th>
                            <th class="px-5 py-2.5 text-right font-medium">Kurang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @forelse ($lowStock as $s)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $s['nama'] }}</p>
                                    <p class="text-xs text-gray-400 dark:text-slate-500">{{ $s['sku'] }} &middot; {{ $s['cabang'] }}</p>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span @class([
                                        'px-2 py-0.5 rounded text-xs font-bold',
                                        'bg-red-100 text-red-700' => $s['quantity'] <= 0,
                                        'bg-amber-100 text-amber-700' => $s['quantity'] > 0,
                                    ])>{{ $s['quantity'] }}</span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-500 dark:text-slate-400">{{ $s['min_stock'] }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-red-600">{{ $s['kurang'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-emerald-600">Semua stok aman</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════ SCRIPT CHART ══════════════ --}}
    @script
    <script>
        Alpine.data('dashboardCharts', () => ({
            charts: {},

            init() {
                this.render();

                // Gambar ulang chart setiap Livewire selesai update filter
                Livewire.hook('morph.updated', () => {
                    this.$nextTick(() => this.render());
                });
            },

            destroyAll() {
                Object.values(this.charts).forEach((c) => c && c.destroy());
                this.charts = {};
            },

            rupiah(value) {
                return 'Rp ' + Number(value).toLocaleString('id-ID');
            },

            render() {
                if (typeof Chart === 'undefined') {
                    console.warn('Chart.js belum dimuat. Tambahkan CDN di layouts/app.blade.php');
                    return;
                }

                this.destroyAll();

                const rupiah = this.rupiah;
                const trend = @json($trend);
                const composition = @json($composition);
                const busy = @json($busyHours);
                const branch = @json($branchCompare);

                Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
                Chart.defaults.font.size = 11;

                // ── 1. Tren Omzet (Line) ──
                const elTrend = document.getElementById('chartTrend');
                if (elTrend) {
                    this.charts.trend = new Chart(elTrend, {
                        type: 'line',
                        data: {
                            labels: trend.labels,
                            datasets: [{
                                label: 'Omzet',
                                data: trend.omzet,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => rupiah(ctx.parsed.y),
                                        afterLabel: (ctx) => trend.transaksi[ctx.dataIndex] + ' transaksi',
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (v) => 'Rp ' + (v >= 1000000
                                            ? (v / 1000000).toFixed(1) + ' jt'
                                            : (v / 1000).toFixed(0) + ' rb'),
                                    },
                                    grid: { color: '#f1f5f9' },
                                },
                                x: { grid: { display: false } },
                            },
                        },
                    });
                }

                // ── 2. Komposisi Omzet (Doughnut) ──
                const elComp = document.getElementById('chartComposition');
                if (elComp) {
                    this.charts.composition = new Chart(elComp, {
                        type: 'doughnut',
                        data: {
                            labels: composition.labels,
                            datasets: [{
                                data: composition.values,
                                backgroundColor: ['#3b82f6', '#f59e0b'],
                                borderWidth: 0,
                                hoverOffset: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                                            const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                            return ctx.label + ': ' + rupiah(ctx.parsed) + ' (' + pct + '%)';
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                // ── 3. Jam Sibuk (Bar) ──
                const elBusy = document.getElementById('chartBusyHours');
                if (elBusy) {
                    const maxVal = Math.max(...busy.values, 1);
                    this.charts.busy = new Chart(elBusy, {
                        type: 'bar',
                        data: {
                            labels: busy.labels,
                            datasets: [{
                                label: 'Transaksi',
                                data: busy.values,
                                backgroundColor: busy.values.map((v) =>
                                    v >= maxVal * 0.8 ? '#ef4444' : v >= maxVal * 0.5 ? '#f59e0b' : '#93c5fd'),
                                borderRadius: 4,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { label: (ctx) => ctx.parsed.y + ' transaksi' } },
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } },
                            },
                        },
                    });
                }

                // ── 4. Perbandingan Cabang (Horizontal Bar) ──
                const elBranch = document.getElementById('chartBranch');
                if (elBranch) {
                    this.charts.branch = new Chart(elBranch, {
                        type: 'bar',
                        data: {
                            labels: branch.map((b) => b.nama),
                            datasets: [{
                                label: 'Omzet',
                                data: branch.map((b) => b.total),
                                backgroundColor: '#8b5cf6',
                                borderRadius: 4,
                                barThickness: 22,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => rupiah(ctx.parsed.x),
                                        afterLabel: (ctx) => branch[ctx.dataIndex].jumlah + ' transaksi',
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (v) => v >= 1000000
                                            ? (v / 1000000).toFixed(1) + ' jt'
                                            : (v / 1000).toFixed(0) + ' rb',
                                    },
                                    grid: { color: '#f1f5f9' },
                                },
                                y: { grid: { display: false } },
                            },
                        },
                    });
                }
            },
        }));
    </script>
    @endscript
</div>

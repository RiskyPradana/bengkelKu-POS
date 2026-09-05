@php
$revenue = $this->revenueStats;
$woStats = $this->workOrderStatsByStatus;
$topSvc  = $this->topServices;
$topProd = $this->topProducts;

$months = $this->months;
$years  = $this->years;

$printSettings = $this->reportPrintSettings;

$statusColors = [
    'Pending'     => 'bg-amber-100 text-amber-700',
    'In Progress' => 'bg-blue-100 text-blue-700',
    'Completed'   => 'bg-emerald-100 text-emerald-700',
    'Paid'        => 'bg-violet-100 text-violet-700',
];
@endphp

<div id="laporan-print-area">

{{-- Sesi 12: CSS cetak laporan mengikuti Pengaturan Printer (ukuran kertas A4/Letter/F4 & orientasi),
     dan menyembunyikan sidebar/topbar/filter saat dicetak. --}}
<style>
    @media print {
        @page { size: {{ $printSettings['paper_size'] }} {{ $printSettings['orientation'] }}; margin: 12mm; }
        body * { visibility: hidden; }
        #laporan-print-area, #laporan-print-area * { visibility: visible; }
        #laporan-print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>

{{-- ===== PERIOD FILTER ===== --}}
<div class="flex flex-wrap items-center gap-3 mb-6 no-print">
    <div class="flex items-center gap-2 bg-white rounded-xl border border-slate-200 px-4 py-2.5 shadow-sm">
        <span class="text-slate-400 text-sm">📅</span>
        <select wire:model.live="month" class="text-sm font-semibold text-slate-700 bg-transparent border-none focus:outline-none pr-1">
            @foreach($months as $num => $name)
            <option value="{{ $num }}">{{ $name }}</option>
            @endforeach
        </select>
        <select wire:model.live="year" class="text-sm font-semibold text-slate-700 bg-transparent border-none focus:outline-none">
            @foreach($years as $y)
            <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-center gap-2">
        @foreach([1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'] as $m => $lbl)
        <button wire:click="$set('month', {{ $m }})"
                class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg transition-colors
                       {{ $month == $m ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50' }}">
            {{ $lbl }}
        </button>
        @endforeach
    </div>
    <button type="button" onclick="window.print()"
            class="ml-auto flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak Laporan ({{ $printSettings['paper_size'] }})
    </button>
</div>

{{-- ===== REVENUE KPI CARDS ===== --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 text-white rounded-2xl shadow-lg p-6 xl:col-span-2">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-3">💵 Total Omzet Periode Ini</p>
        <p class="font-display text-[42px] font-bold text-amber-400 leading-none">
            Rp {{ number_format($revenue['revenue'], 0, ',', '.') }}
        </p>
        <p class="text-xs text-slate-400 mt-2">
            {{ $months[$month] }} {{ $year }} &middot; {{ $revenue['invoice_count'] }} invoice lunas
        </p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-t-4 border-emerald-500">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Rata-rata per Invoice</p>
        <p class="font-display text-3xl font-bold text-emerald-600">
            Rp {{ number_format($revenue['avg_per_inv'], 0, ',', '.') }}
        </p>
        <p class="text-xs text-slate-400 mt-1">{{ $revenue['invoice_count'] }} transaksi</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-t-4 border-rose-400">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Total Diskon</p>
        <p class="font-display text-3xl font-bold text-rose-500">
            Rp {{ number_format($revenue['discount'], 0, ',', '.') }}
        </p>
        <p class="text-xs text-slate-400 mt-1">Pajak: Rp {{ number_format($revenue['tax'], 0, ',', '.') }}</p>
    </div>
</div>

{{-- ===== WO STATUS + TOP ITEMS ===== --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">

    {{-- WO by Status --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-display text-[19px] font-bold text-slate-900">Work Order per Status</h3>
            <p class="text-[11px] text-slate-400">{{ $months[$month] }} {{ $year }}</p>
        </div>
        <div class="p-5">
            @if($woStats->isEmpty())
            <div class="text-center py-8 text-slate-300">
                <div class="text-3xl mb-2">📋</div>
                <p class="text-sm">Belum ada data</p>
            </div>
            @else
            @php $woTotal = $woStats->sum('total'); @endphp
            <div class="space-y-3">
                @foreach($woStats as $row)
                @php
                $st  = $row->status instanceof \App\Domains\WorkOrder\Enums\WorkOrderStatus ? $row->status->value : (string)$row->status;
                $pct = $woTotal > 0 ? round(($row->total / $woTotal) * 100) : 0;
                $barColor = ['Pending'=>'bg-amber-400','In Progress'=>'bg-blue-500','Completed'=>'bg-emerald-500','Paid'=>'bg-violet-500'][$st] ?? 'bg-slate-400';
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $statusColors[$st] ?? 'bg-gray-100 text-gray-600' }}">{{ $st }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono-jet text-sm font-bold text-slate-800">{{ $row->total }}</span>
                            <span class="text-[11px] text-slate-400 ml-1.5">{{ $pct }}%</span>
                        </div>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Total WO bulan ini</span>
                    <span class="font-mono-jet text-sm font-bold text-slate-900">{{ $woTotal }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Top Services --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-display text-[19px] font-bold text-slate-900">🔧 Top Jasa Servis</h3>
            <p class="text-[11px] text-slate-400">{{ $months[$month] }} {{ $year }}</p>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($topSvc as $i => $item)
            <div class="px-5 py-3.5 flex items-center gap-4">
                <span class="font-display text-2xl font-bold text-slate-200 w-7 shrink-0 text-center">#{{ $i + 1 }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-slate-900 truncate">{{ $item->name }}</p>
                    <p class="text-[11px] text-slate-400">{{ $item->total_qty }}x dikerjakan</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-mono-jet text-sm font-bold text-emerald-600">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-10 text-slate-300 px-5">
                <div class="text-3xl mb-2">🔧</div>
                <p class="text-sm">Belum ada data jasa</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Top Products --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-display text-[19px] font-bold text-slate-900">📦 Top Sparepart</h3>
            <p class="text-[11px] text-slate-400">{{ $months[$month] }} {{ $year }}</p>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($topProd as $i => $item)
            <div class="px-5 py-3.5 flex items-center gap-4">
                <span class="font-display text-2xl font-bold text-slate-200 w-7 shrink-0 text-center">#{{ $i + 1 }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-slate-900 truncate">{{ $item->name }}</p>
                    <p class="text-[11px] text-slate-400">{{ $item->total_qty }} unit terjual</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-mono-jet text-sm font-bold text-blue-600">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-10 text-slate-300 px-5">
                <div class="text-3xl mb-2">📦</div>
                <p class="text-sm">Belum ada data sparepart</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===== INVOICE DETAIL TABLE ===== --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-display text-[20px] font-bold text-slate-900">Invoice Lunas — {{ $months[$month] }} {{ $year }}</h3>
        <span class="text-xs text-slate-400">{{ $revenue['invoice_count'] }} invoice</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
            <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                <th class="text-left px-5 py-3">No Invoice</th>
                <th class="text-left px-5 py-3">Pelanggan</th>
                <th class="text-left px-5 py-3">Kendaraan</th>
                <th class="text-right px-5 py-3">Diskon</th>
                <th class="text-right px-5 py-3">Pajak</th>
                <th class="text-right px-5 py-3">Grand Total</th>
                <th class="text-left px-5 py-3">Tgl Bayar</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse(\App\Domains\POS\Models\Invoice::with(['workOrder.customer','workOrder.vehicle'])
                ->where('status', \App\Domains\POS\Enums\InvoiceStatus::Paid)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->limit(30)
                ->get() as $inv)
            <tr class="hover:bg-stone-50 transition-colors">
                <td class="px-5 py-3.5">
                    <span class="font-mono-jet text-[11px] font-bold text-slate-500 uppercase">№{{ strtoupper(substr($inv->id, -8)) }}</span>
                </td>
                <td class="px-5 py-3.5 font-medium text-slate-900">
                    {{ $inv->workOrder?->customer?->name ?? '—' }}
                </td>
                <td class="px-5 py-3.5">
                    <span class="font-mono-jet text-xs font-bold text-slate-700">
                        {{ $inv->workOrder?->vehicle?->plate_number ?? '—' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    @if($inv->discount > 0)
                    <span class="font-mono-jet text-xs text-rose-500">-Rp {{ number_format($inv->discount, 0, ',', '.') }}</span>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    @if($inv->tax > 0)
                    <span class="font-mono-jet text-xs text-slate-500">Rp {{ number_format($inv->tax, 0, ',', '.') }}</span>
                    @else
                    <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono-jet text-sm font-bold text-emerald-600">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3.5 text-slate-400 text-xs whitespace-nowrap">
                    {{ $inv->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-14 text-slate-400">
                    <div class="text-4xl mb-3 opacity-40">🧾</div>
                    <p class="font-medium">Belum ada invoice lunas</p>
                    <p class="text-xs mt-1">Periode {{ $months[$month] }} {{ $year }}</p>
                </td>
            </tr>
            @endforelse
            </tbody>
            @if($revenue['invoice_count'] > 0)
            <tfoot class="bg-slate-50 border-t border-slate-200">
            <tr>
                <td colspan="5" class="px-5 py-3.5 text-right font-semibold text-sm text-slate-700">Total Omzet:</td>
                <td class="px-5 py-3.5 text-right">
                    <span class="font-mono-jet text-base font-bold text-emerald-600">
                        Rp {{ number_format($revenue['revenue'], 0, ',', '.') }}
                    </span>
                </td>
                <td class="px-5 py-3.5"></td>
            </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
</div>{{-- /#laporan-print-area --}}

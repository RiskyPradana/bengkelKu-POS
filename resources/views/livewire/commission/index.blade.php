<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">&#x1F4B0; Komisi Mekanik</h1>
            <p class="text-sm text-slate-500 mt-0.5">2 profit bulanan: komisi kendaraan masuk + bonus KPI target</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <input wire:model.live="period" type="month"
                class="border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none">
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $cards = [
                ['icon'=>'&#x1F527;','label'=>'Total WO Selesai','value'=>number_format($this->totals['total_wo'])],
                ['icon'=>'&#x1F6E0;','label'=>'Total Jasa','value'=>'Rp '.number_format($this->totals['total_jasa'],0,',','.')],
                ['icon'=>'&#x1F697;','label'=>'Komisi Kendaraan Masuk','value'=>'Rp '.number_format($this->totals['commission_vehicle'],0,',','.')],
                ['icon'=>'&#x1F3C6;','label'=>'Bonus KPI','value'=>'Rp '.number_format($this->totals['commission_kpi'],0,',','.')],
                ['icon'=>'&#x1F4B5;','label'=>'Total Komisi','value'=>'Rp '.number_format($this->totals['total_commission'],0,',','.')],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-2xl mb-2">{!! $card['icon'] !!}</p>
            <p class="text-xl font-bold text-slate-800">{{ $card['value'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ $card['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Tabel Komisi per Mekanik --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-800">Rincian per Mekanik &mdash; {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y') }}</h2>
            <p class="text-xs text-slate-400 mt-1">Atur target KPI & bonus per mekanik di menu Manajemen User.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Mekanik</th>
                        <th class="px-6 py-3 text-center">WO Selesai</th>
                        <th class="px-6 py-3 text-right">Total Jasa</th>
                        <th class="px-6 py-3 text-right">Komisi Kendaraan</th>
                        <th class="px-6 py-3 text-center">Target KPI</th>
                        <th class="px-6 py-3 text-right">Bonus KPI</th>
                        <th class="px-6 py-3 text-right">Total Komisi</th>
                        <th class="px-6 py-3 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($this->summary as $row)
                    <tr class="hover:bg-slate-50 transition-colors {{ $selectedMechanicId === $row['mechanic_id'] ? 'bg-amber-50' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-lg">&#x1F468;&#x200D;&#x1F527;</div>
                                <span class="font-medium text-slate-800">{{ $row['mechanic_name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-slate-700">{{ $row['total_wo'] }}</td>
                        <td class="px-6 py-4 text-right text-slate-700">Rp {{ number_format($row['total_jasa'],0,',','.') }}</td>
                        <td class="px-6 py-4 text-right text-slate-700">Rp {{ number_format($row['commission_vehicle'],0,',','.') }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($row['target'])
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $row['kpi_achieved'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $row['total_wo'] }}/{{ $row['target'] }} {{ $row['kpi_achieved'] ? '✓ Tercapai' : '' }}
                                </span>
                            @else
                                <span class="text-slate-300 text-xs">Belum diset</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-semibold {{ $row['kpi_achieved'] ? 'text-emerald-600' : 'text-slate-400' }}">Rp {{ number_format($row['commission_kpi'],0,',','.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-amber-600">Rp {{ number_format($row['total_commission'],0,',','.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <button type="button"
                                wire:click="selectMechanic('{{ $row['mechanic_id'] }}')"
                                class="px-3 py-1 rounded-lg text-xs font-medium transition-colors
                                    {{ $selectedMechanicId === $row['mechanic_id'] ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $selectedMechanicId === $row['mechanic_id'] ? 'Tutup' : 'Lihat WO' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">Belum ada mekanik atau data komisi untuk periode ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail WO per Mekanik --}}
    @if($selectedMechanicId && $this->woDetails->count())
    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-100 bg-amber-50">
            <h2 class="text-base font-semibold text-amber-800">
                &#x1F4CB; Detail WO &mdash; {{ $this->selectedRow['mechanic_name'] ?? '' }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">No. WO</th>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-right">Jasa</th>
                        <th class="px-4 py-3 text-right">Komisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($this->woDetails as $row)
                    @php $wo = $row->workOrder; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-amber-600">{{ $wo?->wo_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $wo?->vehicle?->plate_number }} &mdash; {{ trim(($wo?->vehicle?->brand ?? '').' '.($wo?->vehicle?->type ?? '')) ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $wo?->customer?->name }}</td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $row->earned_at?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">Rp {{ number_format($row->service_amount,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-amber-600">Rp {{ number_format($row->commission_amount,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($selectedMechanicId)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 text-center text-slate-400">
        Belum ada WO komisi untuk mekanik ini pada periode tersebut.
    </div>
    @endif

</div>

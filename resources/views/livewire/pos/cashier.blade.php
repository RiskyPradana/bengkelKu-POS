@php
    // Palet warna status SPK — hardcoded agar Tailwind tidak purge
    $statusPalette = [
        'amber'   => 'bg-amber-100 text-amber-700 border border-amber-200',
        'sky'     => 'bg-sky-100 text-sky-700 border border-sky-200',
        'emerald' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        'slate'   => 'bg-slate-100 text-slate-600 border border-slate-200',
    ];
    $itemBadge = [
        'emerald' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'sky'     => 'bg-sky-50 text-sky-700 border border-sky-200',
    ];
    $itemBtn = [
        'emerald' => 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/20',
        'sky'     => 'bg-sky-600 hover:bg-sky-500 text-white shadow-sky-600/20',
    ];
    $itemAvatar = [
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'sky'     => 'bg-sky-100 text-sky-700',
    ];
    $noticePalette = [
        'success' => ['wrap' => 'bg-emerald-50 border-emerald-200 text-emerald-900', 'icon' => 'text-emerald-500', 'bar' => 'bg-emerald-400', 'sym' => '✓'],
        'warning' => ['wrap' => 'bg-amber-50 border-amber-200 text-amber-900',   'icon' => 'text-amber-500',   'bar' => 'bg-amber-400',   'sym' => '⚠'],
        'danger'  => ['wrap' => 'bg-rose-50 border-rose-200 text-rose-900',     'icon' => 'text-rose-500',   'bar' => 'bg-rose-400',   'sym' => '✕'],
        'info'    => ['wrap' => 'bg-sky-50 border-sky-200 text-sky-900',       'icon' => 'text-sky-500',    'bar' => 'bg-sky-400',    'sym' => 'ℹ'],
    ];
    $np       = $noticePalette[$notice['type'] ?? 'info'] ?? $noticePalette['info'];
    $summary  = $this->paymentSummary;
    $isPaid   = ($summary['outstanding'] <= 0 && $summary['grand_total'] > 0);
    $hasWo    = $this->selectedWorkOrder !== null;
    $hasInv   = $this->selectedInvoice   !== null;
    $cartCount = $this->cartLines->count();
    $printerSettings = $this->printerSettings;
@endphp

<div>
{{--
    BengkelKu-POS — Halaman Kasir
    Versi yang sudah diperbaiki:
    Fix #1  : Payment input UI lengkap (method, amount, reference)
    Fix #2  : canIssueInvoice() → InProgress + Completed
    Fix #3  : Route dilindungi auth (di web.php)
    Fix #4  : N+1 Invoice accessor (di Invoice.php)
    Fix #5  : workOrderSearch & catalogSearch dipisah
    Fix #6  : Notice auto-dismiss dengan Alpine.js timer
    Fix #7-10: Quick Actions semua terhubung
    Fix #11 : Scan Barcode & Hold Transaksi terhubung
    Fix #12 : Grand Total ditampilkan prominently
    Fix #13 : Label "Diskon (Rp)" & "PPN (Rp)" jelas
    Fix #14 : File orphan dihapus (pos/cashier.blade.php)
    Fix #15 : Rounding desimal dipertahankan
    Fix #16 : Category tabs lebih efisien
    Fix #17 : Tombol Buat SPK dari kasir
    Sesi 14 : Voucher & potongan manual + pilihan level harga per item
--}}

<div
    class="min-h-screen bg-slate-100"
    x-data="{
        barcodeOpen: false,
        barcodeQuery: '',
        init() {
            this.$wire.on('printReceipt', () => {
                window.print()
            })
        }
    }"
    @keydown.window.f5.prevent="$wire.holdTransaction()"
    @keydown.window.f2.prevent="barcodeOpen = true; $nextTick(() => $refs.barcodeInput?.focus())"
    @keydown.window.end.prevent="$wire.recordPayment()"
>

{{-- ╔════════════════════════════════════════════════════════╗ --}}
{{-- ║  TOAST NOTIFICATION (Fix #6: auto-dismiss 4 detik)    ║ --}}
{{-- ╚════════════════════════════════════════════════════════╝ --}}
@if (!empty($notice))
<div
    x-data="{ visible: true }"
    x-init="setTimeout(() => { visible = false; setTimeout(() => $wire.clearNotice(), 500) }, 4000)"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-[-0.75rem] scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed top-5 right-5 z-[60] w-80 rounded-2xl border p-4 shadow-2xl shadow-black/10 backdrop-blur-sm {{ $np['wrap'] }}"
>
    <div class="flex items-start gap-3">
        <span class="mt-0.5 text-xl leading-none {{ $np['icon'] }}">{{ $np['sym'] }}</span>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm">{{ $notice['title'] }}</p>
            @if (!empty($notice['message']))
                <p class="mt-0.5 text-xs opacity-75">{{ $notice['message'] }}</p>
            @endif
        </div>
        <button
            @click="visible = false; $wire.clearNotice()"
            class="shrink-0 mt-0.5 text-xs opacity-40 hover:opacity-80 transition"
        >✕</button>
    </div>
    {{-- Progress bar auto-dismiss --}}
    <div class="mt-3 h-0.5 rounded-full bg-black/10 overflow-hidden">
        <div
            class="h-full rounded-full {{ $np['bar'] }}"
            x-data
            x-init="$el.style.cssText = 'width:100%;animation:shrink-bar 4s linear forwards'"
        ></div>
    </div>
</div>
@endif

{{-- Keyframe for progress bar --}}
<style>
    @keyframes shrink-bar { from { width:100% } to { width:0% } }
    @media print {
        @page { size: {{ $printerSettings['paper_width_mm'] }}mm auto; margin: 0; }
        body > * { display: none !important; }
        #print-receipt { display: block !important; }
    }
</style>

{{-- ╔════════════════════════════════════════════════════════╗ --}}
{{-- ║  BARCODE MODAL (Fix #11)                              ║ --}}
{{-- ╚════════════════════════════════════════════════════════╝ --}}
<div
    x-show="barcodeOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    @keydown.escape.window="barcodeOpen = false"
    @click.self="barcodeOpen = false"
>
    <div
        x-show="barcodeOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl"
    >
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Scan Barcode</h3>
                <p class="text-xs text-slate-500 mt-0.5">Ketik atau scan kode produk</p>
            </div>
            <button @click="barcodeOpen = false" class="h-8 w-8 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center transition">
                ✕
            </button>
        </div>
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9V5h4M3 15v4h4M17 5h4v4M17 19h4v-4"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01"/>
            </svg>
            <input
                x-ref="barcodeInput"
                x-model="barcodeQuery"
                @keydown.enter="$wire.set('catalogSearch', barcodeQuery); barcodeOpen = false; barcodeQuery = ''"
                type="text"
                placeholder="Scan atau ketik kode di sini..."
                class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50 py-4 pl-12 pr-4 text-base font-semibold text-slate-900 outline-none transition focus:border-slate-900 focus:bg-white"
            >
        </div>
        <p class="mt-3 text-center text-xs text-slate-400">Tekan <kbd class="rounded-md border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-xs">Enter</kbd> untuk cari — <kbd class="rounded-md border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-xs">Esc</kbd> untuk tutup</p>
    </div>
</div>

{{-- ╔════════════════════════════════════════════════════════╗ --}}
{{-- ║  HEADER STICKY                                         ║ --}}
{{-- ╚════════════════════════════════════════════════════════╝ --}}
<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur-md shadow-sm">
    <div class="mx-auto flex max-w-[1900px] items-center gap-3 px-4 py-3 lg:px-6">

        {{-- Brand --}}
        <div class="flex items-center gap-3 shrink-0">
            <div class="h-9 w-9 rounded-xl bg-slate-900 flex items-center justify-center shrink-0">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="hidden sm:block leading-tight">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">BengkelOS</p>
                <p class="text-sm font-bold text-slate-900">Kasir POS</p>
            </div>
        </div>

        <div class="hidden xl:block h-7 w-px bg-slate-200 mx-1 shrink-0"></div>

        {{-- Branch + Kasir badges --}}
        <div class="hidden lg:flex items-center gap-2 shrink-0">
            <div class="flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1.5">
                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></div>
                <span class="text-xs font-semibold text-slate-700">{{ $this->activeBranch?->name ?? 'Cabang' }}</span>
            </div>
            <div class="rounded-xl bg-slate-100 px-3 py-1.5">
                <span class="text-xs font-semibold text-slate-700">👤 {{ auth()->user()?->name ?? 'Operator' }}</span>
            </div>
        </div>

        <div class="flex-1 min-w-0"></div>

        {{-- SPK Search (desktop) --}}
        <div class="relative hidden md:block shrink-0">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="workOrderSearch"
                type="text"
                placeholder="Cari SPK..."
                class="w-48 rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100"
            >
        </div>

        {{-- Hold button (Fix #11) --}}
        <button
            type="button"
            wire:click="holdTransaction"
            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 shrink-0"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="hidden sm:inline">Hold</span>
            <kbd class="hidden xl:inline-flex items-center rounded-md border border-slate-200 bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] text-slate-500">F5</kbd>
        </button>

        {{-- Scan Barcode button (Fix #11) --}}
        <button
            type="button"
            @click="barcodeOpen = true; $nextTick(() => $refs.barcodeInput?.focus())"
            class="flex items-center gap-2 rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-700 shrink-0"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9V5h4M3 15v4h4M17 5h4v4M17 19h4v-4M7 8h.01M12 8h.01M17 8h.01M7 12h.01M12 12h.01M17 12h.01M7 16h.01M12 16h.01M17 16h.01"/>
                    </svg>
            <span class="hidden sm:inline">Scan</span>
            <kbd class="hidden xl:inline-flex items-center rounded-md border border-slate-700 bg-slate-800 px-1.5 py-0.5 font-mono text-[10px] text-slate-300">F2</kbd>
        </button>
    </div>

    {{-- Status bar --}}
    <div class="border-t border-slate-100 bg-slate-50/80">
        <div class="mx-auto flex max-w-[1900px] items-center gap-4 overflow-x-auto px-4 py-2 lg:px-6 scrollbar-none">
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Invoice</span>
                <span class="text-xs font-bold text-slate-800 font-mono">{{ $summary['invoice_number'] ?? '—' }}</span>
            </div>
            <div class="h-3 w-px bg-slate-200 shrink-0"></div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Pelanggan</span>
                <span class="text-xs font-bold text-slate-800">{{ $this->selectedWorkOrder?->customer?->name ?? '—' }}</span>
            </div>
            <div class="h-3 w-px bg-slate-200 shrink-0"></div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Kendaraan</span>
                <span class="text-xs font-bold text-slate-800 font-mono">{{ $this->selectedWorkOrder?->vehicle?->plate_number ?? '—' }}</span>
            </div>
            @if ($this->selectedWorkOrder)
                <div class="h-3 w-px bg-slate-200 shrink-0"></div>
                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold
                    @foreach($this->quickWorkOrders as $wo)
                        @if($wo['selected']) {{ $statusPalette[$wo['status_tone']] ?? '' }} @break @endif
                    @endforeach
                ">
                    {{ $this->selectedWorkOrder?->status?->label() ?? '—' }}
                </span>
            @endif
            <div class="flex-1"></div>
            <span class="shrink-0 text-[10px] text-slate-400 font-medium">
                {{ now()->locale('id')->isoFormat('ddd, D MMM YYYY · HH:mm') }}
            </span>
        </div>
    </div>
</header>

{{-- ╔════════════════════════════════════════════════════════╗ --}}
{{-- ║  MAIN GRID (2 kolom: konten kiri + sidebar kanan)     ║ --}}
{{-- ╚════════════════════════════════════════════════════════╝ --}}
<div class="mx-auto grid max-w-[1900px] grid-cols-1 gap-4 px-4 py-4 lg:grid-cols-[1fr_440px] xl:grid-cols-[1fr_480px] lg:px-6 lg:py-5 lg:items-start">

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- KOLOM KIRI: Daftar SPK + Katalog                      --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div class="space-y-4 min-w-0">

        {{-- ─────────────────────────────────────────── --}}
        {{-- Daftar SPK (Fix #17: ada tombol Buat SPK)  --}}
        {{-- ─────────────────────────────────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100">
                <div>
                    <h2 class="font-semibold text-slate-900">Daftar SPK Aktif</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih SPK untuk memulai transaksi</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- SPK Search mobile --}}
                    <div class="relative flex-1 sm:hidden">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                        </svg>
                        <input wire:model.live.debounce.300ms="workOrderSearch" type="text" placeholder="Cari SPK..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm outline-none focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">
                    </div>
                    {{-- Fix #17: Buat SPK dari kasir --}}
                    <a href="{{ route('work-orders') }}"
                        class="flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700 shrink-0">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat SPK
                    </a>
                </div>
            </div>

            <div class="p-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @forelse ($this->quickWorkOrders as $wo)
                    <button
                        type="button"
                        wire:click="selectWorkOrder('{{ $wo['id'] }}')"
                        wire:loading.attr="disabled"
                        wire:key="wo-{{ $wo['id'] }}"
                        class="group relative rounded-2xl border p-3.5 text-left transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md
                               {{ $wo['selected']
                                  ? 'border-slate-900 bg-slate-900 text-white shadow-lg shadow-slate-900/20'
                                  : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300 hover:bg-white' }}"
                    >
                        {{-- Pulse indikator aktif --}}
                        @if ($wo['selected'])
                            <span class="absolute top-2.5 right-2.5 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                            </span>
                        @endif

                        <div class="pr-5">
                            <p class="font-semibold text-sm truncate">{{ $wo['customer'] }}</p>
                            <p class="text-xs mt-0.5 font-mono {{ $wo['selected'] ? 'text-slate-400' : 'text-slate-500' }}">{{ $wo['plate'] }}</p>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2 flex-wrap">
                            <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold
                                {{ $wo['selected']
                                   ? 'bg-white/15 text-white'
                                   : ($statusPalette[$wo['status_tone']] ?? 'bg-slate-100 text-slate-600') }}">
                                {{ $wo['status'] }}
                            </span>
                            <span class="text-xs font-bold {{ $wo['selected'] ? 'text-slate-200' : 'text-slate-900' }}">
                                Rp {{ number_format((float)$wo['total'], 0, ',', '.') }}
                            </span>
                        </div>

                        @if ($wo['invoice'])
                            <p class="mt-1.5 text-[10px] font-mono {{ $wo['selected'] ? 'text-slate-500' : 'text-slate-400' }}">{{ $wo['invoice'] }}</p>
                        @endif
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center gap-3 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 py-12 text-center">
                        <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Belum ada SPK</p>
                            <p class="text-xs text-slate-400 mt-0.5">Buat SPK baru atau ubah kata kunci pencarian</p>
                        </div>
                        <a href="{{ route('work-orders') }}"
                            class="flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                            + Buat SPK Pertama
                        </a>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ─────────────────────────────────────────── --}}
        {{-- Katalog Produk & Jasa (Fix #5: search sendiri) --}}
        {{-- ─────────────────────────────────────────── --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100">
                <div>
                    <h2 class="font-semibold text-slate-900">Katalog</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Klik item untuk tambah ke keranjang</p>
                </div>
                {{-- Category tabs --}}
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($this->categoryTabs as $tab)
                        <button type="button" wire:click="setCategory('{{ $tab['key'] }}')"
                            class="rounded-xl px-3 py-1.5 text-xs font-semibold transition
                                   {{ $category === $tab['key'] ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $tab['label'] }}
                            <span class="opacity-60">({{ $tab['count'] }})</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="p-4">
                {{-- Fix #5: Catalog search tersendiri --}}
                <div class="relative mb-4">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                    </svg>
                    <input
                        id="catalog-search"
                        wire:model.live.debounce.300ms="catalogSearch"
                        type="text"
                        placeholder="Cari sparepart, jasa, atau kode SKU... (F2 untuk scan)"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-10 text-sm outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100"
                    >
                    @if ($catalogSearch)
                        <button wire:click="$set('catalogSearch', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($this->catalogItems as $item)
                        <article
                            wire:key="item-{{ $item['type'] }}-{{ $item['id'] }}"
                            x-data="{ selectedLevel: '' }"
                            class="group flex flex-col justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-md hover:border-slate-300"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-2 flex-wrap">
                                    <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $itemBadge[$item['tone']] ?? 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                        {{ $item['type_label'] }}
                                    </span>
                                    @if ($item['in_cart'])
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                            <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Di keranjang
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-2.5 font-semibold text-slate-900 text-sm leading-snug line-clamp-2">{{ $item['name'] }}</h3>
                                @if ($item['meta'])
                                    <p class="mt-1 text-[10px] text-slate-400 font-mono tracking-wider">{{ $item['meta'] }}</p>
                                @endif

                                {{-- Sesi 14: pilihan model harga (Level 2-4), mengikuti data iPos 5 --}}
                                @if ($item['type'] === 'product' && ($item['price_mode'] ?? 'single') === 'level' && !empty($item['price_levels']))
                                    <select x-model="selectedLevel"
                                        class="mt-2 w-full rounded-lg border-slate-200 bg-white py-1.5 px-2 text-[11px] font-medium text-slate-600 focus:border-slate-400 focus:ring-slate-100">
                                        <option value="">Harga Umum — Rp {{ number_format((float)$item['price'], 0, ',', '.') }}</option>
                                        @foreach ($item['price_levels'] as $lvl)
                                            <option value="{{ $lvl['level_no'] }}">{{ $lvl['level_name'] }} — Rp {{ number_format($lvl['price'], 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-2">
                                <p class="text-base font-bold text-slate-900">Rp {{ number_format((float)$item['price'], 0, ',', '.') }}</p>
                                @if ($item['type'] === 'product' && ($item['price_mode'] ?? 'single') === 'level' && !empty($item['price_levels']))
                                    <button
                                        type="button"
                                        @click="$wire.addCatalogItem('{{ $item['type'] }}', '{{ $item['id'] }}', selectedLevel === '' ? null : parseInt(selectedLevel))"
                                        class="flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold text-white transition shadow-sm {{ $itemBtn[$item['tone']] ?? 'bg-slate-600 hover:bg-slate-500 text-white' }}"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ $item['in_cart'] ? 'Lagi' : 'Tambah' }}
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="addCatalogItem('{{ $item['type'] }}', '{{ $item['id'] }}')"
                                        wire:loading.attr="disabled"
                                        class="flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-semibold text-white transition shadow-sm {{ $itemBtn[$item['tone']] ?? 'bg-slate-600 hover:bg-slate-500 text-white' }}"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ $item['in_cart'] ? 'Lagi' : 'Tambah' }}
                                    </button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 py-12 text-center">
                            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-500">Tidak ada item ditemukan</p>
                            <p class="text-xs text-slate-400">Coba ubah filter kategori atau kata kunci</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- SIDEBAR KANAN: Grand Total + Cart + Bayar             --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <aside class="space-y-4 lg:sticky lg:top-[109px] lg:self-start">

        {{-- ─────────────────────────────────── --}}
        {{-- Fix #12: Grand Total prominently   --}}
        {{-- ─────────────────────────────────── --}}
        <div class="rounded-2xl bg-slate-900 p-5 text-white shadow-xl shadow-slate-900/20 overflow-hidden relative">
            {{-- decorative circles --}}
            <div class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="pointer-events-none absolute -bottom-6 -left-6 h-24 w-24 rounded-full bg-white/5"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Tagihan</p>
                    <p class="mt-1 text-4xl font-black tracking-tight leading-none break-all">
                        Rp {{ number_format((float)$summary['grand_total'], 0, ',', '.') }}
                    </p>
                    @if ($summary['invoice_number'])
                        <p class="mt-2 text-[10px] font-mono text-slate-500">{{ $summary['invoice_number'] }}</p>
                    @endif
                </div>
                <div class="shrink-0 text-right">
                    @if ($isPaid)
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3 py-1.5 text-xs font-bold text-white">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            LUNAS
                        </span>
                    @elseif ($summary['outstanding'] > 0)
                        <div>
                            <p class="text-[10px] text-slate-400">Sisa tagihan</p>
                            <p class="text-lg font-bold text-emerald-400">Rp {{ number_format((float)$summary['outstanding'], 0, ',', '.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mini summary chips --}}
            <div class="relative mt-4 grid grid-cols-3 gap-2">
                <div class="rounded-xl bg-white/8 p-2.5 text-center">
                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-wider">Subtotal</p>
                    <p class="text-[11px] font-bold mt-0.5">Rp {{ number_format((float)$summary['subtotal'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-white/8 p-2.5 text-center">
                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-wider">Dibayar</p>
                    <p class="text-[11px] font-bold mt-0.5 text-emerald-400">Rp {{ number_format((float)$summary['paid'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-white/8 p-2.5 text-center">
                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-wider">Status</p>
                    <p class="text-[11px] font-bold mt-0.5 {{ $isPaid ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $summary['status'] ?? ($hasWo ? 'Draft' : '—') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────── --}}
        {{-- Keranjang SPK                       --}}
        {{-- ─────────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-100">
                <div>
                    <h2 class="font-semibold text-slate-900">Keranjang</h2>
                    <p class="text-xs text-slate-500">{{ $cartCount }} item</p>
                </div>
                @if ($hasWo && !$hasInv)
                    <button type="button" wire:click="createInvoice"
                        class="flex items-center gap-1.5 rounded-xl bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-violet-500 shadow-sm">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Buat Invoice
                    </button>
                @elseif ($hasInv)
                    <span class="flex items-center gap-1 rounded-xl bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Invoice aktif
                    </span>
                @endif
            </div>

            @if (!$hasWo)
                <div class="flex flex-col items-center gap-2 py-10 text-center px-4">
                    <svg class="h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <p class="text-sm font-semibold text-slate-400">Pilih SPK untuk mulai</p>
                </div>
            @elseif ($this->cartLines->isEmpty())
                <div class="flex flex-col items-center gap-2 py-8 text-center px-4">
                    <p class="text-sm text-slate-500">Keranjang masih kosong</p>
                    <p class="text-xs text-slate-400">Tambahkan item dari katalog di bawah</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto overscroll-contain">
                    @foreach ($this->cartLines as $line)
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50/80 transition group" wire:key="line-{{ $line['id'] }}">
                            {{-- Avatar --}}
                            <div class="h-9 w-9 shrink-0 rounded-xl grid place-items-center text-xs font-bold
                                        {{ $line['source_label'] === 'Sparepart' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">
                                {{ strtoupper(mb_substr($line['name'], 0, 1)) }}
                            </div>
                            {{-- Info & Qty --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ $line['name'] }}</p>
                                <p class="text-[10px] text-slate-400">{{ $line['source_label'] }} &middot; Rp {{ number_format($line['unit_price'], 0, ',', '.') }}/item</p>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <button type="button" wire:click="changeLineQuantity('{{ $line['id'] }}', -1)"
                                        class="h-6 w-6 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-600 transition hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 flex items-center justify-center">−</button>
                                    <span class="text-xs font-bold text-slate-900 w-6 text-center tabular-nums">{{ $line['qty'] }}</span>
                                    <button type="button" wire:click="changeLineQuantity('{{ $line['id'] }}', 1)"
                                        class="h-6 w-6 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-600 transition hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-600 flex items-center justify-center">+</button>
                                    <button type="button" wire:click="removeLineItem('{{ $line['id'] }}')"
                                        class="ml-1 text-[10px] font-semibold text-rose-400 hover:text-rose-600 transition opacity-0 group-hover:opacity-100">Hapus</button>
                                </div>
                            </div>
                            {{-- Subtotal --}}
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-slate-900 tabular-nums">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─────────────────────────────────── --}}
        {{-- Fix #13: Rincian Biaya (label Rp)  --}}
        {{-- ─────────────────────────────────── --}}
        @if ($hasWo)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 space-y-4">
            <h3 class="text-sm font-semibold text-slate-900">Rincian Biaya</h3>
            <div class="grid grid-cols-2 gap-3">
                <label class="space-y-1">
                    <span class="text-xs font-medium text-slate-500">Diskon <span class="font-bold text-slate-400">(Rp)</span></span>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input wire:model.live="discount" type="text" inputmode="decimal"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">
                    </div>
                </label>
                <label class="space-y-1">
                    <span class="text-xs font-medium text-slate-500">PPN <span class="font-bold text-slate-400">(Rp)</span></span>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input wire:model.live="tax" type="text" inputmode="decimal"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">
                    </div>
                </label>
            </div>

            {{-- Sesi 14: Voucher --}}
            <div class="rounded-xl border border-dashed border-violet-200 bg-violet-50/50 p-3 space-y-2">
                <p class="text-xs font-semibold text-violet-700">🎟️ Kode Voucher</p>
                @if ($appliedVoucherCode)
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-bold text-violet-900 font-mono">{{ $appliedVoucherCode }}</p>
                            @if ($appliedVoucherInfo)
                                <p class="text-[11px] text-violet-500">{{ $appliedVoucherInfo }}</p>
                            @else
                                <p class="text-[11px] text-violet-500">Sudah diterapkan pada invoice ini</p>
                            @endif
                        </div>
                        @if (!$hasInv)
                            <button type="button" wire:click="removeVoucher"
                                class="shrink-0 rounded-lg bg-white border border-violet-200 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">
                                Batalkan
                            </button>
                        @endif
                    </div>
                @elseif (!$hasInv)
                    <div class="flex items-center gap-2">
                        <input wire:model="voucherCode" wire:keydown.enter="applyVoucher" type="text"
                            placeholder="Masukkan kode voucher"
                            class="flex-1 rounded-lg border border-violet-200 bg-white py-2 px-3 text-sm uppercase tracking-wide outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                        <button type="button" wire:click="applyVoucher"
                            class="shrink-0 rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-violet-500">
                            Terapkan
                        </button>
                    </div>
                @else
                    <p class="text-xs text-violet-400">Invoice sudah dibuat tanpa voucher.</p>
                @endif
            </div>

            {{-- Breakdown tabel --}}
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-3 space-y-2 text-sm">
                <div class="flex items-center justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-900 tabular-nums">Rp {{ number_format((float)$summary['subtotal'], 0, ',', '.') }}</span>
                </div>
                @if ($summary['discount'] > 0)
                <div class="flex items-center justify-between text-slate-500">
                    <span>Diskon {{ $appliedVoucherCode ? '(' . $appliedVoucherCode . ')' : '' }}</span>
                    <span class="font-semibold text-rose-600 tabular-nums">&minus; Rp {{ number_format((float)$summary['discount'], 0, ',', '.') }}</span>
                </div>
                @endif
                @if ($summary['tax'] > 0)
                <div class="flex items-center justify-between text-slate-500">
                    <span>PPN</span>
                    <span class="font-semibold text-slate-900 tabular-nums">+ Rp {{ number_format((float)$summary['tax'], 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="border-t border-slate-200 pt-2 flex items-center justify-between font-bold text-slate-900">
                    <span>Grand Total</span>
                    <span class="tabular-nums">Rp {{ number_format((float)$summary['grand_total'], 0, ',', '.') }}</span>
                </div>
                @if ($summary['paid'] > 0)
                <div class="flex items-center justify-between text-emerald-600">
                    <span>Sudah dibayar</span>
                    <span class="font-semibold tabular-nums">Rp {{ number_format((float)$summary['paid'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between font-bold {{ $summary['outstanding'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    <span>Sisa tagihan</span>
                    <span class="tabular-nums">Rp {{ number_format((float)$summary['outstanding'], 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ─────────────────────────────────── --}}
        {{-- Fix #1: Payment UI lengkap          --}}
        {{-- ─────────────────────────────────── --}}
        @if ($hasWo)
            @if (!$isPaid)
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 space-y-4">
                <h3 class="text-sm font-semibold text-slate-900">Pembayaran</h3>

                {{-- Metode Pembayaran (Fix #1) --}}
                <div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Metode</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($this->paymentMethods as $method)
                            <button type="button"
                                wire:click="setPaymentMethod('{{ $method['value'] }}')"
                                class="flex items-center gap-2.5 rounded-xl border-2 px-3 py-2.5 text-sm font-semibold transition-all duration-150 shadow-sm
                                       {{ $paymentMethod === $method['value'] ? $method['selected_class'] : $method['unselected_class'] }}"
                            >
                                <span class="text-lg leading-none">{{ $method['icon'] }}</span>
                                <span>{{ $method['label'] }}</span>
                                @if ($paymentMethod === $method['value'])
                                    <svg class="ml-auto h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Nominal Bayar (Fix #1) --}}
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-500">Nominal Bayar</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                        <input wire:model.live="paymentAmount" type="text" inputmode="numeric"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-base font-bold text-slate-900 tabular-nums outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">
                    </div>
                    @if ($summary['outstanding'] > 0)
                        <button type="button"
                            wire:click="$set('paymentAmount', '{{ (int) $summary['outstanding'] }}')"
                            class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition">
                            &#9889; Isi otomatis: Rp {{ number_format((float)$summary['outstanding'], 0, ',', '.') }}
                        </button>
                    @endif
                </div>

                {{-- Nomor Referensi (opsional, hanya untuk non-cash) (Fix #1) --}}
                @if (in_array($paymentMethod, ['Transfer', 'QRIS', 'Debit']))
                <div class="space-y-1">
                    <label class="text-xs font-medium text-slate-500">
                        No. Referensi <span class="text-slate-400">(opsional)</span>
                    </label>
                    <input wire:model="paymentReference" type="text"
                        placeholder="No. bukti transfer / approval code"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-100">
                </div>
                @endif

                {{-- Tombol Bayar Sekarang --}}
                <button
                    type="button"
                    wire:click="recordPayment"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-not-allowed"
                    class="relative w-full rounded-xl bg-emerald-600 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-500 active:scale-[0.98] flex items-center justify-center gap-2"
                >
                    <svg class="h-4 w-4" wire:loading.remove wire:target="recordPayment" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg class="h-4 w-4 animate-spin" wire:loading wire:target="recordPayment" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="recordPayment">Bayar Sekarang</span>
                    <span wire:loading wire:target="recordPayment">Memproses...</span>
                    <kbd class="absolute right-4 hidden xl:inline-flex items-center rounded-lg border border-emerald-400 bg-emerald-500 px-1.5 py-0.5 font-mono text-[10px] text-emerald-100">END</kbd>
                </button>
            </div>
            @else
            {{-- LUNAS state --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500 shadow-lg shadow-emerald-500/30">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="mt-3 font-bold text-emerald-900 text-base">Transaksi Lunas!</p>
                <p class="text-sm text-emerald-700 mt-0.5">Semua pembayaran sudah diterima</p>
                <button type="button" wire:click="printReceipt"
                    class="mt-4 w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white transition hover:bg-emerald-500 flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Struk
                </button>
            </div>
            @endif
        @endif

        {{-- ─────────────────────────────────── --}}
        {{-- Fix #7–10: Quick Actions (semua terhubung) --}}
        {{-- ─────────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400">Quick Actions</h3>
            </div>
            <div class="grid grid-cols-2 divide-x divide-y divide-slate-100">
                {{-- Tambah Customer --}}
                <a href="{{ route('customers') }}"
                    class="flex items-center gap-2.5 px-4 py-3.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Tambah Customer
                </a>
                {{-- Semua SPK --}}
                <a href="{{ route('work-orders') }}"
                    class="flex items-center gap-2.5 px-4 py-3.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Semua SPK
                </a>
                {{-- Cari Invoice --}}
                <a href="{{ route('pos.cashier') }}"
                    class="flex items-center gap-2.5 px-4 py-3.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari Invoice
                </a>
                {{-- Cetak Struk --}}
                <button type="button" wire:click="printReceipt"
                    class="flex items-center gap-2.5 px-4 py-3.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 text-left">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Struk
                </button>
            </div>
        </div>
    </aside>
</div>

{{-- ╔════════════════════════════════════════════════════════╗ --}}
{{-- ║  PRINT RECEIPT (hanya tampil saat window.print())     ║ --}}
{{-- ╚════════════════════════════════════════════════════════╝ --}}
@if ($hasWo && $hasInv)
<div id="print-receipt" style="display:none">
    <div style="max-width:{{ $printerSettings['paper_width_px'] }}px;margin:0 auto;font-family:monospace;font-size:{{ $printerSettings['font_size'] }}px;padding:20px">
        <div style="text-align:center;margin-bottom:12px">
            <p style="font-size:18px;font-weight:bold;margin:0">BengkelOS</p>
            <p style="margin:2px 0;font-size:11px;color:#666">{{ $this->activeBranch?->name ?? '' }}</p>
        </div>
        <div style="border-top:1px dashed #ccc;margin:10px 0"></div>
        <p style="margin:2px 0">Invoice : {{ $this->selectedInvoice?->invoice_number }}</p>
        <p style="margin:2px 0">Tanggal : {{ now()->format('d/m/Y H:i') }}</p>
        <p style="margin:2px 0">Kasir   : {{ auth()->user()?->name }}</p>
        <p style="margin:2px 0">Plg     : {{ $this->selectedWorkOrder?->customer?->name ?? '-' }}</p>
        <div style="border-top:1px dashed #ccc;margin:10px 0"></div>
        @foreach ($this->cartLines as $line)
        <div style="margin-bottom:6px">
            <p style="margin:0;font-weight:bold">{{ $line['name'] }}</p>
            <div style="display:flex;justify-content:space-between">
                <span>{{ $line['qty'] }} x Rp {{ number_format($line['unit_price'], 0, ',', '.') }}</span>
                <span>Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
        <div style="border-top:1px dashed #ccc;margin:10px 0"></div>
        <div style="display:flex;justify-content:space-between">
            <span>Subtotal</span>
            <span>Rp {{ number_format((float)$summary['subtotal'], 0, ',', '.') }}</span>
        </div>
        @if ($summary['discount'] > 0)
        <div style="display:flex;justify-content:space-between">
            <span>Diskon</span>
            <span>-Rp {{ number_format((float)$summary['discount'], 0, ',', '.') }}</span>
        </div>
        @endif
        @if ($summary['tax'] > 0)
        <div style="display:flex;justify-content:space-between">
            <span>PPN</span>
            <span>Rp {{ number_format((float)$summary['tax'], 0, ',', '.') }}</span>
        </div>
        @endif
        <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:15px;margin-top:4px">
            <span>TOTAL</span>
            <span>Rp {{ number_format((float)$summary['grand_total'], 0, ',', '.') }}</span>
        </div>
        <div style="border-top:1px dashed #ccc;margin:10px 0"></div>
        <p style="text-align:center;color:#666;font-size:11px">Terima kasih atas kepercayaan Anda!</p>
        <p style="text-align:center;color:#999;font-size:10px;margin-top:4px">Powered by BengkelOS</p>
    </div>
</div>
@endif

</div>
</div>{{-- root wrapper --}}

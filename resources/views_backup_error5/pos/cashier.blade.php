<x-app-layout title="Kasir - BengkelOS">
    @php
        $categories = [
            ['label' => 'Semua', 'count' => 128, 'active' => true],
            ['label' => 'Sparepart', 'count' => 84, 'active' => false],
            ['label' => 'Jasa', 'count' => 18, 'active' => false],
            ['label' => 'Ban', 'count' => 12, 'active' => false],
            ['label' => 'Oli', 'count' => 9, 'active' => false],
            ['label' => 'Aksesoris', 'count' => 17, 'active' => false],
        ];

        $items = [
            ['name' => 'Oli Mesin 10W-40', 'type' => 'Sparepart', 'price' => 'Rp 145.000', 'stock' => 'Stock 27', 'tone' => 'emerald'],
            ['name' => 'Ganti Oli', 'type' => 'Jasa', 'price' => 'Rp 65.000', 'stock' => 'Durasi 20 menit', 'tone' => 'sky'],
            ['name' => 'Filter Udara', 'type' => 'Sparepart', 'price' => 'Rp 58.000', 'stock' => 'Stock 9', 'tone' => 'amber'],
            ['name' => 'Spooring', 'type' => 'Jasa', 'price' => 'Rp 120.000', 'stock' => 'Slot tersedia', 'tone' => 'violet'],
            ['name' => 'Aki GS 35AH', 'type' => 'Sparepart', 'price' => 'Rp 780.000', 'stock' => 'Stock 4', 'tone' => 'rose'],
            ['name' => 'Cuci Mesin', 'type' => 'Jasa', 'price' => 'Rp 85.000', 'stock' => 'Slot terbatas', 'tone' => 'cyan'],
        ];

        $toneStyles = [
            'emerald' => ['pill' => 'bg-emerald-50 text-emerald-700', 'button' => 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/20'],
            'sky' => ['pill' => 'bg-sky-50 text-sky-700', 'button' => 'bg-sky-600 hover:bg-sky-500 shadow-sky-600/20'],
            'amber' => ['pill' => 'bg-amber-50 text-amber-700', 'button' => 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/20'],
            'violet' => ['pill' => 'bg-violet-50 text-violet-700', 'button' => 'bg-violet-600 hover:bg-violet-500 shadow-violet-600/20'],
            'rose' => ['pill' => 'bg-rose-50 text-rose-700', 'button' => 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/20'],
            'cyan' => ['pill' => 'bg-cyan-50 text-cyan-700', 'button' => 'bg-cyan-600 hover:bg-cyan-500 shadow-cyan-600/20'],
        ];
    @endphp

    <div class="min-h-screen bg-slate-100">
        <div class="border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-[1600px] flex-col gap-4 px-4 py-4 lg:px-6">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">BengkelOS</p>
                            <h1 class="text-2xl font-bold text-slate-900">Kasir POS</h1>
                        </div>
                        <div class="hidden h-10 w-px bg-slate-200 xl:block"></div>
                        <div class="rounded-2xl bg-slate-100 px-4 py-2">
                            <p class="text-xs text-slate-500">Cabang aktif</p>
                            <p class="font-semibold text-slate-900">Kebon Jeruk</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 px-4 py-2 text-emerald-700">
                            <p class="text-xs">Kasir</p>
                            <p class="font-semibold">Ayu Prameswari</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <label class="flex min-w-[240px] flex-1 items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 shadow-sm">
                            <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                            <input type="text" placeholder="Cari sparepart, jasa, plat, atau invoice" class="w-full border-0 bg-transparent p-0 text-sm outline-none ring-0 placeholder:text-slate-400">
                        </label>
                        <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800">
                            Scan Barcode
                        </button>
                        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Hold Transaksi
                        </button>
                    </div>
                </div>

                <div class="grid gap-3 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <p class="text-xs text-slate-500">Invoice aktif</p>
                        <p class="text-xl font-bold text-slate-900">INV-20260830-001</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <p class="text-xs text-slate-500">Pelanggan</p>
                        <p class="text-xl font-bold text-slate-900">Budi Santoso</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <p class="text-xs text-slate-500">Kendaraan</p>
                        <p class="text-xl font-bold text-slate-900">B 1234 KSP</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-sm">
                        <p class="text-xs text-emerald-600">Status SPK</p>
                        <p class="text-xl font-bold text-emerald-800">Completed</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto grid max-w-[1600px] gap-5 px-4 py-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(360px,0.9fr)] lg:px-6">
            <section class="space-y-5">
                <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                    @foreach ($categories as $category)
                        <button class="rounded-3xl border px-4 py-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $category['active'] ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-700' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold">{{ $category['label'] }}</p>
                                    <p class="mt-1 text-xs {{ $category['active'] ? 'text-slate-300' : 'text-slate-500' }}">Kategori cepat</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category['active'] ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $category['count'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Produk & Jasa Cepat</h2>
                            <p class="text-sm text-slate-500">Pilih item dari daftar atau scan barcode untuk tambah cepat ke keranjang.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Promo hari ini</span>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Stock kritis</span>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Ready service</span>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($items as $item)
                            @php($tone = $toneStyles[$item['tone']])
                            <article class="group rounded-3xl border border-slate-200 bg-slate-50 p-4 transition hover:-translate-y-1 hover:bg-white hover:shadow-lg">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $tone['pill'] }}">{{ $item['type'] }}</span>
                                        <h3 class="mt-3 text-base font-semibold text-slate-900">{{ $item['name'] }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $item['stock'] }}</p>
                                    </div>
                                    <button class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition group-hover:bg-slate-800">
                                        + Tambah
                                    </button>
                                </div>
                                <div class="mt-5 flex items-center justify-between">
                                    <p class="text-lg font-bold text-slate-900">{{ $item['price'] }}</p>
                                    <p class="text-xs text-slate-500">Kode #{{ strtoupper(substr(md5($item['name']), 0, 6)) }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="space-y-5">
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Keranjang</p>
                            <h2 class="text-lg font-semibold text-slate-900">Invoice Aktif</h2>
                        </div>
                        <button class="rounded-2xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Kosongkan</button>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach ([
                            ['name' => 'Oli Mesin 10W-40', 'qty' => 1, 'price' => '145.000'],
                            ['name' => 'Ganti Oli', 'qty' => 1, 'price' => '65.000'],
                            ['name' => 'Filter Udara', 'qty' => 2, 'price' => '116.000'],
                        ] as $row)
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-900 text-sm font-bold text-white">{{ strtoupper(substr($row['name'], 0, 1)) }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-slate-900">{{ $row['name'] }}</p>
                                    <p class="text-xs text-slate-500">Qty {{ $row['qty'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-slate-900">Rp {{ $row['price'] }}</p>
                                    <button class="text-xs text-slate-500 transition hover:text-rose-600">Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900">Rp 326.000</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>Diskon</span>
                            <span class="font-semibold text-slate-900">Rp 10.000</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-slate-600">
                            <span>PPN</span>
                            <span class="font-semibold text-slate-900">Rp 32.000</span>
                        </div>
                        <div class="h-px bg-slate-200"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-600">Total Bayar</span>
                            <span class="text-2xl font-bold text-slate-900">Rp 348.000</span>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Simpan Draft
                        </button>
                        <button class="rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-emerald-500">
                            Bayar Sekarang
                        </button>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-900 p-5 text-white shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Quick Action</p>
                            <h3 class="mt-1 text-lg font-semibold">Shortcut Kasir</h3>
                        </div>
                        <svg class="h-10 w-10 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 6h16M4 12h10M4 18h16"></path>
                        </svg>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <button class="rounded-2xl bg-white/10 px-4 py-3 text-left text-sm font-semibold transition hover:bg-white/15">
                            Tambah Customer
                        </button>
                        <button class="rounded-2xl bg-white/10 px-4 py-3 text-left text-sm font-semibold transition hover:bg-white/15">
                            Pilih SPK
                        </button>
                        <button class="rounded-2xl bg-white/10 px-4 py-3 text-left text-sm font-semibold transition hover:bg-white/15">
                            Cari Invoice
                        </button>
                        <button class="rounded-2xl bg-white/10 px-4 py-3 text-left text-sm font-semibold transition hover:bg-white/15">
                            Cetak Struk
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>

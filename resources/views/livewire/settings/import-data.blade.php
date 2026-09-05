<div class="p-4 space-y-5 md:p-6" x-data="{ toast: null }" x-on:notify.window="toast = $event.detail; setTimeout(() => toast = null, 5000)">

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import dari iPos 5</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Upload file export <b>SUPPLIER.xlsx</b> dan <b>ITEM.xlsx</b> dari iPos 5, lalu impor langsung ke BengkelOS.</p>
    </div>

    <div x-show="toast" x-transition style="display:none"
         class="p-3 text-sm border rounded-lg"
         :class="toast?.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : (toast?.type === 'danger' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-amber-50 border-amber-200 text-amber-800')">
        <span x-text="toast?.message"></span>
    </div>

    <div class="flex gap-1 p-1 bg-gray-100 dark:bg-slate-800 rounded-xl w-fit">
        <button wire:click="switchTab('supplier')"
                @class(['px-4 py-2 text-sm font-semibold rounded-lg transition-colors', 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm' => $tab === 'supplier', 'text-gray-500 dark:text-slate-400' => $tab !== 'supplier'])>
            Supplier
        </button>
        <button wire:click="switchTab('item')"
                @class(['px-4 py-2 text-sm font-semibold rounded-lg transition-colors', 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm' => $tab === 'item', 'text-gray-500 dark:text-slate-400' => $tab !== 'item'])>
            Item / Sparepart
        </button>
    </div>

    {{-- ═════ SUPPLIER ═════ --}}
    @if ($tab === 'supplier')
    <div class="p-5 space-y-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">File SUPPLIER.xlsx</label>
            <input type="file" wire:model="supplierFile" accept=".xlsx"
                   class="block w-full text-sm text-gray-600 dark:text-slate-300 border border-gray-300 dark:border-slate-700 rounded-lg cursor-pointer file:mr-3 file:py-2 file:px-3 file:border-0 file:bg-gray-100 dark:file:bg-slate-800 file:text-sm file:font-medium">
            <div wire:loading wire:target="supplierFile" class="mt-1 text-xs text-gray-400">Membaca file...</div>
        </div>

        @if (!empty($supplierPreview))
            <div class="p-3 text-sm border rounded-lg bg-sky-50 border-sky-200 text-sky-800">
                Ditemukan <b>{{ $supplierPreview['total_rows'] }}</b> baris supplier. Contoh 5 baris pertama:
            </div>
            <div class="overflow-x-auto border border-gray-200 dark:border-slate-800 rounded-lg">
                <table class="w-full text-xs whitespace-nowrap">
                    <thead class="bg-gray-50 dark:bg-slate-800/50">
                        <tr>
                            @foreach ($supplierPreview['header'] as $h)
                                <th class="px-3 py-2 font-semibold text-left text-gray-500 dark:text-slate-400">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @foreach ($supplierPreview['sample'] as $row)
                            <tr>
                                @foreach ($supplierPreview['header'] as $i => $h)
                                    <td class="px-3 py-2 text-gray-700 dark:text-slate-300">{{ $row[$i] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button wire:click="importSuppliers" wire:loading.attr="disabled"
                    class="px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="importSuppliers">Import {{ $supplierPreview['total_rows'] }} Supplier Sekarang</span>
                <span wire:loading wire:target="importSuppliers">Mengimport...</span>
            </button>
        @endif

        @if ($supplierResult)
            <div class="p-3 text-sm border rounded-lg bg-emerald-50 border-emerald-200 text-emerald-800">
                Selesai: <b>{{ $supplierResult['created'] }}</b> supplier baru, <b>{{ $supplierResult['updated'] }}</b> diperbarui, {{ $supplierResult['skipped'] }} dilewati.
            </div>
        @endif
    </div>
    @endif

    {{-- ═════ ITEM ═════ --}}
    @if ($tab === 'item')
    <div class="p-5 space-y-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-xl">
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">File ITEM.xlsx</label>
            <input type="file" wire:model="itemFile" accept=".xlsx"
                   class="block w-full text-sm text-gray-600 dark:text-slate-300 border border-gray-300 dark:border-slate-700 rounded-lg cursor-pointer file:mr-3 file:py-2 file:px-3 file:border-0 file:bg-gray-100 dark:file:bg-slate-800 file:text-sm file:font-medium">
            <div wire:loading wire:target="itemFile" class="mt-1 text-xs text-gray-400">Membaca file...</div>
        </div>

        <div class="max-w-xs">
            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-slate-200">Cabang Tujuan (untuk stok awal, min. stok, & rak)</label>
            <select wire:model="targetBranchId" class="w-full text-sm border-gray-300 dark:border-slate-700 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                @foreach ($this->branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        @if (!empty($itemPreview))
            <div class="p-3 text-sm border rounded-lg bg-sky-50 border-sky-200 text-sky-800">
                Ditemukan <b>{{ $itemPreview['total_rows'] }}</b> baris item. Merek, satuan, dan rak yang belum ada akan otomatis dibuat sebagai data master baru. Contoh 5 baris pertama:
            </div>
            <div class="overflow-x-auto border border-gray-200 dark:border-slate-800 rounded-lg">
                <table class="w-full text-xs whitespace-nowrap">
                    <thead class="bg-gray-50 dark:bg-slate-800/50">
                        <tr>
                            @foreach ($itemPreview['header'] as $h)
                                <th class="px-3 py-2 font-semibold text-left text-gray-500 dark:text-slate-400">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @foreach ($itemPreview['sample'] as $row)
                            <tr>
                                @foreach ($itemPreview['header'] as $i => $h)
                                    <td class="px-3 py-2 text-gray-700 dark:text-slate-300">{{ $row[$i] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button wire:click="importItems" wire:loading.attr="disabled"
                    class="px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="importItems">Import {{ $itemPreview['total_rows'] }} Item Sekarang</span>
                <span wire:loading wire:target="importItems">Mengimport...</span>
            </button>
        @endif

        @if ($itemResult)
            <div class="p-3 text-sm border rounded-lg bg-emerald-50 border-emerald-200 text-emerald-800">
                Selesai: <b>{{ $itemResult['created'] }}</b> item baru, <b>{{ $itemResult['updated'] }}</b> diperbarui, {{ $itemResult['skipped'] }} dilewati.
            </div>
        @endif
    </div>
    @endif
</div>

<?php

namespace App\Livewire\Settings;

use App\Domains\Catalog\Models\Brand;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductPriceLevel;
use App\Domains\Catalog\Models\Unit;
use App\Domains\Inventory\Models\BranchStock;
use App\Domains\Inventory\Models\Rack;
use App\Domains\MasterData\Models\Branch;
use App\Domains\Purchasing\Models\Supplier;
use App\Support\SimpleXlsxReader;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Sesi 14: Import data Supplier & Item dari file export iPos 5 (.xlsx).
 * Parser xlsx murni PHP (tanpa dependency composer baru) ada di
 * App\Support\SimpleXlsxReader.
 */
class ImportData extends Component
{
    use WithFileUploads;

    public string $tab = 'supplier'; // supplier | item

    public $supplierFile = null;
    public $itemFile     = null;

    public ?string $targetBranchId = null;

    public array $supplierPreview = [];
    public array $itemPreview     = [];

    public ?array $supplierResult = null;
    public ?array $itemResult     = null;

    public function mount(): void
    {
        $this->targetBranchId = Branch::query()->where('is_active', true)->orderBy('name')->value('id');
    }

    public function render(): View
    {
        return view('livewire.settings.import-data')
            ->layout('layouts.admin', [
                'title'     => 'Import Data — BengkelOS',
                'pageTitle' => 'Import dari iPos 5',
                'pageSub'   => 'Import data Supplier & Item dari file export iPos 5 (.xlsx)',
            ]);
    }

    #[Computed]
    public function branches()
    {
        return Branch::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function updatedSupplierFile(): void
    {
        $this->supplierResult  = null;
        $this->supplierPreview = [];

        try {
            $rows = $this->readRows($this->supplierFile);
            [$header, $dataRows] = $this->splitHeader($rows, ['KODE', 'NAMA']);
            $this->supplierPreview = [
                'header'     => $header,
                'total_rows' => count($dataRows),
                'sample'     => array_slice($dataRows, 0, 5),
            ];
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'danger', message: 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function updatedItemFile(): void
    {
        $this->itemResult  = null;
        $this->itemPreview = [];

        try {
            $rows = $this->readRows($this->itemFile);
            [$header, $dataRows] = $this->splitHeader($rows, ['KODEITEM', 'NAMAITEM']);
            $this->itemPreview = [
                'header'     => $header,
                'total_rows' => count($dataRows),
                'sample'     => array_slice($dataRows, 0, 5),
            ];
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'danger', message: 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function importSuppliers(): void
    {
        if (! $this->supplierFile) {
            $this->dispatch('notify', type: 'warning', message: 'Upload file SUPPLIER.xlsx dari iPos 5 terlebih dahulu.');
            return;
        }

        try {
            $rows = $this->readRows($this->supplierFile);
            [, $dataRows] = $this->splitHeader($rows, ['KODE', 'NAMA']);
            $map = $this->headerMap($this->splitHeader($rows, ['KODE', 'NAMA'])[0]);

            $created = 0;
            $updated = 0;
            $skipped = 0;

            DB::transaction(function () use ($dataRows, $map, &$created, &$updated, &$skipped): void {
                foreach ($dataRows as $row) {
                    $name = trim((string) ($this->cell($row, $map, 'NAMA') ?? ''));
                    if ($name === '') {
                        $skipped++;
                        continue;
                    }

                    $code = trim((string) ($this->cell($row, $map, 'KODE') ?? ''));

                    $data = [
                        'name'           => $name,
                        'contact_person' => trim((string) ($this->cell($row, $map, 'ATASNAMA') ?? $this->cell($row, $map, 'KONTAK') ?? '')) ?: null,
                        'phone'          => trim((string) ($this->cell($row, $map, 'TELEPON') ?? '')) ?: null,
                        'email'          => trim((string) ($this->cell($row, $map, 'EMAIL') ?? '')) ?: null,
                        'address'        => trim((string) ($this->cell($row, $map, 'ALAMAT') ?? '')) ?: null,
                        'city'           => trim((string) ($this->cell($row, $map, 'KOTA') ?? '')) ?: null,
                        'province'       => trim((string) ($this->cell($row, $map, 'PROVINSI') ?? '')) ?: null,
                        'external_code'  => $code ?: null,
                        'is_active'      => true,
                    ];

                    $existing = $code !== ''
                        ? Supplier::where('external_code', $code)->first()
                        : Supplier::where('name', $name)->first();

                    if ($existing) {
                        $existing->update($data);
                        $updated++;
                    } else {
                        Supplier::create($data);
                        $created++;
                    }
                }
            });

            $this->supplierResult = compact('created', 'updated', 'skipped');
            $this->dispatch('notify', type: 'success', message: "Import supplier selesai: {$created} baru, {$updated} diperbarui, {$skipped} dilewati.");
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'danger', message: 'Import gagal: ' . $e->getMessage());
        }
    }

    public function importItems(): void
    {
        if (! $this->itemFile) {
            $this->dispatch('notify', type: 'warning', message: 'Upload file ITEM.xlsx dari iPos 5 terlebih dahulu.');
            return;
        }
        if (! $this->targetBranchId) {
            $this->dispatch('notify', type: 'warning', message: 'Pilih cabang tujuan untuk stok awal.');
            return;
        }

        try {
            $rows = $this->readRows($this->itemFile);
            [$header, $dataRows] = $this->splitHeader($rows, ['KODEITEM', 'NAMAITEM']);
            $map = $this->headerMap($header);

            $created  = 0;
            $updated  = 0;
            $skipped  = 0;
            $branchId = $this->targetBranchId;

            DB::transaction(function () use ($dataRows, $map, $branchId, &$created, &$updated, &$skipped): void {
                foreach ($dataRows as $row) {
                    $name = trim((string) ($this->cell($row, $map, 'NAMAITEM') ?? ''));
                    if ($name === '') {
                        $skipped++;
                        continue;
                    }

                    $sku       = trim((string) ($this->cell($row, $map, 'KODEITEM') ?? '')) ?: null;
                    $barcode   = trim((string) ($this->cell($row, $map, 'BARCODE1') ?? '')) ?: null;
                    $brandName = trim((string) ($this->cell($row, $map, 'MEREK') ?? ''));
                    $unitName  = trim((string) ($this->cell($row, $map, 'SATUAN1') ?? ''));
                    $rackName  = trim((string) ($this->cell($row, $map, 'RAK') ?? ''));

                    $costPrice = (float) ($this->cell($row, $map, 'HARGAPOKOK1') ?? 0);
                    $level1    = (float) ($this->cell($row, $map, 'HARGAJUAL1') ?? 0);
                    $level2    = (float) ($this->cell($row, $map, 'HARGAJUAL2') ?? 0);
                    $level3    = (float) ($this->cell($row, $map, 'HARGAJUAL3') ?? 0);
                    $level4    = (float) ($this->cell($row, $map, 'HARGAJUAL4') ?? 0);
                    $stok      = (int) ($this->cell($row, $map, 'STOK') ?? 0);
                    $stokMin   = (int) ($this->cell($row, $map, 'STOKMIN') ?? 0);

                    $brand = $brandName !== '' ? Brand::firstOrCreate(['name' => $brandName]) : null;
                    $unit  = $unitName !== '' ? Unit::firstOrCreate(['name' => $unitName]) : null;
                    $rack  = $rackName !== '' ? Rack::firstOrCreate(['branch_id' => $branchId, 'name' => $rackName]) : null;

                    $extraLevels = array_filter([2 => $level2, 3 => $level3, 4 => $level4]);
                    $priceMode   = count($extraLevels) > 0 ? 'level' : 'single';

                    $data = [
                        'sku'        => $sku,
                        'barcode'    => $barcode,
                        'name'       => $name,
                        'cost_price' => $costPrice,
                        'sell_price' => $level1 > 0 ? $level1 : $costPrice,
                        'is_active'  => true,
                        'category'   => trim((string) ($this->cell($row, $map, 'JENIS') ?? '')) ?: null,
                        'brand_id'   => $brand?->id,
                        'unit_id'    => $unit?->id,
                        'price_mode' => $priceMode,
                    ];

                    $existing = $sku
                        ? Product::where('sku', $sku)->first()
                        : ($barcode ? Product::where('barcode', $barcode)->first() : Product::where('name', $name)->first());

                    if ($existing) {
                        $existing->update($data);
                        $product = $existing;
                        $updated++;
                    } else {
                        $product = Product::create($data);
                        $created++;
                    }

                    foreach ($extraLevels as $levelNo => $price) {
                        ProductPriceLevel::updateOrCreate(
                            ['product_id' => $product->id, 'level_no' => $levelNo],
                            ['level_name' => 'Level Harga ' . $levelNo, 'price' => $price]
                        );
                    }

                    if ($rack || $stok > 0 || $stokMin > 0) {
                        BranchStock::updateOrCreate(
                            ['branch_id' => $branchId, 'product_id' => $product->id],
                            [
                                'quantity'      => $stok,
                                'min_stock'     => $stokMin > 0 ? $stokMin : 5,
                                'rack_id'       => $rack?->id,
                                'rack_location' => $rackName ?: null,
                            ]
                        );
                    }
                }
            });

            $this->itemResult = compact('created', 'updated', 'skipped');
            $this->dispatch('notify', type: 'success', message: "Import item selesai: {$created} baru, {$updated} diperbarui, {$skipped} dilewati.");
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'danger', message: 'Import gagal: ' . $e->getMessage());
        }
    }

    // ── Helpers ─────────────────────────

    private function readRows($uploadedFile): array
    {
        $sheets = SimpleXlsxReader::read($uploadedFile->getRealPath());
        $first  = array_key_first($sheets);
        return $first !== null ? ($sheets[$first] ?? []) : [];
    }

    /**
     * Cari baris header (baris yang berisi semua $requiredColumns), lalu
     * kembalikan [header, dataRowsSetelahnya]. Export iPos kadang punya
     * baris metadata di atas header asli.
     */
    private function splitHeader(array $rows, array $requiredColumns): array
    {
        foreach ($rows as $index => $row) {
            $normalized = array_map(fn ($v) => strtoupper(trim((string) $v)), $row);
            $hasAll = true;
            foreach ($requiredColumns as $col) {
                if (! in_array($col, $normalized, true)) {
                    $hasAll = false;
                    break;
                }
            }
            if ($hasAll) {
                $dataRows = array_slice($rows, $index + 1);
                $dataRows = array_values(array_filter(
                    $dataRows,
                    fn ($r) => collect($r)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty()
                ));
                return [$row, $dataRows];
            }
        }

        throw new \RuntimeException('Format file tidak dikenali. Pastikan file adalah export asli dari iPos 5.');
    }

    private function headerMap(array $header): array
    {
        $map = [];
        foreach ($header as $index => $name) {
            $key = strtoupper(trim((string) $name));
            if ($key !== '') {
                $map[$key] = $index;
            }
        }
        return $map;
    }

    private function cell(array $row, array $map, string $column): mixed
    {
        $index = $map[$column] ?? null;
        return $index !== null ? ($row[$index] ?? null) : null;
    }
}

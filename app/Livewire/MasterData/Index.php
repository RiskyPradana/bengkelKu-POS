<?php

namespace App\Livewire\MasterData;

use App\Domains\Catalog\Models\Brand;
use App\Domains\Catalog\Models\Unit;
use App\Domains\Inventory\Models\Rack;
use App\Domains\MasterData\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Sesi 14: Merek, Satuan, dan Rak sekarang jadi data master tersendiri yang
 * dinamis (bisa CRUD sendiri), bukan lagi teks bebas di form produk.
 */
class Index extends Component
{
    public string $tab = 'brand'; // brand | unit | rack

    // ── Form Merek ──────────────────────
    public ?string $brandId     = null;
    public string  $brandName   = '';
    public bool    $brandActive = true;

    // ── Form Satuan ──────────────────────
    public ?string $unitId     = null;
    public string  $unitName   = '';
    public string  $unitAbbr   = '';
    public bool    $unitActive = true;

    // ── Form Rak ──────────────────────
    public ?string $rackId       = null;
    public string  $rackName     = '';
    public ?string $rackBranchId = null;
    public bool    $rackActive   = true;

    public function mount(): void
    {
        $this->rackBranchId = Branch::query()->where('is_active', true)->orderBy('name')->value('id');
    }

    public function render(): View
    {
        return view('livewire.master-data.index')
            ->layout('layouts.admin', [
                'title'     => 'Master Data — BengkelOS',
                'pageTitle' => 'Master Data',
                'pageSub'   => 'Merek, Satuan, dan Rak / lokasi penyimpanan — dinamis & bisa dikelola sendiri',
            ]);
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetErrorBag();
    }

    #[Computed]
    public function brands(): Collection
    {
        return Brand::withCount('products')->orderBy('name')->get();
    }

    #[Computed]
    public function units(): Collection
    {
        return Unit::withCount('products')->orderBy('name')->get();
    }

    #[Computed]
    public function racks(): Collection
    {
        return Rack::with('branch')->withCount('branchStocks')->orderBy('name')->get();
    }

    #[Computed]
    public function branches(): Collection
    {
        return Branch::query()->orderBy('name')->get();
    }

    // ── Merek ──────────────────────────

    public function resetBrandForm(): void
    {
        $this->reset(['brandId', 'brandName']);
        $this->brandActive = true;
        $this->resetErrorBag();
    }

    public function editBrand(string $id): void
    {
        $b = Brand::findOrFail($id);
        $this->brandId     = $b->id;
        $this->brandName   = $b->name;
        $this->brandActive = (bool) $b->is_active;
        $this->resetErrorBag();
    }

    public function saveBrand(): void
    {
        $this->validate(['brandName' => 'required|string|max:150'], [], ['brandName' => 'nama merek']);

        $exists = Brand::where('name', trim($this->brandName))->when($this->brandId, fn ($q) => $q->where('id', '!=', $this->brandId))->exists();
        if ($exists) {
            $this->addError('brandName', 'Nama merek sudah ada.');
            return;
        }

        Brand::updateOrCreate(
            ['id' => $this->brandId],
            ['name' => trim($this->brandName), 'is_active' => $this->brandActive]
        );

        $this->resetBrandForm();
        $this->dispatch('notify', type: 'success', message: 'Merek disimpan.');
    }

    public function toggleBrand(string $id): void
    {
        $b = Brand::findOrFail($id);
        $b->update(['is_active' => ! $b->is_active]);
    }

    public function deleteBrand(string $id): void
    {
        Brand::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Merek dihapus.');
    }

    // ── Satuan ──────────────────────────

    public function resetUnitForm(): void
    {
        $this->reset(['unitId', 'unitName', 'unitAbbr']);
        $this->unitActive = true;
        $this->resetErrorBag();
    }

    public function editUnit(string $id): void
    {
        $u = Unit::findOrFail($id);
        $this->unitId     = $u->id;
        $this->unitName   = $u->name;
        $this->unitAbbr   = $u->abbreviation ?? '';
        $this->unitActive = (bool) $u->is_active;
        $this->resetErrorBag();
    }

    public function saveUnit(): void
    {
        $this->validate(['unitName' => 'required|string|max:100'], [], ['unitName' => 'nama satuan']);

        $exists = Unit::where('name', trim($this->unitName))->when($this->unitId, fn ($q) => $q->where('id', '!=', $this->unitId))->exists();
        if ($exists) {
            $this->addError('unitName', 'Nama satuan sudah ada.');
            return;
        }

        Unit::updateOrCreate(
            ['id' => $this->unitId],
            ['name' => trim($this->unitName), 'abbreviation' => $this->unitAbbr ?: null, 'is_active' => $this->unitActive]
        );

        $this->resetUnitForm();
        $this->dispatch('notify', type: 'success', message: 'Satuan disimpan.');
    }

    public function toggleUnit(string $id): void
    {
        $u = Unit::findOrFail($id);
        $u->update(['is_active' => ! $u->is_active]);
    }

    public function deleteUnit(string $id): void
    {
        Unit::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Satuan dihapus.');
    }

    // ── Rak ──────────────────────────

    public function resetRackForm(): void
    {
        $this->reset(['rackId', 'rackName']);
        $this->rackActive   = true;
        $this->rackBranchId = Branch::query()->where('is_active', true)->orderBy('name')->value('id');
        $this->resetErrorBag();
    }

    public function editRack(string $id): void
    {
        $r = Rack::findOrFail($id);
        $this->rackId       = $r->id;
        $this->rackName     = $r->name;
        $this->rackBranchId = $r->branch_id;
        $this->rackActive   = (bool) $r->is_active;
        $this->resetErrorBag();
    }

    public function saveRack(): void
    {
        $this->validate(['rackName' => 'required|string|max:150'], [], ['rackName' => 'nama rak']);

        Rack::updateOrCreate(
            ['id' => $this->rackId],
            [
                'name'      => trim($this->rackName),
                'branch_id' => $this->rackBranchId ?: null,
                'is_active' => $this->rackActive,
            ]
        );

        $this->resetRackForm();
        $this->dispatch('notify', type: 'success', message: 'Rak disimpan.');
    }

    public function toggleRack(string $id): void
    {
        $r = Rack::findOrFail($id);
        $r->update(['is_active' => ! $r->is_active]);
    }

    public function deleteRack(string $id): void
    {
        Rack::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Rak dihapus.');
    }
}

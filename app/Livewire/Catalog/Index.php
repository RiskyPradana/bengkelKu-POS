<?php

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ServiceItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $tab    = 'product'; // product | service
    public string $search = '';

    // ── Product Modal ──────────────────────────────────────────
    public bool    $showProductModal = false;
    public bool    $editingProduct   = false;
    public ?string $productId        = null;
    public string  $pSku             = '';
    public string  $pBarcode         = '';
    public string  $pName            = '';
    public string  $pCostPrice       = '';
    public string  $pSellPrice       = '';
    public bool    $pActive          = true;

    // ── Service Modal ──────────────────────────────────────────
    public bool    $showServiceModal = false;
    public bool    $editingService   = false;
    public ?string $serviceId        = null;
    public string  $sCode            = '';
    public string  $sName            = '';
    public string  $sPrice           = '';
    public bool    $sActive          = true;

    // ── Computed Properties (Livewire 3) ───────────────────────

    #[Computed]
    public function products(): Collection
    {
        $q = Product::latest();
        if ($this->search !== '' && $this->tab === 'product') {
            $s = $this->search;
            $q->where(fn ($qu) => $qu
                ->where('name',    'like', "%{$s}%")
                ->orWhere('sku',     'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%"));
        }
        return $q->limit(60)->get();
    }

    #[Computed]
    public function serviceItems(): Collection
    {
        $q = ServiceItem::latest();
        if ($this->search !== '' && $this->tab === 'service') {
            $s = $this->search;
            $q->where(fn ($qu) => $qu
                ->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%"));
        }
        return $q->limit(60)->get();
    }

    #[Computed]
    public function productCount(): int
    {
        return Product::count();
    }

    #[Computed]
    public function serviceCount(): int
    {
        return ServiceItem::count();
    }

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.catalog.index')
            ->layout('layouts.admin', [
                'title'     => 'Katalog — BengkelOS',
                'pageTitle' => 'Katalog',
                'pageSub'   => 'Master data sparepart dan jasa servis bengkel',
            ]);
    }

    // ── Tab ───────────────────────────────────────────────────

    public function switchTab(string $tab): void
    {
        $this->tab    = $tab;
        $this->search = '';
    }

    // ── Product CRUD ──────────────────────────────────────────

    public function openCreateProduct(): void
    {
        $this->reset(['productId', 'pSku', 'pBarcode', 'pName', 'pCostPrice', 'pSellPrice']);
        $this->resetErrorBag();
        $this->pActive        = true;
        $this->editingProduct = false;
        $this->showProductModal = true;
    }

    public function openEditProduct(string $id): void
    {
        $p = Product::findOrFail($id);
        $this->productId    = $p->id;
        $this->pSku         = $p->sku     ?? '';
        $this->pBarcode     = $p->barcode ?? '';
        $this->pName        = $p->name;
        $this->pCostPrice   = (string) (float) $p->cost_price;
        $this->pSellPrice   = (string) (float) $p->sell_price;
        $this->pActive      = (bool) $p->is_active;
        $this->resetErrorBag();
        $this->editingProduct   = true;
        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        $this->validate([
            'pName'      => 'required|min:2',
            'pSellPrice' => 'required|numeric|min:0',
            'pCostPrice' => 'nullable|numeric|min:0',
        ], [
            'pName.required'      => 'Nama sparepart wajib diisi.',
            'pSellPrice.required' => 'Harga jual wajib diisi.',
            'pSellPrice.numeric'  => 'Harga jual harus berupa angka.',
        ]);

        $data = [
            'sku'        => $this->pSku     ?: null,
            'barcode'    => $this->pBarcode ?: null,
            'name'       => $this->pName,
            'cost_price' => $this->pCostPrice !== '' ? (float) $this->pCostPrice : 0.0,
            'sell_price' => (float) $this->pSellPrice,
            'is_active'  => $this->pActive,
        ];

        if ($this->editingProduct && $this->productId) {
            Product::findOrFail($this->productId)->update($data);
            $msg = 'Sparepart berhasil diperbarui.';
        } else {
            Product::create($data);
            $msg = 'Sparepart baru berhasil ditambahkan.';
        }

        $this->showProductModal = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function toggleProduct(string $id): void
    {
        $p = Product::findOrFail($id);
        $p->update(['is_active' => !$p->is_active]);
    }

    public function deleteProduct(string $id): void
    {
        Product::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Sparepart dihapus.');
    }

    // ── Service CRUD ──────────────────────────────────────────

    public function openCreateService(): void
    {
        $this->reset(['serviceId', 'sCode', 'sName', 'sPrice']);
        $this->resetErrorBag();
        $this->sActive        = true;
        $this->editingService = false;
        $this->showServiceModal = true;
    }

    public function openEditService(string $id): void
    {
        $s = ServiceItem::findOrFail($id);
        $this->serviceId = $s->id;
        $this->sCode     = $s->code ?? '';
        $this->sName     = $s->name;
        $this->sPrice    = (string) (float) $s->price;
        $this->sActive   = (bool) $s->is_active;
        $this->resetErrorBag();
        $this->editingService   = true;
        $this->showServiceModal = true;
    }

    public function saveService(): void
    {
        $this->validate([
            'sName'  => 'required|min:2',
            'sPrice' => 'required|numeric|min:0',
        ], [
            'sName.required'  => 'Nama jasa wajib diisi.',
            'sPrice.required' => 'Harga jasa wajib diisi.',
            'sPrice.numeric'  => 'Harga harus berupa angka.',
        ]);

        $data = [
            'code'      => $this->sCode ?: null,
            'name'      => $this->sName,
            'price'     => (float) $this->sPrice,
            'is_active' => $this->sActive,
        ];

        if ($this->editingService && $this->serviceId) {
            ServiceItem::findOrFail($this->serviceId)->update($data);
            $msg = 'Jasa berhasil diperbarui.';
        } else {
            ServiceItem::create($data);
            $msg = 'Jasa baru berhasil ditambahkan.';
        }

        $this->showServiceModal = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function toggleService(string $id): void
    {
        $s = ServiceItem::findOrFail($id);
        $s->update(['is_active' => !$s->is_active]);
    }

    public function deleteService(string $id): void
    {
        ServiceItem::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Jasa dihapus.');
    }
}

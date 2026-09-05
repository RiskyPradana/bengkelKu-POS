<?php

namespace App\Livewire\Catalog;

use App\Domains\Catalog\Models\CategoryMargin;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductPriceHistory;
use App\Domains\Catalog\Models\ServiceItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $tab    = 'product'; // product | service
    public string $search = '';

    // ── Product Modal ────────────────────────────────
    public bool    $showProductModal = false;
    public bool    $editingProduct   = false;
    public ?string $productId        = null;
    public string  $pSku             = '';
    public string  $pBarcode         = '';
    public string  $pName            = '';
    public string  $pCostPrice       = '';
    public string  $pSellPrice       = '';
    public string  $pCategory        = '';
    public string  $pMarginPercent   = '';
    public bool    $pActive          = true;
    public string  $priceChangeNote  = '';

    // ── Riwayat harga ───────────────────────────────
    public bool    $showHistoryModal = false;
    public ?string $historyProductId = null;

    // ── Margin per kategori (owner) ────────────────────
    public bool    $showMarginModal    = false;
    public ?string $editingMarginId    = null;
    public string  $marginCategory     = '';
    public string  $marginPercentInput = '';

    // ── Service Modal ────────────────────────────────
    public bool    $showServiceModal = false;
    public bool    $editingService   = false;
    public ?string $serviceId        = null;
    public string  $sCode            = '';
    public string  $sName            = '';
    public string  $sPrice           = '';
    public bool    $sActive          = true;

    // ── Computed Properties (Livewire 3) ─────────────────

    #[Computed]
    public function isOwner(): bool
    {
        return (auth()->user()?->role ?? '') === 'owner';
    }

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

    #[Computed]
    public function categories(): Collection
    {
        return Product::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    #[Computed]
    public function categoryMargins(): Collection
    {
        return CategoryMargin::orderBy('category')->get();
    }

    #[Computed]
    public function productHistory(): Collection
    {
        if (! $this->historyProductId) {
            return collect();
        }

        return ProductPriceHistory::with('changedBy')
            ->where('product_id', $this->historyProductId)
            ->latest()
            ->get();
    }

    // ── Render ──────────────────────────────

    public function render(): View
    {
        return view('livewire.catalog.index')
            ->layout('layouts.admin', [
                'title'     => 'Katalog — BengkelOS',
                'pageTitle' => 'Katalog',
                'pageSub'   => 'Master data sparepart dan jasa servis bengkel',
            ]);
    }

    // ── Tab ────────────────────────────────

    public function switchTab(string $tab): void
    {
        $this->tab    = $tab;
        $this->search = '';
    }

    // ── Product CRUD ────────────────────────────

    public function openCreateProduct(): void
    {
        $this->reset(['productId', 'pSku', 'pBarcode', 'pName', 'pCostPrice', 'pSellPrice', 'pCategory', 'pMarginPercent', 'priceChangeNote']);
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
        $this->pSellPrice   = (string) (float) $p->sell_price;
        $this->pCategory    = $p->category ?? '';
        $this->pActive      = (bool) $p->is_active;
        $this->priceChangeNote = '';

        if ($this->isOwner) {
            $this->pCostPrice     = (string) (float) $p->cost_price;
            $this->pMarginPercent = $p->margin_percent !== null ? (string) (float) $p->margin_percent : '';
        } else {
            $this->pCostPrice     = '';
            $this->pMarginPercent = '';
        }

        $this->resetErrorBag();
        $this->editingProduct   = true;
        $this->showProductModal = true;
    }

    public function saveProduct(): void
    {
        $isOwner = $this->isOwner;

        $rules = [
            'pName'      => 'required|min:2',
            'pSellPrice' => 'required|numeric|min:0',
            'pCategory'  => 'nullable|string|max:100',
        ];

        if ($isOwner) {
            $rules['pCostPrice']     = 'nullable|numeric|min:0';
            $rules['pMarginPercent'] = 'nullable|numeric|min:0|max:1000';
        }

        $this->validate($rules, [
            'pName.required'      => 'Nama sparepart wajib diisi.',
            'pSellPrice.required' => 'Harga jual wajib diisi.',
            'pSellPrice.numeric'  => 'Harga jual harus berupa angka.',
        ]);

        $existing = ($this->editingProduct && $this->productId) ? Product::find($this->productId) : null;

        $newSellPrice = (float) $this->pSellPrice;

        $data = [
            'sku'        => $this->pSku     ?: null,
            'barcode'    => $this->pBarcode ?: null,
            'name'       => $this->pName,
            'sell_price' => $newSellPrice,
            'is_active'  => $this->pActive,
            'category'   => $this->pCategory ?: null,
        ];

        if ($isOwner) {
            $data['cost_price']     = $this->pCostPrice !== '' ? (float) $this->pCostPrice : 0.0;
            $data['margin_percent'] = $this->pMarginPercent !== '' ? (float) $this->pMarginPercent : null;
        }

        $priceChanged = false;

        if ($existing) {
            $newCostForCompare = $isOwner ? ($data['cost_price'] ?? 0.0) : (float) $existing->cost_price;

            if ((float) $existing->sell_price !== $newSellPrice || (float) $existing->cost_price !== (float) $newCostForCompare) {
                $priceChanged = true;
            }
        }

        if ($priceChanged && trim($this->priceChangeNote) === '') {
            $this->addError('priceChangeNote', 'Keterangan wajib diisi karena harga berubah.');

            return;
        }

        if ($existing && $priceChanged) {
            ProductPriceHistory::create([
                'product_id'     => $existing->id,
                'old_cost_price' => $existing->cost_price,
                'new_cost_price' => $data['cost_price'] ?? $existing->cost_price,
                'old_sell_price' => $existing->sell_price,
                'new_sell_price' => $newSellPrice,
                'note'           => trim($this->priceChangeNote),
                'changed_by'     => auth()->id(),
            ]);
        }

        if ($existing) {
            $existing->update($data);
            $msg = 'Sparepart berhasil diperbarui.';
        } else {
            if (! $isOwner) {
                $data['cost_price'] = 0.0;
            }
            Product::create($data);
            $msg = 'Sparepart baru berhasil ditambahkan.';
        }

        $this->showProductModal = false;
        $this->priceChangeNote  = '';
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

    // ── Riwayat harga ──────────────────────────

    public function openHistory(string $productId): void
    {
        $this->historyProductId = $productId;
        $this->showHistoryModal = true;
    }

    public function closeHistory(): void
    {
        $this->showHistoryModal = false;
        $this->historyProductId = null;
    }

    // ── Margin per kategori (owner only — diberi pagar di blade) ───

    public function openMarginSettings(): void
    {
        $this->reset(['editingMarginId', 'marginCategory', 'marginPercentInput']);
        $this->resetErrorBag();
        $this->showMarginModal = true;
    }

    public function editCategoryMargin(string $id): void
    {
        $m = CategoryMargin::findOrFail($id);
        $this->editingMarginId    = $m->id;
        $this->marginCategory     = $m->category;
        $this->marginPercentInput = (string) (float) $m->margin_percent;
    }

    public function saveCategoryMargin(): void
    {
        if (! $this->isOwner) {
            return;
        }

        $this->validate([
            'marginCategory'     => 'required|string|max:100',
            'marginPercentInput' => 'required|numeric|min:0|max:1000',
        ], [], [
            'marginCategory'     => 'kategori',
            'marginPercentInput' => 'margin persen',
        ]);

        CategoryMargin::updateOrCreate(
            ['category' => trim($this->marginCategory)],
            ['margin_percent' => (float) $this->marginPercentInput]
        );

        $this->reset(['editingMarginId', 'marginCategory', 'marginPercentInput']);
        $this->dispatch('notify', type: 'success', message: 'Margin kategori disimpan.');
    }

    public function deleteCategoryMargin(string $id): void
    {
        if (! $this->isOwner) {
            return;
        }

        CategoryMargin::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Margin kategori dihapus.');
    }

    // ── Service CRUD ────────────────────────────

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

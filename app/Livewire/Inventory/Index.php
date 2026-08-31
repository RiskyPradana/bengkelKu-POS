<?php

namespace App\Livewire\Inventory;

use App\Domains\Inventory\Models\BranchStock;
use App\Domains\Inventory\Models\StockMovement;
use App\Domains\Inventory\Models\InterBranchTransfer;
use App\Domains\Inventory\Services\StockService;
use App\Domains\Catalog\Models\Product;
use App\Domains\MasterData\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab    = 'stock';  // stock | movements | transfers | alerts
    public string $search = '';
    public string $filterBranch = '';
    public string $filterMovementType = '';

    // Adjustment Modal
    public bool    $showAdjustModal = false;
    public string  $adjProductId   = '';
    public string  $adjBranchId    = '';
    public string  $adjQty         = '';
    public string  $adjType        = 'in';
    public string  $adjNotes       = '';
    public string  $adjRef         = '';

    // Transfer Modal
    public bool   $showTransferModal = false;
    public string $trfFromBranch     = '';
    public string $trfToBranch       = '';
    public string $trfNotes          = '';
    public array  $trfItems          = []; // [{product_id, qty}]

    #[Computed]
    public function branches(): Collection
    {
        return Branch::orderBy('name')->get();
    }

    #[Computed]
    public function stockItems(): Collection
    {
        $q = BranchStock::with('product', 'branch')
            ->orderBy('updated_at', 'desc');
        if ($this->filterBranch) $q->where('branch_id', $this->filterBranch);
        if ($this->search) {
            $s = $this->search;
            $q->whereHas('product', fn($p) => $p->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%"));
        }
        return $q->limit(80)->get();
    }

    #[Computed]
    public function lowStockItems(): Collection
    {
        return BranchStock::with('product', 'branch')
            ->whereColumn('quantity', '<=', 'min_stock')
            ->orderBy('quantity')
            ->get();
    }

    #[Computed]
    public function recentMovements()
    {
        $q = StockMovement::with('product', 'branch')->latest();
        if ($this->filterBranch) $q->where('branch_id', $this->filterBranch);
        return $q->paginate(20);
    }

    #[Computed]
    public function transfers(): Collection
    {
        return InterBranchTransfer::with('fromBranch', 'toBranch', 'items.product')
            ->latest()->limit(30)->get();
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function alertCount(): int
    {
        return BranchStock::whereColumn('quantity', '<=', 'min_stock')->count();
    }

    #[Computed]
    public function adjustStock()
    {
        if ($this->adjProductId === '' || $this->adjBranchId === '') {
            return null;
        }

        try {
            return \App\Domains\Inventory\Models\BranchStock::with('product', 'branch')
                ->where('product_id', $this->adjProductId)
                ->where('branch_id', $this->adjBranchId)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function render(): View
    {
        return view('livewire.inventory.index')
            ->with([
                'stocks'        => $this->stockItems,
                'movements'     => $this->recentMovements,
                'transfers'     => $this->transfers,
                'lowStockItems' => $this->lowStockItems,
                'branches'      => $this->branches,
                'allProducts'   => $this->products,
                'adjustStock'   => $this->adjustStock,
            ])
            ->layout('layouts.admin', [
                'title'     => 'Inventaris — BengkelOS',
                'pageTitle' => 'Inventaris & Stok',
                'pageSub'   => 'Manajemen stok sparepart per cabang',
            ]);
    }

    public function switchTab(string $tab): void { $this->tab = $tab; }

    // ── Adjustment ────────────────────────────────────────────

    public function openAdjust(string $productId = '', string $branchId = ''): void
    {
        $this->reset(['adjProductId', 'adjBranchId', 'adjQty', 'adjNotes', 'adjRef']);
        $this->adjProductId = $productId;
        $this->adjBranchId  = $branchId ?: ($this->branches->first()?->id ?? '');
        $this->adjType      = 'in';
        $this->showAdjustModal = true;
    }

    public function saveAdjust(): void
    {
        $this->validate([
            'adjProductId' => 'required',
            'adjBranchId'  => 'required',
            'adjQty'       => 'required|integer|min:1',
            'adjType'      => 'required|in:in,out,adjustment',
        ], [
            'adjProductId.required' => 'Pilih produk.',
            'adjBranchId.required'  => 'Pilih cabang.',
            'adjQty.required'       => 'Jumlah wajib diisi.',
        ]);

        $delta = in_array($this->adjType, ['out']) ? -(int) $this->adjQty : (int) $this->adjQty;

        app(StockService::class)->adjust(
            $this->adjBranchId,
            $this->adjProductId,
            $delta,
            $this->adjType,
            $this->adjRef,
            $this->adjNotes,
            auth()->id(),
        );

        $this->showAdjustModal = false;
        $this->dispatch('notify', type: 'success', message: 'Stok berhasil disesuaikan.');
    }

    // ── Transfer ──────────────────────────────────────────────

    public function openTransfer(): void
    {
        $this->reset(['trfFromBranch', 'trfToBranch', 'trfNotes']);
        $this->trfItems = [['product_id' => '', 'qty' => '']];
        $this->showTransferModal = true;
    }

    public function addTransferItem(): void
    {
        $this->trfItems[] = ['product_id' => '', 'qty' => ''];
    }

    public function removeTransferItem(int $index): void
    {
        array_splice($this->trfItems, $index, 1);
    }

    public function saveTransfer(): void
    {
        $this->validate([
            'trfFromBranch'      => 'required|different:trfToBranch',
            'trfToBranch'        => 'required',
            'trfItems.*.product_id' => 'required',
            'trfItems.*.qty'     => 'required|integer|min:1',
        ], [
            'trfFromBranch.required'  => 'Pilih cabang asal.',
            'trfToBranch.required'    => 'Pilih cabang tujuan.',
            'trfFromBranch.different' => 'Cabang asal dan tujuan tidak boleh sama.',
        ]);

        $transfer = InterBranchTransfer::create([
            'transfer_number' => InterBranchTransfer::generateNumber(),
            'from_branch_id'  => $this->trfFromBranch,
            'to_branch_id'    => $this->trfToBranch,
            'status'          => 'pending',
            'notes'           => $this->trfNotes,
            'requested_by'    => auth()->id(),
        ]);

        foreach ($this->trfItems as $item) {
            $transfer->items()->create([
                'product_id'         => $item['product_id'],
                'quantity_requested' => (int) $item['qty'],
            ]);
        }

        $this->showTransferModal = false;
        $this->dispatch('notify', type: 'success', message: 'Transfer stok berhasil dibuat.');
    }
}

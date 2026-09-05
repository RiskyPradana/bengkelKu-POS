<?php

namespace App\Livewire\MobileMechanic;

use App\Domains\Catalog\Models\Product;
use App\Domains\Inventory\Models\BranchStock;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Models\WorkOrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Scanner extends Component
{
    public string  $scannedCode    = '';
    public ?string $foundProductId = null;
    public string  $selectedWoId   = '';
    public string  $qty            = '1';
    public bool    $showResult     = false;
    public bool    $addedToWo      = false;

    #[Computed]
    public function foundProduct(): ?Product
    {
        if (!$this->foundProductId) return null;
        return Product::find($this->foundProductId);
    }

    #[Computed]
    public function activeWorkOrders(): Collection
    {
        return WorkOrder::with('customer', 'vehicle')
            ->whereIn('status', [WorkOrderStatus::Pending->value, WorkOrderStatus::InProgress->value])
            ->latest()->limit(20)->get();
    }

    #[Computed]
    public function stock(): ?BranchStock
    {
        if (!$this->foundProductId) return null;
        $branchId = auth()->user()?->branch_id;
        if (!$branchId) return null;
        return BranchStock::where('product_id', $this->foundProductId)
            ->where('branch_id', $branchId)->first();
    }

    public function render(): View
    {
        return view('livewire.mobile.scanner')
            ->layout('layouts.mobile');
    }

    /**
     * Dipanggil dari Alpine/JS setelah kamera berhasil scan QR/barcode.
     */
    public function onScanned(string $code): void
    {
        $this->scannedCode    = trim($code);
        $this->addedToWo      = false;
        $this->showResult     = true;

        $product = Product::where('barcode', $this->scannedCode)
            ->orWhere('sku', $this->scannedCode)
            ->first();

        $this->foundProductId = $product?->id;
    }

    public function addToWorkOrder(): void
    {
        $this->validate([
            'selectedWoId' => 'required',
            'qty'          => 'required|integer|min:1',
        ], [
            'selectedWoId.required' => 'Pilih Work Order terlebih dahulu.',
        ]);

        if (!$this->foundProductId) {
            $this->dispatch('notify', type: 'error', message: 'Produk tidak ditemukan.');
            return;
        }

        $product = Product::findOrFail($this->foundProductId);
        $wo      = WorkOrder::findOrFail($this->selectedWoId);
        $qty     = (int) $this->qty;

        // Catatan: nama kolom di bawah ini HARUS sama dengan $fillable di
        // App\Domains\WorkOrder\Models\WorkOrderItem (qty/name/snapshot),
        // bukan quantity/name_snapshot/cost_price_snapshot seperti sebelumnya
        // — versi lama gagal disimpan karena kolom tersebut tidak ada.
        $existing = WorkOrderItem::where('work_order_id', $wo->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->increment('qty', $qty);
            $existing->forceFill(['subtotal' => $existing->qty * $existing->unit_price])->save();
        } else {
            WorkOrderItem::create([
                'work_order_id' => $wo->id,
                'product_id'    => $product->id,
                'item_type'     => 'product',
                'name'          => $product->name,
                'qty'           => $qty,
                'unit_price'    => $product->sell_price,
                'subtotal'      => $qty * $product->sell_price,
                'snapshot'      => [
                    'source'     => 'product',
                    'name'       => $product->name,
                    'sku'        => $product->sku,
                    'unit_price' => $product->sell_price,
                ],
            ]);
        }

        $this->addedToWo = true;
        $this->dispatch('notify', type: 'success', message: $product->name . ' ditambahkan ke ' . $wo->wo_number);
    }

    public function resetScan(): void
    {
        $this->scannedCode    = '';
        $this->foundProductId = null;
        $this->selectedWoId   = '';
        $this->qty            = '1';
        $this->showResult     = false;
        $this->addedToWo      = false;
    }
}

<?php

namespace App\Livewire\Purchasing;

use App\Domains\Catalog\Models\Product;
use App\Domains\MasterData\Models\Branch;
use App\Domains\Purchasing\Models\PurchaseOrder;
use App\Domains\Purchasing\Models\PurchasePayment;
use App\Domains\Purchasing\Models\Supplier;
use App\Domains\Purchasing\Services\PurchasingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $tab          = 'orders'; // suppliers | orders | payments
    public string $search       = '';
    public string $filterStatus = '';

    // ── Supplier Modal ──────────────────────────────────────────
    public bool    $showSupplierModal = false;
    public bool    $editingSupplier   = false;
    public ?string $supplierId        = null;
    public string  $supName           = '';
    public string  $supContact        = '';
    public string  $supPhone          = '';
    public string  $supEmail          = '';
    public string  $supAddress        = '';
    public bool    $supActive         = true;

    // ── Purchase Order Modal ─────────────────────────────────────
    public bool   $showOrderModal  = false;
    public string $orderSupplierId = '';
    public string $orderBranchId   = '';
    public string $orderDate       = '';
    public string $orderExpected   = '';
    public string $orderDiscount   = '0';
    public string $orderNotes      = '';

    /** @var array<int, array{product_id:string, quantity_ordered:int|string, unit_cost:int|string}> */
    public array $orderItems = [];

    // ── Receive Modal ────────────────────────────────────────────
    public bool    $showReceiveModal = false;
    public ?string $receivingPoId    = null;
    public string  $receiveBranchId  = '';

    /** @var array<string, int|string> item_id => qty */
    public array $receiveQty = [];

    // ── Payment Modal ────────────────────────────────────────────
    public bool    $showPaymentModal = false;
    public ?string $payingPoId       = null;
    public string  $paymentAmount    = '';
    public string  $paymentMethod    = 'cash';
    public string  $paymentReference = '';
    public string  $paymentNotes     = '';

    // ── Computed ─────────────────────────────────────────────────

    #[Computed]
    public function suppliers(): Collection
    {
        $q = Supplier::latest();
        if ($this->search !== '' && $this->tab === 'suppliers') {
            $s = $this->search;
            $q->where(fn ($qu) => $qu
                ->where('name', 'like', "%{$s}%")
                ->orWhere('contact_person', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"));
        }
        return $q->limit(100)->get();
    }

    #[Computed]
    public function activeSuppliers(): Collection
    {
        return Supplier::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function branches(): Collection
    {
        return Branch::orderBy('name')->get();
    }

    #[Computed]
    public function allProducts(): Collection
    {
        return Product::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function purchaseOrders(): Collection
    {
        $q = PurchaseOrder::with(['supplier', 'branch', 'items'])->latest();
        if ($this->filterStatus !== '' && $this->tab === 'orders') {
            $q->where('status', $this->filterStatus);
        }
        if ($this->search !== '' && $this->tab === 'orders') {
            $s = $this->search;
            $q->where(fn ($qu) => $qu
                ->where('po_number', 'like', "%{$s}%")
                ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$s}%")));
        }
        return $q->limit(100)->get();
    }

    #[Computed]
    public function paymentHistory(): Collection
    {
        return PurchasePayment::with('purchaseOrder.supplier')
            ->latest('paid_at')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function outstandingTotal(): float
    {
        return (float) PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])
            ->get()
            ->sum(fn ($po) => max(0, (float) $po->total - (float) $po->paid_amount));
    }

    // ── Render ──────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.purchasing.index')
            ->layout('layouts.admin', [
                'title'     => 'Pembelian & Supplier — BengkelOS',
                'pageTitle' => 'Pembelian & Supplier',
                'pageSub'   => 'Kelola supplier, pesanan pembelian, penerimaan barang, dan pembayaran hutang',
            ]);
    }

    public function switchTab(string $tab): void
    {
        $this->tab    = $tab;
        $this->search = '';
    }

    // ── Supplier CRUD ─────────────────────────────────────────────

    public function openCreateSupplier(): void
    {
        $this->reset(['supplierId', 'supName', 'supContact', 'supPhone', 'supEmail', 'supAddress']);
        $this->resetErrorBag();
        $this->supActive         = true;
        $this->editingSupplier   = false;
        $this->showSupplierModal = true;
    }

    public function openEditSupplier(string $id): void
    {
        $s = Supplier::findOrFail($id);
        $this->supplierId = $s->id;
        $this->supName    = $s->name;
        $this->supContact = $s->contact_person ?? '';
        $this->supPhone   = $s->phone ?? '';
        $this->supEmail   = $s->email ?? '';
        $this->supAddress = $s->address ?? '';
        $this->supActive  = (bool) $s->is_active;
        $this->resetErrorBag();
        $this->editingSupplier   = true;
        $this->showSupplierModal = true;
    }

    public function saveSupplier(): void
    {
        $this->validate([
            'supName'  => 'required|min:2',
            'supEmail' => 'nullable|email',
        ], [
            'supName.required' => 'Nama supplier wajib diisi.',
            'supEmail.email'   => 'Format email tidak valid.',
        ]);

        $data = [
            'name'           => $this->supName,
            'contact_person' => $this->supContact ?: null,
            'phone'          => $this->supPhone ?: null,
            'email'          => $this->supEmail ?: null,
            'address'        => $this->supAddress ?: null,
            'is_active'      => $this->supActive,
        ];

        if ($this->editingSupplier && $this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($data);
            $msg = 'Supplier berhasil diperbarui.';
        } else {
            Supplier::create($data);
            $msg = 'Supplier baru berhasil ditambahkan.';
        }

        $this->showSupplierModal = false;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function toggleSupplier(string $id): void
    {
        $s = Supplier::findOrFail($id);
        $s->update(['is_active' => !$s->is_active]);
    }

    public function deleteSupplier(string $id): void
    {
        Supplier::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Supplier dihapus.');
    }

    // ── Purchase Order ────────────────────────────────────────────

    public function openCreateOrder(): void
    {
        $this->reset(['orderSupplierId', 'orderBranchId', 'orderExpected', 'orderNotes']);
        $this->resetErrorBag();
        $this->orderDate     = now()->toDateString();
        $this->orderDiscount = '0';
        $this->orderItems    = [['product_id' => '', 'quantity_ordered' => 1, 'unit_cost' => '0']];
        $this->showOrderModal = true;
    }

    public function addOrderItem(): void
    {
        $this->orderItems[] = ['product_id' => '', 'quantity_ordered' => 1, 'unit_cost' => '0'];
    }

    public function removeOrderItem(int $idx): void
    {
        unset($this->orderItems[$idx]);
        $this->orderItems = array_values($this->orderItems);
    }

    public function saveOrder(): void
    {
        $this->validate([
            'orderSupplierId'               => 'required',
            'orderBranchId'                 => 'required',
            'orderItems'                    => 'required|array|min:1',
            'orderItems.*.product_id'       => 'required',
            'orderItems.*.quantity_ordered' => 'required|integer|min:1',
            'orderItems.*.unit_cost'        => 'required|numeric|min:0',
        ], [
            'orderSupplierId.required' => 'Supplier wajib dipilih.',
            'orderBranchId.required'   => 'Cabang tujuan wajib dipilih.',
            'orderItems.required'      => 'Minimal 1 item pembelian.',
        ]);

        app(PurchasingService::class)->createPurchaseOrder([
            'supplier_id'   => $this->orderSupplierId,
            'branch_id'     => $this->orderBranchId,
            'order_date'    => $this->orderDate ?: now()->toDateString(),
            'expected_date' => $this->orderExpected ?: null,
            'discount'      => (float) $this->orderDiscount,
            'notes'         => $this->orderNotes ?: null,
            'created_by'    => auth()->id(),
        ], $this->orderItems);

        $this->showOrderModal = false;
        $this->dispatch('notify', type: 'success', message: 'Pesanan pembelian berhasil dibuat.');
    }

    public function markOrdered(string $id): void
    {
        $po = PurchaseOrder::findOrFail($id);
        app(PurchasingService::class)->markOrdered($po);
        $this->dispatch('notify', type: 'success', message: 'Pesanan ditandai sudah dipesan ke supplier.');
    }

    public function cancelOrder(string $id): void
    {
        $po = PurchaseOrder::findOrFail($id);
        app(PurchasingService::class)->cancel($po);
        $this->dispatch('notify', type: 'success', message: 'Pesanan pembelian dibatalkan.');
    }

    // ── Receive Items ─────────────────────────────────────────────

    public function openReceive(string $id): void
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        $this->receivingPoId   = $po->id;
        $this->receiveBranchId = $po->branch_id ?? '';
        $this->receiveQty      = [];
        foreach ($po->items as $item) {
            $remaining = max(0, $item->quantity_ordered - $item->quantity_received);
            $this->receiveQty[$item->id] = $remaining;
        }
        $this->resetErrorBag();
        $this->showReceiveModal = true;
    }

    public function saveReceive(): void
    {
        $this->validate([
            'receiveBranchId' => 'required',
        ], [
            'receiveBranchId.required' => 'Cabang penerima wajib dipilih.',
        ]);

        $po = PurchaseOrder::findOrFail($this->receivingPoId);

        app(PurchasingService::class)->receiveItems(
            $po,
            array_map(fn ($q) => (int) $q, $this->receiveQty),
            $this->receiveBranchId,
            auth()->id(),
        );

        $this->showReceiveModal = false;
        $this->dispatch('notify', type: 'success', message: 'Penerimaan barang tersimpan & stok diperbarui.');
    }

    // ── Payment ───────────────────────────────────────────────────

    public function openPayment(string $id): void
    {
        $po = PurchaseOrder::findOrFail($id);
        $this->payingPoId       = $po->id;
        $this->paymentAmount    = (string) max(0, (float) $po->total - (float) $po->paid_amount);
        $this->paymentMethod    = 'cash';
        $this->paymentReference = '';
        $this->paymentNotes     = '';
        $this->resetErrorBag();
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1',
            'paymentMethod' => 'required',
        ], [
            'paymentAmount.required' => 'Jumlah pembayaran wajib diisi.',
            'paymentAmount.min'      => 'Jumlah pembayaran harus lebih dari 0.',
        ]);

        $po = PurchaseOrder::findOrFail($this->payingPoId);

        app(PurchasingService::class)->recordPayment(
            $po,
            (float) $this->paymentAmount,
            $this->paymentMethod,
            $this->paymentReference ?: null,
            $this->paymentNotes ?: null,
        );

        $this->showPaymentModal = false;
        $this->dispatch('notify', type: 'success', message: 'Pembayaran berhasil dicatat.');
    }
}

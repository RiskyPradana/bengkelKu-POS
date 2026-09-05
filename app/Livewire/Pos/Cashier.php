<?php

namespace App\Livewire\Pos;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ServiceItem;
use App\Domains\Inventory\Models\BranchStock;
use App\Domains\MasterData\Models\AppSetting;
use App\Domains\MasterData\Models\Branch;
use App\Domains\POS\Enums\InvoiceStatus;
use App\Domains\POS\Enums\PaymentMethod;
use App\Domains\POS\Models\Invoice;
use App\Domains\POS\Services\POSService;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Models\WorkOrderItem;
use App\Domains\WorkOrder\Services\WorkOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Cashier extends Component
{
    // Fix #5: Pisahkan search SPK dan search katalog
    public string $workOrderSearch = '';
    public string $catalogSearch   = '';

    public string  $category         = 'all';
    public ?string $selectedWorkOrderId = null;

    // Fix #13: Tambah label (Rp) di view agar tidak ambigu
    public string $discount = '0';
    public string $tax      = '0';

    // Fix #1: Payment input properties (sudah ada, tinggal wiring UI)
    public string $paymentMethod    = '';
    public string $paymentAmount    = '0';
    public string $paymentReference = '';

    public array $notice = [];

    protected $listeners = [
        'refreshCashier' => '$refresh',
    ];

    public function mount(): void
    {
        $this->paymentMethod = PaymentMethod::Cash->value;

        $this->selectedWorkOrderId = WorkOrder::query()->latest()->value('id');

        if ($this->selectedWorkOrderId !== null) {
            $this->hydrateFormState($this->selectedWorkOrder);
        }
    }

    public function render(): View
    {
        // Sesi 11: Kasir tampil full-screen secara default (sidebar disembunyikan),
        // tapi kasir tetap bisa menampilkannya lagi lewat tombol di topbar.
        return view('livewire.pos.cashier')
            ->layout('layouts.app', ['title' => 'Kasir — BengkelOS', 'startCollapsed' => true]);
    }

    // ──────────────────────────────────────────────
    // Actions
    // ──────────────────────────────────────────────

    public function setCategory(string $category): void
    {
        if (! in_array($category, ['all', 'product', 'service'], true)) {
            return;
        }
        $this->category = $category;
    }

    public function setPaymentMethod(string $method): void
    {
        try {
            PaymentMethod::from($method);
            $this->paymentMethod = $method;
        } catch (\ValueError) {
            // abaikan nilai tidak valid
        }
    }

    public function selectWorkOrder(string $workOrderId): void
    {
        $workOrder = WorkOrder::query()
            ->with(['customer', 'vehicle', 'branch', 'items', 'invoice.payments'])
            ->find($workOrderId);

        if (! $workOrder instanceof WorkOrder) {
            $this->notify('danger', 'SPK tidak ditemukan', 'Pilih SPK lain dari daftar.');
            return;
        }

        $this->selectedWorkOrderId = $workOrder->id;
        $this->hydrateFormState($workOrder);
    }

    public function addCatalogItem(string $type, string $itemId): void
    {
        $workOrder = $this->selectedWorkOrder;

        if (! $workOrder instanceof WorkOrder) {
            $this->notify('warning', 'Pilih SPK dulu', 'Item hanya bisa ditambahkan ke SPK aktif.');
            return;
        }

        if ($this->isPaidWorkOrder($workOrder)) {
            $this->notify('warning', 'SPK sudah lunas', 'Tidak bisa menambah item ke SPK yang sudah dibayar.');
            return;
        }

        if ($type === 'product') {
            $product = Product::query()->find($itemId);
            if (! $product instanceof Product) {
                $this->notify('danger', 'Sparepart tidak ditemukan', 'Item katalog sudah tidak tersedia.');
                return;
            }

            $existing = $workOrder->items()->where('product_id', $product->id)->first();
            $nextQty  = $existing instanceof WorkOrderItem ? $existing->qty + 1 : 1;

            // Sesi 11: cek sisa stok cabang sebelum menambah sparepart ke keranjang.
            if (! $this->hasEnoughStock($product->id, $nextQty, $shortage)) {
                $this->notify('warning', 'Stok tidak cukup', "Sisa stok {$product->name} di cabang ini: {$shortage} unit.");
                return;
            }

            if ($existing instanceof WorkOrderItem) {
                app(WorkOrderService::class)->updateItemQuantity($existing, $nextQty);
            } else {
                app(WorkOrderService::class)->addProductItem($workOrder, $product, 1);
            }
        } elseif ($type === 'service') {
            $serviceItem = ServiceItem::query()->find($itemId);
            if (! $serviceItem instanceof ServiceItem) {
                $this->notify('danger', 'Jasa tidak ditemukan', 'Item katalog sudah tidak tersedia.');
                return;
            }
            $existing = $workOrder->items()->where('service_item_id', $serviceItem->id)->first();
            if ($existing instanceof WorkOrderItem) {
                app(WorkOrderService::class)->updateItemQuantity($existing, $existing->qty + 1);
            } else {
                app(WorkOrderService::class)->addServiceItem($workOrder, $serviceItem, 1);
            }
        }

        $this->selectWorkOrder($workOrder->id);
        $this->notify('success', 'Item ditambahkan', 'Keranjang SPK sudah diperbarui.');
    }

    public function changeLineQuantity(string $lineItemId, int $delta): void
    {
        $workOrder = $this->selectedWorkOrder;
        if (! $workOrder instanceof WorkOrder) {
            return;
        }
        $lineItem = $workOrder->items()->find($lineItemId);
        if (! $lineItem instanceof WorkOrderItem) {
            return;
        }
        $nextQuantity = max(1, $lineItem->qty + $delta);

        // Sesi 11: cek stok kalau jumlah sparepart di keranjang dinaikkan.
        if ($delta > 0 && $lineItem->isProduct() && $lineItem->product_id) {
            if (! $this->hasEnoughStock($lineItem->product_id, $nextQuantity, $shortage)) {
                $this->notify('warning', 'Stok tidak cukup', "Sisa stok di cabang ini: {$shortage} unit.");
                return;
            }
        }

        app(WorkOrderService::class)->updateItemQuantity($lineItem, $nextQuantity);
        $this->selectWorkOrder($workOrder->id);
    }

    public function removeLineItem(string $lineItemId): void
    {
        $workOrder = $this->selectedWorkOrder;
        if (! $workOrder instanceof WorkOrder) {
            return;
        }
        $lineItem = $workOrder->items()->find($lineItemId);
        if (! $lineItem instanceof WorkOrderItem) {
            return;
        }
        app(WorkOrderService::class)->removeItem($lineItem);
        $this->selectWorkOrder($workOrder->id);
        $this->notify('success', 'Item dihapus', 'Keranjang sudah diperbarui.');
    }

    public function createInvoice(): void
    {
        $workOrder = $this->selectedWorkOrder;
        if (! $workOrder instanceof WorkOrder) {
            $this->notify('warning', 'Pilih SPK dulu', 'Invoice dibuat dari SPK aktif.');
            return;
        }
        if ($this->selectedInvoice instanceof Invoice) {
            $this->notify('info', 'Invoice sudah ada', $this->selectedInvoice->invoice_number);
            return;
        }
        // Fix #2: Izinkan InProgress juga bisa buat invoice
        if (! $this->canIssueInvoice($workOrder)) {
            $this->notify('warning', 'SPK belum siap ditagih', 'Status SPK harus minimal In Progress sebelum invoice dibuat.');
            return;
        }
        app(POSService::class)->createInvoiceFromWorkOrder($workOrder, [
            'discount' => $this->normalizeAmount($this->discount),
            'tax'      => $this->normalizeAmount($this->tax),
        ]);
        $this->selectWorkOrder($workOrder->id);
        $this->notify('success', 'Invoice berhasil dibuat', 'Siap lanjut ke pembayaran.');
    }

    public function recordPayment(): void
    {
        $workOrder = $this->selectedWorkOrder;
        if (! $workOrder instanceof WorkOrder) {
            $this->notify('warning', 'Pilih SPK dulu', 'Pembayaran hanya bisa dicatat pada SPK aktif.');
            return;
        }

        if (! $this->selectedInvoice instanceof Invoice) {
            $this->createInvoice();
            $this->selectWorkOrder($workOrder->id);
        }

        $invoice = $this->selectedInvoice;
        if (! $invoice instanceof Invoice) {
            return;
        }

        $amount = $this->normalizeAmount($this->paymentAmount);
        if ($amount <= 0) {
            $amount = (float) $invoice->outstanding_amount;
        }
        if ($amount <= 0) {
            $this->notify('warning', 'Tidak ada tagihan tersisa', 'Invoice ini sudah lunas.');
            return;
        }

        $payment = app(POSService::class)->recordPayment($invoice, [
            'method'           => $this->paymentMethod,
            'amount'           => $amount,
            'reference_number' => $this->paymentReference !== '' ? $this->paymentReference : null,
            'paid_at'          => now(),
        ]);

        // Reset payment fields setelah bayar
        $this->paymentAmount    = '0';
        $this->paymentReference = '';

        $this->selectWorkOrder($workOrder->id);
        $this->notify('success', '✓ Pembayaran berhasil', 'Rp ' . number_format((float) $payment->amount, 0, ',', '.'));
    }

    // Fix #11: Hold Transaksi — reset selection
    public function holdTransaction(): void
    {
        $this->selectedWorkOrderId = null;
        $this->discount            = '0';
        $this->tax                 = '0';
        $this->paymentAmount       = '0';
        $this->paymentReference    = '';
        $this->notify('info', 'Transaksi di-hold', 'Pilih SPK lain untuk melanjutkan.');
    }

    // Fix #7–10: Quick Actions
    public function printReceipt(): void
    {
        if (! $this->selectedInvoice instanceof Invoice) {
            $this->notify('warning', 'Belum ada invoice', 'Buat invoice terlebih dahulu sebelum mencetak struk.');
            return;
        }
        $this->dispatch('printReceipt');
    }

    // Fix #6: Clear notice
    public function clearNotice(): void
    {
        $this->notice = [];
    }

    // ──────────────────────────────────────────────
    // Computed Properties
    // ──────────────────────────────────────────────

    public function getActiveBranchProperty(): ?Branch
    {
        $userBranch = auth()->user()?->branch;
        if ($userBranch instanceof Branch) {
            return $userBranch;
        }
        return Branch::query()->where('is_active', true)->orderBy('name')->first();
    }

    public function getSelectedWorkOrderProperty(): ?WorkOrder
    {
        if ($this->selectedWorkOrderId === null) {
            return null;
        }
        return WorkOrder::query()
            ->with(['customer', 'vehicle', 'branch', 'items.product', 'items.serviceItem', 'invoice.payments'])
            ->find($this->selectedWorkOrderId);
    }

    public function getSelectedInvoiceProperty(): ?Invoice
    {
        return $this->selectedWorkOrder?->invoice;
    }

    // Fix #16: Gabungkan 2 COUNT query menjadi 1 iterasi
    public function getCategoryTabsProperty(): array
    {
        $productCount = Product::query()->where('is_active', true)->count();
        $serviceCount = ServiceItem::query()->where('is_active', true)->count();

        return [
            ['key' => 'all',     'label' => 'Semua',     'count' => $productCount + $serviceCount],
            ['key' => 'product', 'label' => 'Sparepart', 'count' => $productCount],
            ['key' => 'service', 'label' => 'Jasa',      'count' => $serviceCount],
        ];
    }

    // Fix #5: Gunakan workOrderSearch (bukan search lama)
    public function getQuickWorkOrdersProperty(): Collection
    {
        $search = trim($this->workOrderSearch);

        $query = WorkOrder::query()
            ->with(['customer', 'vehicle', 'invoice'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('status', 'like', '%'.$search.'%')
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('vehicle',  fn ($q) => $q->where('plate_number', 'like', '%'.$search.'%'))
                    ->orWhereHas('invoice',  fn ($q) => $q->where('invoice_number', 'like', '%'.$search.'%'));
            });
        }

        return $query->limit(8)->get()->map(function (WorkOrder $workOrder): array {
            $status  = $workOrder->status instanceof WorkOrderStatus
                ? $workOrder->status
                : WorkOrderStatus::make((string) $workOrder->status);
            $invoice = $workOrder->invoice;
            $summary = $this->summarizeWorkOrder($workOrder);

            return [
                'id'          => $workOrder->id,
                'customer'    => $workOrder->customer?->name ?? 'Pelanggan',
                'plate'       => $workOrder->vehicle?->plate_number ?? '-',
                'status'      => $status->label(),
                'status_tone' => match ($status) {
                    WorkOrderStatus::Pending    => 'amber',
                    WorkOrderStatus::InProgress => 'sky',
                    WorkOrderStatus::Completed  => 'emerald',
                    WorkOrderStatus::Paid       => 'slate',
                },
                'invoice'     => $invoice?->invoice_number,
                'total'       => $summary['grand_total'],
                'selected'    => $this->selectedWorkOrderId === $workOrder->id,
            ];
        });
    }

    // Fix #5: Gunakan catalogSearch
    public function getCatalogItemsProperty(): Collection
    {
        $search = trim($this->catalogSearch);
        $items  = collect();

        if (in_array($this->category, ['all', 'product'], true)) {
            $productQuery = Product::query()->where('is_active', true);
            if ($search !== '') {
                $productQuery->where(
                    fn ($b) => $b
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('barcode', 'like', '%'.$search.'%')
                );
            }
            $items = $items->merge(
                $productQuery->orderBy('name')->limit(12)->get()->map(fn (Product $p) => [
                    'id'         => $p->id,
                    'type'       => 'product',
                    'type_label' => 'Sparepart',
                    'name'       => $p->name,
                    'price'      => (float) $p->sell_price,
                    'meta'       => $p->sku,
                    'tone'       => 'emerald',
                    'in_cart'    => $this->hasItemInCart('product', $p->id),
                ])
            );
        }

        if (in_array($this->category, ['all', 'service'], true)) {
            $serviceQuery = ServiceItem::query()->where('is_active', true);
            if ($search !== '') {
                $serviceQuery->where(
                    fn ($b) => $b
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                );
            }
            $items = $items->merge(
                $serviceQuery->orderBy('name')->limit(12)->get()->map(fn (ServiceItem $s) => [
                    'id'         => $s->id,
                    'type'       => 'service',
                    'type_label' => 'Jasa',
                    'name'       => $s->name,
                    'price'      => (float) $s->price,
                    'meta'       => $s->code,
                    'tone'       => 'sky',
                    'in_cart'    => $this->hasItemInCart('service', $s->id),
                ])
            );
        }

        return $items->values();
    }

    public function getCartLinesProperty(): Collection
    {
        $workOrder = $this->selectedWorkOrder;
        if (! $workOrder instanceof WorkOrder) {
            return collect();
        }
        return $workOrder->items
            ->sortByDesc('created_at')
            ->values()
            ->map(fn (WorkOrderItem $item) => [
                'id'           => $item->id,
                'name'         => $item->name,
                'qty'          => $item->qty,
                'unit_price'   => (float) $item->unit_price,
                'subtotal'     => (float) $item->subtotal,
                'type'         => $item->item_type,
                'source_label' => $item->isProduct() ? 'Sparepart' : 'Jasa',
            ]);
    }

    public function getPaymentSummaryProperty(): array
    {
        $workOrder = $this->selectedWorkOrder;
        if (! $workOrder instanceof WorkOrder) {
            return [
                'subtotal'       => 0,
                'discount'       => 0,
                'tax'            => 0,
                'grand_total'    => 0,
                'paid'           => 0,
                'outstanding'    => 0,
                'status'         => null,
                'invoice_number' => null,
            ];
        }
        return $this->summarizeWorkOrder($workOrder);
    }

    // Fix #1: Computed property untuk metode pembayaran
    public function getPaymentMethodsProperty(): array
    {
        return array_map(fn (PaymentMethod $m) => [
            'value' => $m->value,
            'label' => $m->value,
            'icon'  => match ($m) {
                PaymentMethod::Cash     => '💵',
                PaymentMethod::QRIS     => '📱',
                PaymentMethod::Transfer => '🏦',
                PaymentMethod::Debit    => '💳',
            },
            // Full class strings agar Tailwind tidak purge
            'selected_class'   => match ($m) {
                PaymentMethod::Cash     => 'border-emerald-500 bg-emerald-500 text-white shadow-emerald-500/20',
                PaymentMethod::QRIS     => 'border-violet-500 bg-violet-500 text-white shadow-violet-500/20',
                PaymentMethod::Transfer => 'border-sky-500 bg-sky-500 text-white shadow-sky-500/20',
                PaymentMethod::Debit    => 'border-amber-500 bg-amber-500 text-white shadow-amber-500/20',
            },
            'unselected_class' => 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300 hover:bg-white',
        ], PaymentMethod::cases());
    }

    // Sesi 12: Pengaturan printer struk thermal (lebar kertas & ukuran font),
    // dipakai saat mencetak struk supaya sesuai printer thermal 58mm/80mm.
    public function getPrinterSettingsProperty(): array
    {
        $saved = AppSetting::getMany([
            'printer.receipt_paper_width',
            'printer.receipt_font_size',
        ], [
            'printer.receipt_paper_width' => '58',
            'printer.receipt_font_size'   => 12,
        ]);

        $width = (string) $saved['printer.receipt_paper_width'];

        return [
            'paper_width_mm' => $width,
            // Perkiraan lebar cetak dalam px (96dpi) untuk div struk di browser.
            'paper_width_px' => $width === '80' ? 302 : 219,
            'font_size'      => (int) $saved['printer.receipt_font_size'],
        ];
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function summarizeWorkOrder(WorkOrder $workOrder): array
    {
        $invoice   = $workOrder->invoice;
        $subtotal  = (float) ($invoice?->subtotal ?? $workOrder->price_snapshot['total_amount'] ?? $workOrder->items->sum('subtotal'));
        $discount  = (float) ($invoice?->discount ?? $this->normalizeAmount($this->discount));
        $tax       = (float) ($invoice?->tax      ?? $this->normalizeAmount($this->tax));
        $grandTotal = (float) ($invoice?->grand_total ?? max(0, $subtotal - $discount + $tax));
        $paid      = $invoice ? (float) $invoice->paid_amount : 0;
        $outstanding = max(0, $grandTotal - $paid);

        return [
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'tax'            => $tax,
            'grand_total'    => $grandTotal,
            'paid'           => $paid,
            'outstanding'    => $outstanding,
            'status'         => $invoice?->status instanceof InvoiceStatus ? $invoice->status->label() : null,
            'invoice_number' => $invoice?->invoice_number,
        ];
    }

    // Fix #15: Perbaiki rounding — jangan cast ke int
    private function hydrateFormState(?WorkOrder $workOrder): void
    {
        if (! $workOrder instanceof WorkOrder) {
            $this->discount      = '0';
            $this->tax           = '0';
            $this->paymentAmount = '0';
            return;
        }
        $summary = $this->summarizeWorkOrder($workOrder);
        $this->discount      = (string) round($summary['discount'], 2);
        $this->tax           = (string) round($summary['tax'], 2);
        $this->paymentAmount = (string) round(
            $summary['outstanding'] > 0 ? $summary['outstanding'] : $summary['grand_total'],
            2
        );
    }

    private function hasItemInCart(string $type, string $itemId): bool
    {
        $workOrder = $this->selectedWorkOrder;
        if (! $workOrder instanceof WorkOrder) {
            return false;
        }
        return $type === 'product'
            ? $workOrder->items->contains(fn (WorkOrderItem $item) => $item->product_id === $itemId)
            : $workOrder->items->contains(fn (WorkOrderItem $item) => $item->service_item_id === $itemId);
    }

    /**
     * Sesi 11: Cek apakah stok sparepart di cabang aktif cukup untuk jumlah
     * yang diminta. Kalau tidak ada data cabang/stok sama sekali (misalnya
     * belum pernah dicatat lewat modul Inventory), anggap tidak dibatasi
     * supaya tidak memblokir transaksi karena data yang belum lengkap.
     */
    private function hasEnoughStock(string $productId, int $requiredQty, ?int &$availableOut = null): bool
    {
        $branchId = $this->activeBranch?->id;
        if (! $branchId) {
            $availableOut = 0;
            return true;
        }

        $stock = BranchStock::query()
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $stock) {
            // Belum ada record stok untuk kombinasi produk+cabang ini —
            // biarkan lewat, supaya sparepart yang belum diinput stoknya
            // lewat modul Inventory tidak mendadak tidak bisa dijual.
            $availableOut = 0;
            return true;
        }

        $availableOut = (int) $stock->quantity;
        return $availableOut >= $requiredQty;
    }

    // Fix #2: Izinkan InProgress dan Completed
    private function canIssueInvoice(WorkOrder $workOrder): bool
    {
        return in_array($workOrder->status, [
            WorkOrderStatus::InProgress,
            WorkOrderStatus::Completed,
        ], true);
    }

    private function isPaidWorkOrder(WorkOrder $workOrder): bool
    {
        return $workOrder->status === WorkOrderStatus::Paid;
    }

    private function normalizeAmount(string|int|float|null $value): float
    {
        $normalized = preg_replace('/[^0-9.]/', '', (string) $value);
        return (float) ($normalized !== '' ? $normalized : 0);
    }

    private function notify(string $type, string $title, string $message = ''): void
    {
        $this->notice = [
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
        ];
    }
}

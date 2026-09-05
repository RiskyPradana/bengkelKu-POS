<?php

namespace App\Domains\Purchasing\Services;

use App\Domains\Inventory\Services\StockService;
use App\Domains\Purchasing\Models\PurchaseOrder;
use App\Domains\Purchasing\Models\PurchaseOrderItem;
use App\Domains\Purchasing\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;

class PurchasingService
{
    public function __construct(private StockService $stockService)
    {
    }

    public function generatePoNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $count  = PurchaseOrder::where('po_number', 'like', $prefix . '%')->count();

        return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat pesanan pembelian baru (status: draft).
     *
     * @param array<string, mixed> $orderData
     * @param array<int, array{product_id:string, quantity_ordered:int|string, unit_cost:int|string}> $items
     */
    public function createPurchaseOrder(array $orderData, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($orderData, $items) {
            $subtotal = 0.0;
            foreach ($items as $item) {
                $subtotal += ((float) $item['unit_cost']) * ((int) $item['quantity_ordered']);
            }
            $discount = (float) ($orderData['discount'] ?? 0);
            $total    = max(0, $subtotal - $discount);

            $po = PurchaseOrder::create([
                'po_number'      => $this->generatePoNumber(),
                'supplier_id'    => $orderData['supplier_id'],
                'branch_id'      => $orderData['branch_id'] ?? null,
                'status'         => 'draft',
                'order_date'     => $orderData['order_date'] ?? now()->toDateString(),
                'expected_date'  => $orderData['expected_date'] ?? null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total'          => $total,
                'paid_amount'    => 0,
                'payment_status' => 'belum_lunas',
                'notes'          => $orderData['notes'] ?? null,
                'created_by'     => $orderData['created_by'] ?? null,
            ]);

            foreach ($items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'],
                    'quantity_ordered'  => (int) $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost'         => (float) $item['unit_cost'],
                    'subtotal'          => ((float) $item['unit_cost']) * ((int) $item['quantity_ordered']),
                ]);
            }

            return $po->load('items');
        });
    }

    public function markOrdered(PurchaseOrder $po): void
    {
        $po->update(['status' => 'ordered']);
    }

    public function cancel(PurchaseOrder $po): void
    {
        $po->update(['status' => 'cancelled']);
    }

    /**
     * Terima barang dari PO — otomatis menambah stok cabang lewat StockService.
     *
     * @param array<string, int|string> $receivedQuantities [purchase_order_item_id => qty]
     */
    public function receiveItems(PurchaseOrder $po, array $receivedQuantities, string $branchId, ?string $userId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $receivedQuantities, $branchId, $userId) {
            $items = $po->items()->get();

            foreach ($items as $item) {
                $qty = (int) ($receivedQuantities[$item->id] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $remaining = $item->quantity_ordered - $item->quantity_received;
                $qty       = min($qty, max(0, $remaining));
                if ($qty <= 0) {
                    continue;
                }

                $item->increment('quantity_received', $qty);

                $this->stockService->adjust(
                    $branchId,
                    $item->product_id,
                    $qty,
                    'in',
                    $po->po_number,
                    'Penerimaan pembelian ' . $po->po_number,
                    $userId,
                );
            }

            $po->refresh();
            $totalOrdered  = $po->items()->sum('quantity_ordered');
            $totalReceived = $po->items()->sum('quantity_received');

            $status = 'ordered';
            if ($totalOrdered > 0 && $totalReceived >= $totalOrdered) {
                $status = 'received';
            } elseif ($totalReceived > 0) {
                $status = 'partially_received';
            }

            $po->update([
                'status'      => $status,
                'received_at' => $status === 'received' ? now() : $po->received_at,
            ]);

            return $po->refresh();
        });
    }

    public function recordPayment(PurchaseOrder $po, float $amount, string $method, ?string $referenceNumber = null, ?string $notes = null): PurchasePayment
    {
        return DB::transaction(function () use ($po, $amount, $method, $referenceNumber, $notes) {
            $payment = PurchasePayment::create([
                'purchase_order_id' => $po->id,
                'method'            => $method,
                'amount'            => $amount,
                'reference_number'  => $referenceNumber,
                'paid_at'           => now(),
                'notes'             => $notes,
            ]);

            $po->refresh();
            $paidAmount    = (float) $po->payments()->sum('amount');
            $paymentStatus = 'belum_lunas';
            if ((float) $po->total > 0 && $paidAmount >= (float) $po->total) {
                $paymentStatus = 'lunas';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'sebagian';
            }

            $po->update([
                'paid_amount'    => $paidAmount,
                'payment_status' => $paymentStatus,
            ]);

            return $payment;
        });
    }
}

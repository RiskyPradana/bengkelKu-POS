<?php

namespace App\Domains\WorkOrder\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ServiceItem;
use App\Domains\Commission\Services\CommissionService;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Models\WorkOrderAssignment;
use App\Domains\WorkOrder\Models\WorkOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderService
{
    public function create(array $data): WorkOrder
    {
        return DB::transaction(function () use ($data): WorkOrder {
            $data['status'] = WorkOrderStatus::make($data['status'] ?? WorkOrderStatus::Pending)->value;
            $data['price_snapshot'] = $data['price_snapshot'] ?? [];
            $data['wo_number'] = $data['wo_number'] ?? $this->generateWoNumber();

            return WorkOrder::create($data);
        });
    }

    public function assignMechanic(WorkOrder $workOrder, User $mechanic): WorkOrderAssignment
    {
        return DB::transaction(function () use ($workOrder, $mechanic): WorkOrderAssignment {
            $workOrder->forceFill([
                'assigned_mechanic_id' => $mechanic->id,
            ])->save();

            return $workOrder->assignments()->create([
                'mechanic_id' => $mechanic->id,
                'assigned_at' => now(),
            ]);
        });
    }

    /**
     * Sesi 14: $unitPriceOverride dipakai kasir untuk memilih model harga
     * (Level 1-4) saat menambah sparepart ke SPK, alih-alih selalu memakai
     * harga jual utama produk.
     */
    public function addProductItem(WorkOrder $workOrder, Product $product, int $quantity = 1, ?float $unitPriceOverride = null): WorkOrderItem
    {
        $unitPrice = $unitPriceOverride ?? $product->sell_price;

        return $this->storeLineItem(
            workOrder: $workOrder,
            itemType: 'product',
            referenceKey: 'product_id',
            referenceId: $product->getKey(),
            name: $product->name,
            unitPrice: $unitPrice,
            quantity: $quantity,
            snapshot: [
                'source' => 'product',
                'name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $unitPrice,
            ],
        );
    }

    public function addServiceItem(WorkOrder $workOrder, ServiceItem $serviceItem, int $quantity = 1): WorkOrderItem
    {
        return $this->storeLineItem(
            workOrder: $workOrder,
            itemType: 'service',
            referenceKey: 'service_item_id',
            referenceId: $serviceItem->getKey(),
            name: $serviceItem->name,
            unitPrice: $serviceItem->price,
            quantity: $quantity,
            snapshot: [
                'source' => 'service',
                'name' => $serviceItem->name,
                'code' => $serviceItem->code,
                'unit_price' => $serviceItem->price,
            ],
        );
    }

    public function updateItemQuantity(WorkOrderItem $item, int $quantity): WorkOrderItem
    {
        return DB::transaction(function () use ($item, $quantity): WorkOrderItem {
            $quantity = max(1, $quantity);
            $subtotal = (float) $item->unit_price * $quantity;

            $item->forceFill([
                'qty' => $quantity,
                'subtotal' => $subtotal,
            ])->save();

            $this->snapshotPricing($item->workOrder, $this->buildPriceSnapshot($item->workOrder));

            return $item;
        });
    }

    public function removeItem(WorkOrderItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $workOrder = $item->workOrder;
            $item->delete();

            $this->snapshotPricing($workOrder, $this->buildPriceSnapshot($workOrder));
        });
    }

    public function snapshotPricing(WorkOrder $workOrder, array $snapshot): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $snapshot): WorkOrder {
            $workOrder->forceFill([
                'price_snapshot' => $snapshot,
            ])->save();

            return $workOrder;
        });
    }

    public function transitionTo(WorkOrder $workOrder, WorkOrderStatus|string $status): WorkOrder
    {
        $target = WorkOrderStatus::make($status);
        $current = $workOrder->status instanceof WorkOrderStatus
            ? $workOrder->status
            : WorkOrderStatus::make((string) $workOrder->status);

        if (! $current->canTransitionTo($target)) {
            throw new InvalidArgumentException("Cannot transition work order from {$current->value} to {$target->value}.");
        }

        $workOrder->forceFill([
            'status' => $target,
        ])->save();

        return $workOrder;
    }

    public function markInProgress(WorkOrder $workOrder): WorkOrder
    {
        return $this->transitionTo($workOrder, WorkOrderStatus::InProgress);
    }

    public function markCompleted(WorkOrder $workOrder): WorkOrder
    {
        $workOrder = $this->transitionTo($workOrder, WorkOrderStatus::Completed);

        $workOrder->forceFill([
            'completed_at' => $workOrder->completed_at ?? now(),
        ])->save();

        return $workOrder;
    }

    public function markPaid(WorkOrder $workOrder): WorkOrder
    {
        $workOrder = $this->transitionTo($workOrder, WorkOrderStatus::Paid);

        $workOrder->forceFill([
            'completed_at' => $workOrder->completed_at ?? now(),
            'paid_at' => $workOrder->paid_at ?? now(),
        ])->save();

        // Profit stream 1 mekanik: komisi "kendaraan masuk" dicatat otomatis
        // begitu WO dinyatakan Paid.
        app(CommissionService::class)->recordForWorkOrder($workOrder->refresh());

        return $workOrder;
    }

    public function buildPriceSnapshot(WorkOrder $workOrder): array
    {
        $items = $workOrder->items()->get();

        return [
            'items' => $items->map(fn (WorkOrderItem $item): array => [
                'id' => $item->id,
                'type' => $item->item_type,
                'name' => $item->name,
                'qty' => $item->qty,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
            ])->all(),
            'total_items' => $items->count(),
            'total_amount' => $items->sum('subtotal'),
        ];
    }

    private function generateWoNumber(): string
    {
        $day = now()->format('Ymd');
        $prefix = 'WO-' . $day . '-';

        $count = WorkOrder::where('wo_number', 'like', $prefix . '%')->count();

        return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    private function storeLineItem(
        WorkOrder $workOrder,
        string $itemType,
        string $referenceKey,
        string $referenceId,
        string $name,
        mixed $unitPrice,
        int $quantity,
        array $snapshot
    ): WorkOrderItem {
        $subtotal = (float) $unitPrice * $quantity;

        return DB::transaction(function () use (
            $workOrder,
            $itemType,
            $referenceKey,
            $referenceId,
            $name,
            $unitPrice,
            $quantity,
            $subtotal,
            $snapshot
        ): WorkOrderItem {
            $payload = [
                'item_type' => $itemType,
                'name' => $name,
                'qty' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'snapshot' => $snapshot,
            ];

            $payload[$referenceKey] = $referenceId;

            $item = $workOrder->items()->create($payload);
            $this->snapshotPricing($workOrder, $this->buildPriceSnapshot($workOrder));

            return $item;
        });
    }
}

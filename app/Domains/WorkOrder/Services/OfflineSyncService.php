<?php

namespace App\Domains\WorkOrder\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Models\WorkOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Menjalankan ulang aksi mekanik yang sebelumnya diantrekan di IndexedDB
 * browser saat perangkat offline (Modul 7: Hybrid Offline Sync).
 *
 * Setiap key di sini meniru persis efek aksi yang sama saat dijalankan
 * online lewat komponen Livewire terkait (App\Livewire\MobileMechanic\*),
 * memakai WorkOrderService yang sama supaya hasil akhirnya konsisten baik
 * dikerjakan online maupun offline lalu disinkronkan.
 */
class OfflineSyncService
{
    public function __construct(private readonly WorkOrderService $workOrderService)
    {
    }

    public function handle(string $key, array $payload, ?User $user): array
    {
        return match ($key) {
            'wo.start' => $this->startWork($payload),
            'wo.finish' => $this->finishWork($payload),
            'scanner.addToWorkOrder' => $this->addToWorkOrder($payload),
            default => throw ValidationException::withMessages([
                'key' => "Aksi offline '{$key}' tidak dikenali.",
            ]),
        };
    }

    private function startWork(array $payload): array
    {
        $data = $this->validate($payload, ['id' => 'required']);
        $workOrder = WorkOrder::findOrFail($data['id']);
        $this->workOrderService->markInProgress($workOrder);

        return ['work_order_id' => $workOrder->id, 'status' => $workOrder->status->value];
    }

    private function finishWork(array $payload): array
    {
        $data = $this->validate($payload, ['id' => 'required']);
        $workOrder = WorkOrder::findOrFail($data['id']);
        $this->workOrderService->markCompleted($workOrder);

        return ['work_order_id' => $workOrder->id, 'status' => $workOrder->status->value];
    }

    private function addToWorkOrder(array $payload): array
    {
        $data = $this->validate($payload, [
            'wo_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $workOrder = WorkOrder::findOrFail($data['wo_id']);
        $qty = (int) $data['qty'];

        $existing = WorkOrderItem::where('work_order_id', $workOrder->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->increment('qty', $qty);
            $existing->forceFill(['subtotal' => $existing->qty * $existing->unit_price])->save();
            $item = $existing;
        } else {
            $item = WorkOrderItem::create([
                'work_order_id' => $workOrder->id,
                'product_id' => $product->id,
                'item_type' => 'product',
                'name' => $product->name,
                'qty' => $qty,
                'unit_price' => $product->sell_price,
                'subtotal' => $qty * $product->sell_price,
                'snapshot' => [
                    'source' => 'product',
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->sell_price,
                ],
            ]);
        }

        return ['work_order_item_id' => $item->id, 'work_order_id' => $workOrder->id];
    }

    private function validate(array $payload, array $rules): array
    {
        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}

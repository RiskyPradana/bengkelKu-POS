<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Services\WorkOrderService;

class CreateWorkOrder
{
    public function __construct(private readonly WorkOrderService $service)
    {
    }

    public function handle(array $data): WorkOrder
    {
        $data['status'] = WorkOrderStatus::Pending->value;
        $data['price_snapshot'] = $data['price_snapshot'] ?? [];

        return $this->service->create($data);
    }
}

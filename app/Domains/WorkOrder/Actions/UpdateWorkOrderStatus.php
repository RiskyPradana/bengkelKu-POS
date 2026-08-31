<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Services\WorkOrderService;

class UpdateWorkOrderStatus
{
    public function __construct(private readonly WorkOrderService $service)
    {
    }

    public function handle(WorkOrder $workOrder, WorkOrderStatus|string $status): WorkOrder
    {
        return $this->service->transitionTo($workOrder, $status);
    }
}

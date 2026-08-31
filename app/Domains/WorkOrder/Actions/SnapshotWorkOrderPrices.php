<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Services\WorkOrderService;

class SnapshotWorkOrderPrices
{
    public function __construct(private readonly WorkOrderService $service)
    {
    }

    public function handle(WorkOrder $workOrder, array $snapshot): WorkOrder
    {
        return $this->service->snapshotPricing($workOrder, $snapshot);
    }
}

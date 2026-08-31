<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Models\WorkOrderAssignment;
use App\Domains\WorkOrder\Services\WorkOrderService;
use App\Models\User;

class AssignMechanicToWorkOrder
{
    public function __construct(private readonly WorkOrderService $service)
    {
    }

    public function handle(WorkOrder $workOrder, User $mechanic): WorkOrderAssignment
    {
        return $this->service->assignMechanic($workOrder, $mechanic);
    }
}

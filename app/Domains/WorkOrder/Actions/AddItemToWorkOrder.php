<?php

namespace App\Domains\WorkOrder\Actions;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ServiceItem;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Models\WorkOrderItem;
use App\Domains\WorkOrder\Services\WorkOrderService;

class AddItemToWorkOrder
{
    public function __construct(private readonly WorkOrderService $service)
    {
    }

    public function handle(WorkOrder $workOrder, Product|ServiceItem $reference, int $quantity = 1): WorkOrderItem
    {
        return $this->service->addItem($workOrder, $reference, $quantity);
    }
}

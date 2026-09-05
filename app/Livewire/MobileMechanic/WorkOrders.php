<?php

namespace App\Livewire\MobileMechanic;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Services\WorkOrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class WorkOrders extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function workOrders(): LengthAwarePaginator
    {
        return WorkOrder::with('vehicle', 'customer')
            ->whereIn('status', [
                WorkOrderStatus::Pending->value,
                WorkOrderStatus::InProgress->value,
                WorkOrderStatus::Completed->value,
            ])
            ->when($this->search, fn($q) => $q
                ->whereHas('vehicle', fn($v) => $v->where('plate_number', 'like', "%{$this->search}%"))
                ->orWhere('wo_number', 'like', "%{$this->search}%")
            )
            ->latest()->paginate(10);
    }

    /**
     * ID SPK berupa UUID (teks), bukan integer — jangan diubah balik ke `int`.
     */
    public function startWork(string $id): void
    {
        $workOrder = WorkOrder::findOrFail($id);
        app(WorkOrderService::class)->markInProgress($workOrder);
        $this->dispatch('notify', type: 'success', message: 'WO dimulai!');
        unset($this->workOrders);
    }

    public function finishWork(string $id): void
    {
        $workOrder = WorkOrder::findOrFail($id);
        app(WorkOrderService::class)->markCompleted($workOrder);
        $this->dispatch('notify', type: 'success', message: 'WO selesai!');
        unset($this->workOrders);
    }

    public function render(): View
    {
        return view('livewire.mobile.wo')
            ->layout('layouts.mobile');
    }
}

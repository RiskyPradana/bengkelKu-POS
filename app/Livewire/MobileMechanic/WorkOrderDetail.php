<?php

namespace App\Livewire\MobileMechanic;

use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Services\WorkOrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Sesi 14: halaman detail SPK di mode mekanik. Sebelumnya link "klik WO"
 * dari Beranda mengarah ke /mobile/wo/{id} yang belum punya route/komponen
 * sama sekali (404). Sekarang route + komponen ini melengkapi tautan itu.
 */
class WorkOrderDetail extends Component
{
    public string $workOrderId;

    public function mount(string $id): void
    {
        $this->workOrderId = $id;
    }

    #[Computed]
    public function workOrder(): ?WorkOrder
    {
        return WorkOrder::with(['vehicle', 'customer', 'items', 'mechanic'])
            ->find($this->workOrderId);
    }

    public function startWork(): void
    {
        $workOrder = $this->workOrder;
        if (! $workOrder instanceof WorkOrder) {
            return;
        }
        app(WorkOrderService::class)->markInProgress($workOrder);
        unset($this->workOrder);
        $this->dispatch('notify', type: 'success', message: 'WO dimulai!');
    }

    public function finishWork(): void
    {
        $workOrder = $this->workOrder;
        if (! $workOrder instanceof WorkOrder) {
            return;
        }
        app(WorkOrderService::class)->markCompleted($workOrder);
        unset($this->workOrder);
        $this->dispatch('notify', type: 'success', message: 'WO selesai!');
    }

    public function render(): View
    {
        return view('livewire.mobile.wo-detail')
            ->layout('layouts.mobile');
    }
}

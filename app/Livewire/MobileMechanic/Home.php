<?php

namespace App\Livewire\MobileMechanic;

use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Home extends Component
{
    #[Computed]
    public function activeWoCount(): int
    {
        return WorkOrder::whereDate('created_at', today())
            ->whereIn('status', [WorkOrderStatus::Pending->value, WorkOrderStatus::InProgress->value])
            ->count();
    }

    #[Computed]
    public function completedWoCount(): int
    {
        return WorkOrder::whereDate('updated_at', today())
            ->where('status', WorkOrderStatus::Completed->value)
            ->count();
    }

    #[Computed]
    public function myWorkOrders(): Collection
    {
        return WorkOrder::with('vehicle', 'customer')
            ->whereIn('status', [WorkOrderStatus::Pending->value, WorkOrderStatus::InProgress->value])
            ->latest()->limit(5)->get();
    }

    public function render(): View
    {
        return view('livewire.mobile.home')
            ->with([
                'activeWoCount'    => $this->activeWoCount,
                'completedWoCount' => $this->completedWoCount,
                'myWorkOrders'     => $this->myWorkOrders,
            ])
            ->layout('layouts.mobile');
    }
}

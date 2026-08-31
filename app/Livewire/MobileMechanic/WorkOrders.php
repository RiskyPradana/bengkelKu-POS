<?php

namespace App\Livewire\MobileMechanic;

use App\Domains\WorkOrder\Models\WorkOrder;
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
            ->whereIn('status', ['pending', 'in_progress', 'done'])
            ->when($this->search, fn($q) => $q
                ->whereHas('vehicle', fn($v) => $v->where('plate_number', 'like', "%{$this->search}%"))
                ->orWhere('wo_number', 'like', "%{$this->search}%")
            )
            ->latest()->paginate(10);
    }

    public function startWork(int $id): void
    {
        WorkOrder::findOrFail($id)->update(['status' => 'in_progress']);
        $this->dispatch('notify', type: 'success', message: 'WO dimulai!');
        unset($this->workOrders);
    }

    public function finishWork(int $id): void
    {
        WorkOrder::findOrFail($id)->update(['status' => 'done']);
        $this->dispatch('notify', type: 'success', message: 'WO selesai!');
        unset($this->workOrders);
    }

    public function render(): View
    {
        return view('livewire.mobile.wo')
            ->layout('layouts.mobile');
    }
}

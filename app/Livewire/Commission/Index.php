<?php

namespace App\Livewire\Commission;

use App\Domains\Commission\Services\CommissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $period = '';

    public ?string $selectedMechanicId = null;

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    public function updatedPeriod(): void
    {
        unset($this->summary, $this->woDetails);
    }

    public function selectMechanic(?string $id): void
    {
        $this->selectedMechanicId = $this->selectedMechanicId === $id ? null : $id;
        unset($this->woDetails);
    }

    #[Computed]
    public function summary(): Collection
    {
        return app(CommissionService::class)->monthlySummary($this->period);
    }

    #[Computed]
    public function totals(): array
    {
        $data = $this->summary;

        return [
            'total_wo'           => $data->sum('total_wo'),
            'total_jasa'         => $data->sum('total_jasa'),
            'commission_vehicle' => $data->sum('commission_vehicle'),
            'commission_kpi'     => $data->sum('commission_kpi'),
            'total_commission'   => $data->sum('total_commission'),
        ];
    }

    #[Computed]
    public function selectedRow(): ?array
    {
        if (! $this->selectedMechanicId) {
            return null;
        }

        return $this->summary->firstWhere('mechanic_id', $this->selectedMechanicId);
    }

    #[Computed]
    public function woDetails(): Collection
    {
        if (! $this->selectedMechanicId) {
            return collect();
        }

        return app(CommissionService::class)->workOrdersFor($this->selectedMechanicId, $this->period);
    }

    public function render(): View
    {
        return view('livewire.commission.index')
            ->layout('layouts.admin', ['pageTitle' => 'Komisi Mekanik', 'pageSub' => 'Laporan komisi bulanan mekanik']);
    }
}

<?php

namespace App\Livewire\Commission;

use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $period;
    public ?int   $selectedMechanicId = null;

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    #[Computed]
    public function mechanics(): Collection
    {
        return \App\Models\User::where('role', 'mechanic')
            ->orWhereHas('roles', fn($q) => $q->where('name', 'mechanic'))
            ->orderBy('name')
            ->get()
            ->whenEmpty(fn() => \App\Models\User::orderBy('name')->get());
    }

    #[Computed]
    public function commissionData(): Collection
    {
        [$year, $month] = explode('-', $this->period);

        $query = WorkOrder::with('mechanic')
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->where('status', 'done')
            ->select(
                'mechanic_id',
                DB::raw('COUNT(*) as total_wo'),
                DB::raw('SUM(grand_total) as total_revenue'),
                DB::raw('SUM(labor_cost) as total_labor')
            )
            ->groupBy('mechanic_id');

        if ($this->selectedMechanicId) {
            $query->where('mechanic_id', $this->selectedMechanicId);
        }

        return $query->get()->map(function ($row) {
            $commissionRate = 0.10; // 10% dari jasa
            $commission     = ($row->total_labor ?? 0) * $commissionRate;
            return [
                'mechanic_id'   => $row->mechanic_id,
                'mechanic_name' => $row->mechanic?->name ?? 'Tidak Diketahui',
                'total_wo'      => $row->total_wo,
                'total_revenue' => $row->total_revenue ?? 0,
                'total_labor'   => $row->total_labor ?? 0,
                'commission'    => $commission,
            ];
        });
    }

    #[Computed]
    public function summaryStats(): array
    {
        $data = $this->commissionData;
        return [
            'total_wo'      => $data->sum('total_wo'),
            'total_revenue' => $data->sum('total_revenue'),
            'total_labor'   => $data->sum('total_labor'),
            'total_commission' => $data->sum('commission'),
        ];
    }

    #[Computed]
    public function woDetails(): Collection
    {
        if (!$this->selectedMechanicId) return collect();

        [$year, $month] = explode('-', $this->period);

        return WorkOrder::with('vehicle', 'customer')
            ->where('mechanic_id', $this->selectedMechanicId)
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->where('status', 'done')
            ->latest('completed_at')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.commission.index')
            ->with([
                'mechanics'      => $this->mechanics,
                'commissionData' => $this->commissionData,
                'summaryStats'   => $this->summaryStats,
                'woDetails'      => $this->woDetails,
            ])
            ->layout('layouts.admin', ['pageTitle' => 'Komisi Mekanik', 'pageSub' => 'Laporan komisi bulanan mekanik']);
    }
}

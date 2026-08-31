<?php

namespace App\Livewire\Analytics;

use App\Domains\Analytics\Services\AnalyticsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Dashboard Analitik Owner.
 *
 * Namespace sengaja App\Livewire\Analytics (bukan App\Livewire\Dashboard)
 * karena App\Livewire\Dashboard sudah dipakai sebagai nama CLASS.
 */
class OwnerDashboard extends Component
{
    #[Url(as: 'periode')]
    public string $preset = 'bulan_ini';

    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    #[Url(as: 'cabang')]
    public ?string $branchId = null;

    public function mount(): void
    {
        if ($this->startDate === '' || $this->endDate === '') {
            $this->applyPreset($this->preset);
        }
    }

    public function updatedPreset(string $value): void
    {
        $this->applyPreset($value);
    }

    public function applyPreset(string $preset): void
    {
        [$start, $end] = match ($preset) {
            'hari_ini'     => [now()->startOfDay(), now()->endOfDay()],
            'minggu_ini'   => [now()->startOfWeek(), now()->endOfWeek()],
            'bulan_ini'    => [now()->startOfMonth(), now()->endOfMonth()],
            'bulan_lalu'   => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'tahun_ini'    => [now()->startOfYear(), now()->endOfYear()],
            'akhir_30hari' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            default        => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $this->preset    = $preset;
        $this->startDate = $start->format('Y-m-d');
        $this->endDate   = $end->format('Y-m-d');
    }

    public function updatedStartDate(): void
    {
        $this->preset = 'kustom';
    }

    public function updatedEndDate(): void
    {
        $this->preset = 'kustom';
    }

    private function range(): array
    {
        return [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ];
    }

    public function render()
    {
        $analytics = app(AnalyticsService::class);
        [$start, $end] = $this->range();

        $branches = collect();

        try {
            if (Schema::hasTable('branches')) {
                $branches = DB::table('branches')->select('id', 'name')->orderBy('name')->get();
            }
        } catch (\Throwable $e) {
            $branches = collect();
        }

        return view('livewire.dashboard.owner-dashboard', [
            'kpi'           => $analytics->kpi($start, $end, $this->branchId),
            'trend'         => $analytics->revenueTrend($start, $end, $this->branchId),
            'composition'   => $analytics->revenueComposition($start, $end, $this->branchId),
            'busyHours'     => $analytics->busyHours($start, $end, $this->branchId),
            'topParts'      => $analytics->topParts($start, $end, 10, $this->branchId),
            'topServices'   => $analytics->topServices($start, $end, 8, $this->branchId),
            'mechanics'     => $analytics->mechanicPerformance($start, $end, $this->branchId),
            'branchCompare' => $analytics->branchComparison($start, $end),
            'topCustomers'  => $analytics->topCustomers($start, $end, 8),
            'lowStock'      => $analytics->lowStockAlert($this->branchId, 10),
            'branches'      => $branches,
        ])->layout('layouts.app', ['title' => 'Dashboard Analitik']);
    }
}

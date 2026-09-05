<?php

namespace App\Domains\Commission\Services;

use App\Domains\Commission\Models\MechanicCommission;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Sumber tunggal untuk menghitung komisi & bonus KPI mekanik.
 *
 * Mekanik punya 2 profit yang diakumulasi per bulan (periode 'YYYY-MM'):
 * 1. Komisi "Kendaraan Masuk" — % dari nilai jasa tiap WO yang sudah Paid,
 *    dicatat otomatis satu baris per WO di tabel mechanic_commissions.
 * 2. Bonus KPI — nominal tetap (kpi_bonus_amount) yang cair kalau jumlah WO
 *    yang ditangani mekanik dalam sebulan mencapai target (monthly_target).
 */
class CommissionService
{
    public function defaultRate(): float
    {
        return (float) env('COMMISSION_RATE', 10);
    }

    /**
     * Dipanggil setiap kali Work Order berpindah status ke Paid.
     * Aman dipanggil berulang — tidak akan membuat baris dobel untuk WO yang sama.
     */
    public function recordForWorkOrder(WorkOrder $workOrder): ?MechanicCommission
    {
        if (! $workOrder->assigned_mechanic_id) {
            return null;
        }

        $existing = MechanicCommission::where('work_order_id', $workOrder->id)->first();

        if ($existing) {
            return $existing;
        }

        $serviceAmount = (float) $workOrder->items()->where('item_type', 'service')->sum('subtotal');
        $mechanic      = $workOrder->mechanic ?? User::find($workOrder->assigned_mechanic_id);
        $rate          = $mechanic && $mechanic->commission_rate !== null
            ? (float) $mechanic->commission_rate
            : $this->defaultRate();

        $commissionAmount = round($serviceAmount * ($rate / 100), 2);
        $earnedAt         = $workOrder->paid_at ?? now();

        return MechanicCommission::create([
            'user_id'           => $workOrder->assigned_mechanic_id,
            'work_order_id'     => $workOrder->id,
            'branch_id'         => $workOrder->branch_id,
            'commission_amount' => $commissionAmount,
            'commission_rate'   => $rate,
            'base_amount'       => $serviceAmount,
            'service_amount'    => $serviceAmount,
            'period'            => \Illuminate\Support\Carbon::parse($earnedAt)->format('Y-m'),
            'source'            => 'vehicle',
            'earned_at'         => $earnedAt,
            'is_paid'           => false,
        ]);
    }

    /**
     * Rekap komisi bulanan (2 profit stream) untuk semua mekanik pada satu periode.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function monthlySummary(string $period): Collection
    {
        $mechanics = User::query()
            ->where('role', 'mekanik')
            ->orderBy('name')
            ->get();

        $rowsByMechanic = MechanicCommission::query()
            ->where('period', $period)
            ->get()
            ->groupBy('user_id');

        return $mechanics->map(function (User $mechanic) use ($rowsByMechanic) {
            $rows = $rowsByMechanic->get($mechanic->id, collect());

            $totalWo           = $rows->count();
            $totalJasa         = (float) $rows->sum('service_amount');
            $commissionVehicle = (float) $rows->sum('commission_amount');

            $target        = $mechanic->monthly_target;
            $bonus         = (float) ($mechanic->kpi_bonus_amount ?? 0);
            $achieved      = $target !== null && $target > 0 && $totalWo >= $target;
            $commissionKpi = $achieved ? $bonus : 0.0;

            return [
                'mechanic_id'        => $mechanic->id,
                'mechanic_name'      => $mechanic->name,
                'total_wo'           => $totalWo,
                'total_jasa'         => $totalJasa,
                'commission_vehicle' => $commissionVehicle,
                'target'             => $target,
                'kpi_bonus'          => $bonus,
                'kpi_achieved'       => $achieved,
                'commission_kpi'     => $commissionKpi,
                'total_commission'   => $commissionVehicle + $commissionKpi,
            ];
        });
    }

    /**
     * Daftar komisi "kendaraan masuk" (dengan WO, kendaraan & pelanggan) seorang
     * mekanik pada satu periode — dipakai untuk detail drill-down.
     */
    public function workOrdersFor(string $mechanicId, string $period): Collection
    {
        return MechanicCommission::with('workOrder.vehicle', 'workOrder.customer')
            ->where('user_id', $mechanicId)
            ->where('period', $period)
            ->latest('earned_at')
            ->get();
    }
}

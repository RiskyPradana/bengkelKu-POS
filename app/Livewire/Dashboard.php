<?php

namespace App\Livewire;

use App\Domains\CustomerVehicle\Models\Customer;
use App\Domains\POS\Enums\InvoiceStatus;
use App\Domains\POS\Models\Invoice;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.dashboard')
            ->layout('layouts.admin', [
                'title'     => 'Dashboard — BengkelOS',
                'pageTitle' => 'Dashboard',
                'pageSub'   => 'Ringkasan operasional bengkel hari ini',
            ]);
    }

    public function getWorkOrderStatsProperty(): array
    {
        $counts = WorkOrder::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $omzetBulan = Invoice::where('status', InvoiceStatus::Paid)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total');

        $omzetHari = Invoice::where('status', InvoiceStatus::Paid)
            ->whereDate('created_at', today())
            ->sum('grand_total');

        $totalCustomers = Customer::count();

        return [
            'total'           => array_sum($counts),
            'pending'         => $counts[WorkOrderStatus::Pending->value]    ?? 0,
            'in_progress'     => $counts[WorkOrderStatus::InProgress->value] ?? 0,
            'completed'       => $counts[WorkOrderStatus::Completed->value]  ?? 0,
            'paid'            => $counts[WorkOrderStatus::Paid->value]       ?? 0,
            'omzet_bulan'     => (float) $omzetBulan,
            'omzet_hari'      => (float) $omzetHari,
            'total_customers' => $totalCustomers,
        ];
    }

    public function getKanbanProperty(): array
    {
        $workOrders = WorkOrder::with(['customer', 'vehicle', 'invoice'])
            ->latest()
            ->limit(80)
            ->get();

        return [
            WorkOrderStatus::Pending->value    => $workOrders->where('status', WorkOrderStatus::Pending)->values(),
            WorkOrderStatus::InProgress->value => $workOrders->where('status', WorkOrderStatus::InProgress)->values(),
            WorkOrderStatus::Completed->value  => $workOrders->where('status', WorkOrderStatus::Completed)->values(),
            WorkOrderStatus::Paid->value       => $workOrders->where('status', WorkOrderStatus::Paid)->values(),
        ];
    }

    public function getRecentWorkOrdersProperty(): Collection
    {
        return WorkOrder::with(['customer', 'vehicle', 'invoice'])
            ->latest()
            ->limit(8)
            ->get();
    }
}

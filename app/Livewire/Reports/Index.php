<?php

namespace App\Livewire\Reports;

use App\Domains\MasterData\Models\AppSetting;
use App\Domains\POS\Enums\InvoiceStatus;
use App\Domains\POS\Models\Invoice;
use App\Domains\WorkOrder\Enums\WorkOrderStatus;
use App\Domains\WorkOrder\Models\WorkOrder;
use App\Domains\WorkOrder\Models\WorkOrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year  = now()->year;
    }

    public function render(): View
    {
        return view('livewire.reports.index')
            ->layout('layouts.admin', [
                'title'     => 'Laporan — BengkelOS',
                'pageTitle' => 'Laporan',
                'pageSub'   => 'Omzet bengkel & komisi mekanik per periode',
            ]);
    }

    public function getRevenueStatsProperty(): array
    {
        $invoices = Invoice::where('status', InvoiceStatus::Paid)
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->get(['grand_total', 'discount', 'tax']);

        $count    = $invoices->count();
        $revenue  = (float) $invoices->sum('grand_total');
        $discount = (float) $invoices->sum('discount');
        $tax      = (float) $invoices->sum('tax');

        return [
            'revenue'       => $revenue,
            'discount'      => $discount,
            'tax'           => $tax,
            'invoice_count' => $count,
            'avg_per_inv'   => $count > 0 ? $revenue / $count : 0.0,
        ];
    }

    public function getWorkOrderStatsByStatusProperty(): Collection
    {
        return WorkOrder::selectRaw('status, count(*) as total')
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->groupBy('status')
            ->get();
    }

    public function getTopServicesProperty(): Collection
    {
        return WorkOrderItem::with('serviceItem')
            ->where('item_type', 'service')
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->selectRaw('name, sum(qty) as total_qty, sum(subtotal) as total_revenue')
            ->groupBy('name')
            ->orderByDesc('total_revenue')
            ->limit(7)
            ->get();
    }

    public function getTopProductsProperty(): Collection
    {
        return WorkOrderItem::with('product')
            ->where('item_type', 'product')
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->selectRaw('name, sum(qty) as total_qty, sum(subtotal) as total_revenue')
            ->groupBy('name')
            ->orderByDesc('total_revenue')
            ->limit(7)
            ->get();
    }

    public function getMonthsProperty(): array
    {
        return [
            1 => 'Januari',   2 => 'Februari', 3 => 'Maret',     4 => 'April',
            5 => 'Mei',       6 => 'Juni',     7 => 'Juli',       8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November',  12 => 'Desember',
        ];
    }

    public function getYearsProperty(): array
    {
        $current = now()->year;
        return range($current, $current - 4);
    }

    // Sesi 12: Pengaturan printer laporan (ukuran kertas A4/Letter/F4 & orientasi),
    // dipakai saat mencetak halaman Laporan lewat tombol "Cetak Laporan".
    public function getReportPrintSettingsProperty(): array
    {
        $saved = AppSetting::getMany([
            'printer.report_paper_size',
            'printer.report_orientation',
        ], [
            'printer.report_paper_size'  => 'A4',
            'printer.report_orientation' => 'portrait',
        ]);

        return [
            'paper_size'  => (string) $saved['printer.report_paper_size'],
            'orientation' => (string) $saved['printer.report_orientation'],
        ];
    }
}

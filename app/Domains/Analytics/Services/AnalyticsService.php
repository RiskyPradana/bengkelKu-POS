<?php

namespace App\Domains\Analytics\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sumber data tunggal untuk Dashboard Analitik Owner & Laporan.
 * Semua method mengembalikan array siap pakai untuk chart / tabel.
 *
 * CATATAN Sesi 13: aplikasi ini TIDAK memiliki tabel `invoice_items`.
 * Rincian jasa/sparepart per transaksi ada di `work_order_items`, yang
 * dihubungkan ke `invoices` lewat `invoices.work_order_id`. Semua query
 * yang sebelumnya join ke `invoice_items` (penyebab error di halaman
 * Dashboard Analitik) sudah diarahkan ke `work_order_items`.
 */
class AnalyticsService
{
    /**
     * Ringkasan KPI utama untuk kartu di atas dashboard.
     */
    public function kpi(Carbon $start, Carbon $end, ?string $branchId = null): array
    {
        $current = $this->sumInvoices($start, $end, $branchId);

        // Periode pembanding dengan panjang hari yang sama
        $days = max(1, $start->diffInDays($end) + 1);
        $prevStart = $start->copy()->subDays($days);
        $prevEnd   = $start->copy()->subDay()->endOfDay();
        $previous  = $this->sumInvoices($prevStart, $prevEnd, $branchId);

        $woCount = DB::table('work_orders')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $newCustomers = DB::table('customers')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'omzet'              => (float) $current['total'],
            'omzet_growth'       => $this->growth((float) $current['total'], (float) $previous['total']),
            'transaksi'          => (int) $current['count'],
            'transaksi_growth'   => $this->growth((float) $current['count'], (float) $previous['count']),
            'rata_transaksi'     => $current['count'] > 0 ? (float) $current['total'] / (int) $current['count'] : 0.0,
            'omzet_jasa'         => (float) $current['service'],
            'omzet_sparepart'    => (float) $current['parts'],
            'work_order'         => $woCount,
            'pelanggan_baru'     => $newCustomers,
            'periode'            => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
        ];
    }

    /**
     * Tren omzet harian untuk line chart.
     */
    public function revenueTrend(Carbon $start, Carbon $end, ?string $branchId = null): array
    {
        $rows = DB::table('invoices')
            ->selectRaw("DATE(created_at) as tanggal, SUM({$this->amountColumn()}) as total, COUNT(*) as jumlah")
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', '!=', 'void')
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $labels = [];
        $values = [];
        $counts = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $values[] = (float) ($rows[$key]->total ?? 0);
            $counts[] = (int) ($rows[$key]->jumlah ?? 0);
        }

        return ['labels' => $labels, 'omzet' => $values, 'transaksi' => $counts];
    }

    /**
     * Sparepart terlaris berdasarkan kuantitas terjual.
     */
    public function topParts(Carbon $start, Carbon $end, int $limit = 10, ?string $branchId = null): array
    {
        return DB::table('work_order_items as woi')
            ->join('invoices as i', 'i.work_order_id', '=', 'woi.work_order_id')
            ->leftJoin('products as p', 'p.id', '=', 'woi.product_id')
            ->selectRaw("COALESCE(p.name, woi.name) as nama, COALESCE(p.sku, '-') as sku, SUM(woi.qty) as qty, SUM(woi.subtotal) as total")
            ->where('i.status', '!=', 'void')
            ->where('woi.item_type', 'product')
            ->when($branchId, fn ($q) => $q->where('i.branch_id', $branchId))
            ->whereBetween('i.created_at', [$start, $end])
            ->groupByRaw("COALESCE(p.name, woi.name), COALESCE(p.sku, '-')")
            ->orderByDesc('qty')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'nama'  => $r->nama,
                'sku'   => $r->sku,
                'qty'   => (int) $r->qty,
                'total' => (float) $r->total,
            ])
            ->all();
    }

    /**
     * Jasa/servis paling sering dikerjakan.
     */
    public function topServices(Carbon $start, Carbon $end, int $limit = 10, ?string $branchId = null): array
    {
        return DB::table('work_order_items as woi')
            ->join('invoices as i', 'i.work_order_id', '=', 'woi.work_order_id')
            ->selectRaw('woi.name as nama, COUNT(*) as jumlah, SUM(woi.subtotal) as total')
            ->where('i.status', '!=', 'void')
            ->where('woi.item_type', 'service')
            ->when($branchId, fn ($q) => $q->where('i.branch_id', $branchId))
            ->whereBetween('i.created_at', [$start, $end])
            ->groupBy('woi.name')
            ->orderByDesc('jumlah')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'nama'   => $r->nama,
                'jumlah' => (int) $r->jumlah,
                'total'  => (float) $r->total,
            ])
            ->all();
    }

    /**
     * Performa mekanik: jumlah WO selesai, nilai jasa, dan komisi.
     */
    public function mechanicPerformance(Carbon $start, Carbon $end, ?string $branchId = null): array
    {
        return DB::table('mechanic_commissions as mc')
            ->leftJoin('users as u', 'u.id', '=', 'mc.user_id')
            ->selectRaw("COALESCE(u.name, 'Tanpa Nama') as nama, COUNT(*) as total_wo, SUM(mc.service_amount) as total_jasa, SUM(mc.commission_amount) as total_komisi")
            ->when($branchId, fn ($q) => $q->where('mc.branch_id', $branchId))
            ->whereBetween('mc.earned_at', [$start, $end])
            ->groupByRaw("COALESCE(u.name, 'Tanpa Nama')")
            ->orderByDesc('total_jasa')
            ->get()
            ->map(fn ($r) => [
                'nama'         => $r->nama,
                'total_wo'     => (int) $r->total_wo,
                'total_jasa'   => (float) $r->total_jasa,
                'total_komisi' => (float) $r->total_komisi,
                'rata_jasa'    => $r->total_wo > 0 ? (float) $r->total_jasa / (int) $r->total_wo : 0.0,
            ])
            ->all();
    }

    /**
     * Komposisi omzet: jasa vs sparepart (untuk donut chart).
     */
    public function revenueComposition(Carbon $start, Carbon $end, ?string $branchId = null): array
    {
        $rows = DB::table('work_order_items as woi')
            ->join('invoices as i', 'i.work_order_id', '=', 'woi.work_order_id')
            ->selectRaw('woi.item_type as type, SUM(woi.subtotal) as total')
            ->where('i.status', '!=', 'void')
            ->when($branchId, fn ($q) => $q->where('i.branch_id', $branchId))
            ->whereBetween('i.created_at', [$start, $end])
            ->groupBy('woi.item_type')
            ->pluck('total', 'type');

        return [
            'labels' => ['Jasa Servis', 'Sparepart'],
            'values' => [
                (float) ($rows['service'] ?? 0),
                (float) ($rows['product'] ?? 0),
            ],
        ];
    }

    /**
     * Distribusi transaksi per jam (untuk tahu jam sibuk bengkel).
     */
    public function busyHours(Carbon $start, Carbon $end, ?string $branchId = null): array
    {
        $rows = DB::table('invoices')
            ->selectRaw($this->hourExpression() . ' as jam, COUNT(*) as jumlah')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', '!=', 'void')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('jam')
            ->pluck('jumlah', 'jam');

        $labels = [];
        $values = [];

        for ($hour = 7; $hour <= 21; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            $values[] = (int) ($rows[$hour] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Perbandingan omzet antar cabang.
     */
    public function branchComparison(Carbon $start, Carbon $end): array
    {
        return DB::table('invoices as i')
            ->leftJoin('branches as b', 'b.id', '=', 'i.branch_id')
            ->selectRaw("COALESCE(b.name, 'Pusat') as nama, SUM(i.{$this->amountColumn()}) as total, COUNT(*) as jumlah")
            ->where('i.status', '!=', 'void')
            ->whereBetween('i.created_at', [$start, $end])
            ->groupByRaw("COALESCE(b.name, 'Pusat')")
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'nama'   => $r->nama,
                'total'  => (float) $r->total,
                'jumlah' => (int) $r->jumlah,
            ])
            ->all();
    }

    /**
     * Pelanggan paling loyal berdasarkan nilai belanja.
     */
    public function topCustomers(Carbon $start, Carbon $end, int $limit = 10): array
    {
        return DB::table('invoices as i')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->selectRaw("COALESCE(c.name, 'Umum') as nama, COALESCE(c.phone, '-') as telepon, COUNT(*) as kunjungan, SUM(i.{$this->amountColumn()}) as total")
            ->where('i.status', '!=', 'void')
            ->whereBetween('i.created_at', [$start, $end])
            ->groupByRaw("COALESCE(c.name, 'Umum'), COALESCE(c.phone, '-')")
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'nama'      => $r->nama,
                'telepon'   => $r->telepon,
                'kunjungan' => (int) $r->kunjungan,
                'total'     => (float) $r->total,
            ])
            ->all();
    }

    /**
     * Sparepart yang perlu segera di-restock.
     */
    public function lowStockAlert(?string $branchId = null, int $limit = 20): array
    {
        return DB::table('branch_stocks as bs')
            ->leftJoin('products as p', 'p.id', '=', 'bs.product_id')
            ->leftJoin('branches as b', 'b.id', '=', 'bs.branch_id')
            ->selectRaw("COALESCE(p.name, '-') as nama, COALESCE(p.sku, '-') as sku, COALESCE(b.name, 'Pusat') as cabang, bs.quantity, bs.min_stock")
            ->whereColumn('bs.quantity', '<=', 'bs.min_stock')
            ->when($branchId, fn ($q) => $q->where('bs.branch_id', $branchId))
            ->orderBy('bs.quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'nama'      => $r->nama,
                'sku'       => $r->sku,
                'cabang'    => $r->cabang,
                'quantity'  => (int) $r->quantity,
                'min_stock' => (int) $r->min_stock,
                'kurang'    => max(0, (int) $r->min_stock - (int) $r->quantity),
            ])
            ->all();
    }

    // ───────────────────── Helper ─────────────────────

    private function sumInvoices(Carbon $start, Carbon $end, ?string $branchId = null): array
    {
        $total = DB::table('invoices')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', '!=', 'void')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("COALESCE(SUM({$this->amountColumn()}), 0) as total, COUNT(*) as count")
            ->first();

        $split = DB::table('work_order_items as woi')
            ->join('invoices as i', 'i.work_order_id', '=', 'woi.work_order_id')
            ->when($branchId, fn ($q) => $q->where('i.branch_id', $branchId))
            ->where('i.status', '!=', 'void')
            ->whereBetween('i.created_at', [$start, $end])
            ->selectRaw('woi.item_type as type, COALESCE(SUM(woi.subtotal), 0) as total')
            ->groupBy('woi.item_type')
            ->pluck('total', 'type');

        return [
            'total'   => $total->total ?? 0,
            'count'   => $total->count ?? 0,
            'service' => $split['service'] ?? 0,
            'parts'   => $split['product'] ?? 0,
        ];
    }

    /**
     * Nama kolom nilai uang di tabel invoices berbeda-beda antar proyek
     * (total / grand_total / total_amount / ...). Dideteksi otomatis supaya
     * tidak muncul error "Unknown column".
     */
    private function amountColumn(): string
    {
        static $kolom = null;

        if ($kolom !== null) {
            return $kolom;
        }

        $kandidat = [
            'total', 'grand_total', 'total_amount', 'amount',
            'net_total', 'total_price', 'final_total', 'total_bayar',
        ];

        foreach ($kandidat as $c) {
            try {
                if (Schema::hasColumn('invoices', $c)) {
                    return $kolom = $c;
                }
            } catch (\Throwable $e) {
                break;
            }
        }

        return $kolom = 'total';
    }

    /**
     * Ekspresi jam yang kompatibel MySQL, PostgreSQL, dan SQLite.
     */
    private function hourExpression(): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            'pgsql'  => 'CAST(EXTRACT(HOUR FROM created_at) AS INTEGER)',
            default  => 'HOUR(created_at)',
        };
    }

    private function growth(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}

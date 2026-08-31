<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateCommissions extends Command
{
    protected $signature = 'bengkel:generate-commissions {--month= : Format YYYY-MM, default bulan lalu} {--rate= : Persentase komisi, default dari config}';

    protected $description = 'Hitung dan simpan rekap komisi mekanik untuk satu periode bulanan';

    public function handle(): int
    {
        $month = $this->option('month')
            ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
            : now()->subMonth()->startOfMonth();

        $rate = (float) ($this->option('rate') ?: env('COMMISSION_RATE', 10));

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $this->info('Periode: ' . $start->format('d/m/Y') . ' s/d ' . $end->format('d/m/Y'));
        $this->info('Rate komisi: ' . $rate . '%');

        $rows = DB::table('mechanic_commissions')
            ->selectRaw('mechanic_id, COUNT(*) as total_wo, SUM(service_amount) as total_service, SUM(commission_amount) as total_commission')
            ->whereBetween('earned_at', [$start, $end])
            ->groupBy('mechanic_id')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('Belum ada data komisi pada periode ini.');
            return self::SUCCESS;
        }

        $created = 0;

        foreach ($rows as $row) {
            DB::table('commission_summaries')->updateOrInsert(
                [
                    'mechanic_id' => $row->mechanic_id,
                    'period'      => $start->format('Y-m'),
                ],
                [
                    'total_work_orders' => $row->total_wo,
                    'total_service'     => $row->total_service,
                    'total_commission'  => $row->total_commission,
                    'rate'              => $rate,
                    'status'            => 'draft',
                    'generated_at'      => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]
            );
            $created++;
        }

        $this->table(
            ['Mekanik ID', 'Jml WO', 'Total Jasa', 'Total Komisi'],
            $rows->map(fn ($r) => [
                $r->mechanic_id,
                $r->total_wo,
                'Rp ' . number_format((float) $r->total_service, 0, ',', '.'),
                'Rp ' . number_format((float) $r->total_commission, 0, ',', '.'),
            ])->all()
        );

        $this->info('Rekap komisi tersimpan untuk ' . $created . ' mekanik.');

        return self::SUCCESS;
    }
}

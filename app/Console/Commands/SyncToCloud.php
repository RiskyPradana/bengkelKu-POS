<?php

namespace App\Console\Commands;

use App\Domains\Sync\Services\SyncService;
use Illuminate\Console\Command;

class SyncToCloud extends Command
{
    protected $signature = 'bengkel:sync {--status : Hanya tampilkan status sinkronisasi} {--force : Paksa sync ulang termasuk data yang sudah tersinkron}';

    protected $description = 'Sinkronkan data lokal bengkel ke server pusat (cloud)';

    public function handle(SyncService $sync): int
    {
        if (! env('SYNC_ENABLED', false)) {
            $this->warn('Cloud Sync sedang NONAKTIF (SYNC_ENABLED=false).');
            $this->line('Aktifkan di .env kalau sudah punya server pusat.');
            return self::SUCCESS;
        }

        if ($this->option('status')) {
            $status = $sync->getStatus();

            $tables = collect($status['tables'] ?? [])
                ->map(fn ($row, $table) => [
                    $table,
                    $row['pending'] ?? 0,
                    $row['last_synced_at'] ?? 'Belum pernah',
                ])
                ->values()
                ->all();

            $this->table(['Tabel', 'Belum Sinkron', 'Terakhir Sinkron'], $tables);
            $this->info('Total tertunda: ' . ($status['total_pending'] ?? 0));

            return self::SUCCESS;
        }

        $this->info('Mulai sinkronisasi ke ' . env('SYNC_SERVER_URL'));
        $this->info('Cabang: ' . env('SYNC_BRANCH_ID', 'PUSAT'));

        $result = $sync->process(force: (bool) $this->option('force'));

        $this->newLine();
        $this->info('Terkirim : ' . ($result['pushed'] ?? 0) . ' record');
        $this->info('Diterima : ' . ($result['pulled'] ?? 0) . ' record');

        if (! empty($result['errors'])) {
            $this->newLine();
            $this->error('Ada ' . count($result['errors']) . ' error:');
            foreach ($result['errors'] as $error) {
                $this->line('  - ' . $error);
            }
            return self::FAILURE;
        }

        $this->info('Sinkronisasi selesai tanpa error.');

        return self::SUCCESS;
    }
}

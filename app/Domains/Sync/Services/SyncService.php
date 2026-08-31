<?php

namespace App\Domains\Sync\Services;

use App\Domains\Sync\Models\SyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncService
{
    private string $cloudUrl;
    private string $syncKey;

    public function __construct()
    {
        $this->cloudUrl = config('services.sync.cloud_url', '');
        $this->syncKey  = config('services.sync.key', '');
    }

    /**
     * Antrekan record untuk di-push ke cloud.
     */
    public function queuePush(string $table, string $recordId, array $payload): SyncLog
    {
        return SyncLog::updateOrCreate(
            ['table_name' => $table, 'record_id' => $recordId, 'direction' => 'push'],
            ['status' => 'pending', 'payload' => $payload, 'attempt_count' => 0]
        );
    }

    /**
     * Proses antrian push — dipanggil oleh Queue Worker.
     */
    public function processPushQueue(int $limit = 50): array
    {
        $results = ['success' => 0, 'failed' => 0, 'skipped' => 0];

        if (empty($this->cloudUrl) || empty($this->syncKey)) {
            Log::info('SyncService: Cloud URL belum dikonfigurasi — skip push.');
            return $results;
        }

        $pending = SyncLog::pending()
            ->where('direction', 'push')
            ->where('attempt_count', '<', 5)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($pending as $log) {
            try {
                $response = Http::withToken($this->syncKey)
                    ->post($this->cloudUrl . '/sync/push', [
                        'table'     => $log->table_name,
                        'record_id' => $log->record_id,
                        'payload'   => $log->payload,
                    ]);

                if ($response->successful()) {
                    $log->update(['status' => 'success', 'last_attempt_at' => now()]);
                    $results['success']++;
                } else {
                    $log->increment('attempt_count');
                    $log->update([
                        'status'         => 'failed',
                        'error_message'  => $response->body(),
                        'last_attempt_at'=> now(),
                    ]);
                    $results['failed']++;
                }
            } catch (\Throwable $e) {
                $log->increment('attempt_count');
                $log->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'last_attempt_at' => now()]);
                $results['failed']++;
            }
        }

        return $results;
    }

    public function getSyncStats(): array
    {
        return [
            'pending'  => SyncLog::where('status', 'pending')->count(),
            'success'  => SyncLog::where('status', 'success')->count(),
            'failed'   => SyncLog::where('status', 'failed')->count(),
            'conflict' => SyncLog::where('status', 'conflict')->count(),
        ];
    }
}

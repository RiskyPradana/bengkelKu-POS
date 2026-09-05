<?php

namespace App\Http\Controllers\Api;

use App\Domains\WorkOrder\Services\OfflineSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Modul 7: Hybrid Offline Sync.
 *
 * Menerima aksi yang sempat diantrekan di IndexedDB browser mekanik saat
 * perangkat offline, lalu menjalankan ulang aksi tersebut di server begitu
 * koneksi kembali. Dipanggil oleh Background Sync di public/sw.js atau
 * fallback manual di resources/js/offline-sync.js.
 */
class OfflineSyncController
{
    public function __construct(private readonly OfflineSyncService $offlineSyncService)
    {
    }

    public function push(Request $request): JsonResponse
    {
        $key = (string) $request->header('X-Sync-Key', (string) $request->input('key', ''));

        if ($key === '') {
            return response()->json(['message' => 'Kunci sinkronisasi (key) wajib diisi.'], 422);
        }

        try {
            $result = $this->offlineSyncService->handle($key, $request->except(['key']), $request->user());
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal memproses aksi offline: '.$e->getMessage()], 500);
        }

        return response()->json(['message' => 'Tersinkronisasi', 'result' => $result]);
    }

    /**
     * Endpoint ringan untuk mengecek konektivitas nyata ke server (bukan
     * hanya status jaringan perangkat).
     */
    public function status(): JsonResponse
    {
        return response()->json(['online' => true, 'time' => now()->toISOString()]);
    }
}

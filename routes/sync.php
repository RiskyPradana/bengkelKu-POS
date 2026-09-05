<?php

use App\Http\Controllers\Api\OfflineSyncController;
use Illuminate\Support\Facades\Route;

// Modul 7: Hybrid Offline Sync
// Dipanggil oleh Background Sync di public/sw.js (atau fallback flush manual
// dari resources/js/offline-sync.js) untuk mengirim ulang aksi yang sempat
// diantrekan di IndexedDB browser saat mekanik sedang offline.
Route::middleware(['web', 'auth'])->prefix('api/sync')->name('sync.')->group(function (): void {
    Route::post('/push', [OfflineSyncController::class, 'push'])->name('push');
    Route::get('/status', [OfflineSyncController::class, 'status'])->name('status');
});

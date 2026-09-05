<?php

use Illuminate\Support\Facades\Route;

// Modul 7: Hybrid Offline Sync — halaman fallback saat mekanik benar-benar
// tanpa koneksi dan cache Service Worker juga masih kosong.
Route::middleware(['web', 'auth'])->group(function (): void {
    Route::view('/mobile/offline', 'mobile.offline')->name('mobile.offline');
});

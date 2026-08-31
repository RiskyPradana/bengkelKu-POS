<?php

/*
|--------------------------------------------------------------------------
| Penjadwalan Otomatis - BengkelKu-POS Add-On (Laravel 11/12)
|--------------------------------------------------------------------------
| File ini menggantikan Kernel::schedule() pada Laravel 11+.
| Kalau projectmu masih Laravel 10, pindahkan isi blok di bawah ke
| app/Console/Kernel.php pada method schedule(Schedule $schedule).
|
| Jangan lupa jalankan scheduler di server (cron):
|   * * * * * cd /path/ke/project && php artisan schedule:run >> /dev/null 2>&1
*/

use Illuminate\Support\Facades\Schedule;

$timezone = 'Asia/Makassar'; // WITA - Gorontalo

// 1. Kirim pengingat servis berkala via WhatsApp (setiap hari)
Schedule::command('bengkel:send-reminders')
    ->dailyAt(config('whatsapp.reminder.send_at', '09:00'))
    ->timezone($timezone)
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/reminders.log'));

// 2. Rekap komisi mekanik (tanggal 1 tiap bulan untuk periode bulan lalu)
Schedule::command('bengkel:generate-commissions')
    ->monthlyOn(1, '01:00')
    ->timezone($timezone)
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/commissions.log'));

// 3. Sinkronisasi ke cloud (tiap 15 menit, hanya kalau SYNC_ENABLED=true)
Schedule::command('bengkel:sync')
    ->cron('*/' . env('SYNC_INTERVAL_MINUTES', 15) . ' * * * *')
    ->timezone($timezone)
    ->when(fn () => (bool) env('SYNC_ENABLED', false))
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sync.log'));

// 4. Bersihkan log WhatsApp lebih dari 90 hari (mingguan, hemat storage)
Schedule::call(function () {
    \App\Domains\CRM\Models\WhatsappLog::query()
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
})->weeklyOn(0, '02:00')->timezone($timezone);

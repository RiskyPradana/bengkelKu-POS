<?php

namespace App\Console\Commands;

use App\Domains\CRM\Models\ServiceReminder;
use App\Domains\CRM\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendServiceReminders extends Command
{
    protected $signature = 'bengkel:send-reminders {--dry-run : Tampilkan daftar target tanpa mengirim pesan} {--limit= : Batasi jumlah pesan pada eksekusi ini}';

    protected $description = 'Kirim pengingat servis berkala via WhatsApp ke pelanggan yang mendekati jatuh tempo';

    public function handle(WhatsAppService $wa): int
    {
        // Pengaman: pastikan patch migration sudah dijalankan
        if (! Schema::hasColumn('service_reminders', 'status')) {
            $this->error('Kolom `status` belum ada di tabel service_reminders.');
            $this->line('Jalankan dulu: php artisan migrate');
            $this->line('Pastikan file 2026_08_31_000006_align_addon_columns.php ada di database/migrations.');

            return self::FAILURE;
        }

        if (! config('whatsapp.reminder.enabled')) {
            $this->warn('Pengingat otomatis sedang NONAKTIF (WA_REMINDER_ENABLED=false).');

            return self::SUCCESS;
        }

        $daysBefore = (int) config('whatsapp.reminder.days_before', 3);
        $batchSize  = (int) ($this->option('limit') ?: config('whatsapp.reminder.batch_size', 50));
        $delay      = (int) config('whatsapp.reminder.delay_seconds', 2);
        $isDryRun   = (bool) $this->option('dry-run');
        $cutoff     = now()->addDays($daysBefore);

        $reminders = ServiceReminder::query()
            ->with(['customer', 'vehicle'])
            ->pending()
            ->dueBefore($cutoff)
            ->orderByRaw('COALESCE(due_date, next_reminder_date) ASC')
            ->limit($batchSize)
            ->get();

        if ($reminders->isEmpty()) {
            $this->info('Tidak ada pengingat yang perlu dikirim hari ini.');
            $this->line('Cutoff jatuh tempo: ' . $cutoff->format('d/m/Y'));

            return self::SUCCESS;
        }

        $this->info("Menemukan {$reminders->count()} pengingat (jatuh tempo dalam {$daysBefore} hari).");

        if ($isDryRun) {
            $this->table(
                ['Pelanggan', 'No. Polisi', 'Jatuh Tempo', 'No. HP'],
                $reminders->map(fn (ServiceReminder $r) => [
                    $r->customer->name ?? '-',
                    $r->vehicle->plate_number ?? '-',
                    optional($r->effective_due_date)->format('d/m/Y') ?? '-',
                    $r->customer->phone ?? '(kosong)',
                ])->all()
            );
            $this->comment('MODE DRY-RUN: tidak ada pesan yang dikirim.');

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;
        $bar = $this->output->createProgressBar($reminders->count());
        $bar->start();

        foreach ($reminders as $reminder) {
            $phone = $reminder->customer->phone ?? null;

            if (empty($phone)) {
                $reminder->markFailed('Nomor HP pelanggan kosong');
                $failed++;
                $bar->advance();
                continue;
            }

            $ok = $wa->sendServiceReminder(
                phone:        $phone,
                customerName: $reminder->customer->name ?? 'Pelanggan',
                plateNumber:  $reminder->vehicle->plate_number ?? '-',
                dueDate:      optional($reminder->effective_due_date)->format('d/m/Y') ?? '-',
                customerId:   $reminder->customer_id,
            );

            $ok ? $reminder->markSent() : $reminder->markFailed('Gagal kirim ke provider WhatsApp');
            $ok ? $sent++ : $failed++;

            $bar->advance();

            if ($delay > 0 && ! $reminder->is($reminders->last())) {
                sleep($delay);
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai. Terkirim: {$sent} | Gagal: {$failed}");

        return self::SUCCESS;
    }
}

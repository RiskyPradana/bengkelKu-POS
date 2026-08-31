<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PATCH MIGRATION
 * Menyelaraskan nama kolom antara migrasi Modul 6/7 dengan kode command & Livewire.
 * Aman dijalankan berulang kali (semua pengecekan pakai hasColumn).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ────── service_reminders ──────
        if (Schema::hasTable('service_reminders')) {
            Schema::table('service_reminders', function (Blueprint $table): void {
                if (! Schema::hasColumn('service_reminders', 'status')) {
                    $table->string('status', 20)->default('pending')->index();
                }

                if (! Schema::hasColumn('service_reminders', 'due_date')) {
                    $table->date('due_date')->nullable()->index();
                }

                if (! Schema::hasColumn('service_reminders', 'sent_at')) {
                    $table->timestamp('sent_at')->nullable();
                }

                if (! Schema::hasColumn('service_reminders', 'notes')) {
                    $table->text('notes')->nullable();
                }

                if (! Schema::hasColumn('service_reminders', 'attempt_count')) {
                    $table->unsignedSmallInteger('attempt_count')->default(0);
                }
            });

            // Backfill: due_date diambil dari next_reminder_date yang sudah ada
            if (Schema::hasColumn('service_reminders', 'next_reminder_date')) {
                DB::statement('UPDATE service_reminders SET due_date = next_reminder_date WHERE due_date IS NULL AND next_reminder_date IS NOT NULL');
            }

            // Backfill: status diturunkan dari is_active
            if (Schema::hasColumn('service_reminders', 'is_active')) {
                DB::statement("UPDATE service_reminders SET status = CASE WHEN is_active = 1 THEN 'pending' ELSE 'cancelled' END WHERE status IS NULL OR status = ''");
            }
        }

        // ────── whatsapp_logs ──────
        if (Schema::hasTable('whatsapp_logs')) {
            Schema::table('whatsapp_logs', function (Blueprint $table): void {
                // WhatsAppService menyimpan body respons provider ke kolom ini
                if (! Schema::hasColumn('whatsapp_logs', 'response')) {
                    $table->text('response')->nullable();
                }

                if (! Schema::hasColumn('whatsapp_logs', 'provider')) {
                    $table->string('provider', 30)->nullable();
                }
            });

            // Kolom `type` awalnya ENUM terbatas. Ubah ke VARCHAR supaya
            // tipe baru seperti 'pickup' tidak menyebabkan error.
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE whatsapp_logs MODIFY COLUMN type VARCHAR(30) NOT NULL DEFAULT 'custom'");
                DB::statement("ALTER TABLE whatsapp_logs MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'queued'");
            }
        }

        // ────── mechanic_commissions ──────
        if (Schema::hasTable('mechanic_commissions')) {
            Schema::table('mechanic_commissions', function (Blueprint $table): void {
                if (! Schema::hasColumn('mechanic_commissions', 'earned_at')) {
                    $table->timestamp('earned_at')->nullable()->index();
                }

                if (! Schema::hasColumn('mechanic_commissions', 'service_amount')) {
                    $table->decimal('service_amount', 15, 2)->default(0);
                }

                if (! Schema::hasColumn('mechanic_commissions', 'commission_amount')) {
                    $table->decimal('commission_amount', 15, 2)->default(0);
                }

                if (! Schema::hasColumn('mechanic_commissions', 'branch_id')) {
                    $table->uuid('branch_id')->nullable()->index();
                }
            });

            // Backfill earned_at dari created_at
            DB::statement('UPDATE mechanic_commissions SET earned_at = created_at WHERE earned_at IS NULL');
        }

        // ────── commission_summaries ──────
        if (Schema::hasTable('commission_summaries')) {
            Schema::table('commission_summaries', function (Blueprint $table): void {
                foreach ([
                    'period'            => fn () => $table->string('period', 7)->nullable()->index(),
                    'total_work_orders' => fn () => $table->unsignedInteger('total_work_orders')->default(0),
                    'total_service'     => fn () => $table->decimal('total_service', 15, 2)->default(0),
                    'total_commission'  => fn () => $table->decimal('total_commission', 15, 2)->default(0),
                    'rate'              => fn () => $table->decimal('rate', 5, 2)->default(10),
                    'status'            => fn () => $table->string('status', 20)->default('draft'),
                    'generated_at'      => fn () => $table->timestamp('generated_at')->nullable(),
                ] as $column => $definition) {
                    if (! Schema::hasColumn('commission_summaries', $column)) {
                        $definition();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Kolom tambahan dibiarkan supaya data tidak hilang.
        // Kalau benar-benar perlu rollback, hapus manual lewat SQL.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PATCH MIGRATION — Sesi 13
 *
 * Menyelaraskan tabel work_orders dengan kode aplikasi:
 * - Sebagian baris lama bisa saja punya nilai `status` yang tidak persis
 *   cocok dengan App\Domains\WorkOrder\Enums\WorkOrderStatus
 *   (Pending / In Progress / Completed / Paid). Karena kolom ini di-cast
 *   langsung ke native PHP enum di model WorkOrder, SATU baris saja dengan
 *   nilai status yang tidak valid membuat SELURUH query yang mengambil
 *   baris tersebut gagal fatal (ValueError) — ini akar masalah
 *   "Mode Mekanik error semua".
 * - Kolom `wo_number`, `completed_at`, `paid_at` dipakai di banyak tempat
 *   (blade mobile, halaman komisi) tapi belum pernah dibuat sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('work_orders')) {
            return;
        }

        // ── Normalisasi nilai status yang tidak valid ──
        $map = [
            'pending'     => 'Pending',
            'in progress' => 'In Progress',
            'in_progress' => 'In Progress',
            'inprogress'  => 'In Progress',
            'proses'      => 'In Progress',
            'dikerjakan'  => 'In Progress',
            'completed'   => 'Completed',
            'complete'    => 'Completed',
            'selesai'     => 'Completed',
            'done'        => 'Completed',
            'paid'        => 'Paid',
            'lunas'       => 'Paid',
        ];

        $valid = ['Pending', 'In Progress', 'Completed', 'Paid'];

        $rows = DB::table('work_orders')->select('id', 'status')->get();

        foreach ($rows as $row) {
            $current = (string) $row->status;

            if (in_array($current, $valid, true)) {
                continue;
            }

            $normalized = $map[strtolower(trim($current))] ?? 'Pending';

            DB::table('work_orders')->where('id', $row->id)->update(['status' => $normalized]);
        }

        // ── Kolom tambahan ──
        Schema::table('work_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('work_orders', 'wo_number')) {
                $table->string('wo_number', 40)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('work_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->index();
            }

            if (! Schema::hasColumn('work_orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->index();
            }
        });

        // ── Backfill wo_number ──
        if (Schema::hasColumn('work_orders', 'wo_number')) {
            $toNumber = DB::table('work_orders')->whereNull('wo_number')->orderBy('created_at')->get(['id', 'created_at']);

            $seqPerDay = [];

            foreach ($toNumber as $wo) {
                $day = \Illuminate\Support\Carbon::parse($wo->created_at)->format('Ymd');
                $seqPerDay[$day] = ($seqPerDay[$day] ?? 0) + 1;
                $number = 'WO-' . $day . '-' . str_pad((string) $seqPerDay[$day], 4, '0', STR_PAD_LEFT);

                DB::table('work_orders')->where('id', $wo->id)->update(['wo_number' => $number]);
            }
        }

        // ── Backfill completed_at / paid_at dari updated_at ──
        if (Schema::hasColumn('work_orders', 'completed_at')) {
            DB::table('work_orders')
                ->whereIn('status', ['Completed', 'Paid'])
                ->whereNull('completed_at')
                ->update(['completed_at' => DB::raw('updated_at')]);
        }

        if (Schema::hasColumn('work_orders', 'paid_at')) {
            DB::table('work_orders')
                ->where('status', 'Paid')
                ->whereNull('paid_at')
                ->update(['paid_at' => DB::raw('updated_at')]);
        }
    }

    public function down(): void
    {
        // Kolom & normalisasi data dibiarkan supaya data tidak hilang.
    }
};

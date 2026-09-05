<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PATCH MIGRATION — Sesi 13
 * - users: target KPI bulanan + bonus KPI per mekanik (profit stream ke-2).
 * - mechanic_commissions: kolom `source` untuk membedakan komisi kendaraan
 *   masuk dari jenis komisi lain di masa depan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'monthly_target')) {
                    $table->unsignedInteger('monthly_target')->nullable()->comment('Target jumlah kendaraan/WO per bulan untuk bonus KPI mekanik');
                }

                if (! Schema::hasColumn('users', 'kpi_bonus_amount')) {
                    $table->decimal('kpi_bonus_amount', 18, 2)->nullable()->default(0)->comment('Bonus Rp jika target KPI bulanan tercapai');
                }
            });
        }

        if (Schema::hasTable('mechanic_commissions') && ! Schema::hasColumn('mechanic_commissions', 'source')) {
            Schema::table('mechanic_commissions', function (Blueprint $table): void {
                // 'vehicle' = komisi per kendaraan masuk (profit stream 1)
                $table->string('source', 20)->default('vehicle')->index();
            });
        }
    }

    public function down(): void
    {
        // Dibiarkan supaya data tidak hilang.
    }
};

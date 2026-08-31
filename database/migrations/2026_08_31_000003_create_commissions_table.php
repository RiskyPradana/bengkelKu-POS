<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Komisi per mekanik per WO
        Schema::create('mechanic_commissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');           // Mekanik
            $table->uuid('work_order_id');
            $table->uuid('branch_id')->nullable();
            $table->decimal('commission_amount', 18, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);  // Persentase %
            $table->decimal('base_amount', 18, 2)->default(0);     // Dasar perhitungan
            $table->string('period');          // Format: '2026-08' (tahun-bulan)
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');
        });

        // Rekap komisi bulanan
        Schema::create('commission_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');           // Mekanik
            $table->uuid('branch_id')->nullable();
            $table->string('period');          // Format: '2026-08'
            $table->integer('total_wo');       // Jumlah WO selesai
            $table->decimal('total_service_amount', 18, 2)->default(0);
            $table->decimal('total_commission', 18, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->unique(['user_id', 'period']); // Satu rekap per mekanik per bulan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_summaries');
        Schema::dropIfExists('mechanic_commissions');
    }
};

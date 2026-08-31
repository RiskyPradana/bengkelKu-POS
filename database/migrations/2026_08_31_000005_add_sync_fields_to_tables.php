<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tambahkan is_synced & synced_at ke semua tabel utama
    // untuk mendukung Modul 7: Hybrid Offline Sync Engine
    private array $tables = [
        'customers', 'vehicles', 'products', 'service_items',
        'work_orders', 'work_order_items', 'invoices', 'payments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('is_synced')->default(false)->after('updated_at');
                $t->timestamp('synced_at')->nullable()->after('is_synced');
            });
        }

        // Tabel log sync
        Schema::create('sync_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('table_name');
            $table->uuid('record_id');
            $table->enum('direction', ['push', 'pull']);
            $table->enum('status', ['pending', 'success', 'failed', 'conflict']);
            $table->text('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->index(['table_name', 'record_id']);
            $table->index(['status', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['is_synced', 'synced_at']);
            });
        }
    }
};

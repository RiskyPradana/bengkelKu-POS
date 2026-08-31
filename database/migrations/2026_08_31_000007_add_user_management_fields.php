<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyiapkan tabel users untuk manajemen role & multi-cabang.
 * Aman dijalankan berulang kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('kasir')->index();
            }

            if (! Schema::hasColumn('users', 'branch_id')) {
                $table->uuid('branch_id')->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 25)->nullable();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }

            if (! Schema::hasColumn('users', 'commission_rate')) {
                // Rate komisi khusus per mekanik. Kalau null, pakai default global.
                $table->decimal('commission_rate', 5, 2)->nullable();
            }
        });

        // Kalau kolom role sebelumnya ENUM, ubah ke VARCHAR agar fleksibel
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'kasir'");
            } catch (\Throwable $e) {
                // Diabaikan: kolom mungkin sudah VARCHAR
            }
        }

        // Promosikan user pertama menjadi owner supaya tidak ada yang terkunci
        $firstUser = DB::table('users')->orderBy('created_at')->first();

        if ($firstUser) {
            $needsPromotion = DB::table('users')
                ->whereIn('role', ['owner', 'admin'])
                ->doesntExist();

            if ($needsPromotion) {
                DB::table('users')->where('id', $firstUser->id)->update(['role' => 'owner']);
            }
        }
    }

    public function down(): void
    {
        // Kolom dibiarkan supaya data user tidak hilang.
    }
};

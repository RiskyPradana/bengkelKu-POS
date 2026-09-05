<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memindahkan definisi role & hak akses dari config/roles.php ke database,
 * supaya bisa diatur lewat menu Pengaturan > Role & Hak Akses (/pengaturan/role)
 * tanpa perlu edit kode.
 *
 * config/roles.php tetap ada sebagai fallback (dipakai App\Domains\MasterData\Services\RoleRegistry
 * kalau tabel ini belum ada / masih kosong, misalnya sesaat sebelum migration ini jalan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_settings', function (Blueprint $table): void {
            $table->string('key', 30)->primary();
            $table->string('label', 100);
            $table->string('description', 255)->nullable();
            $table->string('color', 20)->default('gray');
            $table->unsignedInteger('level')->default(0);
            $table->json('access')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $roles = [
            'owner' => [
                'label'       => 'Owner / Pemilik',
                'description' => 'Akses penuh ke semua fitur termasuk laporan keuangan dan manajemen user.',
                'color'       => 'purple',
                'level'       => 100,
                'is_default'  => false,
                'is_system'   => true,
            ],
            'admin' => [
                'label'       => 'Admin',
                'description' => 'Mengelola operasional harian, stok, dan data master. Tidak bisa hapus user owner.',
                'color'       => 'blue',
                'level'       => 80,
                'is_default'  => false,
                'is_system'   => false,
            ],
            'kasir' => [
                'label'       => 'Kasir',
                'description' => 'Melayani pembayaran, membuat nota, dan mengelola data pelanggan.',
                'color'       => 'emerald',
                'level'       => 50,
                'is_default'  => true,
                'is_system'   => false,
            ],
            'gudang' => [
                'label'       => 'Staf Gudang',
                'description' => 'Mengelola stok sparepart, transfer antar cabang, dan penerimaan barang.',
                'color'       => 'orange',
                'level'       => 40,
                'is_default'  => false,
                'is_system'   => false,
            ],
            'mekanik' => [
                'label'       => 'Mekanik',
                'description' => 'Mengerjakan work order lewat aplikasi mobile. Bisa lihat komisi sendiri.',
                'color'       => 'amber',
                'level'       => 30,
                'is_default'  => false,
                'is_system'   => false,
            ],
        ];

        // route => role yang boleh akses (dipindah dari config('roles.access'))
        $accessByRoute = [
            'analytics'         => ['owner', 'admin'],
            'inventory'         => ['owner', 'admin', 'gudang'],
            'purchasing'        => ['owner', 'admin', 'gudang'],
            'crm.reminders'     => ['owner', 'admin', 'kasir'],
            'commission'        => ['owner', 'admin'],
            'settings.users'    => ['owner'],
            'settings.branches' => ['owner', 'admin'],
            'settings.roles'    => ['owner'],
            'mobile.home'       => ['owner', 'admin', 'mekanik'],
            'mobile.scanner'    => ['owner', 'admin', 'mekanik', 'gudang'],
            'mobile.wo'         => ['owner', 'admin', 'mekanik'],
            'reports'           => ['owner', 'admin'],
        ];

        $now = now();

        foreach ($roles as $key => $meta) {
            $roleAccess = [];

            foreach ($accessByRoute as $route => $allowedRoles) {
                if (in_array($key, $allowedRoles, true)) {
                    $roleAccess[] = $route;
                }
            }

            DB::table('role_settings')->insertOrIgnore([
                'key'         => $key,
                'label'       => $meta['label'],
                'description' => $meta['description'],
                'color'       => $meta['color'],
                'level'       => $meta['level'],
                'access'      => json_encode($roleAccess),
                'is_default'  => $meta['is_default'],
                'is_system'   => $meta['is_system'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_settings');
    }
};

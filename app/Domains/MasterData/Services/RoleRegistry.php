<?php

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Models\RoleSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Jembatan antara pengaturan role di database (tabel `role_settings`, bisa
 * diubah lewat menu Pengaturan > Role & Hak Akses di /pengaturan/role) dan
 * config/roles.php yang dipakai sebagai fallback kalau migration belum
 * dijalankan atau tabelnya masih kosong.
 *
 * Semua tempat yang sebelumnya baca config('roles.list') / config('roles.access')
 * / config('roles.default') sebaiknya pakai class ini supaya perubahan lewat
 * halaman web langsung berlaku tanpa perlu deploy ulang kode.
 */
class RoleRegistry
{
    public static function available(): bool
    {
        try {
            return Schema::hasTable('role_settings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return array<string, array{label:string, description:?string, color:string, level:int, is_system?:bool}>
     */
    public static function list(): array
    {
        if (self::available()) {
            $rows = RoleSetting::query()->orderByDesc('level')->get();

            if ($rows->isNotEmpty()) {
                return $rows->mapWithKeys(fn (RoleSetting $r) => [
                    $r->key => [
                        'label'       => $r->label,
                        'description' => $r->description,
                        'color'       => $r->color,
                        'level'       => $r->level,
                        'is_system'   => (bool) $r->is_system,
                    ],
                ])->all();
            }
        }

        return (array) config('roles.list', []);
    }

    /**
     * @return array<string, array<int, string>> nama route => daftar kode role yang diizinkan
     */
    public static function access(): array
    {
        if (self::available()) {
            $rows = RoleSetting::query()->get();

            if ($rows->isNotEmpty()) {
                $access = [];

                foreach ($rows as $row) {
                    foreach ((array) ($row->access ?? []) as $route) {
                        $access[$route] ??= [];
                        $access[$route][] = $row->key;
                    }
                }

                return $access;
            }
        }

        return (array) config('roles.access', []);
    }

    public static function default(): string
    {
        if (self::available()) {
            $row = RoleSetting::query()->where('is_default', true)->first();

            if ($row) {
                return $row->key;
            }
        }

        return (string) config('roles.default', 'kasir');
    }

    /**
     * Semua halaman yang bisa diatur hak aksesnya lewat halaman Role & Hak Akses.
     * Cocokkan dengan menu di resources/views/components/sidebar-addons.blade.php.
     *
     * @return array<string, string> nama route => label menu
     */
    public static function manageableRoutes(): array
    {
        return [
            'analytics'         => 'Dashboard Analitik',
            'inventory'         => 'Stok Multi-Cabang',
            'purchasing'        => 'Pembelian & Supplier',
            'crm.reminders'     => 'CRM & Pengingat WA',
            'commission'        => 'Komisi Mekanik',
            'reports'           => 'Laporan Keuangan',
            'settings.users'    => 'Manajemen User',
            'settings.branches' => 'Manajemen Cabang',
            'settings.roles'    => 'Role & Hak Akses',
            'settings.network'  => 'Jaringan Lokal (LAN)',
            'settings.printer'  => 'Pengaturan Printer',
            'mobile.home'       => 'Mode Mekanik (Mobile)',
            'mobile.scanner'    => 'Scan Sparepart (Mobile)',
            'mobile.wo'         => 'SPK Mekanik (Mobile)',
        ];
    }
}

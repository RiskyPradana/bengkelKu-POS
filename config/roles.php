<?php

/*
|--------------------------------------------------------------------------
| Definisi Role & Hak Akses — BengkelKu-POS
|--------------------------------------------------------------------------
| File ini dipakai sebagai NILAI AWAL / FALLBACK.
| Setelah migration dijalankan, role & hak akses sesungguhnya disimpan di
| tabel `role_settings` dan bisa diatur lewat menu Pengaturan > Role & Hak
| Akses (/pengaturan/role) tanpa perlu edit file ini.
| Lihat App\Domains\MasterData\Services\RoleRegistry.
*/

return [

    'default' => 'kasir',

    'list' => [

        'owner' => [
            'label'       => 'Owner / Pemilik',
            'description' => 'Akses penuh ke semua fitur termasuk laporan keuangan dan manajemen user.',
            'color'       => 'purple',
            'level'       => 100,
        ],

        'admin' => [
            'label'       => 'Admin',
            'description' => 'Mengelola operasional harian, stok, dan data master. Tidak bisa hapus user owner.',
            'color'       => 'blue',
            'level'       => 80,
        ],

        'kasir' => [
            'label'       => 'Kasir',
            'description' => 'Melayani pembayaran, membuat nota, dan mengelola data pelanggan.',
            'color'       => 'emerald',
            'level'       => 50,
        ],

        'mekanik' => [
            'label'       => 'Mekanik',
            'description' => 'Mengerjakan work order lewat aplikasi mobile. Bisa lihat komisi sendiri.',
            'color'       => 'amber',
            'level'       => 30,
        ],

        'gudang' => [
            'label'       => 'Staf Gudang',
            'description' => 'Mengelola stok sparepart, transfer antar cabang, dan penerimaan barang.',
            'color'       => 'orange',
            'level'       => 40,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Role yang boleh mengakses tiap halaman
    |----------------------------------------------------------------------
    | Dipakai oleh <x-sidebar-addons /> dan middleware role.
    */
    'access' => [
        'analytics'          => ['owner', 'admin'],
        'inventory'          => ['owner', 'admin', 'gudang'],
        'purchasing'         => ['owner', 'admin', 'gudang'],
        'crm.reminders'      => ['owner', 'admin', 'kasir'],
        'commission'         => ['owner', 'admin'],
        'settings.users'     => ['owner'],
        'settings.branches'  => ['owner', 'admin'],
        'settings.roles'     => ['owner'],
        'mobile.home'        => ['owner', 'admin', 'mekanik'],
        'mobile.scanner'     => ['owner', 'admin', 'mekanik', 'gudang'],
        'mobile.wo'          => ['owner', 'admin', 'mekanik'],
        'reports'            => ['owner', 'admin'],
    ],

    /*
    |----------------------------------------------------------------------
    | Mode Longgar (Fallback)
    |----------------------------------------------------------------------
    | Kalau true dan kolom `users.role` belum ada di database,
    | semua menu ditampilkan agar sistem tidak terlihat "kosong".
    | Set false setelah manajemen user sudah rapi.
    */
    'permissive_when_no_role_column' => true,
];

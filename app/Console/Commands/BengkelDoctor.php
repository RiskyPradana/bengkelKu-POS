<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnosa cepat instalasi add-on BengkelKu-POS.
 * Jalankan: php artisan bengkel:doctor
 */
class BengkelDoctor extends Command
{
    protected $signature = 'bengkel:doctor';

    protected $description = 'Cek kelengkapan instalasi add-on: tabel, kolom, route, view, dan konfigurasi';

    private int $problems = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <bg=blue;fg=white> BENGKELKU-POS DOCTOR </>');
        $this->newLine();

        $this->checkTables();
        $this->checkUserColumns();
        $this->checkRoutes();
        $this->checkViews();
        $this->checkConfig();
        $this->checkUsers();

        $this->newLine();

        if ($this->problems === 0) {
            $this->line('  <bg=green;fg=white> SEMUA SEHAT </> Tidak ada masalah ditemukan.');
        } else {
            $this->line('  <bg=yellow;fg=black> ' . $this->problems . ' MASALAH </> Ikuti saran di atas untuk memperbaiki.');
        }

        $this->newLine();

        return self::SUCCESS;
    }

    private function checkTables(): void
    {
        $this->line('<options=bold>1. Tabel Database</>');

        $tables = [
            'branch_stocks', 'stock_movements', 'inter_branch_transfers',
            'mechanic_commissions', 'commission_summaries',
            'service_reminders', 'whatsapp_logs', 'sync_logs',
        ];

        foreach ($tables as $table) {
            Schema::hasTable($table)
                ? $this->ok($table)
                : $this->problem($table, 'Jalankan: php artisan migrate');
        }

        $this->newLine();
    }

    private function checkUserColumns(): void
    {
        $this->line('<options=bold>2. Kolom Tabel Users</>');

        if (! Schema::hasTable('users')) {
            $this->problem('tabel users', 'Tabel users tidak ditemukan!');
            $this->newLine();

            return;
        }

        foreach (['role', 'branch_id', 'phone', 'is_active', 'last_login_at'] as $column) {
            Schema::hasColumn('users', $column)
                ? $this->ok('users.' . $column)
                : $this->problem('users.' . $column, 'Jalankan: php artisan migrate (butuh migrasi 000007)');
        }

        $this->newLine();
    }

    private function checkRoutes(): void
    {
        $this->line('<options=bold>3. Route Terdaftar</>');

        $routes = [
            'analytics'      => 'Dashboard Analitik',
            'inventory'      => 'Stok Multi-Cabang',
            'crm.reminders'  => 'CRM dan Pengingat',
            'commission'     => 'Komisi Mekanik',
            'settings.users' => 'Manajemen User',
            'mobile.home'    => 'Mobile Mekanik',
            'mobile.scanner' => 'Scanner Barcode',
        ];

        foreach ($routes as $name => $label) {
            Route::has($name)
                ? $this->ok($name . '  (' . $label . ')')
                : $this->problem($name, 'Belum ada di routes/web.php');
        }

        $this->newLine();
    }

    private function checkViews(): void
    {
        $this->line('<options=bold>4. File View</>');

        $views = [
            'components/sidebar-addons.blade.php'          => 'Komponen sidebar',
            'livewire/dashboard/owner-dashboard.blade.php' => 'Dashboard analitik',
            'livewire/settings/user-management.blade.php'  => 'Manajemen user',
        ];

        foreach ($views as $path => $label) {
            file_exists(resource_path('views/' . $path))
                ? $this->ok($label)
                : $this->problem($label, 'File tidak ada: resources/views/' . $path);
        }

        $layout = resource_path('views/layouts/app.blade.php');

        if (file_exists($layout)) {
            $content = (string) file_get_contents($layout);

            str_contains($content, 'sidebar-addons')
                ? $this->ok('Komponen sidebar sudah dipasang di layout')
                : $this->problem(
                    'Komponen sidebar BELUM dipasang',
                    'Tambahkan tag x-sidebar-addons di dalam nav pada resources/views/layouts/app.blade.php'
                );

            (str_contains($content, 'chart.js') || str_contains($content, 'chart.umd'))
                ? $this->ok('Chart.js sudah dimuat')
                : $this->caution('Chart.js belum dimuat', 'Chart di halaman /analitik tidak akan tampil');
        } else {
            $this->problem('layouts/app.blade.php', 'File layout tidak ditemukan');
        }

        $this->newLine();
    }

    private function checkConfig(): void
    {
        $this->line('<options=bold>5. Konfigurasi</>');

        file_exists(config_path('whatsapp.php'))
            ? $this->ok('config/whatsapp.php')
            : $this->problem('config/whatsapp.php', 'Salin dari paket add-on');

        file_exists(config_path('roles.php'))
            ? $this->ok('config/roles.php')
            : $this->problem('config/roles.php', 'Salin dari paket add-on');

        $this->line('   - Provider WhatsApp : <fg=cyan>' . config('whatsapp.provider', 'belum diset') . '</>');
        $this->line('   - Driver database   : <fg=cyan>' . DB::getDriverName() . '</>');
        $this->line('   - Mode debug        : <fg=cyan>' . (config('app.debug') ? 'aktif' : 'nonaktif') . '</>');

        $this->newLine();
    }

    private function checkUsers(): void
    {
        $this->line('<options=bold>6. Data User</>');

        if (! Schema::hasTable('users')) {
            $this->newLine();

            return;
        }

        $total = DB::table('users')->count();
        $this->line('   - Total user: <fg=cyan>' . $total . '</>');

        if ($total === 0) {
            $this->problem('Belum ada user', 'Buat user: php artisan bengkel:create-user');
            $this->newLine();

            return;
        }

        if (! Schema::hasColumn('users', 'role')) {
            $this->newLine();

            return;
        }

        $byRole = DB::table('users')
            ->selectRaw('role, COUNT(*) as jumlah')
            ->groupBy('role')
            ->pluck('jumlah', 'role');

        foreach ($byRole as $role => $count) {
            $label = config('roles.list.' . $role . '.label', $role ?: '(kosong)');
            $this->line('   - ' . str_pad((string) $label, 22) . ': ' . $count . ' user');
        }

        $hasOwner = DB::table('users')->whereIn('role', ['owner', 'admin'])->exists();

        $hasOwner
            ? $this->ok('Ada user dengan role owner/admin')
            : $this->problem('Tidak ada owner/admin', 'Jalankan: php artisan bengkel:create-user --promote');

        $this->newLine();
    }

    private function ok(string $label): void
    {
        $this->line('   <fg=green>[OK]</>   ' . $label);
    }

    private function problem(string $label, string $hint): void
    {
        $this->problems++;
        $this->line('   <fg=red>[GAGAL]</> ' . $label);
        $this->line('            <fg=yellow>-> ' . $hint . '</>');
    }

    private function caution(string $label, string $hint): void
    {
        $this->problems++;
        $this->line('   <fg=yellow>[PERINGATAN]</> ' . $label);
        $this->line('            <fg=yellow>-> ' . $hint . '</>');
    }
}

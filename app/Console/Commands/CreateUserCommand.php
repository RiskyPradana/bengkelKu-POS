<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

/**
 * Membuat user baru atau mempromosikan user jadi owner lewat terminal.
 *
 * Contoh:
 *   php artisan bengkel:create-user
 *   php artisan bengkel:create-user --promote
 *   php artisan bengkel:create-user --list
 */
class CreateUserCommand extends Command
{
    protected $signature = 'bengkel:create-user {--promote : Ubah role user yang sudah ada menjadi owner} {--list : Tampilkan semua user} {--reset-password : Ganti password user yang sudah ada}';

    protected $description = 'Kelola user lewat terminal: buat baru, promosikan jadi owner, atau reset password';

    public function handle(): int
    {
        if (! Schema::hasTable('users')) {
            $this->error('Tabel users tidak ditemukan. Jalankan php artisan migrate dulu.');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('users', 'role')) {
            $this->error('Kolom role belum ada di tabel users.');
            $this->line('Jalankan: php artisan migrate');

            return self::FAILURE;
        }

        if ($this->option('list')) {
            return $this->listUsers();
        }

        if ($this->option('promote')) {
            return $this->promoteUser();
        }

        if ($this->option('reset-password')) {
            return $this->resetPassword();
        }

        return $this->createUser();
    }

    private function listUsers(): int
    {
        $users = DB::table('users')->orderBy('role')->orderBy('name')->get();

        if ($users->isEmpty()) {
            $this->warn('Belum ada user sama sekali.');

            return self::SUCCESS;
        }

        $rows = $users->map(fn ($u) => [
            $u->name,
            $u->email,
            config('roles.list.' . ($u->role ?? '') . '.label', $u->role ?: '-'),
            (property_exists($u, 'is_active') && ! $u->is_active) ? 'Nonaktif' : 'Aktif',
        ])->all();

        $this->newLine();
        $this->table(['Nama', 'Email', 'Role', 'Status'], $rows);
        $this->newLine();

        return self::SUCCESS;
    }

    private function createUser(): int
    {
        $this->newLine();
        $this->line('  <bg=blue;fg=white> BUAT USER BARU </>');
        $this->newLine();

        $name  = (string) $this->ask('Nama lengkap');
        $email = (string) $this->ask('Email (untuk login)');

        $roleKeys   = array_keys(config('roles.list', []));
        $roleLabels = [];

        foreach ($roleKeys as $key) {
            $roleLabels[] = $key . ' - ' . config('roles.list.' . $key . '.label');
        }

        $picked = (string) $this->choice('Pilih role', $roleLabels, 0);
        $role   = trim(explode(' - ', $picked)[0]);

        $phone    = (string) ($this->ask('Nomor WhatsApp (opsional, contoh 081234567890)') ?? '');
        $password = (string) $this->secret('Password (minimal 8 karakter)');
        $confirm  = (string) $this->secret('Ulangi password');

        if ($password !== $confirm) {
            $this->error('Password tidak sama. Dibatalkan.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name'     => ['required', 'string', 'min:3', 'max:100'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            $this->newLine();
            $this->error('Data tidak valid:');

            foreach ($validator->errors()->all() as $message) {
                $this->line('  - ' . $message);
            }

            return self::FAILURE;
        }

        $data = [
            'name'     => $name,
            'email'    => strtolower(trim($email)),
            'password' => Hash::make($password),
            'role'     => $role,
        ];

        if (Schema::hasColumn('users', 'phone') && $phone !== '') {
            $data['phone'] = $this->normalizePhone($phone);
        }

        if (Schema::hasColumn('users', 'is_active')) {
            $data['is_active'] = true;
        }

        $user = User::create($data);

        $this->newLine();
        $this->line('  <bg=green;fg=white> BERHASIL </>');
        $this->line('  Nama  : ' . $user->name);
        $this->line('  Email : ' . $user->email);
        $this->line('  Role  : ' . config('roles.list.' . $role . '.label', $role));
        $this->newLine();
        $this->line('  User bisa langsung login di halaman /login');
        $this->newLine();

        return self::SUCCESS;
    }

    private function promoteUser(): int
    {
        $users = DB::table('users')->orderBy('name')->get();

        if ($users->isEmpty()) {
            $this->error('Belum ada user. Buat dulu dengan: php artisan bengkel:create-user');

            return self::FAILURE;
        }

        $options = $users->map(fn ($u) => $u->email . ' (' . $u->name . ')')->all();
        $picked  = (string) $this->choice('Pilih user yang akan dijadikan OWNER', $options, 0);
        $email   = trim(explode(' (', $picked)[0]);

        DB::table('users')->where('email', $email)->update(['role' => 'owner']);

        $this->newLine();
        $this->info('Berhasil! ' . $email . ' sekarang punya role owner (akses penuh).');
        $this->newLine();

        return self::SUCCESS;
    }

    private function resetPassword(): int
    {
        $users = DB::table('users')->orderBy('name')->get();

        if ($users->isEmpty()) {
            $this->error('Belum ada user.');

            return self::FAILURE;
        }

        $options = $users->map(fn ($u) => $u->email . ' (' . $u->name . ')')->all();
        $picked  = (string) $this->choice('Pilih user', $options, 0);
        $email   = trim(explode(' (', $picked)[0]);

        $password = (string) $this->secret('Password baru (minimal 8 karakter)');
        $confirm  = (string) $this->secret('Ulangi password baru');

        if ($password !== $confirm) {
            $this->error('Password tidak sama. Dibatalkan.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password minimal 8 karakter.');

            return self::FAILURE;
        }

        DB::table('users')->where('email', $email)->update(['password' => Hash::make($password)]);

        $this->newLine();
        $this->info('Password ' . $email . ' berhasil diganti.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }
}

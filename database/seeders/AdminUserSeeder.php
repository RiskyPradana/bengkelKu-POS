<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bengkel.com'],
            [
                'name'     => 'Admin BengkelOS',
                'email'    => 'admin@bengkel.com',
                'password' => Hash::make('password123'),
            ]
        );

        $this->command->info('\u2705 Admin user berhasil dibuat!');
        $this->command->info('   Email    : admin@bengkel.com');
        $this->command->info('   Password : password123');
    }
}

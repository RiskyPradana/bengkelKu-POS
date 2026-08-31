<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin Pusat',
            'Service Advisor',
            'Kasir',
            'Mekanik',
        ];

        $permissions = [
            'manage master data',
            'manage customers',
            'manage vehicles',
            'manage products',
            'manage work orders',
            'manage invoices',
            'manage payments',
            'manage inventory',
            'view reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName);
        }

        foreach ($roles as $roleName) {
            $role = Role::findOrCreate($roleName);

            if ($roleName === 'Admin Pusat') {
                $role->syncPermissions(Permission::all());
            }
        }
    }
}

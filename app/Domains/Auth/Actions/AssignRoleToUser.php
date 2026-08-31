<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignRoleToUser
{
    public function handle(User $user, string|Role $role): User
    {
        $user->assignRole($role);

        return $user;
    }
}

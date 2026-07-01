<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    protected function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}

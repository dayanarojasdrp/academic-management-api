<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('grades.manage')
            || ($user->hasPermission('grades.view') && ! $user->hasAnyRole(['student', 'professor']));
    }

    public function view(User $user, Grade $grade): bool
    {
        return $this->viewAny($user)
            || $user->student_id === $grade->student_id
            || ($user->professor_id && $user->professor_id === $grade->professor_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('grades.manage');
    }

    public function update(User $user, Grade $grade): bool
    {
        if ($grade->locked_at && ! $user->hasPermission('grades.change.approve')) {
            return false;
        }

        return $user->hasPermission('grades.manage')
            || ($user->professor_id && $user->professor_id === $grade->professor_id && $grade->status !== 'published');
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->hasPermission('grades.manage') && ! $grade->locked_at;
    }
}

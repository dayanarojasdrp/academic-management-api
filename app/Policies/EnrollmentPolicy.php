<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('enrollments.manage')
            || ($user->hasPermission('enrollments.view') && ! $user->hasRole('student'));
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        return $this->viewAny($user) || $user->student_id === $enrollment->student_id;
    }

    public function create(User $user): bool
    {
        return $this->hasAnyPermission($user, ['enrollments.create', 'enrollments.manage']);
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        return $user->hasPermission('enrollments.manage');
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        return $user->hasPermission('enrollments.manage');
    }
}

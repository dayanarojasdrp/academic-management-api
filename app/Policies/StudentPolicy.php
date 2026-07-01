<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyPermission($user, ['students.view', 'students.manage']);
    }

    public function view(User $user, Student $student): bool
    {
        return $this->viewAny($user) || $user->student_id === $student->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('students.manage');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasPermission('students.manage');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasPermission('students.manage');
    }

    public function viewAcademicHistory(User $user, Student $student): bool
    {
        return $user->student_id === $student->id
            || $this->hasAnyPermission($user, ['students.view', 'students.manage'])
            || ($user->hasPermission('academic_history.view') && ! $user->hasRole('student'));
    }
}

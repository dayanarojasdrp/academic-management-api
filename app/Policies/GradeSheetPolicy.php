<?php

namespace App\Policies;

use App\Models\GradeSheet;
use App\Models\User;

class GradeSheetPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyPermission($user, ['grades.view', 'grades.manage']);
    }

    public function view(User $user, GradeSheet $gradeSheet): bool
    {
        return $this->viewAny($user)
            || ($user->professor_id && $user->professor_id === $gradeSheet->professor_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('grades.manage');
    }

    public function update(User $user, GradeSheet $gradeSheet): bool
    {
        return $user->hasPermission('grades.manage') && ! in_array($gradeSheet->status, ['closed', 'archived'], true);
    }

    public function submit(User $user, GradeSheet $gradeSheet): bool
    {
        return $user->hasPermission('grades.manage')
            && ($gradeSheet->professor_id === null || $user->professor_id === $gradeSheet->professor_id || $user->hasAnyRole(['registrar', 'academic_coordinator']));
    }

    public function sign(User $user, GradeSheet $gradeSheet): bool
    {
        return $user->hasPermission('grades.sign')
            && ($gradeSheet->professor_id === null || $user->professor_id === $gradeSheet->professor_id || $user->hasAnyRole(['career_director', 'department_head']));
    }

    public function close(User $user, GradeSheet $gradeSheet): bool
    {
        return $user->hasPermission('grades.close');
    }

    public function delete(User $user, GradeSheet $gradeSheet): bool
    {
        return $user->hasPermission('grades.manage') && $gradeSheet->status === 'draft';
    }
}

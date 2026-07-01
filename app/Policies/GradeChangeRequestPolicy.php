<?php

namespace App\Policies;

use App\Models\GradeChangeRequest;
use App\Models\User;

class GradeChangeRequestPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyPermission($user, ['grades.manage', 'grades.change.approve'])
            || ($user->hasPermission('grades.view') && ! $user->hasAnyRole(['student', 'professor']));
    }

    public function view(User $user, GradeChangeRequest $gradeChangeRequest): bool
    {
        return $this->viewAny($user)
            || $user->id === $gradeChangeRequest->requested_by_user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('grades.manage');
    }

    public function approve(User $user, GradeChangeRequest $gradeChangeRequest): bool
    {
        return $user->hasPermission('grades.change.approve');
    }

    public function reject(User $user, GradeChangeRequest $gradeChangeRequest): bool
    {
        return $this->approve($user, $gradeChangeRequest);
    }
}

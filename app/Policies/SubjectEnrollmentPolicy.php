<?php

namespace App\Policies;

use App\Models\SubjectEnrollment;
use App\Models\SubjectOffering;
use App\Models\User;

class SubjectEnrollmentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('subject_enrollments.manage')
            || ($user->hasPermission('subject_enrollments.view') && ! $user->hasAnyRole(['student', 'professor']));
    }

    public function view(User $user, SubjectEnrollment $subjectEnrollment): bool
    {
        $offeringProfessorId = $subjectEnrollment->subject_offering_id
            ? SubjectOffering::query()->whereKey($subjectEnrollment->subject_offering_id)->value('professor_id')
            : null;

        return $this->viewAny($user)
            || $user->student_id === $subjectEnrollment->student_id
            || ($user->professor_id && $user->professor_id === $offeringProfessorId);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('subject_enrollments.manage');
    }

    public function update(User $user, SubjectEnrollment $subjectEnrollment): bool
    {
        return $user->hasPermission('subject_enrollments.manage');
    }

    public function delete(User $user, SubjectEnrollment $subjectEnrollment): bool
    {
        return $user->hasPermission('subject_enrollments.manage');
    }
}

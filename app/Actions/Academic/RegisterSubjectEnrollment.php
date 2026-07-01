<?php

namespace App\Actions\Academic;

use App\Models\Enrollment;
use App\Models\SubjectEnrollment;
use App\Models\SubjectOffering;
use App\Models\SubjectPrerequisite;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterSubjectEnrollment
{
    public function handle(array $data): SubjectEnrollment
    {
        return DB::transaction(function () use ($data): SubjectEnrollment {
            $enrollment = Enrollment::with('student.group')->lockForUpdate()->findOrFail($data['enrollment_id']);
            $offering = SubjectOffering::with(['curriculumPlan.subjects', 'schedules'])
                ->lockForUpdate()
                ->findOrFail($data['subject_offering_id']);

            $this->validateOfferingContext($enrollment, $offering, $data);
            $this->validateCurriculumPlan($offering);
            $this->validateCapacity($offering);
            $this->validatePrerequisites($enrollment, $offering);
            $this->validateScheduleConflicts($enrollment, $offering);

            return SubjectEnrollment::create(array_merge($data, [
                'student_id' => $data['student_id'] ?? $enrollment->student_id,
                'subject_id' => $offering->subject_id,
                'course_id' => $offering->course_id,
                'career_id' => $offering->career_id,
                'group_id' => $offering->group_id ?? $enrollment->student?->group_id,
                'curriculum_plan_id' => $offering->curriculum_plan_id,
                'semester' => $offering->semester,
                'enrolled_at' => $data['enrolled_at'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'enrolled',
            ]));
        });
    }

    private function validateOfferingContext(Enrollment $enrollment, SubjectOffering $offering, array $data): void
    {
        if ($offering->status !== 'open') {
            throw ValidationException::withMessages(['subject_offering_id' => 'La oferta de asignatura no esta abierta para matricula.']);
        }

        if ($offering->course_id !== $enrollment->start_course_id) {
            throw ValidationException::withMessages(['subject_offering_id' => 'La oferta no pertenece al curso de la matricula.']);
        }

        if ($offering->career_id !== $enrollment->student?->group?->career_id) {
            throw ValidationException::withMessages(['subject_offering_id' => 'La oferta no pertenece a la carrera del estudiante.']);
        }

        if ($offering->group_id && $offering->group_id !== $enrollment->student?->group_id) {
            throw ValidationException::withMessages(['subject_offering_id' => 'La oferta no corresponde al grupo del estudiante.']);
        }

        if (($data['subject_id'] ?? $offering->subject_id) !== $offering->subject_id) {
            throw ValidationException::withMessages(['subject_id' => 'La asignatura solicitada no coincide con la oferta academica.']);
        }
    }

    private function validateCurriculumPlan(SubjectOffering $offering): void
    {
        $plan = $offering->curriculumPlan;

        if ($plan->status !== 'active' || ! $plan->is_current) {
            throw ValidationException::withMessages(['curriculum_plan_id' => 'El plan de estudio de la oferta no esta vigente.']);
        }

        $subjectInPlan = $plan->subjects()
            ->where('subjects.id', $offering->subject_id)
            ->wherePivot('semester', $offering->semester)
            ->exists();

        if (! $subjectInPlan) {
            throw ValidationException::withMessages(['subject_id' => 'La asignatura no pertenece al plan de estudio vigente para ese semestre.']);
        }
    }

    private function validateCapacity(SubjectOffering $offering): void
    {
        if ($offering->capacity <= 0) {
            return;
        }

        $occupied = $offering->subjectEnrollments()
            ->whereIn('status', ['enrolled', 'passed', 'failed'])
            ->count();

        if ($occupied >= $offering->capacity) {
            throw ValidationException::withMessages(['subject_offering_id' => 'La oferta no tiene cupos disponibles.']);
        }
    }

    private function validatePrerequisites(Enrollment $enrollment, SubjectOffering $offering): void
    {
        $prerequisites = SubjectPrerequisite::query()
            ->where('curriculum_plan_id', $offering->curriculum_plan_id)
            ->where('subject_id', $offering->subject_id)
            ->get();

        foreach ($prerequisites as $prerequisite) {
            $passed = SubjectEnrollment::query()
                ->where('student_id', $enrollment->student_id)
                ->where('subject_id', $prerequisite->prerequisite_subject_id)
                ->where('status', 'passed')
                ->whereHas('grades', fn ($query) => $query
                    ->where('status', 'published')
                    ->where('value', '>=', $prerequisite->minimum_grade))
                ->exists();

            if (! $passed) {
                throw ValidationException::withMessages([
                    'subject_offering_id' => 'El estudiante no cumple los prerrequisitos de la asignatura.',
                ]);
            }
        }
    }

    private function validateScheduleConflicts(Enrollment $enrollment, SubjectOffering $offering): void
    {
        $newSchedules = $offering->schedules;

        if ($newSchedules->isEmpty()) {
            return;
        }

        $currentOfferings = SubjectEnrollment::query()
            ->with('subjectOffering.schedules')
            ->where('student_id', $enrollment->student_id)
            ->where('course_id', $offering->course_id)
            ->whereIn('status', ['enrolled'])
            ->get()
            ->pluck('subjectOffering')
            ->filter();

        foreach ($currentOfferings as $currentOffering) {
            foreach ($currentOffering->schedules as $currentSchedule) {
                foreach ($newSchedules as $newSchedule) {
                    if (
                        $currentSchedule->weekday === $newSchedule->weekday
                        && $currentSchedule->starts_at < $newSchedule->ends_at
                        && $newSchedule->starts_at < $currentSchedule->ends_at
                    ) {
                        throw ValidationException::withMessages([
                            'subject_offering_id' => 'La oferta tiene choque de horario con otra asignatura matriculada.',
                        ]);
                    }
                }
            }
        }
    }
}

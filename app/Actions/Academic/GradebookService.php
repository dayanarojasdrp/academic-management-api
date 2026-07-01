<?php

namespace App\Actions\Academic;

use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\GradeSheet;
use App\Models\GradingScale;
use App\Models\SubjectEnrollment;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class GradebookService
{
    public function prepare(array $data, ?Grade $grade = null): array
    {
        if ($grade?->locked_at && empty($data['change_authorized_by_user_id'])) {
            throw ValidationException::withMessages([
                'locked_at' => 'La calificacion esta bloqueada. Debe existir una autorizacion aprobada para modificarla.',
            ]);
        }

        $subjectEnrollment = $this->subjectEnrollment($data, $grade);
        $gradeComponent = $this->gradeComponent($data, $grade);
        $gradeSheet = $this->gradeSheet($data, $grade);
        $gradingScale = $this->gradingScale($data, $gradeSheet, $grade);

        if ($gradeComponent && $subjectEnrollment->subject_offering_id !== $gradeComponent->subject_offering_id) {
            throw ValidationException::withMessages([
                'grade_component_id' => 'El componente de evaluacion no pertenece a la oferta academica matriculada por el estudiante.',
            ]);
        }

        if ($gradeSheet && $subjectEnrollment->subject_offering_id !== $gradeSheet->subject_offering_id) {
            throw ValidationException::withMessages([
                'grade_sheet_id' => 'El acta no pertenece a la oferta academica matriculada por el estudiante.',
            ]);
        }

        if ($gradeSheet && in_array($gradeSheet->status, ['closed', 'archived'], true) && ! $grade?->locked_at) {
            throw ValidationException::withMessages([
                'grade_sheet_id' => 'No se pueden registrar calificaciones nuevas en un acta cerrada.',
            ]);
        }

        $rawValue = Arr::get($data, 'raw_value', Arr::get($data, 'value', $grade?->raw_value ?? $grade?->value));
        $maxScore = (float) ($gradeComponent?->max_score ?? 100);
        $normalizedValue = $rawValue === null ? null : round(((float) $rawValue / max($maxScore, 1)) * (float) $gradingScale->max_value, (int) $gradingScale->decimal_places);
        $scaleLevel = $normalizedValue === null ? null : $gradingScale->levels()
            ->where('min_value', '<=', $normalizedValue)
            ->where('max_value', '>=', $normalizedValue)
            ->orderByDesc('min_value')
            ->first();

        return array_merge($data, [
            'subject_enrollment_id' => $subjectEnrollment->id,
            'student_id' => $subjectEnrollment->student_id,
            'subject_id' => $subjectEnrollment->subject_id,
            'professor_id' => $data['professor_id'] ?? $gradeSheet?->professor_id ?? $grade?->professor_id,
            'grade_sheet_id' => $gradeSheet?->id ?? $grade?->grade_sheet_id,
            'grade_component_id' => $gradeComponent?->id ?? $grade?->grade_component_id,
            'grading_scale_id' => $gradingScale->id,
            'grading_scale_level_id' => $scaleLevel?->id,
            'raw_value' => $rawValue,
            'normalized_value' => $normalizedValue,
            'value' => $normalizedValue,
            'weight' => $data['weight'] ?? $gradeComponent?->weight ?? $grade?->weight ?? 0,
            'evaluation_type' => $data['evaluation_type'] ?? $gradeComponent?->type ?? $gradeSheet?->sheet_type ?? $grade?->evaluation_type,
            'attempt_type' => $data['attempt_type'] ?? $gradeSheet?->sheet_type ?? $grade?->attempt_type ?? 'ordinary',
            'call_number' => $data['call_number'] ?? $gradeSheet?->call_number ?? $grade?->call_number ?? 1,
            'partial_number' => $data['partial_number'] ?? $gradeSheet?->partial_number ?? $grade?->partial_number,
            'is_final' => $data['is_final'] ?? ($gradeComponent ? $gradeComponent->type === 'final' : ($grade?->is_final ?? false)),
            'published_at' => ($data['status'] ?? $grade?->status) === 'published'
                ? ($data['published_at'] ?? $grade?->published_at ?? now())
                : ($data['published_at'] ?? $grade?->published_at),
        ]);
    }

    public function closeImpactedSubjectEnrollment(Grade $grade): void
    {
        if (! $grade->subjectEnrollment || $grade->status !== 'published' || ! $grade->is_final || $grade->value === null) {
            return;
        }

        $passingValue = (float) ($grade->gradingScale?->passing_value ?? 60);
        $grade->subjectEnrollment->update([
            'status' => (float) $grade->value >= $passingValue ? 'passed' : 'failed',
            'completed_at' => $grade->evaluated_at ?? now()->toDateString(),
        ]);
    }

    private function subjectEnrollment(array $data, ?Grade $grade): SubjectEnrollment
    {
        $id = $data['subject_enrollment_id'] ?? $grade?->subject_enrollment_id;

        if (! $id) {
            throw ValidationException::withMessages([
                'subject_enrollment_id' => 'La calificacion debe estar asociada a una matricula de asignatura.',
            ]);
        }

        return SubjectEnrollment::query()->findOrFail($id);
    }

    private function gradeComponent(array $data, ?Grade $grade): ?GradeComponent
    {
        $id = $data['grade_component_id'] ?? $grade?->grade_component_id;

        return $id ? GradeComponent::query()->findOrFail($id) : null;
    }

    private function gradeSheet(array $data, ?Grade $grade): ?GradeSheet
    {
        $id = $data['grade_sheet_id'] ?? $grade?->grade_sheet_id;

        return $id ? GradeSheet::query()->findOrFail($id) : null;
    }

    private function gradingScale(array $data, ?GradeSheet $gradeSheet, ?Grade $grade): GradingScale
    {
        $id = $data['grading_scale_id'] ?? $gradeSheet?->grading_scale_id ?? $grade?->grading_scale_id;

        if ($id) {
            return GradingScale::query()->with('levels')->findOrFail($id);
        }

        $scale = GradingScale::query()
            ->with('levels')
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        if (! $scale) {
            throw ValidationException::withMessages([
                'grading_scale_id' => 'Debe existir una escala de calificacion activa antes de registrar notas.',
            ]);
        }

        return $scale;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\SubjectOfferingSchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SubjectOfferingScheduleController extends ApiController
{
    protected string $modelClass = SubjectOfferingSchedule::class;

    protected array $relations = ['offering.subject', 'offering.group'];

    public function show(SubjectOfferingSchedule $subjectOfferingSchedule) { return $this->showRecord($subjectOfferingSchedule); }
    public function update(Request $request, SubjectOfferingSchedule $subjectOfferingSchedule) { return $this->updateRecord($request, $subjectOfferingSchedule); }
    public function destroy(SubjectOfferingSchedule $subjectOfferingSchedule) { return $this->destroyRecord($subjectOfferingSchedule); }

    protected function rules(?Model $record = null): array
    {
        return [
            'subject_offering_id' => ['required', 'exists:subject_offerings,id'],
            'weekday' => ['required', 'integer', 'min:1', 'max:7'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'classroom' => ['nullable', 'string', 'max:120'],
        ];
    }
}

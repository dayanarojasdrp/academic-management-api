# Gradebook and Official Grade Sheets

This module handles institutional grading, official grade sheets, signatures, academic closure and authorized grade changes.

## Main Concepts

- `grading_scales`: institutional scales, for example `0-100`, `0-5`, letter grades or pass/fail.
- `grading_scale_levels`: labels/ranges inside a scale, for example `A`, `B`, `C`, `D`, `F`.
- `grade_components`: evaluation cuts for a subject offering, for example first partial, second partial, final exam, recovery or extraordinary.
- `grade_sheets`: official grade sheets for one subject offering, professor, call and partial/final cut.
- `grades`: individual student grades. They belong to a subject enrollment and may belong to a grade component and grade sheet.
- `grade_change_requests`: controlled workflow to modify locked or closed grades.

## Recommended Flow

1. Configure the institutional scale.

```http
POST /api/grading-scales
```

```json
{
  "code": "LATAM-100",
  "name": "Escala institucional 0-100",
  "min_value": 0,
  "max_value": 100,
  "passing_value": 60,
  "decimal_places": 2,
  "is_default": true,
  "status": "active"
}
```

2. Configure scale levels.

```http
POST /api/grading-scale-levels
```

```json
{
  "grading_scale_id": 1,
  "code": "A",
  "label": "Excelente",
  "min_value": 90,
  "max_value": 100,
  "grade_points": 4,
  "is_passing": true,
  "sort_order": 1
}
```

3. Configure evaluation components for the subject offering.

```http
POST /api/grade-components
```

```json
{
  "subject_offering_id": 1,
  "code": "FINAL",
  "name": "Evaluacion final",
  "type": "final",
  "term": "final",
  "weight": 100,
  "max_score": 100,
  "is_required": true,
  "due_date": "2026-12-15",
  "status": "active",
  "sort_order": 1
}
```

4. Open a grade sheet.

```http
POST /api/grade-sheets
```

```json
{
  "subject_offering_id": 1,
  "grading_scale_id": 1,
  "sheet_type": "ordinary",
  "call_number": 1,
  "partial_number": null,
  "status": "draft",
  "opened_at": "2026-12-01"
}
```

The API derives `course_id`, `career_id`, `group_id`, `subject_id` and `professor_id` from the subject offering when they are omitted.

5. Register grades.

```http
POST /api/grades
```

```json
{
  "subject_enrollment_id": 1,
  "grade_sheet_id": 1,
  "grade_component_id": 1,
  "raw_value": 95,
  "attempt_type": "ordinary",
  "call_number": 1,
  "is_final": true,
  "evaluated_at": "2026-12-15",
  "status": "published",
  "notes": "Excelente desempeno"
}
```

The API derives `student_id`, `subject_id`, `professor_id`, normalized `value`, grading scale level and final pass/fail status.

6. Submit, sign and close the grade sheet.

```http
POST /api/grade-sheets/{gradeSheet}/submit
POST /api/grade-sheets/{gradeSheet}/sign
POST /api/grade-sheets/{gradeSheet}/close
```

`close` locks published grades. A locked grade cannot be edited directly.

7. Request an authorized grade change.

```http
POST /api/grade-change-requests
```

```json
{
  "grade_id": 1,
  "requested_value": 88,
  "reason": "Correccion aprobada por revision de examen final."
}
```

Approve or reject:

```http
POST /api/grade-change-requests/{gradeChangeRequest}/approve
POST /api/grade-change-requests/{gradeChangeRequest}/reject
```

Approve recalculates the grade with the same scale and records who authorized the change.

## Statuses

Suggested grade sheet statuses:

- `draft`: professor or academic office can edit.
- `submitted`: sent for official review.
- `signed`: signed by professor or academic authority.
- `closed`: official academic closure; published grades are locked.
- `reopened`: exceptional correction window.

Suggested grade statuses:

- `draft`: captured but not visible as official.
- `published`: visible and considered for academic history.
- `void`: cancelled record that remains auditable.

Suggested attempt types:

- `ordinary`
- `recovery`
- `extraordinary`
- `special`

## Frontend Notes

Use `/api/grade-components?subject_offering_id=1` to build the professor gradebook columns.
Use `/api/grade-sheets?subject_offering_id=1` to show official sheet workflow.
Use `/api/grades?grade_sheet_id=1` for sheet detail when a registrar reviews an acta.
Use `/api/grade-change-requests` for post-closure corrections instead of editing locked grades directly.

# Subject Enrollment Validation

Subject enrollment is not a raw insert. The backend validates the academic plan, active offering, capacity, prerequisites and schedule conflicts.

## Required setup

1. Career exists.
2. Course exists.
3. Group belongs to course and career.
4. Curriculum plan belongs to the career and is current.
5. Subject belongs to that curriculum plan and semester.
6. Subject offering is open for that course/group/career/plan.
7. Offering has capacity.
8. Student has enrollment for that course.

## Curriculum plan

```text
POST /api/curriculum-plans
```

Example:

```json
{
  "career_id": 1,
  "effective_course_id": 1,
  "name": "Plan Regular",
  "version": "2026",
  "duration_semesters": 10,
  "status": "active",
  "is_current": true,
  "subjects": [
    {
      "id": 1,
      "semester": 1,
      "is_required": true,
      "minimum_passing_grade": 60
    },
    {
      "id": 2,
      "semester": 2,
      "is_required": true,
      "prerequisite_subject_id": 1,
      "minimum_passing_grade": 60
    }
  ]
}
```

## Subject offering

The plan says what should be studied. The offering says what is actually opened in a course/group with professor, capacity and schedule.

```text
POST /api/subject-offerings
```

Example:

```json
{
  "course_id": 1,
  "career_id": 1,
  "institution_id": 1,
  "campus_id": 1,
  "faculty_id": 1,
  "department_id": 1,
  "modality_id": 1,
  "group_id": 1,
  "curriculum_plan_id": 1,
  "subject_id": 2,
  "professor_id": 1,
  "semester": 1,
  "capacity": 30,
  "reserved_seats": 0,
  "modality": "presencial",
  "status": "open",
  "starts_at": "2026-09-01",
  "ends_at": "2026-12-20"
}
```

Statuses:

```text
draft
open
closed
cancelled
completed
```

## Offering schedules

```text
POST /api/subject-offering-schedules
```

Example:

```json
{
  "subject_offering_id": 1,
  "weekday": 1,
  "starts_at": "08:00",
  "ends_at": "10:00",
  "classroom": "Lab 1"
}
```

Weekday convention:

```text
1 Monday
2 Tuesday
3 Wednesday
4 Thursday
5 Friday
6 Saturday
7 Sunday
```

## Enroll a student in a subject

```text
POST /api/subject-enrollments
```

Minimal body:

```json
{
  "enrollment_id": 1,
  "subject_offering_id": 1
}
```

The backend infers:

```text
student_id
subject_id
course_id
career_id
group_id
curriculum_plan_id
semester
enrolled_at
status
```

## Backend validations

The backend rejects enrollment when:

- The offering is not `open`.
- The offering course does not match the student's enrollment course.
- The offering career does not match the student's group career.
- The offering group does not match the student's group.
- The offering plan is not `active` and `is_current`.
- The subject is not in the current curriculum plan for that semester.
- The offering capacity is full.
- The student has not passed prerequisites.
- The offering schedule conflicts with another enrolled subject.

## Typical validation errors

```json
{
  "errors": {
    "subject_offering_id": [
      "La oferta tiene choque de horario con otra asignatura matriculada."
    ]
  }
}
```

```json
{
  "errors": {
    "subject_id": [
      "La asignatura no pertenece al plan de estudio vigente para ese semestre."
    ]
  }
}
```

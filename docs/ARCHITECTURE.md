# Architecture Notes

This API is designed as an academic management core, not only as CRUD screens over tables.

## Domain boundaries

The current domain is split into these bounded areas:

- Catalogs: careers, courses, subjects.
- Curriculum planning: curriculum plans and the subjects required by a career.
- Student administration: students, groups and enrollments.
- Finance gatekeeping: enrollment payments and payment validation.
- Teaching records: subject enrollments, professors and grades.
- Auditability: status histories for every entity that moves through operational states.

This keeps the code readable now and gives room to extract modules later if the system grows into admissions, treasury, LMS integrations, reports or analytics.

## Integration model

The data model intentionally stays close to common edtech concepts:

- People: students and professors.
- Courses/classes: courses, groups and subject enrollments.
- Enrollments: program enrollment and subject-level enrollment.
- Gradebook: grades linked to subject enrollments and professors.

That mirrors the kind of rostering and grade exchange described by 1EdTech OneRoster, while keeping names familiar for a Latin American higher education institution.

## API contract

Controllers should not expose raw Eloquent models forever. Public responses should move through API Resources so the frontend gets stable payloads even when table columns change.

Current resources:

- `StudentResource`
- `EnrollmentResource`
- `FinanceResource`
- `SubjectEnrollmentResource`

Add one resource per public aggregate as the frontend starts consuming it heavily.

## Query strategy

The API supports regular pagination and cursor pagination:

```text
GET /api/students?cursor=1&per_page=50
```

Use cursor pagination for large operational screens that scroll forward through stable ordering. Use regular pagination for small administrative catalogs.

Recommended high-traffic queries:

- Students by group: `group_id + status`.
- Payments by student/concept/status: `student_id + concept + status`.
- Subject enrollments by career/course/status.
- Subject enrollments by group/course/status.
- Grades by subject enrollment/status.
- Status histories by trackable entity and date.

Those query paths have explicit compound indexes in migrations.

## Transactional flows

Enrollment is a business transaction:

1. Lock the student row.
2. Verify a paid enrollment finance record.
3. Create the enrollment.
4. Attach it as the student's current enrollment.

This flow lives in `App\Actions\Academic\EnrollStudent`.

Subject enrollment is also a business transaction:

1. Lock the enrollment row.
2. Derive student, course, career and group from the enrollment context.
3. Create the subject enrollment.

This lives in `App\Actions\Academic\RegisterSubjectEnrollment`.

Payment confirmation lives in `App\Actions\Academic\PaymentVerifier` so a future payment gateway can replace the manual validation route without rewriting enrollment rules.

## Performance rules

- Avoid lazy loading during development to catch accidental N+1 queries early.
- Use eager loading with selected relationships on list endpoints.
- Keep list endpoints paginated.
- Prefer compound indexes that match real screens over many disconnected single-column indexes.
- Treat `status_histories` as an audit/event stream and query it by `trackable_type`, `trackable_id` and `changed_at`.

## Next scaling steps

- Add authentication with token abilities per module.
- Add Form Request classes per command instead of inline validation.
- Add policies for academic secretary, finance, professor and admin roles.
- Add API Resources for every public aggregate.
- Add read-optimized report endpoints instead of letting the frontend compose heavy joins.
- Add PostgreSQL in production and inspect hot queries with `EXPLAIN`.
- Add queue jobs for external integrations, notifications and report generation.

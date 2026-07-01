# Frontend Integration Contract

This document translates the frontend expectations into the current Laravel API contract.

## Naming Map

The frontend proposal uses some generic SIS names. The backend already has equivalent domain names:

| Frontend name | Backend name | Notes |
| --- | --- | --- |
| `programs` | `careers` | Alias routes `/api/programs` were added. |
| `academic_periods` | `courses` | Alias routes `/api/academic-periods` were added. |
| `teachers` | `professors` | Alias routes `/api/teachers` were added. |
| `course_groups` | `subject_offerings` | Alias routes `/api/course-groups` were added. |
| `payments` | `student_payments` | Alias routes `/api/payments` were added. |
| `enrollment_courses` | `subject_enrollments` | Use `/api/subject-enrollments`. |
| `transcript` | `kardex` | Alias `/api/students/{student}/transcript` was added. |

Frontend should prefer the backend canonical names when possible, but aliases exist to reduce integration friction.

## Authentication

Use Laravel Sanctum token auth.

- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`
- `GET /api/auth/roles`
- `GET /api/auth/permissions`

Every protected request must send:

```http
Authorization: Bearer {token}
Accept: application/json
```

## Students

Main routes:

- `GET /api/students`
- `POST /api/students`
- `GET /api/students/{student}`
- `PUT/PATCH /api/students/{student}`
- `DELETE /api/students/{student}`
- `GET /api/students/check-duplicate?document_number=...&email=...`
- `GET /api/students/{student}/academic-summary`
- `GET /api/students/{student}/academic-history`
- `GET /api/students/{student}/kardex`
- `GET /api/students/{student}/transcript`
- `GET /api/students/{student}/gpa`
- `GET /api/students/{student}/grades`
- `GET /api/students/{student}/attendance-summary`
- `GET /api/students/{student}/payment-status`
- `GET /api/students/{student}/financial-clearance`
- `GET /api/students/{student}/certificates`

Minimum create payload:

```json
{
  "student_code": "STU-2026-0001",
  "first_name": "Maria",
  "last_name": "Perez",
  "document_type": "carnet",
  "document_number": "99010112345",
  "email": "maria@example.edu",
  "group_id": 1,
  "admission_date": "2026-09-01",
  "status": "active"
}
```

Backend guarantees:

- unique `student_code`;
- unique `document_number`;
- duplicate check by document/email;
- academic history and GPA are calculated in backend;
- financial clearance is calculated in backend.

## Admissions

Use admissions for aspirants before they become students.

- `GET|POST /api/applicants`
- `GET|PUT|PATCH|DELETE /api/applicants/{applicant}`
- `POST /api/applicants/{applicant}/submit`
- `POST /api/applicants/{applicant}/convert-to-student`
- `GET|POST|PUT|DELETE /api/application-documents`
- `GET|POST|PUT|DELETE /api/admission-interviews`
- `GET|POST|PUT|DELETE /api/admission-decisions`

Applicant conversion requires an approved admission decision.

```json
{
  "student_code": "STU-2026-0002",
  "group_id": 1,
  "admission_date": "2026-09-01"
}
```

The backend prevents conversion if a student already exists with the applicant document.

## Enrollment

Main routes:

- `GET|POST /api/enrollments`
- `GET|PUT|PATCH|DELETE /api/enrollments/{enrollment}`
- `POST /api/enrollments/{enrollment}/submit`
- `POST /api/enrollments/{enrollment}/cancel`
- `POST /api/enrollments/{enrollment}/confirm-payment`
- `POST /api/enrollments/{enrollment}/activate`

Minimum create payload:

```json
{
  "student_id": 1,
  "start_course_id": 1,
  "enrollment_date": "2026-09-01",
  "status": "active"
}
```

Backend guarantees:

- enrollment is blocked when required debt exists;
- successful activation updates the student's current enrollment;
- payment validation can activate the enrollment;
- subject enrollment validates plan, capacity and schedule conflicts.

## Subject Enrollment And Course Groups

Canonical routes:

- `GET|POST /api/subject-offerings`
- `GET|PUT|PATCH|DELETE /api/subject-offerings/{subjectOffering}`
- `GET /api/subject-offerings/{subjectOffering}/students`
- `GET|POST /api/subject-enrollments`
- `GET|PUT|PATCH|DELETE /api/subject-enrollments/{subjectEnrollment}`

Frontend-friendly aliases:

- `GET|POST /api/course-groups`
- `GET|PUT|PATCH|DELETE /api/course-groups/{subjectOffering}`
- `GET /api/course-groups/{subjectOffering}/students`

Students appear in offering lists only when they have an active/enrolled academic enrollment and a valid subject enrollment.

## Payments And Finance

Canonical routes:

- `GET|POST /api/student-charges`
- `GET|PUT|PATCH|DELETE /api/student-charges/{studentCharge}`
- `POST /api/student-charges/{studentCharge}/adjustments`
- `GET|POST /api/student-payments`
- `GET|PUT|PATCH|DELETE /api/student-payments/{studentPayment}`
- `POST /api/student-payments/{studentPayment}/validate`
- `POST /api/student-payments/{studentPayment}/reject`

Frontend-friendly aliases:

- `GET|POST /api/payments`
- `GET|PUT|PATCH|DELETE /api/payments/{studentPayment}`
- `POST /api/payments/{studentPayment}/validate`
- `POST /api/payments/{studentPayment}/reject`

Payment create payload:

```json
{
  "student_id": 1,
  "enrollment_id": 1,
  "amount": 250,
  "currency": "USD",
  "payment_method": "manual",
  "payment_reference": "PAY-2026-0001",
  "paid_at": "2026-08-25"
}
```

Reject payload:

```json
{
  "reason": "Referencia bancaria no encontrada"
}
```

Backend guarantees:

- payment references are unique;
- validated payments become `confirmed`;
- rejected payments store the rejection reason;
- confirmed payment can activate enrollment and student status.

## Grades And Academic Record

Routes:

- `GET|POST /api/grades`
- `GET|PUT|PATCH|DELETE /api/grades/{grade}`
- `GET /api/grades/{grade}/audit-logs`
- `GET /api/students/{student}/transcript`
- `GET /api/students/{student}/gpa`
- `GET /api/students/{student}/kardex`

Grade update must include `change_reason` when changing `value`, `raw_value` or `status`:

```json
{
  "raw_value": 92,
  "status": "published",
  "change_reason": "Correction after teacher review"
}
```

Backend guarantees:

- grade updates without reason are rejected;
- grade changes create `grade_audit_logs`;
- closed/locked grade sheets block normal grade changes;
- final published grades update subject enrollment outcome.

## Certificates

Routes:

- `GET /api/certificates`
- `GET /api/certificates/{certificate}`
- `POST /api/certificates/generate`
- `GET /api/students/{student}/certificates`
- `GET /api/certificates/{certificate}/download?format=pdf`
- `GET /api/certificates/{certificate}/download?format=csv`
- `GET /api/certificates/verify/{verificationCode}`

Generate payload:

```json
{
  "student_id": 1,
  "type": "grade_certificate",
  "course_id": 1,
  "purpose": "scholarship_application"
}
```

Backend guarantees:

- certificate uses real student/enrollment/grade data;
- certificate stores `snapshot_data`;
- verification code is unique;
- PDF and CSV downloads are generated server-side.

## Attendance

Routes:

- `GET|POST /api/class-sessions`
- `GET|PUT|PATCH|DELETE /api/class-sessions/{classSession}`
- `POST /api/class-sessions/{classSession}/generate-attendance`
- `GET|POST /api/attendance-records`
- `GET|PUT|PATCH|DELETE /api/attendance-records/{attendanceRecord}`
- `GET /api/students/{student}/attendance-summary`

Record payload:

```json
{
  "class_session_id": 1,
  "student_id": 1,
  "status": "present",
  "minutes_late": 0,
  "justified": false
}
```

Recommended statuses: `present`, `absent`, `late`, `excused`, `remote`.

## Dashboard

Use:

- `GET /api/dashboard/metrics`

Filters:

- `faculty_id`
- `career_id`
- `program_id` should be sent as `career_id`
- `course_id`
- `academic_period_id` should be sent as `course_id`
- `group_id`

Response includes:

- `kpis`
- `research_metrics`
- `charts.enrollments_by_period`
- `charts.pending_processes_by_program`
- `tables.program_indicators`
- `tables.operational_processes`
- `tables.recent_certificates`

The frontend should not calculate dashboard totals manually.

## Catalogs For Selects

Canonical:

- `GET /api/institutions`
- `GET /api/campuses`
- `GET /api/faculties`
- `GET /api/departments`
- `GET /api/modalities`
- `GET /api/careers`
- `GET /api/courses`
- `GET /api/subjects`
- `GET /api/professors`
- `GET /api/groups`
- `GET /api/auth/roles`

Aliases:

- `GET /api/programs`
- `GET /api/academic-periods`
- `GET /api/teachers`

## Status Values

Backend currently stores lowercase operational statuses. Frontend can display friendly labels.

Suggested frontend labels:

| Backend | UI label |
| --- | --- |
| `draft` | Draft |
| `pending_payment` | Pending Payment |
| `payment_confirmed` | Payment Confirmed |
| `active` / `enrolled` | Enrolled / Active |
| `cancelled` | Cancelled |
| `confirmed` | Validated |
| `rejected` | Rejected |
| `published` | Completed |
| `passed` | Passed |
| `failed` | Failed |
| `generated` | Generated |

Do not hard-delete important institutional records from the UI unless the user has administrative permissions. Prefer status transitions.

## What Frontend Must Not Own

Frontend should not implement these business rules:

- duplicate detection;
- payment clearance;
- enrollment activation;
- subject capacity validation;
- schedule conflict validation;
- curriculum plan validation;
- GPA/transcript calculation;
- grade audit enforcement;
- certificate content generation;
- dashboard aggregations.

All of those are backend responsibilities and now have backend endpoints or services.

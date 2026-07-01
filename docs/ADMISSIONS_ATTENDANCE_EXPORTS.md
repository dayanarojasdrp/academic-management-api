# Admissions, Attendance And Official Exports

This module closes three institutional gaps that matter in Latin American academic management platforms: admissions, attendance control and downloadable official reports.

## Admissions

Admissions tracks the applicant before they become a student.

Main routes:

- `GET /api/applicants`
- `POST /api/applicants`
- `GET /api/applicants/{applicant}`
- `PUT/PATCH /api/applicants/{applicant}`
- `DELETE /api/applicants/{applicant}`
- `POST /api/applicants/{applicant}/submit`
- `POST /api/applicants/{applicant}/convert-to-student`
- `GET|POST|PUT|DELETE /api/application-documents`
- `GET|POST|PUT|DELETE /api/admission-interviews`
- `GET|POST|PUT|DELETE /api/admission-decisions`

Applicant minimum payload:

```json
{
  "applicant_code": "ASP-2026-0002",
  "first_name": "Maria",
  "last_name": "Gomez",
  "document_type": "carnet",
  "document_number": "99010112345",
  "career_id": 1,
  "course_id": 1,
  "group_id": 1,
  "application_date": "2026-06-30",
  "status": "draft"
}
```

Conversion to student requires an approved admission decision and receives:

```json
{
  "student_code": "EST-0002",
  "group_id": 1,
  "admission_date": "2026-09-01"
}
```

## Attendance

Attendance is modeled as a class session plus one attendance record per student. This supports professor workflows, student attendance history and retention reports.

Main routes:

- `GET /api/class-sessions`
- `POST /api/class-sessions`
- `GET /api/class-sessions/{classSession}`
- `PUT/PATCH /api/class-sessions/{classSession}`
- `DELETE /api/class-sessions/{classSession}`
- `POST /api/class-sessions/{classSession}/generate-attendance`
- `GET|POST|PUT|DELETE /api/attendance-records`
- `GET /api/students/{student}/attendance-summary`

Class session minimum payload:

```json
{
  "subject_offering_id": 1,
  "session_date": "2026-09-07",
  "starts_at": "08:00",
  "ends_at": "10:00",
  "topic": "Introduccion a algoritmos",
  "status": "completed"
}
```

Attendance record payload:

```json
{
  "class_session_id": 1,
  "student_id": 1,
  "subject_enrollment_id": 1,
  "status": "present",
  "minutes_late": 0,
  "justified": false
}
```

Recommended statuses are `present`, `absent`, `late`, `excused` and `remote`.

## Official Exports

The API now supports downloadable official documents without requiring external packages:

- `GET /api/reports/students/{student}/certificate/export?format=pdf`
- `GET /api/reports/students/{student}/certificate/export?format=csv`
- `GET /api/reports/students/{student}/kardex/export?format=pdf`
- `GET /api/reports/students/{student}/kardex/export?format=csv`
- `GET /api/reports/grade-sheets/{gradeSheet}/export?format=pdf`
- `GET /api/reports/grade-sheets/{gradeSheet}/export?format=csv`
- `GET /api/reports/delinquency/export?format=pdf`
- `GET /api/reports/delinquency/export?format=csv`

CSV is compatible with Excel. PDF is generated server-side as a lightweight official text document, useful for constancias, kardex, actas and delinquency summaries.

## Verification

The local verification uses:

```bash
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=/tmp/academic-management-final.sqlite php artisan migrate:fresh --seed --force
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=/tmp/academic-management-final.sqlite php artisan route:list --path=api
```

The seeded baseline includes one applicant, two applicant documents, one interview, one admission decision, one class session and one attendance record.

# Demo Data Guide

The database seeder is idempotent: you can run it more than once and it will reset and rebuild the demo dataset.

## Load Demo Data

MySQL:

```bash
php artisan config:clear
php artisan migrate:fresh --seed --force
```

Or, if migrations are already applied and you only want to reload demo data:

```bash
php artisan db:seed --class=DatabaseSeeder --no-interaction
```

## Demo Users

All demo users use the password:

```text
password
```

Users:

- `admin@example.edu` - Super administrador.
- `secretaria@example.edu` - Secretaria academica.
- `finanzas@example.edu` - Director financiero.
- `profesor@example.edu` - Profesor demo.
- `estudiante@example.edu` - Estudiante demo.

Login:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.edu","password":"password"}'
```

Use the returned token:

```http
Authorization: Bearer {token}
Accept: application/json
```

## Seeded Coverage

The demo database includes:

- 1 institution.
- 1 campus.
- 1 faculty.
- 2 departments.
- 2 modalities.
- 2 careers/programs.
- 3 academic periods/courses.
- 7 subjects.
- 2 curriculum plans.
- 3 groups.
- 4 professors.
- 7 students.
- 7 enrollments.
- paid, partial and overdue financial cases.
- 6 student payments.
- 6 receipts.
- 6 subject offerings.
- 7 subject enrollments.
- 6 grade sheets.
- 7 grades.
- 7 grade audit logs.
- 7 class sessions.
- 7 attendance records.
- 4 applicants.
- 5 applicant documents.
- 4 admission interviews.
- 3 admission decisions.
- 7 certificates.
- 17 roles.
- 34 permissions.

## Suggested Manual Test Flow

### 1. Health And Auth

```bash
curl http://127.0.0.1:8000/api/health
curl http://127.0.0.1:8000/api/health/deep
```

Then login as admin and call:

```bash
curl http://127.0.0.1:8000/api/auth/me \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### 2. Catalogs

```bash
curl http://127.0.0.1:8000/api/careers -H "Authorization: Bearer {token}" -H "Accept: application/json"
curl http://127.0.0.1:8000/api/programs -H "Authorization: Bearer {token}" -H "Accept: application/json"
curl http://127.0.0.1:8000/api/academic-periods -H "Authorization: Bearer {token}" -H "Accept: application/json"
curl http://127.0.0.1:8000/api/teachers -H "Authorization: Bearer {token}" -H "Accept: application/json"
```

### 3. Students

```bash
curl "http://127.0.0.1:8000/api/students?search=EST-0003" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Duplicate check:

```bash
curl "http://127.0.0.1:8000/api/students/check-duplicate?document_number=00010312345" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### 4. Finance And Enrollment

Financial clearance:

```bash
curl http://127.0.0.1:8000/api/students/3/financial-clearance \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Delinquency report:

```bash
curl http://127.0.0.1:8000/api/reports/delinquency \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

CSV export:

```bash
curl -L "http://127.0.0.1:8000/api/reports/delinquency/export?format=csv" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: text/csv"
```

### 5. Subject Offerings

```bash
curl http://127.0.0.1:8000/api/course-groups \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Students in a course group:

```bash
curl http://127.0.0.1:8000/api/course-groups/1/students \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### 6. Grades And Audit

Student transcript:

```bash
curl http://127.0.0.1:8000/api/students/1/transcript \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Grade audit:

```bash
curl http://127.0.0.1:8000/api/grades/1/audit-logs \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Try updating a grade without `change_reason`; backend should reject it with `422`.

### 7. Attendance

```bash
curl http://127.0.0.1:8000/api/class-sessions \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Student attendance summary:

```bash
curl http://127.0.0.1:8000/api/students/1/attendance-summary \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### 8. Admissions

```bash
curl http://127.0.0.1:8000/api/applicants \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

The seed includes approved, submitted, waitlisted and rejected applicant cases.

### 9. Certificates

List certificates:

```bash
curl http://127.0.0.1:8000/api/certificates \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Download PDF:

```bash
curl -L "http://127.0.0.1:8000/api/certificates/1/download?format=pdf" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/pdf" \
  -o certificate.pdf
```

Public verification:

```bash
curl http://127.0.0.1:8000/api/certificates/verify/CERTVERIFYDEMO000000000001 \
  -H "Accept: application/json"
```

### 10. Dashboard

```bash
curl http://127.0.0.1:8000/api/dashboard/metrics \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

## Smoke Test

Run:

```bash
composer run test:smoke
```

This validates migrations, seed data, routes, model loading and PDF generation using a temporary SQLite database.

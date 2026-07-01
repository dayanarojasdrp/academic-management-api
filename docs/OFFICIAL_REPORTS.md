# Official Reports

The reporting layer returns verified JSON payloads ready for frontend tables, Excel export or PDF rendering.

The design follows three practical patterns seen in academic platforms:

- academic/financial modules must be reported together;
- rostering, enrollments, classes and results are separate report dimensions;
- official documents such as actas, certificates and kardex should reuse the same source data used by operational screens.

References used for the reporting shape:

- [Q10](https://es.wikipedia.org/wiki/Software_acad%C3%A9mico_Q10) is a Latin American SaaS academic platform that integrates academic management, finance, virtual education and academic reports.
- [1EdTech OneRoster](https://www.imsglobal.org/oneroster-v11-final-specification) separates rostering data, classes, enrollments, line items and results.
- [Ellucian SIS](https://www.ellucian.com/products/student/student-information-systems) emphasizes student information, academic records, retention and graduation workflows.

## Common Filters

Most reports accept:

```text
institution_id=1
campus_id=1
faculty_id=1
department_id=1
modality_id=1
course_id=1
career_id=1
group_id=1
from=2026-09-01
to=2027-07-31
per_page=50
```

## Academic Reports

### Enrollment By Period

```http
GET /api/reports/enrollment-by-period
```

Use for institutional enrollment dashboards and official enrollment cuts by course, career, group and status.

Returns grouped totals:

```json
{
  "report": "enrollment_by_period",
  "generated_at": "2026-07-01T00:00:00.000000Z",
  "filters": {
    "course_id": "1"
  },
  "data": [
    {
      "course_id": 1,
      "course_name": "Curso 2026-2027",
      "career_id": 1,
      "career_name": "Ingenieria Informatica",
      "group_id": 1,
      "group_name": "INF-1A",
      "status": "active",
      "total": 35
    }
  ]
}
```

### Grades By Group

```http
GET /api/reports/grades-by-group
```

Use for professor, secretaria academica and coordinator grade review by group, subject and student.

Extra filters:

```text
subject_id=1
status=passed
```

### Grade Sheets / Actas

```http
GET /api/reports/grade-sheets
```

Use for official acta tracking: draft, submitted, signed, closed, averages and pass/fail totals.

Extra filters:

```text
status=closed
professor_id=1
```

### Certificate Payload

```http
GET /api/reports/students/{student}/certificate
```

Returns student identity, current academic summary and issuance metadata. The frontend can render it as a certificate PDF.

Optional:

```text
purpose=constancia_estudios
```

### Kardex

```http
GET /api/reports/students/{student}/kardex
```

Uses the same paginated kardex logic as academic history, with official closed subjects and final grades.

### Graduates

```http
GET /api/reports/graduates
```

Uses `students.status = graduated` or `exit_reason = graduation`.

### Withdrawals / Bajas

```http
GET /api/reports/withdrawals
```

Uses students with `exit_date` or status `withdrawn`, `inactive`, `dropped`.

### Retention

```http
GET /api/reports/retention
```

Returns cohort totals, active totals, withdrawals and calculated rates by course and career.

### Faculty Performance

```http
GET /api/reports/faculty-performance
```

Returns professor workload and outcomes:

- assigned subject offerings;
- grade sheets;
- closed grade sheets;
- grades captured;
- published average;
- passed/failed totals.

## Finance Reports

### Delinquency / Morosidad

```http
GET /api/reports/delinquency
```

Use for treasury/finance. Returns overdue charges with student, group, career, concept, currency and balance.

Extra filters:

```text
as_of=2026-10-01
```

## Frontend Notes

Use these report endpoints for official exports instead of reconstructing reports from CRUD endpoints.

For PDF/Excel export, the recommended flow is:

1. Frontend requests the report JSON with filters.
2. Frontend shows preview and selected columns.
3. Export service renders the same payload to PDF/XLSX.
4. Backend later can add signed export records without changing report semantics.

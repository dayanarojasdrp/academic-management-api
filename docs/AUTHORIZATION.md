# Authentication and Authorization

The API uses token authentication with Laravel Sanctum and database-managed role based access control.

## Authentication flow

1. Login with email and password.
2. Store the returned Bearer token in the frontend session storage strategy.
3. Send the token on every protected request.
4. Logout to revoke the current token.

Header:

```text
Authorization: Bearer {access_token}
Accept: application/json
```

## Public routes

```text
GET  /api/health
POST /api/auth/login
```

## Auth routes

```text
POST /api/auth/login
GET  /api/auth/me
POST /api/auth/logout
```

Login body:

```json
{
  "email": "admin@example.edu",
  "password": "password",
  "device_name": "frontend-web"
}
```

Login response:

```json
{
  "token_type": "Bearer",
  "access_token": "plain-text-token",
  "user": {
    "id": 1,
    "name": "Sistema Admin",
    "email": "admin@example.edu",
    "status": "active",
    "student_id": null,
    "professor_id": null,
    "roles": ["super_admin"],
    "permissions": ["users.manage", "students.view"]
  }
}
```

## User management routes

Require `users.manage`.

```text
GET    /api/auth/users
POST   /api/auth/users
GET    /api/auth/users/{user}
PATCH  /api/auth/users/{user}
PUT    /api/auth/users/{user}
DELETE /api/auth/users/{user}
```

Create user:

```json
{
  "name": "Secretaria Academica",
  "email": "secretaria@example.edu",
  "password": "password",
  "status": "active",
  "student_id": null,
  "professor_id": null,
  "roles": ["academic_secretary"]
}
```

Update user roles:

```json
{
  "roles": ["academic_secretary", "reports_analyst"]
}
```

Delete user does not remove the record. It sets:

```text
status = inactive
```

and revokes all tokens.

## Role and permission catalog

```text
GET /api/auth/roles
GET /api/auth/permissions
```

Require `roles.view` or `users.manage`.

## System roles

```text
super_admin
rector
institution_admin
academic_secretary
registrar
admissions_officer
finance_manager
cashier
academic_coordinator
career_director
department_head
professor
student
auditor
support
reports_analyst
lms_coordinator
```

## Role meaning

- `super_admin`: unrestricted platform owner.
- `rector`: executive visibility, academic/finance reports and audit.
- `institution_admin`: institutional configuration, users, catalogs and academic structure.
- `academic_secretary`: student records, enrollments, academic history and operational reports.
- `registrar`: official academic records, enrollments, grades view and audit.
- `admissions_officer`: admissions and initial student creation.
- `finance_manager`: finance administration, payment validation, financial reports.
- `cashier`: payment validation and student account lookup.
- `academic_coordinator`: curriculum, groups, subject enrollments and academic reports.
- `career_director`: program-level curriculum, professors, students, grades and reports.
- `department_head`: professor management and department academic reporting.
- `professor`: assigned academic work, subject enrollments and grade management.
- `student`: own academic history, grades, enrollments and finances.
- `auditor`: read-only institutional audit access.
- `support`: technical support without academic write access.
- `reports_analyst`: cross-module reporting.
- `lms_coordinator`: virtual learning / LMS coordination.

## Permission model

Permissions are stored as codes:

```text
users.manage
roles.view
catalogs.view
catalogs.manage
curriculum.view
curriculum.manage
groups.view
groups.manage
students.view
students.manage
admissions.manage
enrollments.view
enrollments.create
enrollments.manage
subject_enrollments.view
subject_enrollments.manage
professors.view
professors.manage
grades.view
grades.manage
academic_history.view
finances.view
finances.manage
finances.payments.validate
reports.academic.view
reports.finance.view
audit.view
support.impersonate
```

The frontend should not hardcode what each user can do based only on role name. It should use the `permissions` array returned by `/api/auth/login` or `/api/auth/me`.

## Demo users

Seeded users use password:

```text
password
```

Accounts:

```text
admin@example.edu       super_admin
secretaria@example.edu  academic_secretary
finanzas@example.edu    finance_manager
profesor@example.edu    professor
estudiante@example.edu  student
```

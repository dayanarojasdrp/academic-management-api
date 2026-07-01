# Form Requests and Policies

The API now separates input validation and contextual authorization from controllers in the most sensitive academic flows.

## Layers

1. Route middleware checks broad permissions, for example `students.manage` or `grades.manage`.
2. Form Requests validate request payloads before controller logic runs.
3. Policies enforce contextual access, for example:
   - a student can see their own academic history, not another student's;
   - a professor can work with assigned grade sheets;
   - locked grades require explicit change authorization;
   - closed grade sheets cannot be edited through normal update flows.

This keeps controllers focused on orchestration and leaves security rules in testable classes.

## Current Form Requests

Students:

- `StoreStudentRequest`
- `UpdateStudentRequest`

Enrollments:

- `StoreEnrollmentRequest`
- `UpdateEnrollmentRequest`

Subject enrollments:

- `StoreSubjectEnrollmentRequest`
- `UpdateSubjectEnrollmentRequest`

Grades:

- `StoreGradeRequest`
- `UpdateGradeRequest`
- `StoreGradeChangeRequestRequest`

Grade sheets:

- `StoreGradeSheetRequest`
- `UpdateGradeSheetRequest`

## Current Policies

- `StudentPolicy`
- `EnrollmentPolicy`
- `SubjectEnrollmentPolicy`
- `GradePolicy`
- `GradeSheetPolicy`
- `GradeChangeRequestPolicy`

All policies inherit from `BasePolicy`:

- inactive users are denied;
- `super_admin` can perform every action;
- regular users are checked by permission and contextual ownership.

## Frontend Impact

The frontend should still use the `permissions` array from `/api/auth/login` and `/api/auth/me` to show or hide actions.

The backend may still reject a visible action with `403` when the user lacks contextual access. Examples:

- a student tries to open another student's `/academic-history`;
- a professor tries to sign an acta assigned to another professor;
- a user tries to edit a locked grade without an approved correction flow.

Frontend behavior should treat `403` as "not allowed in this context", not as a generic crash.

## Backend Growth Rule

New write-heavy or sensitive endpoints should add a Form Request before reaching controller logic.

New entities with ownership or role-dependent access should add a Policy and register it in `AppServiceProvider`.

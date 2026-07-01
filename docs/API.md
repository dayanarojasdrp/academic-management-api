# Guia de API para frontend

Base local:

```text
http://127.0.0.1:8000/api
```

Todas las rutas devuelven JSON. Los listados CRUD usan paginacion de Laravel.

Excepto `/api/health` y `/api/auth/login`, las rutas requieren:

```text
Authorization: Bearer {access_token}
```

La guia de roles, permisos y usuarios esta en [AUTHORIZATION.md](AUTHORIZATION.md).
La guia financiera de cargos, pagos, becas, descuentos y solvencia esta en [FINANCE.md](FINANCE.md).
La validacion de matricula de asignaturas esta en [SUBJECT_ENROLLMENT.md](SUBJECT_ENROLLMENT.md).

## Regla principal de matricula

Un estudiante no se puede matricular si antes no tiene un pago de matricula validado.

Flujo recomendado:

1. Crear estudiante: `POST /api/students`.
2. Crear pago pendiente: `POST /api/finances` con `concept = "enrollment"` y `status = "pending"`.
3. Simular/validar pago: `PATCH /api/finances/{finance}/mark-paid`.
4. Consultar si ya puede matricularse: `GET /api/students/{student}/payment-status`.
5. Crear matricula: `POST /api/enrollments`.
6. Matricular asignaturas: `POST /api/subject-enrollments`.
7. Publicar calificacion: `POST /api/grades` o `PATCH /api/grades/{grade}`.

Si el pago no esta `paid`, `POST /api/enrollments` responde `422`.

## CRUD comun

Cada recurso principal tiene:

```text
GET    /api/{resource}
POST   /api/{resource}
GET    /api/{resource}/{id}
PUT    /api/{resource}/{id}
PATCH  /api/{resource}/{id}
DELETE /api/{resource}/{id}
```

Recursos:

```text
careers
courses
subjects
curriculum-plans
groups
students
enrollments
finances
professors
subject-enrollments
grades
```

## Carreras

Ruta CRUD: `/api/careers`

Campos:

```json
{
  "name": "Ingenieria Informatica",
  "abbreviation": "INF",
  "description": "Opcional"
}
```

Rutas especiales:

```text
GET /api/careers/{career}/subjects
GET /api/careers/{career}/subject-enrollments
```

Uso:

- `subjects`: devuelve las asignaturas que debe cursar una carrera segun sus planes de estudio.
- `subject-enrollments`: corte de asignaturas matriculadas por carrera, con estudiante, curso, grupo y notas.

## Cursos

Ruta CRUD: `/api/courses`

Campos:

```json
{
  "name": "Curso 2026-2027",
  "start_date": "2026-09-01",
  "end_date": "2027-07-31",
  "status": "active"
}
```

Ruta especial:

```text
GET /api/courses/{course}/subject-enrollments
```

Sirve para cortes por curso: que estudiantes cursan que asignaturas, en que carrera y grupo.

## Asignaturas

Ruta CRUD: `/api/subjects`

Campos:

```json
{
  "code": "PRG-101",
  "name": "Programacion I",
  "credits": 5,
  "weekly_hours": 8
}
```

Las asignaturas se usan en planes de estudio, profesores, matriculas de asignatura y calificaciones.

## Planes de estudio

Ruta CRUD: `/api/curriculum-plans`

Campos:

```json
{
  "career_id": 1,
  "name": "Plan Regular",
  "version": "2026",
  "duration_semesters": 10,
  "status": "active",
  "subjects": [
    { "id": 1, "semester": 1, "is_required": true },
    { "id": 2, "semester": 1, "is_required": true }
  ]
}
```

Para cambiar las asignaturas de una carrera, el frontend actualiza el plan con `PUT/PATCH /api/curriculum-plans/{id}` enviando el arreglo `subjects`. Ese arreglo reemplaza la relacion actual del plan.

## Grupos

Ruta CRUD: `/api/groups`

Campos:

```json
{
  "course_id": 1,
  "career_id": 1,
  "name": "INF-1A",
  "shift": "diurno",
  "status": "active"
}
```

Ruta especial:

```text
GET /api/groups/{group}/students
```

Devuelve todos los estudiantes cuyo `group_id` coincide con el grupo. Como el grupo ya tiene `course_id` y `career_id`, esa respuesta representa los estudiantes de esa carrera y curso dentro de ese grupo.

## Estudiantes

Ruta CRUD: `/api/students`

Campos:

```json
{
  "group_id": 1,
  "current_enrollment_id": null,
  "student_code": "EST-0001",
  "first_name": "Ana",
  "last_name": "Perez Gomez",
  "document_type": "carnet",
  "document_number": "00010112345",
  "email": "ana.perez@example.edu",
  "phone": "+5350000000",
  "birth_date": "2005-01-01",
  "admission_date": "2026-09-01",
  "exit_date": null,
  "exit_reason": null,
  "status": "active"
}
```

Rutas especiales:

```text
GET /api/students/{student}/payment-status
GET /api/students/{student}/academic-summary
GET /api/students/{student}/academic-history
GET /api/students/{student}/kardex
GET /api/students/{student}/grades
```

`payment-status` responde si el estudiante puede matricularse:

```json
{
  "student_id": 1,
  "can_enroll": true,
  "required_payment_concept": "enrollment",
  "latest_payments": []
}
```

### Historial academico paginado

El historial academico esta separado por vistas para evitar respuestas gigantes en produccion.

#### Resumen rapido

```text
GET /api/students/{student}/academic-summary
```

Uso recomendado: ficha del estudiante, dashboard, encabezado del expediente.

Respuesta:

```json
{
  "student_id": 1,
  "current_status": "active",
  "admission_date": "2026-09-01",
  "exit_date": null,
  "exit_reason": null,
  "current_group": {
    "id": 1,
    "name": "INF-2026-A",
    "course": {
      "id": 1,
      "name": "2026-2027"
    },
    "career": {
      "id": 1,
      "name": "Ingenieria Informatica",
      "abbreviation": "INF"
    }
  },
  "current_enrollment": {
    "id": 1,
    "start_course_id": 1,
    "end_course_id": null,
    "status": "active"
  },
  "subject_totals": {
    "total": 8,
    "enrolled": 4,
    "passed": 3,
    "failed": 1,
    "withdrawn": 0
  },
  "credits": {
    "passed": 12,
    "attempted": 28
  },
  "grades": {
    "published_count": 6,
    "average": 87.5,
    "last_evaluated_at": "2026-11-20"
  }
}
```

#### Historial de asignaturas

```text
GET /api/students/{student}/academic-history
```

Uso recomendado: pantalla de asignaturas matriculadas/cursadas. Esta ruta ahora es paginada.

Query params:

```text
per_page=25
cursor=true
status=enrolled|passed|failed|withdrawn
course_id=1
career_id=1
group_id=1
semester=1
subject_id=1
curriculum_plan_id=1
from=2026-09-01
to=2027-07-30
```

Cada item incluye asignatura, curso, carrera, grupo, oferta academica y metricas de notas publicadas:

```json
{
  "data": [
    {
      "id": 10,
      "student_id": 1,
      "subject_id": 3,
      "course_id": 1,
      "career_id": 1,
      "group_id": 1,
      "semester": 1,
      "status": "passed",
      "published_grades_count": 2,
      "published_grade_average": "88.500000",
      "last_evaluated_at": "2026-11-20",
      "subject": {
        "id": 3,
        "code": "MAT-101",
        "name": "Matematica I",
        "credits": 4
      }
    }
  ],
  "links": {},
  "meta": {}
}
```

#### Kardex oficial

```text
GET /api/students/{student}/kardex
```

Uso recomendado: constancia academica, expediente oficial, vista por secretaria/registro. Devuelve solo asignaturas cerradas: `passed`, `failed` y `withdrawn`.

Acepta los mismos filtros de `academic-history`. Cada item incluye plan de estudio, curso, asignatura, estado final, fecha de cierre y nota final promedio de evaluaciones publicadas.

#### Notas del estudiante

```text
GET /api/students/{student}/grades
```

Uso recomendado: tabla de evaluaciones, detalle por profesor, frontend de estudiante/profesor.

Query params:

```text
per_page=25
cursor=true
status=draft|published|void
course_id=1
subject_id=1
professor_id=1
evaluation_type=final
from=2026-09-01
to=2026-12-30
```

Esta ruta devuelve las calificaciones paginadas con asignatura, profesor y la matricula de asignatura relacionada.

## Finanzas y pago falso/manual

Ruta CRUD: `/api/finances`

Campos para crear un pago pendiente:

```json
{
  "student_id": 1,
  "enrollment_id": null,
  "amount": 250.00,
  "currency": "USD",
  "concept": "enrollment",
  "due_date": "2026-08-30",
  "status": "pending"
}
```

Ruta para validar pago:

```text
PATCH /api/finances/{finance}/mark-paid
```

Body:

```json
{
  "payment_method": "manual",
  "payment_reference": "PAY-0001",
  "paid_at": "2026-08-25",
  "status_reason": "Pago validado manualmente por administracion"
}
```

Despues de esto, ese pago queda `paid` y el estudiante puede matricularse. Cuando exista pasarela real, `payment_reference` debe venir de la pasarela.

## Matriculas

Ruta CRUD: `/api/enrollments`

Campos:

```json
{
  "student_id": 1,
  "start_course_id": 1,
  "end_course_id": null,
  "enrollment_date": "2026-09-01",
  "status": "active",
  "notes": "Matricula inicial"
}
```

Condicion obligatoria:

- El estudiante debe tener al menos un registro en `finances` con `concept = "enrollment"` y `status = "paid"`.

Si no existe ese pago, la API responde:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "student_id": [
      "El estudiante no puede matricularse hasta tener un pago de matricula validado con status paid."
    ]
  }
}
```

## Matricula de asignaturas

Ruta CRUD: `/api/subject-enrollments`

Campos minimos:

```json
{
  "enrollment_id": 1,
  "subject_id": 2
}
```

La API infiere:

- `student_id` desde la matricula.
- `course_id` desde `start_course_id` de la matricula.
- `career_id` y `group_id` desde el grupo actual del estudiante.
- `enrolled_at` con la fecha actual si no se manda.
- `status = "enrolled"` si no se manda.

Campos completos si el frontend necesita control total:

```json
{
  "enrollment_id": 1,
  "student_id": 1,
  "subject_id": 2,
  "course_id": 1,
  "career_id": 1,
  "group_id": 1,
  "enrolled_at": "2026-09-01",
  "completed_at": null,
  "status": "enrolled",
  "notes": "Asignatura del primer semestre"
}
```

Estados sugeridos:

```text
enrolled
passed
failed
withdrawn
cancelled
```

## Profesores

Ruta CRUD: `/api/professors`

Campos:

```json
{
  "subject_id": 2,
  "professor_code": "PROF-0001",
  "first_name": "Carlos",
  "last_name": "Rodriguez",
  "email": "carlos.rodriguez@example.edu",
  "phone": "+5351111111",
  "status": "active"
}
```

Por ahora cada profesor tiene una asignatura principal. Mas adelante se puede agregar tabla pivote si un profesor imparte muchas asignaturas en muchos grupos.

## Calificaciones

Rutas principales:

```text
/api/grading-scales
/api/grading-scale-levels
/api/grade-components
/api/grade-sheets
/api/grade-change-requests
/api/grades
```

Acciones de acta:

```text
POST /api/grade-sheets/{gradeSheet}/submit
POST /api/grade-sheets/{gradeSheet}/sign
POST /api/grade-sheets/{gradeSheet}/close
```

Acciones de cambio autorizado:

```text
POST /api/grade-change-requests/{gradeChangeRequest}/approve
POST /api/grade-change-requests/{gradeChangeRequest}/reject
```

Ruta CRUD de notas: `/api/grades`

Campos:

```json
{
  "subject_enrollment_id": 1,
  "grade_sheet_id": 1,
  "grade_component_id": 1,
  "raw_value": 95,
  "attempt_type": "ordinary",
  "call_number": 1,
  "partial_number": null,
  "is_final": true,
  "evaluated_at": "2026-12-15",
  "status": "published",
  "notes": "Excelente desempeno"
}
```

La API deriva desde `subject_enrollment_id`, `grade_component_id` y `grade_sheet_id`:

- `student_id`
- `subject_id`
- `professor_id`
- escala de calificacion
- nivel de escala
- `value` normalizado
- peso del componente

Cuando una nota final queda `published`, la API actualiza la matricula de asignatura:

- `value >= passing_value` de la escala: `subject_enrollments.status = "passed"`.
- `value < passing_value` de la escala: `subject_enrollments.status = "failed"`.

Asi queda constancia de que el estudiante curso la asignatura, con que profesor y con que nota.

Filtros utiles:

```text
GET /api/grades?grade_sheet_id=1
GET /api/grades?subject_enrollment_id=1
GET /api/grades?student_id=1&status=published
GET /api/grades?professor_id=1&attempt_type=ordinary&call_number=1
```

El flujo completo de escalas, componentes, actas, firma, cierre y cambios autorizados esta en [GRADEBOOK.md](GRADEBOOK.md).

## Historial de estados

Ruta de consulta:

```text
GET /api/status-histories
GET /api/status-histories/{id}
```

Filtros:

```text
GET /api/status-histories?trackable_type=App\Models\Student&trackable_id=1
```

Cada vez que una entidad con campo `status` se crea o cambia de estado, se guarda:

```json
{
  "trackable_type": "App\\Models\\Student",
  "trackable_id": 1,
  "previous_status": "active",
  "new_status": "inactive",
  "reason": "Baja temporal",
  "metadata": null,
  "changed_at": "2026-06-30T22:00:00.000000Z"
}
```

Para guardar motivo desde cualquier `PATCH`, el frontend puede mandar:

```json
{
  "status": "inactive",
  "status_reason": "Baja temporal por solicitud del estudiante",
  "status_metadata": {
    "approved_by": "secretaria academica"
  }
}
```

## Orden recomendado de pantallas

1. Catalogos: carreras, cursos, asignaturas.
2. Planes de estudio: crear plan y asociar asignaturas por semestre.
3. Grupos: asociar curso + carrera.
4. Estudiantes: crear estudiante y asignarle grupo.
5. Finanzas: crear pago pendiente y marcarlo pagado.
6. Matriculas: crear matricula solo si `payment-status.can_enroll = true`.
7. Asignaturas matriculadas: registrar asignaturas del estudiante.
8. Profesores: crear profesor asociado a asignatura.
9. Calificaciones: publicar notas.
10. Historial academico: consultar lo cursado/aprobado por estudiante.
